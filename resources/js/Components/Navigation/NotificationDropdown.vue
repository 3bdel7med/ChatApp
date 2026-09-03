<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  notifications: {
    type: Array,
    default: () => [],
  },
  unreadCount: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits([
  'toggle',
  'update:notifications',
  'update:unreadCount',
])

const sortedNotifications = computed(() =>
  [...props.notifications].sort(
    (a, b) => new Date(b.created_at) - new Date(a.created_at)
  )
)

const loadNotifications = async () => {
  try {
    const { data } = await axios.get(route('notifications.index'))
    emit('update:notifications', data.notifications || [])
    emit('update:unreadCount', data.unread_count || 0)
  } catch (error) {
    console.error('Error loading notifications:', error)
  }
}

const toggleDropdown = () => {
  const nextState = !props.isOpen
  emit('toggle', nextState)

  if (nextState && props.notifications.length === 0) {
    loadNotifications()
  }
}

const markAsRead = async (notification) => {
  try {
    await axios.post(route('notifications.read', notification.id))

    const updated = props.notifications.map((n) =>
      n.id === notification.id ? { ...n, read_at: new Date().toISOString() } : n
    )
    emit('update:notifications', updated)

    if (props.unreadCount > 0) {
      emit('update:unreadCount', props.unreadCount - 1)
    }
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}

const navigateToConversation = async (notification) => {
  await markAsRead(notification)
  emit('toggle', false)

  const conversationId = notification.data?.conversation_id
  if (conversationId) {
    router.visit(route('chat.show', conversationId))
  }
}

const markAllAsRead = async () => {
  try {
    await axios.post(route('notifications.read-all'))

    const updated = props.notifications.map((n) => ({
      ...n,
      read_at: n.read_at || new Date().toISOString(),
    }))

    emit('update:notifications', updated)
    emit('update:unreadCount', 0)
  } catch (error) {
    console.error('Error marking all notifications as read:', error)
  }
}
</script>

<template>
  <div class="relative">
    <!-- Notifications Icon Trigger -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-full transition"
      title="Notifications"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <span
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center border-2 border-white"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown Panel -->
    <div
      v-if="isOpen"
      class="absolute top-14 right-0 w-80 bg-white rounded-2xl shadow-2xl border z-50 overflow-hidden"
    >
      <div class="p-3 border-b bg-gray-50 flex items-center justify-between">
        <h3 class="font-bold text-gray-800 text-sm">Notifications</h3>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-xs text-blue-600 hover:text-blue-800 font-medium"
        >
          Mark all as read
        </button>
      </div>

      <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
        <div v-if="sortedNotifications.length === 0" class="p-6 text-center text-sm text-gray-400">
          No notifications yet.
        </div>

        <button
          v-for="notification in sortedNotifications"
          :key="notification.id"
          @click="navigateToConversation(notification)"
          class="w-full text-left p-3 hover:bg-blue-50 transition flex items-start gap-3"
          :class="{ 'bg-blue-50/50': !notification.read_at }"
        >
          <div
            class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
            :class="notification.read_at ? 'bg-gray-100 text-gray-500' : 'bg-blue-100 text-blue-700'"
          >
            {{ (notification.data?.sender_name || 'U')[0].toUpperCase() }}
          </div>

          <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-800 truncate">
              <span class="font-semibold">{{ notification.data?.sender_name || 'User' }}</span>
            </p>
            <p class="text-xs text-gray-500 truncate">{{ notification.data?.preview || 'New message' }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">
              {{ new Date(notification.created_at).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' }) }}
            </p>
          </div>

          <span
            v-if="!notification.read_at"
            class="w-2 h-2 rounded-full bg-blue-600 flex-shrink-0 mt-1.5"
          ></span>
        </button>
      </div>
    </div>
  </div>
</template>
