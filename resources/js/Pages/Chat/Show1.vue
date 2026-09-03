<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useForm, usePage, Head, router } from '@inertiajs/vue3'
import axios from 'axios'

import ChatLayout from '@/Layouts/ChatLayout.vue'
import CallPopup from '@/Components/CallPopup.vue'
import ChatHeader from '@/Components/Chat/ChatHeader.vue'
import ChatMessageList from '@/Components/Chat/ChatMessageList.vue'
import ChatInputForm from '@/Components/Chat/ChatInputForm.vue'
import { useWebRTC } from '@/composables/useWebRTC'

const props = defineProps({
  conversations: Array,
  conversation: Object,
  messages: Array,
})

const page = usePage()
const currentUserId = page.props.auth.user.id
const localMessages = ref([...props.messages])

const messageListRef = ref(null)
const chatInputRef = ref(null)
const selectedFilePreview = ref(null)

// --- MESSAGE EDITING STATE & HANDLERS ---
const editingMessageId = ref(null)
const editForm = useForm({
  body: '',
})

const handleEditMessage = (message) => {
  editingMessageId.value = message.id
  editForm.body = message.body
}

const cancelEdit = () => {
  editingMessageId.value = null
  editForm.reset()
}

const updateMessage = (messageId) => {
  if (!editForm.body.trim() || editForm.processing) return

  axios.patch(route('chat.messages.update', messageId), {
    body: editForm.body
  }).then((response) => {
    const updatedMsg = response.data
    const index = localMessages.value.findIndex(m => m.id === messageId)
    if (index !== -1) {
      localMessages.value[index] = { ...localMessages.value[index], ...updatedMsg }
    }
    cancelEdit()
  }).catch((error) => {
    console.error('Failed to update message:', error)
  })
}

// --- VOICE MESSAGE HANDLER ---
const sendVoiceMessage = (audioFile) => {
  if (!audioFile) return

  const formData = new FormData()
  formData.append('file', audioFile)

  router.post(route('chat.messages.store', props.conversation.id), formData, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: (response) => {
      const latestMessages = response.props?.messages || []
      if (latestMessages.length > 0) {
        localMessages.value = [...latestMessages]
      }
      scrollToBottom()
    },
    onError: (errors) => {
      console.error('Voice Note Upload Error:', errors)
    }
  })
}

// Typing Indicator State & Handlers
const isPartnerTyping = ref(false)
let typingTimeout = null

const sendTypingEvent = () => {
  if (!props.conversation?.id) return
  window.Echo.private(`chat.${props.conversation.id}`)
    .whisper('typing', { user_id: currentUserId })
}

const listenForTyping = () => {
  if (!props.conversation?.id) return
  window.Echo.private(`chat.${props.conversation.id}`)
    .listenForWhisper('typing', (e) => {
      if (e.user_id !== currentUserId) {
        isPartnerTyping.value = true
        clearTimeout(typingTimeout)
        typingTimeout = setTimeout(() => {
          isPartnerTyping.value = false
        }, 2500)
      }
    })
}

// AI Agent Simulation State
const isSimulating = ref(false)
const simulationTopic = ref('Product Purchase Negotiation')

// Message Form Submission
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
  handleSignal,
  startCall,
  acceptCall,
  rejectCall,
  endCall,
  toggleMic,
  toggleSpeaker,
  toggleCamera,
} = useWebRTC(props.conversation.id)

// Handle File Selection
const onFileSelected = (e) => {
  const file = e.target.files[0]
  if (!file) return

  form.file = file
  if (file.type.startsWith('image/')) {
    selectedFilePreview.value = URL.createObjectURL(file)
  } else {
    selectedFilePreview.value = file.name
  }
}

// Clear Selected File
const clearSelectedFile = () => {
  form.file = null
  selectedFilePreview.value = null
  if (chatInputRef.value?.fileInput) {
    chatInputRef.value.fileInput.value = ''
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    const el = messageListRef.value?.messagesContainer
    if (el) el.scrollTop = el.scrollHeight
  })
}

const sendMessage = () => {
  if ((!form.body.trim() && !form.file) || form.processing) return

  form.post(route('chat.messages.store', props.conversation.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: (response) => {
      const latestMessages = response.props?.messages || []
      if (latestMessages.length > 0) {
        localMessages.value = [...latestMessages]
      } else {
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

// Trigger AI Agent Simulation
const triggerAiSimulation = async () => {
  if (isSimulating.value) return
  isSimulating.value = true

  try {
    await axios.post('/simulation/start', {
      topic: simulationTopic.value,
      conversation_id: props.conversation.id,
      receiver_id: props.conversation.other_user?.id || currentUserId
    })
  } catch (error) {
    console.error('Error starting simulation:', error)
    isSimulating.value = false
  }
}

watch(() => props.messages, (newMessages) => {
  if (newMessages && newMessages.length > 0) {
    localMessages.value = [...newMessages]
    scrollToBottom()
  }
}, { deep: true })

let echoChannel = null

onMounted(() => {
  scrollToBottom()
  listenForTyping()

  const channelName = `chat.${props.conversation.id}`
  echoChannel = window.Echo.private(channelName)

  echoChannel.listen('.message.sent', (e) => {
    const message = e.message
    if (!localMessages.value.some(m => m.id === message.id)) {
      localMessages.value.push(message)
      scrollToBottom()
    }
  })

  // Real-time edited message handler
  echoChannel.listen('.message.updated', (e) => {
    const message = e.message
    const index = localMessages.value.findIndex(m => m.id === message.id)
    if (index !== -1) {
      localMessages.value[index] = { ...localMessages.value[index], ...message }
    }
  })

  echoChannel.listen('.call.event', (e) => {
    if (e.caller?.id !== currentUserId) {
      handleSignal(e)
    }
  })

  echoChannel.listen('.simulation.message', (e) => {
    const messageData = e.message
    const incomingMessage = typeof messageData === 'object' ? {
      ...messageData,
      is_ai_agent: true,
      speaker_name: e.speaker || messageData.speaker_name || 'AI Assistant'
    } : {
      id: 'sim-' + Date.now() + Math.random(),
      body: e.message,
      is_ai_agent: true,
      speaker_name: e.speaker || 'AI Assistant',
      sender_id: null,
      conversation_id: props.conversation.id,
      created_at: new Date().toISOString()
    }

    if (!localMessages.value.some(m => m.id === incomingMessage.id)) {
      localMessages.value.push(incomingMessage)
      scrollToBottom()
    }

    isSimulating.value = false
  })
})

onUnmounted(() => {
  clearTimeout(typingTimeout)
  if (echoChannel) {
    const channelName = `chat.${props.conversation.id}`
    window.Echo.leave(channelName)
    echoChannel = null
  }
})
</script>

<template>
  <Head title="Chat" />
  <ChatLayout :conversations="conversations" :activeConversationId="conversation.id">
    <div class="flex flex-col flex-1 min-h-0 bg-gray-50">
      <ChatHeader
        :conversation="conversation"
        :isSimulating="isSimulating"
        :isCallActive="isCallActive"
        @triggerSimulation="triggerAiSimulation"
        @startCall="startCall"
      />

      <ChatMessageList
        ref="messageListRef"
        :messages="localMessages"
        :currentUserId="currentUserId"
        :editingMessageId="editingMessageId"
        :editForm="editForm"
        @startEdit="handleEditMessage"
        @cancelEdit="cancelEdit"
        @saveEdit="updateMessage"
      />

      <ChatInputForm
        ref="chatInputRef"
        :form="form"
        :selectedFilePreview="selectedFilePreview"
        :isPartnerTyping="isPartnerTyping"
        @submit="sendMessage"
        @fileSelected="onFileSelected"
        @clearFile="clearSelectedFile"
        @typing="sendTypingEvent"
        @sendAudio="sendVoiceMessage"
      />
    </div>
  </ChatLayout>

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
