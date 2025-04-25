<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Administrator Control Panel</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 p-4 rounded-lg shadow">
                                <h4 class="font-medium text-blue-800">User Management</h4>
                                <p class="text-sm text-gray-600 my-2">Manage system users and roles</p>
                                <a :href="route('admin.users')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Manage Users →
                                </a>
                            </div>
                            
                            <div class="bg-purple-50 p-4 rounded-lg shadow">
                                <h4 class="font-medium text-purple-800">Applications</h4>
                                <p class="text-sm text-gray-600 my-2">Review and approve applications</p>
                                <a :href="route('applications.index')" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                    View Applications →
                                </a>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg shadow">
                                <h4 class="font-medium text-green-800">System Settings</h4>
                                <p class="text-sm text-gray-600 my-2">Configure application settings</p>
                                <span class="text-green-600 text-sm font-medium">
                                    Settings →
                                </span>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg shadow">
                            <h4 class="font-medium text-gray-800">Admin Notes</h4>
                            <p class="text-sm text-gray-600 my-2">This is your admin dashboard. You have access to all system features and settings.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>