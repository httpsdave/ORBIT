<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AuthenticatedLayout.vue';
import Calendar from '@/Components/Calendar.vue';

defineProps({
  initialEvents: Array
});
</script>

<template>
  <AppLayout>
    <Head title="Calendar" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <Calendar :initial-events="initialEvents" />
      </div>
    </div>
  </AppLayout>
</template>
