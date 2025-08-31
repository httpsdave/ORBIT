<script setup>
import { router } from '@inertiajs/vue3';
import FormSelector from '@/Components/FormSelector.vue';

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
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Create New Application</h1>
        <p class="text-gray-600">Select the type of form you want to fill out</p>
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
          class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
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
