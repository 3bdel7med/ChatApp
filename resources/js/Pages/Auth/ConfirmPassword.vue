<script setup lang="ts">
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const showPassword = ref(false);

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-sky-100 via-sky-50 to-indigo-100 p-4">
            <!-- Background Ambient Glow Shapes -->
            <div class="absolute -left-12 -top-12 h-72 w-72 rounded-full bg-sky-400/30 blur-3xl"></div>
            <div class="absolute -bottom-12 -right-12 h-72 w-72 rounded-full bg-indigo-400/25 blur-3xl"></div>

            <!-- Card Container -->
            <div class="relative z-10 w-full max-w-md rounded-3xl border border-white/60 bg-white/80 p-8 shadow-2xl backdrop-blur-xl">

                <!-- Brand Header -->
                <div class="mb-6 flex items-center justify-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-sky-600 text-white shadow-md shadow-sky-500/30">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold leading-none text-slate-900">ConnectSphere</h2>
                        <span class="text-xs font-medium text-slate-500">Chat Application</span>
                    </div>
                </div>

                <!-- Security Shield Icon -->
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>

                <h1 class="text-center text-2xl font-bold text-slate-900">Security Check</h1>
                <p class="mb-6 mt-2 text-center text-sm leading-relaxed text-slate-600">
                    This is a secure area of the application. Please confirm your password before continuing.
                </p>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
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
                                autofocus
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

                    <div class="pt-2">
                        <PrimaryButton
                            class="w-full justify-center rounded-xl bg-gradient-to-r from-sky-600 to-sky-700 py-3 text-sm font-semibold tracking-wide text-white shadow-md shadow-sky-600/25 transition hover:from-sky-700 hover:to-sky-800 focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Confirm Password
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
