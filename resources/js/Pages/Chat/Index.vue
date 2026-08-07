<script setup>
import ChatLayout from '@/Layouts/ChatLayout.vue'

defineProps({
  conversations: Array,
})
</script>

<template>
  <ChatLayout :conversations="conversations">
    <div class="flex-1 flex flex-col items-center justify-center p-6 text-center bg-gray-50">

      <!-- أيقونة الترحيب -->
      <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4 shadow-inner">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 8 9 8z" />
        </svg>
      </div>

      <h2 class="text-xl font-bold text-gray-800 mb-2">hello on your chats</h2>
      <p class="text-sm text-gray-500 max-w-sm leading-relaxed">
        select a chat to start messaging.
      </p>

    </div>
  </ChatLayout>
</template>
