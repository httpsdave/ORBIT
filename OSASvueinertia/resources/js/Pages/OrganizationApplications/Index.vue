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
  { type: 'LSPU-OSAS-SF-STATUS', label: 'Activity Status Report' },
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

// Back-to-top button state and handler
const showBackToTop = ref(false);
const onScroll = () => {
  try {
    const y = window.scrollY || window.pageYOffset;
    showBackToTop.value = y > 300;
  } catch (e) {
    // ignore in non-browser environments
  }
};

const scrollToTop = (e) => {
  e?.preventDefault();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const props = defineProps({ 
  applications: Array,
  successMessage: String,
  errorMessage: String,
  userId: Number,
  isAdmin: Boolean,
  users: Array,
  currentUserFilter: String,
  currentOrganizationFilter: String,
  currentArchiveFilter: String,
  updateMessage: String,
  currentPage: {
    type: Number,
    default: 1
  },
  hasMorePages: {
    type: Boolean,
    default: false
  },
  perPage: {
    type: Number,
    default: 20
  },
  allStatuses: {
    type: Array,
    default: () => []
  },
  allFormTypes: {
    type: Array,
    default: () => []
  }
});

// Infinite scroll state
const allApplications = ref([...props.applications]);
const isLoadingMore = ref(false);
const isFiltering = ref(false);
const currentPage = ref(props.currentPage);
const hasMorePages = ref(props.hasMorePages);

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
const organizationFilter = ref(props.currentOrganizationFilter || (!props.isAdmin ? props.userId?.toString() : ''));

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

// Bulk delete state (admin/superadmin only)
const showBulkDeleteModal = ref(false);
const selectedApplications = ref([]);
const isBulkModeActive = ref(false);

// Computed properties for bulk operations (admin only)
const isAllSelected = computed(() => {
  return props.isAdmin && filteredApplications.value.length > 0 && 
         selectedApplications.value.length === filteredApplications.value.length;
});

const isSomeSelected = computed(() => {
  return props.isAdmin && selectedApplications.value.length > 0 && 
         selectedApplications.value.length < filteredApplications.value.length;
});

const hasSelectedApplications = computed(() => {
  return props.isAdmin && selectedApplications.value.length > 0;
});

// Clear saved data modal state
const showClearDataModal = ref(false);
const isClearingData = ref(false);

// Get unique values for filter options from server-provided data
const statusOptions = computed(() => {
  const statuses = props.allStatuses && props.allStatuses.length > 0 
    ? props.allStatuses 
    : [...new Set(props.applications.map(app => app.status))];
  
  // Helper to check if an app has a signed document
  const hasSignedDocument = (app) => {
    return (app.signed_document_path && app.signed_document_path.trim() !== '') || 
           (app.signed_document_link && app.signed_document_link.trim() !== '');
  };
  
  // Create status options with "with Signed" variants (only if they exist)
  const options = [];
  statuses.forEach(status => {
    // Always add the regular status option
    options.push({ value: status, label: status });
    
    // Check if there are any applications with this status AND a signed document
    const hasAppsWithSigned = props.applications.some(app => {
      const statusMatch = status.toLowerCase() === 'disapproved' 
        ? ['rejected', 'disapproved'].includes(app.status?.toLowerCase())
        : app.status?.toLowerCase() === status.toLowerCase();
      
      return statusMatch && hasSignedDocument(app);
    });

    // Only add "with Signed" variant if there are actual applications
    // Skip adding the variant for 'Approved' status per request
    if (hasAppsWithSigned && status.toLowerCase() !== 'approved') {
      options.push({ 
        value: `${status}_with_signed`, 
        label: `${status} with Signed` 
      });
    }
  });
  
  return options;
});

const formTypeOptions = computed(() => {
  const types = props.allFormTypes && props.allFormTypes.length > 0 
    ? props.allFormTypes 
    : [...new Set(props.applications.map(app => app.form_type))];
  
  // Sort by numeric part if present, otherwise put at end
  const sortedTypes = types.slice().sort((a, b) => {
    const numA = a.match(/-(\d{3})$/)?.[1];
    const numB = b.match(/-(\d{3})$/)?.[1];
    if (numA && numB) {
      return parseInt(numA) - parseInt(numB);
    } else if (numA) {
      return -1;
    } else if (numB) {
      return 1;
    } else {
      return a.localeCompare(b);
    }
  });
  return sortedTypes.map(type => {
    const template = formTemplates.find(t => t.type === type);
    return { 
      value: type, 
      label: template?.label || type,
      formType: type // Keep the original form type for tooltip
    };
  });
});

const organizationOptions = computed(() => {
  if (!props.users || props.users.length === 0) return [];
  return props.users.map(user => ({ value: user.id.toString(), label: user.name }));
});

// Check if user has multiple viewable organizations (parent/sub-orgs)
const hasMultipleViewableOrgs = computed(() => {
  return !props.isAdmin && props.users && props.users.length > 1;
});

// Helper to check if an app has a signed document
const hasSignedDocument = (app) => {
  return (app.signed_document_path && app.signed_document_path.trim() !== '') || 
         (app.signed_document_link && app.signed_document_link.trim() !== '');
};

// Check if there are pending applications with signed documents
const hasPendingWithSigned = computed(() => {
  return props.applications.some(app => 
    app.status?.toLowerCase() === 'pending' && hasSignedDocument(app)
  );
});

// Check if there are approved applications with signed documents
const hasApprovedWithSigned = computed(() => {
  return props.applications.some(app => 
    app.status?.toLowerCase() === 'approved' && hasSignedDocument(app)
  );
});

// Check if there are disapproved applications with signed documents
const hasDisapprovedWithSigned = computed(() => {
  return props.applications.some(app => 
    ['rejected', 'disapproved'].includes(app.status?.toLowerCase()) && hasSignedDocument(app)
  );
});

// Combined filter function - now triggers server-side filtering
const filterApplications = async () => {
  // When filters are applied, we need to reload from server with filters
  if (hasActiveFilters.value) {
    await reloadWithFilters();
  } else {
    // No filters, show all loaded applications
    filteredApplications.value = [...allApplications.value];
  }
};

// Reload applications from server with current filters
const reloadWithFilters = async () => {
  isFiltering.value = true;
  try {
    const params = new URLSearchParams({
      page: 1,
      per_page: props.perPage.toString()
    });
    
    // Add current filters
    if (statusFilter.value) params.append('status_filter', statusFilter.value);
    if (formTypeFilter.value) params.append('form_type_filter', formTypeFilter.value);
    if (organizationFilter.value) params.append('organization_filter', organizationFilter.value);
    if (searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
    
    const response = await fetch(`/applications/load-more?${params.toString()}`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    
    const data = await response.json();
    
    if (data.applications && Array.isArray(data.applications)) {
      // Replace all applications with filtered results
      allApplications.value = [...data.applications];
      filteredApplications.value = [...data.applications];
      currentPage.value = data.currentPage;
      hasMorePages.value = data.hasMorePages;
    }
    
  } catch (error) {
    console.error('Error filtering applications:', error);
    localMessage.value = 'Failed to filter applications. Please try again.';
    statusType.value = 'error';
    showMessage.value = true;
    setTimeout(() => {
      showMessage.value = false;
    }, 5000);
  } finally {
    isFiltering.value = false;
  }
};

// Clear all filters
const clearAllFilters = async () => {
  searchQuery.value = '';
  statusFilter.value = '';
  formTypeFilter.value = '';
  // Reset organization filter to user's own ID for non-admins, empty for admins
  organizationFilter.value = props.isAdmin ? '' : props.userId?.toString();
  // Reset pagination when filters change
  resetPagination();
  await filterApplications();
};

// Reset pagination to initial state
const resetPagination = () => {
  currentPage.value = 1;
  hasMorePages.value = props.hasMorePages;
  // Only reset to props if no filters are active, otherwise let reloadWithFilters handle it
  if (!hasActiveFilters.value) {
    allApplications.value = [...props.applications];
  }
};

// Check if any filters are active
const hasActiveFilters = computed(() => {
  // For non-admin users, don't count organization filter as "active" if it's their own ID (default)
  const orgFilterActive = props.isAdmin 
    ? !!organizationFilter.value 
    : organizationFilter.value && organizationFilter.value !== props.userId?.toString();
  
  return searchQuery.value || statusFilter.value || formTypeFilter.value || orgFilterActive;
});

const clearSearch = async () => {
    searchQuery.value = '';
    resetPagination();
    await filterApplications();
};

// Helper to set status filter from pill buttons
const setStatusFilter = async (val) => {
  statusFilter.value = val;
  resetPagination();
  await filterApplications();
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

// Handle clicks outside to exit bulk mode
const handleClickOutside = (event) => {
  if (!isBulkModeActive.value || !props.isAdmin) return;
  
  // Check if click is outside the applications table, filter bar, bulk button, and selection banner
  const applicationsTable = document.querySelector('.applications-table-container');
  const filterBar = document.querySelector('.filter-bar-container');
  const bulkButton = document.querySelector('.bulk-delete-button');
  const selectionBanner = document.querySelector('.bulk-selection-banner');
  const bulkDeleteModal = document.querySelector('.bulk-delete-modal');
  
  // Don't close if clicking inside these elements
  const isInsideAllowedArea = 
    (applicationsTable && applicationsTable.contains(event.target)) ||
    (filterBar && filterBar.contains(event.target)) ||
    (bulkButton && bulkButton.contains(event.target)) ||
    (selectionBanner && selectionBanner.contains(event.target)) ||
    (bulkDeleteModal && bulkDeleteModal.contains(event.target));
  
  if (!isInsideAllowedArea) {
    isBulkModeActive.value = false;
    selectedApplications.value = [];
  }
};

onMounted(() => {
  filteredApplications.value = [...allApplications.value];
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
  // register back-to-top scroll listener
  window.addEventListener('scroll', onScroll, { passive: true });
  // register infinite scroll listener
  window.addEventListener('scroll', handleScroll, { passive: true });
  // register click outside handler for bulk mode
  document.addEventListener('click', handleClickOutside);
  
  onUnmounted(() => {
    window.removeEventListener('inertia:navigate', handler);
    window.removeEventListener('scroll', onScroll);
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', handleClickOutside);
  });
});

// Watch for filter changes with debouncing
let filterTimeout = null;
watch([searchQuery, statusFilter, formTypeFilter, organizationFilter], ([newSearch, newStatus, newFormType, newOrg], [oldSearch, oldStatus, oldFormType, oldOrg]) => {
  // Reset pagination if filters changed
  const filtersChanged = newSearch !== oldSearch || 
                         newStatus !== oldStatus || 
                         newFormType !== oldFormType || 
                         newOrg !== oldOrg;
  
  if (filtersChanged) {
    resetPagination();
    // Clear selections when filters change (admin only)
    if (props.isAdmin) {
      selectedApplications.value = [];
    }
  }
  
  // Debounce filter changes to avoid excessive API calls
  if (filterTimeout) clearTimeout(filterTimeout);
  filterTimeout = setTimeout(async () => {
    await filterApplications();
  }, 300); // 300ms debounce
}, { immediate: true });

// Watch for modal open/close to lock body scroll
watch(showPreviewModal, (val) => {
  if (val) {
    document.body.classList.add('overflow-hidden');
  } else {
    document.body.classList.remove('overflow-hidden');
  }
});

// Watch for changes in props to update local state
watch(() => props.applications, (newApplications) => {
  if (newApplications) {
    allApplications.value = [...newApplications];
    filterApplications();
  }
}, { deep: true });

watch(() => props.hasMorePages, (newValue) => {
  hasMorePages.value = newValue;
});

watch(() => props.currentPage, (newValue) => {
  currentPage.value = newValue;
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

const refreshApplications = async () => {
  // Reset to the initial state and re-fetch first page
  resetPagination();
  
  // If we have active filters, reload with filters, otherwise reload via Inertia
  if (hasActiveFilters.value) {
    await filterApplications();
  } else {
    // Reload the first page via Inertia for unfiltered data
    router.reload({ only: ['applications', 'currentPage', 'hasMorePages'], preserveScroll: true });
  }
};

// Infinite scroll functionality
const loadMoreApplications = async () => {
  if (isLoadingMore.value || !hasMorePages.value || isFiltering.value) return;
  
  isLoadingMore.value = true;
  
  try {
    const params = new URLSearchParams({
      page: (currentPage.value + 1).toString(),
      per_page: props.perPage.toString()
    });
    
    // Add current filters to maintain consistency - but only if they actually have values
    if (statusFilter.value) params.append('status_filter', statusFilter.value);
    if (formTypeFilter.value) params.append('form_type_filter', formTypeFilter.value);
    if (organizationFilter.value) params.append('organization_filter', organizationFilter.value);
    if (searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
    
    const response = await fetch(`/applications/load-more?${params.toString()}`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    
    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    
    const data = await response.json();
    
    // Only proceed if we got valid data
    if (data.applications && Array.isArray(data.applications)) {
      // Append new applications to the existing list, avoiding duplicates
      const existingIds = new Set(allApplications.value.map(app => app.id));
      const newApplications = data.applications.filter(app => !existingIds.has(app.id));
      
      allApplications.value = [...allApplications.value, ...newApplications];
      filteredApplications.value = [...allApplications.value];
      currentPage.value = data.currentPage;
      hasMorePages.value = data.hasMorePages;
    }
    
  } catch (error) {
    console.error('Error loading more applications:', error);
    localMessage.value = 'Failed to load more applications. Please try again.';
    statusType.value = 'error';
    showMessage.value = true;
    setTimeout(() => {
      showMessage.value = false;
    }, 5000);
  } finally {
    isLoadingMore.value = false;
  }
};

// Scroll detection for infinite scroll
const handleScroll = () => {
  // Throttle scroll events to avoid excessive calls
  if (isLoadingMore.value || isFiltering.value) return;
  
  const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  const windowHeight = window.innerHeight;
  const documentHeight = document.documentElement.scrollHeight;
  
  // Load more when user is 200px from bottom and there are more pages
  if (hasMorePages.value && scrollTop + windowHeight >= documentHeight - 200) {
    loadMoreApplications();
  }
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

// Bulk delete functions (admin only)
const toggleBulkMode = () => {
  if (!props.isAdmin) return;
  
  isBulkModeActive.value = !isBulkModeActive.value;
  // Clear selections when exiting bulk mode
  if (!isBulkModeActive.value) {
    selectedApplications.value = [];
  }
};

const toggleSelectAll = () => {
  if (!props.isAdmin) return;
  
  if (isAllSelected.value) {
    selectedApplications.value = [];
  } else {
    selectedApplications.value = filteredApplications.value.map(app => app.id);
  }
};

const toggleApplicationSelection = (applicationId) => {
  if (!props.isAdmin) return;
  
  const index = selectedApplications.value.indexOf(applicationId);
  if (index > -1) {
    selectedApplications.value.splice(index, 1);
  } else {
    selectedApplications.value.push(applicationId);
  }
};

const confirmBulkDelete = () => {
  if (!props.isAdmin || selectedApplications.value.length === 0) return;
  showBulkDeleteModal.value = true;
};

const bulkDeleteApplications = () => {
  if (!props.isAdmin || selectedApplications.value.length === 0) return;
  
  router.delete('/applications/bulk-destroy', {
    data: {
      application_ids: selectedApplications.value
    },
    preserveScroll: true,
    onSuccess: () => {
      showBulkDeleteModal.value = false;
      selectedApplications.value = [];
      isBulkModeActive.value = false; // Exit bulk mode after successful deletion
      localMessage.value = 'Applications deleted successfully!';
      statusType.value = 'delete';
      showMessage.value = true;
      setTimeout(() => {
        showMessage.value = false;
      }, 5000);
      // Refresh applications list
      refreshApplications();
    },
    onError: () => {
      localMessage.value = 'Failed to delete applications.';
      statusType.value = 'error';
      showMessage.value = true;
    }
  });
};
</script>

<template>
  <Head title="Applications" />

  <AuthenticatedLayout>
    <!-- Background Illustration - Responsive across all screen sizes -->
    <div class="fixed bottom-0 left-1/2 transform -translate-x-1/2 translate-y-[32%] opacity-[0.25] dark:opacity-[0.08] w-[400px] h-[400px] sm:w-[500px] sm:h-[500px] md:w-[700px] md:h-[700px] lg:w-[900px] lg:h-[900px] xl:w-[1200px] xl:h-[1200px] pointer-events-none z-0">
      <img 
        src="/images/flatillus1.svg" 
        alt="" 
        class="w-full h-full object-contain"
        role="presentation"
      />
    </div>

    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 w-full">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
          {{ isAdmin ? 'Manage Submissions' : 'Your Submissions' }}
        </h2>
        <div class="flex flex-row sm:flex-row gap-1.5 sm:gap-2 w-full sm:w-auto items-center relative">
          <Link
            href="/applications/select-form"
            class="inline-flex items-center justify-center px-2 py-1.5 sm:px-4 sm:py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-xs sm:text-sm font-medium text-white rounded-md sm:rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group flex-1 sm:flex-none"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 mr-1 sm:mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            <span class="hidden xs:inline">Create</span>
            <span class="xs:hidden">New</span>
          </Link>
          <!-- Preview Forms Dropdown (Users and Admins) -->
          <div class="relative flex-1 sm:flex-none">
            <button
              @click="showPreviewDropdown = !showPreviewDropdown"
              class="inline-flex items-center justify-center px-2 py-1.5 sm:px-4 sm:py-2 bg-white dark:bg-gray-800 border border-blue-500 dark:border-blue-400 text-blue-700 dark:text-blue-300 text-xs sm:text-sm font-medium rounded-md sm:rounded-xl shadow-md hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-800 dark:hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group w-full sm:w-auto"
              type="button"
            >
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-blue-500 rounded-full group-hover:w-96 group-hover:h-96 opacity-5"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 mr-1 sm:mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M15 10a1 1 0 01-1 1H6a1 1 0 110-2h8a1 1 0 011 1z" clip-rule="evenodd" />
              </svg>
              <span class="hidden xs:inline">Preview</span>
              <span class="xs:hidden">Forms</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 ml-0.5 sm:ml-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div
              v-if="showPreviewDropdown"
              class="absolute left-0 sm:right-0 sm:left-auto mt-2 w-64 sm:w-56 max-w-[calc(100vw-2rem)] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50 max-h-[70vh] overflow-y-auto"
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
          <button
            v-if="isAdmin"
            @click="openEndYearModal"
            class="inline-flex items-center justify-center px-2 py-1.5 sm:px-4 sm:py-2 bg-gradient-to-r from-red-500 to-red-600 text-xs sm:text-sm font-medium text-white rounded-md sm:rounded-xl shadow-md hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group flex-1 sm:flex-none"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 mr-1 sm:mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span class="hidden xs:inline">End Year</span>
            <span class="xs:hidden">End</span>
          </button>
          <button
            @click="openClearDataModal"
            class="inline-flex items-center justify-center p-1.5 sm:p-2 bg-transparent hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 rounded-md sm:rounded-lg shadow-sm transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900 group relative flex-shrink-0"
            aria-label="Clear Saved Form Data"
          >
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor" class="sm:h-[26px] sm:w-[26px]">
              <path d="M280-720v520-520Zm170 600H280q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v172q-17-5-39.5-8.5T680-560v-160H280v520h132q6 21 16 41.5t22 38.5Zm-90-160h40q0-63 20-103.5l20-40.5v-216h-80v360Zm160-230q17-11 38.5-22t41.5-16v-92h-80v130ZM680-80q-83 0-141.5-58.5T480-280q0-83 58.5-141.5T680-480q83 0 141.5 58.5T880-280q0 83-58.5 141.5T680-80Zm66-106 28-28-74-74v-112h-40v128l86 86Z"/>
            </svg>
            <!-- Tooltip: move above button, increase z-index for responsiveness -->
            <span class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-30 shadow-lg pointer-events-none">
              Clear saved form data
            </span>
          </button>
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
    <div class="max-w-4xl mx-auto px-3 sm:px-6 mb-6 space-y-3">
      <!-- Search Bar -->
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input
          type="text"
          v-model="searchQuery"
          class="block w-full pl-9 sm:pl-12 pr-9 sm:pr-12 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 dark:border-gray-600 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 transition duration-150 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
          :placeholder="isAdmin ? 'Search submissions...' : 'Search submissions...'"
        />
        <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center">
          <button @click="clearSearch" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Status pill buttons and Form Type Filter - Non-Admin Users -->
      <div v-if="!isAdmin" class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
        <!-- Filters Row - Left side on larger screens -->
        <div class="flex flex-row sm:flex-row gap-2 w-full sm:w-auto items-center">
          <!-- Organization Filter (only show if user has parent/sub orgs) -->
          <div v-if="hasMultipleViewableOrgs" class="flex-1 sm:flex-none sm:w-auto">
            <select 
              v-model="organizationFilter"
              class="w-full pl-2.5 pr-7 py-1.5 sm:pl-3 sm:pr-8 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-full text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
            >
              <option v-for="option in organizationOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
          
          <!-- Form Type Filter -->
          <div class="flex-1 sm:flex-none sm:w-auto">
            <select 
              v-model="formTypeFilter"
              class="w-full pl-2.5 pr-7 py-1.5 sm:pl-3 sm:pr-8 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-full text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
            >
              <option value="">All Form Types</option>
              <option 
                v-for="option in formTypeOptions" 
                :key="option.value" 
                :value="option.value"
                :title="option.formType"
              >
                {{ option.label }}
              </option>
            </select>
          </div>

          <!-- Clear Filters Button -->
          <button 
            v-if="hasActiveFilters"
            @click="clearAllFilters"
            class="flex-shrink-0 p-1.5 sm:p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
            title="Clear all filters"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
        
        <!-- Status pill buttons - Right side on larger screens -->
        <div class="flex justify-center sm:justify-end">
          <div class="inline-flex flex-wrap rounded-full bg-gray-100 dark:bg-gray-800 p-0.5 gap-1 w-full sm:w-auto justify-center">
            <button @click="setStatusFilter('')" :class="['px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs font-medium text-center transition-all whitespace-nowrap', !statusFilter ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200']">All</button>
            <button @click="setStatusFilter('pending')" :class="['px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs font-medium text-center transition-all whitespace-nowrap', statusFilter && statusFilter.toLowerCase() === 'pending' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200']">Pending</button>
            <button v-if="hasPendingWithSigned" @click="setStatusFilter('pending_with_signed')" :class="['px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs font-medium text-center transition-all whitespace-nowrap', statusFilter && statusFilter.toLowerCase() === 'pending_with_signed' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200']">Pending w/ Signed</button>
            <button @click="setStatusFilter('approved')" :class="['px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs font-medium text-center transition-all whitespace-nowrap', statusFilter && statusFilter.toLowerCase() === 'approved' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200']">Approved</button>
            <button v-if="hasApprovedWithSigned" @click="setStatusFilter('approved_with_signed')" :class="['px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs font-medium text-center transition-all whitespace-nowrap', statusFilter && statusFilter.toLowerCase() === 'approved_with_signed' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200']">Approved w/ Signed</button>
            <button @click="setStatusFilter('disapproved')" :class="['px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs font-medium text-center transition-all whitespace-nowrap', statusFilter && statusFilter.toLowerCase() === 'disapproved' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200']">Disapproved</button>
            <button v-if="hasDisapprovedWithSigned" @click="setStatusFilter('disapproved_with_signed')" :class="['px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs font-medium text-center transition-all whitespace-nowrap', statusFilter && statusFilter.toLowerCase() === 'disapproved_with_signed' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200']">Disapproved w/ Signed</button>
          </div>
        </div>
      </div>

      <!-- Filter Bar (Admin Only) - Responsive Grid Layout -->
      <div v-if="isAdmin" class="filter-bar-container grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2">
        <!-- Status Filter -->
        <div class="col-span-1">
          <select 
            v-model="statusFilter"
            class="w-full pl-2.5 pr-7 py-1.5 sm:pl-3 sm:pr-8 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-full text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
          >
            <option value="">All Statuses</option>
            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Form Type Filter -->
        <div class="col-span-1">
          <select 
            v-model="formTypeFilter"
            class="w-full pl-2.5 pr-7 py-1.5 sm:pl-3 sm:pr-8 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-full text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
          >
            <option value="">All Types</option>
            <option 
              v-for="option in formTypeOptions" 
              :key="option.value" 
              :value="option.value"
              :title="option.formType"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Organization Filter -->
        <div v-if="users.length > 0" class="col-span-2 sm:col-span-1">
          <select 
            v-model="organizationFilter"
            class="w-full pl-2.5 pr-7 py-1.5 sm:pl-3 sm:pr-8 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-full text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
          >
            <option value="">All Organizations</option>
            <option 
              v-for="option in organizationOptions" 
              :key="option.value" 
              :value="option.value"
              :title="option.label.length > 15 ? option.label : undefined"
            >
              {{ option.label.length > 15 ? option.label.substring(0, 15) + '...' : option.label }}
            </option>
          </select>
        </div>

        <!-- Bulk Delete Toggle Button -->
        <button
          @click="toggleBulkMode"
          :class="[
            'bulk-delete-button relative p-2 rounded-full transition-all duration-200 flex items-center justify-center group',
            isBulkModeActive 
              ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 shadow-md' 
              : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 shadow-sm'
          ]"
          :title="isBulkModeActive ? 'Exit Bulk Delete Mode' : 'Bulk Delete Mode'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="14" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
          </svg>
          <!-- Tooltip -->
          <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50 pointer-events-none">
            {{ isBulkModeActive ? 'Exit Bulk Delete' : 'Bulk Delete' }}
          </span>
        </button>

        <!-- Clear All Filters Button -->
        <button
          v-if="hasActiveFilters"
          @click="clearAllFilters"
          class="col-span-2 sm:col-span-1 px-3 py-1.5 sm:py-2 text-xs sm:text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition duration-200 flex items-center justify-center gap-1.5 border border-gray-300 dark:border-gray-600 shadow-sm"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <span>Clear Filters</span>
        </button>
      </div>

      <!-- Active Filters Display - Compact for Mobile -->
      <div v-if="hasActiveFilters" class="flex flex-wrap gap-1.5 sm:gap-2 items-center text-xs sm:text-sm">
        <span class="text-gray-600 dark:text-gray-400 font-medium text-xs sm:text-sm">Active:</span>
        <span v-if="searchQuery" class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-xs">
          "{{ searchQuery.length > 15 ? searchQuery.substring(0, 15) + '...' : searchQuery }}"
        </span>
        <span v-if="statusFilter" class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-md text-xs">
          {{ statusFilter }}
        </span>
        <span v-if="formTypeFilter" class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-md text-xs truncate max-w-[120px] sm:max-w-xs" :title="`Form Type: ${formTypeFilter}`">
          {{ formTemplates.find(f => f.type === formTypeFilter)?.label || formTypeFilter }}
        </span>
        <!-- Only show organization filter badge if it's not the user's own organization (for non-admins) or if admin -->
        <span v-if="organizationFilter && (isAdmin || organizationFilter !== userId?.toString())" class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-md text-xs truncate max-w-[120px] sm:max-w-xs" :title="organizationOptions.find(opt => opt.value === organizationFilter)?.label">
          {{ organizationOptions.find(opt => opt.value === organizationFilter)?.label && organizationOptions.find(opt => opt.value === organizationFilter)?.label.length > 15 ? organizationOptions.find(opt => opt.value === organizationFilter)?.label.substring(0, 15) + '...' : organizationOptions.find(opt => opt.value === organizationFilter)?.label }}
        </span>
      </div>
    </div>

    <!-- Applications Table -->
    <div class="relative max-w-7xl mx-auto px-3 sm:px-6">
      <div class="max-w-4xl mx-auto">
        <!-- Bulk selection info and actions (Admin only - only show when bulk mode is active) -->
        <div v-if="isBulkModeActive && hasSelectedApplications" class="bulk-selection-banner mb-4 flex flex-col xs:flex-row xs:items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 max-w-4xl mx-auto">
          <div class="flex items-center gap-2 flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
              {{ selectedApplications.length }} application{{ selectedApplications.length !== 1 ? 's' : '' }} selected
            </span>
          </div>
          <div class="flex gap-2">
            <button
              @click="selectedApplications = []"
              class="inline-flex items-center justify-center px-3 py-1.5 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 uppercase tracking-wider hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition duration-150 ease-in-out"
            >
              Clear Selection
            </button>
            <button
              @click="confirmBulkDelete"
              class="relative inline-flex items-center justify-center p-2 bg-red-500 border border-transparent rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 ease-in-out group"
              :title="`Delete ${selectedApplications.length} selected application${selectedApplications.length !== 1 ? 's' : ''}`"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="applications-table-container relative">
          <!-- Content Layer -->
          <div class="relative z-10">
        <ApplicationsTable 
          v-if="filteredApplications.length > 0" 
          :applications="filteredApplications" 
          :isAdmin="isAdmin"
          :isPreviewModalOpen="showPreviewModal"
          :selectedApplications="selectedApplications"
          :isAllSelected="isAllSelected"
          :isSomeSelected="isSomeSelected"
          :isBulkModeActive="isBulkModeActive"
          @openStatusModal="openStatusModal"
          @deleteApplication="deleteApplication"
          @uploadDocument="handleDocumentUpload"
          @submitLink="handleSubmitLink"
          @refreshData="refreshApplications"
          @confirmDeleteDocument="handleConfirmDeleteDocument"
          @toggleSelectAll="toggleSelectAll"
          @toggleApplicationSelection="toggleApplicationSelection"
        />
        <NoApplicationsMessage v-else />
        
        <!-- Filtering loading indicator -->
        <div v-if="isFiltering" class="flex justify-center py-8">
          <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium">Filtering applications...</span>
          </div>
        </div>
        
        <!-- Infinite scroll loading indicator -->
        <div v-else-if="isLoadingMore" class="flex justify-center py-8">
          <div class="flex items-center space-x-2 text-gray-600 dark:text-gray-400">
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium">Loading more applications...</span>
          </div>
        </div>
          </div>
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

    <!-- Back to top floating button -->
    <button
      v-if="showBackToTop"
      @click="scrollToTop"
      aria-label="Back to top"
      class="fixed z-50 right-6 bottom-8 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 shadow-lg hover:shadow-2xl rounded-full p-3 transition transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
      title="Back to top"
    >
      <!-- Modern chevron-up icon -->
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 5a1 1 0 01.707.293l5 5a1 1 0 01-1.414 1.414L10 7.414 5.707 11.707A1 1 0 014.293 10.293l5-5A1 1 0 0110 5z" clip-rule="evenodd" />
      </svg>
    </button>

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

    <!-- Bulk Delete Confirmation Modal (Admin Only) -->
    <Modal :show="showBulkDeleteModal" @close="showBulkDeleteModal = false">
      <div class="bulk-delete-modal p-6 bg-white dark:bg-gray-800">
        <div class="flex items-center mb-5">
          <div class="flex-shrink-0 bg-red-100 dark:bg-red-900/20 rounded-full p-2 mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Multiple Applications</h3>
        </div>
        <div class="mb-6">
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Are you sure you want to delete {{ selectedApplications.length }} application{{ selectedApplications.length !== 1 ? 's' : '' }}?
          </p>
          <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
            <div class="flex items-start">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              <div class="ml-3">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Warning</p>
                <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                  This action cannot be undone. All selected applications and their associated data will be permanently deleted.
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="flex justify-end space-x-3">
          <button
            @click="showBulkDeleteModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out"
          >
            Cancel
          </button>
          <button
            @click="bulkDeleteApplications"
            class="px-4 py-2 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out"
          >
            Delete {{ selectedApplications.length }} Application{{ selectedApplications.length !== 1 ? 's' : '' }}
          </button>
        </div>
      </div>
    </Modal>

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