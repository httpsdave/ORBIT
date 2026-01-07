<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Two-Factor Authentication
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Add additional security to your account using two-factor authentication.
            </p>
        </header>

        <!-- 2FA Not Enabled -->
        <div v-if="!twoFactorEnabled" class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. 
                You may retrieve this token from your phone's Google Authenticator, Microsoft Authenticator, or similar TOTP application.
            </p>

            <PrimaryButton
                @click="enableTwoFactor"
                type="button"
                :disabled="enabling"
                class="relative inline-flex items-center"
            >
                <svg v-if="enabling" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Enable 2FA
            </PrimaryButton>
        </div>

        <!-- 2FA Enabled -->
        <div v-else class="space-y-6">
            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            Two-factor authentication is enabled.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    @click="showRecoveryCodes"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group"
                >
                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                    View Recovery Codes
                </button>

                <button
                    @click="regenerateCodes"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl shadow-md hover:bg-gray-50 dark:hover:bg-gray-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group"
                    :class="{ 'opacity-50 cursor-not-allowed': regenerating }"
                    :disabled="regenerating"
                >
                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                    <svg v-if="regenerating" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Regenerate Recovery Codes
                </button>

                <button
                    @click="disableTwoFactor"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-red-300/30 hover:from-red-400 hover:to-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:from-red-600 active:to-red-700 transition-all duration-300 relative overflow-hidden group"
                    :class="{ 'opacity-50 cursor-not-allowed': disabling }"
                    :disabled="disabling"
                >
                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                    <svg v-if="disabling" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Disable
                </button>
            </div>
        </div>

        <!-- Enable 2FA Modal -->
        <div v-if="showEnableModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeEnableModal"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl transform transition-all p-6">
                    <div>
                        <div class="text-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                {{ confirmingSetup ? 'Confirm Two-Factor Authentication' : 'Enable Two-Factor Authentication' }}
                            </h3>

                            <!-- Step 1: Password Confirmation -->
                            <div v-if="!confirmingSetup" class="mt-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Please confirm your password to continue.
                                </p>

                                <!-- Error Alert -->
                                <div v-if="passwordError" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-4 w-4 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-2">
                                            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                                {{ passwordError }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <input
                                        v-model="passwordInput"
                                        type="password"
                                        placeholder="Password"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 rounded-md shadow-sm"
                                        :class="{ 'border-red-300 dark:border-red-700 focus:border-red-500 focus:ring-red-500': passwordError }"
                                        @keyup.enter="confirmPassword"
                                    />
                                </div>
                            </div>

                            <!-- Step 2: QR Code and Confirmation -->
                            <div v-else class="mt-4 space-y-4">
                                <div v-if="qrCode" class="space-y-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Scan this QR code with your authenticator app
                                    </p>

                                    <div class="flex justify-center">
                                        <img :src="qrCode" alt="QR Code" class="w-48 h-48 sm:w-56 sm:h-56 border-4 border-gray-200 dark:border-gray-700 rounded-lg" />
                                    </div>

                                    <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            Or enter this code manually:
                                        </p>
                                        <code class="text-xs sm:text-sm font-mono text-gray-900 dark:text-gray-100 break-all">
                                            {{ secret }}
                                        </code>
                                    </div>

                                    <!-- Recovery Codes -->
                                    <div v-if="recoveryCodes.length > 0" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">
                                            Save these recovery codes:
                                        </p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-mono text-yellow-900 dark:text-yellow-100">
                                            <div v-for="code in recoveryCodes" :key="code" class="bg-white dark:bg-gray-800 p-2 rounded break-all">
                                                {{ code }}
                                            </div>
                                        </div>
                                        <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-2">
                                            These codes can be used if you lose your authenticator device.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left mb-2">
                                            Enter the 6-digit code:
                                        </label>
                                        <input
                                            v-model="confirmForm.code"
                                            type="text"
                                            inputmode="numeric"
                                            pattern="[0-9]*"
                                            maxlength="6"
                                            placeholder="000000"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 rounded-md shadow-sm text-center text-lg sm:text-2xl tracking-widest"
                                            @keyup.enter="confirmTwoFactor"
                                        />
                                        <p v-if="confirmForm.errors.code" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                            {{ confirmForm.errors.code }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2 sm:space-y-0 sm:flex sm:gap-3 sm:flex-row-reverse">
                        <button
                            v-if="!confirmingSetup"
                            @click="confirmPassword"
                            type="button"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-base font-medium text-white hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
                            :class="{ 'opacity-50 cursor-not-allowed': passwordProcessing }"
                            :disabled="passwordProcessing"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <svg v-if="passwordProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Continue
                        </button>

                        <button
                            v-else
                            @click="confirmTwoFactor"
                            type="button"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-base font-medium text-white hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
                            :class="{ 'opacity-50 cursor-not-allowed': confirmForm.processing }"
                            :disabled="confirmForm.processing"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <svg v-if="confirmForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Confirm & Enable
                        </button>

                        <button
                            @click="closeEnableModal"
                            type="button"
                            class="w-full inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 shadow-md px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm transition-all duration-300 relative overflow-hidden group"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recovery Codes Modal -->
        <div v-if="showRecoveryCodesModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showRecoveryCodesModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                Recovery Codes
                            </h3>

                            <div class="mt-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Store these recovery codes in a secure location. They can be used to access your account if you lose your authenticator device.
                                </p>

                                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                                    <div class="grid grid-cols-2 gap-2 text-sm font-mono text-gray-900 dark:text-gray-100">
                                        <div v-for="code in recoveryCodes" :key="code" class="bg-white dark:bg-gray-800 p-2 rounded border border-gray-200 dark:border-gray-700">
                                            {{ code }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6">
                        <button
                            @click="showRecoveryCodesModal = false"
                            type="button"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-base font-medium text-white hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disable 2FA Modal -->
        <div v-if="showDisableModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDisableModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                Disable Two-Factor Authentication
                            </h3>

                            <div class="mt-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Please confirm your password to disable two-factor authentication.
                                </p>

                                <input
                                    v-model="disableForm.password"
                                    type="password"
                                    placeholder="Password"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 rounded-md shadow-sm"
                                    @keyup.enter="confirmDisable"
                                />
                                <p v-if="disableForm.errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ disableForm.errors.password }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button
                            @click="confirmDisable"
                            type="button"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-md px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-base font-medium text-white hover:shadow-red-300/30 hover:from-red-400 hover:to-red-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm active:from-red-600 active:to-red-700 transition-all duration-300 relative overflow-hidden group"
                            :class="{ 'opacity-50 cursor-not-allowed': disableForm.processing }"
                            :disabled="disableForm.processing"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <svg v-if="disableForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Disable 2FA
                        </button>

                        <button
                            @click="showDisableModal = false"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 dark:border-gray-600 shadow-md px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm transition-all duration-300 relative overflow-hidden group"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import axios from 'axios';

const page = usePage();

const twoFactorEnabled = computed(() => page.props.auth?.user?.two_factor_enabled ?? false);

const enabling = ref(false);
const disabling = ref(false);
const regenerating = ref(false);
const confirmingSetup = ref(false);
const showEnableModal = ref(false);
const showDisableModal = ref(false);
const showRecoveryCodesModal = ref(false);

const qrCode = ref('');
const secret = ref('');
const recoveryCodes = ref([]);

// Use plain refs for password form to work better with axios
const passwordInput = ref('');
const passwordError = ref('');
const passwordProcessing = ref(false);

const confirmForm = useForm({
    code: '',
});

const disableForm = useForm({
    password: '',
});

const enableTwoFactor = () => {
    showEnableModal.value = true;
    passwordInput.value = '';
    passwordError.value = '';
};

const confirmPassword = async () => {
    if (passwordProcessing.value) return;
    
    passwordProcessing.value = true;
    passwordError.value = '';

    try {
        const response = await axios.post(route('two-factor.enable'), {
            password: passwordInput.value
        });

        console.log('Response:', response.data);

        if (response.data.success) {
            qrCode.value = response.data.qr_code_url;
            secret.value = response.data.secret;
            recoveryCodes.value = response.data.recovery_codes;
            confirmingSetup.value = true;
            passwordInput.value = '';
        }
    } catch (error) {
        console.error('Error:', error);
        if (error.response?.data?.errors?.password) {
            passwordError.value = error.response.data.errors.password[0];
        } else {
            passwordError.value = error.response?.data?.message || 'An error occurred';
        }
        passwordInput.value = '';
    } finally {
        passwordProcessing.value = false;
    }
};

const confirmTwoFactor = () => {
    confirmForm.post(route('two-factor.confirm'), {
        preserveScroll: true,
        onSuccess: () => {
            closeEnableModal();
            confirmForm.code = '';
            // Reload the page to refresh the user state
            window.location.reload();
        },
        onError: () => {
            confirmForm.code = '';
        }
    });
};

const closeEnableModal = () => {
    showEnableModal.value = false;
    confirmingSetup.value = false;
    qrCode.value = '';
    secret.value = '';
    passwordInput.value = '';
    passwordError.value = '';
    confirmForm.code = '';
    confirmForm.errors = {};
};

const disableTwoFactor = () => {
    showDisableModal.value = true;
    disableForm.password = '';
    disableForm.errors = {};
};

const confirmDisable = () => {
    disableForm.post(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            showDisableModal.value = false;
            disableForm.password = '';
            recoveryCodes.value = [];
            // Reload the page to refresh the user state
            window.location.reload();
        },
        onError: () => {
            disableForm.password = '';
        }
    });
};

const showRecoveryCodes = () => {
    // Recovery codes are already stored, just display them
    if (recoveryCodes.value.length === 0 && page.props.auth?.user?.two_factor_recovery_codes) {
        recoveryCodes.value = page.props.auth.user.two_factor_recovery_codes;
    }
    showRecoveryCodesModal.value = true;
};

const regenerateCodes = async () => {
    if (regenerating.value) return;

    const password = prompt('Please enter your password to regenerate recovery codes:');
    
    if (!password) return;

    regenerating.value = true;

    try {
        const response = await axios.post(route('two-factor.regenerate-recovery-codes'), {
            password: password
        });

        recoveryCodes.value = response.data.recovery_codes;
        showRecoveryCodesModal.value = true;
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to regenerate recovery codes. Please check your password.');
    } finally {
        regenerating.value = false;
    }
};
</script>
