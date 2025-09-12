<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ApplicationsTable from '@/Components/ApplicationsTable.vue';
import StatusModal from '@/Components/StatusModal.vue';
import NoApplicationsMessage from '@/Components/NoApplicationsMessage.vue';
import Modal from '@/Components/Modal.vue';
import StatusBanner from '@/Components/StatusBanner.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import ClearDataModal from '@/Components/ClearDataModal.vue';

// --- Add form preview dropdown state and data ---
const showPreviewDropdown = ref(false);
const showPreviewModal = ref(false); // NEW: Modal state
const previewFormType = ref(null); // NEW: Which form to preview
const formTemplates = [
  { type: 'LSPU-OSAS-SF-001', label: 'Application for Recognition' },
  { type: 'LSPU-OSAS-SF-002', label: 'Renewal Form' },
  { type: 'LSPU-OSAS-SF-003', label: 'Commitment Form' },
  { type: 'LSPU-OSAS-SF-004', label: 'Plan of Activities' },
  { type: 'LSPU-OSAS-SF-005', label: 'List of Members' },
  { type: 'LSPU-OSAS-SF-006', label: 'Student Certification' },
  { type: 'LSPU-OSAS-SF-007', label: 'List of Officers' },
  { type: 'LSPU-OSAS-SF-009', label: 'Activity Attendance Sheet' },
  { type: 'LSPU-OSAS-SF-EVAL', label: 'Evaluation Form' },
  { type: 'LSPU-OSAS-SF-EVALSHEET', label: 'Evaluation Sheet' },
  // { type: 'LSPU-ACAD-RL', label: 'Academic Recognition List' }, // Removed
];
const openPreview = (formType) => {
  // Device detection for mobile
  const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  previewFormType.value = formType;
  showPreviewDropdown.value = false;
  if (isMobile) {
    openPreviewInNewWindow();
  } else {
    showPreviewModal.value = true;
  }
};
const closePreviewModal = () => {
  showPreviewModal.value = false;
  previewFormType.value = null;
};
const openPreviewInNewWindow = () => {
  if (previewFormType.value) {
    const url = `/applications/preview/${previewFormType.value}?action=view`;
    window.open(url, '_blank');
  }
};

const props = defineProps({ 
  applications: Array,
  successMessage: String,
  errorMessage: String,
  userId: Number,
  isAdmin: Boolean,
  users: Array,
  currentUserFilter: String,
  updateMessage: String,
});



// Only set showMessage to true on first mount if a prop is present
const showMessage = ref(!!(props.successMessage || props.updateMessage || props.errorMessage));

const localMessage = ref('');
const message = computed(() =>
  localMessage.value ||
  props.successMessage ||
  props.updateMessage ||
  props.errorMessage ||
  ''
);
const statusType = ref('success');

if (props.updateMessage) {
  statusType.value = 'update';
} else if (props.successMessage) {
  statusType.value = 'success';
} else if (props.errorMessage) {
  statusType.value = 'error';
}

// Update all places where message and showMessage are set to also set statusType
// For success (create/submit)
// Example: message.value = 'Application submitted successfully!'; statusType.value = 'success';
// For update
// Example: message.value = 'Application updated successfully!'; statusType.value = 'update';
// For delete
// Example: message.value = 'Application deleted successfully!'; statusType.value = 'delete';
// For error
// Example: message.value = 'Failed to delete application.'; statusType.value = 'error';

// Unified filter states
const searchQuery = ref('');
const filteredApplications = ref([]);
const formElement = ref(null);
const activeDropdown = ref(null);

// Filter states
const statusFilter = ref('');
const formTypeFilter = ref('');
const organizationFilter = ref('');

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

// Delete confirmation modal state
const showDeleteConfirmation = ref(false);
const applicationToDelete = ref(null);

// Clear saved data modal state
const showClearDataModal = ref(false);
const isClearingData = ref(false);

// Get unique values for filter options
const statusOptions = computed(() => {
  const statuses = [...new Set(props.applications.map(app => app.status))];
  return statuses.map(status => ({ value: status, label: status }));
});

const formTypeOptions = computed(() => {
  const types = [...new Set(props.applications.map(app => app.form_type))];
  return types.map(type => ({ value: type, label: type }));
});

const organizationOptions = computed(() => {
  return props.users.map(user => ({ value: user.id.toString(), label: user.name }));
});

// Combined filter function
const filterApplications = () => {
  let filtered = [...props.applications];
  
  // Search filter (by form type, form name, and status)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(app => {
      const formTypeMatch = app.form_type?.toLowerCase().includes(query);
      const formNameMatch = (formTemplates.find(f => f.type === app.form_type)?.label.toLowerCase() || '').includes(query);
      const statusMatch = app.status?.toLowerCase().includes(query);
      return formTypeMatch || formNameMatch || statusMatch;
    });
  }
  
  // Status filter
  if (statusFilter.value) {
    filtered = filtered.filter(app => app.status === statusFilter.value);
  }
  
  // Form type filter
  if (formTypeFilter.value) {
    filtered = filtered.filter(app => app.form_type === formTypeFilter.value);
  }
  
  // Organization filter
  if (organizationFilter.value) {
    filtered = filtered.filter(app => app.user_id?.toString() === organizationFilter.value);
  }
  
  filteredApplications.value = filtered;
};

// Clear all filters
const clearAllFilters = () => {
  searchQuery.value = '';
  statusFilter.value = '';
  formTypeFilter.value = '';
  organizationFilter.value = '';
  filterApplications();
};

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return searchQuery.value || statusFilter.value || formTypeFilter.value || organizationFilter.value;
});

const clearSearch = () => {
    searchQuery.value = '';
    filterApplications();
};

let bannerTimeout = null;

function startBannerTimeout() {
  if (bannerTimeout) clearTimeout(bannerTimeout);
  bannerTimeout = setTimeout(() => {
    showMessage.value = false;
    bannerTimeout = null;
  }, 2500);
}

watch(showMessage, (val) => {
  if (val) {
    startBannerTimeout();
  } else if (bannerTimeout) {
    clearTimeout(bannerTimeout);
    bannerTimeout = null;
  }
});

onMounted(() => {
  filteredApplications.value = props.applications;
  if (showMessage.value) {
    startBannerTimeout();
  }
  if (formElement.value) {
    formElement.value.classList.add('opacity-100');
  }
  const handler = (event) => {
    // If coming from DocumentView, force reload applications
    if (event.detail && event.detail.page && event.detail.page.component === 'DocumentView') {
      router.reload({ only: ['applications'], preserveScroll: true });
    }
  };
  window.addEventListener('inertia:navigate', handler);
  onUnmounted(() => {
    window.removeEventListener('inertia:navigate', handler);
  });
});

// Watch for filter changes
watch([searchQuery, statusFilter, formTypeFilter, organizationFilter], () => {
  filterApplications();
}, { immediate: true });

// Watch for modal open/close to lock body scroll
watch(showPreviewModal, (val) => {
  if (val) {
    document.body.classList.add('overflow-hidden');
  } else {
    document.body.classList.remove('overflow-hidden');
  }
});

const deleteApplication = (id) => {
  applicationToDelete.value = id;
  showDeleteConfirmation.value = true;
};

const closeDeleteConfirmation = () => {
  showDeleteConfirmation.value = false;
  applicationToDelete.value = null;
};

const confirmDeleteApplication = () => {
  if (!applicationToDelete.value) return;
  router.delete(`/applications/${applicationToDelete.value}`, {
    onSuccess: () => {
      filteredApplications.value = filteredApplications.value.filter(app => app.id !== applicationToDelete.value);
      localMessage.value = "Application deleted successfully!";
      statusType.value = 'delete';
      showMessage.value = true;
      
      setTimeout(() => {
        showMessage.value = false;
      }, 5000);
      closeDeleteConfirmation();
    },
    onError: () => {
      localMessage.value = "Failed to delete application.";
      statusType.value = 'error';
      showMessage.value = true;
      closeDeleteConfirmation();
    }
  });
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
        
        localMessage.value = "Application status updated successfully!";
        statusType.value = 'update';
        showMessage.value = true;
        
        setTimeout(() => {
          showMessage.value = false;
        }, 5000);
      },
      onError: (errors) => {
        isSubmitting.value = false;
        localMessage.value = errors?.message || "Failed to update status.";
        statusType.value = 'error';
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
      
      localMessage.value = "Application status updated successfully!";
      statusType.value = 'update';
      showMessage.value = true;
      
      setTimeout(() => {
        showMessage.value = false;
      }, 5000);
    })
    .catch(error => {
      isSubmitting.value = false;
      localMessage.value = error.message || "Failed to update status.";
      statusType.value = 'error';
      showMessage.value = true;
    });
  }
};

const handleDocumentUpload = (uploadResult) => {
  if (uploadResult.success) {
    localMessage.value = uploadResult.message;
    statusType.value = 'success';
    showMessage.value = true;
    
    setTimeout(() => {
      showMessage.value = false;
    }, 5000);
  } else {
    localMessage.value = uploadResult.message;
    statusType.value = 'error';
    showMessage.value = true;
  }
};

const handleSubmitLink = (submitResult) => {
  if (submitResult.success) {
    localMessage.value = submitResult.message;
    statusType.value = 'success';
    showMessage.value = true;
    
    setTimeout(() => {
      showMessage.value = false;
    }, 5000);
  } else {
    localMessage.value = submitResult.message;
    statusType.value = 'error';
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

const showDeleteDocumentModal = ref(false);
const documentToDeleteId = ref(null);
const isDeletingDocument = ref(false);

// Handle document delete confirmation from ApplicationsTable
const handleConfirmDeleteDocument = (appId) => {
  documentToDeleteId.value = appId;
  showDeleteDocumentModal.value = true;
};

const actuallyDeleteDocument = () => {
  if (!documentToDeleteId.value) return;
  showDeleteDocumentModal.value = false;
  isDeletingDocument.value = true; // Show the loading modal
  router.delete(`/applications/${documentToDeleteId.value}/delete-document`, {
    onSuccess: () => {
      isDeletingDocument.value = false; // Hide the loading modal
      localMessage.value = "Document deleted successfully!";
      statusType.value = 'delete';
      showMessage.value = true;
      setTimeout(() => { showMessage.value = false; }, 5000);
      refreshApplications();
    },
    onError: () => {
      isDeletingDocument.value = false; // Hide the loading modal
      localMessage.value = "Failed to delete document.";
      statusType.value = 'error';
      showMessage.value = true;
    }
  });
  documentToDeleteId.value = null;
};

const cancelDeleteDocument = () => {
  showDeleteDocumentModal.value = false;
  documentToDeleteId.value = null;
};

// Clear saved data functions
const openClearDataModal = () => {
  showClearDataModal.value = true;
};

const closeClearDataModal = () => {
  showClearDataModal.value = false;
  isClearingData.value = false;
};

const confirmClearData = () => {
  isClearingData.value = true;
  
  router.delete('/clear-saved-form-data', {
    onSuccess: () => {
      isClearingData.value = false;
      showClearDataModal.value = false;
      localMessage.value = 'Saved form data cleared successfully!';
      statusType.value = 'success';
      showMessage.value = true;
      setTimeout(() => {
        showMessage.value = false;
      }, 5000);
    },
    onError: () => {
      isClearingData.value = false;
      localMessage.value = 'Failed to clear saved data.';
      statusType.value = 'error';
      showMessage.value = true;
    }
  });
};
</script>

<template>
  <Head title="Applications" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 w-full">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
          {{ isAdmin ? 'Manage Submissions' : 'Your Submissions' }}
        </h2>
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto items-stretch sm:items-center relative">
          <Link
            href="/applications/select-form"
            class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group w-full sm:w-auto"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Create New
          </Link>
          <!-- Preview Forms Dropdown (Users and Admins) -->
          <div class="relative w-full sm:w-auto">
            <button
              @click="showPreviewDropdown = !showPreviewDropdown"
              class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-blue-500 dark:border-blue-400 text-blue-700 dark:text-blue-300 text-sm font-medium rounded-xl shadow-md hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-800 dark:hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group w-full sm:w-auto"
              type="button"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-blue-500 rounded-full group-hover:w-96 group-hover:h-96 opacity-5"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M15 10a1 1 0 01-1 1H6a1 1 0 110-2h8a1 1 0 011 1z" clip-rule="evenodd" />
              </svg>
              Preview Forms
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div
              v-if="showPreviewDropdown"
              class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50"
            >
              <ul class="py-1">
                <li v-for="form in formTemplates" :key="form.type">
                  <button
                    @click="openPreview(form.type)"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-700 dark:hover:text-blue-300 transition"
                  >
                    <span class="font-medium">{{ form.label }}</span>
                  </button>
                </li>
              </ul>
            </div>
            <!-- Click outside to close -->
            <div v-if="showPreviewDropdown" class="fixed inset-0 z-40" @click="showPreviewDropdown = false"></div>
          </div>
          <div class="flex items-center gap-2">
            <button
              v-if="isAdmin"
              @click="openEndYearModal"
              class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-sm font-medium text-white rounded-xl shadow-md hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group w-full sm:w-auto"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              End the Year
            </button>
            <button
              @click="openClearDataModal"
              class="inline-flex items-center justify-center p-2 bg-transparent hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 rounded-lg shadow-sm transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 group relative"
              aria-label="Clear Saved Form Data"
            >
              <svg xmlns="http://www.w3.org/2000/svg" height="26px" viewBox="0 -960 960 960" width="26px" fill="currentColor">
                <path d="M280-720v520-520Zm170 600H280q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v172q-17-5-39.5-8.5T680-560v-160H280v520h132q6 21 16 41.5t22 38.5Zm-90-160h40q0-63 20-103.5l20-40.5v-216h-80v360Zm160-230q17-11 38.5-22t41.5-16v-92h-80v130ZM680-80q-83 0-141.5-58.5T480-280q0-83 58.5-141.5T680-480q83 0 141.5 58.5T880-280q0 83-58.5 141.5T680-80Zm66-106 28-28-74-74v-112h-40v128l86 86Z"/>
              </svg>
              <!-- Tooltip: move above button, increase z-index for responsiveness -->
              <span class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50 shadow-lg">
                Clear saved form data
              </span>
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- Status Banner -->
    <StatusBanner
      :show="showMessage"
      :type="statusType"
      :message="message"
      @close="showMessage = false"
    />

    <!-- PDF Preview Modal -->
    <transition name="fade">
      <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60" @click="closePreviewModal">
        <div
          class="relative bg-transparent shadow-2xl flex flex-col w-[95vw] max-w-4xl md:w-[70vw] md:max-w-3xl lg:w-[60vw] lg:max-w-4xl xl:w-[50vw] xl:max-w-5xl h-[75vh] md:h-[85vh] lg:h-[90vh] xl:h-[95vh] max-h-[95vh] overflow-hidden border border-transparent"
          @click.stop
        >
          <!-- Close Button: floating at top-right, outside header -->
          <button
            @click="closePreviewModal"
            class="absolute top-4 right-4 flex items-center justify-center text-white hover:text-gray-200 focus:outline-none transition z-20 opacity-90"
            title="Close Preview"
            aria-label="Close Preview"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <!-- Header -->
          <div class="flex items-center justify-between px-4 py-3 pr-16 bg-transparent relative">
            <div class="font-semibold text-gray-200 text-base truncate opacity-90">
              {{ formTemplates.find(f => f.type === previewFormType)?.label || 'Form Preview' }}
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="openPreviewInNewWindow"
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 text-sm font-medium text-white rounded-xl shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group"
                title="Open in New Window"
                aria-label="Open in New Window"
              >
                <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7v7m0 0L10 21l-7-7 11-11z" />
                </svg>
                New Window
              </button>
            </div>
          </div>
          <!-- PDF Iframe -->
          <div class="flex-1 w-full h-full flex items-center justify-center bg-gray-100">
            <iframe
              v-if="previewFormType"
              :src="`/applications/preview/${previewFormType}?action=view`"
              class="w-full h-full border-0 bg-white"
              style="min-height: 300px;"
              allowfullscreen
            ></iframe>
          </div>
        </div>
      </div>
    </transition>

    <!-- Unified Search and Filter Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 mb-6 space-y-4">
      <!-- Search Bar -->
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input
          type="text"
          v-model="searchQuery"
          class="block w-full pl-12 pr-12 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 transition duration-150 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
          :placeholder="isAdmin ? 'Search submissions by organization, president, form type, or status...' : 'Search submissions by form type or status...'"
        />
        <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-4 flex items-center">
          <button @click="clearSearch" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Filter Bar (Admin Only) -->
      <div v-if="isAdmin" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Status Filter -->
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
          <select 
            v-model="statusFilter"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          >
            <option value="">All Statuses</option>
            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Form Type Filter -->
        <div class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Form Type</label>
          <select 
            v-model="formTypeFilter"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          >
            <option value="">All Types</option>
            <option v-for="option in formTypeOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Organization Filter -->
        <div v-if="users.length > 0" class="flex flex-col gap-1">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Organization</label>
          <select 
            v-model="organizationFilter"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
          >
            <option value="">All Organizations</option>
            <option 
              v-for="option in organizationOptions" 
              :key="option.value" 
              :value="option.value"
              :title="option.label.length > 25 ? option.label : undefined"
            >
              {{ option.label.length > 25 ? option.label.substring(0, 25) + '...' : option.label }}
            </option>
          </select>
        </div>

        <!-- Clear All Filters Button -->
        <div v-if="hasActiveFilters" class="flex flex-col justify-end">
          <button
            @click="clearAllFilters"
            class="w-full px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition duration-200 flex items-center justify-center gap-1 border border-gray-300 dark:border-gray-600"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Clear All
          </button>
        </div>
      </div>

      <!-- Active Filters Display -->
      <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 items-center text-sm">
        <span class="text-gray-600 dark:text-gray-400 font-medium">Active filters:</span>
        <span v-if="searchQuery" class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-xs">
          Search: "{{ searchQuery }}"
        </span>
        <span v-if="statusFilter" class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-md text-xs">
          Status: {{ statusFilter }}
        </span>
        <span v-if="formTypeFilter" class="px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-md text-xs truncate max-w-xs">
          Form: {{ formTypeFilter }}
        </span>
        <span v-if="organizationFilter" class="px-2 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-md text-xs truncate max-w-xs" :title="organizationOptions.find(opt => opt.value === organizationFilter)?.label && organizationOptions.find(opt => opt.value === organizationFilter)?.label.length > 20 ? `Organization: ${organizationOptions.find(opt => opt.value === organizationFilter)?.label}` : undefined">
          Organization: {{ organizationOptions.find(opt => opt.value === organizationFilter)?.label && organizationOptions.find(opt => opt.value === organizationFilter)?.label.length > 20 ? organizationOptions.find(opt => opt.value === organizationFilter)?.label.substring(0, 20) + '...' : organizationOptions.find(opt => opt.value === organizationFilter)?.label }}
        </span>
      </div>
    </div>

    <!-- Applications Table -->
    <div class="relative">
      <ApplicationsTable 
        v-if="filteredApplications.length > 0" 
        :applications="filteredApplications" 
        :isAdmin="isAdmin"
        :isPreviewModalOpen="showPreviewModal"
        @openStatusModal="openStatusModal"
        @deleteApplication="deleteApplication"
        @uploadDocument="handleDocumentUpload"
        @submitLink="handleSubmitLink"
        @refreshData="refreshApplications"
        @confirmDeleteDocument="handleConfirmDeleteDocument"
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
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
              End the Academic Year
            </h3>
          </div>
        </div>
        <div class="mt-2">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            This action will archive all current applications and end the academic year. This process cannot be undone easily.
          </p>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Academic Year</label>
              <input
                v-model="endYearForm.academic_year"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:focus:border-red-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                placeholder="e.g., 2024-2025"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirmation</label>
              <input
                v-model="endYearForm.confirmation"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:focus:border-red-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                placeholder="Type 'END_YEAR' to confirm"
              />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Type <strong>END_YEAR</strong> to confirm this action
              </p>
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="showEndYearModal = false"
            class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 transition"
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

    <!-- Delete Document Confirmation Modal -->
        <!-- Delete Document Confirmation Modal -->
    <Modal :show="showDeleteDocumentModal" @close="cancelDeleteDocument">
      <div class="p-6">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
              Delete Document
            </h3>
          </div>
        </div>
        <div class="mt-2">
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Are you sure you want to delete this signed document? This action cannot be undone.
          </p>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="cancelDeleteDocument"
            class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 transition"
          >
            Cancel
          </button>
          <button
            @click="actuallyDeleteDocument"
            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition"
          >
            Delete
          </button>
        </div>
      </div>
    </Modal>
    <!-- Deleting document progress modal -->
    <div v-if="isDeletingDocument" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-80">
        <h3 class="text-gray-800 dark:text-gray-100 font-semibold mb-4">Deleting document...</h3>
        <div class="flex justify-center">
          <svg class="animate-spin h-8 w-8 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </div>
    </div>

      <!-- Add a subtle, center-aligned archive link at the bottom -->
    <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mt-10 mb-6">
      <a
        :href="isAdmin ? route('admin.archive.index') : route('archive.index')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg shadow-sm transition duration-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
        aria-label="View Archive"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <span>{{ isAdmin ? 'Archive Management' : 'View Archive' }}</span>
      </a>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteConfirmation"
      title="Delete Application"
      :message="`Are you sure you want to delete this application? This action cannot be undone.`"
      type="danger"
      confirm-text="Delete"
      cancel-text="Cancel"
      @confirm="confirmDeleteApplication"
      @cancel="closeDeleteConfirmation"
    />

    <!-- Clear Saved Data Modal -->
    <ClearDataModal
      :show="showClearDataModal"
      :is-clearing="isClearingData"
      @close="closeClearDataModal"
      @confirm="confirmClearData"
    />

  </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.fade-enter-to, .fade-leave-from {
  opacity: 1;
}
</style>