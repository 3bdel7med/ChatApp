<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

defineProps({
  conversations: Array,
  activeConversationId: Number,
})


const page = usePage()
const authUser = page.props.auth.user

// حالة الـ Modal والبحث
const isModalOpen = ref(false)
const searchQuery = ref('')
const searchResults = ref([])
const isLoading = ref(false)

// Notifications
const isNotificationsOpen = ref(false)
const notifications = ref([...(page.props.notifications?.items || [])])
const unreadCount = ref(page.props.notifications?.unread_count || 0)

const sortedNotifications = computed(() =>
  [...notifications.value].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
)

const loadNotifications = async () => {
  try {
    const { data } = await axios.get(route('notifications.index'))
    notifications.value = data.notifications || []
    unreadCount.value = data.unread_count || 0
  } catch (error) {
    console.error('Error loading notifications:', error)
  }
}

const openNotifications = () => {
  isNotificationsOpen.value = !isNotificationsOpen.value
  if (isNotificationsOpen.value && notifications.value.length === 0) {
    loadNotifications()
  }
}

const markAsRead = async (notification) => {
  try {
    await axios.post(route('notifications.read', notification.id))
    notification.read_at = new Date().toISOString()
    if (unreadCount.value > 0) unreadCount.value--
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}

const navigateToConversation = async (notification) => {
  await markAsRead(notification)
  isNotificationsOpen.value = false

  const conversationId = notification.data?.conversation_id
  if (conversationId) {
    router.visit(route('chat.show', conversationId))
  }
}

const markAllAsRead = async () => {
  try {
    await axios.post(route('notifications.read-all'))
    notifications.value.forEach((n) => (n.read_at = new Date().toISOString()))
    unreadCount.value = 0
  } catch (error) {
    console.error('Error marking all notifications as read:', error)
  }
}

// Browser toast on new message
const showToast = (payload) => {
  if (!('Notification' in window)) return

  const senderName = payload.sender_name || payload.sender?.name || 'رسالة جديدة'
  const preview = payload.preview || payload.body || 'لديك رسالة جديدة'

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

// Realtime listener for new chat messages
let notificationChannel = null

onMounted(() => {
  const channelName = `App.Models.User.${authUser.id}`
  notificationChannel = window.Echo.private(channelName)

  notificationChannel.notification((notification) => {
    // The payload is flattened by Laravel's broadcast notification event.
    // Echo merges the broadcast data directly into the notification object.
    const data = notification

    // Add to list (dedupe by id)
    if (!notifications.value.some((n) => n.id === notification.id)) {
      notifications.value.unshift({
        id: notification.id,
        type: notification.type,
        data: {
          sender_name: data.sender_name || data.sender?.name || 'مستخدم',
          preview: data.preview || data.body || 'رسالة جديدة',
          conversation_id: data.conversation_id,
        },
        read_at: null,
        created_at: new Date().toISOString(),
      })
    }

    unreadCount.value++

    // Toast notification
    showToast(data)
  })
})

onUnmounted(() => {
  if (notificationChannel) {
    window.Echo.leave(`App.Models.User.${authUser.id}`)
    notificationChannel = null
  }
})

// البحث عن المستخدمين مع Debounce بسيط
let timeout = null
watch(searchQuery, (newQuery) => {
  clearTimeout(timeout)
  if (newQuery.trim().length < 2) {
    searchResults.value = []
    return
  }

  isLoading.value = true
  timeout = setTimeout(async () => {
    try {
      const response = await axios.get(route('chat.users.search'), {
        params: { query: newQuery }
      })
      searchResults.value = response.data
    } catch (error) {
      console.error('Error searching users:', error)
    } finally {
      isLoading.value = false
    }
  }, 300)
})


const startChat = (userId) => {
  isModalOpen.value = false
  searchQuery.value = ''
  searchResults.value = []

  router.post(route('chat.start'), {
    receiver_id: userId
  })
}

// === Group Chat Logic ===
const isGroupModalOpen = ref(false)
const availableUsers = ref([])
const groupForm = ref({
  name: '',
  user_ids: [],
  processing: false,
})

// Load all users for group member selection
const loadAvailableUsers = async () => {
  try {
    const response = await axios.get(route('chat.users.list'))
    availableUsers.value = response.data
  } catch (error) {
    console.error('Error loading users:', error)
  }
}

const createGroup = () => {
  if (!groupForm.value.name || groupForm.value.user_ids.length === 0 || groupForm.value.processing) return

  groupForm.value.processing = true

  router.post(route('chat.group.create'), {
    name: groupForm.value.name,
    user_ids: groupForm.value.user_ids,
  }, {
    onFinish: () => {
      isGroupModalOpen.value = false
      groupForm.value.name = ''
      groupForm.value.user_ids = []
      groupForm.value.processing = false
    },
    onError: () => {
      groupForm.value.processing = false
    }
  })
}

const openGroupModal = () => {
  loadAvailableUsers()
  isGroupModalOpen.value = true
}
</script>

<template>
  <div class="flex flex-col h-screen bg-gray-100 overflow-hidden font-sans dir-rtl">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b shadow-sm flex-shrink-0 z-10">
      <div class="flex items-center justify-between px-4 py-2">
        <!-- Logo / App Name -->
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-blue-400 text-white flex items-center justify-center font-bold text-sm">
            C
          </div>
          <span class="font-bold text-gray-800 text-sm hidden sm:inline">ChatApp</span>
        </div>




        <!-- Notifications & Profile -->
        <div class="flex items-center gap-2 relative">
          <!-- Notifications Icon -->
          <button
            @click="openNotifications"
            class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-full transition"
            title="الإشعارات"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <!-- Notification Badge (unread count) -->
            <span
              v-if="unreadCount > 0"
              class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center border-2 border-white"
            >
              {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
          </button>

          <!-- Notifications Dropdown -->
          <div
            v-if="isNotificationsOpen"
            class="absolute top-14 left-0 sm:left-auto sm:right-0 w-80 bg-white rounded-2xl shadow-2xl border z-50 overflow-hidden"
          >
            <div class="p-3 border-b bg-gray-50 flex items-center justify-between">
              <h3 class="font-bold text-gray-800 text-sm">Notifications</h3>
              <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                class="text-xs text-blue-600 hover:text-blue-800 font-medium"
              >
                  make all read
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
                class="w-full text-right p-3 hover:bg-blue-50 transition flex items-start gap-3"
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
                    <span class="font-semibold">{{ notification.data?.sender_name || 'مستخدم' }}</span>
                  </p>
                  <p class="text-xs text-gray-500 truncate">{{ notification.data?.preview || 'رسالة جديدة' }}</p>
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

          <!-- User Avatar (small) -->
          <Link
            :href="route('profile.edit')"
            class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-600 to-blue-400 text-white flex items-center justify-center font-bold text-xs cursor-pointer hover:opacity-90 transition"
          >
            {{ authUser.name[0].toUpperCase() }}
          </Link>
        </div>
      </div>
    </header>

    <!-- Main Layout (Sidebar + Content) -->
    <div class="flex flex-1 overflow-hidden">

      <!-- Sidebar -->
      <aside
        class="w-full md:w-80 lg:w-96 bg-white border-l flex flex-col flex-shrink-0 transition-all overflow-hidden"
        :class="{ 'hidden md:flex': activeConversationId }"
      >
        <!-- Header الـ Sidebar مع زرار محادثة جديدة -->
        <div class="p-4 bg-gray-50 border-b flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg">
              {{ authUser.name[0].toUpperCase() }}
            </div>
            <div>
              <h1 class="font-bold text-gray-800 text-sm">{{ authUser.name }}</h1>
              <p class="text-xs text-gray-500">my personal account</p>
            </div>
          </div>
          <!-- زرار فتح Modal اsearch and create group -->
          <div class="flex items-center gap-1">
            <button
              @click="openGroupModal"
              title="create new group"
              class="p-2 text-gray-600 hover:text-green-600 hover:bg-gray-200 rounded-full transition"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </button>
            <button
              @click="isModalOpen = true"
              title="start new chat"
              class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-200 rounded-full transition"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Conversation List -->
        <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
          <div v-if="!conversations || conversations.length === 0" class="p-6 text-center text-gray-400 text-sm">
            No conversations yet. Click (+) to start a new chat.
          </div>

          <Link
            v-for="conv in conversations"
            :key="conv.id"
            :href="route('chat.show', conv.id)"
            class="flex items-center gap-3 p-4 hover:bg-gray-50 transition relative"
            :class="{ 'bg-blue-50/70 border-r-4 border-blue-600': conv.id === activeConversationId }"
          >
            <div
              class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-base border"
              :class="conv.type === 'group' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-200 text-gray-700'"
            >
              <template v-if="conv.type === 'group'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </template>
              <template v-else>
                {{ (conv.other_user?.name || 'U')[0].toUpperCase() }}
              </template>
            </div>

            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-baseline mb-1">
                <h3 class="text-sm font-semibold text-gray-900 truncate">
                  <template v-if="conv.type === 'group'">
                    {{ conv.name }}
                  </template>
                  <template v-else>
                    {{ conv.other_user?.name }}
                  </template>
                </h3>
                <span v-if="conv.last_message" class="text-[11px] text-gray-400">
                  {{ new Date(conv.last_message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                </span>
              </div>
              <p class="text-xs text-gray-500 truncate">
                {{ conv.last_message ? conv.last_message.body : (conv.type === 'group' ? 'no message yet' : '  start conversayion now...') }}
              </p>
            </div>
          </Link>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 flex flex-col h-full bg-gray-50 overflow-hidden">
        <slot />
      </main>

    </div>

    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
      @click.self="isModalOpen = false"
    >
      <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl transition-all">

        <!-- Modal Header -->
        <div class="p-4 border-b flex justify-between items-center bg-gray-50">
          <h3 class="font-bold text-gray-800">start new chat  </h3>
          <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
        </div>

        <div class="p-4 border-b">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="search by name or email..."
            class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            autoFocus
          />
        </div>

        <!-- Search Results -->
        <div class="max-h-60 overflow-y-auto divide-y divide-gray-100 p-2">
          <div v-if="isLoading" class="p-4 text-center text-sm text-gray-400">
            searching...
          </div>

          <div v-else-if="searchQuery.trim().length >= 2 && searchResults.length === 0" class="p-4 text-center text-sm text-gray-400">
            No users found.
          </div>

          <button
            v-for="user in searchResults"
            :key="user.id"
            @click="startChat(user.id)"
            class="w-full text-right p-3 hover:bg-blue-50 rounded-xl flex items-center gap-3 transition"
          >
            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
              {{ user.name[0].toUpperCase() }}
            </div>
            <div>
              <h4 class="text-sm font-semibold text-gray-800">{{ user.name }}</h4>
              <p class="text-xs text-gray-500">{{ user.email }}</p>
            </div>
          </button>
        </div>

      </div>
    </div>

    <!-- Modal   -->
    <div
      v-if="isGroupModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
      @click.self="isGroupModalOpen = false"
    >
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-bold text-gray-800 text-lg">create new group</h3>
          <button @click="isGroupModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
        </div>

        <form @submit.prevent="createGroup" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Group Name</label>
            <input
              v-model="groupForm.name"
              type="text"
              placeholder="Example: Development Team"
              class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Select Members</label>
            <div class="max-h-48 overflow-y-auto space-y-2 border border-gray-200 rounded-xl p-3">
              <label
                v-for="user in availableUsers"
                :key="user.id"
                class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer"
              >
                <input
                  type="checkbox"
                  :value="user.id"
                  v-model="groupForm.user_ids"
                  class="rounded text-blue-600 focus:ring-blue-500"
                />
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                  {{ user.name[0].toUpperCase() }}
                </div>
                <span class="text-sm font-medium text-gray-700">{{ user.name }}</span>
              </label>
              <div v-if="availableUsers.length === 0" class="text-center text-sm text-gray-400 py-4">
                No other users available.
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button type="button" @click="isGroupModalOpen = false" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
            <button
              type="submit"
              :disabled="groupForm.processing || !groupForm.name || groupForm.user_ids.length === 0"
              class="px-5 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-700 disabled:opacity-50 transition"
            >
              {{ groupForm.processing ? 'Creating...' : 'Create Group' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>
