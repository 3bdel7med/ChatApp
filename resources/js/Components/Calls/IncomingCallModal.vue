<script setup>
defineProps({
  call: Object
})

const emit = defineEmits(['accept', 'reject'])
</script>

<template>
  <div v-if="call" class="fixed inset-0 bg-black/70 backdrop-blur-md z-[100] flex items-center justify-center p-4 animate-fade-in">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl flex flex-col items-center">
      <div class="relative mb-4">
        <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center font-bold text-3xl shadow-lg animate-pulse">
          {{ call.caller_name?.[0]?.toUpperCase() ?? 'U' }}
        </div>
        <span class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 border-4 border-white rounded-full"></span>
      </div>

      <h3 class="text-xl font-bold text-gray-800 mb-1">{{ call.caller_name }}</h3>
      <p class="text-sm text-gray-500 mb-8 animate-bounce">Incoming Video/Audio Call...</p>

      <div class="flex items-center justify-center gap-6 w-full">
        <button
          @click="emit('reject')"
          class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-semibold shadow-lg shadow-red-500/30 transition flex items-center justify-center gap-2"
        >
          Decline
        </button>

        <button
          @click="emit('accept')"
          class="flex-1 py-3 bg-green-500 hover:bg-green-600 text-white rounded-2xl font-semibold shadow-lg shadow-green-500/30 transition flex items-center justify-center gap-2"
        >
          Accept
        </button>
      </div>
    </div>
  </div>
</template>
