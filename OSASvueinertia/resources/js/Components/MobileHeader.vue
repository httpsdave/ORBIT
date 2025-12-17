<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
  isAdmin: {
    type: Boolean,
    default: false
  },
  showingSidebar: {
    type: Boolean,
    required: true
  }
});
</script>

<template>
  <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm z-30 md:hidden sticky top-0">
    <div class="px-3 sm:px-4 py-3 sm:py-4 flex items-center justify-between">
      <div class="flex items-center min-w-0">
        <Link 
          :href="isAdmin ? route('admin.dashboard') : route('dashboard')"
          class="flex items-center min-w-0"
        >
          <div class="relative flex-shrink-0">
            <div class="absolute inset-0 bg-blue-500 opacity-20 blur-sm rounded-lg transform rotate-45 animate-pulse"></div>
            <ApplicationLogo class="block h-7 w-auto sm:h-8 filter drop-shadow" alt="ORBIT logo" />
          </div>
          <span class="ml-1 font-extrabold tracking-wider text-base text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-green-500 select-none hidden" style="font-family: 'Montserrat', 'Inter', 'Poppins', 'Segoe UI', 'Arial', sans-serif; letter-spacing: 0.04em;">
            ORBIT
          </span>
        </Link>
      </div>
      
      <!-- Right side icons - notifications, calendar, and user -->
      <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
        <!-- Calendar icon -->
        <Link 
          :href="route('calendar')"
          class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </Link>
        
        <!-- Notification icon -->
        <div class="relative">
          <button class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V9a6 6 0 10-12 0v3l-5 5h5a3 3 0 006 0z" />
            </svg>
          </button>
          <!-- Notification badge -->
          <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
            1
          </span>
        </div>
        
        <!-- User profile -->
        <Link 
          :href="route('profile.edit')"
          class="flex items-center space-x-1 sm:space-x-2 min-w-0"
        >
          <div class="flex-shrink-0">
            <img
              v-if="$page.props.auth.user.profile_photo_url"
              :src="$page.props.auth.user.profile_photo_url"
              alt="Profile Photo"
              class="h-8 w-8 sm:h-10 sm:w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600 shadow-inner"
            />
            <div
              v-else
              class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner text-sm sm:text-base"
            >
              {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
            </div>
          </div>
          <div v-if="$page.props.auth.user.role?.slug === 'super_admin'" class="hidden sm:block">
            <span class="px-2 py-0.5 text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded-full border border-purple-200 dark:border-purple-700 shadow-inner">
              Super Admin
            </span>
          </div>
          <div v-else-if="isAdmin" class="hidden sm:block">
            <span class="px-2 py-0.5 text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-700 shadow-inner">
              Admin
            </span>
          </div>
        </Link>
      </div>
    </div>
  </header>
</template>