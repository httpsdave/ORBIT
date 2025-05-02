<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
  isAdmin: {
    type: Boolean,
    default: false
  },
  sidebarExpanded: {
    type: Boolean,
    required: true
  },
  showingSidebar: {
    type: Boolean,
    required: true
  }
});

// Add toggleSidebarExpanded to emits
const emit = defineEmits(['close-sidebar', 'toggle-sidebar-expanded']);

const closeSidebar = () => {
  emit('close-sidebar');
};

// Add a function to handle toggling sidebar
const toggleSidebar = () => {
  emit('toggle-sidebar-expanded');
};
</script>

<template>
  <!-- Combined colored banner and close button -->
  <div class="flex items-center justify-between px-4 mt-4">
    <!-- Colored banner -->
    <div 
      :class="{'justify-center': !sidebarExpanded && !showingSidebar, 'justify-start': sidebarExpanded || showingSidebar }"
      class="flex overflow-hidden rounded-lg h-2"
      aria-hidden="true"
    >
      <div class="w-6 h-1 bg-blue-500 animate-pulse rounded-full mr-1" style="animation-delay: 0.2s;"></div>
      <div class="w-4 h-1 bg-green-500 animate-pulse rounded-full mr-1" style="animation-delay: 0.4s;"></div>
      <div class="w-2 h-1 bg-yellow-500 animate-pulse rounded-full" style="animation-delay: 0.6s;"></div>
    </div>
    
    <!-- Close sidebar button (mobile only) -->
    <button 
      v-if="showingSidebar"
      @click="closeSidebar"
      class="md:hidden flex items-center justify-center h-8 w-8 rounded-full bg-red-50 text-red-600 hover:text-red-700 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 transition-all duration-300"
      aria-label="Close menu"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</template>