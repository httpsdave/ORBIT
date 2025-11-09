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

        <div class="py-4 px-3 sm:py-8 sm:px-6 lg:px-8">
            
                <div class="mb-4 sm:mb-6">
                    <Link :href="route('student-orgs.index')" class="inline-flex items-center transition duration-300" :class="isDarkMode ? 'text-blue-400 hover:text-blue-300' : 'text-blue-600 hover:text-blue-800'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Student Organizations
                    </Link>
                </div>

                <div class="rounded-lg shadow-sm overflow-hidden" :class="isDarkMode ? 'bg-gray-800' : 'bg-white'">
                    <div class="h-2 w-full" :class="{
                        'bg-green-500': studentOrg.status === 'active',
                        'bg-red-500': studentOrg.status !== 'active'
                    }"></div>

                    <div class="p-4 sm:p-6 lg:p-8">
                        <div class="lg:flex lg:items-start">
                            <div v-if="studentOrg.logo_path" class="lg:mr-8 mb-4 sm:mb-6 lg:mb-0 flex-shrink-0">
                                <div class="p-3 sm:p-4 rounded-lg border" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-gray-50 border-gray-100'">
                                    <img
                                        :src="`/storage/${studentOrg.logo_path}`"
                                        :alt="`${studentOrg.name} logo`"
                                        class="h-32 w-32 sm:h-40 sm:w-40 object-contain"
                                    />
                                </div>
                            </div>

                            <div class="flex-grow">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                                    <span
                                        class="px-3 py-1.5 rounded-full text-sm font-medium"
                                        :class="studentOrg.status === 'active'
                                            ? (isDarkMode ? 'bg-green-900 text-green-200' : 'bg-green-100 text-green-800')
                                            : (isDarkMode ? 'bg-red-900 text-red-200' : 'bg-red-100 text-red-800')"
                                    >
                                        {{ studentOrg.status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                    
                                    <Link v-if="studentOrg.college" :href="route('colleges.show', studentOrg.college.id)" class="inline-flex items-center text-sm px-3 py-1.5 rounded-full transition duration-300" :class="isDarkMode ? 'bg-blue-900 text-blue-200 hover:bg-blue-800' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a2 2 0 012-2h6a2 2 0 012 2v5" />
                                        </svg>
                                        {{ studentOrg.college.name }}
                                    </Link>
                                    
                                    <span v-else class="inline-flex items-center text-sm px-3 py-1.5 rounded-full" :class="isDarkMode ? 'bg-purple-900 text-purple-200' : 'bg-purple-50 text-purple-700'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Non-College
                                    </span>
                                </div>

                                <h1 class="text-2xl sm:text-3xl font-bold mb-2" :class="isDarkMode ? 'text-white' : 'text-gray-900'">{{ studentOrg.name }}</h1>
                                
                                <div v-if="studentOrg.email" class="text-lg sm:text-xl mb-2 break-words" :class="[isDarkMode ? 'text-gray-400' : 'text-gray-500']">
                                    {{ studentOrg.email }}
                                </div>

                                <div v-if="studentOrg.acronym" class="text-lg sm:text-xl mb-4 sm:mb-6" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                                    {{ studentOrg.acronym }}
                                </div>

                                <div class="mt-6 sm:mt-8">
                                    <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4 flex items-center" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
                                        <span class="w-1 h-6 bg-blue-500 mr-2 rounded"></span>
                                        About the Organization
                                    </h2>
                                    <div class="p-4 sm:p-6 rounded-lg border" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-gray-50 border-gray-100'">
                                        <div v-if="organizationDetails.president_name">
                                            <p class="text-sm font-medium mb-2" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                                                Current President:
                                            </p>
                                            <p class="text-lg font-semibold" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">
                                                {{ organizationDetails.president_name }}
                                            </p>
                                        </div>
                                        <p v-else :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'" class="italic">
                                            No president information available
                                        </p>
                                    </div>
                                </div>

                                <!-- Social Links Section -->
                                <div v-if="studentOrg.social_links && studentOrg.social_links.length > 0" class="mt-6 sm:mt-8">
                                        <h2 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 flex items-center" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
                                            <span class="w-1 h-6 bg-blue-500 mr-2 rounded"></span>
                                            Connect With Us
                                        </h2>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                                            <a 
                                                v-for="link in studentOrg.social_links" 
                                                :key="link.platform"
                                                :href="link.url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="flex items-center gap-3 p-3 sm:p-4 rounded-lg border transition-all duration-200 hover:shadow-md"
                                                :class="[
                                                    isDarkMode ? 'bg-gray-700 border-gray-600 hover:border-blue-400' : 'bg-white border-gray-200 hover:border-blue-500'
                                                ]"
                                            >
                                                <!-- Platform Icon -->
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                                                    :class="{
                                                        [isDarkMode ? 'bg-blue-900/50' : 'bg-blue-100']: link.platform.toLowerCase() === 'facebook',
                                                        [isDarkMode ? 'bg-sky-900/50' : 'bg-sky-100']: link.platform.toLowerCase() === 'twitter',
                                                        [isDarkMode ? 'bg-pink-900/50' : 'bg-pink-100']: link.platform.toLowerCase() === 'instagram',
                                                        [isDarkMode ? 'bg-red-900/50' : 'bg-red-100']: link.platform.toLowerCase() === 'youtube',
                                                        [isDarkMode ? 'bg-blue-900/50' : 'bg-blue-100']: link.platform.toLowerCase() === 'linkedin',
                                                        [isDarkMode ? 'bg-purple-900/50' : 'bg-purple-100']: link.platform.toLowerCase() === 'tiktok',
                                                        [isDarkMode ? 'bg-gray-900/50' : 'bg-gray-100']: !['facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'tiktok'].includes(link.platform.toLowerCase())
                                                    }"
                                                >
                                                    <!-- Facebook -->
                                                    <svg v-if="link.platform.toLowerCase() === 'facebook'" class="w-5 h-5" :class="isDarkMode ? 'text-blue-400' : 'text-blue-600'" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                    </svg>
                                                    <!-- Twitter -->
                                                    <svg v-else-if="link.platform.toLowerCase() === 'twitter'" class="w-5 h-5" :class="isDarkMode ? 'text-sky-400' : 'text-sky-500'" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                                    </svg>
                                                    <!-- Instagram -->
                                                    <svg v-else-if="link.platform.toLowerCase() === 'instagram'" class="w-5 h-5" :class="isDarkMode ? 'text-pink-400' : 'text-pink-600'" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                                    </svg>
                                                    <!-- YouTube -->
                                                    <svg v-else-if="link.platform.toLowerCase() === 'youtube'" class="w-5 h-5" :class="isDarkMode ? 'text-red-400' : 'text-red-600'" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                    </svg>
                                                    <!-- LinkedIn -->
                                                    <svg v-else-if="link.platform.toLowerCase() === 'linkedin'" class="w-5 h-5" :class="isDarkMode ? 'text-blue-400' : 'text-blue-700'" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                                    </svg>
                                                    <!-- TikTok -->
                                                    <svg v-else-if="link.platform.toLowerCase() === 'tiktok'" class="w-5 h-5" :class="isDarkMode ? 'text-white' : 'text-gray-900'" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                                    </svg>
                                                    <!-- Website/Other -->
                                                    <svg v-else class="w-5 h-5" :class="isDarkMode ? 'text-gray-400' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                                    </svg>
                                                </div>
                                                <!-- Platform Info -->
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium capitalize" :class="isDarkMode ? 'text-white' : 'text-gray-900'">{{ link.platform }}</p>
                                                    <p class="text-sm truncate" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">Visit our page</p>
                                                </div>
                                                <!-- Arrow Icon -->
                                                <svg class="w-5 h-5 flex-shrink-0" :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Empty state when no social links -->
                                    <div v-else class="text-center py-8 sm:py-12 rounded-lg border" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-gray-50 border-gray-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 sm:h-16 sm:w-16 mx-auto mb-3 sm:mb-4" :class="isDarkMode ? 'text-gray-600' : 'text-gray-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                        </svg>
                                        <h3 class="text-base sm:text-lg font-medium mb-2 px-4" :class="isDarkMode ? 'text-gray-300' : 'text-gray-700'">No Social Links Available</h3>
                                        <p class="text-sm sm:text-base px-4" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                                            This organization hasn't added any social media links yet.
                                        </p>
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
import { useTheme } from '@/Composables/useTheme';

const { isDark: isDarkMode } = useTheme();

defineProps({
    auth: Object,
    studentOrg: Object,
    organizationDetails: Object,
});
</script>