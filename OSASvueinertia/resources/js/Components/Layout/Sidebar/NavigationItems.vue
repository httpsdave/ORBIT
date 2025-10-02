<script setup>
import { computed, ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import NavigationItem from './NavigationItem.vue';
import SidebarTooltipButton from './SidebarTooltipButton.vue';

const props = defineProps({
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

const navElementsVisible = ref(false);

// Navigation items for reusable rendering
const adminNavItems = [
  {
    name: 'Dashboard',
    route: 'admin.dashboard',
    icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
  },
  {
    name: 'Users',
    route: 'admin.users',
    icon: 'M640-400q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM400-160v-76q0-21 10-40t28-30q45-27 95.5-40.5T640-360q56 0 106.5 13.5T842-306q18 11 28 30t10 40v76H400Zm86-80h308q-35-20-74-30t-80-10q-41 0-80 10t-74 30Zm154-240q17 0 28.5-11.5T680-520q0-17-11.5-28.5T640-560q-17 0-28.5 11.5T600-520q0 17 11.5 28.5T640-480Zm0-40Zm0 280ZM120-400v-80h320v80H120Zm0-320v-80h480v80H120Zm324 160H120v-80h360q-14 17-22.5 37T444-560Z'
  },
  {
    name: 'Organizations',
    route: 'admin.student-orgs.index',
    exactMatch: false,
    checkStartsWith: true,
    icon: 'm160-419 101-101-101-101L59-520l101 101Zm540-21 100-160 100 160H700Zm-220-40q-50 0-85-35t-35-85q0-51 35-85.5t85-34.5q51 0 85.5 34.5T600-600q0 50-34.5 85T480-480Zm0-160q-17 0-28.5 11.5T440-600q0 17 11.5 28.5T480-560q17 0 28.5-11.5T520-600q0-17-11.5-28.5T480-640Zm0 40ZM0-240v-63q0-44 44.5-70.5T160-400q13 0 25 .5t23 2.5q-14 20-21 43t-7 49v65H0Zm240 0v-65q0-65 66.5-105T480-450q108 0 174 40t66 105v65H240Zm560-160q72 0 116 26.5t44 70.5v63H780v-65q0-26-6.5-49T754-397q11-2 22.5-2.5t23.5-.5Zm-320 30q-57 0-102 15t-53 35h311q-9-20-53.5-35T480-370Zm0 50Z'
  },
  {
    name: 'Colleges',
    route: 'admin.colleges.index',
    exactMatch: false,
    checkStartsWith: true,
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
  }
];

const userNavItems = [
  {
    name: 'Dashboard',
    route: 'dashboard',
    icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
  },
  {
    name: 'Archive',
    route: 'archive.index',
    icon: 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'
  },
  {
    name: 'Colleges',
    route: 'colleges.index',
    exactMatch: false,
    checkStartsWith: true,
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
  },
  {
    name: 'Organizations',
    route: 'student-orgs.index',
    exactMatch: false,
    checkStartsWith: true,
    icon: 'm160-419 101-101-101-101L59-520l101 101Zm540-21 100-160 100 160H700Zm-220-40q-50 0-85-35t-35-85q0-51 35-85.5t85-34.5q51 0 85.5 34.5T600-600q0 50-34.5 85T480-480Zm0-160q-17 0-28.5 11.5T440-600q0 17 11.5 28.5T480-560q17 0 28.5-11.5T520-600q0-17-11.5-28.5T480-640Zm0 40ZM0-240v-63q0-44 44.5-70.5T160-400q13 0 25 .5t23 2.5q-14 20-21 43t-7 49v65H0Zm240 0v-65q0-65 66.5-105T480-450q108 0 174 40t66 105v65H240Zm560-160q72 0 116 26.5t44 70.5v63H780v-65q0-26-6.5-49T754-397q11-2 22.5-2.5t23.5-.5Zm-320 30q-57 0-102 15t-53 35h311q-9-20-53.5-35T480-370Zm0 50Z'
  }
];

// Current navigation items based on user role
const navItems = computed(() => props.isAdmin ? adminNavItems : userNavItems);

onMounted(() => {
  // Add initial transition delay for navigation elements
  setTimeout(() => {
    navElementsVisible.value = true;
  }, 300);
});

// Prevent event bubbling when clicking on action buttons
const handleActionClick = (event) => {
  event.stopPropagation();
};
</script>

<template>
  <!-- Navigation Links - Wrap in overflow container -->
  <div class="flex-grow overflow-y-auto custom-scrollbar flex flex-col">
    <!-- Main navigation section -->
    <nav class="mt-4 px-2 space-y-4 flex-grow" aria-label="Main navigation">
      <!-- Navigation items based on role -->
      <template v-for="(item, index) in navItems" :key="`nav-item-${index}`">
        <NavigationItem 
          :item="item" 
          :sidebar-expanded="sidebarExpanded" 
          :showing-sidebar="showingSidebar" 
        />
        
        <!-- Place Applications button directly after Colleges (last item in admin navigation) -->
        <div v-if="isAdmin && item.route === 'admin.colleges.index'" class="py-2">
          <SidebarTooltipButton
            :tooltip="'All Applications'"
            :sidebar-expanded="sidebarExpanded"
            :showing-sidebar="showingSidebar"
          >
            <Link
              :href="route('applications.index')"
              @click="handleActionClick"
              class="w-full flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white text-base font-medium rounded-xl shadow-md hover:shadow-blue-300/70 dark:hover:shadow-blue-500/30 transition-all duration-300 relative overflow-hidden group focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
              :class="{'px-2': !sidebarExpanded && !showingSidebar}"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-0': !sidebarExpanded && !showingSidebar, 'mr-2': sidebarExpanded || showingSidebar}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span v-if="sidebarExpanded || showingSidebar"> All Applications</span>
              <span v-else class="sr-only">Applications</span>
            </Link>
          </SidebarTooltipButton>
          
          <!-- Calendar link after All Applications -->
          <div class="mt-4">
            <NavigationItem 
              :item="{
                name: 'Archive Management',
                route: 'admin.archive.index',
                icon: 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'
              }" 
              :sidebar-expanded="sidebarExpanded" 
              :showing-sidebar="showingSidebar" 
            />
          </div>
          
          <!-- Calendar link after Archive Management -->
          <div class="mt-4">
            <NavigationItem 
              :item="{
                name: 'Calendar',
                route: 'calendar',
                icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
              }" 
              :sidebar-expanded="sidebarExpanded" 
              :showing-sidebar="showingSidebar" 
            />
          </div>
        </div>
        
        <!-- Place New Application button directly after Organizations for regular users -->
        <div v-if="!isAdmin && item.route === 'student-orgs.index'" class="py-2">
          <SidebarTooltipButton
            :tooltip="'New Application'"
            :sidebar-expanded="sidebarExpanded"
            :showing-sidebar="showingSidebar"
          >
            <Link
              :href="route('applications.index')"
              @click="handleActionClick"
              class="w-full flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white text-base font-medium rounded-xl shadow-md hover:shadow-blue-300/70 dark:hover:shadow-blue-500/30 transition-all duration-300 relative overflow-hidden group focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
              :class="{'px-2': !sidebarExpanded && !showingSidebar}"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="{'mr-0': !sidebarExpanded && !showingSidebar, 'mr-2': sidebarExpanded || showingSidebar}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span v-if="sidebarExpanded || showingSidebar">New Application</span>
              <span v-else class="sr-only">New Application</span>
            </Link>
          </SidebarTooltipButton>
          
          <!-- Calendar link after Organizations for regular users -->
          <div class="mt-4">
            <NavigationItem 
              :item="{
                name: 'Calendar',
                route: 'calendar',
                icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
              }" 
              :sidebar-expanded="sidebarExpanded" 
              :showing-sidebar="showingSidebar" 
            />
          </div>
        </div>
      </template>
    </nav>
    
    <!-- Calendar section has been removed -->
  </div>
</template>

<style scoped>
/* Custom scrollbar styling */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(203, 213, 225, 0.4) transparent;
}

@media (prefers-color-scheme: dark) {
  .custom-scrollbar {
    scrollbar-color: rgba(75, 85, 99, 0.4) transparent;
  }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(203, 213, 225, 0.4);
  border-radius: 9999px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(75, 85, 99, 0.4);
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(148, 163, 184, 0.6);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(107, 114, 128, 0.6);
}
</style>