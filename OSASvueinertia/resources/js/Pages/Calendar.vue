<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineAsyncComponent } from 'vue';

// Lazy-load the heavy calendar widget so this page's initial bundle stays slim.
const Calendar = defineAsyncComponent(() => import('@/Components/Calendar.vue'));

const props = defineProps({
  initialEvents: Array,
  isAdmin: {
    type: Boolean,
    default: false
  }
});

</script>

<template>
  <AuthenticatedLayout>
    <Head title="Calendar" />

    <div class="w-full select-none">
      <!-- Pass the isAdmin prop to the Calendar component -->
      <Calendar :initial-events="props.initialEvents" :is-admin="props.isAdmin" />
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.select-none {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}
</style>