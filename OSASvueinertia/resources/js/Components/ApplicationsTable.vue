<script setup>
import { defineProps, defineEmits } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  applications: Array,
  isAdmin: Boolean,
});

const emit = defineEmits(['openStatusModal', 'deleteApplication']);

const getStatusColor = (status) => {
  switch(status.toLowerCase()) {
    case 'approved':
      return 'bg-green-100 text-green-800';
    case 'pending':
      return 'bg-amber-100 text-amber-800';
    case 'disapproved':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const formTypeToName = (formType) => {
  switch(formType) {
    case 'LSPU-OSAS-SF-001':
      return 'Organization Registration';
    case 'LSPU-OSAS-SF-002':
      return 'Renewal Application';
    case 'LSPU-OSAS-SF-003':
      return 'Commitment Form';
    case 'LSPU-OSAS-SF-004':
      return 'Activity Plan';
    case 'LSPU-OSAS-SF-005':
      return 'Members List';
    case 'LSPU-OSAS-SF-006':
      return 'Certification Form';
    case 'LSPU-OSAS-SF-007':
      return 'Officers List';
    case 'LSPU-OSAS-SF-009':
      return 'Student Activity Attendance Sheet';
    default:
      return formType;
  }
};

const getPdfRoute = (app, action = 'download') => {
  const queryParams = action === 'view' ? '?action=view' : '';
  
  // Check the form type directly
  if (app.form_type === 'LSPU-OSAS-SF-002') {
    return `/applications/${app.id}/export-renewal${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-001') {
    return `/applications/${app.id}/pdf${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-003') {
    return `/applications/${app.id}/export-commitment${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-004') {
    return `/applications/${app.id}/export-plan${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-006') {
    return `/applications/${app.id}/export-certification${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-005') {
    return `/applications/${app.id}/export-members${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-007') {
    return `/applications/${app.id}/export-officers${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-009') {
    return `/applications/${app.id}/export-attendance${queryParams}`;
  } else {
    // Default case
    console.warn('Unknown form type:', app.form_type);
    return `/applications/${app.id}/pdf${queryParams}`;
  }
};
</script>

<template>
  <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
    <!-- Colored banner -->
    <div class="flex w-full overflow-hidden shadow-md">
      <div class="w-1/4 h-1.5 bg-blue-600 animate-pulse" style="animation-delay: 0.2s;"></div>
      <div class="w-1/4 h-1.5 bg-green-500 animate-pulse" style="animation-delay: 0.4s;"></div>
      <div class="w-1/4 h-1.5 bg-amber-500 animate-pulse" style="animation-delay: 0.6s;"></div>
      <div class="w-1/4 h-1.5 bg-red-500 animate-pulse" style="animation-delay: 0.8s;"></div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="bg-gray-50 text-left text-gray-600 text-sm">
            <th class="px-6 py-5 font-semibold">Form Type</th>
            <th class="px-6 py-5 font-semibold">Organization</th>
            <th class="px-6 py-5 font-semibold">President</th>
            <th class="px-6 py-5 font-semibold">Date</th>
            <th class="px-6 py-5 font-semibold">Status</th>
            <th class="px-6 py-5 font-semibold text-center">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="app in applications" :key="app.id" class="hover:bg-blue-50 transition-colors duration-200">
            <td class="px-6 py-5">
              <div class="text-sm font-semibold text-gray-800">{{ formTypeToName(app.form_type) }}</div>
              <div class="text-xs text-gray-500 mt-1">{{ app.form_type }}</div>
            </td>
            <td class="px-6 py-5">
              <div class="text-sm font-medium text-gray-800">{{ app.organization_name }}</div>
            </td>
            <td class="px-6 py-5">
              <div class="text-sm text-gray-600">{{ app.president_name }}</div>
            </td>
            <td class="px-6 py-5">
              <div class="text-sm text-gray-600">{{ app.application_date }}</div>
            </td>
            <td class="px-6 py-5">
              <span :class="`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(app.status)}`">
                {{ app.status }}
              </span>
              <!-- Feedback badge if exists -->
              <div v-if="app.feedback" class="mt-1.5">
                <button 
                  @click="emit('openStatusModal', app)"
                  class="text-xs text-gray-500 hover:text-blue-600 flex items-center gap-1 transition-colors duration-200"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                  </svg>
                  View feedback
                </button>
              </div>
            </td>
            <td class="px-6 py-5">
              <div class="flex justify-center space-x-3">
                <!-- Admin-only Status Update Button -->
                <button 
                  v-if="isAdmin"
                  @click="emit('openStatusModal', app)"
                  class="bg-purple-600 hover:bg-purple-500 text-white p-2.5 rounded-lg transition duration-300 relative overflow-hidden group shadow-sm"
                  title="Update Status"
                >
                  <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-16 group-hover:h-16 opacity-10"></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3-9a1 1 0 10-2 0v4a1 1 0 102 0V9z" clip-rule="evenodd" />
                    <path d="M10 6a1 1 0 100 2 1 1 0 000-2z" />
                  </svg>
                </button>
                
                <Link 
                  :href="`/applications/${app.id}/edit`" 
                  class="bg-amber-500 hover:bg-amber-400 text-white p-2.5 rounded-lg transition duration-300 relative overflow-hidden group shadow-sm"
                  title="Edit Application"
                >
                  <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-16 group-hover:h-16 opacity-10"></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                  </svg>
                </Link>
                <a 
                  :href="getPdfRoute(app, 'view')" 
                  target="_blank" 
                  class="bg-green-500 hover:bg-green-400 text-white p-2.5 rounded-lg transition duration-300 relative overflow-hidden group shadow-sm"
                  title="View PDF"
                >
                  <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-16 group-hover:h-16 opacity-10"></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                  </svg>
                </a>
                <a 
                  :href="getPdfRoute(app)" 
                  class="bg-blue-600 hover:bg-blue-500 text-white p-2.5 rounded-lg transition duration-300 relative overflow-hidden group shadow-sm"
                  title="Download PDF"
                >
                  <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-16 group-hover:h-16 opacity-10"></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </a>
                <button 
                  @click="emit('deleteApplication', app.id)" 
                  class="bg-red-500 hover:bg-red-400 text-white p-2.5 rounded-lg transition duration-300 relative overflow-hidden group shadow-sm"
                  title="Delete Application"
                >
                  <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-16 group-hover:h-16 opacity-10"></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

