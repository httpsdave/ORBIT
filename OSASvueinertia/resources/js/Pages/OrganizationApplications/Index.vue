<script setup>
import { defineProps, ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const props = defineProps({ 
  applications: Array,
  successMessage: String
});

const message = ref(props.successMessage || null);
const showMessage = ref(!!props.successMessage);
const searchQuery = ref('');
const filteredApplications = ref([]);
const formElement = ref(null);

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
  
  // Fade in animation on page load
  if (formElement.value) {
    formElement.value.classList.add('opacity-100');
  }
});

const deleteApplication = (id) => {
    if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
        router.delete(`/applications/${id}`, {
            onSuccess: () => {
                // Update the local applications array by removing the deleted item
                const updatedApplications = props.applications.filter(app => app.id !== id);
                // Update both the original and filtered lists
                filteredApplications.value = filteredApplications.value.filter(app => app.id !== id);
                
                // Show success message
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
            return 'bg-amber-100 text-amber-800';
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
    <AuthenticatedLayout>
    <Head title="Applications | LSPU ORBIT" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 font-sans">
        <div ref="formElement" class="container mx-auto px-4 py-12 opacity-0 transition-opacity duration-700">
            <!-- Header with gradient background -->
            <div class="bg-gradient-to-br from-blue-600 via-indigo-500 to-purple-600 p-8 rounded-3xl shadow-xl text-white mb-8 relative overflow-hidden">
                <!-- Animated background pattern -->
                <div class="absolute inset-0 overflow-hidden opacity-10">
                    <div class="absolute top-1/4 left-1/4 w-16 h-16 rounded-full bg-white transform rotate-45 animate-pulse"></div>
                    <div class="absolute bottom-1/3 right-1/5 w-20 h-20 rounded-full bg-white animate-pulse" style="animation-delay: 1s;"></div>
                    <div class="absolute top-3/4 left-1/2 w-12 h-12 rounded-full bg-white animate-pulse" style="animation-delay: 2s;"></div>
                </div>
                
                <div class="relative z-10">
                    <div class="flex items-center mb-4">
                        <div class="relative mr-5">
                            <div class="absolute inset-0 bg-blue-500 opacity-20 blur-md rounded-lg transform rotate-45 animate-pulse"></div>
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="relative">
                                <path d="M24 12L36 24L24 36L12 24L24 12Z" fill="#ffffff"/>
                                <path d="M24 6L42 24L24 42L6 24L24 6Z" stroke="#ffffff" stroke-width="2.5"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">Organization Applications</h1>
                            <div class="h-1 w-40 bg-white opacity-70 my-3 rounded-full"></div>
                            <p class="text-white text-opacity-90 font-medium">Manage student organization registrations and renewals</p>
                        </div>
                    </div>
                </div>
                
                <!-- Decorative elements -->
                <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
            </div>
            
            <!-- Success/Failure Message -->
            <div v-if="showMessage" 
                class="mb-6 p-4 rounded-xl shadow-md bg-green-50 border border-green-200 text-green-700 flex justify-between items-center animate-fadeIn">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-medium">{{ message }}</span>
                </div>
                <button @click="showMessage = false" class="text-green-600 hover:text-green-800 transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Action Bar -->
            <div class="bg-white p-6 rounded-2xl shadow-lg mb-8 border border-gray-100">
                <div class="flex flex-col md:flex-row justify-between items-center gap-5">
                    <div class="w-full md:w-1/3 relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-focus-within:text-blue-600 transition-colors duration-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            @input="filterApplications" 
                            placeholder="Search applications..." 
                            class="pl-12 pr-4 py-4 bg-gray-50 border border-gray-200 text-gray-700 rounded-xl w-full focus:border-blue-600 focus:ring focus:ring-blue-500 focus:ring-opacity-40 transition duration-300 font-medium"
                        >
                    </div>
                    <Link href="/applications/create" class="w-full md:w-auto bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium px-8 py-4 rounded-xl transition duration-300 flex items-center justify-center shadow-md relative overflow-hidden group">
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New Application
                    </Link>
                </div>
            </div>

            <!-- No Applications Message -->
            <div v-if="props.applications.length === 0" class="bg-white p-12 rounded-2xl shadow-lg text-center border border-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-300 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 mb-6 text-lg">No applications found.</p>
                <Link href="/applications/create" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium px-8 py-4 rounded-xl transition duration-300 inline-flex items-center shadow-md relative overflow-hidden group">
                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create New Application
                </Link>
            </div>

            <!-- Applications Table -->
            <div v-else class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
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
                            <tr v-for="app in filteredApplications" :key="app.id" class="hover:bg-blue-50 transition-colors duration-200">
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
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-center space-x-3">
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
                                            @click="deleteApplication(app.id)" 
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
            
            <!-- Footer -->
            <div class="mt-10 text-center">
                <p class="text-sm text-gray-500">
                    © 2025 Laguna State Polytechnic University
                </p>
                <div class="mt-2 flex justify-center space-x-4">
                    <a href="#" class="text-sm text-gray-500 hover:text-blue-600 transition-colors duration-300">Privacy Policy</a>
                    <span class="text-gray-400">•</span>
                    <a href="#" class="text-sm text-gray-500 hover:text-blue-600 transition-colors duration-300">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</AuthenticatedLayout>
</template>

<style scoped>
/* Import Inter font */  
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* Apply Inter font to the entire component */
.font-sans {
  font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
  animation: fadeIn 0.6s ease-out forwards;
}

/* Improve focus visibility for accessibility */
a:focus, button:focus, input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}
</style>