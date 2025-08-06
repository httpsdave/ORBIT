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
    <div class="px-4 py-4 flex items-center justify-between">
      <div class="flex items-center">
        <Link 
          :href="isAdmin ? route('admin.dashboard') : route('dashboard')"
          class="flex items-center"
        >
          <div class="relative">
            <div class="absolute inset-0 bg-blue-500 opacity-20 blur-sm rounded-lg transform rotate-45 animate-pulse"></div>
            <ApplicationLogo class="block h-8 w-auto filter drop-shadow" alt="ORBIT logo" />
          </div>
          <span class="ml-2 font-extrabold tracking-wider text-xl text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-green-500 select-none" style="font-family: 'Montserrat', 'Inter', 'Poppins', 'Segoe UI', 'Arial', sans-serif; letter-spacing: 0.04em;">
            ORBIT
          </span>
        </Link>
      </div>
      
      <!-- User badge - optional for mobile header -->
      <div class="flex items-center">
        <Link 
          :href="route('profile.edit')"
          class="flex items-center space-x-1"
        >
          <div>
            <img
              v-if="$page.props.auth.user.profile_photo_url"
              :src="$page.props.auth.user.profile_photo_url"
              alt="Profile Photo"
              class="h-10 w-10 rounded-full object-cover border border-gray-200 dark:border-gray-600 shadow-inner"
            />
            <div
              v-else
              class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner"
            >
              {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
            </div>
          </div>
          <div v-if="isAdmin" class="hidden sm:block">
            <span class="px-2 py-0.5 text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-700 shadow-inner">
              Admin
            </span>
          </div>
        </Link>
      </div>
    </div>
  </header>
</template>