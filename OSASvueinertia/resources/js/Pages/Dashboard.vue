<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

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

// Format time only for display
const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Format date without time
const formatDateOnly = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

// Get application status color
const getStatusColor = (status) => {
    const colors = {
        'pending': 'bg-amber-100 text-amber-800 border-amber-200',
        'approved': 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'rejected': 'bg-rose-100 text-rose-800 border-rose-200',
        'draft': 'bg-slate-100 text-slate-800 border-slate-200',
    };
    return colors[status.toLowerCase()] || 'bg-blue-100 text-blue-800 border-blue-200';
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

// Greeting based on time of day
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
});

// Animated card appearances
const isVisible = ref(false);
onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

// Get time until next event
const timeUntilNext = computed(() => {
    if (!props.upcomingEvents || props.upcomingEvents.length === 0) return null;
    
    const nextEvent = props.upcomingEvents[0];
    const now = new Date();
    const eventDate = new Date(nextEvent.start_date);
    const diffTime = Math.abs(eventDate - now);
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return "Today";
    if (diffDays === 1) return "Tomorrow";
    if (diffDays < 7) return `${diffDays} days`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks`;
    return `${Math.floor(diffDays / 30)} months`;
});
</script>

<template>
    <Head title="User Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Dashboard</h2>
                <div class="text-sm text-gray-500">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</div>
            </div>
        </template>

       
           
                <!-- Welcome Card -->
                <div 
                    class="bg-gradient-to-r from-blue-600 to-indigo-700 overflow-hidden shadow-lg rounded-xl mb-8 transform transition-all duration-500 ease-in-out"
                    :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
                >
                    <div class="p-6 sm:p-8 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl sm:text-2xl font-bold text-white">{{ greeting }}, {{ $page.props.auth.user.name }}!</h3>
                                <p class="mt-2 text-blue-100 opacity-90">Here's your latest activity and important updates.</p>
                            </div>
                            <div class="hidden md:block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Stats Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                <div class="text-3xl font-bold">{{ props.myApplications.length }}</div>
                                <div class="text-sm text-blue-100">Applications</div>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                <div class="text-3xl font-bold">{{ props.upcomingEvents.length + (props.todayEvent ? 1 : 0) }}</div>
                                <div class="text-sm text-blue-100">Upcoming Events</div>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                <div class="text-3xl font-bold">{{ props.recentActivity.length }}</div>
                                <div class="text-sm text-blue-100">Recent Activities</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- My Applications Card -->
                    <div 
                        class="lg:col-span-2 bg-white overflow-hidden shadow-md rounded-xl border border-gray-100 transform transition-all duration-500 ease-in-out"
                        :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
                        style="transition-delay: 100ms;"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    My Applications
                                </h3>
                                <Link :href="route('applications.create')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create New
                                    </span>
                                </Link>
                            </div>
                            
                            <div v-if="props.myApplications && props.myApplications.length > 0" class="space-y-3">
                                <div v-for="application in props.myApplications.slice(0, 3)" :key="application.id" 
                                     class="flex items-center justify-between border rounded-lg p-4 hover:bg-gray-50 transition duration-200 ease-in-out">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-800">{{ application.title }}</h4>
                                            <p class="text-sm text-gray-500">Updated: {{ formatDate(application.updated_at) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <span :class="`px-3 py-1 rounded-full text-xs font-medium ring-1 ring-inset ${getStatusColor(application.status)}`">
                                            {{ application.status }}
                                        </span>
                                        <Link :href="`/applications`" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-2 rounded-full transition-colors duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>
                                
                                <div class="text-center pt-4" v-if="props.myApplications.length > 3">
                                    <Link :href="route('applications.index')" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors duration-200">
                                        View All Applications
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                            
                            <div v-else class="text-center py-12 bg-gray-50 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-4 text-gray-600 mb-6">You don't have any applications yet</p>
                                <Link :href="route('applications.create')" class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm transition-all duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create Your First Application
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events Card -->
                    <div 
                        class="bg-white overflow-hidden shadow-md rounded-xl border border-gray-100 transform transition-all duration-500 ease-in-out"
                        :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
                        style="transition-delay: 200ms;"
                    >
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-6 text-gray-800 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Upcoming Events
                            </h3>
                            
                            <div v-if="props.todayEvent" class="mb-6 bg-gradient-to-r from-indigo-500 to-blue-600 text-white p-4 rounded-xl shadow-md">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="bg-white text-indigo-600 text-xs font-bold px-2 py-1 rounded-full">TODAY</span>
                                    <span class="text-xs text-indigo-100">{{ formatTime(props.todayEvent.start_date) }}</span>
                                </div>
                                <h4 class="font-medium text-lg">{{ props.todayEvent.title }}</h4>
                                <p class="text-xs text-indigo-100 mt-1 opacity-80">{{ formatDate(props.todayEvent.start_date) }}</p>
                            </div>
                            
                            <div v-if="props.upcomingEvents && props.upcomingEvents.length > 0" class="space-y-4">
                                <div v-for="(event, index) in props.upcomingEvents.slice(0, 3)" :key="event.id" 
                                     class="flex p-3 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                                    <div class="mr-4 flex-shrink-0">
                                        <div class="h-12 w-12 rounded-lg bg-indigo-100 flex flex-col items-center justify-center">
                                            <span class="text-xs font-medium text-indigo-600">{{ formatDateOnly(event.start_date).split(' ')[0] }}</span>
                                            <span class="text-lg font-bold text-indigo-800">{{ formatDateOnly(event.start_date).split(' ')[1] }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-800">{{ event.title }}</h4>
                                        <p class="text-xs text-gray-500">{{ formatTime(event.start_date) }}</p>
                                    </div>
                                </div>
                                
                                <div class="text-center pt-4" v-if="props.upcomingEvents.length > 3">
                                    <Link :href="route('calendar')" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium text-sm transition-colors duration-200">
                                        View All Events
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                            
                            <div v-else-if="!props.todayEvent" class="text-center py-12 bg-gray-50 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-4 text-gray-600">No upcoming events</p>
                                <Link :href="route('calendar')" class="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium text-sm transition-colors duration-200">
                                    View Calendar
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links and Recent Activity -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Recent Activity -->
                    <div 
                        class="bg-white overflow-hidden shadow-md rounded-xl border border-gray-100 transform transition-all duration-500 ease-in-out"
                        :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
                        style="transition-delay: 300ms;"
                    >
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-6 text-gray-800 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                                Recent Activity
                            </h3>
                            
                            <div v-if="props.recentActivity && props.recentActivity.length > 0" class="space-y-4">
                                <div v-for="(activity, index) in props.recentActivity" :key="activity.id" 
                                     class="flex p-3 rounded-lg border-l-4 border-green-400 hover:bg-green-50 transition-colors duration-200"
                                     :class="{'animate-pulse': index === 0}">
                                    <div class="flex-shrink-0 mr-4">
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-600" v-html="getActivityIcon(activity.type)">
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ activity.description }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="text-center py-10 bg-gray-50 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-4 text-gray-600">No recent activity</p>
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
           
       
    </AuthenticatedLayout>
</template>