<template>
    <AuthenticatedLayout :user="auth.user">
        <Head :title="studentOrg.name" />

        <!-- Color banner -->
        <div class="flex w-full overflow-hidden">
            <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
            <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
            <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
            <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
        </div>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            
                <div class="mb-6">
                    <Link :href="route('student-orgs.index')" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Student Organizations
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="h-2 w-full" :class="{
                        'bg-green-500': studentOrg.status === 'active',
                        'bg-red-500': studentOrg.status !== 'active'
                    }"></div>

                    <div class="p-8">
                        <div class="lg:flex lg:items-start">
                            <div v-if="studentOrg.logo_path" class="lg:mr-8 mb-6 lg:mb-0 flex-shrink-0">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                                    <img
                                        :src="`/storage/${studentOrg.logo_path}`"
                                        :alt="`${studentOrg.name} logo`"
                                        class="h-40 w-40 object-contain"
                                    />
                                </div>
                            </div>

                            <div class="flex-grow">
                                <div class="flex flex-wrap items-center gap-3 mb-2">
                                    <span
                                        class="px-3 py-1.5 rounded-full text-sm font-medium"
                                        :class="studentOrg.status === 'active'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800'"
                                    >
                                        {{ studentOrg.status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                    
                                    <Link :href="route('colleges.show', studentOrg.college.id)" class="inline-flex items-center text-sm bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full hover:bg-blue-100 transition duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a2 2 0 012-2h6a2 2 0 012 2v5" />
                                        </svg>
                                        {{ studentOrg.college.name }}
                                    </Link>
                                </div>

                                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ studentOrg.name }}</h1>
                                
                                <div v-if="studentOrg.acronym" class="text-xl text-gray-500 mb-6">
                                    {{ studentOrg.acronym }}
                                </div>

                                <div class="mt-8">
                                    <h2 class="text-xl font-semibold mb-4 flex items-center">
                                        <span class="w-1 h-6 bg-blue-500 mr-2 rounded"></span>
                                        About the Organization
                                    </h2>
                                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                                        <p v-if="studentOrg.description" class="text-gray-700">
                                            {{ studentOrg.description }}
                                        </p>
                                        <p v-else class="text-gray-500 italic">
                                            No description available
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps } from 'vue';

defineProps({
    auth: Object,
    studentOrg: Object,
});
</script>