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
    name: 'Plan of Activities',
    route: 'plan-of-activities.index',
    icon: 'm80-520 200-360 200 360H80Zm200 400q-66 0-113-47t-47-113q0-67 47-113.5T280-440q66 0 113 47t47 113q0 66-47 113t-113 47Zm0-80q33 0 56.5-23.5T360-280q0-33-23.5-56.5T280-360q-33 0-56.5 23.5T200-280q0 33 23.5 56.5T280-200Zm-64-400h128l-64-115-64 115Zm304 480v-320h320v320H520Zm80-80h160v-160H600v160Zm80-320q-57-48-95.5-81T523-659q-23-25-33-47t-10-47q0-45 31.5-76t78.5-31q27 0 50.5 12.5T680-813q16-22 39.5-34.5T770-860q47 0 78.5 31t31.5 76q0 25-10 47t-33 47q-23 25-61.5 58T680-520Zm0-105q72-60 96-85t24-41q0-13-7.5-21t-20.5-8q-10 0-19.5 5.5T729-755l-49 47-49-47q-14-14-23.5-19.5T588-780q-13 0-20.5 8t-7.5 21q0 16 24 41t96 85Zm0-78Zm-400 45Zm0 378Zm400 0Z'
  },
  {
    name: 'Officers & Members',
    route: 'members-officers.index',
    icon: 'M440-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm0-80q33 0 56.5-23.5T520-640q0-33-23.5-56.5T440-720q-33 0-56.5 23.5T360-640q0 33 23.5 56.5T440-560ZM884-20 756-148q-21 12-45 20t-51 8q-75 0-127.5-52.5T480-300q0-75 52.5-127.5T660-480q75 0 127.5 52.5T840-300q0 27-8 51t-20 45L940-76l-56 56ZM660-200q42 0 71-29t29-71q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 42 29 71t71 29Zm-540 40v-111q0-34 17-63t47-44q51-26 115-44t142-18q-12 18-20.5 38.5T407-359q-60 5-107 20.5T221-306q-10 5-15.5 14.5T200-271v31h207q5 22 13.5 42t20.5 38H120Zm320-480Zm-33 400Z'
  },
  {
    name: 'Organizations',
    route: 'student-orgs.index',
    exactMatch: false,
    checkStartsWith: true,
    icon: 'm160-419 101-101-101-101L59-520l101 101Zm540-21 100-160 100 160H700Zm-220-40q-50 0-85-35t-35-85q0-51 35-85.5t85-34.5q51 0 85.5 34.5T600-600q0 50-34.5 85T480-480Zm0-160q-17 0-28.5 11.5T440-600q0 17 11.5 28.5T480-560q17 0 28.5-11.5T520-600q0-17-11.5-28.5T480-640Zm0 40ZM0-240v-63q0-44 44.5-70.5T160-400q13 0 25 .5t23 2.5q-14 20-21 43t-7 49v65H0Zm240 0v-65q0-65 66.5-105T480-450q108 0 174 40t66 105v65H240Zm560-160q72 0 116 26.5t44 70.5v63H780v-65q0-26-6.5-49T754-397q11-2 22.5-2.5t23.5-.5Zm-320 30q-57 0-102 15t-53 35h311q-9-20-53.5-35T480-370Zm0 50Z'
  },
  {
    name: 'Colleges',
    route: 'colleges.index',
    exactMatch: false,
    checkStartsWith: true,
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
  },
  {
    name: 'Archive',
    route: 'archive.index',
    icon: 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'
  },
  {
    name: 'Calendar',
    route: 'calendar',
    icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
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
  <div class="flex-1 flex flex-col overflow-hidden min-h-0">
    <!-- Main navigation section -->
    <nav class="flex-1 mt-4 px-2 space-y-1 overflow-y-auto overflow-x-hidden custom-scrollbar" aria-label="Main navigation">
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
              class="w-full flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white text-base font-medium rounded-xl shadow-md hover:shadow-blue-300/70 dark:hover:shadow-blue-500/30 transition-all duration-200 relative overflow-hidden group focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
              :class="{'px-2': !sidebarExpanded && !showingSidebar}"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" :class="{'mr-0': !sidebarExpanded && !showingSidebar, 'mr-2': sidebarExpanded || showingSidebar}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span 
                v-if="sidebarExpanded || showingSidebar"
                class="truncate min-w-0 transition-opacity duration-200"
                :class="sidebarExpanded || showingSidebar ? 'opacity-100' : 'opacity-0'"
              >
                All Applications
              </span>
              <span v-else class="sr-only">Applications</span>
            </Link>
          </SidebarTooltipButton>
          
          <!-- Plan of Activities link after All Applications -->
          <div class="mt-4">
            <NavigationItem 
              :item="{
                name: 'Plan of Activities',
                route: 'admin.plan-of-activities.index',
                icon: 'm80-520 200-360 200 360H80Zm200 400q-66 0-113-47t-47-113q0-67 47-113.5T280-440q66 0 113 47t47 113q0 66-47 113t-113 47Zm0-80q33 0 56.5-23.5T360-280q0-33-23.5-56.5T280-360q-33 0-56.5 23.5T200-280q0 33 23.5 56.5T280-200Zm-64-400h128l-64-115-64 115Zm304 480v-320h320v320H520Zm80-80h160v-160H600v160Zm80-320q-57-48-95.5-81T523-659q-23-25-33-47t-10-47q0-45 31.5-76t78.5-31q27 0 50.5 12.5T680-813q16-22 39.5-34.5T770-860q47 0 78.5 31t31.5 76q0 25-10 47t-33 47q-23 25-61.5 58T680-520Zm0-105q72-60 96-85t24-41q0-13-7.5-21t-20.5-8q-10 0-19.5 5.5T729-755l-49 47-49-47q-14-14-23.5-19.5T588-780q-13 0-20.5 8t-7.5 21q0 16 24 41t96 85Zm0-78Zm-400 45Zm0 378Zm400 0Z'
              }" 
              :sidebar-expanded="sidebarExpanded" 
              :showing-sidebar="showingSidebar" 
            />
          </div>
          
          <!-- Officers & Members link after Plan of Activities -->
          <div class="mt-4">
            <NavigationItem 
              :item="{
                name: 'Officers & Members',
                route: 'admin.members-officers.index',
                icon: 'M440-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm0-80q33 0 56.5-23.5T520-640q0-33-23.5-56.5T440-720q-33 0-56.5 23.5T360-640q0 33 23.5 56.5T440-560ZM884-20 756-148q-21 12-45 20t-51 8q-75 0-127.5-52.5T480-300q0-75 52.5-127.5T660-480q75 0 127.5 52.5T840-300q0 27-8 51t-20 45L940-76l-56 56ZM660-200q42 0 71-29t29-71q0-42-29-71t-71-29q-42 0-71 29t-29 71q0 42 29 71t71 29Zm-540 40v-111q0-34 17-63t47-44q51-26 115-44t142-18q-12 18-20.5 38.5T407-359q-60 5-107 20.5T221-306q-10 5-15.5 14.5T200-271v31h207q5 22 13.5 42t20.5 38H120Zm320-480Zm-33 400Z'
              }" 
              :sidebar-expanded="sidebarExpanded" 
              :showing-sidebar="showingSidebar" 
            />
          </div>
          
          <!-- Archive Management link after Members & Officers -->
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
              class="w-full flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white text-base font-medium rounded-xl shadow-md hover:shadow-blue-300/70 dark:hover:shadow-blue-500/30 transition-all duration-200 relative overflow-hidden group focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
              :class="{'px-2': !sidebarExpanded && !showingSidebar}"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" :class="{'mr-0': !sidebarExpanded && !showingSidebar, 'mr-2': sidebarExpanded || showingSidebar}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span 
                v-if="sidebarExpanded || showingSidebar"
                class="truncate min-w-0 transition-opacity duration-200"
                :class="sidebarExpanded || showingSidebar ? 'opacity-100' : 'opacity-0'"
              >
                New Application
              </span>
              <span v-else class="sr-only">New Application</span>
            </Link>
          </SidebarTooltipButton>
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

/* Prevent horizontal scrollbar and text overflow */
nav {
  min-width: 0;
}

nav > * {
  min-width: 0;
  max-width: 100%;
}
</style>