<script setup>
import { ref } from 'vue'
import { useForm, usePage, Head } from '@inertiajs/vue3'
import ChatLayout from '@/Layouts/ChatLayout.vue'

const props = defineProps({
  mustVerifyEmail: Boolean,
  status: String,
  conversations: Array,
})

const user = usePage().props.auth.user

// Avatar preview logic
const avatarInput = ref(null)
const avatarPreview = ref(user.avatar ? `/storage/${user.avatar}` : null)

// 1. Profile Information & Avatar Form
const profileForm = useForm({
  _method: 'PATCH', // Required for Inertia multipart/form-data requests
  name: user.name,
  email: user.email,
  avatar: null,
})

const onAvatarSelected = (e) => {
  const file = e.target.files[0]
  if (!file) return

  profileForm.avatar = file
  avatarPreview.value = URL.createObjectURL(file)
}

const updateProfileInformation = () => {
  profileForm.post(route('profile.update'), {
    preserveScroll: true,
    forceFormData: true,
  })
}

// 2. Password Form
const passwordInput = ref(null)
const currentPasswordInput = ref(null)

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const updatePassword = () => {
  passwordForm.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
    onError: (errors) => {
      if (errors.password) {
        passwordForm.reset('password', 'password_confirmation')
        passwordInput.value?.focus()
      }
      if (errors.current_password) {
        passwordForm.reset('current_password')
        currentPasswordInput.value?.focus()
      }
    },
  })
}

// 3. Delete Account Form
const confirmingUserDeletion = ref(false)
const passwordInputDelete = ref(null)

const deleteForm = useForm({
  password: '',
})

const confirmUserDeletion = () => {
  confirmingUserDeletion.value = true
}

const deleteUser = () => {
  deleteForm.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInputDelete.value?.focus(),
    onFinish: () => deleteForm.reset(),
  })
}

const closeModal = () => {
  confirmingUserDeletion.value = false
  deleteForm.reset()
}
</script>

<template>
  <Head title="Edit Profile" />

  <ChatLayout :conversations="conversations">
    <div class="flex-1 min-h-0 bg-gray-50 overflow-y-auto p-4 md:p-8">
      <div class="max-w-4xl mx-auto space-y-6">

        <!-- Page Header -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
          <div>
            <h1 class="text-xl font-bold text-gray-800">Profile Settings</h1>
            <p class="text-xs text-gray-500 mt-1">Manage your account information, security, and avatar.</p>
          </div>
          <div class="relative group">
            <img
              v-if="avatarPreview"
              :src="avatarPreview"
              class="w-14 h-14 rounded-full object-cover border-2 border-blue-500 shadow-sm"
              alt="Profile Avatar"
            />
            <div
              v-else
              class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xl border-2 border-blue-200"
            >
              {{ user.name ? user.name[0].toUpperCase() : 'U' }}
            </div>
          </div>
        </div>

        <!-- 1. Profile Information & Avatar Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
          <div>
            <h2 class="text-base font-bold text-gray-800">Personal Details & Avatar</h2>
            <p class="text-xs text-gray-500">Update your account's profile information, email address, and avatar.</p>
          </div>

          <form @submit.prevent="updateProfileInformation" class="space-y-5 max-w-xl">

            <!-- Avatar Upload Field -->
            <div class="flex items-center gap-4">
              <div class="relative">
                <img
                  v-if="avatarPreview"
                  :src="avatarPreview"
                  class="w-20 h-20 rounded-full object-cover border-2 border-gray-200"
                  alt="Avatar Preview"
                />
                <div
                  v-else
                  class="w-20 h-20 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-2xl border-2 border-gray-200"
                >
                  {{ user.name ? user.name[0].toUpperCase() : 'U' }}
                </div>
              </div>

              <div>
                <input
                  type="file"
                  ref="avatarInput"
                  @change="onAvatarSelected"
                  class="hidden"
                  accept="image/png, image/jpeg, image/jpg, image/webp"
                />
                <button
                  type="button"
                  @click="avatarInput.click()"
                  class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition"
                >
                  Change Avatar
                </button>
                <p class="text-[11px] text-gray-400 mt-1">PNG, JPG or WEBP (Max 2MB)</p>
                <span v-if="profileForm.errors.avatar" class="text-xs text-red-500 block mt-1">
                  {{ profileForm.errors.avatar }}
                </span>
              </div>
            </div>

            <!-- Name Field -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Full Name</label>
              <input
                v-model="profileForm.name"
                type="text"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition"
                required
              />
              <span v-if="profileForm.errors.name" class="text-xs text-red-500 mt-1 block">
                {{ profileForm.errors.name }}
              </span>
            </div>

            <!-- Email Field -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
              <input
                v-model="profileForm.email"
                type="email"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition"
                required
              />
              <span v-if="profileForm.errors.email" class="text-xs text-red-500 mt-1 block">
                {{ profileForm.errors.email }}
              </span>
            </div>

            <div class="flex items-center gap-3 pt-2">
              <button
                type="submit"
                :disabled="profileForm.processing"
                class="bg-blue-600 text-white px-5 py-2 rounded-xl text-xs font-semibold hover:bg-blue-700 disabled:opacity-50 transition"
              >
                Save Changes
              </button>
              <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                <p v-if="profileForm.recentlySuccessful" class="text-xs text-green-600 font-medium">Saved successfully.</p>
              </Transition>
            </div>
          </form>
        </div>

        <!-- 2. Password Update Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
          <div>
            <h2 class="text-base font-bold text-gray-800">Update Password</h2>
            <p class="text-xs text-gray-500">Ensure your account is using a long, random password to stay secure.</p>
          </div>

          <form @submit.prevent="updatePassword" class="space-y-4 max-w-xl">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Current Password</label>
              <input
                ref="currentPasswordInput"
                v-model="passwordForm.current_password"
                type="password"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition"
                autocomplete="current-password"
              />
              <span v-if="passwordForm.errors.current_password" class="text-xs text-red-500 mt-1 block">
                {{ passwordForm.errors.current_password }}
              </span>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">New Password</label>
              <input
                ref="passwordInput"
                v-model="passwordForm.password"
                type="password"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition"
                autocomplete="new-password"
              />
              <span v-if="passwordForm.errors.password" class="text-xs text-red-500 mt-1 block">
                {{ passwordForm.errors.password }}
              </span>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Confirm New Password</label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-blue-500 transition"
                autocomplete="new-password"
              />
              <span v-if="passwordForm.errors.password_confirmation" class="text-xs text-red-500 mt-1 block">
                {{ passwordForm.errors.password_confirmation }}
              </span>
            </div>

            <div class="flex items-center gap-3 pt-2">
              <button
                type="submit"
                :disabled="passwordForm.processing"
                class="bg-blue-600 text-white px-5 py-2 rounded-xl text-xs font-semibold hover:bg-blue-700 disabled:opacity-50 transition"
              >
                Update Password
              </button>
              <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                <p v-if="passwordForm.recentlySuccessful" class="text-xs text-green-600 font-medium">Password updated.</p>
              </Transition>
            </div>
          </form>
        </div>

        <!-- 3. Delete Account Section -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-red-100 space-y-4">
          <div>
            <h2 class="text-base font-bold text-red-600">Delete Account</h2>
            <p class="text-xs text-gray-500">Once your account is deleted, all of its resources and data will be permanently purged.</p>
          </div>

          <button
            @click="confirmUserDeletion"
            class="bg-red-50 text-red-600 border border-red-200 px-5 py-2 rounded-xl text-xs font-semibold hover:bg-red-600 hover:text-white transition"
          >
            Delete Account
          </button>
        </div>

      </div>
    </div>

    <!-- Account Deletion Modal -->
    <div v-if="confirmingUserDeletion" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl">
        <h3 class="text-base font-bold text-gray-900">Are you sure you want to delete your account?</h3>
        <p class="text-xs text-gray-500 leading-relaxed">
          Please enter your password to confirm you would like to permanently delete your account.
        </p>

        <div>
          <input
            ref="passwordInputDelete"
            v-model="deleteForm.password"
            type="password"
            placeholder="Password"
            class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-red-500 transition"
            @keyup.enter="deleteUser"
          />
          <span v-if="deleteForm.errors.password" class="text-xs text-red-500 mt-1 block">
            {{ deleteForm.errors.password }}
          </span>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            @click="closeModal"
            class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition"
          >
            Cancel
          </button>
          <button
            @click="deleteUser"
            :disabled="deleteForm.processing"
            class="bg-red-600 text-white px-4 py-2 rounded-xl text-xs font-semibold hover:bg-red-700 disabled:opacity-50 transition"
          >
            Confirm Delete
          </button>
        </div>
      </div>
    </div>
  </ChatLayout>
</template>
