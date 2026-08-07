<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import ChatLayout from '@/Layouts/ChatLayout.vue'
import CallPopup from '@/Components/CallPopup.vue'
import { useWebRTC } from '@/composables/useWebRTC'

const props = defineProps({
  conversations: Array,
  conversation: Object,
  messages: Array,
})

const page = usePage()
const currentUserId = page.props.auth.user.id
const localMessages = ref([...props.messages])
const messagesContainer = ref(null)

const fileInput = ref(null)
const selectedFilePreview = ref(null)

// Form الإرسال مع المرفق
const form = useForm({
  body: '',
  file: null,
})

// WebRTC Voice/Video Call
const {
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
} = useWebRTC(props.conversation.id)

// اختيار ملف
const onFileSelected = (e) => {
  const file = e.target.files[0]
  if (!file) return

  form.file = file

  // عمل معاينة بسيطة لو الملف صورة
  if (file.type.startsWith('image/')) {
    selectedFilePreview.value = URL.createObjectURL(file)
  } else {
    selectedFilePreview.value = file.name
  }
}

// إلغاء تحديد الملف
const clearSelectedFile = () => {
  form.file = null
  selectedFilePreview.value = null
  if (fileInput.value) fileInput.value.value = ''
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const sendMessage = () => {
  if ((!form.body.trim() && !form.file) || form.processing) return

  form.post(route('chat.messages.store', props.conversation.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: (response) => {
      // Get the latest messages from the response to include the new one
      const latestMessages = response.props?.messages || []
      if (latestMessages.length > 0) {
        localMessages.value = [...latestMessages]
      } else {
        // Fallback: use flash message
        const flashMessage = response.props?.flash?.message
        if (flashMessage && !localMessages.value.some(m => m.id === flashMessage.id)) {
          localMessages.value.push(flashMessage)
        }
      }
      form.reset('body', 'file')
      clearSelectedFile()
      scrollToBottom()
    },
  })
}

// Auto-scroll to bottom on initial load
onMounted(() => {
  scrollToBottom()
})

// Watch for new messages from props (when page reloads or re-renders)
watch(() => props.messages, (newMessages) => {
  if (newMessages && newMessages.length > 0) {
    localMessages.value = [...newMessages]
    scrollToBottom()
  }
}, { deep: true })

// Echo listeners for messages + calls
let echoChannel = null

onMounted(() => {
  scrollToBottom()

  const channelName = `chat.${props.conversation.id}`
  echoChannel = window.Echo.private(channelName)

  // Listen for new messages
  echoChannel.listen('.message.sent', (e) => {
    const message = e.message
    if (!localMessages.value.some(m => m.id === message.id)) {
      localMessages.value.push(message)
      scrollToBottom()
    }
  })

// Initialize call listener (listens for .call.event on the same channel)
  if (echoChannel) {
    registerCallListeners(echoChannel)
  }
})

onUnmounted(() => {
  if (echoChannel) {
    const channelName = `chat.${props.conversation.id}`
    window.Echo.leave(channelName)
    echoChannel = null
  }
})
</script>

<template>
  <ChatLayout :conversations="conversations" :activeConversationId="conversation.id">
    <div class="flex flex-col flex-1 min-h-0 bg-gray-50">

      <!-- Header الشات -->
      <div class="p-4 bg-white border-b flex items-center justify-between shadow-sm flex-shrink-0">
        <div class="flex items-center gap-3">
          <template v-if="conversation.type === 'group'">
            <div class="w-10 h-10 rounded-full bg-green-100 text-green-700 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <h2 class="font-bold text-gray-800 text-sm">{{ conversation.name }}</h2>
              <p class="text-[11px] text-gray-500">
                {{ conversation.participants?.length || 0 }} عضو
              </p>
            </div>
          </template>
          <template v-else>
            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
              {{ (conversation.other_user?.name || 'U')[0].toUpperCase() }}
            </div>
            <h2 class="font-bold text-gray-800 text-sm">{{ conversation.other_user?.name }}</h2>
          </template>
        </div>

        <!-- Call Buttons -->
        <div v-if="conversation.type === 'direct'" class="flex items-center gap-1">
          <!-- Voice Call Button -->
          <button
            @click="startCall(false)"
            :disabled="isCallActive"
            class="p-2 text-green-600 hover:text-white hover:bg-green-600 rounded-full transition disabled:opacity-40 disabled:cursor-not-allowed"
            :title="isCallActive ? 'في مكالمة حالياً' : 'اتصال صوتي'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          </button>

          <!-- Video Call Button -->
          <button
            @click="startCall(true)"
            :disabled="isCallActive"
            class="p-2 text-blue-600 hover:text-white hover:bg-blue-600 rounded-full transition disabled:opacity-40 disabled:cursor-not-allowed"
            :title="isCallActive ? 'في مكالمة حالياً' : 'مكالمة فيديو'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- منطقة عرض الرسائل -->
      <div ref="messagesContainer" class="flex-1 min-h-0 p-4 overflow-y-auto space-y-4">
        <div
          v-for="msg in localMessages"
          :key="msg.id"
          :class="['flex', msg.sender_id === currentUserId ? 'justify-end' : 'justify-start']"
        >
          <div
            :class="[
              'max-w-xs md:max-w-md px-4 py-2 rounded-2xl text-sm shadow-sm',
              msg.sender_id === currentUserId
                ? 'bg-blue-600 text-white rounded-br-none'
                : 'bg-white text-gray-800 border rounded-bl-none'
            ]"
          >
            <!-- عرض الصورة إن وجدت -->
            <div v-if="msg.file_path && msg.file_type === 'image'" class="mb-2">
              <a :href="msg.file_path" target="_blank">
                <img :src="msg.file_path" class="rounded-lg max-h-60 w-full object-cover border" />
              </a>
            </div>

            <!-- عرض الملف العادي/المستند إن وجد -->
            <div v-else-if="msg.file_path" class="mb-2">
              <a
                :href="msg.file_path"
                target="_blank"
                class="flex items-center gap-2 p-2 rounded-lg bg-black/10 hover:bg-black/20 transition"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-xs truncate font-medium underline">{{ msg.file_name || 'تحميل الملف' }}</span>
              </a>
            </div>

            <!-- نص الرسالة -->
            <p v-if="msg.body">{{ msg.body }}</p>

            <span :class="['block text-[10px] mt-1 text-left opacity-75', msg.sender_id === currentUserId ? 'text-blue-100' : 'text-gray-400']">
              {{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
            </span>
          </div>
        </div>
      </div>

      <!-- شريط معاينة الملف قبل الإرسال -->
      <div v-if="selectedFilePreview" class="px-4 py-2 bg-gray-100 border-t flex items-center justify-between text-xs flex-shrink-0">
        <div class="flex items-center gap-2 truncate">
          <span class="font-bold text-gray-700">الملف المرفق:</span>
          <span class="text-gray-600 truncate max-w-xs">{{ form.file?.name }}</span>
        </div>
        <button @click="clearSelectedFile" class="text-red-500 hover:text-red-700 font-bold">&times; إلغاء</button>
      </div>

      <!-- حقل إرسال الرسالة والملف -->
      <div class="p-4 bg-white border-t flex-shrink-0">
        <form @submit.prevent="sendMessage" class="flex items-center gap-2">

          <!-- Input مخفي للملفات -->
          <input
            type="file"
            ref="fileInput"
            @change="onFileSelected"
            class="hidden"
            accept="image/*,.pdf,.doc,.docx,.zip"
          />

          <!-- زرار أرفق ملف (Paperclip Icon) -->
          <button
            type="button"
            @click="fileInput.click()"
            class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-full transition"
            title="إرفاق ملف أو صورة"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
          </button>

          <input
            v-model="form.body"
            type="text"
            placeholder="اكتب رسالتك هنا..."
            class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:border-blue-500"
            :disabled="form.processing"
          />

          <button
            type="submit"
            :disabled="form.processing || (!form.body.trim() && !form.file)"
            class="bg-blue-600 text-white px-5 py-2 rounded-full text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition"
          >
            إرسال
          </button>
        </form>
      </div>

    </div>
  </ChatLayout>

  <!-- Voice/Video Call Popup -->
  <CallPopup
    :isCallActive="isCallActive"
    :isCallIncoming="isCallIncoming"
    :isCallOutgoing="isCallOutgoing"
    :callDuration="callDuration"
    :callerName="callerName"
    :isMicMuted="isMicMuted"
    :isSpeakerMuted="isSpeakerMuted"
    :isVideoCall="isVideoCall"
    :isCameraEnabled="isCameraEnabled"
    :remoteStream="remoteStream"
    :localStream="localStream"
    :formatDuration="formatDuration"
    @accept="acceptCall"
    @reject="rejectCall"
    @endCall="endCall"
    @toggleMic="toggleMic"
    @toggleSpeaker="toggleSpeaker"
    @toggleCamera="toggleCamera"
  />
</template>

