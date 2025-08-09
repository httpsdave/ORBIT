<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
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

// Real-time clock
const currentDateTime = ref(new Date());
const clockTimer = ref(null);

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

// Format current date time for header
const formatCurrentDateTime = computed(() => {
  return currentDateTime.value.toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  });
});

// Get application status color
const getStatusColor = (status) => {
  const colors = {
    'pending': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 border-yellow-200 dark:border-yellow-700',
    'approved': 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border-green-200 dark:border-green-700',
    'rejected': 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border-red-200 dark:border-red-700',
    'draft': 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700',
  };
  return colors[status.toLowerCase()] || 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-700';
};

// Get activity icon and color based on activity type
const getActivityIcon = (type) => {
  const icons = {
    'submission': `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>`,
    'update': `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>`,
    'approval': `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>`,
    'rejection': `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>`,
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

// Get activity icon container classes based on activity type
const getActivityIconClasses = (type) => {
  const classes = {
    'submission': 'h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-500 dark:text-blue-400',
    'update': 'h-10 w-10 rounded-full bg-yellow-100 dark:bg-yellow-900/50 flex items-center justify-center text-yellow-500 dark:text-yellow-400',
    'approval': 'h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center text-green-500 dark:text-green-400',
    'rejection': 'h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center text-red-500 dark:text-red-400',
    'application': 'h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center text-purple-500 dark:text-purple-400',
    'profile': 'h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-500 dark:text-indigo-400',
    'event': 'h-10 w-10 rounded-full bg-cyan-100 dark:bg-cyan-900/50 flex items-center justify-center text-cyan-500 dark:text-cyan-400'
  };
  
  return classes[type] || classes['application'];
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
  
  // Start real-time clock
  clockTimer.value = setInterval(() => {
    currentDateTime.value = new Date();
  }, 1000);
});

onUnmounted(() => {
  if (clockTimer.value) {
    clearInterval(clockTimer.value);
  }
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

// Tab state for combined Applications/Activity card
const activeTab = ref('applications');
</script>

<template>
    <Head title="Dashboard" />
  
    <AuthenticatedLayout>
      <template #header>
        <div class="flex justify-between items-center">
          <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">My Dashboard</h2>
          <div class="text-sm text-gray-500 dark:text-gray-400">{{ formatCurrentDateTime }}</div>
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
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6 transform transition-all duration-500 ease-in-out"
            :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
          >
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">{{ greeting }}, {{ $page.props.auth?.user?.name || 'User' }}!</h2>
                  <p class="mt-1 text-gray-600 dark:text-gray-400">Welcome to orbit. Here's your latest activity and important updates.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Stats Cards Row (separate from greeting) -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-blue-500 transition hover:shadow-md">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900/50">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Applications</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ props.myApplications.length }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-green-500 transition hover:shadow-md">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/50">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming Events</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ props.upcomingEvents.length + (props.todayEvent ? 1 : 0) }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border-l-4 border-yellow-500 transition hover:shadow-md">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900/50">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Recent Activities</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ props.recentActivity.length }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Combined Applications & Activity Card with Tabs -->
            <div 
              class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transform transition-all duration-500 ease-in-out"
              :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
              style="transition-delay: 100ms;"
            >
              <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                  <div class="flex items-center space-x-2">
                    <button
                      class="px-4 py-2 rounded-t-md text-sm font-medium focus:outline-none"
                      :class="activeTab === 'applications' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
                      @click="activeTab = 'applications'"
                    >
                      Applications
                    </button>
                    <button
                      class="px-4 py-2 rounded-t-md text-sm font-medium focus:outline-none"
                      :class="activeTab === 'activity' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
                      @click="activeTab = 'activity'"
                    >
                      Activity
                    </button>
                  </div>
                  <div v-if="activeTab === 'applications'">
                    <Link :href="route('applications.index')" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group">
                      <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Create New
                    </Link>
                  </div>
                </div>
                <!-- Applications Tab -->
                <div v-if="activeTab === 'applications'">
                  <div v-if="props.myApplications && props.myApplications.length > 0" class="space-y-3">
                    <div v-for="application in props.myApplications.slice(0, 3)" :key="application.id" 
                      class="flex items-center justify-between border border-gray-100 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-200 ease-in-out">
                      <div class="flex items-center">
                        <div class="h-10 w-10 rounded-md bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center mr-4">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                          </svg>
                        </div>
                        <div>
                          <h4 class="font-medium text-gray-800 dark:text-gray-200">{{ application.title }}</h4>
                          <p class="text-xs text-gray-500 dark:text-gray-400">Updated: {{ formatDate(application.updated_at) }}</p>
                        </div>
                      </div>
                      <div class="flex items-center space-x-3">
                        <span :class="`px-3 py-1 rounded-full text-xs font-medium ring-1 ring-inset ${getStatusColor(application.status)}`">
                          {{ application.status }}
                        </span>
                        <Link :href="`/applications`" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-900/50 hover:bg-blue-100 dark:hover:bg-blue-900/70 p-2 rounded-xl transition-all duration-300 shadow-sm hover:shadow-md">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                        </Link>
                      </div>
                    </div>
                    <div class="text-center pt-4" v-if="props.myApplications.length > 3">
                      <Link :href="route('applications.index')" class="inline-flex items-center text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm transition-colors duration-200">
                        View All Applications
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                      </Link>
                    </div>
                  </div>
                  <div v-else class="text-center py-12 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-4 text-gray-600 dark:text-gray-400 mb-6">You don't have any applications yet</p>
                    <Link :href="route('applications.create')" class="mt-3 inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group">
                      <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Create Your First Application
                    </Link>
                  </div>
                </div>
                <!-- Activity Tab -->
                <div v-if="activeTab === 'activity'">
                  <div v-if="props.recentActivity && props.recentActivity.length > 0" class="space-y-4">
                    <div v-for="(activity, index) in props.recentActivity" :key="activity.id" 
                      class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800 shadow-sm">
                      <div class="flex">
                        <div class="flex-shrink-0 mr-4">
                          <div :class="getActivityIconClasses(activity.type)" v-html="getActivityIcon(activity.type)">
                          </div>
                        </div>
                        <div class="flex-1">
                          <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ activity.description }}</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ formatDate(activity.created_at) }}</p>
                          <!-- Show status badge if applicable -->
                          <div v-if="activity.status" class="mt-2">
                            <span :class="getStatusColor(activity.status)" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border">
                              {{ activity.status }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 bg-gray-50 dark:bg-gray-700 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">No recent activity</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Upcoming Events Card - Styled like admin event card -->
            <div 
              class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transform transition-all duration-500 ease-in-out"
              :class="{ 'translate-y-0 opacity-100': isVisible, 'translate-y-4 opacity-0': !isVisible }"
              style="transition-delay: 200ms;"
            >
              <div class="p-6">
                <div class="flex items-center mb-4">
                  <div class="p-2 rounded-md bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Upcoming Events</h3>
                </div>
                
                <div v-if="props.todayEvent" class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800 shadow-sm cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 transition" >
                  <Link :href="route('calendar')" class="block">
                    <div class="flex justify-between items-center mb-2">
                      <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-md">TODAY</span>
                      <span class="text-xs text-gray-600 dark:text-gray-400">{{ formatTime(props.todayEvent.start_date) }}</span>
                    </div>
                    <h4 class="font-medium text-lg text-gray-800 dark:text-gray-200">{{ props.todayEvent.title }}</h4>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-2 space-y-2">
                      <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Start: {{ formatDate(props.todayEvent.start_date) }}</span>
                      </div>
                      <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>End: {{ formatDate(props.todayEvent.end_date) }}</span>
                      </div>
                    </div>
                    <div v-if="props.todayEvent.description" class="mt-4 border-t border-gray-100 dark:border-gray-700 pt-3">
                      <p class="text-sm text-gray-700 dark:text-gray-300">{{ props.todayEvent.description }}</p>
                    </div>
                  </Link>
                </div>
                
                <div v-if="props.upcomingEvents && props.upcomingEvents.length > 0" class="space-y-4">
                  <div v-for="(event, index) in props.upcomingEvents.slice(0, 3)" :key="event.id" 
                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800 shadow-sm cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                    <Link :href="route('calendar')" class="block">
                      <div class="flex">
                        <div class="mr-4 flex-shrink-0">
                          <div class="h-12 w-12 rounded-md bg-green-100 dark:bg-green-900/50 flex flex-col items-center justify-center">
                            <span class="text-xs font-medium text-green-600 dark:text-green-400">{{ formatDateOnly(event.start_date).split(' ')[0] }}</span>
                            <span class="text-lg font-bold text-green-800 dark:text-green-300">{{ formatDateOnly(event.start_date).split(' ')[1] }}</span>
                          </div>
                        </div>
                        <div>
                          <h4 class="font-medium text-gray-800 dark:text-gray-200">{{ event.title }}</h4>
                          <div class="text-sm text-gray-600 dark:text-gray-400 mt-2 space-y-1">
                            <div class="flex items-center text-xs">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                              <span>Start: {{ formatDate(event.start_date) }}</span>
                            </div>
                            <div class="flex items-center text-xs">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                              </svg>
                              <span>End: {{ formatDate(event.end_date) }}</span>
                            </div>
                            <div v-if="event.description" class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                              <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">{{ event.description }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </Link>
                  </div>
                  
                  <div class="text-center pt-4" v-if="props.upcomingEvents.length > 3">
                    <Link :href="route('calendar')" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl shadow-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group">
                      <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-blue-500 rounded-full group-hover:w-96 group-hover:h-96 opacity-5"></span>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      View All Events
                    </Link>
                  </div>
                </div>
                
                <div v-else-if="!props.todayEvent" class="text-center py-12 bg-gray-50 dark:bg-gray-700 rounded-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="mt-4 text-gray-600 dark:text-gray-400">No upcoming events scheduled</p>
                  <Link :href="route('calendar')" class="inline-block mt-3 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group">
                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                    View Calendar
                  </Link>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Quick Links and Recent Activity -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Recent Activity -->
            <!-- Entire Recent Activity card below is now redundant and should be removed -->
            
            <!-- Quick Links REMOVED -->
        
      </div>
    </div>
  </AuthenticatedLayout>
</template>