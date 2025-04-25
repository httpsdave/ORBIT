<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Welcome back, {{ $page.props.auth.user.name }}</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg shadow">
                                <h4 class="font-medium text-blue-800">My Applications</h4>
                                <p class="text-sm text-gray-600 my-2">View and manage your applications</p>
                                <div class="flex space-x-2 mt-2">
                                    <a :href="route('applications.index')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View All →
                                    </a>
                                    <a :href="route('applications.create')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Create New →
                                    </a>
                                </div>
                            </div>
                            
                            <div class="bg-amber-50 p-4 rounded-lg shadow">
                                <h4 class="font-medium text-amber-800">Profile Information</h4>
                                <p class="text-sm text-gray-600 my-2">Update your account information</p>
                                <a :href="route('profile.edit')" class="text-amber-600 hover:text-amber-800 text-sm font-medium">
                                    Edit Profile →
                                </a>
                            </div>
                        </div>
                        
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg shadow">
                            <h4 class="font-medium text-gray-800">Quick Links</h4>
                            <div class="flex flex-wrap gap-4 mt-2">
                                <a :href="route('myhome')" class="text-gray-600 hover:text-gray-800 text-sm">
                                    Home
                                </a>
                                <a :href="route('aboutUs')" class="text-gray-600 hover:text-gray-800 text-sm">
                                    About Us
                                </a>
                                <a :href="route('contactUs')" class="text-gray-600 hover:text-gray-800 text-sm">
                                    Contact
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>