<script setup>
import { ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import NotificationDropdown from './NotificationDropdown.vue'

const page = usePage()

const isNotificationsOpen = ref(false)
const notifications = ref([...(page.props.notifications?.items || [])])
const unreadCount = ref(page.props.notifications?.unread_count || 0)

const showToast = (payload) => {
  if (!('Notification' in window)) return

  const senderName = payload.sender_name || payload.sender?.name || 'New Message'
  const preview = payload.preview || payload.body || 'You have received a new message'

  const show = () => {
    if (Notification.permission === 'granted') {
      new Notification(senderName, {
        body: preview,
        tag: 'chat-notification',
      })
    }
  }

  if (Notification.permission === 'granted') {
    show()
  } else if (Notification.permission !== 'denied') {
    Notification.requestPermission().then((permission) => {
      if (permission === 'granted') show()
    })
  }
}

onMounted(() => {
  const authUser = page.props.auth.user
  if (!authUser?.id || !window.Echo) return

  const channelName = `App.Models.User.${authUser.id}`

  // Listen for real-time notification events across the navbar
  window.Echo.private(channelName).notification((notification) => {
    const data = notification

    if (!notifications.value.some((n) => n.id === notification.id)) {
      notifications.value.unshift({
        id: notification.id,
        type: notification.type,
        data: {
          sender_name: data.sender_name || data.sender?.name || 'User',
          preview: data.preview || data.body || 'New message',
          conversation_id: data.conversation_id,
        },
        read_at: null,
        created_at: new Date().toISOString(),
      })
    }

    unreadCount.value++
    showToast(data)
  })
})

const handleLogout = () => {
  router.visit(route('logout'), { method: 'post' })
}
</script>

<template>
  <header class="bg-white border-b shadow-sm flex-shrink-0 z-10">
    <div class="flex items-center justify-between px-4 py-2">
      <!-- Logo / App Name -->
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-blue-400 text-white flex items-center justify-center font-bold text-sm">
          C
        </div>
        <span class="font-bold text-gray-800 text-sm hidden sm:inline">Nexus</span>
      </div>

      <!-- Right Controls: Notifications & Profile -->
      <div class="flex items-center gap-2 relative">
        <NotificationDropdown
          :is-open="isNotificationsOpen"
          v-model:notifications="notifications"
          v-model:unread-count="unreadCount"
          @toggle="isNotificationsOpen = $event"
        />

        <!-- Logout Button -->
        <div class="relative">
          <button
            @click="handleLogout"
            class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-full transition"
            title="Logout"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
