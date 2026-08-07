<script setup lang="ts">
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>

        <Head title="Log in" />

        <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-sky-100 via-sky-50 to-indigo-100 p-4">
            <!-- Background Ambient Glow Shapes -->
            <div class="absolute -left-12 -top-12 h-72 w-72 rounded-full bg-sky-400/30 blur-3xl"></div>
            <div class="absolute -bottom-12 -right-12 h-72 w-72 rounded-full bg-indigo-400/25 blur-3xl"></div>

            <!-- Card Container -->
            <div class="relative z-10 w-full max-width max-w-md rounded-3xl border border-white/60 bg-white/80 p-8 shadow-2xl backdrop-blur-xl">

                <!-- Brand Header -->
                <div class="mb-6 flex items-center justify-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-sky-600 text-white shadow-md shadow-sky-500/30">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 leading-none">ConnectSphere</h2>
                        <span class="text-xs font-medium text-slate-500">Chat Application</span>
                    </div>
                </div>

                <h1 class="text-center text-2xl font-bold text-slate-900">Welcome Back!</h1>
                <p class="mb-6 text-center text-sm text-slate-500">Sign in to continue chatting.</p>

                <!-- Status Message -->
                <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-xs font-semibold text-slate-700" />

                        <div class="relative mt-1 flex items-center">
                            <span class="absolute left-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <TextInput
                                id="email"
                                type="email"
                                class="w-full rounded-xl border-slate-300 pl-10 pr-4 py-2.5 text-sm transition focus:border-sky-500 focus:ring-sky-500/20"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="name@company.com"
                            />
                        </div>

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div class="mt-4">
                        <InputLabel for="password" value="Password" class="text-xs font-semibold text-slate-700" />

                        <div class="relative mt-1 flex items-center">
                            <span class="absolute left-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <TextInput
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full rounded-xl border-slate-300 pl-10 pr-10 py-2.5 text-sm transition focus:border-sky-500 focus:ring-sky-500/20"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 text-slate-400 hover:text-slate-600 transition"
                            >
                                <svg v-if="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a9.04 9.04 0 012.122-.163c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18" />
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>

                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="mt-4 flex items-center justify-between text-xs">
                        <label class="flex items-center cursor-pointer text-slate-600">
                            <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
                            <span class="ms-2">Remember me</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="font-semibold text-sky-600 hover:text-sky-700 hover:underline"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <PrimaryButton
                            class="w-full justify-center rounded-xl bg-gradient-to-r from-sky-600 to-sky-700 py-3 text-sm font-semibold tracking-wide text-white shadow-md shadow-sky-600/25 transition hover:from-sky-700 hover:to-sky-800 focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            SIGN IN
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Divider -->
                <div class="my-6 flex items-center text-xs text-slate-400">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="px-3">Or sign in with</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <!-- Social Login Buttons -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <button type="button" class="flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24">
                            <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.6l3.1-3.1C17.3 1.7 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.3 9 5 12 5z"/>
                            <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                            <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.8s.2-2.1.4-2.8L1.9 6.3C.7 8.7 0 10.3 0 12s.7 3.3 1.9 5.7l3.7-2.9z"/>
                            <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.3-6.4-5.2L1.9 16C3.7 19.7 7.5 23 12 23z"/>
                        </svg>
                        Google
                    </button>

                    <button type="button" class="flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        <svg class="h-4 w-4 fill-slate-900" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" />
                        </svg>
                        GitHub
                    </button>
                </div>

                <!-- Footer Sign Up Link -->
                <div class="text-center text-xs text-slate-500">
                    Don't have an account?
                    <Link :href="route('register')" class="font-semibold text-sky-600 hover:underline">
                        Sign Up
                    </Link>
                </div>
            </div>
        </div>

</template>
