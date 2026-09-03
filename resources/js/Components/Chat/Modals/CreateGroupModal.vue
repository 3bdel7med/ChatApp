<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const emit = defineEmits(['close'])

const groupName = ref('')
const selectedUsers = ref([])
const searchQuery = ref('')
const searchResults = ref([])

watch(searchQuery, async (newQuery) => {
  if (newQuery.trim().length < 2) {
    searchResults.value = []
    return
  }

  try {
    const response = await axios.get(route('chat.users.search'), { params: { query: newQuery } })
    searchResults.value = response.data
  } catch (error) {
    console.error('Error searching users for group:', error)
  }
})

const createGroup = async () => {
  if (!groupName.value || selectedUsers.value.length === 0) return

  try {
    const response = await axios.post(route('chat.group.store'), {
      name: groupName.value,
      user_ids: selectedUsers.value,
    })

    groupName.value = ''
    selectedUsers.value = []
    emit('close')
    router.visit(route('chat.show', response.data.id))
  } catch (error) {
    console.error('Error creating group:', error)
  }
}
</script>

<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold text-gray-800">Create Group Chat</h2>
        <button @click="emit('close')" class="text-gray-400 hover:text-gray-600">✕</button>
      </div>

      <input
        v-model="groupName"
        type="text"
        placeholder="Group Name"
        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none mb-4 text-sm"
      />

      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search members..."
        class="w-full px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 outline-none mb-2 text-sm"
      />

      <div class="max-h-40 overflow-y-auto divide-y border rounded-xl p-2 mb-4">
        <label
          v-for="user in searchResults"
          :key="user.id"
          class="flex items-center gap-3 p-2 hover:bg-gray-50 cursor-pointer"
        >
          <input
            type="checkbox"
            :value="user.id"
            v-model="selectedUsers"
            class="rounded text-blue-600 focus:ring-blue-500"
          />
          <span class="text-sm text-gray-700">{{ user.name }}</span>
        </label>
      </div>

      <button
        @click="createGroup"
        :disabled="!groupName || selectedUsers.length === 0"
        class="w-full bg-blue-600 text-white py-2 rounded-xl font-semibold disabled:opacity-50 hover:bg-blue-700 transition text-sm"
      >
        Create Group
      </button>
    </div>
  </div>
</template>
