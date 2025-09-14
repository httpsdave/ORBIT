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

                    <div class="p-8">
                        <div class="lg:flex lg:items-start">
                            <div v-if="studentOrg.logo_path" class="lg:mr-8 mb-6 lg:mb-0 flex-shrink-0">
                                <div class="p-4 rounded-lg border" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-gray-50 border-gray-100'">
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
                                            ? (isDarkMode ? 'bg-green-900 text-green-200' : 'bg-green-100 text-green-800')
                                            : (isDarkMode ? 'bg-red-900 text-red-200' : 'bg-red-100 text-red-800')"
                                    >
                                        {{ studentOrg.status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                    
                                    <Link :href="route('colleges.show', studentOrg.college.id)" class="inline-flex items-center text-sm px-3 py-1.5 rounded-full transition duration-300" :class="isDarkMode ? 'bg-blue-900 text-blue-200 hover:bg-blue-800' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a2 2 0 012-2h6a2 2 0 012 2v5" />
                                        </svg>
                                        {{ studentOrg.college.name }}
                                    </Link>
                                </div>

                                <h1 class="text-3xl font-bold mb-2" :class="isDarkMode ? 'text-white' : 'text-gray-900'">{{ studentOrg.name }}</h1>
                                
                                <div v-if="studentOrg.email" class="text-xl mb-2" :class="[isDarkMode ? 'text-gray-400' : 'text-gray-500']">
                                    {{ studentOrg.email }}
                                </div>

                                <div v-if="studentOrg.acronym" class="text-xl mb-6" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                                    {{ studentOrg.acronym }}
                                </div>

                                <div class="mt-8">
                                    <h2 class="text-xl font-semibold mb-4 flex items-center" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
                                        <span class="w-1 h-6 bg-blue-500 mr-2 rounded"></span>
                                        About the Organization
                                    </h2>
                                    <div class="p-6 rounded-lg border" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-gray-50 border-gray-100'">
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

                                <!-- Organization Details Section -->
                                <div v-if="organizationDetails.has_approved_data" class="mt-8">
                                    <h2 class="text-xl font-semibold mb-6 flex items-center" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
                                        <span class="w-1 h-6 bg-green-500 mr-2 rounded"></span>
                                        Organization Information
                                    </h2>
                                    
                                    <!-- Stats Cards -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                        <!-- Adviser Card -->
                                        <div class="rounded-lg border p-6" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-200'">
                                            <div class="flex items-center mb-4">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3" :class="isDarkMode ? 'bg-blue-900/50' : 'bg-blue-100'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="isDarkMode ? 'text-blue-400' : 'text-blue-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-medium" :class="isDarkMode ? 'text-white' : 'text-gray-900'">Adviser</h3>
                                                    <p class="text-sm" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">Primary</p>
                                                </div>
                                            </div>
                                            <p class="font-semibold" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">
                                                {{ organizationDetails.adviser_name || 'Not specified' }}
                                            </p>
                                            <div v-if="organizationDetails.second_adviser" class="mt-3 pt-3 border-t" :class="isDarkMode ? 'border-gray-600' : 'border-gray-200'">
                                                <p class="text-sm" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">Second Adviser:</p>
                                                <p class="font-medium" :class="isDarkMode ? 'text-gray-300' : 'text-gray-700'">{{ organizationDetails.second_adviser }}</p>
                                            </div>
                                        </div>

                                        <!-- Members Count Card -->
                                        <div class="rounded-lg border p-6" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-200'">
                                            <div class="flex items-center mb-4">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3" :class="isDarkMode ? 'bg-green-900/50' : 'bg-green-100'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="isDarkMode ? 'text-green-400' : 'text-green-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-medium" :class="isDarkMode ? 'text-white' : 'text-gray-900'">Members</h3>
                                                    <p class="text-sm" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">Total count</p>
                                                </div>
                                            </div>
                                            <p class="text-3xl font-bold" :class="isDarkMode ? 'text-green-400' : 'text-green-600'">
                                                {{ organizationDetails.members_count || 0 }}
                                            </p>
                                        </div>

                                        <!-- Officers Count Card -->
                                        <div class="rounded-lg border p-6" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-200'">
                                            <div class="flex items-center mb-4">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3" :class="isDarkMode ? 'bg-purple-900/50' : 'bg-purple-100'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="isDarkMode ? 'text-purple-400' : 'text-purple-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="font-medium" :class="isDarkMode ? 'text-white' : 'text-gray-900'">Officers</h3>
                                                    <p class="text-sm" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">Leadership</p>
                                                </div>
                                            </div>
                                            <p class="text-3xl font-bold" :class="isDarkMode ? 'text-purple-400' : 'text-purple-600'">
                                                {{ organizationDetails.officers_count || 0 }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Members List -->
                                    <div v-if="organizationDetails.members && organizationDetails.members.length > 0" class="mb-8">
                                        <h3 class="text-lg font-semibold mb-4 flex items-center" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
                                            <span class="w-1 h-5 bg-green-500 mr-2 rounded"></span>
                                            Members ({{ organizationDetails.members.length }})
                                        </h3>
                                        <div class="rounded-lg border overflow-hidden" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-200'">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y" :class="isDarkMode ? 'divide-gray-600' : 'divide-gray-200'">
                                                    <thead :class="isDarkMode ? 'bg-gray-800' : 'bg-gray-50'">
                                                        <tr>
                                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="isDarkMode ? 'text-gray-300' : 'text-gray-500'">Name</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y" :class="isDarkMode ? 'divide-gray-600' : 'divide-gray-200'">
                                                        <tr v-for="member in organizationDetails.members" :key="member.id">
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-900'">{{ member.student_name }}</div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Officers List -->
                                    <div v-if="organizationDetails.officers && organizationDetails.officers.length > 0" class="mb-8">
                                        <h3 class="text-lg font-semibold mb-4 flex items-center" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
                                            <span class="w-1 h-5 bg-purple-500 mr-2 rounded"></span>
                                            Officers ({{ organizationDetails.officers.length }})
                                        </h3>
                                        <div class="rounded-lg border overflow-hidden" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-white border-gray-200'">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y" :class="isDarkMode ? 'divide-gray-600' : 'divide-gray-200'">
                                                    <thead :class="isDarkMode ? 'bg-gray-800' : 'bg-gray-50'">
                                                        <tr>
                                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="isDarkMode ? 'text-gray-300' : 'text-gray-500'">Name</th>
                                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" :class="isDarkMode ? 'text-gray-300' : 'text-gray-500'">Position</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y" :class="isDarkMode ? 'divide-gray-600' : 'divide-gray-200'">
                                                        <tr v-for="officer in organizationDetails.officers" :key="officer.id">
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-900'">{{ officer.student_name }}</div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="text-sm font-medium" :class="isDarkMode ? 'text-purple-400' : 'text-purple-600'">{{ officer.position || 'Officer' }}</div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- No Data Available Message -->
                                <div v-else class="mt-8">
                                    <div class="text-center py-12 rounded-lg border" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-gray-50 border-gray-200'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4" :class="isDarkMode ? 'text-gray-600' : 'text-gray-300'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h3 class="text-lg font-medium mb-2" :class="isDarkMode ? 'text-gray-300' : 'text-gray-700'">No Approved Data Available</h3>
                                        <p :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                                            This organization hasn't submitted approved member and officer lists yet.
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
import { useTheme } from '@/Composables/useTheme';

const { isDark: isDarkMode } = useTheme();

defineProps({
    auth: Object,
    studentOrg: Object,
    organizationDetails: Object,
});
</script>