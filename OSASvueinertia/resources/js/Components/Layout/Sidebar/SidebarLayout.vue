<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SidebarHeader from './SidebarHeader.vue';
import NavigationItems from './NavigationItems.vue';
import SidebarFooter from './SidebarFooter.vue';
import MobileHeader from '@/Components/MobileHeader.vue';

const props = defineProps({
  isAdmin: {
    type: Boolean,
    default: false
  }
});

// Emits
const emit = defineEmits(['close-mobile-menu']);

// State management
const showingSidebar = ref(false);
const sidebarExpanded = ref(true);

// Toggle sidebar for mobile
const toggleSidebar = () => {
  showingSidebar.value = !showingSidebar.value;
};

// Toggle sidebar expanded state with localStorage persistence
const toggleSidebarExpanded = () => {
  sidebarExpanded.value = !sidebarExpanded.value;
  // Save preference to localStorage
  try {
    localStorage.setItem('sidebarExpanded', String(sidebarExpanded.value));
  } catch (e) {
    console.error('Could not save sidebar preference');
  }
};

// Handle sidebar click - expands the sidebar when collapsed
const handleSidebarClick = (event) => {
  // Only toggle if sidebar is collapsed and we're on desktop
  if (!sidebarExpanded.value && window.innerWidth >= 768) {
    toggleSidebarExpanded();
  }
};

// Close sidebar when clicking outside on mobile
const closeSidebarOnClickOutside = (event) => {
  if (window.innerWidth < 768 && showingSidebar.value) {
    const sidebarElement = document.getElementById('sidebar');
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    
    if (sidebarElement && 
        !sidebarElement.contains(event.target) && 
        mobileMenuButton && 
        !mobileMenuButton.contains(event.target)) {
      closeSidebar();
    }
  } else if (window.innerWidth >= 768 && sidebarExpanded.value) {
    // For desktop mode, check if click is outside the sidebar to collapse it
    const sidebarElement = document.getElementById('sidebar');
    if (sidebarElement && !sidebarElement.contains(event.target)) {
      sidebarExpanded.value = false;
      try {
        localStorage.setItem('sidebarExpanded', 'false');
      } catch (e) {
        console.error('Could not save sidebar preference');
      }
    }
  }
};

// Check window size and adjust sidebar on small screens
const checkWindowSize = () => {
  if (window.innerWidth < 768) {
    sidebarExpanded.value = false;
    showingSidebar.value = false;
  } else if (window.innerWidth >= 768) {
    // Only restore the saved preference when on desktop
    try {
      const savedExpanded = localStorage.getItem('sidebarExpanded');
      if (savedExpanded !== null) {
        sidebarExpanded.value = savedExpanded === 'true';
      } else {
        sidebarExpanded.value = true; // Default to expanded
      }
    } catch (e) {
      sidebarExpanded.value = true; // Default if localStorage fails
    }
  }
};

// Close mobile menu and emit event
const closeSidebar = () => {
  showingSidebar.value = false;
  emit('close-mobile-menu');
};

// Keyboard navigation for accessibility
const handleKeyDown = (event) => {
  if (event.key === 'Escape') {
    if (showingSidebar.value && window.innerWidth < 768) {
      closeSidebar();
    } else if (sidebarExpanded.value && window.innerWidth >= 768) {
      // Collapse sidebar with Escape key on desktop
      sidebarExpanded.value = false;
      try {
        localStorage.setItem('sidebarExpanded', 'false');
      } catch (e) {
        console.error('Could not save sidebar preference');
      }
    }
  }
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', closeSidebarOnClickOutside);
  document.addEventListener('keydown', handleKeyDown);
  window.addEventListener('resize', checkWindowSize);
  
  // Check initial window size
  checkWindowSize();
});

onUnmounted(() => {
  document.removeEventListener('click', closeSidebarOnClickOutside);
  document.removeEventListener('keydown', handleKeyDown);
  window.removeEventListener('resize', checkWindowSize);
});
</script>

<template>
  <div class="flex min-h-screen bg-gray-50 relative">
    <!-- Fixed header for both desktop and mobile that spans the entire width -->
    <header class="fixed top-0 left-0 right-0 bg-white border-b border-gray-200 shadow-sm z-40 h-16">
      <div class="px-4 sm:px-6 h-full flex items-center justify-between">
        <div class="flex items-center">
          <!-- Mobile menu button - visible only on mobile -->
          <button
            @click="toggleSidebar"
            class="mobile-menu-button md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300"
            aria-label="Open menu"
            :aria-expanded="showingSidebar"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <Link 
            :href="isAdmin ? route('admin.dashboard') : route('dashboard')"
            class="flex items-center"
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
        
        <!-- Right side - user profile & actions -->
        <div class="flex items-center space-x-4">
          <!-- Notifications button (optional) -->
          <button class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </button>
          
          <!-- User profile -->
          <Link 
            :href="route('profile.edit')"
            class="flex items-center group"
          >
            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-sm group-hover:shadow-md transition-all duration-300">
              {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
            </div>
            <div class="ml-2 hidden sm:block">
              <div class="text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors duration-300">
                {{ $page.props.auth.user.name }}
              </div>
              <div v-if="isAdmin" class="text-xs font-medium text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-full border border-indigo-200">
                Admin
              </div>
            </div>
          </Link>
        </div>
      </div>
    </header>

    <!-- Sidebar - with additional top padding to account for the fixed header -->
    <aside
      id="sidebar"
      :class="[
        'z-30 transition-all duration-300 ease-in-out border-r border-gray-200 bg-white shadow-lg shadow-blue-200/20 flex flex-col',
        sidebarExpanded ? 'md:w-64' : 'md:w-20',
        showingSidebar ? 'fixed left-0 w-64 h-full pt-16' : 'fixed -left-64 md:left-0 md:relative md:h-screen pt-16'
      ]"
      aria-label="Navigation sidebar"
      @click="handleSidebarClick"
    >
      <!-- Sidebar Header -->
      <SidebarHeader 
        :is-admin="isAdmin" 
        :sidebar-expanded="sidebarExpanded" 
        :showing-sidebar="showingSidebar"
        @toggle-sidebar-expanded="toggleSidebarExpanded"
        @close-sidebar="closeSidebar"
      />
      
      <!-- Navigation Links -->
      <NavigationItems 
        :is-admin="isAdmin" 
        :sidebar-expanded="sidebarExpanded" 
        :showing-sidebar="showingSidebar"
      />
      
      <!-- Bottom Actions -->
      <SidebarFooter 
        :sidebar-expanded="sidebarExpanded" 
        :showing-sidebar="showingSidebar"
      />
    </aside>

    <!-- Main Content - with additional top padding to account for the fixed header -->
    <div class="flex-1 flex flex-col min-h-screen overflow-hidden pt-16 md:ml-0">
      <!-- Page Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 relative p-4 sm:p-6">
        <!-- Content provided via slot -->
        <slot></slot>
      </main>
    </div>
    
    <!-- Overlay for mobile sidebar -->
    <div 
      v-if="showingSidebar" 
      @click="closeSidebar" 
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-35 md:hidden animate-fadeIn"
      aria-hidden="true"
    ></div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out forwards;
}

/* Improve focus visibility for accessibility */
a:focus, button:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* Make collapsed sidebar have a pointer cursor to indicate clickability */
@media (min-width: 768px) {
  aside:not(.md\:w-64) {
    cursor: pointer;
  }
}
</style>