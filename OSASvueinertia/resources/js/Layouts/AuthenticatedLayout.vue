<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const showingNavigationDropdown = ref(false);
const user = computed(() => usePage().props.auth.user);
const isAdmin = computed(() => user.value?.role?.slug === 'admin');
const isScrolled = ref(false);

// Handle scroll events for dynamic navigation appearance
const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

// Close mobile menu when clicking outside
const closeNavigationDropdown = () => {
  showingNavigationDropdown.value = false;
};
</script>

<template>
  <div>
    <div class="min-h-screen bg-gray-50">
      <nav 
        :class="[
          'fixed w-full z-50 transition-all duration-300', 
          isScrolled ? 'bg-white shadow-md' : 'bg-white/95'
        ]"
      >
        <!-- Primary Navigation Menu -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between items-center">
            <div class="flex items-center">
              <!-- Logo -->
              <div class="flex shrink-0 items-center">
                <Link :href="isAdmin ? route('admin.dashboard') : route('dashboard')" class="flex items-center">
                  <ApplicationLogo class="block h-8 w-auto" />
                  <span class="ml-2 font-semibold text-lg text-blue-600">ORBIT</span>
                </Link>
              </div>

              <!-- Navigation Links -->
              <div class="hidden md:flex ml-8 space-x-6">
                <!-- Admin Navigation -->
                <template v-if="isAdmin">
                  <NavLink 
                    :href="route('admin.dashboard')" 
                    :active="route().current('admin.dashboard')" 
                    class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-colors"
                    activeClass="text-blue-600 border-b-2 border-blue-600"
                  >
                    Dashboard
                  </NavLink>
                  <NavLink 
                    :href="route('admin.users')" 
                    :active="route().current('admin.users')" 
                    class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-colors"
                    activeClass="text-blue-600 border-b-2 border-blue-600"
                  >
                    Users
                  </NavLink>
                </template>
                
                <!-- Regular User Navigation -->
                <template v-else>
                  <NavLink 
                    :href="route('dashboard')" 
                    :active="route().current('dashboard')" 
                    class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-colors"
                    activeClass="text-blue-600 border-b-2 border-blue-600"
                  >
                    Dashboard
                  </NavLink>
                  <NavLink 
                    :href="route('applications.index')" 
                    :active="route().current('applications.index')" 
                    class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-colors"
                    activeClass="text-blue-600 border-b-2 border-blue-600"
                  >
                    Applications
                  </NavLink>
                </template>
                
                <!-- Common navigation item -->
                <NavLink 
                  :href="route('myhome')" 
                  :active="route().current('myhome')" 
                  class="px-3 py-2 text-gray-600 font-medium hover:text-blue-600 transition-colors"
                  activeClass="text-blue-600 border-b-2 border-blue-600"
                >
                  Home
                </NavLink>
              </div>
            </div>

            <div class="hidden md:flex items-center space-x-4">
              
              
              <!-- Role Indicator for admins -->
              <div v-if="isAdmin" class="px-3 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
                Admin
              </div>
              
              <!-- Settings Dropdown -->
              <Dropdown align="right" width="48">
                <template #trigger>
                  <button
                    type="button"
                    class="flex items-center rounded-full bg-white p-1 text-gray-600 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  >
                    <span class="sr-only">Open user menu</span>
                    <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                  </button>
                </template>

                <template #content>
                  <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-medium text-gray-900">{{ $page.props.auth.user.name }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $page.props.auth.user.email }}</p>
                  </div>
                  <DropdownLink :href="route('profile.edit')" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    My Profile
                  </DropdownLink>
                  <DropdownLink :href="route('logout')" method="post" as="button" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign Out
                  </DropdownLink>
                </template>
              </Dropdown>
            </div>

            <!-- Mobile menu button -->
            <div class="flex md:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                aria-expanded="false"
                aria-controls="mobile-menu"
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
          :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}"
          class="md:hidden border-t border-gray-200"
        >
          <div class="space-y-1 px-2 pb-3 pt-2 bg-white">
            <!-- Admin Mobile Navigation -->
            <template v-if="isAdmin">
              <ResponsiveNavLink 
                :href="route('admin.dashboard')" 
                :active="route().current('admin.dashboard')"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600"
                activeClass="bg-blue-50 text-blue-600"
              >
                Dashboard
              </ResponsiveNavLink>
              <ResponsiveNavLink 
                :href="route('admin.users')" 
                :active="route().current('admin.users')"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600"
                activeClass="bg-blue-50 text-blue-600"
              >
                Users
              </ResponsiveNavLink>
            </template>
            
            <!-- Regular User Mobile Navigation -->
            <template v-else>
              <ResponsiveNavLink 
                :href="route('dashboard')" 
                :active="route().current('dashboard')"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600"
                activeClass="bg-blue-50 text-blue-600"
              >
                Dashboard
              </ResponsiveNavLink>
              <ResponsiveNavLink 
                :href="route('applications.index')" 
                :active="route().current('applications.index')"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600"
                activeClass="bg-blue-50 text-blue-600"
              >
                Applications
              </ResponsiveNavLink>
            </template>
            
            <!-- Common mobile navigation item -->
            <ResponsiveNavLink 
              :href="route('myhome')" 
              :active="route().current('myhome')"
              class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600"
              activeClass="bg-blue-50 text-blue-600"
            >
              Home
            </ResponsiveNavLink>

            <!-- Quick action in mobile -->
            <div class="px-3 py-3">
              <Link
                :href="route('applications.index')"
                class="w-full flex justify-center items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-base font-medium rounded-md transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Application
              </Link>
            </div>
          </div>

          <!-- Mobile user menu -->
          <div class="border-t border-gray-200 pt-4 pb-3 bg-gray-50">
            <div class="flex items-center px-4">
              <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
              </div>
              <div class="ml-3">
                <div class="text-base font-medium text-gray-800">{{ $page.props.auth.user.name }}</div>
                <div class="text-sm font-medium text-gray-500">{{ $page.props.auth.user.email }}</div>
              </div>
              <div v-if="isAdmin" class="ml-auto">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                  Admin
                </span>
              </div>
            </div>
            <div class="mt-3 space-y-1 px-2">
              <ResponsiveNavLink 
                :href="route('profile.edit')"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-100"
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
                class="w-full text-left block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-red-50"
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

      <!-- Page Heading -->
      <header class="pt-16 bg-white" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="pt-16">
        <slot />
      </main>
      
      <!-- Overlay for mobile menu -->
      <div 
        v-if="showingNavigationDropdown" 
        @click="closeNavigationDropdown" 
        class="fixed inset-0 bg-black bg-opacity-25 z-40 md:hidden"
      ></div>
    </div>
  </div>
</template>