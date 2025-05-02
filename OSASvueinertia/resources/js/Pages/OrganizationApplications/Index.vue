<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ApplicationsTable from '@/Components/ApplicationsTable.vue';
import StatusModal from '@/Components/StatusModal.vue';
import NoApplicationsMessage from '@/Components/NoApplicationsMessage.vue';

const props = defineProps({ 
  applications: Array,
  successMessage: String,
  userId: Number,
  isAdmin: Boolean
});

const message = ref(props.successMessage || null);
const showMessage = ref(!!props.successMessage);
const searchQuery = ref('');
const filteredApplications = ref([]);
const formElement = ref(null);

// Status update variables
const showStatusModal = ref(false);
const selectedApplication = ref(null);
const isSubmitting = ref(false);

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

// Open status update modal
const openStatusModal = (app) => {
  selectedApplication.value = app;
  showStatusModal.value = true;
};

// Close status update modal
const closeStatusModal = () => {
  showStatusModal.value = false;
  selectedApplication.value = null;
};

// Update application status
const updateApplicationStatus = (statusData) => {
  if (!selectedApplication.value) return;
  
  isSubmitting.value = true;
  
  // Check if we're using Inertia - if so, use Inertia methods
  if (typeof router !== 'undefined' && router.post) {
    router.post(`/applications/${selectedApplication.value.id}/update-status`, {
      status: statusData.status,
      feedback: statusData.feedback
    }, {
      onSuccess: () => {
        // Update the application in the local array
        const index = filteredApplications.value.findIndex(app => app.id === selectedApplication.value.id);
        if (index !== -1) {
          filteredApplications.value[index].status = statusData.status;
          filteredApplications.value[index].feedback = statusData.feedback;
        }
        
        // Close modal and show success message
        closeStatusModal();
        isSubmitting.value = false;
        
        message.value = "Application status updated successfully!";
        showMessage.value = true;
        
        // Auto-hide message after 5 seconds
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
    // Fallback to standard fetch API if Inertia isn't available
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
      // Update the application in the local array
      const index = filteredApplications.value.findIndex(app => app.id === selectedApplication.value.id);
      if (index !== -1) {
        filteredApplications.value[index].status = statusData.status;
        filteredApplications.value[index].feedback = statusData.feedback;
      }
      
      // Close modal and show success message
      closeStatusModal();
      isSubmitting.value = false;
      
      message.value = "Application status updated successfully!";
      showMessage.value = true;
      
      // Auto-hide message after 5 seconds
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
</script>

<template>
  <Head title="Applications" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ isAdmin ? 'Manage Applications' : 'Your Applications' }}
        </h2>
        <Link
          href="/applications/create"
          class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-5 py-2.5 rounded-lg shadow inline-flex items-center transition duration-300 text-sm font-medium"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          New Application
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Success Message -->
        <div v-if="showMessage" class="mb-6 transition-opacity duration-500 ease-in-out">
          <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white py-4 px-6 rounded-lg shadow-md flex items-center justify-between">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>{{ message }}</span>
            </div>
            <button @click="showMessage = false" class="text-white hover:text-gray-100">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Search Box -->
        <div class="mb-6">
          <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              type="text"
              v-model="searchQuery"
              @input="filterApplications"
              class="form-input block w-full pl-12 pr-12 py-4 rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition duration-150"
              placeholder="Search applications by organization, president, form type, or status..."
            />
            <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-4 flex items-center">
              <button @click="searchQuery = ''; filterApplications()" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Applications Table or No Applications Message -->
        <div 
          ref="formElement"
          class="opacity-0 transition-opacity duration-500 ease-in-out"
        >
          <ApplicationsTable 
            v-if="filteredApplications.length > 0" 
            :applications="filteredApplications" 
            :isAdmin="isAdmin"
            @openStatusModal="openStatusModal"
            @deleteApplication="deleteApplication"
          />
          <NoApplicationsMessage v-else />
        </div>
      </div>
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
  </AuthenticatedLayout>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>