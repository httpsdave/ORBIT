<script setup>
import { router } from '@inertiajs/vue3';
import FormSelector from '@/Components/FormSelector.vue';
import { useTheme } from '@/Composables/useTheme';

// Initialize theme
const { isDark } = useTheme();

const formOptions = [
    { value: 'LSPU-OSAS-SF-001', label: 'Recognition Form' },
    { value: 'LSPU-OSAS-SF-002', label: 'Renewal Form' },
    { value: 'LSPU-OSAS-SF-003', label: 'Commitment Form' },
    { value: 'LSPU-OSAS-SF-004', label: 'Plan of Activities' },
    { value: 'LSPU-OSAS-SF-005', label: 'List of Members' },
    { value: 'LSPU-OSAS-SF-006', label: 'Student Certification' },
    { value: 'LSPU-OSAS-SF-007', label: 'List of Officers' }, 
    { value: 'LSPU-OSAS-SF-009', label: 'Student Activity Attendance Sheet' },
    { value: 'LSPU-OSAS-SF-EVAL', label: 'Evaluation Form' },
    { value: 'LSPU-OSAS-SF-ACCOMPLISHMENT', label: 'Accomplishment Report' },
    { value: 'LSPU-OSAS-SF-NARRATIVE', label: 'Narrative Report' },
    { value: 'LSPU-OSAS-SF-BYLAWS', label: 'Constitution & By-laws' },
    { value: 'LSPU-OSAS-SF-FINANCIAL', label: 'Financial Report' },
    { value: 'LSPU-ACAD-RL', label: 'Event Letter' },
];

const handleFormSelection = (formId) => {
    // Navigate to the create page with the selected form type
    router.visit(`/applications/create?form_type=${formId}`);
};
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Create New Application</h1>
        <p class="text-gray-600 dark:text-gray-400">Select the type of form you want to fill out</p>
      </div>

      <!-- Form Selector -->
      <FormSelector 
        :formOptions="formOptions" 
        title="Select a Form to Fill"
        @form-selected="handleFormSelection" 
      />

      <!-- Back Button -->
      <div class="mt-6 text-center">
        <button
          @click="$inertia.visit('/applications')"
          class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-150"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Back to Applications
        </button>
      </div>
    </div>
  </div>
</template>
