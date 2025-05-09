<template>
    <AuthenticatedLayout :user="auth.user">
        <Head :title="college.name" />

        <div class="py-8 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Animated colored banner -->
                <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
                    <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
                    <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
                    <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
                    <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
                </div>

                <!-- Back button -->
                <div class="mb-6">
                    <Link :href="route('colleges.index')" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Colleges
                    </Link>
                </div>

                <!-- College details card -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <!-- College header with custom accent color -->
                    <div class="px-6 py-4 border-b border-gray-100 bg-white relative">
                        <div class="absolute top-0 left-0 w-full h-1" :class="{
                            'bg-blue-500': college.id % 4 === 0,
                            'bg-green-500': college.id % 4 === 1,  
                            'bg-yellow-500': college.id % 4 === 2,
                            'bg-red-500': college.id % 4 === 3,
                        }"></div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-2">
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">{{ college.name }}</h1>
                                <div v-if="college.acronym" class="text-gray-600 text-sm sm:text-base mt-1">
                                    {{ college.acronym }}
                                </div>
                            </div>
                            <div class="mt-2 sm:mt-0">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ college.student_orgs.length }} {{ college.student_orgs.length === 1 ? 'Organization' : 'Organizations' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- College description -->
                    <div class="px-6 py-4" v-if="college.description">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">About</h2>
                        <p class="text-gray-600">{{ college.description }}</p>
                    </div>
                    <div class="px-6 py-4 italic text-gray-500" v-else>
                        No description available for this college.
                    </div>
                </div>

                <!-- Student Organizations section -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Student Organizations</h2>
                    </div>

                    <div v-if="college.student_orgs.length === 0" class="bg-white rounded-xl shadow-md p-8 text-center">
                        <div class="text-gray-500">No student organizations found for this college.</div>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="org in college.student_orgs"
                            :key="org.id"
                            class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col"
                        >
                            <!-- Status indicator at top -->
                            <div class="w-full h-1.5" :class="org.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></div>
                            
                            <div class="p-5 flex-1 flex flex-col">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-semibold text-gray-800">
                                        <Link 
                                            :href="route('student-orgs.show', org.id)" 
                                            class="hover:text-blue-600 transition-colors"
                                        >
                                            {{ org.name }}
                                        </Link>
                                    </h3>
                                    <span v-if="org.acronym" 
                                          class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-md">
                                        {{ org.acronym }}
                                    </span>
                                </div>
                                
                                <!-- Logo centered -->
                                <div v-if="org.logo_path" class="flex justify-center my-4">
                                    <img
                                        :src="`/storage/${org.logo_path}`"
                                        :alt="`${org.name} logo`"
                                        class="h-20 w-auto object-contain"
                                    />
                                </div>
                                
                                <p v-if="org.description" class="text-gray-600 text-sm mt-3 line-clamp-3">
                                    {{ org.description }}
                                </p>
                                <div v-else class="text-gray-400 italic text-sm mt-3">No description available</div>
                                
                                <div class="mt-auto pt-4 flex items-center justify-between">
                                    <span
                                        class="inline-flex items-center text-xs px-2 py-1 rounded-full font-medium"
                                        :class="org.status === 'active'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800'"
                                    >
                                        <span class="w-2 h-2 rounded-full mr-1.5" 
                                              :class="org.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></span>
                                        {{ org.status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                    
                                    <Link
                                        :href="route('student-orgs.show', org.id)"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
                                    >
                                        Details
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
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
    college: Object,
});
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
  
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>