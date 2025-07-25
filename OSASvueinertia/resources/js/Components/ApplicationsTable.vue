<script setup>
import { Link, router } from '@inertiajs/vue3';
import { defineProps, defineEmits, ref, nextTick, onMounted, onUnmounted } from 'vue';
const props = defineProps({
  applications: Array,
  isAdmin: Boolean,
});

const emit = defineEmits(['openStatusModal', 'deleteApplication', 'uploadDocument', 'refreshData', 'confirmDeleteDocument']);

// Upload document functionality
const documentUploadForm = ref(null);
const selectedApplicationId = ref(null);
const fileInput = ref(null);
const isUploading = ref(false);
const uploadProgress = ref(0);
const isDeleting = ref(false);
const activeDropdownApp = ref(null);
const dropdownPosition = ref({ top: 0, left: 0 });
const dropdownButtonEl = ref(null);
const dropdownRef = ref(null);
const dropdownDirection = ref('down'); // 'down' or 'up'
const activeMobileDropdownId = ref(null); // For mobile card dropdown

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
      return 'Organization Recognition';
    case 'LSPU-OSAS-SF-002':
      return 'Renewal Application';
    case 'LSPU-OSAS-SF-003':
      return 'Commitment Form';
    case 'LSPU-OSAS-SF-004':
      return 'Plan of Activities';
    case 'LSPU-OSAS-SF-005':
      return 'Members List';
    case 'LSPU-OSAS-SF-006':
      return 'Certification Form';
    case 'LSPU-OSAS-SF-007':
      return 'Officers List';
    case 'LSPU-OSAS-SF-009':
      return 'Student Activity Attendance Sheet';
    case 'LSPU-OSAS-SF-EVAL':
      return 'Evaluation Summary';
    case 'LSPU-OSAS-SF-ACCOMPLISHMENT':
      return 'Accomplishment Report';
    case 'LSPU-OSAS-SF-NARRATIVE':
      return 'Narrative Report';
    case 'LSPU-OSAS-SF-BYLAWS':
      return 'Constitution & By-Laws';
    case 'LSPU-OSAS-SF-FINANCIAL':
      return 'Financial Report';
    case 'LSPU-ACAD-RL':
      return 'Event Letter'; // Show as Event Letter
    default:
      return formType;
  }
};

// Format date function to display submission date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return 'Invalid Date';
  
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Add this function to your methods
const getDropdownPosition = (appId) => {
  // Find the button element that triggered this dropdown
  const buttonElement = document.querySelector(`[data-dropdown-trigger="${appId}"]`);
  
  if (!buttonElement) return { top: '0px', right: '0px' };
  
  const rect = buttonElement.getBoundingClientRect();
  
  // Position dropdown relative to the button
  return {
    top: `${rect.bottom + window.scrollY + 5}px`, // 5px padding below button
    left: `${rect.right - 192 + window.scrollX}px`, // 192px = 48px (dropdown width) * 4
  };
};

const getPdfRoute = (app, action = 'download') => {
  const queryParams = action === 'view' ? '?action=view' : '';

  // Direct-upload forms: no generated PDF route needed
  const directUploadTypes = [
    'LSPU-OSAS-SF-ACCOMPLISHMENT',
    'LSPU-OSAS-SF-NARRATIVE',
    'LSPU-OSAS-SF-BYLAWS',
    'LSPU-OSAS-SF-FINANCIAL',
    'LSPU-ACAD-RL', // Added
  ];
  if (directUploadTypes.includes(app.form_type)) {
    // No PDF route for these types
    return null;
  }

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
  } else if (app.form_type === 'LSPU-OSAS-SF-EVAL') {
    return `/applications/${app.id}/export-evaluation${queryParams}`;
  } else {
    // Default case: do not warn for unknown direct-upload types
    return `/applications/${app.id}/pdf${queryParams}`;
  }
};

// Toggle action dropdown
const toggleDropdown = (app, event) => {
  if (window.innerWidth < 640) { // Mobile: show inline dropdown
    if (activeMobileDropdownId.value === app.id) {
      activeMobileDropdownId.value = null;
    } else {
      activeMobileDropdownId.value = app.id;
    }
    return;
  }
  // Desktop/table: floating dropdown
  if (activeDropdownApp.value && activeDropdownApp.value.id === app.id) {
    activeDropdownApp.value = null;
    dropdownButtonEl.value = null;
    removeDropdownListeners();
  } else {
    activeDropdownApp.value = app;
    dropdownButtonEl.value = event.currentTarget;
    updateDropdownPosition();
    addDropdownListeners();
  }
};

async function updateDropdownPosition() {
  if (!dropdownButtonEl.value) return;
  const rect = dropdownButtonEl.value.getBoundingClientRect();
  let dropdownWidth = window.innerWidth < 640 ? Math.min(window.innerWidth - 32, 320) : 256;
  let left = rect.right - dropdownWidth;
  if (left + dropdownWidth > window.innerWidth) left = window.innerWidth - dropdownWidth - 16;
  if (left < 16) left = 16;

  await nextTick();
  let dropdownHeight = dropdownRef.value ? dropdownRef.value.offsetHeight : 320;

  const spaceBelow = window.innerHeight - rect.bottom;
  const spaceAbove = rect.top;

  let top;
  if (spaceBelow >= dropdownHeight + 16) {
    top = rect.bottom + 6;
    dropdownDirection.value = 'down';
  } else if (spaceAbove >= dropdownHeight + 16) {
    top = rect.top - dropdownHeight - 6;
    dropdownDirection.value = 'up';
  } else if (spaceBelow >= spaceAbove) {
    top = rect.bottom + 6;
    dropdownDirection.value = 'down';
  } else {
    top = Math.max(8, rect.top - dropdownHeight - 6);
    dropdownDirection.value = 'up';
  }

  dropdownPosition.value = { top, left };
}

function addDropdownListeners() {
  window.addEventListener('scroll', updateDropdownPosition, true);
  window.addEventListener('resize', updateDropdownPosition);
}
function removeDropdownListeners() {
  window.removeEventListener('scroll', updateDropdownPosition, true);
  window.removeEventListener('resize', updateDropdownPosition);
}

// Function to trigger file input click
const triggerFileUpload = (appId) => {
  selectedApplicationId.value = appId;
  activeDropdownApp.value = null;
  nextTick(() => {
    fileInput.value.click();
  });
};

// Function to handle file selection
const handleFileSelection = () => {
  if (fileInput.value.files.length > 0) {
    uploadDocument(fileInput.value.files[0]);
  }
};

// Function to upload document
const uploadDocument = (file) => {
  if (!selectedApplicationId.value) return;
  
  isUploading.value = true;
  uploadProgress.value = 0;
  
  const formData = new FormData();
  formData.append('signed_document', file);
  
  router.post(`/applications/${selectedApplicationId.value}/upload-document`, formData, {
    onProgress: (progress) => {
      uploadProgress.value = progress.percentage;
    },
    onSuccess: () => {
      isUploading.value = false;
      uploadProgress.value = 100;
      
      // Reset the file input
      if (fileInput.value) {
        fileInput.value.value = '';
      }
      
      // Emit event for parent component to handle success message
      emit('uploadDocument', {
        success: true,
        message: 'Document uploaded successfully!'
      });
      
      // Refresh data
      emit('refreshData');
      
      // After 2 seconds, reset progress bar
      setTimeout(() => {
        uploadProgress.value = 0;
      }, 2000);
    },
    onError: () => {
      isUploading.value = false;
      
      // Emit event for parent component to handle error message
      emit('uploadDocument', {
        success: false,
        message: 'Failed to upload document.'
      });
      
      // Reset the file input
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    }
  });
};

// Function to delete document
const deleteDocument = (appId) => {
  activeDropdownApp.value = null;
  emit('confirmDeleteDocument', appId);
};

// Handle other actions
const handleAction = (app, action) => {
  // Close dropdown
  activeDropdownApp.value = null;
  
  // Handle specific actions
  switch(action) {
    case 'updateStatus':
      emit('openStatusModal', app);
      break;
    case 'delete':
      emit('deleteApplication', app.id);
      break;
  }
};

// Add this after your existing refs
onMounted(() => {
  document.addEventListener('click', closeDropdowns);
  document.addEventListener('click', closeMobileDropdowns);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdowns);
  document.removeEventListener('click', closeMobileDropdowns);
  removeDropdownListeners();
});

// Update the closeDropdowns function
const closeDropdowns = (event) => {
  if (!event.target.closest('.dropdown-container')) {
    activeDropdownApp.value = null;
  }
};

const getReportPath = (app) => {
  let path = null;
  switch(app.form_type) {
    case 'LSPU-OSAS-SF-ACCOMPLISHMENT':
      path = app.accomplishment_report_path;
      break;
    case 'LSPU-OSAS-SF-NARRATIVE':
      path = app.narrative_report_path;
      break;
    case 'LSPU-OSAS-SF-BYLAWS':
      path = app.bylaws_path;
      break;
    case 'LSPU-OSAS-SF-FINANCIAL':
      path = app.financial_report_path;
      break;
    case 'LSPU-ACAD-RL':
      path = app.event_letter_path;
      break;
    default:
      path = app.signed_document_path;
  }
  return path && path !== '' ? path : null;
};

const getViewUrl = (app) => {
  // For direct-upload forms, link directly to the PDF
  const reportPath = getReportPath(app);
  if ([
    'LSPU-OSAS-SF-ACCOMPLISHMENT',
    'LSPU-OSAS-SF-NARRATIVE',
    'LSPU-OSAS-SF-BYLAWS',
    'LSPU-OSAS-SF-FINANCIAL',
    'LSPU-ACAD-RL', // Added
  ].includes(app.form_type) && reportPath) {
    return `/storage/${reportPath}`;
  }
  // Otherwise, use the generated PDF route (if available)
  const pdfRoute = getPdfRoute(app, 'view');
  return pdfRoute ? pdfRoute : '#';
};

// Add for mobile dropdown close on outside click
const closeMobileDropdowns = (event) => {
  if (window.innerWidth >= 640) return;
  if (
    activeMobileDropdownId.value &&
    !event.target.closest('.mobile-dropdown-menu')
  ) {
    activeMobileDropdownId.value = null;
  }
};

// Add this method in <script setup>
const viewPdf = (app) => {
  const url = getViewUrl(app);
  if (url && url !== '#') {
    window.open(url, '_blank');
  }
};

</script>

<template>
  <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 relative" @click="closeDropdowns">
    <!-- File input (hidden) -->
    <input 
      ref="fileInput"
      type="file" 
      @change="handleFileSelection"
      accept=".pdf"
      class="hidden"
    />
    
    <!-- Upload progress overlay -->
    <div v-if="isUploading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl w-80">
        <h3 class="text-gray-800 font-semibold mb-4">Uploading document...</h3>
        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
          <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" :style="{ width: `${uploadProgress}%` }"></div>
        </div>
        <p class="text-gray-600 text-sm text-center">{{ Math.round(uploadProgress) }}% complete</p>
      </div>
    </div>
    
    <!-- Deleting document overlay -->
    <div v-if="isDeleting" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl w-80">
        <h3 class="text-gray-800 font-semibold mb-4">Deleting document...</h3>
        <div class="flex justify-center">
          <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
      </div>
    </div>
    
    <!-- Colored banner -->
    <div class="flex w-full overflow-hidden shadow-md">
      <div class="w-1/4 h-1.5 bg-blue-600 animate-pulse" style="animation-delay: 0.2s;"></div>
      <div class="w-1/4 h-1.5 bg-green-500 animate-pulse" style="animation-delay: 0.4s;"></div>
      <div class="w-1/4 h-1.5 bg-amber-500 animate-pulse" style="animation-delay: 0.6s;"></div>
      <div class="w-1/4 h-1.5 bg-red-500 animate-pulse" style="animation-delay: 0.8s;"></div>
    </div>

    <!-- MOBILE CARD LAYOUT -->
    <div class="sm:hidden p-2 space-y-4">
      <div v-for="app in applications" :key="app.id" 
        class="bg-white rounded-xl shadow border border-gray-100 p-4 flex flex-col gap-2 cursor-pointer hover:bg-gray-50 transition"
        @click="viewPdf(app)">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#2563eb"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h168q13-36 43.5-58t68.5-22q38 0 68.5 22t43.5 58h168q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm80-80h280v-80H280v80Zm0-160h400v-80H280v80Zm0-160h400v-80H280v80Zm200-190q13 0 21.5-8.5T510-820q0-13-8.5-21.5T480-850q-13 0-21.5 8.5T450-820q0 13 8.5 21.5T480-790ZM200-200v-560 560Z"/></svg>
            <div class="text-base font-semibold text-gray-800">{{ formTypeToName(app.form_type) }}</div>
          </div>
          <span :class="`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${getStatusColor(app.status)}`">
            {{ app.status }}
          </span>
        </div>
        <div class="flex flex-wrap gap-2 text-xs text-gray-600">
          <span v-if="isAdmin"><span class="font-medium">Org:</span> {{ app.user.name }}</span>
          <span><span class="font-medium">Submitted:</span> {{ formatDate(app.created_at) }}</span>
        </div>
        <div class="flex flex-wrap gap-2 text-xs mt-1">
          <span v-if="app.signed_document_path" class="text-green-600 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Document uploaded
          </span>
          <span v-else class="text-gray-500 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-8-3a1 1 0 100 2 1 1 0 000-2zm2 7a1 1 0 10-2 0v-4a1 1 0 112 0v4z" clip-rule="evenodd"/>
            </svg>
            No document
          </span>
        </div>
        <div class="flex gap-2 mt-2 flex-wrap">
          <button
            @click.stop="toggleDropdown(app, $event)"
            :aria-label="'Actions for ' + formTypeToName(app.form_type)"
            class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-200 transition group"
            :data-dropdown-trigger="app.id"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="#1f2937">
              <circle cx="10" cy="4" r="2.2"/>
              <circle cx="10" cy="10" r="2.2"/>
              <circle cx="10" cy="16" r="2.2"/>
            </svg>
            <span class="absolute left-1/2 -bottom-8 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
              Actions
            </span>
          </button>
        </div>
        <!-- MOBILE INLINE DROPDOWN -->
        <div v-if="activeMobileDropdownId === app.id" class="mobile-dropdown-menu mt-2 bg-gray-50 border border-gray-200 rounded-lg shadow p-3 flex flex-col gap-2 z-10" @click.stop>
          <button v-if="isAdmin" @click="activeMobileDropdownId = null; handleAction(app, 'updateStatus')" class="w-full text-left px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3-9a1 1 0 10-2 0v4a1 1 0 102 0V9z" clip-rule="evenodd" />
              <path d="M10 6a1 1 0 100 2 1 1 0 000-2z" />
            </svg>
            Update Status
          </button>
          <button v-if="!app.signed_document_path" @click="activeMobileDropdownId = null; triggerFileUpload(app.id)" class="w-full text-left px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            Upload Document
          </button>
          <button v-if="app.signed_document_path" @click="activeMobileDropdownId = null; deleteDocument(app.id)" class="w-full text-left px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zm3 8a1 1 0 11-2 0 1 1 0 012 0zm-8 2a1 1 0 100 2h10a1 1 0 100-2H4z" clip-rule="evenodd" />
            </svg>
            Delete Document
          </button>
          <a v-if="app.signed_document_path" :href="`/applications/${app.id}/view-document`" target="_blank" @click="activeMobileDropdownId = null" class="w-full text-left px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" viewBox="0 0 20 20" fill="currentColor">
              <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2h-1.528A6 6 0 004 9.528V4z" />
              <path fill-rule="evenodd" d="M8 10a4 4 0 00-3.446 6.032l-1.261 1.26a1 1 0 101.415 1.415l1.261-1.261A4 4 0 006 10z" clip-rule="evenodd" />
            </svg>
            View Document
          </a>
          <Link v-if="isAdmin || (!isAdmin && app.status !== 'Approved')" :href="`/applications/${app.id}/edit`" @click="activeMobileDropdownId = null" class="w-full text-left px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
            </svg>
            Edit Application
          </Link>
          <a :href="getPdfRoute(app)" @click="activeMobileDropdownId = null" class="w-full text-left px-2 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            Download PDF
          </a>
          <button v-if="isAdmin || (!isAdmin && app.status !== 'Approved')" @click="activeMobileDropdownId = null; handleAction(app, 'delete')" class="w-full text-left px-2 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 transition duration-200 border-t border-gray-100 mt-1 pt-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            Delete Application
          </button>
        </div>
      </div>
    </div>

    <!-- DESKTOP STACKED LIST LAYOUT -->
    <div class="hidden sm:block p-4">
      <div
        v-for="app in applications"
        :key="app.id"
        class="bg-white rounded-xl shadow border border-gray-100 mb-4 flex flex-col md:flex-row md:items-center md:justify-between hover:shadow-lg transition cursor-pointer hover:bg-gray-50"
        @click="viewPdf(app)"
      >
        <div class="flex items-center gap-4 p-5 flex-1 min-w-0">
          <div class="flex-shrink-0">
            <div class="bg-blue-100 text-blue-600 rounded-full p-3">
              <svg xmlns="http://www.w3.org/2000/svg" height="28px" viewBox="0 -960 960 960" width="28px" fill="#2563eb"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h168q13-36 43.5-58t68.5-22q38 0 68.5 22t43.5 58h168q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm80-80h280v-80H280v80Zm0-160h400v-80H280v80Zm0-160h400v-80H280v80Zm200-190q13 0 21.5-8.5T510-820q0-13-8.5-21.5T480-850q-13 0-21.5 8.5T450-820q0 13 8.5 21.5T480-790ZM200-200v-560 560Z"/></svg>
            </div>
          </div>
          <div class="min-w-0 flex-1">
            <div class="font-medium text-base text-gray-900 truncate">
              {{ formTypeToName(app.form_type) }}
              <span v-if="isAdmin" class="inline-flex items-center">
                <svg class="mx-1" width="10" height="10" viewBox="0 0 10 10" fill="#374151" xmlns="http://www.w3.org/2000/svg" style="display:inline"><polygon points="0,0 10,5 0,10"/></svg>
                {{ app.user.name }}
              </span>
            </div>
            <div class="text-sm text-gray-600 truncate">{{ app.form_type }}</div>
            <div class="flex flex-wrap gap-2 text-xs text-gray-500 mt-1">
              <span>Submitted: {{ formatDate(app.created_at) }}</span>
              <span v-if="app.status">&bull; <span :class="`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${getStatusColor(app.status)}`">{{ app.status }}</span></span>
              <span v-if="app.signed_document_path" class="text-green-600 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Document uploaded
              </span>
              <span v-else class="text-gray-500 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-8-3a1 1 0 100 2 1 1 0 000-2zm2 7a1 1 0 10-2 0v-4a1 1 0 112 0v4z" clip-rule="evenodd"/>
                </svg>
                No document
              </span>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2 p-5 pt-0 md:pt-5 md:pl-0 md:pr-6 md:flex-col md:items-end">
          <button
            @click.stop="toggleDropdown(app, $event)"
            :aria-label="'Actions for ' + formTypeToName(app.form_type)"
            class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-200 transition group"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="#1f2937">
              <circle cx="10" cy="4" r="2.2"/>
              <circle cx="10" cy="10" r="2.2"/>
              <circle cx="10" cy="16" r="2.2"/>
            </svg>
            <span class="absolute left-1/2 -bottom-8 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
              Actions
            </span>
          </button>
        </div>
      </div>
    </div>
    <!-- Render the dropdown only once, outside the table -->
    <Teleport to="body">
      <div 
        ref="dropdownRef"
        v-if="activeDropdownApp"
        class="fixed z-50 bg-white rounded-lg shadow-lg border border-gray-200 py-1 w-48 max-w-xs w-full sm:w-64"
        :style="{ top: `${dropdownPosition.top}px`, left: `${dropdownPosition.left}px`, visibility: activeDropdownApp ? 'visible' : 'hidden' }"
        @click.stop
      >
        <!-- Admin-only Status Update Option -->
        <button 
          v-if="isAdmin"
          @click="handleAction(activeDropdownApp, 'updateStatus')"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3-9a1 1 0 10-2 0v4a1 1 0 102 0V9z" clip-rule="evenodd" />
            <path d="M10 6a1 1 0 100 2 1 1 0 000-2z" />
          </svg>
          Update Status
        </button>
        <!-- Upload document option -->
        <button
          v-if="!activeDropdownApp.signed_document_path"
          @click="triggerFileUpload(activeDropdownApp.id)"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
          </svg>
          Upload Document
        </button>
        <!-- Delete document option (only if signed_document_path exists) -->
        <button 
          v-if="activeDropdownApp.signed_document_path"
          @click="deleteDocument(activeDropdownApp.id)"
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-600" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zm3 8a1 1 0 11-2 0 1 1 0 012 0zm-8 2a1 1 0 100 2h10a1 1 0 100-2H4z" clip-rule="evenodd" />
          </svg>
          Delete Document
        </button>
        <!-- View signed document option (only if signed_document_path exists) -->
        <a 
          v-if="activeDropdownApp.signed_document_path"
          :href="`/applications/${activeDropdownApp.id}/view-document`" 
          target="_blank" 
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" viewBox="0 0 20 20" fill="currentColor">
            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2h-1.528A6 6 0 004 9.528V4z" />
            <path fill-rule="evenodd" d="M8 10a4 4 0 00-3.446 6.032l-1.261 1.26a1 1 0 101.415 1.415l1.261-1.261A4 4 0 006 10z" clip-rule="evenodd" />
          </svg>
          View Document
        </a>
        <!-- Edit Application -->
        <Link 
          v-if="isAdmin || (!isAdmin && activeDropdownApp.status !== 'Approved')"
          :href="`/applications/${activeDropdownApp.id}/edit`" 
          class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
          </svg>
          Edit Application
        </Link>
        <!-- Download PDF -->
        <a 
          :href="getPdfRoute(activeDropdownApp)" 
         class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 flex items-center gap-2 transition duration-200"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
          Download PDF
        </a>
        <!-- Delete Application -->
        <button 
          v-if="isAdmin || (!isAdmin && activeDropdownApp.status !== 'Approved')"
          @click="handleAction(activeDropdownApp, 'delete')" 
          class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 transition duration-200 border-t border-gray-100 mt-1 pt-1"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          Delete Application
        </button>
      </div>
    </Teleport>
  </div>
</template>