<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SidebarLayout from '@/Components/Layout/Sidebar/SidebarLayout.vue';

const user = computed(() => usePage().props.auth.user);
const isAdmin = computed(() => user.value?.role?.slug === 'admin');
const showMobileMenu = ref(false);

// Handle mobile menu state changes from the navigation component
const handleCloseMobileMenu = () => {
  showMobileMenu.value = false;
};
</script>

<template>
  
  <SidebarLayout
    :is-admin="isAdmin" 
    @close-mobile-menu="handleCloseMobileMenu"
  >
    <!-- Page Heading -->
    <header v-if="$slots.header" class="bg-gradient-to-b from-blue-50 to-white">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Page Content -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <slot />
    </div>
  </SidebarLayout>
</template>