import { ref, onUnmounted } from 'vue'
import axios from 'axios'

const ICE_SERVERS = {
  iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' },
  ],
}

/**
 * Generate a ringing tone using Web Audio API (no external files needed)
 */
function createRingingTone(): { play: () => void; stop: () => void } {
  let audioCtx: AudioContext | null = null
  let gainNode: GainNode | null = null
  let oscillator1: OscillatorNode | null = null
  let oscillator2: OscillatorNode | null = null
  let isPlaying = false
  let timeoutId: number | null = null

  const play = () => {
    if (isPlaying) return
    isPlaying = true

    try {
      audioCtx = new AudioContext()
      gainNode = audioCtx.createGain()
      gainNode.gain.value = 0.3
      gainNode.connect(audioCtx.destination)

      // Create a dual-tone ring (like a phone ring: 440Hz + 480Hz)
      oscillator1 = audioCtx.createOscillator()
      oscillator1.type = 'sine'
      oscillator1.frequency.value = 440
      oscillator1.connect(gainNode)

      oscillator2 = audioCtx.createOscillator()
      oscillator2.type = 'sine'
      oscillator2.frequency.value = 480
      oscillator2.connect(gainNode)

      oscillator1.start()
      oscillator2.start()

      // Ring pattern: 2 seconds on, 4 seconds off (repeat)
      const ringPattern = () => {
        if (!gainNode || !isPlaying) return
        gainNode.gain.value = 0.3
        timeoutId = window.setTimeout(() => {
          if (gainNode && isPlaying) {
            gainNode.gain.value = 0
            timeoutId = window.setTimeout(ringPattern, 4000)
          }
        }, 2000)
      }
      ringPattern()
    } catch (e) {
      console.warn('Could not create ringing tone:', e)
    }
  }

  const stop = () => {
    isPlaying = false
    if (timeoutId !== null) {
      clearTimeout(timeoutId)
      timeoutId = null
    }
    try {
      oscillator1?.stop()
      oscillator2?.stop()
    } catch (e) { /* ignore */ }
    oscillator1 = null
    oscillator2 = null
    gainNode = null
    if (audioCtx) {
      audioCtx.close().catch(() => {})
      audioCtx = null
    }
  }

  return { play, stop }
}

export function useWebRTC(conversationId: number) {
  const isCallActive = ref(false)
  const isCallIncoming = ref(false)
  const isCallOutgoing = ref(false)
  const callDuration = ref(0)
  const remoteStream = ref<MediaStream | null>(null)
  const localStream = ref<MediaStream | null>(null)
  const callerName = ref('')
  const isMicMuted = ref(false)
  const isSpeakerMuted = ref(false)
  const isVideoCall = ref(false)
  const isCameraEnabled = ref(false)

  let peerConnection: RTCPeerConnection | null = null
  let localMediaStream: MediaStream | null = null
  let callTimerInterval: number | null = null

  // Ringing tone controllers
  let incomingRingTone = createRingingTone()
  let outgoingRingbackTone = createRingingTone()

  // Store pending offer data until user accepts
  let pendingOffer: any = null
  let pendingCaller: any = null
  let pendingVideo = false

  // Initialize Echo listener for call events on an existing channel
  const registerCallListeners = (channel: any) => {
    channel.listen('.call.event', async (e: any) => {
      const { type, data, caller } = e

      switch (type) {
        case 'offer':
          // Store offer + caller info, show incoming UI
          pendingOffer = data
          pendingCaller = caller
          callerName.value = caller.name
          // Detect whether this is a video call from the offer payload
          pendingVideo = !!(data && data.video)
          isVideoCall.value = pendingVideo
          isCallIncoming.value = true
          // Play incoming ringing tone
          incomingRingTone.play()
          break

        case 'answer':
          // Caller receives answer — stop ringback tone
          outgoingRingbackTone.stop()
          await handleAnswer(data)
          // Start the call timer only when the receiver accepts the call
          if (callTimerInterval === null) {
            startCallTimer()
          }
          break

        case 'ice-candidate':
          if (data && peerConnection) {
            try {
              await peerConnection.addIceCandidate(new RTCIceCandidate(data))
            } catch (err) {
              console.error('Error adding ICE candidate:', err)
            }
          }
          break

        case 'end-call':
          stopAllTones()
          cleanupCall()
          break
      }
    })
  }

  // Start a call (caller)
  const startCall = async (video = false) => {
    try {
      localMediaStream = await navigator.mediaDevices.getUserMedia({ audio: true, video })
      localStream.value = localMediaStream
      isVideoCall.value = video
      isCameraEnabled.value = video
      isCallOutgoing.value = true
      isCallActive.value = true
      // Play ringback tone for the caller (timer starts when receiver accepts)
      outgoingRingbackTone.play()

      peerConnection = new RTCPeerConnection(ICE_SERVERS)
      localMediaStream.getTracks().forEach(track => {
        peerConnection!.addTrack(track, localMediaStream!)
      })

      peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
          sendSignal('ice-candidate', event.candidate)
        }
      }

      peerConnection.ontrack = (event) => {
        remoteStream.value = event.streams[0]
      }

      peerConnection.oniceconnectionstatechange = () => {
        if (peerConnection?.iceConnectionState === 'connected' ||
            peerConnection?.iceConnectionState === 'completed') {
          // Connection established — stop ringback if still playing
          outgoingRingbackTone.stop()
        }
        if (peerConnection?.iceConnectionState === 'disconnected' ||
            peerConnection?.iceConnectionState === 'failed') {
          cleanupCall()
        }
      }

      const offer = await peerConnection.createOffer({
        offerToReceiveAudio: true,
        offerToReceiveVideo: video,
      })
      await peerConnection.setLocalDescription(offer)
      // Include the video flag in the offer payload so the callee knows it's a video call
      sendSignal('offer', { sdp: offer.sdp, type: offer.type, video })
    } catch (err) {
      console.error('Error starting call:', err)
      cleanupCall()
    }
  }

  // Accept incoming call (receiver clicks "Accept")
  const acceptCall = async () => {
    try {
      // Stop incoming ringing tone
      incomingRingTone.stop()

      isCallIncoming.value = false
      isCallOutgoing.value = false
      isVideoCall.value = pendingVideo

      // Get local media (video only if the incoming call is a video call)
      localMediaStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: pendingVideo })
      localStream.value = localMediaStream
      isCameraEnabled.value = pendingVideo
      isCallActive.value = true
      if (callTimerInterval === null) {
        startCallTimer()
      }

      peerConnection = new RTCPeerConnection(ICE_SERVERS)
      localMediaStream.getTracks().forEach(track => {
        peerConnection!.addTrack(track, localMediaStream!)
      })

      peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
          sendSignal('ice-candidate', event.candidate)
        }
      }

      peerConnection.ontrack = (event) => {
        remoteStream.value = event.streams[0]
      }

      peerConnection.oniceconnectionstatechange = () => {
        if (peerConnection?.iceConnectionState === 'disconnected' ||
            peerConnection?.iceConnectionState === 'failed') {
          cleanupCall()
        }
      }

      // If we have a pending offer, set remote description and send answer
      if (pendingOffer) {
        // Rebuild a proper RTCSessionDescription from the payload
        const remoteDescription = new RTCSessionDescription({
          type: pendingOffer.type || 'offer',
          sdp: pendingOffer.sdp,
        })
        await peerConnection.setRemoteDescription(remoteDescription)
        const answer = await peerConnection.createAnswer({
          offerToReceiveAudio: true,
          offerToReceiveVideo: pendingVideo,
        })
        await peerConnection.setLocalDescription(answer)
        sendSignal('answer', { sdp: answer.sdp, type: answer.type, video: pendingVideo })
        pendingOffer = null
        pendingCaller = null
      }
    } catch (err) {
      console.error('Error accepting call:', err)
      cleanupCall()
    }
  }

  // Handle answer (caller receives answer)
  const handleAnswer = async (answer: any) => {
    try {
      if (peerConnection && peerConnection.signalingState !== 'closed') {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(answer))
      }
    } catch (err) {
      console.error('Error handling answer:', err)
    }
  }

  // Reject incoming call
  const rejectCall = () => {
    sendSignal('end-call', { reason: 'rejected' })
    pendingOffer = null
    pendingCaller = null
    incomingRingTone.stop()
    cleanupCall()
  }

  // End call
  const endCall = () => {
    sendSignal('end-call', { reason: 'ended' })
    pendingOffer = null
    pendingCaller = null
    stopAllTones()
    cleanupCall()
  }

  // Toggle microphone
  const toggleMic = () => {
    if (localMediaStream) {
      const audioTrack = localMediaStream.getAudioTracks()[0]
      if (audioTrack) {
        audioTrack.enabled = !audioTrack.enabled
        isMicMuted.value = !audioTrack.enabled
      }
    }
  }

  // Toggle speaker
  const toggleSpeaker = () => {
    isSpeakerMuted.value = !isSpeakerMuted.value
  }

  // Toggle camera during a video call
  const toggleCamera = () => {
    if (!isVideoCall.value) return
    if (!localMediaStream) return

    const videoTrack = localMediaStream.getVideoTracks()[0]
    if (videoTrack) {
      videoTrack.enabled = !videoTrack.enabled
      isCameraEnabled.value = videoTrack.enabled
    }
  }

  // Stop all tones
  const stopAllTones = () => {
    incomingRingTone.stop()
    outgoingRingbackTone.stop()
  }

  // Cleanup
  const cleanupCall = () => {
    stopAllTones()

    if (peerConnection) {
      peerConnection.close()
      peerConnection = null
    }
    if (localMediaStream) {
      localMediaStream.getTracks().forEach(track => track.stop())
      localMediaStream = null
    }
    if (callTimerInterval) {
      clearInterval(callTimerInterval)
      callTimerInterval = null
    }

    localStream.value = null
    remoteStream.value = null
    isCallActive.value = false
    isCallIncoming.value = false
    isCallOutgoing.value = false
    callDuration.value = 0
    isMicMuted.value = false
    isSpeakerMuted.value = false
    isVideoCall.value = false
    isCameraEnabled.value = false
    callerName.value = ''
    pendingOffer = null
    pendingCaller = null
    pendingVideo = false
  }

  // Start call timer
  const startCallTimer = () => {
    callDuration.value = 0
    callTimerInterval = window.setInterval(() => {
      callDuration.value++
    }, 1000)
  }

  // Send signal via server
  const sendSignal = (type: string, data: any) => {
    axios.post(route('chat.call.signal', conversationId), {
      type,
      data,
    }).catch(err => {
      console.error('Error sending signal:', err)
    })
  }

  // Format duration
  const formatDuration = (seconds: number): string => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }

  // Cleanup on unmount
  onUnmounted(() => {
    stopAllTones()
    cleanupCall()
  })

  return {
    isCallActive,
    isCallIncoming,
    isCallOutgoing,
    callDuration,
    remoteStream,
    localStream,
    callerName,
    isMicMuted,
    isSpeakerMuted,
    isVideoCall,
    isCameraEnabled,
    formatDuration,
    registerCallListeners,
    startCall,
    acceptCall,
    rejectCall,
    endCall,
    toggleMic,
    toggleSpeaker,
    toggleCamera,
  }
}

