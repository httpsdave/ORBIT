<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

// Password visibility states
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Track which field is currently focused
const focusedField = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};

// Toggle password visibility functions
const toggleCurrentPassword = (event) => {
    event.preventDefault();
    event.stopPropagation();
    showCurrentPassword.value = !showCurrentPassword.value;
};

const toggleNewPassword = (event) => {
    event.preventDefault();
    event.stopPropagation();
    showNewPassword.value = !showNewPassword.value;
};

const toggleConfirmPassword = (event) => {
    event.preventDefault();
    event.stopPropagation();
    showConfirmPassword.value = !showConfirmPassword.value;
};

// Focus and blur handlers
const handleFocus = (fieldName) => {
    focusedField.value = fieldName;
};

const handleBlur = () => {
    setTimeout(() => {
        focusedField.value = null;
    }, 150);
};

// Prevent blur when clicking toggle button
const handleToggleMouseDown = (event) => {
    event.preventDefault();
};
</script>

<template>
    <section class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
        <!-- Header with colored accent -->
        <div class="border-l-4 border-green-500 pl-3 mb-6">
            <h2 class="text-xl font-semibold text-gray-800">
                Update Password
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Ensure your account is using a long, random password to stay secure.
            </p>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-6">
            <!-- Hidden username/email field for accessibility and autofill -->
            <input
                type="text"
                name="username"
                :value="user.email"
                autocomplete="username"
                style="display: none;"
                tabindex="-1"
                aria-hidden="true"
            />
            <!-- Password fields in responsive layout -->
            <div class="space-y-6 md:space-y-0 md:grid md:grid-cols-2 md:gap-6">
                <!-- Current password field -->
                <div class="col-span-2 md:col-span-1">
                    <InputLabel 
                        for="current_password" 
                        value="Current Password" 
                        class="text-gray-700 font-medium" 
                    />
                    <div class="relative">
                        <TextInput
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            :type="showCurrentPassword ? 'text' : 'password'"
                            class="mt-1 block w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 rounded-md shadow-sm pr-10"
                            autocomplete="current-password"
                            @focus="handleFocus('current')"
                            @blur="handleBlur"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pt-1">
                            <!-- Show toggle button only when field is focused -->
                            <button
                                v-if="focusedField === 'current'"
                                type="button"
                                @click="toggleCurrentPassword"
                                @mousedown="handleToggleMouseDown"
                                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition-colors duration-200"
                                :aria-label="showCurrentPassword ? 'Hide password' : 'Show password'"
                            >
                                <!-- Eye icon for show password -->
                                <svg
                                    v-if="!showCurrentPassword"
                                    class="h-5 w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                <!-- Eye slash icon for hide password -->
                                <svg
                                    v-else
                                    class="h-5 w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                                    />
                                </svg>
                            </button>
                            <!-- Show lock icon when field is not focused -->
                            <svg
                                v-else
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <InputError
                        :message="form.errors.current_password"
                        class="mt-2"
                    />
                </div>
                
                <!-- New password field -->
                <div class="md:col-span-1">
                    <InputLabel 
                        for="password" 
                        value="New Password" 
                        class="text-gray-700 font-medium" 
                    />
                    <div class="relative">
                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            :type="showNewPassword ? 'text' : 'password'"
                            class="mt-1 block w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 rounded-md shadow-sm pr-10"
                            autocomplete="new-password"
                            @focus="handleFocus('new')"
                            @blur="handleBlur"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pt-1">
                            <!-- Show toggle button only when field is focused -->
                            <button
                                v-if="focusedField === 'new'"
                                type="button"
                                @click="toggleNewPassword"
                                @mousedown="handleToggleMouseDown"
                                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition-colors duration-200"
                                :aria-label="showNewPassword ? 'Hide password' : 'Show password'"
                            >
                                <!-- Eye icon for show password -->
                                <svg
                                    v-if="!showNewPassword"
                                    class="h-5 w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                <!-- Eye slash icon for hide password -->
                                <svg
                                    v-else
                                    class="h-5 w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                                    />
                                </svg>
                            </button>
                            <!-- Show lock icon when field is not focused -->
                            <svg
                                v-else
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>
                
                <!-- Password confirmation field -->
                <div class="md:col-span-1">
                    <InputLabel
                        for="password_confirmation"
                        value="Confirm Password"
                        class="text-gray-700 font-medium"
                    />
                    <div class="relative">
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="mt-1 block w-full border-gray-300 focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 rounded-md shadow-sm pr-10"
                            autocomplete="new-password"
                            @focus="handleFocus('confirm')"
                            @blur="handleBlur"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pt-1">
                            <!-- Show toggle button only when field is focused -->
                            <button
                                v-if="focusedField === 'confirm'"
                                type="button"
                                @click="toggleConfirmPassword"
                                @mousedown="handleToggleMouseDown"
                                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition-colors duration-200"
                                :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                            >
                                <!-- Eye icon for show password -->
                                <svg
                                    v-if="!showConfirmPassword"
                                    class="h-5 w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>
                                <!-- Eye slash icon for hide password -->
                                <svg
                                    v-else
                                    class="h-5 w-5"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                                    />
                                </svg>
                            </button>
                            <!-- Show lock icon when field is not focused -->
                            <svg
                                v-else
                                class="h-5 w-5 text-gray-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <InputError
                        :message="form.errors.password_confirmation"
                        class="mt-2"
                    />
                </div>
            </div>

            <!-- Password tips/requirements section -->
            <div class="py-3 px-4 bg-gray-50 rounded-md border border-gray-100">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Password Requirements:</h3>
                <ul class="space-y-1 text-xs text-gray-600">
                    <li class="flex items-center">
                        <svg class="h-4 w-4 mr-1 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Minimum 8 characters in length
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 mr-1 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Include a mix of letters, numbers, and symbols
                    </li>
                    <li class="flex items-center">
                        <svg class="h-4 w-4 mr-1 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Avoid using personal information
                    </li>
                </ul>
            </div>

            <div class="flex items-center pt-4 border-t border-gray-100">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="bg-green-500 hover:bg-green-600 focus:bg-green-600"
                >
                    Update Password
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="ml-4 text-sm text-green-500 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Password updated successfully
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>