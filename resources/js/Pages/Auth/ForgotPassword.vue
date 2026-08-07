<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

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

                <!-- Key Illustration Icon -->
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>

                <h1 class="text-center text-2xl font-bold text-slate-900">Forgot Password?</h1>

                <p class="mb-6 mt-2 text-center text-sm leading-relaxed text-slate-600">
                    No problem. Just enter your email address and we'll email you a password reset link to choose a new one.
                </p>

                <!-- Dynamic Status Message Banner -->
                <div
                    v-if="status"
                    class="mb-6 rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 text-center text-xs font-medium text-emerald-700 backdrop-blur-sm"
                >
                    {{ status }}
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-xs font-semibold text-slate-700" />

                        <div class="relative mt-1 flex items-center">
                            <span class="absolute left-3.5 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
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

                    <!-- Actions -->
                    <div class="flex flex-col gap-3 pt-2">
                        <PrimaryButton
                            class="w-full justify-center rounded-xl bg-gradient-to-r from-sky-600 to-sky-700 py-3 text-sm font-semibold tracking-wide text-white shadow-md shadow-sky-600/25 transition hover:from-sky-700 hover:to-sky-800 focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Email Password Reset Link
                        </PrimaryButton>

                        <div class="text-center">
                            <Link
                                :href="route('login')"
                                class="text-xs font-semibold text-sky-600 hover:text-sky-700 hover:underline"
                            >
                                Back to Log In
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
