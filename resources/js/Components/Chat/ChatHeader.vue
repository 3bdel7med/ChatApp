<script setup>
defineProps({
  conversation: Object,
  isSimulating: Boolean,
  isCallActive: Boolean,
})

const emit = defineEmits(['triggerSimulation', 'startCall'])
</script>

<template>
  <div class="p-4 bg-white border-b flex items-center justify-between shadow-sm flex-shrink-0">
    <!-- Conversation Details -->
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
            {{ conversation.participants?.length || 0 }} members
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

    <!-- Action Buttons -->
    <div class="flex items-center gap-2">
      <!-- AI Simulation Button -->
      <button
        @click="emit('triggerSimulation')"
        :disabled="isSimulating"
        class="px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white border border-indigo-200 rounded-full text-xs font-semibold flex items-center gap-1.5 transition disabled:opacity-50"
        title="Start an AI Agent simulation between two personas"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span>{{ isSimulating ? 'Simulating...' : 'AI Simulation' }}</span>
      </button>

      <!-- Call Buttons -->
      <div v-if="conversation.type === 'direct'" class="flex items-center gap-1">
        <!-- Voice Call Button -->
        <button
          @click="emit('startCall', false)"
          :disabled="isCallActive"
          class="p-2 text-green-600 hover:text-white hover:bg-green-600 rounded-full transition disabled:opacity-40 disabled:cursor-not-allowed"
          :title="isCallActive ? 'Currently in a call' : 'Voice Call'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
          </svg>
        </button>

        <!-- Video Call Button -->
        <button
          @click="emit('startCall', true)"
          :disabled="isCallActive"
          class="p-2 text-blue-600 hover:text-white hover:bg-blue-600 rounded-full transition disabled:opacity-40 disabled:cursor-not-allowed"
          :title="isCallActive ? 'Currently in a call' : 'Video Call'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>
