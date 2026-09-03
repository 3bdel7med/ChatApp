<script setup>
import { ref } from 'vue'

const emit = defineEmits(['send-audio'])

const isRecording = ref(false)
const isRecorded = ref(false)
const isPlaying = ref(false)
const recordingTime = ref(0)
const audioUrl = ref(null)

let mediaRecorder = null
let audioChunks = []
let timerInterval = null
let recordedBlob = null
let audioElement = null

const startRecording = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    mediaRecorder = new MediaRecorder(stream)
    audioChunks = []
    audioUrl.value = null
    isRecorded.value = false

    mediaRecorder.ondataavailable = (event) => {
      if (event.data.size > 0) audioChunks.push(event.data)
    }

    mediaRecorder.start()
    isRecording.value = true
    recordingTime.value = 0

    timerInterval = setInterval(() => {
      recordingTime.value++
    }, 1000)
  } catch (err) {
    alert('Microphone access is required to record voice messages.')
  }
}

// Stop recording and show Preview / Re-record options
const stopRecording = () => {
  if (!mediaRecorder || mediaRecorder.state === 'inactive') return

  mediaRecorder.onstop = () => {
    recordedBlob = new Blob(audioChunks, { type: 'audio/webm' })
    audioUrl.value = URL.createObjectURL(recordedBlob)
    isRecording.value = false
    isRecorded.value = true
    clearInterval(timerInterval)

    audioElement = new Audio(audioUrl.value)
    audioElement.onended = () => {
      isPlaying.value = false
    }
  }

  mediaRecorder.stop()
  mediaRecorder.stream.getTracks().forEach((track) => track.stop())
}

// Play / Pause Preview Audio
const togglePlayPreview = () => {
  if (!audioElement) return

  if (isPlaying.value) {
    audioElement.pause()
    isPlaying.value = false
  } else {
    audioElement.play()
    isPlaying.value = true
  }
}

// Re-record: Reset state and immediately start recording again
const reRecord = () => {
  resetRecorder()
  startRecording()
}

// Send the recorded voice message
const sendAudio = () => {
  if (!recordedBlob) return

  const audioFile = new File([recordedBlob], `voice_note_${Date.now()}.webm`, {
    type: 'audio/webm',
  })

  emit('send-audio', audioFile)
  resetRecorder()
}

const cancelRecording = () => {
  if (mediaRecorder && mediaRecorder.state !== 'inactive') {
    mediaRecorder.stop()
    mediaRecorder.stream.getTracks().forEach((track) => track.stop())
  }
  resetRecorder()
}

const resetRecorder = () => {
  if (audioElement) {
    audioElement.pause()
    audioElement = null
  }
  isRecording.value = false
  isRecorded.value = false
  isPlaying.value = false
  recordingTime.value = 0
  audioUrl.value = null
  recordedBlob = null
  audioChunks = []
  clearInterval(timerInterval)
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs < 10 ? '0' : ''}${secs}`
}
</script>

<template>
  <div class="flex items-center gap-2">
    <!-- Active Recording UI -->
    <div v-if="isRecording" class="flex items-center gap-3 bg-red-50 text-red-600 px-3 py-1.5 rounded-full border border-red-200">
      <span class="w-3 h-3 bg-red-500 rounded-full animate-ping"></span>
      <span class="text-xs font-mono font-bold">{{ formatTime(recordingTime) }}</span>

      <!-- Stop Recording (Proceed to Preview) -->
      <button
        @click="stopRecording"
        type="button"
        class="p-1 bg-red-600 text-white rounded-full hover:bg-red-700 transition"
        title="Done Recording"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
        </svg>
      </button>

      <!-- Cancel -->
      <button @click="cancelRecording" type="button" class="text-xs text-gray-500 hover:text-red-600 ml-1">
        Cancel
      </button>
    </div>

    <!-- Recorded Preview UI (Play, Re-record, Send, Cancel) -->
    <div v-else-if="isRecorded" class="flex items-center gap-2 bg-gray-100 px-3 py-1.5 rounded-full border border-gray-200">
      <!-- Play / Pause Preview -->
      <button @click="togglePlayPreview" type="button" class="p-1 text-blue-600 hover:bg-gray-200 rounded-full">
        <svg v-if="!isPlaying" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.333-5.89a1.5 1.5 0 000-2.538L6.3 2.841z" />
        </svg>
        <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      </button>

      <span class="text-xs font-mono text-gray-600 font-medium">{{ formatTime(recordingTime) }}</span>

      <!-- RE-RECORD BUTTON -->
      <button
        @click="reRecord"
        type="button"
        class="flex items-center gap-1 text-xs text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 px-2 py-0.5 rounded-full transition"
        title="Re-record Voice"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Re-record
      </button>

      <!-- Cancel Button -->
      <button @click="cancelRecording" type="button" class="text-xs text-gray-400 hover:text-red-500">
        ✕
      </button>

      <!-- Send Button -->
      <button
        @click="sendAudio"
        type="button"
        class="p-1 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition ml-1"
        title="Send Voice Message"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </button>
    </div>

    <!-- Default Microphone Start Button -->
    <button
      v-else
      @click="startRecording"
      type="button"
      class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-full transition"
      title="Record Voice Message"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
      </svg>
    </button>
  </div>
</template>
