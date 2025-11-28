<template>
    <Head title="Two-Factor Authentication" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-8 text-center">
                    <div class="flex justify-center mb-4">
                        <div class="bg-white rounded-full p-3">
                            <svg class="h-12 w-12 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-white">Two-Factor Authentication</h2>
                    <p class="mt-2 text-sm text-purple-100">
                        {{ useRecoveryCode ? 'Enter one of your recovery codes' : 'Enter the code from your authenticator app' }}
                    </p>
                </div>

                <!-- Form -->
                <div class="px-6 py-8">
                    <form @submit.prevent="submit">
                        <!-- TOTP Code Input -->
                        <div v-if="!useRecoveryCode" class="space-y-4">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Authentication Code
                                </label>
                                <input
                                    id="code"
                                    v-model="form.code"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="6"
                                    placeholder="000000"
                                    autofocus
                                    autocomplete="one-time-code"
                                    class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center text-2xl tracking-widest font-mono"
                                    :class="{ 'border-red-500': form.errors.code }"
                                />
                                <p v-if="form.errors.code" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.code }}
                                </p>
                            </div>

                            <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                Enter the 6-digit code from your authenticator app
                            </p>
                        </div>

                        <!-- Recovery Code Input -->
                        <div v-else class="space-y-4">
                            <div>
                                <label for="recovery_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Recovery Code
                                </label>
                                <input
                                    id="recovery_code"
                                    v-model="form.code"
                                    type="text"
                                    placeholder="XXXXX-XXXXX"
                                    autofocus
                                    autocomplete="one-time-code"
                                    class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center font-mono"
                                    :class="{ 'border-red-500': form.errors.code }"
                                />
                                <p v-if="form.errors.code" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.code }}
                                </p>
                            </div>

                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                <p class="text-xs text-yellow-800 dark:text-yellow-200">
                                    <strong>Warning:</strong> Each recovery code can only be used once. Make sure to generate new codes after using this one.
                                </p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6">
                            <button
                                type="submit"
                                class="w-full flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-150"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                :disabled="form.processing"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Verifying...' : 'Verify' }}
                            </button>
                        </div>

                        <!-- Toggle Recovery Code -->
                        <div class="mt-4 text-center">
                            <button
                                type="button"
                                @click="toggleRecoveryCode"
                                class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-500 dark:hover:text-purple-300 font-medium"
                            >
                                {{ useRecoveryCode ? 'Use authenticator code instead' : 'Use a recovery code' }}
                            </button>
                        </div>

                        <!-- Back to Login -->
                        <div class="mt-6 text-center">
                            <Link
                                :href="route('login')"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                            >
                                ← Back to login
                            </Link>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-400">
                    Having trouble? Contact your system administrator for help.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user_id: {
        type: Number,
        required: true,
    },
    remember: {
        type: Boolean,
        default: false,
    },
});

const useRecoveryCode = ref(false);

const form = useForm({
    code: '',
    user_id: props.user_id,
    remember: props.remember,
});

const toggleRecoveryCode = () => {
    useRecoveryCode.value = !useRecoveryCode.value;
    form.code = '';
    form.errors = {};
};

const submit = () => {
    form.post(route('two-factor.verify'), {
        preserveScroll: true,
        onFinish: () => {
            if (form.hasErrors) {
                form.code = '';
            }
        },
    });
};
</script>
