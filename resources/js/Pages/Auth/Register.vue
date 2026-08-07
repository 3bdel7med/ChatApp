<script setup lang="ts">
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Form definition
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Handling form submission
const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            // Re-evaluating form.reset with single quotes for consistency.
            form.reset('password', 'password_confirmation');
        },
    });
};

// Password visibility state and icon. Setting to "password" shows. Setting to "text" hides.
// Using single quotes consistently.
const passwordType = ref<'password' | 'text'>('password');
const showPasswordIcon = ref<'slash' | 'eye'>('slash');

// Function to handle the eye click for the password input.
const handleEyeClick = () => {
    if (passwordType.value === 'password') {
        passwordType.value = 'text';
        showPasswordIcon.value = 'eye';
    } else {
        passwordType.value = 'password';
        showPasswordIcon.value = 'slash';
    }
};

// Password visibility state and icon for confirmation input. Setting to "password" shows. Setting to "text" hides.
const passwordConfirmationType = ref<'password' | 'text'>('password');
const showPasswordConfirmationIcon = ref<'slash' | 'eye'>('slash');

// Function to handle the eye click for the password confirmation input.
const handleEyeConfirmationClick = () => {
    if (passwordConfirmationType.value === 'password') {
        passwordConfirmationType.value = 'text';
        showPasswordConfirmationIcon.value = 'eye';
    } else {
        passwordConfirmationType.value = 'password';
        showPasswordConfirmationIcon.value = 'slash';
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <!-- Form with submission prevention. Added standard Tailwind classes for modern forms. -->
        <form @submit.prevent="submit" class="w-full max-w-md bg-white p-8 rounded-lg shadow-md space-y-6">

            <!-- Standard form field definition. Added relevant Tailwind classes to create modern inputs. -->
            <div>
                <InputLabel for="name" value="Name" class="text-sm font-medium text-gray-700" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Standard form field definition. Added relevant Tailwind classes to create modern inputs. -->
            <div>
                <InputLabel for="email" value="Email" class="text-sm font-medium text-gray-700" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password field definition. Standard input with the addition of the visibility toggle logic.  -->
            <div class="relative">
                <InputLabel for="password" value="Password" class="text-sm font-medium text-gray-700" />

                <TextInput
                    id="password"
                    :type="passwordType"
                    class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <!-- Dynamic visibility icon button. Dynamic binding to class allows us to easily change the eye based on local state. -->
                <button type="button" @click="handleEyeClick" class="absolute right-4 bottom-2 text-gray-400 hover:text-gray-600 focus:outline-none">
                  <i class="fas" :class="'fa-eye-' + showPasswordIcon"></i>
                </button>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Password confirmation field definition. Standard input with the addition of the visibility toggle logic. -->
            <div class="relative">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                    class="text-sm font-medium text-gray-700"
                />

                <TextInput
                    id="password_confirmation"
                    :type="passwordConfirmationType"
                    class="mt-2 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <!-- Dynamic visibility icon button. Dynamic binding to class allows us to easily change the eye based on local state. -->
                <button type="button" @click="handleEyeConfirmationClick" class="absolute right-4 bottom-2 text-gray-400 hover:text-gray-600 focus:outline-none">
                  <i class="fas" :class="'fa-eye-' + showPasswordConfirmationIcon"></i>
                </button>

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <!-- Standard submission field with the inclusion of secondary CTA 'already registered?'. Applied primary button styles. Added space-y-4 class to this parent div to add space between the two buttons when stacked on mobile.  -->
            <div class="flex items-center justify-end flex-wrap space-y-4 md:space-y-0">
                <Link
                    :href="route('login')"
                    class="w-full text-center text-sm text-sky-600 hover:text-sky-700 underline md:w-auto md:text-left focus:outline-none"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="w-full md:w-auto md:ms-4 px-6 py-2.5 bg-sky-600 text-white font-medium text-sm leading-tight uppercase rounded shadow-md hover:bg-sky-700 hover:shadow-lg focus:bg-sky-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-sky-800 active:shadow-lg transition duration-150 ease-in-out"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
