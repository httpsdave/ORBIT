<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

// Define props for data passed from the controller
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
    'pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'approved': 'bg-green-100 text-green-800 border-green-200',
    'rejected': 'bg-red-100 text-red-800 border-red-200',
    'draft': 'bg-gray-100 text-gray-800 border-gray-200',
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
    <Head title="Dashboard" />
  
    <AuthenticatedLayout>
      <template #header>
        <div class="flex justify-between items-center">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Dashboard</h2>
          <div class="text-sm text-gray-500">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</div>
        </div>
      </template>
  
      <div class="py-8">
        
          <!-- Colored Banner - Matching admin dashboard -->
          <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
              <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
              <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
              <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
              <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
          </div>

          <!-- Greeting Card (no stats inside) -->
          <div 
            class="bg-white overflow-hidden shadow-sm rounded-lg mb-6 transform transition-all duration-500 ease-in-out"
            :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
          >
            <div class="p-6 bg-white border-b border-gray-200">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h2 class="text-2xl font-semibold text-gray-800">{{ greeting }}, {{ $page.props.auth?.user?.name || 'User' }}!</h2>
                  <p class="mt-1 text-gray-600">Welcome to orbit. Here's your latest activity and important updates.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Stats Cards Row (separate from greeting) -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-blue-500 transition hover:shadow-md">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-100">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Applications</p>
                    <p class="text-3xl font-bold text-gray-900">{{ props.myApplications.length }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-green-500 transition hover:shadow-md">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-100">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Upcoming Events</p>
                    <p class="text-3xl font-bold text-gray-900">{{ props.upcomingEvents.length + (props.todayEvent ? 1 : 0) }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-yellow-500 transition hover:shadow-md">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-yellow-100">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Recent Activities</p>
                    <p class="text-3xl font-bold text-gray-900">{{ props.recentActivity.length }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- My Applications Card -->
            <div 
              class="lg:col-span-2 bg-white overflow-hidden shadow-sm rounded-lg transform transition-all duration-500 ease-in-out"
              :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
              style="transition-delay: 100ms;"
            >
              <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                  <h3 class="text-lg font-medium text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    My Applications
                  </h3>
                  <Link :href="route('applications.index')" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New
                  </Link>
                </div>
                
                <div v-if="props.myApplications && props.myApplications.length > 0" class="space-y-3">
                  <div v-for="application in props.myApplications.slice(0, 3)" :key="application.id" 
                    class="flex items-center justify-between border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition duration-200 ease-in-out">
                    <div class="flex items-center">
                      <div class="h-10 w-10 rounded-md bg-blue-100 flex items-center justify-center mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="font-medium text-gray-800">{{ application.title }}</h4>
                        <p class="text-xs text-gray-500">Updated: {{ formatDate(application.updated_at) }}</p>
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
                    <Link :href="route('applications.index')" class="inline-flex items-center text-blue-500 hover:text-blue-700 font-medium text-sm transition-colors duration-200">
                      View All Applications
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                      </svg>
                    </Link>
                  </div>
                </div>
                
                <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <p class="mt-4 text-gray-600 mb-6">You don't have any applications yet</p>
                  <Link :href="route('applications.create')" class="mt-3 inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Your First Application
                  </Link>
                </div>
              </div>
            </div>
  
            <!-- Upcoming Events Card - Styled like admin event card -->
            <div 
              class="bg-white overflow-hidden shadow-sm rounded-lg transform transition-all duration-500 ease-in-out"
              :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
              style="transition-delay: 200ms;"
            >
              <div class="p-6">
                <div class="flex items-center mb-4">
                  <div class="p-2 rounded-md bg-blue-100 text-blue-600 mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-medium text-gray-800">Upcoming Events</h3>
                </div>
                
                <div v-if="props.todayEvent" class="mb-6 border rounded-lg p-4 bg-white shadow-sm cursor-pointer hover:bg-blue-50 transition" >
                  <Link :href="route('calendar')" class="block">
                    <div class="flex justify-between items-center mb-2">
                      <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-md">TODAY</span>
                      <span class="text-xs text-gray-600">{{ formatTime(props.todayEvent.start_date) }}</span>
                    </div>
                    <h4 class="font-medium text-lg text-gray-800">{{ props.todayEvent.title }}</h4>
                    <div class="text-sm text-gray-600 mt-2 space-y-2">
                      <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ formatDate(props.todayEvent.start_date) }}</span>
                      </div>
                    </div>
                  </Link>
                </div>
                
                <div v-if="props.upcomingEvents && props.upcomingEvents.length > 0" class="space-y-4">
                  <div v-for="(event, index) in props.upcomingEvents.slice(0, 3)" :key="event.id" 
                    class="border rounded-lg p-4 bg-white shadow-sm cursor-pointer hover:bg-blue-50 transition">
                    <Link :href="route('calendar')" class="block">
                      <div class="flex">
                        <div class="mr-4 flex-shrink-0">
                          <div class="h-12 w-12 rounded-md bg-green-100 flex flex-col items-center justify-center">
                            <span class="text-xs font-medium text-green-600">{{ formatDateOnly(event.start_date).split(' ')[0] }}</span>
                            <span class="text-lg font-bold text-green-800">{{ formatDateOnly(event.start_date).split(' ')[1] }}</span>
                          </div>
                        </div>
                        <div>
                          <h4 class="font-medium text-gray-800">{{ event.title }}</h4>
                          <div class="flex items-center text-xs text-gray-500 mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ formatTime(event.start_date) }}
                          </div>
                        </div>
                      </div>
                    </Link>
                  </div>
                  
                  <div class="text-center pt-4" v-if="props.upcomingEvents.length > 3">
                    <Link :href="route('calendar')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      View All Events
                    </Link>
                  </div>
                </div>
                
                <div v-else-if="!props.todayEvent" class="text-center py-12 bg-gray-50 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="mt-4 text-gray-600">No upcoming events scheduled</p>
                  <Link :href="route('calendar')" class="inline-block mt-3 px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    View Calendar
                  </Link>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Quick Links and Recent Activity -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Recent Activity -->
            <div 
              class="bg-white overflow-hidden shadow-sm rounded-lg transform transition-all duration-500 ease-in-out"
              :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
              style="transition-delay: 300ms;"
            >
              <div class="p-6">
                <div class="flex items-center mb-4">
                  <div class="p-2 rounded-md bg-yellow-100 text-yellow-600 mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-medium text-gray-800">Recent Activity</h3>
                </div>
                
                <div v-if="props.recentActivity && props.recentActivity.length > 0" class="space-y-4">
                  <div v-for="(activity, index) in props.recentActivity" :key="activity.id" 
                    class="border rounded-lg p-4 bg-white shadow-sm">
                    <div class="flex">
                      <div class="flex-shrink-0 mr-4">
                        <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-500" v-html="getActivityIcon(activity.type)">
                        </div>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-800">{{ activity.description }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div v-else class="border rounded-lg p-6 bg-gray-50 text-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="mt-4 text-gray-600">No recent activity</p>
                </div>
              </div>
            </div>
            
            <!-- Quick Links -->
            <div 
              class="bg-white overflow-hidden shadow-sm rounded-lg transform transition-all duration-500 ease-in-out"
              :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
              style="transition-delay: 400ms;"
            >
              <div class="p-6">
                <div class="flex items-center mb-4">
                  <div class="p-2 rounded-md bg-blue-100 text-blue-600 mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-medium text-gray-800">Quick Links</h3>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                  <a :href="route('calendar')" class="flex items-center p-4 rounded-lg border border-gray-100 hover:bg-yellow-50 hover:border-yellow-200 transition-colors duration-200 group">
                    <div class="bg-yellow-100 p-2 rounded-md mr-3">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Calendar</span>
                  </a>
                  <a :href="route('colleges.index')" class="flex items-center p-4 rounded-lg border border-gray-100 hover:bg-blue-50 hover:border-blue-200 transition-colors duration-200 group">
                    <div class="bg-blue-100 p-2 rounded-md mr-3">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Colleges</span>
                  </a>
                <a :href="route('student-orgs.index')" class="flex items-center p-4 rounded-lg border border-gray-100 hover:bg-green-50 hover:border-green-200 transition-colors duration-200 group">
                  <div class="bg-green-100 p-2 rounded-md mr-3 group-hover:bg-green-200 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-700">Organizations</span>
                </a>
                <a :href="route('profile.edit')" class="flex items-center p-4 rounded-lg border border-gray-100 hover:bg-red-50 hover:border-red-200 transition-colors duration-200 group">
                  <div class="bg-red-100 p-2 rounded-md mr-3 group-hover:bg-red-200 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-700">Profile</span>
                </a>
              </div>
            </div>
         
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>