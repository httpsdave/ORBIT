<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Define props for data that will be passed from the controller
const props = defineProps({
    myApplications: {
        type: Array,
        default: () => [],
    },
    upcomingEvents: {
        type: Array, 
        default: () => [],
    },
    todayEvent: {
        type: Object,
        default: null,
    },
    recentActivity: {
        type: Array,
        default: () => [],
    }
});

// Format date for display
const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Get application status color
const getStatusColor = (status) => {
    const colors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'draft': 'bg-gray-100 text-gray-800',
    };
    return colors[status.toLowerCase()] || 'bg-blue-100 text-blue-800';
};

// Get activity icon
const getActivityIcon = (type) => {
    const icons = {
        'application': `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>`,
        'profile': `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>`,
        'event': `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>`
    };
    return icons[type] || icons['application'];
};
</script>

<template>
    <Head title="User Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Card -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-800">Welcome back, {{ $page.props.auth.user.name }}</h3>
                        <p class="mt-2 text-gray-600">Here's an overview of your activity and important information.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- My Applications Card -->
                    <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-800">My Applications</h3>
                                <a :href="route('applications.create')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md">
                                    Create New
                                </a>
                            </div>
                            
                            <div v-if="props.myApplications && props.myApplications.length > 0" class="space-y-3">
                                <div v-for="application in props.myApplications.slice(0, 3)" :key="application.id" 
                                     class="flex items-center justify-between border rounded-lg p-3 hover:bg-gray-50">
                                    <div>
                                        <h4 class="font-medium text-gray-800">{{ application.title }}</h4>
                                        <p class="text-sm text-gray-600">Last updated: {{ formatDate(application.updated_at) }}</p>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span :class="`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(application.status)}`">
                                            {{ application.status }}
                                        </span>
                                        <a :href="`/applications/${application.id}/edit`" class="text-blue-600 hover:text-blue-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="text-center pt-2" v-if="props.myApplications.length > 3">
                                    <a :href="route('applications.index')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View All Applications →
                                    </a>
                                </div>
                            </div>
                            
                            <div v-else class="text-center py-8 bg-gray-50 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2 text-gray-600">You don't have any applications yet</p>
                                <a :href="route('applications.create')" class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md">
                                    Create Your First Application
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events Card -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium mb-4 text-gray-800">Upcoming Events</h3>
                            
                            <div v-if="props.todayEvent" class="mb-4 border-l-4 border-blue-500 bg-blue-50 p-3 rounded-r-lg">
                                <div class="flex items-center">
                                    <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded mr-2">TODAY</span>
                                    <h4 class="font-medium text-blue-800">{{ props.todayEvent.title }}</h4>
                                </div>
                                <p class="text-xs text-gray-600 mt-1">{{ formatDate(props.todayEvent.start_date) }}</p>
                            </div>
                            
                            <div v-if="props.upcomingEvents && props.upcomingEvents.length > 0" class="space-y-3">
                                <div v-for="event in props.upcomingEvents.slice(0, 3)" :key="event.id" class="border-l-4 border-gray-300 p-3 hover:bg-gray-50 rounded-r-lg">
                                    <h4 class="font-medium text-gray-800">{{ event.title }}</h4>
                                    <p class="text-xs text-gray-600">{{ formatDate(event.start_date) }}</p>
                                </div>
                                
                                <div class="text-center pt-2" v-if="props.upcomingEvents.length > 3">
                                    <a :href="route('calendar')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View All Events →
                                    </a>
                                </div>
                            </div>
                            
                            <div v-else-if="!props.todayEvent" class="text-center py-6 bg-gray-50 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-gray-600">No upcoming events</p>
                                <a :href="route('calendar')" class="mt-2 inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Calendar →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links and Recent Activity -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Recent Activity -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium mb-4 text-gray-800">Recent Activity</h3>
                            
                            <div v-if="props.recentActivity && props.recentActivity.length > 0" class="space-y-4">
                                <div v-for="activity in props.recentActivity" :key="activity.id" class="flex space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600" v-html="getActivityIcon(activity.type)">
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ activity.description }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="text-center py-6 bg-gray-50 rounded-lg">
                                <p class="text-gray-600">No recent activity</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links and Profile -->
                    <div class="space-y-6">
                        <!-- Quick Links Card -->
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium mb-4 text-gray-800">Quick Links</h3>
                                <div class="grid grid-cols-2 gap-3">
                                    <a :href="route('calendar')" class="flex items-center p-3 rounded-lg border hover:bg-gray-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Calendar</span>
                                    </a>
                                    <a :href="route('colleges.index')" class="flex items-center p-3 rounded-lg border hover:bg-gray-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Colleges</span>
                                    </a>
                                    <a :href="route('student-orgs.index')" class="flex items-center p-3 rounded-lg border hover:bg-gray-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Organizations</span>
                                    </a>
                                    <a :href="route('profile.edit')" class="flex items-center p-3 rounded-lg border hover:bg-gray-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700">Profile</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>