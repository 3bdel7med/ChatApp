<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const emit = defineEmits(['close', 'conversation-created'])

const searchQuery = ref('')
const searchResults = ref([])
const isSearching = ref(false)
const isSubmitting = ref(false)

watch(searchQuery, async (newQuery) => {
  if (newQuery.trim().length < 2) {
    searchResults.value = []
    return
  }

  isSearching.value = true

  try {
    const response = await axios.get(route('chat.users.search'), { params: { query: newQuery } })
    searchResults.value = response.data
  } catch (error) {
    console.error('Error searching users:', error)
  } finally {
    isSearching.value = false
  }
})

// ✅ Send receiver_id in the POST request body to satisfy validation
const startChat = async (userId) => {
  if (isSubmitting.value) return
  isSubmitting.value = true

  try {
    const response = await axios.post(route('chat.start'), {
      receiver_id: userId,
    })

    // Refresh conversation list in background without performing a full navigation
    router.reload({ only: ['conversations'] })

    emit('conversation-created', response.data)
    emit('close')
  } catch (error) {
    console.error('Error starting chat:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-gray-800">New Conversation</h2>
        <button @click="emit('close')" class="text-gray-400 hover:text-gray-600">✕</button>
      </div>

      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search users..."
        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none mb-4 text-sm"
      />

      <div class="max-h-60 overflow-y-auto divide-y">
        <div v-if="isSearching" class="p-4 text-center text-sm text-gray-500">
          Searching...
        </div>
        <div v-else-if="searchResults.length === 0 && searchQuery" class="p-4 text-center text-sm text-gray-500">
          No users found
        </div>

        <button
          v-for="user in searchResults"
          :key="user.id"
          :disabled="isSubmitting"
          @click="startChat(user.id)"
          class="w-full text-left p-3 hover:bg-gray-50 flex items-center gap-3 transition disabled:opacity-50"
        >
          <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold flex items-center justify-center text-xs">
            {{ user.name?.[0]?.toUpperCase() ?? 'U' }}
          </div>
          <span class="text-sm font-medium text-gray-700">{{ user.name }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
