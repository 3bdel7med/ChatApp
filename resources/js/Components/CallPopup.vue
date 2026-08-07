<script setup lang="ts">
import { ref, watch, nextTick, onUnmounted } from 'vue'

const props = defineProps<{
  isCallActive: boolean
  isCallIncoming: boolean
  isCallOutgoing: boolean
  callDuration: number
  callerName: string
  isMicMuted: boolean
  isSpeakerMuted: boolean
  isVideoCall: boolean
  isCameraEnabled: boolean
  remoteStream: MediaStream | null
  localStream: MediaStream | null
  formatDuration: (seconds: number) => string
}>()

const emit = defineEmits<{
  accept: []
  reject: []
  endCall: []
  toggleMic: []
  toggleSpeaker: []
  toggleCamera: []
}>()

const localAudioRef = ref<HTMLAudioElement | null>(null)
const remoteAudioRef = ref<HTMLAudioElement | null>(null)
const localVideoRef = ref<HTMLVideoElement | null>(null)
const remoteVideoRef = ref<HTMLVideoElement | null>(null)

// Attach streams to audio elements
watch(() => props.localStream, (stream) => {
  if (localAudioRef.value && stream) {
    localAudioRef.value.srcObject = stream
  }
})

// Attach the local camera stream to the <video> element.
// The element is conditionally rendered (v-if isVideoCall && isCameraEnabled && localStream),
// so wait a tick until it exists before assigning srcObject.
watch(
  () => [props.localStream, props.isVideoCall, props.isCameraEnabled],
  () => {
    nextTick(() => {
      if (localVideoRef.value && props.localStream) {
        localVideoRef.value.srcObject = props.localStream
      }
    })
  },
  { immediate: true }
)

watch(() => props.remoteStream, (stream) => {
  if (remoteAudioRef.value && stream) {
    remoteAudioRef.value.srcObject = stream
  }
  // The remote <video> element is conditionally rendered only in the video layout,
  // so wait a tick until it exists before assigning srcObject.
  nextTick(() => {
    if (remoteVideoRef.value && stream) {
      remoteVideoRef.value.srcObject = stream
    }
  })
})

// Prevent body scroll when call is active
watch(() => props.isCallActive || props.isCallIncoming, (val) => {
  if (val) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

onUnmounted(() => {
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <!-- Incoming Call Notification -->
    <div
      v-if="isCallIncoming"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm"
    >
      <div class="bg-white rounded-3xl w-full max-w-sm p-8 text-center shadow-2xl animate-bounce-in">
        <div class="w-20 h-20 mx-auto rounded-full bg-green-100 text-green-600 flex items-center justify-center mb-4">
          <svg v-if="isVideoCall" xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-1">{{ isVideoCall ? 'مكالمة فيديو واردة' : 'مكالمة واردة' }}</h3>
        <p class="text-gray-500 mb-6">{{ callerName }} يتصل بك...</p>

        <div class="flex justify-center gap-6">
          <button
            @click="emit('reject')"
            class="w-16 h-16 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 transition shadow-lg"
            title="رفض"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <button
            @click="emit('accept')"
            class="w-16 h-16 rounded-full bg-green-500 text-white flex items-center justify-center hover:bg-green-600 transition shadow-lg"
            title="قبول"
          >
            <svg v-if="isVideoCall" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Active Call Overlay -->
    <div
      v-if="isCallActive"
      class="fixed inset-0 z-[100] bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex flex-col items-center justify-center"
    >
      <!-- ===================== VIDEO CALL LAYOUT ===================== -->
      <template v-if="isVideoCall">
        <!-- Remote Video (full screen) -->
        <video
          ref="remoteVideoRef"
          autoplay
          playsinline
          class="absolute inset-0 w-full h-full object-cover"
        ></video>

        <!-- Fallback avatar if no remote video yet -->
        <div
          v-if="!remoteStream"
          class="absolute inset-0 flex flex-col items-center justify-center bg-gray-900"
        >
          <div class="w-28 h-28 rounded-full bg-gradient-to-br from-green-400 to-green-600 text-white flex items-center justify-center mb-4 shadow-2xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-white mb-1">
            {{ isCallOutgoing ? 'جارٍ الاتصال...' : callerName || 'في مكالمة' }}
          </h2>
          <p class="text-gray-400 text-lg font-mono">
            {{ formatDuration(callDuration) }}
          </p>
        </div>

        <!-- Top Overlay: caller info -->
        <div class="absolute top-0 inset-x-0 p-6 bg-gradient-to-b from-black/60 to-transparent flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur text-white flex items-center justify-center font-bold text-lg">
              {{ (callerName || 'U')[0].toUpperCase() }}
            </div>
            <div>
              <h2 class="text-lg font-bold text-white">{{ callerName || 'في مكالمة' }}</h2>
              <p class="text-gray-300 text-sm font-mono">{{ formatDuration(callDuration) }}</p>
            </div>
          </div>
          <span
            class="px-3 py-1 rounded-full bg-white/20 backdrop-blur text-white text-xs font-bold"
            v-if="isCallOutgoing"
          >
            جارٍ الاتصال...
          </span>
        </div>

        <!-- Local Video PiP -->
        <div class="absolute top-4 left-4 w-32 h-48 sm:w-40 sm:h-56 rounded-2xl overflow-hidden shadow-2xl border-2 border-white/30 bg-black z-10">
          <video
            v-if="isCameraEnabled && localStream"
            ref="localVideoRef"
            autoplay
            playsinline
            muted
            class="w-full h-full object-cover"
          ></video>
          <div
            v-else
            class="w-full h-full flex flex-col items-center justify-center bg-gray-800 text-white/60"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M15.536 8.464a5 5 0 010 7.072M13 7a4.95 4.95 0 011.464 3.536M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            </svg>
            <span class="text-[10px] font-medium">الكاميرا متوقفة</span>
          </div>
        </div>

        <!-- Bottom Controls -->
        <div class="absolute bottom-8 inset-x-0 flex justify-center">
          <div class="flex items-center gap-6 bg-black/40 backdrop-blur px-8 py-4 rounded-full shadow-2xl">
            <!-- Toggle Mic -->
            <button
              @click="emit('toggleMic')"
              class="w-14 h-14 rounded-full flex items-center justify-center transition"
              :class="isMicMuted
                ? 'bg-red-500 text-white hover:bg-red-600'
                : 'bg-white/15 text-white hover:bg-white/25'"
              :title="isMicMuted ? 'إلغاء كتم الميكروفون' : 'كتم الميكروفون'"
            >
              <svg v-if="!isMicMuted" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
              </svg>
            </button>

            <!-- Toggle Camera -->
            <button
              @click="emit('toggleCamera')"
              class="w-14 h-14 rounded-full flex items-center justify-center transition"
              :class="isCameraEnabled
                ? 'bg-white/15 text-white hover:bg-white/25'
                : 'bg-red-500 text-white hover:bg-red-600'"
              :title="isCameraEnabled ? 'إيقاف الكاميرا' : 'تشغيل الكاميرا'"
            >
              <svg v-if="isCameraEnabled" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
              </svg>
            </button>

            <!-- End Call -->
            <button
              @click="emit('endCall')"
              class="w-16 h-16 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 transition shadow-xl"
              title="إنهاء المكالمة"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.516l2.257-1.13a1 1 0 00.502-1.21L9.228 3.684A1 1 0 008.28 3H5z" />
              </svg>
            </button>

            <!-- Toggle Speaker -->
            <button
              @click="emit('toggleSpeaker')"
              class="w-14 h-14 rounded-full flex items-center justify-center transition"
              :class="isSpeakerMuted
                ? 'bg-red-500 text-white hover:bg-red-600'
                : 'bg-white/15 text-white hover:bg-white/25'"
              :title="isSpeakerMuted ? 'تشغيل السماعة' : 'كتم السماعة'"
            >
              <svg v-if="!isSpeakerMuted" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
              </svg>
            </button>
          </div>
        </div>
      </template>

      <!-- ===================== AUDIO CALL LAYOUT ===================== -->
      <template v-else>
        <!-- Top Section: Call Info -->
        <div class="text-center mb-12">
          <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-green-400 to-green-600 text-white flex items-center justify-center mb-4 shadow-2xl">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-white mb-1">
            {{ isCallOutgoing ? 'جارٍ الاتصال...' : callerName || 'في مكالمة' }}
          </h2>
          <p class="text-gray-400 text-lg font-mono">
            {{ formatDuration(callDuration) }}
          </p>
        </div>

        <!-- Bottom Section: Controls -->
        <div class="flex items-center gap-6">
          <!-- Toggle Mic -->
          <button
            @click="emit('toggleMic')"
            class="w-16 h-16 rounded-full flex items-center justify-center transition shadow-lg"
            :class="isMicMuted
              ? 'bg-red-500 text-white hover:bg-red-600'
              : 'bg-white/10 text-white hover:bg-white/20'"
            :title="isMicMuted ? 'إلغاء كتم الميكروفون' : 'كتم الميكروفون'"
          >
            <svg v-if="!isMicMuted" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
            </svg>
          </button>

          <!-- End Call -->
          <button
            @click="emit('endCall')"
            class="w-20 h-20 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 transition shadow-xl"
            title="إنهاء المكالمة"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.516l2.257-1.13a1 1 0 00.502-1.21L9.228 3.684A1 1 0 008.28 3H5z" />
            </svg>
          </button>

          <!-- Toggle Speaker -->
          <button
            @click="emit('toggleSpeaker')"
            class="w-16 h-16 rounded-full flex items-center justify-center transition shadow-lg"
            :class="isSpeakerMuted
              ? 'bg-red-500 text-white hover:bg-red-600'
              : 'bg-white/10 text-white hover:bg-white/20'"
            :title="isSpeakerMuted ? 'تشغيل السماعة' : 'كتم السماعة'"
          >
            <svg v-if="!isSpeakerMuted" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
            </svg>
          </button>
        </div>
      </template>

      <!-- Hidden Audio Elements (both layouts) -->
      <audio ref="localAudioRef" autoplay muted playsinline class="hidden" />
      <audio ref="remoteAudioRef" autoplay playsinline class="hidden" />
    </div>
  </Teleport>
</template>

<style scoped>
@keyframes bounceIn {
  0% { transform: scale(0.3); opacity: 0; }
  50% { transform: scale(1.05); }
  70% { transform: scale(0.9); }
  100% { transform: scale(1); opacity: 1; }
}
.animate-bounce-in {
  animation: bounceIn 0.5s ease-out;
}
</style>

