<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  sidebarExpanded: {
    type: Boolean,
    required: true
  },
  showingSidebar: {
    type: Boolean,
    required: true
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isDropdownOpen = ref(false);
// Calculate dropdown position based on sidebar state
const dropdownPosition = computed(() => {
  // If sidebar is expanded or showing on mobile, dropdown opens above
  if (props.sidebarExpanded || props.showingSidebar) {
    return 'bottom-full mb-2 left-0';
  } 
  // If sidebar is collapsed on desktop, dropdown opens to the right and higher
  return 'left-full ml-2 top-auto bottom-0';
});

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

// Close dropdown when clicking outside
const dropdownRef = ref(null);

// Close dropdown when sidebar state changes
watch(() => props.sidebarExpanded, () => {
  isDropdownOpen.value = false;
});

onMounted(() => {
  // Add click outside listener when mounted
  if (typeof window !== 'undefined') {
    window.addEventListener('click', (event) => {
      if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
      }
    });
  }
});
</script>

<template>
  <div class="p-4 border-t border-gray-200">
    <!-- Profile Avatar Button -->
    <div ref="dropdownRef" class="relative">
      <button 
        @click.stop="toggleDropdown"
        class="w-full flex items-center p-2 rounded-lg hover:bg-gray-100 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400"
        :class="{'justify-center': !sidebarExpanded && !showingSidebar}"
      >
        <div class="flex-shrink-0">
          <div 
            class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner"
            :title="user.name"
          >
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
        </div>
        <div v-if="sidebarExpanded || showingSidebar" class="ml-3 min-w-0">
          <div class="text-sm font-medium text-gray-800 truncate">{{ user.name }}</div>
          <div class="text-xs text-gray-500 truncate">{{ user.email }}</div>
          <div v-if="isAdmin" class="mt-1">
            <span class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full border border-indigo-200 shadow-inner">
              Admin
            </span>
          </div>
        </div>
        
        <!-- Dropdown Icon -->
        <svg 
          v-if="sidebarExpanded || showingSidebar"
          xmlns="http://www.w3.org/2000/svg" 
          class="h-5 w-5 ml-auto text-gray-400 transition-transform duration-300" 
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
          :class="{'rotate-180': isDropdownOpen}"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      
      <!-- Dropdown Menu with Dynamic Positioning -->
      <div 
        v-show="isDropdownOpen"
        :class="[
          'absolute bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50 w-56',
          dropdownPosition
        ]"
      >
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
</template>