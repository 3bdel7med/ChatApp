<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

// Sub-components
import TopNavbar from '@/Components/Navigation/TopNavbar.vue'
import ChatSidebar from '@/Components/Chat/Sidebar/ChatSidebar.vue'
import NewChatModal from '@/Components/Chat/Modals/NewChatModal.vue'
import CreateGroupModal from '@/Components/Chat/Modals/CreateGroupModal.vue'
import IncomingCallModal from '@/Components/Calls/IncomingCallModal.vue'

const props = defineProps({
  conversations: {
    type: Array,
    default: () => [],
  },
  activeConversationId: {
    type: Number,
    default: null,
  },
})

const page = usePage()
const authUser = page.props.auth.user

// Modals State
const isModalOpen = ref(false)
const isGroupModalOpen = ref(false)

// Global Call State
const incomingCall = ref(null)
let userPrivateChannel = null

// Real-time Global Call Event Listeners
onMounted(() => {
  if (!authUser?.id || !window.Echo) return

  const channelName = `App.Models.User.${authUser.id}`
  userPrivateChannel = window.Echo.private(channelName)

  // Listen for global incoming WebRTC call
  userPrivateChannel.listen('.incoming-call', (e) => {
    incomingCall.value = {
      conversation_id: e.conversation_id || e.conversationId,
      caller_name: e.caller_name || e.user?.name || 'Incoming Call...',
      caller_avatar: e.caller_avatar || null,
      signal_data: e.signal_data || e.signal,
    }
  })

  // Listen for remote end/reject events
  userPrivateChannel.listen('.call-ended', () => {
    incomingCall.value = null
  })
})

onUnmounted(() => {
  if (userPrivateChannel && authUser?.id) {
    window.Echo.leave(`App.Models.User.${authUser.id}`)
    userPrivateChannel = null
  }
})

// Call Actions
const acceptIncomingCall = () => {
  if (!incomingCall.value) return

  const convId = incomingCall.value.conversation_id
  const offerSignal = incomingCall.value.signal_data

  if (offerSignal) {
    sessionStorage.setItem(
      'pending_webrtc_offer',
      typeof offerSignal === 'string' ? offerSignal : JSON.stringify(offerSignal)
    )
  }

  incomingCall.value = null

  router.visit(route('chat.show', convId), {
    preserveState: false,
    data: { acceptCall: true },
  })
}

const rejectIncomingCall = () => {
  if (incomingCall.value?.conversation_id) {
    axios
      .post(route('chat.call.reject', incomingCall.value.conversation_id))
      .catch(() => {})
  }
  incomingCall.value = null
}
</script>

<template>
  <div class="flex flex-col h-screen bg-gray-100 overflow-hidden font-sans ltr">
    <!-- Top Navigation Bar -->
    <TopNavbar />

    <!-- Main Workspace (Sidebar + Dynamic Slot Content) -->
    <div class="flex flex-1 overflow-hidden">
      <ChatSidebar
        :conversations="conversations"
        :active-conversation-id="activeConversationId"
        :auth-user="authUser"
        @open-new-chat="isModalOpen = true"
        @open-group-chat="isGroupModalOpen = true"
      />

      <!-- Content View (e.g., active conversation or placeholder) -->
      <main class="flex-1 flex flex-col h-full bg-gray-50 overflow-hidden">
        <slot />
      </main>
    </div>

    <!-- Global Incoming Call Overlay -->
    <IncomingCallModal
      :call="incomingCall"
      @accept="acceptIncomingCall"
      @reject="rejectIncomingCall"
    />

    <!-- New Direct Chat Modal -->
    <NewChatModal
      v-if="isModalOpen"
      @close="isModalOpen = false"
    />

    <!-- New Group Chat Modal -->
    <CreateGroupModal
      v-if="isGroupModalOpen"
      @close="isGroupModalOpen = false"
    />
  </div>
</template>
