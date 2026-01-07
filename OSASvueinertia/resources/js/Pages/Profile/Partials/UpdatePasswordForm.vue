<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';
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

// Readonly state for current password to prevent autofill
const currentPasswordReadonly = ref(true);

// Track if password input is focused or has content
const isPasswordActive = computed(() => focusedField.value === 'new' || form.password.length > 0);

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

// Password requirement checks
const passwordRequirements = computed(() => {
    const password = form.password;
    return {
        minLength: password.length >= 8,
        hasUpper: /[A-Z]/.test(password),
        hasLower: /[a-z]/.test(password),
        hasNumber: /[0-9]/.test(password),
        hasSymbol: /[^A-Za-z0-9]/.test(password),
        matches: form.password && form.password === form.password_confirmation,
    };
});

// Password strength calculation
const passwordStrength = computed(() => {
    const req = passwordRequirements.value;
    let score = 0;
    if (req.minLength) score++;
    if (req.hasUpper) score++;
    if (req.hasLower) score++;
    if (req.hasNumber) score++;
    if (req.hasSymbol) score++;
    if (score <= 2) return { label: 'Weak', color: 'bg-red-400', width: 'w-1/4' };
    if (score === 3) return { label: 'Good', color: 'bg-yellow-400', width: 'w-2/4' };
    if (score === 4) return { label: 'Strong', color: 'bg-blue-400', width: 'w-3/4' };
    if (score === 5) return { label: 'Excellent', color: 'bg-green-500', width: 'w-full' };
    return { label: '', color: '', width: '' };
});

// Toggle password visibility functions
const toggleCurrentPassword = (event) => {
    event.preventDefault();
    event.stopPropagation();
    showCurrentPassword.value = !showCurrentPassword.value;
    // Refocus the input to maintain the eye icon
    nextTick(() => {
        if (currentPasswordInput.value) {
            currentPasswordInput.value.focus();
        }
    });
};

const toggleNewPassword = (event) => {
    event.preventDefault();
    event.stopPropagation();
    showNewPassword.value = !showNewPassword.value;
    // Refocus the input to maintain the eye icon
    nextTick(() => {
        if (passwordInput.value) {
            passwordInput.value.focus();
        }
    });
};

const toggleConfirmPassword = (event) => {
    event.preventDefault();
    event.stopPropagation();
    showConfirmPassword.value = !showConfirmPassword.value;
    // Refocus the input to maintain the eye icon
    nextTick(() => {
        const confirmInput = document.getElementById('password_confirmation');
        if (confirmInput) {
            confirmInput.focus();
        }
    });
};

// Focus and blur handlers
const handleFocus = (fieldName) => {
    // Clear any existing blur timeout
    if (blurTimeout.value) {
        clearTimeout(blurTimeout.value);
        blurTimeout.value = null;
    }
    focusedField.value = fieldName;
    
    // Remove readonly from current password when user interacts with it
    if (fieldName === 'current') {
        currentPasswordReadonly.value = false;
    }
};

const blurTimeout = ref(null);

const handleBlur = (event) => {
    // Check if the blur is caused by clicking on the toggle button
    const relatedTarget = event.relatedTarget;
    if (relatedTarget && relatedTarget.closest('button[type="button"]')) {
        // Don't blur if clicking on the toggle button
        return;
    }
    
    // Clear any existing timeout
    if (blurTimeout.value) {
        clearTimeout(blurTimeout.value);
    }
    
    // Set a new timeout to clear focus
    blurTimeout.value = setTimeout(() => {
        focusedField.value = null;
        blurTimeout.value = null;
    }, 200);
};

// Prevent blur when clicking toggle button
const handleToggleMouseDown = (event) => {
    event.preventDefault();
    event.stopPropagation();
};
</script>

<template>
    <section class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-100 dark:border-gray-700">
        <!-- Specific section description with colored accent -->
        <div class="border-l-4 border-green-500 pl-3 mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Change your current password to a new secure password. All fields are required for verification.
            </p>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-6" data-form-type="password-change">
            <!-- Password fields in responsive layout -->
            <div class="space-y-6 md:space-y-0 md:grid md:grid-cols-2 md:gap-6">
                <!-- Current password field -->
                <div class="col-span-2 md:col-span-1">
                    <InputLabel 
                        for="current_password" 
                        value="Current Password" 
                        class="text-gray-700 dark:text-gray-300 font-medium" 
                    />
                    <div class="relative">
                        <TextInput
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            :type="showCurrentPassword ? 'text' : 'password'"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 rounded-md shadow-sm pr-10 dark:bg-gray-700 dark:text-gray-300"
                            autocomplete="off"
                            :readonly="currentPasswordReadonly"
                            data-lpignore="true"
                            data-form-type="other"
                            @focus="handleFocus('current')"
                            @blur="handleBlur($event)"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pt-1">
                            <!-- Show toggle button only when field is focused -->
                            <button
                                v-if="focusedField === 'current'"
                                type="button"
                                @click="toggleCurrentPassword"
                                @mousedown="handleToggleMouseDown"
                                class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:text-gray-600 dark:focus:text-gray-300 transition-colors duration-200"
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
                                class="h-5 w-5 text-gray-400 dark:text-gray-500"
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
                        class="text-gray-700 dark:text-gray-300 font-medium" 
                    />
                    <div class="relative">
                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            :type="showNewPassword ? 'text' : 'password'"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 rounded-md shadow-sm pr-10 dark:bg-gray-700 dark:text-gray-300"
                            autocomplete="new-password"
                            @focus="handleFocus('new')"
                            @blur="handleBlur($event)"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pt-1">
                            <!-- Show toggle button only when field is focused -->
                            <button
                                v-if="focusedField === 'new'"
                                type="button"
                                @click="toggleNewPassword"
                                @mousedown="handleToggleMouseDown"
                                class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 focus:outline-none focus:text-gray-600 dark:focus:text-gray-400 transition-colors duration-200"
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
                                class="h-5 w-5 text-gray-400 dark:text-gray-500"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <!-- Password strength bar and label moved below input+icon container -->
                    <div v-if="form.password">
                        <div class="mt-1 h-0.5 w-full rounded bg-gray-200 dark:bg-gray-600 overflow-hidden flex">
                            <div :class="[passwordStrength.color, passwordStrength.width, 'h-0.5 rounded transition-all duration-300']"></div>
                        </div>
                        <div class="text-xs mt-1 font-semibold text-gray-600 dark:text-gray-400">{{ passwordStrength.label }} Password</div>
                    </div>
                    <InputError :message="form.errors.password" class="mt-2" />
                    <!-- Password requirements as errors -->
                    <ul v-if="isPasswordActive" class="mt-2 space-y-1 text-xs text-red-500">
                        <li v-if="!passwordRequirements.minLength">Minimum 8 characters</li>
                        <li v-if="!passwordRequirements.hasUpper">At least one uppercase letter</li>
                        <li v-if="!passwordRequirements.hasLower">At least one lowercase letter</li>
                        <li v-if="!passwordRequirements.hasNumber">At least one number</li>
                        <li v-if="!passwordRequirements.hasSymbol">At least one symbol</li>
                        <li v-if="form.password && !passwordRequirements.matches">Passwords match</li>
                    </ul>
                </div>
                
                <!-- Password confirmation field -->
                <div class="md:col-span-1">
                    <InputLabel
                        for="password_confirmation"
                        value="Confirm Password"
                        class="text-gray-700 dark:text-gray-300 font-medium"
                    />
                    <div class="relative">
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 rounded-md shadow-sm pr-10 dark:bg-gray-700 dark:text-gray-300"
                            autocomplete="new-password"
                            @focus="handleFocus('confirm')"
                            @blur="handleBlur($event)"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pt-1">
                            <!-- Show toggle button only when field is focused -->
                            <button
                                v-if="focusedField === 'confirm'"
                                type="button"
                                @click="toggleConfirmPassword"
                                @mousedown="handleToggleMouseDown"
                                class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 focus:outline-none focus:text-gray-600 dark:focus:text-gray-400 transition-colors duration-200"
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
                                class="h-5 w-5 text-gray-400 dark:text-gray-500"
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
            <div class="py-3 px-4 bg-gray-50 dark:bg-gray-700 rounded-md border border-gray-100 dark:border-gray-600">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password Requirements:</h3>
                <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
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

            <div class="flex items-center pt-4 border-t border-gray-100 dark:border-gray-700">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="bg-green-500 hover:bg-green-600 focus:bg-green-600 inline-flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
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