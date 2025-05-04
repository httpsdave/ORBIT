<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SidebarHeader from './SidebarHeader.vue';
import NavigationItems from './NavigationItems.vue';
import SidebarFooter from './SidebarFooter.vue';
import MobileHeader from '@/Components/MobileHeader.vue';

const props = defineProps({
  isAdmin: {
    type: Boolean,
    default: false
  },
  // New prop to control logo size
  logoSize: {
    type: String,
    default: 'large' // 'default', 'medium', 'large'
  }
});

// Emits
const emit = defineEmits(['close-mobile-menu']);

// State management
const showingSidebar = ref(false);
const sidebarExpanded = ref(true);

// User profile dropdown state
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);
const page = usePage();
const user = computed(() => page.props.auth.user);

// Calculate dropdown position based on sidebar state
const dropdownPosition = computed(() => {
  // If sidebar is expanded or showing on mobile, dropdown opens above
  if (sidebarExpanded.value || showingSidebar.value) {
    return 'top-full mt-2 right-0';
  } 
  // If sidebar is collapsed on desktop, dropdown opens to the right
  return 'right-full mr-2 top-0';
});

// Compute logo size class based on prop
const logoSizeClass = computed(() => {
  switch(props.logoSize) {
    case 'medium':
      return 'h-10 w-auto';
    case 'large':
      return 'h-12 w-auto';
    case 'extra-large':
      return 'h-16 w-auto';
    default:
      return 'h-8 w-auto';
  }
});

// Toggle dropdown
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

// Close dropdown when sidebar state changes
watch(() => sidebarExpanded.value, () => {
  isDropdownOpen.value = false;
});

// Calendar data - moved from NavigationItems.vue
const calendarItem = {
  name: 'Calendar',
  route: 'calendar',
  icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
};

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

// Close dropdown when clicking outside
const closeDropdownOnClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isDropdownOpen.value = false;
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
    // Also close dropdown if open
    if (isDropdownOpen.value) {
      isDropdownOpen.value = false;
    }
  }
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', closeSidebarOnClickOutside);
  document.addEventListener('click', closeDropdownOnClickOutside);
  document.addEventListener('keydown', handleKeyDown);
  window.addEventListener('resize', checkWindowSize);
  
  // Check initial window size
  checkWindowSize();
});

onUnmounted(() => {
  document.removeEventListener('click', closeSidebarOnClickOutside);
  document.removeEventListener('click', closeDropdownOnClickOutside);
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
              <!-- Animated background glow, size adjusted to match logo -->
              <div 
               
                :class="{'scale-125': props.logoSize === 'medium', 'scale-150': props.logoSize === 'large', 'scale-175': props.logoSize === 'extra-large'}"
              ></div>
              <!-- Logo with dynamic size class -->
              <ApplicationLogo :class="[logoSizeClass, 'block filter drop-shadow']" alt="ORBIT logo" />
            </div>
            <!-- Company name text adjusted based on logo size -->
            <span 
              class="ml-2 font-semibold text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-green-500"
              :class="{
                'text-lg': props.logoSize === 'default',
                'text-xl': props.logoSize === 'medium',
                'text-2xl': props.logoSize === 'large',
                'text-3xl': props.logoSize === 'extra-large'
              }"
            >
              ORBIT
            </span>
          </Link>
        </div>
        
        <!-- Right side - user profile & actions -->
        <div class="flex items-center space-x-4">
          <!-- Calendar link -->
          <Link 
            :href="route(calendarItem.route)"
            class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300 group relative"
            aria-label="Calendar"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="calendarItem.icon" />
            </svg>
            <!-- Tooltip for calendar -->
            <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
              Calendar
            </span>
          </Link>
          
      <!-- Notifications button with tooltip -->
      <button class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300 group relative">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <!-- Tooltip for notifications -->
          <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
            Notifications
          </span>
        </button>
          
          <!-- User profile with dropdown -->
          <div ref="dropdownRef" class="relative">
            <button 
              @click.stop="toggleDropdown"
              class="flex items-center group focus:outline-none focus:ring-2 focus:ring-blue-400 rounded-full"
              aria-haspopup="true"
              :aria-expanded="isDropdownOpen"
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
              <!-- Dropdown indicator -->
              <svg 
                class="ml-1 h-4 w-4 text-gray-400 transition-transform duration-300 hidden sm:block"
                :class="{'rotate-180': isDropdownOpen}"
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <!-- Dropdown Menu -->
            <div 
              v-show="isDropdownOpen"
              :class="[
                'absolute bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50 w-56 mt-2',
                'right-0'
              ]"
            >
              <div class="py-2 px-4 border-b border-gray-100">
                <div class="text-sm font-medium text-gray-800">{{ user.name }}</div>
                <div class="text-xs text-gray-500 truncate">{{ user.email }}</div>
              </div>
              <div class="flex flex-col">
                <Link 
                  :href="route('profile.edit')" 
                  class="flex items-center px-4 py-2 text-gray-600 font-medium hover:text-blue-600 transition-all duration-300 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-400"
                  :aria-current="route().current('profile.edit') ? 'page' : undefined"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span class="ml-3">My Profile</span>
                </Link>
                
                <Link 
                  :href="route('logout')" 
                  method="post" 
                  as="button" 
                  class="w-full flex items-center px-4 py-2 text-red-600 font-medium hover:text-red-700 transition-all duration-300 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-400"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  <span class="ml-3">Sign Out</span>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

   <!-- Sidebar - with additional top padding to account for the fixed header -->
   <aside
      id="sidebar"
      :class="[
        'z-30 transition-all duration-300 ease-in-out border-r border-gray-200 bg-white shadow-lg shadow-blue-200/20 flex flex-col',
        sidebarExpanded ? 'md:w-64' : 'md:w-20',
        showingSidebar ? 'fixed left-0 w-64 h-full pt-16' : 'fixed -left-64 md:left-0 md:relative pt-16 h-auto min-h-screen'
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
      
      <!-- Bottom Actions (Empty for future implementation) -->
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

/* Add scale-175 which isn't standard in Tailwind */
.scale-175 {
  transform: scale(1.75);
}
</style>