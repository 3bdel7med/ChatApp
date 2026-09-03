<script setup>
import ConversationItem from './ConversationItem.vue'

defineProps({
  conversations: {
    type: Array,
    default: () => [],
  },
  activeConversationId: {
    type: Number,
    default: null,
  },
  authUser: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['open-new-chat', 'open-group-chat'])
</script>

<template>
  <aside
    class="w-full md:w-80 lg:w-96 bg-white border-r flex flex-col flex-shrink-0 transition-all overflow-hidden"
    :class="{ 'hidden md:flex': activeConversationId }"
  >
    <!-- Sidebar Header & Action Buttons -->
    <div class="p-4 bg-gray-50 border-b flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg">
          {{ authUser.name?.[0]?.toUpperCase() ?? 'U' }}
        </div>
        <div>
          <h1 class="font-bold text-gray-800 text-sm">{{ authUser.name }}</h1>
          <p class="text-xs text-gray-500">My Account</p>
        </div>
      </div>

      <div class="flex items-center gap-1">
        <button
          @click="emit('open-new-chat')"
          class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-200 rounded-full transition"
          title="New Chat"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </button>

        <button
          @click="emit('open-group-chat')"
          class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-200 rounded-full transition"
          title="New Group Chat"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Conversation List -->
    <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
      <div v-if="!conversations || conversations.length === 0" class="p-6 text-center text-gray-400 text-sm">
        No conversations available yet.
      </div>

      <ConversationItem
        v-for="conv in conversations"
        :key="conv.id"
        :conv="conv"
        :is-active="conv.id === activeConversationId"
      />
    </div>
  </aside>
</template>
