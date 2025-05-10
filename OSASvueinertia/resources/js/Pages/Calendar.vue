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

    
      
        <!-- Pass the isAdmin prop to the Calendar component -->
        <Calendar :initial-events="initialEvents" :is-admin="isAdmin" />
      
    
  </AuthenticatedLayout>
</template>