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

const emit = defineEmits(['toggle-sidebar']);

const toggleSidebar = () => {
  emit('toggle-sidebar');
};
</script>

<template>
  <header class="bg-white border-b border-gray-200 shadow-sm z-30 md:hidden sticky top-0">
    <div class="px-4 py-4 flex items-center justify-between">
      <div class="flex items-center">
        <button
          @click="toggleSidebar"
          class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300"
          aria-label="Open menu"
          :aria-expanded="showingSidebar"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        
        <Link 
          :href="isAdmin ? route('admin.dashboard') : route('dashboard')"
          class="ml-2 flex items-center"
        >
          <div class="relative">
            <div class="absolute inset-0 bg-blue-500 opacity-20 blur-sm rounded-lg transform rotate-45 animate-pulse"></div>
            <ApplicationLogo class="block h-8 w-auto filter drop-shadow" alt="ORBIT logo" />
          </div>
          <span class="ml-2 font-semibold text-lg text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-green-500">
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
              class="h-10 w-10 rounded-full object-cover border border-gray-200 shadow-inner"
            />
            <div
              v-else
              class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner"
            >
              {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
            </div>
          </div>
          <div v-if="isAdmin" class="hidden sm:block">
            <span class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full border border-indigo-200 shadow-inner">
              Admin
            </span>
          </div>
        </Link>
      </div>
    </div>
  </header>
</template>