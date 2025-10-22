<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

// Disable automatic attribute inheritance since we manually apply them
defineOptions({
  inheritAttrs: false
});

defineProps({
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
</script>

<template>
  <!-- Colored banner only -->
  <div v-bind="$attrs" class="flex items-center justify-start px-4 mt-4">
    <!-- Colored banner -->
    <div 
      class="flex overflow-hidden rounded-lg h-2"
      aria-hidden="true"
    >
      <div class="w-6 h-1 bg-blue-500 animate-pulse rounded-full mr-1" style="animation-delay: 0.2s;"></div>
      <div class="w-4 h-1 bg-green-500 animate-pulse rounded-full mr-1" style="animation-delay: 0.4s;"></div>
      <div class="w-2 h-1 bg-yellow-500 animate-pulse rounded-full" style="animation-delay: 0.6s;"></div>
    </div>
  </div>
</template>