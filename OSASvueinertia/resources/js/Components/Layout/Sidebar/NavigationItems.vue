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
    icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
  },
  {
    name: 'Organizations',
    route: 'admin.student-orgs.index',
    exactMatch: false,
    checkStartsWith: true,
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
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
    name: 'Applications',
    route: 'applications.index',
    icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
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
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
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
              :href="route('applications.create')"
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