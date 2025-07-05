<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  item: {
    type: Object,
    required: true
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

// Helper function to check if route is active
const isRouteActive = (routeName, exactMatch = true, checkStartsWith = false) => {
  if (!routeName) return false;
  
  const currentRouteName = route().current();
  
  if (checkStartsWith && currentRouteName) {
    return currentRouteName.startsWith(routeName);
  }
  
  return route().current(routeName);
};

// Prevent event bubbling when clicking on navigation items
const handleNavigationClick = (event) => {
  event.stopPropagation();
};
</script>

<template>
  <Link 
    v-if="item && item.route"
    :href="route(item.route)" 
    @click="handleNavigationClick"
    class="flex items-center px-4 py-2 text-gray-600 font-medium hover:text-blue-600 transition-all duration-300 rounded-lg hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-400"
    :class="[
      ((item.exactMatch === false && item.checkStartsWith && isRouteActive(item.route, false, true)) ||
      (!item.checkStartsWith && route().current(item.route))) ? 'text-blue-600 bg-blue-50 shadow-sm' : '',
      {'justify-center': !sidebarExpanded && !showingSidebar}
    ]"
    :aria-current="(item.exactMatch === false && item.checkStartsWith && isRouteActive(item.route, false, true)) ||
                  (!item.checkStartsWith && route().current(item.route)) ? 'page' : undefined"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
    </svg>
    <span v-if="sidebarExpanded || showingSidebar" class="ml-3">{{ item.name }}</span>
    <span v-else class="sr-only">{{ item.name }}</span>
  </Link>
</template>