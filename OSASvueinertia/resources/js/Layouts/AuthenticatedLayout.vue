<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppNavigation from '@/Components/AppNavigation.vue';

const user = computed(() => usePage().props.auth.user);
const isAdmin = computed(() => user.value?.role?.slug === 'admin');
const showMobileMenu = ref(false);

// Handle mobile menu state changes from the navigation component
const handleCloseMobileMenu = () => {
  showMobileMenu.value = false;
};
</script>

<template>
  <div>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
      <!-- Import the Navigation Component -->
      <AppNavigation 
        :is-admin="isAdmin" 
        @close-mobile-menu="handleCloseMobileMenu"
      />

      <!-- Page Heading -->
      <header class="pt-16 bg-gradient-to-b from-blue-50 to-white" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="pt-16">
        <slot />
      </main>
    </div>
  </div>
</template>