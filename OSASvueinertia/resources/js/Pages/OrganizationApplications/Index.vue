<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ApplicationsTable from '@/Components/ApplicationsTable.vue';
import StatusModal from '@/Components/StatusModal.vue';
import NoApplicationsMessage from '@/Components/NoApplicationsMessage.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({ 
  applications: Array,
  successMessage: String,
  errorMessage: String,
  userId: Number,
  isAdmin: Boolean,
  users: Array,
  currentUserFilter: String,
});

const selectedUser = ref(props.currentUserFilter || '');
const isFiltering = ref(false);

const message = ref(props.successMessage || props.errorMessage || null);
const showMessage = ref(!!props.successMessage || !!props.errorMessage);

// Combined search and filter states
const searchQuery = ref('');
const searchType = ref('all'); // all, organization, president, form_type, status
const filteredApplications = ref([]);
const formElement = ref(null);
const activeDropdown = ref(null);

// Status update variables
const showStatusModal = ref(false);
const selectedApplication = ref(null);
const isSubmitting = ref(false);

// Add End the Year logic and modal for admins only, matching the dashboard implementation
const showEndYearModal = ref(false);
const endYearForm = ref({
  academic_year: '',
  confirmation: ''
});

// Search type options for admins
const searchTypeOptions = computed(() => {
  const baseOptions = [
    { value: 'all', label: 'All Fields' },
   
    { value: 'president', label: 'President' },
    { value: 'form_type', label: 'Form Type' },
    { value: 'status', label: 'Status' }
  ];
  
  // Regular users only see "All Fields" option
  return props.isAdmin ? baseOptions : [{ value: 'all', label: 'Search Applications' }];
});

// Get placeholder text based on search type
const searchPlaceholder = computed(() => {
  if (!props.isAdmin) {
    return 'Search applications by president, form type, or status...';
  }
  
  switch (searchType.value) {
    
    case 'president':
      return 'Search by president name...';
    case 'form_type':
      return 'Search by form type...';
    case 'status':
      return 'Search by status...';
    default:
      return 'Search applications by any field...';
  }
});

// Combined filter function
const filterApplications = () => {
  if (!searchQuery.value) {
    filteredApplications.value = props.applications;
    return;
  }
  
  const query = searchQuery.value.toLowerCase();
  
  filteredApplications.value = props.applications.filter(app => {
    switch (searchType.value) {
      case 'organization':
        return app.organization_name.toLowerCase().includes(query);
      case 'president':
        return app.president_name.toLowerCase().includes(query);
      case 'form_type':
        return app.form_type.toLowerCase().includes(query);
      case 'status':
        return app.status.toLowerCase().includes(query);
      default: // 'all'
        return app.organization_name.toLowerCase().includes(query) ||
               app.president_name.toLowerCase().includes(query) ||
               app.form_type.toLowerCase().includes(query) ||
               app.status.toLowerCase().includes(query);
    }
  });
};

// Organization filter function (admin only)
const filterByUser = () => {
    isFiltering.value = true;
    
    const url = new URL(window.location);
    
    if (selectedUser.value) {
        url.searchParams.set('user_filter', selectedUser.value);
    } else {
        url.searchParams.delete('user_filter');
    }
    
    router.visit(url.toString(), {
        preserveScroll: true,
        onFinish: () => {
            isFiltering.value = false;
        }
    });
};

const clearUserFilter = () => {
    selectedUser.value = '';
    filterByUser();
};

const clearSearch = () => {
    searchQuery.value = '';
    searchType.value = 'all';
    filterApplications();
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
        filteredApplications.value = filteredApplications.value.filter(app => app.id !== id);
        
        message.value = "Application deleted successfully!";
        showMessage.value = true;
        
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

const openStatusModal = (app) => {
  selectedApplication.value = app;
  showStatusModal.value = true;
};

const closeStatusModal = () => {
  showStatusModal.value = false;
  selectedApplication.value = null;
};

const updateApplicationStatus = (statusData) => {
  if (!selectedApplication.value) return;
  
  isSubmitting.value = true;
  
  if (typeof router !== 'undefined' && router.post) {
    router.post(`/applications/${selectedApplication.value.id}/update-status`, {
      status: statusData.status,
      feedback: statusData.feedback
    }, {
      onSuccess: () => {
        const index = filteredApplications.value.findIndex(app => app.id === selectedApplication.value.id);
        if (index !== -1) {
          filteredApplications.value[index].status = statusData.status;
          filteredApplications.value[index].feedback = statusData.feedback;
        }
        
        closeStatusModal();
        isSubmitting.value = false;
        
        message.value = "Application status updated successfully!";
        showMessage.value = true;
        
        setTimeout(() => {
          showMessage.value = false;
        }, 5000);
      },
      onError: (errors) => {
        isSubmitting.value = false;
        message.value = errors?.message || "Failed to update status.";
        showMessage.value = true;
      }
    });
  } else {
    fetch(`/applications/${selectedApplication.value.id}/update-status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify({
        status: statusData.status,
        feedback: statusData.feedback
      })
    })
    .then(response => {
      if (!response.ok) throw new Error('Failed to update status');
      return response.json();
    })
    .then(data => {
      const index = filteredApplications.value.findIndex(app => app.id === selectedApplication.value.id);
      if (index !== -1) {
        filteredApplications.value[index].status = statusData.status;
        filteredApplications.value[index].feedback = statusData.feedback;
      }
      
      closeStatusModal();
      isSubmitting.value = false;
      
      message.value = "Application status updated successfully!";
      showMessage.value = true;
      
      setTimeout(() => {
        showMessage.value = false;
      }, 5000);
    })
    .catch(error => {
      isSubmitting.value = false;
      message.value = error.message || "Failed to update status.";
      showMessage.value = true;
    });
  }
};

const handleDocumentUpload = (uploadResult) => {
  if (uploadResult.success) {
    message.value = uploadResult.message;
    showMessage.value = true;
    
    setTimeout(() => {
      showMessage.value = false;
    }, 5000);
  } else {
    message.value = uploadResult.message;
    showMessage.value = true;
  }
};

const refreshApplications = () => {
  filteredApplications.value = [...props.applications];
};

const openEndYearModal = () => {
  const currentYear = new Date().getFullYear();
  endYearForm.value.academic_year = `${currentYear}-${currentYear + 1}`;
  endYearForm.value.confirmation = '';
  showEndYearModal.value = true;
};

const endYear = () => {
  if (endYearForm.value.confirmation !== 'END_YEAR') {
    return;
  }
  router.post(route('admin.archive.end-year'), endYearForm.value, {
    onSuccess: () => {
      showEndYearModal.value = false;
      endYearForm.value = { academic_year: '', confirmation: '' };
      // Force refresh to ensure changes are visible
      window.location.reload();
    },
  });
};
</script>

<template>
  <Head title="Applications" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ isAdmin ? 'Manage Applications' : 'Your Applications' }}
        </h2>
        <div class="flex gap-3">
          <Link
            href="/applications/create"
            class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-5 py-2.5 rounded-lg shadow inline-flex items-center transition duration-300 text-sm font-medium"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            New Application
          </Link>
          <button
            v-if="isAdmin"
            @click="openEndYearModal"
            class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-lg shadow inline-flex items-center transition duration-300 text-sm font-medium"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            End the Year
          </button>
        </div>
      </div>
    </template>

    <!-- Success/Error Message -->
    <div v-if="showMessage" class="mb-6 transition-opacity duration-500 ease-in-out">
      <div :class="props.successMessage ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gradient-to-r from-red-500 to-pink-500'" class="text-white py-4 px-6 rounded-lg shadow-md flex items-center justify-between">
        <div class="flex items-center">
          <svg v-if="props.successMessage" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <span>{{ props.successMessage || props.errorMessage || message }}</span>
        </div>
        <button @click="showMessage = false" class="text-white hover:text-gray-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Combined Search and Filter Section -->
    <div class="mb-6 flex flex-col lg:flex-row gap-4">
      <!-- Search Section -->
      <div class="flex-1">
        <div class="flex rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <!-- Search Type Selector (Admin Only) -->
          <div v-if="isAdmin" class="relative">
            <select
              v-model="searchType"
              @change="filterApplications"
              class="h-full py-4 pl-4 pr-8 border-0 border-r border-gray-200 bg-gray-50 text-gray-700 focus:ring-0 focus:border-gray-200 text-sm font-medium min-w-32"
            >
              <option v-for="option in searchTypeOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
          
          <!-- Search Input -->
          <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              type="text"
              v-model="searchQuery"
              @input="filterApplications"
              class="block w-full pl-12 pr-12 py-4 border-0 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150"
              :placeholder="searchPlaceholder"
            />
            <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-4 flex items-center">
              <button @click="clearSearch" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Organization Filter Section (Admin Only) -->
      <div v-if="isAdmin && users.length > 0" class="lg:w-80">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
          <div class="flex items-center gap-3">
            <div class="flex-1">
              <label for="user-filter" class="block text-sm font-medium text-gray-700 mb-2">
                Filter by Organization
              </label>
              <select 
                id="user-filter"
                v-model="selectedUser"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                :disabled="isFiltering"
              >
                <option value="">All Organizations</option>
                <option 
                  v-for="user in users" 
                  :key="user.id" 
                  :value="user.id"
                >
                  {{ user.name }}
                </option>
              </select>
            </div>
            <div class="flex gap-2 mt-6">
              <button
                @click="filterByUser"
                :disabled="isFiltering"
                class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition duration-200 text-sm font-medium"
              >
                <span v-if="isFiltering">...</span>
                <span v-else>Apply</span>
              </button>
              <button
                v-if="selectedUser"
                @click="clearUserFilter"
                :disabled="isFiltering"
                class="px-3 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 transition duration-200 text-sm font-medium"
              >
                Clear
              </button>
            </div>
          </div>
          
          <!-- Current Filter Status -->
          <div v-if="currentUserFilter" class="mt-3 pt-3 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z" />
              </svg>
              <span class="font-medium">Showing:</span>
              <span class="ml-1">{{ users.find(u => u.id == currentUserFilter)?.name }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Applications Table -->
    <div class="relative">
      <ApplicationsTable 
        v-if="filteredApplications.length > 0" 
        :applications="filteredApplications" 
        :isAdmin="isAdmin"
        @openStatusModal="openStatusModal"
        @deleteApplication="deleteApplication"
        @uploadDocument="handleDocumentUpload"
        @refreshData="refreshApplications"
      />
      <NoApplicationsMessage v-else />
    </div>

    <!-- Status Update Modal -->
    <StatusModal
      :showModal="showStatusModal"
      :application="selectedApplication"
      :isAdmin="isAdmin"
      :isSubmitting="isSubmitting"
      @close="closeStatusModal"
      @updateStatus="updateApplicationStatus"
    />

    <!-- End Year Confirmation Modal -->
    <Modal :show="showEndYearModal" @close="showEndYearModal = false">
      <div class="p-6">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900">
              End the Academic Year
            </h3>
          </div>
        </div>
        <div class="mt-2">
          <p class="text-sm text-gray-500 mb-4">
            This action will archive all current applications and end the academic year. This process cannot be undone easily.
          </p>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
              <input
                v-model="endYearForm.academic_year"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                placeholder="e.g., 2024-2025"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Confirmation</label>
              <input
                v-model="endYearForm.confirmation"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                placeholder="Type 'END_YEAR' to confirm"
              />
              <p class="text-xs text-gray-500 mt-1">
                Type <strong>END_YEAR</strong> to confirm this action
              </p>
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="showEndYearModal = false"
            class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 transition"
          >
            Cancel
          </button>
          <button
            @click="endYear"
            :disabled="endYearForm.confirmation !== 'END_YEAR'"
            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            End the Year
          </button>
        </div>
      </div>
    </Modal>

    <!-- Add a subtle, center-aligned archive link at the bottom -->
    <div class="flex justify-center mt-10 mb-6">
      <a
        :href="isAdmin ? route('admin.archive.index') : route('archive.index')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg shadow-sm transition duration-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
        aria-label="View Archive"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <span>{{ isAdmin ? 'Archive Management' : 'View Archive' }}</span>
      </a>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>