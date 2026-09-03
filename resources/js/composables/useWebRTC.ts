import { ref, unref, onUnmounted } from 'vue'
import axios from 'axios'

export function useWebRTC(conversationIdRef?: any) {
  const peerConnection = ref<RTCPeerConnection | null>(null)
  const localStream = ref<MediaStream | null>(null)
  const remoteStream = ref<MediaStream | null>(null)

  const isCallActive = ref(false)
  const isCallIncoming = ref(false)
  const isCallOutgoing = ref(false)
  const isVideoCall = ref(false)

  const callerName = ref('')
  const targetConversationId = ref<number | null>(null)

  const isMicMuted = ref(false)
  const isCameraEnabled = ref(true)
  const isSpeakerMuted = ref(false)

  const callDuration = ref(0)
  let timerInterval: any = null

  const pendingOffer = ref<any>(null)
  const pendingCandidates: RTCIceCandidateInit[] = []

  const rtcConfig: RTCConfiguration = {
    iceServers: [
      { urls: 'stun:stun.l.google.com:19302' },
      { urls: 'stun:stun1.l.google.com:19302' }
    ]
  }

  // مستخرج آمن لرقم المحادثة
  const getActiveConversationId = (overrideId?: number): number | null => {
    if (overrideId) return Number(overrideId)
    if (targetConversationId.value) return Number(targetConversationId.value)

    const extracted = unref(conversationIdRef)
    if (extracted && typeof extracted === 'object' && 'value' in extracted) {
      return Number(extracted.value) || null
    }
    return extracted ? Number(extracted) : null
  }

  const startTimer = () => {
    stopTimer()
    callDuration.value = 0
    timerInterval = setInterval(() => {
      callDuration.value++
    }, 1000)
  }

  const stopTimer = () => {
    if (timerInterval) {
      clearInterval(timerInterval)
      timerInterval = null
    }
  }

  const formatDuration = (seconds: number) => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }

  // إرسال إشارات WebRTC مع دعم Fallback Route
  const sendSignal = async (type: string, data: any = null, convId?: number) => {
    const activeId = getActiveConversationId(convId)
    if (!activeId) {
      console.warn('Cannot send signal: No active conversation ID provided.')
      return
    }

    const payload = { type, data, conversation_id: activeId }

    try {
      await axios.post(`/conversations/${activeId}/signal`, payload)
    } catch (err: any) {
      if (err?.response?.status === 404) {
        try {
          await axios.post(`/chat/${activeId}/call/signal`, payload)
        } catch (fallbackErr) {
          console.error('Signal Fallback Failed:', fallbackErr)
        }
      } else {
        console.error('Error sending signal:', err)
      }
    }
  }

  const initPeerConnection = () => {
    if (peerConnection.value) return peerConnection.value

    const pc = new RTCPeerConnection(rtcConfig)
    remoteStream.value = new MediaStream()

    pc.ontrack = (event) => {
      if (event.streams && event.streams[0]) {
        event.streams[0].getTracks().forEach((track) => {
          remoteStream.value?.addTrack(track)
        })
      }
    }

    pc.onicecandidate = (event) => {
      if (event.candidate) {
        const currentConvId = getActiveConversationId()
        if (currentConvId) {
          sendSignal('candidate', event.candidate, currentConvId)
        }
      }
    }

    pc.onconnectionstatechange = () => {
      if (pc.connectionState === 'connected') {
        isCallOutgoing.value = false
        isCallIncoming.value = false
        isCallActive.value = true
        startTimer()
      } else if (['disconnected', 'failed', 'closed'].includes(pc.connectionState)) {
        cleanupCall()
      }
    }

    peerConnection.value = pc
    return pc
  }

  const setupLocalStream = async (video: boolean) => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        audio: true,
        video: video ? { width: { ideal: 1280 }, height: { ideal: 720 } } : false
      })
      localStream.value = stream
      const pc = initPeerConnection()

      stream.getTracks().forEach((track) => {
        pc.addTrack(track, stream)
      })
    } catch (err) {
      console.error('Failed to access media devices:', err)
      cleanupCall()
    }
  }

  const startCall = async (video: boolean = false, convId?: number) => {
    const activeId = getActiveConversationId(convId)
    if (!activeId) return

    targetConversationId.value = activeId
    isVideoCall.value = video
    isCallOutgoing.value = true

    await setupLocalStream(video)
    const pc = peerConnection.value
    if (!pc) return

    const offer = await pc.createOffer()
    await pc.setLocalDescription(offer)

    await sendSignal('offer', {
      type: offer.type,
      sdp: offer.sdp,
      isVideo: video
    }, activeId)
  }

  const acceptCall = async (customOffer?: any) => {
    const offerToUse = customOffer || pendingOffer.value
    if (!offerToUse) {
      console.warn('No pending offer found to accept call.')
      return
    }

    const activeId = getActiveConversationId()
    isCallIncoming.value = false

    // فحص ما إذا كانت المكالمة تحتوي على فيديو
    let sdpData = offerToUse
    if (typeof sdpData === 'string') {
      try { sdpData = JSON.parse(sdpData) } catch (e) { sdpData = { type: 'offer', sdp: sdpData } }
    }

    if (sdpData.isVideo !== undefined) {
      isVideoCall.value = !!sdpData.isVideo
    }

    await setupLocalStream(isVideoCall.value)

    const pc = peerConnection.value
    if (!pc) return

    const sessionInit: RTCSessionDescriptionInit = {
      type: sdpData.type || 'offer',
      sdp: typeof sdpData.sdp === 'string' ? sdpData.sdp : sdpData.sdp || sdpData
    }

    await pc.setRemoteDescription(new RTCSessionDescription(sessionInit))

    const answer = await pc.createAnswer()
    await pc.setLocalDescription(answer)

    await sendSignal('answer', {
      type: answer.type,
      sdp: answer.sdp
    }, activeId!)

    await processPendingCandidates()
    pendingOffer.value = null
  }

  const handleSignal = async (event: any) => {
    const { type, data, caller, conversation_id } = event

    if (conversation_id) {
      targetConversationId.value = Number(conversation_id)
    }

    switch (type) {
      case 'offer':
        callerName.value = caller?.name || 'مكالمة واردة'
        isVideoCall.value = !!data?.isVideo
        pendingOffer.value = data
        isCallIncoming.value = true
        break

      case 'answer':
        if (peerConnection.value && isCallOutgoing.value) {
          let sdpData = data
          if (typeof sdpData === 'string') {
            try { sdpData = JSON.parse(sdpData) } catch (e) { sdpData = { type: 'answer', sdp: sdpData } }
          }
          const sessionInit: RTCSessionDescriptionInit = {
            type: sdpData.type || 'answer',
            sdp: typeof sdpData.sdp === 'string' ? sdpData.sdp : sdpData.sdp || sdpData
          }
          await peerConnection.value.setRemoteDescription(new RTCSessionDescription(sessionInit))
          await processPendingCandidates()
        }
        break

      case 'candidate':
        if (peerConnection.value && peerConnection.value.remoteDescription) {
          try {
            await peerConnection.value.addIceCandidate(new RTCIceCandidate(data))
          } catch (e) {
            console.error('Error adding ICE candidate:', e)
          }
        } else {
          pendingCandidates.push(data)
        }
        break

      case 'reject':
      case 'end':
        cleanupCall()
        break
    }
  }

  const processPendingCandidates = async () => {
    if (peerConnection.value && peerConnection.value.remoteDescription) {
      while (pendingCandidates.length > 0) {
        const cand = pendingCandidates.shift()
        if (cand) {
          try {
            await peerConnection.value.addIceCandidate(new RTCIceCandidate(cand))
          } catch (e) {
            console.error('Error adding pending ICE candidate:', e)
          }
        }
      }
    }
  }

  const rejectCall = () => {
    const activeId = getActiveConversationId()
    if (activeId) sendSignal('reject', null, activeId)
    cleanupCall()
  }

  const endCall = () => {
    const activeId = getActiveConversationId()
    if (activeId) sendSignal('end', null, activeId)
    cleanupCall()
  }

  const cleanupCall = () => {
    stopTimer()

    if (localStream.value) {
      localStream.value.getTracks().forEach((track) => track.stop())
      localStream.value = null
    }

    if (remoteStream.value) {
      remoteStream.value.getTracks().forEach((track) => track.stop())
      remoteStream.value = null
    }

    if (peerConnection.value) {
      peerConnection.value.ontrack = null
      peerConnection.value.onicecandidate = null
      peerConnection.value.onconnectionstatechange = null
      peerConnection.value.close()
      peerConnection.value = null
    }

    isCallActive.value = false
    isCallIncoming.value = false
    isCallOutgoing.value = false
    isMicMuted.value = false
    isCameraEnabled.value = true
    isSpeakerMuted.value = false
    pendingOffer.value = null
    pendingCandidates.length = 0
  }

  const toggleMic = () => {
    if (localStream.value) {
      const audioTrack = localStream.value.getAudioTracks()[0]
      if (audioTrack) {
        audioTrack.enabled = !audioTrack.enabled
        isMicMuted.value = !audioTrack.enabled
      }
    }
  }

  const toggleCamera = () => {
    if (localStream.value) {
      const videoTrack = localStream.value.getVideoTracks()[0]
      if (videoTrack) {
        videoTrack.enabled = !videoTrack.enabled
        isCameraEnabled.value = videoTrack.enabled
      }
    }
  }

  const toggleSpeaker = () => {
    isSpeakerMuted.value = !isSpeakerMuted.value
  }

  onUnmounted(() => {
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
    pendingOffer,
    formatDuration,
    handleSignal,
    startCall,
    acceptCall,
    rejectCall,
    endCall,
    toggleMic,
    toggleSpeaker,
    toggleCamera
  }
}
