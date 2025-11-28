<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateFormDefaultsForm from './Partials/UpdateFormDefaultsForm.vue';
import TwoFactorAuthenticationForm from './Partials/TwoFactorAuthenticationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Check if user is admin
const isAdmin = computed(() => {
    return user.value.role?.slug === 'admin' || user.value.is_admin;
});

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                Profile
            </h2>
        </template>

        <div class="py-12 bg-gray-50 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Colored banner -->
                <div class="flex w-full overflow-hidden rounded-lg shadow-lg">
                    <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
                    <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
                    <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
                    <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
                </div>
                
                <!-- Main Content Grid - Two Column Layout for Large Screens -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Profile Information Section -->
                        <div class="bg-white dark:bg-gray-800 p-6 shadow-md rounded-lg border-l-4 border-blue-500 transition-all duration-300 hover:shadow-xl h-fit">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profile Information
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update your account's profile information and email address.</p>
                            </div>
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                                class="w-full"
                            />
                        </div>

                        <!-- Form Default Values Section - Admin Only -->
                        <div v-if="isAdmin" class="bg-white dark:bg-gray-800 p-6 shadow-md rounded-lg border-l-4 border-amber-500 transition-all duration-300 hover:shadow-xl h-fit">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Form Default Values
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Set global default values for coordinator and director names that will auto-populate in all users' forms.</p>
                            </div>
                            <UpdateFormDefaultsForm class="w-full" />
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Update Password Section -->
                        <div class="bg-white dark:bg-gray-800 p-6 shadow-md rounded-lg border-l-4 border-green-500 transition-all duration-300 hover:shadow-xl h-fit">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Update Password
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ensure your account is using a long, random password to stay secure.</p>
                            </div>
                            <UpdatePasswordForm class="w-full" />
                        </div>

                        <!-- Two-Factor Authentication Section -->
                        <div class="bg-white dark:bg-gray-800 p-6 shadow-md rounded-lg border-l-4 border-purple-500 transition-all duration-300 hover:shadow-xl h-fit">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
                                    </svg>
                                    Two-Factor Authentication
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Add an extra layer of security to your account.</p>
                            </div>
                            <TwoFactorAuthenticationForm class="w-full" />
                        </div>
                    </div>
                </div>
                
                <!-- Colored banner at the bottom -->
                <div class="flex w-full overflow-hidden rounded-lg shadow-lg">
                    <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
                    <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
                    <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
                    <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>