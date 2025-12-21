<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SidebarLayout from '@/Components/Layout/Sidebar/SidebarLayout.vue';

const user = computed(() => usePage().props.auth.user);
const isAdmin = computed(() => user.value?.role?.slug === 'admin' || user.value?.role?.slug === 'super_admin');
const showMobileMenu = ref(false);
const windowWidth = ref(window.innerWidth);

// Handle mobile menu state changes from the navigation component
const handleCloseMobileMenu = () => {
  showMobileMenu.value = false;
};

// Track window width for responsive adjustments
const updateWindowWidth = () => {
  windowWidth.value = window.innerWidth;
};

// Get content padding class based on screen size
const contentPadding = computed(() => {
  // Less padding on smaller screens, more on larger screens
  if (windowWidth.value < 640) {
    return 'px-2 py-2'; // Small screens
  } else if (windowWidth.value < 1024) {
    return 'px-4 py-3'; // Medium screens
  } else {
    return 'px-6 py-4'; // Large screens
  }
});

// Listen for window resize events
onMounted(() => {
  window.addEventListener('resize', updateWindowWidth);
});

onUnmounted(() => {
  window.removeEventListener('resize', updateWindowWidth);
});
    
    defineProps({
      title: {
        type: String,
        default: ''
      }
    });
</script>

<template>
  <SidebarLayout
    :is-admin="isAdmin" 
    @close-mobile-menu="handleCloseMobileMenu"
  >
    <!-- Page Heading -->
    <header v-if="$slots.header" class="bg-gray-50 dark:bg-gray-900 w-full">
      <div class="w-full px-4 py-4 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Page Content - Adaptive Width -->
    <div class="w-full" :class="contentPadding">
      <slot />
    </div>
  </SidebarLayout>
</template>