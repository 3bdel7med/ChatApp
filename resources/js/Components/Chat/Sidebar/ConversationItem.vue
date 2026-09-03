<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  conv: {
    type: Object,
    required: true,
  },
  isActive: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <Link
    :href="route('chat.show', conv.id)"
    class="flex items-center gap-3 p-4 hover:bg-gray-50 transition relative"
    :class="{ 'bg-blue-50/70 border-l-4 border-blue-600': isActive }"
  >
    <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-base border bg-gray-200 text-gray-700">
      {{ (conv.other_user?.name || conv.name || 'C')[0].toUpperCase() }}
    </div>

    <div class="flex-1 min-w-0">
      <div class="flex justify-between items-baseline mb-1">
        <h3 class="text-sm font-semibold text-gray-900 truncate">
          {{ conv.name || conv.other_user?.name }}
        </h3>
        <span v-if="conv.last_message" class="text-[11px] text-gray-400">
          {{ new Date(conv.last_message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
        </span>
      </div>
      <p class="text-xs text-gray-500 truncate">
        {{ conv.last_message ? conv.last_message.body : 'Start conversation now...' }}
      </p>
    </div>
  </Link>
</template>
