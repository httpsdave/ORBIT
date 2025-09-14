<template>
    <AuthenticatedLayout>
        <Head :title="studentOrg.name" />

        <!-- Color banner -->
        <div class="flex w-full overflow-hidden">
            <div class="w-1/4 h-1.5 bg-blue-500" style="animation-delay: 0.2s;"></div>
            <div class="w-1/4 h-1.5 bg-green-500" style="animation-delay: 0.4s;"></div>
            <div class="w-1/4 h-1.5 bg-yellow-500" style="animation-delay: 0.6s;"></div>
            <div class="w-1/4 h-1.5 bg-red-500" style="animation-delay: 0.8s;"></div>
        </div>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <Link :href="route('admin.colleges.show', studentOrg.college.id)" class="inline-flex items-center transition duration-300 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to {{ studentOrg.college.name }}
                </Link>
            </div>

            <div class="rounded-lg shadow-sm overflow-hidden bg-white dark:bg-gray-800">
                <div class="h-2 w-full" :class="{
                    'bg-green-500': studentOrg.status === 'active',
                    'bg-red-500': studentOrg.status !== 'active'
                }"></div>

                <div class="p-8">
                    <div class="flex flex-wrap items-center gap-3 mb-2">
                        <span
                            class="px-3 py-1.5 rounded-full text-sm font-medium"
                            :class="studentOrg.status === 'active'
                                ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'"
                        >
                            {{ studentOrg.status === 'active' ? 'Active' : 'Inactive' }}
                        </span>
                        
                        <Link :href="route('admin.colleges.show', studentOrg.college.id)" class="inline-flex items-center text-sm px-3 py-1.5 rounded-full transition duration-300 bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-200 hover:bg-blue-100 dark:hover:bg-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a2 2 0 012-2h6a2 2 0 012 2v5" />
                            </svg>
                            {{ studentOrg.college.name }}
                        </Link>
                    </div>

                    <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">{{ studentOrg.name }}</h1>
                    
                    <div v-if="studentOrg.email" class="text-xl mb-6 text-gray-500 dark:text-gray-400">
                        {{ studentOrg.email }}
                    </div>

                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-4 flex items-center text-gray-900 dark:text-white">
                            <span class="w-1 h-6 bg-blue-500 mr-2 rounded"></span>
                            About the Organization
                        </h2>
                        <div class="p-6 rounded-lg border bg-gray-50 dark:bg-gray-700 border-gray-100 dark:border-gray-600">
                            <div v-if="organizationDetails.president_name">
                                <p class="text-sm font-medium mb-2 text-gray-500 dark:text-gray-400">
                                    Current President:
                                </p>
                                <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                    {{ organizationDetails.president_name }}
                                </p>
                            </div>
                            <p v-else class="text-gray-500 dark:text-gray-400 italic">
                                No president information available
                            </p>
                        </div>
                    </div>

                    <!-- Organization Details Section -->
                    <div v-if="organizationDetails.has_approved_data" class="mt-8">
                        <h2 class="text-xl font-semibold mb-6 flex items-center text-gray-900 dark:text-white">
                            <span class="w-1 h-6 bg-green-500 mr-2 rounded"></span>
                            Organization Information
                        </h2>
                        
                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Adviser Card -->
                            <div class="rounded-lg border p-6 bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 bg-blue-100 dark:bg-blue-900/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900 dark:text-white">Adviser</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Primary</p>
                                    </div>
                                </div>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">
                                    {{ organizationDetails.adviser_name || 'Not specified' }}
                                </p>
                                <div v-if="organizationDetails.second_adviser" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Second Adviser:</p>
                                    <p class="font-medium text-gray-700 dark:text-gray-300">{{ organizationDetails.second_adviser }}</p>
                                </div>
                            </div>

                            <!-- Members Count Card -->
                            <div class="rounded-lg border p-6 bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 bg-green-100 dark:bg-green-900/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900 dark:text-white">Members</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Total count</p>
                                    </div>
                                </div>
                                <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                                    {{ organizationDetails.members_count || 0 }}
                                </p>
                            </div>

                            <!-- Officers Count Card -->
                            <div class="rounded-lg border p-6 bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 bg-purple-100 dark:bg-purple-900/50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900 dark:text-white">Officers</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Leadership</p>
                                    </div>
                                </div>
                                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ organizationDetails.officers_count || 0 }}
                                </p>
                            </div>
                        </div>

                        <!-- Members List -->
                        <div v-if="organizationDetails.members && organizationDetails.members.length > 0" class="mb-8">
                            <h3 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-white">
                                <span class="w-1 h-5 bg-green-500 mr-2 rounded"></span>
                                Members ({{ organizationDetails.members.length }})
                            </h3>
                            <div class="rounded-lg border overflow-hidden bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Name</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                            <tr v-for="member in organizationDetails.members" :key="member.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="font-medium text-gray-900 dark:text-gray-200">{{ member.student_name }}</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Officers List -->
                        <div v-if="organizationDetails.officers && organizationDetails.officers.length > 0" class="mb-8">
                            <h3 class="text-lg font-semibold mb-4 flex items-center text-gray-900 dark:text-white">
                                <span class="w-1 h-5 bg-purple-500 mr-2 rounded"></span>
                                Officers ({{ organizationDetails.officers.length }})
                            </h3>
                            <div class="rounded-lg border overflow-hidden bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Position</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                            <tr v-for="officer in organizationDetails.officers" :key="officer.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="font-medium text-gray-900 dark:text-gray-200">{{ officer.student_name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-purple-600 dark:text-purple-400">{{ officer.position || 'Officer' }}</div>
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
                        <div class="text-center py-12 rounded-lg border bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="text-lg font-medium mb-2 text-gray-700 dark:text-gray-300">No Approved Data Available</h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                This organization hasn't submitted approved member and officer lists yet.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
  components: {
    AuthenticatedLayout,
    Head,
    Link
  },
  
  props: {
    studentOrg: Object,
    organizationDetails: Object
  }
};
</script>
