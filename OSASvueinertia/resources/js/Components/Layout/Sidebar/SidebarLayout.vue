<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import SidebarHeader from './SidebarHeader.vue';
import NavigationItems from './NavigationItems.vue';
import SidebarFooter from './SidebarFooter.vue';
import MobileHeader from '@/Components/MobileHeader.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import Modal from '@/Components/Modal.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import axios from 'axios';

const props = defineProps({
  isAdmin: {
    type: Boolean,
    default: false
  },
  // New prop to control logo size
  logoSize: {
    type: String,
    default: 'extra-large' // 'default', 'medium', 'large'
  },
  // User prop to avoid Vue warning (though we use $page.props.auth.user internally)
  user: {
    type: Object,
    default: null
  }
});

// Emits
const emit = defineEmits(['close-mobile-menu']);

// Get page data
const page = usePage();

// Safely access user with proper fallback
const user = computed(() => {
  return page.props.auth?.user || null;
});

// Safely access unreadNotificationsCount with proper fallback
const unreadNotificationsCount = computed(() => {
  // Check if auth exists and if unreadNotificationsCount exists within auth
  return page.props.auth && 'unreadNotificationsCount' in page.props.auth
    ? page.props.auth.unreadNotificationsCount 
    : 0;
});

// State management
const showingSidebar = ref(false);
const sidebarExpanded = ref(true);
const windowWidth = ref(window.innerWidth); // Track window width

// Track if sidebar was expanded via toggle button (for content push behavior)
const expandedViaToggle = ref(true);

// Sign out confirmation modal state
const showSignOutModal = ref(false);

// User profile dropdown state
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

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
      return 'h-12 w-auto'; // Increased from h-10
    case 'large':
      return 'h-14 w-auto'; // Increased from h-12
    case 'extra-large':
      return 'h-18 w-auto'; // Increased from h-16
    default:
      return 'h-10 w-auto'; // Increased from h-8
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

// Calendar events state
const hasUpcomingEvents = ref(false);
const hasEventsToday = ref(false);
const badgeWasClicked = ref(false);
const eventCheckTimer = ref(null);
const isCheckingEvents = ref(false); // Prevent concurrent API calls

// Check for upcoming events - Optimized with debouncing
const checkUpcomingEvents = async () => {
  // Prevent concurrent API calls
  if (isCheckingEvents.value) return;
  
  isCheckingEvents.value = true;
  
  try {
    const response = await axios.get('/api/events');
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    // Filter active events only (not cancelled and not finished)
    const activeEvents = response.data.filter(event => {
      // Skip cancelled events
      if (event.status === 'cancelled') {
        return false;
      }
      
      // Check if event has finished
      const endDate = event.end_date ? new Date(event.end_date) : new Date(event.start_date);
      
      // If event has finished (current time is past end date), exclude it
      if (endDate < now) {
        return false;
      }
      
      return true;
    });
    
    hasEventsToday.value = activeEvents.some(event => {
      const eventDate = new Date(event.start_date);
      return eventDate >= today && eventDate < tomorrow;
    });
    
    hasUpcomingEvents.value = activeEvents.some(event => {
      const eventDate = new Date(event.start_date);
      return eventDate >= tomorrow;
    });
    
    // If no active events exist, reset the clicked state
    if (!hasEventsToday.value && !hasUpcomingEvents.value) {
      badgeWasClicked.value = false;
    }
  } catch (error) {
    console.error('Error fetching events:', error);
  } finally {
    isCheckingEvents.value = false;
  }
};

// Handle calendar click to hide badge
const handleCalendarClick = () => {
  badgeWasClicked.value = true;
};

// Listen for new events added or deleted
const handleEventAdded = () => {
  badgeWasClicked.value = false;
  checkUpcomingEvents();
};

const handleEventDeleted = () => {
  checkUpcomingEvents();
};

const handleEventCancelled = () => {
  checkUpcomingEvents();
};

// Computed property for showing badge
const showCalendarBadge = computed(() => {
  return (hasEventsToday.value || hasUpcomingEvents.value) && !badgeWasClicked.value;
});

// Global event listener for calendar updates
if (typeof window !== 'undefined') {
  window.addEventListener('calendar-event-added', handleEventAdded);
  window.addEventListener('calendar-event-deleted', handleEventDeleted);
  window.addEventListener('calendar-event-cancelled', handleEventCancelled);
}

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
  expandedViaToggle.value = sidebarExpanded.value; // Track that this was done via toggle
  // Save preference to localStorage
  try {
    localStorage.setItem('sidebarExpanded', String(sidebarExpanded.value));
  } catch (e) {
    console.error('Could not save sidebar preference');
  }
};

// Handle sidebar click - expands the sidebar when collapsed (without content push)
const handleSidebarClick = (event) => {
  // Only expand if sidebar is collapsed and we're on desktop
  if (!sidebarExpanded.value && window.innerWidth >= 768) {
    // Check if the click is on a navigation item or its children
    const isNavigationClick = event.target.closest('a[href]') || 
                             event.target.closest('button') ||
                             event.target.closest('[role="button"]');
    
    // Only expand if it's not a navigation click
    if (!isNavigationClick) {
      // Temporarily expand without saving to localStorage and without content push
      sidebarExpanded.value = true;
      expandedViaToggle.value = false; // Mark as temporary expansion (no content push)
    }
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
    // For desktop mode, check if click is outside the sidebar
    const sidebarElement = document.getElementById('sidebar');
    const desktopToggleButton = event.target.closest('button[aria-label="Toggle sidebar"]');
    
    // Don't collapse if clicking on the desktop toggle button or inside sidebar
    if (sidebarElement && !sidebarElement.contains(event.target) && !desktopToggleButton) {
      // Only collapse if sidebar was not expanded via toggle button (temporary expansion)
      if (!expandedViaToggle.value) {
        sidebarExpanded.value = false;
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
  windowWidth.value = window.innerWidth;
  
  if (window.innerWidth < 768) {
    sidebarExpanded.value = false;
    showingSidebar.value = false;
    expandedViaToggle.value = false;
  } else if (window.innerWidth >= 768) {
    // Only restore the saved preference when on desktop
    try {
      const savedExpanded = localStorage.getItem('sidebarExpanded');
      if (savedExpanded !== null) {
        sidebarExpanded.value = savedExpanded === 'true';
        expandedViaToggle.value = savedExpanded === 'true'; // Track initial state
      } else {
        sidebarExpanded.value = false; // Default to collapsed
        expandedViaToggle.value = false; // Default to toggle-based
      }
    } catch (e) {
      sidebarExpanded.value = false; // Default if localStorage fails
      expandedViaToggle.value = false;
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
      // Only collapse if sidebar was not expanded via toggle button (temporary expansion)
      if (!expandedViaToggle.value) {
        sidebarExpanded.value = false;
      }
    }
    // Also close dropdown if open
    if (isDropdownOpen.value) {
      isDropdownOpen.value = false;
    }
  }
};

// Handle sign out confirmation
const confirmSignOut = () => {
  showSignOutModal.value = true;
  isDropdownOpen.value = false;
};

const cancelSignOut = () => {
  showSignOutModal.value = false;
};

const proceedSignOut = () => {
  // Use Inertia router to post logout (handles CSRF automatically)
  router.post(route('logout'));
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', closeSidebarOnClickOutside);
  document.addEventListener('click', closeDropdownOnClickOutside);
  document.addEventListener('keydown', handleKeyDown);
  window.addEventListener('resize', checkWindowSize);
  
  // Check initial window size
  checkWindowSize();
  
  // Check for upcoming events
  checkUpcomingEvents();
  
  // Set up periodic checking for expired events (every 5 minutes)
  eventCheckTimer.value = setInterval(() => {
    checkUpcomingEvents();
  }, 5 * 60 * 1000);
});

onUnmounted(() => {
  document.removeEventListener('click', closeSidebarOnClickOutside);
  document.removeEventListener('click', closeDropdownOnClickOutside);
  document.removeEventListener('keydown', handleKeyDown);
  window.removeEventListener('resize', checkWindowSize);
  
  // Clean up timer
  if (eventCheckTimer.value) {
    clearInterval(eventCheckTimer.value);
  }
  
  // Clean up event listeners
  if (typeof window !== 'undefined') {
    window.removeEventListener('calendar-event-added', handleEventAdded);
    window.removeEventListener('calendar-event-deleted', handleEventDeleted);
    window.removeEventListener('calendar-event-cancelled', handleEventCancelled);
  }
});
</script>

<template>
  <div class="flex min-h-screen bg-gray-50 dark:bg-gray-900 relative overflow-x-hidden w-full">
    <!-- Fixed header for both desktop and mobile that spans the entire width -->
    <header class="fixed top-0 left-0 right-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm z-40 h-16">
      <div class="px-3 sm:px-4 md:px-6 h-full flex items-center justify-between">
        <div class="flex items-center">
          <!-- Desktop sidebar toggle button - visible only on desktop -->
          <button
            @click="toggleSidebarExpanded"
            class="hidden md:inline-flex items-center justify-center p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300 mr-2"
            aria-label="Toggle sidebar"
            :aria-expanded="sidebarExpanded"
            style="z-index: 50;"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <!-- Mobile menu button - visible only on mobile -->
          <button
            @click="toggleSidebar"
            class="mobile-menu-button md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300"
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
              <!-- Animated background glow, size adjusted to match larger logo -->
              <div 
                :class="{'scale-150': props.logoSize === 'medium', 'scale-175': props.logoSize === 'large', 'scale-200': props.logoSize === 'extra-large'}"
              ></div>
              <!-- Enhanced Logo with larger size class -->
              <ApplicationLogo :class="[logoSizeClass, 'block filter drop-shadow transform scale-125']" alt="ORBIT logo" />
            </div>
            <!-- Company name text with bolder, wider font - hidden on screens smaller than 660px -->
            <span 
              class="orbit-text ml-3 sm:ml-6 font-black text-lg sm:text-2xl md:text-3xl text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-green-500 uppercase select-none"
              style="font-family: 'Montserrat SemiCondensed', 'Inter Tight', 'Arial Narrow', 'Montserrat', 'Inter', 'Poppins', 'Segoe UI', 'Arial', sans-serif; letter-spacing: -0.08em; font-stretch: condensed; transform: scaleX(1.35);"
            >
              ORBIT
            </span>
          </Link>
        </div>
        
        <!-- Right side - user profile & actions -->
        <div class="flex items-center space-x-2 sm:space-x-3 md:space-x-4">
          <!-- Calendar link -->
          <Link 
            :href="route(calendarItem.route)"
            @click="handleCalendarClick"
            class="p-2 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300 group relative"
            aria-label="Calendar"
          >
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="calendarItem.icon" />
              </svg>
              <!-- Event notification badge -->
              <transition
                enter-active-class="transition-all duration-200 ease-out"
                leave-active-class="transition-all duration-300 ease-in"
                enter-from-class="opacity-0 scale-0"
                enter-to-class="opacity-100 scale-100"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-150"
              >
                <span 
                  v-if="showCalendarBadge && (hasEventsToday || hasUpcomingEvents)" 
                  :class="[
                    'absolute -top-1 -right-1 h-3 w-3 rounded-full',
                    hasEventsToday ? 'bg-red-500' : 'bg-blue-500'
                  ]"
                ></span>
              </transition>
            </div>
            <!-- Tooltip for calendar -->
            <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
              Calendar
            </span>
          </Link>
          
      <!-- Notifications button with tooltip and unread counter -->

   <NotificationDropdown :is-admin="isAdmin" />
          
          <!-- FAQ button - Hidden on mobile, shown on larger screens -->
          <Link 
            :href="route('faq')"
            class="hidden sm:flex p-2 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300 group relative"
            aria-label="FAQ"
          >
            <div class="relative">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 -960 960 960" fill="currentColor">
                <path d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm-36-154h74q0-33 7.5-52t42.5-52q26-26 41-49.5t15-56.5q0-56-41-86t-97-30q-57 0-92.5 30T342-618l66 26q5-18 22.5-39t53.5-21q32 0 48 17.5t16 38.5q0 20-12 37.5T506-526q-44 39-54 59t-10 73Zm38 314q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
              </svg>
            </div>
            <!-- Tooltip for FAQ -->
            <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
              FAQ
            </span>
          </Link>
          
          <!-- User profile with dropdown - Only show if user exists -->
          <div v-if="user" ref="dropdownRef" class="relative">
            <button 
              @click.stop="toggleDropdown"
              class="flex items-center group focus:outline-none focus:ring-2 focus:ring-blue-400 rounded-full"
              aria-haspopup="true"
              :aria-expanded="isDropdownOpen"
            >
              <div>
                <img
                  v-if="user?.profile_photo_url"
                  :src="user.profile_photo_url"
                  alt="Profile Photo"
                  class="h-10 w-10 rounded-full object-cover border border-gray-200 shadow-inner"
                />
                <div
                  v-else
                  class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-sm group-hover:shadow-md transition-all duration-300"
                >
                  {{ user?.name?.charAt(0)?.toUpperCase() || '?' }}
                </div>
              </div>
              <div class="ml-2 hidden sm:block">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300 truncate max-w-32 relative group/name" :title="user?.name || 'User'">
                  {{ user?.name || 'User' }}
                  <!-- Tooltip for truncated name -->
                  <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover/name:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50 pointer-events-none">
                    {{ user?.name || 'User' }}
                  </span>
                </div>
                <div v-if="user?.role?.slug === 'super_admin'" class="text-xs font-medium text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-900/50 px-2 py-0.5 rounded-full border border-purple-200 dark:border-purple-700">
                  Super Admin
                </div>
                <div v-else-if="isAdmin" class="text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/50 px-2 py-0.5 rounded-full border border-indigo-200 dark:border-indigo-700">
                  Admin
                </div>
              </div>
              <!-- Dropdown indicator -->
              <svg 
                class="ml-1 h-4 w-4 text-gray-400 dark:text-gray-500 transition-transform duration-300 hidden sm:block"
                :class="{'rotate-180': isDropdownOpen}"
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            
            <!-- Dropdown Menu with smooth animation -->
            <Transition
              enter-active-class="transition-all duration-200 ease-out"
              enter-from-class="opacity-0 -translate-y-2 scale-95"
              enter-to-class="opacity-100 translate-y-0 scale-100"
              leave-active-class="transition-all duration-150 ease-in"
              leave-from-class="opacity-100 translate-y-0 scale-100"
              leave-to-class="opacity-0 -translate-y-2 scale-95"
            >
              <div 
                v-show="isDropdownOpen"
                :class="[
                  'absolute bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden z-50 w-56 max-w-xs mt-2',
                  'right-0'
                ]"
              >
              <div class="py-2 px-4 border-b border-gray-100 dark:border-gray-700">
                <div class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate relative group/dropdown-name" :title="user?.name || 'User'">
                  {{ user?.name || 'User' }}
                  <!-- Tooltip for truncated name in dropdown -->
                  <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-900 dark:bg-gray-600 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover/dropdown-name:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50 pointer-events-none">
                    {{ user?.name || 'User' }}
                  </span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate" :title="user?.email || ''">{{ user?.email || '' }}</div>
              </div>
              <div class="flex flex-col">
                <Link 
                  :href="route('profile.edit')" 
                  class="flex items-center px-4 py-2 text-gray-600 dark:text-gray-300 font-medium hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300 hover:bg-blue-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                  :aria-current="route().current('profile.edit') ? 'page' : undefined"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="ml-3">Settings</span>
                </Link>

                <!-- FAQ Link - Visible on mobile, hidden on larger screens -->
                <Link 
                  :href="route('faq')" 
                  class="flex sm:hidden items-center px-4 py-2 text-gray-600 dark:text-gray-300 font-medium hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300 hover:bg-blue-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                  :aria-current="route().current('faq') ? 'page' : undefined"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 -960 960 960" fill="currentColor">
                    <path d="M478-240q21 0 35.5-14.5T528-290q0-21-14.5-35.5T478-340q-21 0-35.5 14.5T428-290q0 21 14.5 35.5T478-240Zm-36-154h74q0-33 7.5-52t42.5-52q26-26 41-49.5t15-56.5q0-56-41-86t-97-30q-57 0-92.5 30T342-618l66 26q5-18 22.5-39t53.5-21q32 0 48 17.5t16 38.5q0 20-12 37.5T506-526q-44 39-54 59t-10 73Zm38 314q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z"/>
                  </svg>
                  <span class="ml-3">FAQ</span>
                </Link>

                <!-- Theme Toggle with Modern Switch Design -->
                <div 
                  class="flex items-center px-4 py-2 text-gray-600 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                  @click.stop
                >
                  <!-- Dynamic Theme Icon -->
                  <div class="relative">
                    <!-- Light mode icon -->
                    <svg 
                      v-if="$page.props.theme?.current === 'light' || (!$page.props.theme?.current && !$page.props.theme?.isDark)"
                      xmlns="http://www.w3.org/2000/svg" 
                      class="h-5 w-5 text-amber-500 transition-all duration-300" 
                      fill="none" 
                      viewBox="0 0 24 24" 
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    
                    <!-- Dark mode icon -->
                    <svg 
                      v-else-if="$page.props.theme?.current === 'dark' || $page.props.theme?.isDark"
                      xmlns="http://www.w3.org/2000/svg" 
                      class="h-5 w-5 text-blue-400 transition-all duration-300" 
                      fill="none" 
                      viewBox="0 0 24 24" 
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    
                    <!-- System/Auto mode icon -->
                    <svg 
                      v-else
                      xmlns="http://www.w3.org/2000/svg" 
                      class="h-5 w-5 text-purple-500 transition-all duration-300" 
                      fill="none" 
                      viewBox="0 0 24 24" 
                      stroke="currentColor"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  
                  <span class="ml-3 flex-1">Theme</span>
                  
                  <!-- Modern Three-Way Toggle Switch -->
                  <div class="relative" @click.stop>
                    <ThemeToggle variant="dropdown" size="sm" />
                  </div>
                </div>
                
                <button
                  type="button"
                  @click="confirmSignOut"
                  class="w-full flex items-center px-4 py-2 text-red-600 dark:text-red-400 font-medium hover:text-red-700 dark:hover:text-red-300 transition-all duration-300 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-red-400"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  <span class="ml-3">Sign Out</span>
                </button>
              </div>
              </div>
            </Transition>
          </div>
          
          <!-- Fallback if no user is available -->
          <div v-else class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
        </div>
      </div>
    </header>

   <!-- Sidebar - with additional top padding to account for the fixed header -->
   <aside
      id="sidebar"
      :class="[
        'z-30 transition-all duration-200 ease-in-out border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg shadow-blue-200/20 dark:shadow-gray-900/50 flex flex-col overflow-hidden',
        // Desktop positioning logic - always fixed positioned
        'md:fixed md:left-0 md:pt-16 md:h-screen',
        // Width logic - always overlays content when expanded via click
        sidebarExpanded ? 'md:w-64' : 'md:w-20',
        // Mobile positioning - use transform for smooth animation
        'fixed left-0 w-64 h-full pt-16 md:transform-none',
        // Mobile transform for smooth slide animation
        showingSidebar ? 'transform translate-x-0' : 'transform -translate-x-full'
      ]"
      aria-label="Navigation sidebar"
      @click="handleSidebarClick"
    >
      <!-- Sidebar Header -->
      <SidebarHeader 
        :is-admin="isAdmin" 
        :sidebar-expanded="sidebarExpanded"
        :showing-sidebar="showingSidebar"
        :class="[
          'flex-shrink-0 transition-opacity duration-200',
          sidebarExpanded || showingSidebar ? 'opacity-100' : 'opacity-100'
        ]"
      />
      
      <!-- Navigation Links -->
      <NavigationItems 
        :is-admin="isAdmin" 
        :sidebar-expanded="sidebarExpanded" 
        :showing-sidebar="showingSidebar"
        :class="[
          'flex-1 overflow-y-auto overflow-x-hidden transition-opacity duration-200',
          sidebarExpanded || showingSidebar ? 'opacity-100' : 'opacity-100'
        ]"
      />
      
      <!-- Bottom Actions (Empty for future implementation) -->
      <SidebarFooter 
        :sidebar-expanded="sidebarExpanded" 
        :showing-sidebar="showingSidebar"
        :class="[
          'flex-shrink-0 transition-opacity duration-200',
          sidebarExpanded || showingSidebar ? 'opacity-100' : 'opacity-100'
        ]"
      />
    </aside>

    <!-- Main Content - with additional top padding to account for the fixed header -->
    <div 
        :class="[
          'flex-1 flex flex-col pt-16 overflow-x-hidden w-full min-w-0 transition-all duration-200 ease-in-out',
          // Push content only when expanded via toggle button
          (sidebarExpanded && expandedViaToggle) ? 'md:ml-64' : 'md:ml-20'
        ]"
      >
      <!-- Page Content -->
      <main class="flex-1 bg-gray-50 dark:bg-gray-900 relative p-4 sm:p-6 pb-8 overflow-x-hidden w-full min-w-0">
        <!-- Content provided via slot -->
        <slot></slot>
      </main>
    </div>
    
    <!-- Overlay for mobile sidebar -->
    <div 
      v-if="showingSidebar" 
      @click="closeSidebar" 
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-20 md:hidden animate-fadeIn"
      aria-hidden="true"
    ></div>
  </div>
  <!-- Sign Out Confirmation Modal -->
  <Modal :show="showSignOutModal" @close="cancelSignOut">
    <div class="p-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md mx-auto">
      <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">Confirm Sign Out</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-8">Are you sure you want to sign out?</p>
      <div class="flex justify-end gap-2 w-full">
        <button
          @click="cancelSignOut"
          type="button"
          class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl shadow-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group"
        >
          <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-400 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
          Cancel
        </button>
        <button
          @click="proceedSignOut"
          type="button"
          class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-sm font-medium text-white rounded-xl shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group"
        >
          <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
          Sign Out
        </button>
      </div>
    </div>
  </Modal>
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

/* Mobile sidebar animation improvements */
@media (max-width: 767px) {
  #sidebar {
    will-change: transform;
    backface-visibility: hidden;
    transform-style: preserve-3d;
    transition: transform 0.2s ease-in-out;
  }
}

/* Prevent content overflow and horizontal scrollbars */
#sidebar * {
  min-width: 0;
}

/* Add scale-175 which isn't standard in Tailwind */
.scale-175 {
  transform: scale(1.75);
}

.scale-200 {
  transform: scale(2);
}

/* Define h-18 which isn't standard in Tailwind */
.h-18 {
  height: 4.5rem;
}

/* Hide ORBIT text on screens smaller than 660px for more space */
.orbit-text {
  display: none;
}

@media (min-width: 330px) {
  .orbit-text {
    display: inline-block;
  }
}
</style>