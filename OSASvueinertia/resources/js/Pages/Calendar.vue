<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Calendar from '@/Components/Calendar.vue';
import { ref, onMounted } from 'vue';

// Props definition
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
      <Calendar :initial-events="initialEvents" :is-admin="isAdmin" />
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