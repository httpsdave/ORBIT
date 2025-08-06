<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';

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

// Tooltip logic
const isHovered = ref(false);
const tooltipPos = ref({ top: 0, left: 0 });
const navItemRef = ref(null);

const showTooltip = async () => {
  isHovered.value = true;
  await nextTick();
  if (navItemRef.value) {
    const rect = navItemRef.value.getBoundingClientRect();
    tooltipPos.value = {
      top: rect.top + rect.height / 2,
      left: rect.right + 8 // 8px gap
    };
  }
};

const hideTooltip = () => {
  isHovered.value = false;
};

// Clean up on unmount
onBeforeUnmount(() => {
  isHovered.value = false;
});
</script>

<template>
  <div
    ref="navItemRef"
    @mouseenter="!sidebarExpanded && !showingSidebar ? showTooltip() : null"
    @mouseleave="hideTooltip"
    @focus="!sidebarExpanded && !showingSidebar ? showTooltip() : null"
    @blur="hideTooltip"
    class="relative"
  >
    <Link 
      v-if="item && item.route"
      :href="route(item.route)" 
      @click="handleNavigationClick"
      class="flex items-center px-4 py-2 text-gray-600 dark:text-gray-300 font-medium hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300 rounded-lg hover:bg-blue-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400 group"
      :class="[
        ((item.exactMatch === false && item.checkStartsWith && isRouteActive(item.route, false, true)) ||
        (!item.checkStartsWith && route().current(item.route))) ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/50 shadow-sm' : '',
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
    
    <!-- No inline tooltip here, handled by teleport below -->
    <teleport to="body">
      <span
        v-if="isHovered && !sidebarExpanded && !showingSidebar"
        :style="{
          position: 'fixed',
          top: tooltipPos.top + 'px',
          left: tooltipPos.left + 'px',
          transform: 'translateY(-50%)',
          zIndex: 9999
        }"
        class="bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 shadow-lg opacity-100 transition-opacity duration-300 whitespace-nowrap pointer-events-none"
      >
        {{ item.name }}
      </span>
    </teleport>
  </div>
</template>