<script setup>
import { defineProps, ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({ 
  applications: Array,
  successMessage: String
});

const message = ref(props.successMessage || null);
const showMessage = ref(!!props.successMessage);
const searchQuery = ref('');
const filteredApplications = ref([]);

// Filter applications based on search query
const filterApplications = () => {
  if (!searchQuery.value) {
    filteredApplications.value = props.applications;
    return;
  }
  
  const query = searchQuery.value.toLowerCase();
  filteredApplications.value = props.applications.filter(app => 
    app.organization_name.toLowerCase().includes(query) ||
    app.president_name.toLowerCase().includes(query) ||
    app.form_type.toLowerCase().includes(query) ||
    app.status.toLowerCase().includes(query)
  );
};

// Initialize filtered applications
onMounted(() => {
  filteredApplications.value = props.applications;
  
  // Auto-hide success message after 5 seconds
  if (showMessage.value) {
    setTimeout(() => {
      showMessage.value = false;
    }, 5000);
  }
});

const deleteApplication = (id) => {
    if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
        router.delete(`/applications/${id}`, {
            onSuccess: () => {
                message.value = "Application deleted successfully!";
                showMessage.value = true;
                
                // Auto-hide message after 5 seconds
                setTimeout(() => {
                    showMessage.value = false;
                }, 5000);
            },
            onError: () => {
                message.value = "Failed to delete application.";
                showMessage.value = true;
            }
        });
    }
};

const getStatusColor = (status) => {
    switch(status.toLowerCase()) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
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
    }else {
        // Default case
        console.warn('Unknown form type:', app.form_type);
        return `/applications/${app.id}/pdf${queryParams}`;
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
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white shadow-md">
            <div class="container mx-auto px-6 py-4">
                <h1 class="text-2xl font-bold">Organization Applications</h1>
                <p class="text-sm opacity-80">Manage student organization registrations and renewals</p>
            </div>
        </div>
        
        <div class="container mx-auto px-6 py-6">
            <!-- Success/Failure Message -->
            <div v-if="showMessage" 
                class="mb-6 p-4 rounded-lg shadow-sm bg-green-100 text-green-800 flex justify-between items-center">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ message }}</span>
                </div>
                <button @click="showMessage = false" class="text-green-800 hover:text-green-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Action Bar -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="w-full md:w-1/3">
                    <div class="relative">
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            @input="filterApplications" 
                            placeholder="Search applications..." 
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-2.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <Link href="/applications/create" class="w-full md:w-auto bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center justify-center transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    New Application
                </Link>
            </div>

            <!-- No Applications Message -->
            <div v-if="props.applications.length === 0" class="bg-white p-10 rounded-lg shadow-sm text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-600 mb-4">No applications found.</p>
                <Link href="/applications/create" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg inline-flex items-center transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create New Application
                </Link>
            </div>

            <!-- Applications Table -->
            <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left text-gray-600 text-sm">
                                <th class="px-6 py-3 font-medium">Form Type</th>
                                <th class="px-6 py-3 font-medium">Organization</th>
                                <th class="px-6 py-3 font-medium">President</th>
                                <th class="px-6 py-3 font-medium">Date</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="app in filteredApplications" :key="app.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ formTypeToName(app.form_type) }}</div>
                                    <div class="text-xs text-gray-500">{{ app.form_type }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ app.organization_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-800">{{ app.president_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-800">{{ app.application_date }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(app.status)}`">
                                        {{ app.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center space-x-2">
                                        <Link 
                                            :href="`/applications/${app.id}/edit`" 
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition duration-200"
                                            title="Edit Application"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </Link>
                                        <a 
                                            :href="getPdfRoute(app, 'view')" 
                                            target="_blank" 
                                            class="bg-green-500 hover:bg-green-600 text-white p-2 rounded transition duration-200"
                                            title="View PDF"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <a 
                                            :href="getPdfRoute(app)" 
                                            class="bg-red-500 hover:bg-red-600 text-white p-2 rounded transition duration-200"
                                            title="Download PDF"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <button 
                                            @click="deleteApplication(app.id)" 
                                            class="bg-gray-500 hover:bg-gray-600 text-white p-2 rounded transition duration-200"
                                            title="Delete Application"
                                        >
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
        </div>
    </div>
</template>