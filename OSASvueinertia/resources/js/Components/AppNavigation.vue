<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const props = defineProps({
  isAdmin: {
    type: Boolean,
    default: false
  }
});

// Emits
const emit = defineEmits(['close-mobile-menu']);

const showingNavigationDropdown = ref(false);
const user = computed(() => usePage().props.auth.user);
const isScrolled = ref(false);

// Handle scroll events for dynamic navigation appearance
const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  // Add initial transition delay for navigation elements
  setTimeout(() => {
    navElementsVisible.value = true;
  }, 300);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

// Close mobile menu when clicking outside
const closeNavigationDropdown = () => {
  showingNavigationDropdown.value = false;
  emit('close-mobile-menu');
};

// Animation states
const navElementsVisible = ref(false);
</script>

<template>
  <div>
    <nav 
      :class="[
        'fixed w-full z-50 transition-all duration-500', 
        isScrolled ? 'bg-white shadow-lg shadow-blue-200/50' : 'bg-white/95 backdrop-blur-md'
      ]"
    >
      <!-- Primary Navigation Menu -->
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between items-center">
          <div class="flex items-center">
            <!-- Logo -->
            <div class="flex shrink-0 items-center">
              <Link :href="isAdmin ? route('admin.dashboard') : route('dashboard')" 
                    class="flex items-center transform transition-all duration-500 hover:scale-105"
                    :class="{'translate-x-0 opacity-100': navElementsVisible, '-translate-x-4 opacity-0': !navElementsVisible}">
                <div class="relative">
                  <div class="absolute inset-0 bg-blue-500 opacity-20 blur-sm rounded-lg transform rotate-45 animate-pulse"></div>
                  <ApplicationLogo class="block h-8 w-auto filter drop-shadow" />
                </div>
                <span class="ml-2 font-semibold text-lg text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-green-500">ORBIT</span>
              </Link>
            </div>

            <!-- Colored banner (similar to login form) -->
            <div class="hidden md:flex ml-6 overflow-hidden rounded-lg h-6 items-center">
              <div class="w-6 h-1 bg-blue-500 animate-pulse rounded-full mr-1" style="animation-delay: 0.2s;"></div>
              <div class="w-4 h-1 bg-green-500 animate-pulse rounded-full mr-1" style="animation-delay: 0.4s;"></div>
              <div class="w-2 h-1 bg-yellow-500 animate-pulse rounded-full" style="animation-delay: 0.6s;"></div>
            </div>

            <!-- Navigation Links -->
            <div class="hidden md:flex ml-6 space-x-1">
              <!-- Admin Navigation -->
              <template v-if="isAdmin">
                <NavLink 
                  :href="route('admin.dashboard')" 
                  :active="route().current('admin.dashboard')" 
                  class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-all duration-300 rounded-lg hover:bg-blue-100"
                  activeClass="text-blue-600 bg-blue-50 shadow-sm"
                  :class="{'translate-y-0 opacity-100': navElementsVisible, 'translate-y-4 opacity-0': !navElementsVisible}"
                  style="transition-delay: 100ms;"
                >
                  Dashboard
                </NavLink>
                <NavLink 
                  :href="route('admin.users')" 
                  :active="route().current('admin.users')" 
                  class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-all duration-300 rounded-lg hover:bg-blue-100"
                  activeClass="text-blue-600 bg-blue-50 shadow-sm"
                  :class="{'translate-y-0 opacity-100': navElementsVisible, 'translate-y-4 opacity-0': !navElementsVisible}"
                  style="transition-delay: 200ms;"
                >
                  Users
                </NavLink>
              </template>
              
              <!-- Regular User Navigation -->
              <template v-else>
                <NavLink 
                  :href="route('dashboard')" 
                  :active="route().current('dashboard')" 
                  class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-all duration-300 rounded-lg hover:bg-blue-100"
                  activeClass="text-blue-600 bg-blue-50 shadow-sm"
                  :class="{'translate-y-0 opacity-100': navElementsVisible, 'translate-y-4 opacity-0': !navElementsVisible}"
                  style="transition-delay: 100ms;"
                >
                  Dashboard
                </NavLink>
                <NavLink 
                  :href="route('applications.index')" 
                  :active="route().current('applications.index')" 
                  class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-all duration-300 rounded-lg hover:bg-blue-100"
                  activeClass="text-blue-600 bg-blue-50 shadow-sm"
                  :class="{'translate-y-0 opacity-100': navElementsVisible, 'translate-y-4 opacity-0': !navElementsVisible}"
                  style="transition-delay: 200ms;"
                >
                  Applications
                </NavLink>
              </template>
              
              <!-- Common navigation item -->
              <NavLink 
                :href="route('myhome')" 
                :active="route().current('myhome')" 
                class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-all duration-300 rounded-lg hover:bg-blue-100"
                activeClass="text-blue-600 bg-blue-50 shadow-sm"
                :class="{'translate-y-0 opacity-100': navElementsVisible, 'translate-y-4 opacity-0': !navElementsVisible}"
                style="transition-delay: 300ms;"
              >
                Home
              </NavLink>
            </div>
          </div>

          <div class="hidden md:flex items-center space-x-4">
          
            <!-- Role Indicator for admins -->
            <div 
              v-if="isAdmin" 
              class="px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full border border-indigo-200 shadow-inner transition-all duration-300"
              :class="{'translate-y-0 opacity-100': navElementsVisible, 'translate-y-4 opacity-0': !navElementsVisible}"
              style="transition-delay: 500ms;"
            >
              Admin
            </div>
            
            <!-- Settings Dropdown -->
            <Dropdown 
              align="right" 
              width="48" 
              :class="{'translate-y-0 opacity-100': navElementsVisible, 'translate-y-4 opacity-0': !navElementsVisible}"
              style="transition-delay: 600ms;"
            >
              <template #trigger>
                <button
                  type="button"
                  class="flex items-center rounded-full bg-blue-50 p-1 text-gray-600 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white transition-all duration-300 shadow-md hover:shadow-blue-300/30"
                >
                  <span class="sr-only">Open user menu</span>
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </div>
                </button>
              </template>

              <template #content>
                <div class="px-4 py-3 border-b border-gray-200 bg-white">
                  <p class="text-sm font-medium text-gray-900">{{ $page.props.auth.user.name }}</p>
                  <p class="text-xs text-gray-500 mt-1">{{ $page.props.auth.user.email }}</p>
                </div>
                <div class="bg-white">
                  <DropdownLink :href="route('profile.edit')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    My Profile
                  </DropdownLink>
                  <DropdownLink :href="route('logout')" method="post" as="button" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign Out
                  </DropdownLink>
                </div>
              </template>
            </Dropdown>
          </div>

          <!-- Mobile menu button -->
          <div class="flex md:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all duration-300"
              aria-expanded="false"
              aria-controls="mobile-menu"
              :class="{'bg-blue-50': showingNavigationDropdown}"
            >
              <span class="sr-only">Open main menu</span>
              <svg 
                :class="{'hidden': showingNavigationDropdown, 'block': !showingNavigationDropdown }"
                xmlns="http://www.w3.org/2000/svg" 
                class="h-6 w-6" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg 
                :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                xmlns="http://www.w3.org/2000/svg" 
                class="h-6 w-6" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu, show/hide based on menu state -->
      <div
        id="mobile-menu"
        :class="{'block animate-fadeIn': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}"
        class="md:hidden border-t border-gray-200"
      >
        <div class="space-y-1 px-2 pb-3 pt-2 bg-white backdrop-blur-lg">
          <!-- Admin Mobile Navigation -->
          <template v-if="isAdmin">
            <ResponsiveNavLink 
              :href="route('admin.dashboard')" 
              :active="route().current('admin.dashboard')"
              class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300"
              activeClass="bg-blue-50 text-blue-600 border-l-2 border-blue-500"
            >
              <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
              </div>
            </ResponsiveNavLink>
            <ResponsiveNavLink 
              :href="route('admin.users')" 
              :active="route().current('admin.users')"
              class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300"
              activeClass="bg-blue-50 text-blue-600 border-l-2 border-blue-500"
            >
              <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Users
              </div>
            </ResponsiveNavLink>
          </template>
          
          <!-- Regular User Mobile Navigation -->
          <template v-else>
            <ResponsiveNavLink 
              :href="route('dashboard')" 
              :active="route().current('dashboard')"
              class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300"
              activeClass="bg-blue-50 text-blue-600 border-l-2 border-blue-500"
            >
              <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
              </div>
            </ResponsiveNavLink>
            <ResponsiveNavLink 
              :href="route('applications.index')" 
              :active="route().current('applications.index')"
              class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300"
              activeClass="bg-blue-50 text-blue-600 border-l-2 border-blue-500"
            >
              <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Applications
              </div>
            </ResponsiveNavLink>
          </template>
          
          <!-- Common mobile navigation item -->
          <ResponsiveNavLink 
            :href="route('myhome')" 
            :active="route().current('myhome')"
            class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300"
            activeClass="bg-blue-50 text-blue-600 border-l-2 border-blue-500"
          >
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              Home
            </div>
          </ResponsiveNavLink>

          <!-- Quick action in mobile -->
          <div class="px-3 py-3">
            <Link
              :href="route('applications.index')"
              class="w-full flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white text-base font-medium rounded-xl shadow-md shadow-blue-300/50 hover:shadow-blue-300/70 transition-all duration-300 relative overflow-hidden group"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              New Application
            </Link>
          </div>
        </div>

        <!-- Mobile user menu -->
        <div class="border-t border-gray-200 pt-4 pb-3 bg-white/95 backdrop-blur-lg">
          <div class="flex items-center px-4">
            <div class="flex-shrink-0">
              <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
            </div>
            <div class="ml-3">
              <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
              <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
            </div>
            <div v-if="isAdmin" class="ml-auto">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 border border-indigo-200">
                Admin
              </span>
            </div>
          </div>
          <div class="mt-3 space-y-1 px-2">
            <ResponsiveNavLink 
              :href="route('profile.edit')"
              class="block px-3 py-2 rounded-lg text-base font-medium text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition-all duration-300"
            >
              <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                My Profile
              </div>
            </ResponsiveNavLink>
            <ResponsiveNavLink 
              :href="route('logout')" 
              method="post" 
              as="button"
              class="w-full text-left block px-3 py-2 rounded-lg text-base font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-300"
            >
              <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Sign Out
              </div>
            </ResponsiveNavLink>
          </div>
        </div>
      </div>
    </nav>

    <!-- Overlay for mobile menu -->
    <div 
      v-if="showingNavigationDropdown" 
      @click="closeNavigationDropdown" 
      class="fixed inset-0 bg-black bg-opacity-20 backdrop-blur-sm z-40 md:hidden animate-fadeIn"
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
</style>