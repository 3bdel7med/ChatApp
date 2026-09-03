<script setup lang="ts">
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#0b0f19] p-4 antialiased selection:bg-sky-500 selection:text-white">
            <!-- Background Ambient Glow Shapes -->
            <div class="absolute -left-20 -top-20 h-96 w-96 rounded-full bg-sky-500/10 blur-[120px]"></div>
            <div class="absolute -bottom-20 -right-20 h-96 w-96 rounded-full bg-indigo-500/10 blur-[120px]"></div>

            <!-- Glassmorphic Card Container -->
            <div class="relative z-10 w-full max-w-md rounded-3xl border border-white/10 bg-slate-900/60 p-8 shadow-2xl backdrop-blur-2xl sm:p-10">

                <!-- Brand Header -->
                <div class="mb-6 flex items-center justify-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-500 text-white shadow-lg shadow-sky-500/20">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-extrabold text-white tracking-tight leading-tight">ConnectSphere</h2>
                        <span class="text-xs font-medium text-slate-400">Create Account</span>
                    </div>
                </div>

                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">Get Started</h1>
                    <p class="mt-1.5 text-sm text-slate-400">Join and connect with your team instantly.</p>
                </div>

                <!-- Registration Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Name Field -->
                    <div>
                        <InputLabel for="name" value="Full Name" class="text-xs font-medium text-slate-300" />

                        <div class="relative mt-1.5 flex items-center">
                            <span class="pointer-events-none absolute left-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <TextInput
                                id="name"
                                type="text"
                                class="w-full rounded-xl border-white/10 bg-slate-800/50 pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-sky-500 focus:bg-slate-800 focus:ring-2 focus:ring-sky-500/20"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="John Doe"
                            />
                        </div>

                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-xs font-medium text-slate-300" />

                        <div class="relative mt-1.5 flex items-center">
                            <span class="pointer-events-none absolute left-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <TextInput
                                id="email"
                                type="email"
                                class="w-full rounded-xl border-white/10 bg-slate-800/50 pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-sky-500 focus:bg-slate-800 focus:ring-2 focus:ring-sky-500/20"
                                v-model="form.email"
                                required
                                autocomplete="username"
                                placeholder="name@company.com"
                            />
                        </div>

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <InputLabel for="password" value="Password" class="text-xs font-medium text-slate-300" />

                        <div class="relative mt-1.5 flex items-center">
                            <span class="pointer-events-none absolute left-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <TextInput
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full rounded-xl border-white/10 bg-slate-800/50 pl-10 pr-10 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-sky-500 focus:bg-slate-800 focus:ring-2 focus:ring-sky-500/20"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 text-slate-400 hover:text-slate-200 transition"
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

                    <!-- Confirm Password Field -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-xs font-medium text-slate-300" />

                        <div class="relative mt-1.5 flex items-center">
                            <span class="pointer-events-none absolute left-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                            <TextInput
                                id="password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                class="w-full rounded-xl border-white/10 bg-slate-800/50 pl-10 pr-10 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-sky-500 focus:bg-slate-800 focus:ring-2 focus:ring-sky-500/20"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                                class="absolute right-3.5 text-slate-400 hover:text-slate-200 transition"
                            >
                                <svg v-if="!showPasswordConfirmation" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a9.04 9.04 0 012.122-.163c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18" />
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>

                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <PrimaryButton
                            class="w-full justify-center rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 py-3 text-sm font-semibold tracking-wide text-white shadow-lg shadow-sky-500/25 transition hover:from-sky-400 hover:to-indigo-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 focus:ring-offset-slate-900 active:scale-[0.99]"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            CREATE ACCOUNT
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Footer Sign In Link -->
                <div class="mt-8 text-center text-xs text-slate-400">
                    Already registered?
                    <Link :href="route('login')" class="font-semibold text-sky-400 transition hover:text-sky-300 hover:underline">
                        Sign In
                    </Link>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
