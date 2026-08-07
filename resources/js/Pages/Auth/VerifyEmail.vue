<script setup lang="ts">
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

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

                <!-- Envelope Illustration Icon -->
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-100 text-sky-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>

                <h1 class="text-center text-2xl font-bold text-slate-900">Verify Your Email</h1>

                <p class="mb-6 mt-2 text-center text-sm leading-relaxed text-slate-600">
                    Thanks for signing up! Before getting started, please verify your email address by clicking the link we sent to you.
                </p>

                <!-- Dynamic Success Banner -->
                <div
                    v-if="verificationLinkSent"
                    class="mb-6 rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 text-center text-xs font-medium text-emerald-700 backdrop-blur-sm"
                >
                    A new verification link has been sent to the email address you provided during registration.
                </div>

                <!-- Form -->
                <form @submit.prevent="submit">
                    <div class="flex flex-col gap-3">
                        <PrimaryButton
                            class="w-full justify-center rounded-xl bg-gradient-to-r from-sky-600 to-sky-700 py-3 text-sm font-semibold tracking-wide text-white shadow-md shadow-sky-600/25 transition hover:from-sky-700 hover:to-sky-800 focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Resend Verification Email
                        </PrimaryButton>

                        <div class="mt-2 text-center">
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline focus:outline-none"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
