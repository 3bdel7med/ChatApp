<script setup>
import { ref } from 'vue'
import VoiceRecorder from '@/Components/Chat/VoiceRecorder.vue'

const props = defineProps({
  form: Object,
  selectedFilePreview: String,
  isPartnerTyping: Boolean,
})

const emit = defineEmits([
  'submit',
  'fileSelected',
  'clearFile',
  'typing',
  'sendAudio',
])

const fileInput = ref(null)

const handleFileChange = (e) => {
  emit('fileSelected', e)
}

const triggerFileInput = () => {
  if (fileInput.value) fileInput.value.click()
}

// Handler when voice recording is submitted
const handleSendAudio = (audioFile) => {
  emit('sendAudio', audioFile)
}

defineExpose({
  fileInput,
})
</script>

<template>
  <div class="flex-shrink-0">
    <!-- Selected File Preview Bar -->
    <div
      v-if="selectedFilePreview"
      class="px-4 py-2 bg-gray-100 border-t flex items-center justify-between text-xs"
    >
      <div class="flex items-center gap-2 truncate">
        <span class="font-bold text-gray-700">Attached File:</span>
        <span class="text-gray-600 truncate max-w-xs">{{ form.file?.name }}</span>
      </div>
      <button
        @click="emit('clearFile')"
        class="text-red-500 hover:text-red-700 font-bold"
      >
        &times; Cancel
      </button>
    </div>

    <!-- Partner Typing Indicator Banner -->
    <div
      v-if="isPartnerTyping"
      class="px-4 py-1.5 text-xs text-gray-500 italic flex items-center gap-1.5 bg-gray-100/70 border-t"
    >
      <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
      <span>typing...</span>
    </div>

    <!-- Message Input Form -->
    <div class="p-4 bg-white border-t">
      <form @submit.prevent="emit('submit')" class="flex items-center gap-2">
        <!-- Hidden File Input -->
        <input
          type="file"
          ref="fileInput"
          @change="handleFileChange"
          class="hidden"
          accept="image/*,.pdf,.doc,.docx,.zip"
        />

        <!-- Attachment Button -->
        <button
          type="button"
          @click="triggerFileInput"
          class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-full transition"
          title="Attach a file or image"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
            />
          </svg>
        </button>

        <!-- Voice Recorder Component -->
        <VoiceRecorder @send-audio="handleSendAudio" />

        <!-- Text Input Field -->
        <input
          v-model="form.body"
          @input="emit('typing')"
          type="text"
          placeholder="Type your message..."
          class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:border-blue-500"
          :disabled="form.processing"
        />

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="form.processing || (!form.body.trim() && !form.file)"
          class="bg-blue-600 text-white px-5 py-2 rounded-full text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition"
        >
          Send
        </button>
      </form>
    </div>
  </div>
</template>
