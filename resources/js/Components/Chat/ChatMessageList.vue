<script setup>
import { ref } from 'vue'

const props = defineProps({
  messages: Array,
  currentUserId: Number,
  editingMessageId: [Number, String],
  editForm: Object,
})

const emit = defineEmits(['startEdit', 'cancelEdit', 'saveEdit'])
const messagesContainer = ref(null)

defineExpose({ messagesContainer })
</script>

<template>
  <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
    <div
      v-for="msg in messages"
      :key="msg.id"
      class="flex flex-col"
      :class="msg.sender_id === currentUserId ? 'items-end' : 'items-start'"
    >
      <div class="relative group max-w-[70%]">
        <!-- Show Edit Pencil ONLY for own text messages (no image or audio attachments) -->
        <button
          v-if="msg.sender_id === currentUserId && editingMessageId !== msg.id && !msg.file_type"
          @click="emit('startEdit', msg)"
          class="absolute -left-7 top-2 opacity-0 group-hover:opacity-100 transition p-1 text-gray-400 hover:text-gray-600 text-xs"
          title="Edit Message"
        >
          ✏️
        </button>

        <!-- 1. INLINE TEXT EDITOR -->
        <div v-if="editingMessageId === msg.id" class="flex flex-col gap-2 bg-white p-3 rounded-2xl shadow border">
          <textarea
            v-model="editForm.body"
            class="text-sm p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-800"
            rows="2"
          ></textarea>
          <div class="flex justify-end gap-2">
            <button @click="emit('cancelEdit')" class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700">Cancel</button>
            <button @click="emit('saveEdit', msg.id)" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-md font-medium">Save</button>
          </div>
        </div>

        <!-- 2. IMAGE MESSAGE VIEW -->
        <div
          v-else-if="msg.file_type === 'image'"
          class="overflow-hidden rounded-2xl border bg-gray-100 shadow-sm"
        >
          <a :href="msg.file_path" target="_blank" rel="noopener noreferrer">
            <img
              :src="msg.file_path"
              :alt="msg.file_name || 'Attached Image'"
              class="max-w-xs sm:max-w-sm rounded-2xl object-cover max-h-80 hover:opacity-95 transition"
            />
          </a>
          <p v-if="msg.body" class="p-2 text-sm bg-white text-gray-800 border-t">{{ msg.body }}</p>
        </div>

        <!-- 3. AUDIO MESSAGE VIEW -->
        <div
          v-else-if="msg.file_type === 'audio'"
          class="p-2 rounded-2xl bg-white border shadow-sm"
        >
          <audio controls class="max-w-[240px] h-9">
            <source :src="msg.file_path" type="audio/webm" />
            <source :src="msg.file_path" type="audio/ogg" />
            <source :src="msg.file_path" type="audio/mp4" />
            Your browser does not support audio elements.
          </audio>
        </div>

        <!-- 4. STANDARD TEXT & DOCUMENT MESSAGE VIEW -->
        <div
          v-else
          class="p-3 rounded-2xl text-sm"
          :class="msg.sender_id === currentUserId ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white text-gray-800 border rounded-bl-none'"
        >
          <p v-if="msg.body">{{ msg.body }}</p>

          <!-- Attachment Link if file_type is generic 'file' -->
          <a
            v-if="msg.file_path && msg.file_type !== 'image' && msg.file_type !== 'audio'"
            :href="msg.file_path"
            target="_blank"
            rel="noopener noreferrer"
            class="flex items-center gap-1 mt-1 text-xs underline font-medium opacity-90 hover:opacity-100"
          >
            📎 {{ msg.file_name || 'Download Attachment' }}
          </a>

          <span v-if="msg.is_edited" class="text-[10px] opacity-60 ml-1">(edited)</span>
        </div>
      </div>
    </div>
  </div>
</template>
