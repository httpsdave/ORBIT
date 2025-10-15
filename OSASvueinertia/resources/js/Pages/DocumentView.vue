<template>
  <SidebarLayout :is-admin="props.isAdmin">
    <div class="flex flex-col lg:flex-row h-full min-h-screen bg-white -m-4 sm:-m-6 lg:-m-6 relative overflow-hidden">
      <!-- PDF Viewer Section - No padding, flush to edges -->
      <div class="flex-1 flex flex-col h-full order-2 lg:order-1 min-h-0">
        <!-- PDF Iframe Container -->
        <div class="flex-1 relative bg-gray-100 min-h-[50vh] sm:min-h-[60vh] lg:min-h-0">
          <iframe
            v-if="application && documentUrl"
            :src="documentUrl"
            class="w-full h-full border-0 bg-white"
            style="min-height: calc(100vh - 4rem);"
            allowfullscreen
            @load="onDocumentLoad"
            @error="onDocumentError"
          ></iframe>
          
          <!-- Loading overlay -->
          <div v-if="documentLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
            <div class="text-center">
              <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="text-sm text-gray-600">Loading document...</p>
            </div>
          </div>
          
          <!-- Error state -->
          <div v-if="documentError" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
            <div class="text-center">
              <svg class="h-12 w-12 text-red-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <p class="text-sm text-gray-600 mb-4">{{ documentError }}</p>
              <button
                @click="openInNewWindow"
                class="inline-flex items-center px-4 py-2 bg-blue-500 text-sm font-medium text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
              >
                Open in New Window
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Separator Bar - Hidden on mobile, visible on desktop -->
      <div class="hidden lg:block w-1 bg-gray-300 flex-shrink-0 shadow-sm"></div>

<!-- Right Panel - GPU accelerated for smooth animations -->
<div 
  class="bg-white flex flex-col overflow-hidden will-change-transform"
  :class="{
    // Mobile - optimized transforms
    'fixed top-0 right-0 z-50 h-screen w-80 sm:w-96 shadow-lg': isMobile,
    // Desktop - always visible
    'w-80 h-full order-1 lg:order-2 border-b lg:border-b-0 lg:relative lg:flex': !isMobile
  }"
  :style="isMobile ? {
    transform: showInfoPanel ? 'translate3d(0, 0, 0)' : 'translate3d(100%, 0, 0)',
    transition: 'transform 0.25s cubic-bezier(0.4, 0.0, 0.2, 1)'
  } : {}"
>
        <!-- Panel Header -->
        <div class="p-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">Document Details</h2>
              <p v-if="props.viewType === 'unsigned'" class="text-xs text-gray-500 mt-0.5">Viewing: Original Submission</p>
              <p v-else-if="application?.signed_document_path" class="text-xs text-gray-500 mt-0.5">Viewing: Signed Document</p>
            </div>
            <div class="flex items-center space-x-2">
              <!-- Close button for mobile overlay -->
<button
  v-if="isMobile && showInfoPanel"
  @click="toggleInfoPanel"
  class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition-all duration-200 rounded-full hover:bg-gray-100 transform hover:rotate-90"
  aria-label="Close panel"
>
  <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
    <path d="M18 6L6 18M6 6l12 12" />
  </svg>
</button>
              <!-- Info button for all <lg screens -->
                <button
  v-if="!showInfoPanel"
  @click="toggleInfoPanel"
  class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition rounded-full hover:bg-gray-100 lg:hidden"
  aria-label="Show information panel"
>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="12" y1="16" x2="12" y2="12" />
                  <line x1="12" y1="8" x2="12.01" y2="8" />
                </svg>
              </button>
              <!-- Back button -->
              <button
                @click="goBackToApplications"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-800 focus:outline-none focus:text-blue-600 transition"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M15 19l-7-7 7-7" />
                </svg>
                Back
              </button>
            </div>
          </div>
        </div>

        <!-- Panel Content -->
        <!-- Panel Content with staggered animation -->
<div class="flex-1 overflow-y-auto p-4 space-y-6" style="max-height: calc(100vh - 4rem);">
  <transition-group
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 translate-x-4"
    enter-to-class="opacity-100 translate-x-0"
    appear
  >
          <!-- Basic Information -->
          <div key="basic-info">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Basic Information</h3>
            <div class="space-y-2 text-sm">
              <div>
                <span class="font-medium text-gray-600">Form Type:</span>
                <span class="ml-2 text-gray-900">{{ formTypeToName(application?.form_type) }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-600">Organization:</span>
                <span class="ml-2 text-gray-900">{{ application?.organization_name || 'N/A' }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-600">Submitted:</span>
                <span class="ml-2 text-gray-900">{{ formatDate(application?.created_at) }}</span>
              </div>
              <div v-if="application?.signed_document_path">
                <span class="font-medium text-gray-600">Document:</span>
                <span 
                  class="ml-2 text-gray-900 truncate block max-w-48 cursor-help" 
                  :title="getFileName(application.signed_document_path)"
                >
                  {{ getFileName(application.signed_document_path) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Status Section -->
          <div key="status">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Status</h3>
            <div class="flex items-center space-x-2">
              <span 
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  getStatusColor(application?.status)
                ]"
              >
                {{ application?.status || 'Unknown' }}
              </span>
            </div>
          </div>

          <!-- Admin: Status Update & Feedback Section -->
          <div key="admin-status-update" v-if="props.isAdmin">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Update Status & Feedback</h3>
            <div class="space-y-3">
              <div class="grid grid-cols-3 gap-2 min-w-0">
                <button
                  @click="selectedStatus = 'Pending'"
                  :class="[
                    'py-2 px-2 rounded-lg border text-xs font-medium transition-all duration-200 w-full min-w-0 truncate',
                    selectedStatus === 'Pending'
                      ? 'bg-amber-100 border-amber-400 text-amber-800 ring-2 ring-amber-200'
                      : 'bg-white border-gray-200 text-gray-600 hover:bg-amber-50'
                  ]"
                >
                  Pending
                </button>
                <button
                  @click="selectedStatus = 'Approved'"
                  :class="[
                    'py-2 px-2 rounded-lg border text-xs font-medium transition-all duration-200 w-full min-w-0 truncate',
                    selectedStatus === 'Approved'
                      ? 'bg-green-100 border-green-400 text-green-800 ring-2 ring-green-200'
                      : 'bg-white border-gray-200 text-gray-600 hover:bg-green-50'
                  ]"
                >
                  Approved
                </button>
                <button
                  @click="selectedStatus = 'Disapproved'"
                  :class="[
                    'py-2 px-2 rounded-lg border text-xs font-medium transition-all duration-200 w-full min-w-0 truncate',
                    selectedStatus === 'Disapproved'
                      ? 'bg-red-100 border-red-400 text-red-800 ring-2 ring-red-200'
                      : 'bg-white border-gray-200 text-gray-600 hover:bg-red-50'
                  ]"
                >
                  Rejected
                </button>
              </div>
              <textarea
                v-model="feedbackText"
                rows="4"
                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition duration-200 p-3 text-sm resize-none"
                :placeholder="selectedStatus === 'Approved' ? 'Goodjob! thank you for your submission. Keep it up.' : 'Enter feedback to the organization...'"
              ></textarea>
              <button
                @click="updateStatus"
                :disabled="isUpdatingStatus || (selectedStatus === application?.status && feedbackText.trim() === (application?.feedback || ''))"
                class="w-full inline-flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium rounded-lg transition duration-200 text-sm disabled:opacity-70"
              >
                <svg
                  v-if="isUpdatingStatus"
                  class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  />
                </svg>
                <span>{{ isUpdatingStatus ? 'Updating...' : 'Update Status' }}</span>
              </button>
            </div>
          </div>

          <!-- User: Feedback Display Section -->
          <div key="user-feedback-display" v-if="!props.isAdmin">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Feedback from Admin</h3>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-700">
              {{ application?.feedback || 'No feedback provided.' }}
            </div>
          </div>

          <!-- Admin: Existing Feedback Display -->
          <div key="admin-feedback-display" v-if="props.isAdmin && application?.feedback">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Current Feedback</h3>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-700">
              {{ application.feedback }}
            </div>
          </div>

          <!-- Actions -->
          <div key="actions">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Actions</h3>
            <div class="space-y-2">
              <!-- Toggle between signed and unsigned view if signed document exists -->
              <button
                v-if="application?.signed_document_path"
                @click="toggleDocumentView"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-100 text-sm font-medium text-purple-700 rounded-md hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                {{ props.viewType === 'unsigned' ? 'View Signed Document' : 'View Original Submission' }}
              </button>
              <button
                @click="openInNewWindow"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Open in New Window
              </button>
              <button
                @click="downloadDocument"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-100 text-sm font-medium text-blue-700 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download
              </button>
            </div>
          </div>
        </transition-group>
        </div>
        
      </div>

      <!-- Floating Info Button for Mobile -->
<transition
  enter-active-class="transition-all duration-300 ease-out"
  leave-active-class="transition-all duration-200 ease-in"
  enter-from-class="opacity-0 scale-75 translate-y-4"
  enter-to-class="opacity-100 scale-100 translate-y-0"
  leave-from-class="opacity-100 scale-100 translate-y-0"
  leave-to-class="opacity-0 scale-75 translate-y-4"
>
  <button
    v-if="isMobile && !showInfoPanel"
    @click="toggleInfoPanel"
    class="fixed bottom-6 right-6 z-40 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center focus:outline-none focus:ring-4 focus:ring-blue-300 focus:ring-opacity-50 transform hover:scale-105 active:scale-95"
    aria-label="Show document information"
  >
    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10" />
      <path d="M12 16v-4M12 8h.01" />
    </svg>
  </button>
</transition>

      <!-- Mobile Overlay Backdrop - Optimized for performance -->
<div
  v-if="isMobile && showInfoPanel"
  @click="toggleInfoPanel"
  class="fixed inset-0 bg-black z-40 transition-opacity duration-200 ease-out"
  :class="showInfoPanel ? 'bg-opacity-40' : 'bg-opacity-0'"
  aria-hidden="true"
></div>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Components/Layout/Sidebar/SidebarLayout.vue';

const props = defineProps({
  application: Object,
  backUrl: {
    type: String,
    default: '/dashboard'
  },
  isAdmin: Boolean,
  viewType: {
    type: String,
    default: 'signed' // 'signed' or 'unsigned'
  }
})



// State
const documentLoading = ref(true)
const documentError = ref(null)
const selectedStatus = ref(props.application?.status || '')
const isUpdatingStatus = ref(false)
const feedbackText = ref(props.application?.feedback || '')
const isSavingFeedback = ref(false)
const showInfoPanel = ref(false)
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)

// Methods
const onDocumentLoad = () => {
  documentLoading.value = false
  documentError.value = null
}

const onDocumentError = () => {
  documentLoading.value = false
  documentError.value = 'Failed to load document. Please try opening in a new window.'
}

const openInNewWindow = () => {
  if (props.application && documentUrl.value) {
    window.open(documentUrl.value, '_blank')
  }
}

const downloadDocument = () => {
  if (props.application && documentUrl.value) {
    const url = documentUrl.value.includes('?') 
      ? `${documentUrl.value}&download=1` 
      : `${documentUrl.value}?download=1`;
    const fileName = props.viewType === 'unsigned' 
      ? `${formTypeToName(props.application.form_type)}_unsigned.pdf`
      : getFileName(props.application.signed_document_path);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', fileName || 'document.pdf');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}

const updateStatus = async () => {
  if (!props.application || isUpdatingStatus.value) return
  
  isUpdatingStatus.value = true
  
  try {
    await router.post(`/admin/applications/${props.application.id}/update-status`, {
      status: selectedStatus.value,
      feedback: feedbackText.value
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        // Update local state
        props.application.status = selectedStatus.value
        if (feedbackText.value.trim()) {
          props.application.feedback = feedbackText.value.trim()
        }
      },
      onError: (errors) => {
        console.error('Failed to update status:', errors)
      }
    })
  } catch (error) {
    console.error('Error updating status:', error)
  } finally {
    isUpdatingStatus.value = false
  }
}

const getStatusColor = (status) => {
  switch(status?.toLowerCase()) {
    case 'approved':
      return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
    case 'pending':
      return 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300'
    case 'disapproved':
      return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'
    default:
      return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'
  }
}

const formTypeToName = (formType) => {
  const typeMap = {
    'LSPU-OSAS-SF-001': 'Organization Recognition',
    'LSPU-OSAS-SF-002': 'Renewal Application',
    'LSPU-OSAS-SF-003': 'Commitment Form',
    'LSPU-OSAS-SF-004': 'Plan of Activities',
    'LSPU-OSAS-SF-005': 'Members List',
    'LSPU-OSAS-SF-006': 'Certification Form',
    'LSPU-OSAS-SF-007': 'Officers List',
    'LSPU-OSAS-SF-009': 'Student Activity Attendance Sheet',
    'LSPU-OSAS-SF-EVAL': 'Evaluation Summary',
    'LSPU-OSAS-SF-ACCOMPLISHMENT': 'Accomplishment Report',
    'LSPU-OSAS-SF-NARRATIVE': 'Narrative Report',
    'LSPU-OSAS-SF-BYLAWS': 'Constitution & By-Laws',
    'LSPU-OSAS-SF-FINANCIAL': 'Financial Report',
    'LSPU-ACAD-RL': 'Event Letter'
  }
  return typeMap[formType] || formType
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return 'Invalid Date'
  
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getFileName = (path) => {
  if (!path) return 'N/A'
  return path.split('/').pop() || path
}

// Watch for application changes to update local state
watch(() => props.application, (newApp) => {
  if (newApp) {
    selectedStatus.value = newApp.status || '';
    feedbackText.value = newApp.feedback || '';
  }
}, { immediate: true });

// Computed property to determine if we're on mobile
const isMobile = computed(() => windowWidth.value < 1024)
const isSmallScreen = computed(() => windowWidth.value < 640) // For very small screens

// Get the appropriate document URL based on viewType
const documentUrl = computed(() => {
  if (!props.application) return null;
  
  if (props.viewType === 'unsigned') {
    // Return the original submission URL
    return getUnsignedDocumentUrl();
  } else {
    // Return the signed document URL (default behavior)
    return `/applications/${props.application.id}/view-document`;
  }
});

// Get unsigned document URL (original submission)
const getUnsignedDocumentUrl = () => {
  if (!props.application) return null;
  
  const formType = props.application.form_type;
  const appId = props.application.id;
  
  // Direct-upload forms
  const directUploadTypes = [
    'LSPU-OSAS-SF-ACCOMPLISHMENT',
    'LSPU-OSAS-SF-NARRATIVE',
    'LSPU-OSAS-SF-BYLAWS',
    'LSPU-OSAS-SF-FINANCIAL',
    'LSPU-ACAD-RL',
  ];
  
  if (directUploadTypes.includes(formType)) {
    const reportPath = getReportPath();
    return reportPath ? `/storage/${reportPath}` : null;
  }
  
  // Generated PDF routes
  if (formType === 'LSPU-OSAS-SF-002') {
    return `/applications/${appId}/export-renewal?action=view`;
  } else if (formType === 'LSPU-OSAS-SF-001') {
    return `/applications/${appId}/pdf?action=view`;
  } else if (formType === 'LSPU-OSAS-SF-003') {
    return `/applications/${appId}/export-commitment?action=view`;
  } else if (formType === 'LSPU-OSAS-SF-004') {
    return `/applications/${appId}/export-plan?action=view`;
  } else if (formType === 'LSPU-OSAS-SF-006') {
    return `/applications/${appId}/export-certification?action=view`;
  } else if (formType === 'LSPU-OSAS-SF-005') {
    return `/applications/${appId}/export-members?action=view`;
  } else if (formType === 'LSPU-OSAS-SF-007') {
    return `/applications/${appId}/export-officers?action=view`;
  } else if (formType === 'LSPU-OSAS-SF-009') {
    return `/applications/${appId}/export-attendance?action=view`;
  } else if (formType === 'LSPU-OSAS-SF-EVAL') {
    return `/applications/${appId}/export-evaluation?action=view`;
  } else {
    return `/applications/${appId}/pdf?action=view`;
  }
};

// Get report path for direct-upload forms
const getReportPath = () => {
  if (!props.application) return null;
  
  const app = props.application;
  const formType = app.form_type;
  
  switch(formType) {
    case 'LSPU-OSAS-SF-ACCOMPLISHMENT':
      return app.accomplishment_report_path;
    case 'LSPU-OSAS-SF-NARRATIVE':
      return app.narrative_report_path;
    case 'LSPU-OSAS-SF-BYLAWS':
      return app.bylaws_path;
    case 'LSPU-OSAS-SF-FINANCIAL':
      return app.financial_report_path;
    case 'LSPU-ACAD-RL':
      return app.event_letter_path;
    default:
      return null;
  }
};

// Handle window resize
const handleResize = () => {
  windowWidth.value = window.innerWidth
  // Close info panel when switching to desktop
  if (!isMobile.value) {
    showInfoPanel.value = false
  }
}

// Toggle info panel visibility
const toggleInfoPanel = () => {
  showInfoPanel.value = !showInfoPanel.value
}

// Handle escape key to close panel
const handleKeydown = (event) => {
  if (event.key === 'Escape' && showInfoPanel.value && isMobile.value) {
    showInfoPanel.value = false
  }
}

// Replace the back button handler
const goBackToApplications = () => {
  router.visit('/applications', { preserveState: false, preserveScroll: true });
}

// Toggle between signed and unsigned document view
const toggleDocumentView = () => {
  if (!props.application) return;
  
  const newViewType = props.viewType === 'unsigned' ? 'signed' : 'unsigned';
  const url = newViewType === 'unsigned' 
    ? `/applications/${props.application.id}/document?view=unsigned`
    : `/applications/${props.application.id}/document`;
  
  router.visit(url, { preserveState: false, preserveScroll: true });
}

onMounted(() => {
  // Set initial loading state
  documentLoading.value = true
  
  // Add event listeners
  window.addEventListener('resize', handleResize)
  document.addEventListener('keydown', handleKeydown)
  
  // Set initial window width
  handleResize()
})

onUnmounted(() => {
  // Clean up event listeners
  window.removeEventListener('resize', handleResize)
  document.removeEventListener('keydown', handleKeydown)
})

</script>

<style scoped>
/* Ensure the layout takes full height */
.h-full {
  height: calc(100vh - 4rem); /* Account for header height */
}

/* Custom scrollbar for the right panel */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
@media (max-width: 1023px) {
  .min-h-screen {
    min-height: 100vh;
  }
  
  /* Ensure PDF takes appropriate space on mobile */
  .flex-1 {
    min-height: 50vh;
  }
}

/* Tablet specific adjustments */
@media (min-width: 768px) and (max-width: 1023px) {
  .flex-1 {
    min-height: 60vh;
  }
}
@media (max-width: 640px) {
  .break-words {
    word-break: break-all;
  }
  
  .max-w-48 {
    max-width: 8rem;
  }
}
/* Smooth transitions for panel */
.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0.0, 0.2, 1);
}

/* Ensure panel slides in smoothly */
@media (max-width: 1023px) {
  .right-panel-enter {
    transform: translateX(100%);
  }
  
  .right-panel-enter-active {
    transition: transform 0.3s ease-in-out;
  }
  
  .right-panel-enter-to {
    transform: translateX(0);
  }
  
  .right-panel-leave {
    transform: translateX(0);
  }
  
  .right-panel-leave-active {
    transition: transform 0.3s ease-in-out;
  }
  
  .right-panel-leave-to {
    transform: translateX(100%);
  }
}

/* Floating button pulse animation */
@keyframes pulse-ring {
  0% {
    transform: scale(0.33);
  }
  80%, 100% {
    opacity: 0;
  }
}

.floating-btn::before {
  content: '';
  position: absolute;
  display: block;
  width: 300%;
  height: 300%;
  box-sizing: border-box;
  margin-left: -100%;
  margin-top: -100%;
  border-radius: 50%;
  background-color: currentColor;
  animation: pulse-ring 1.25s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}

/* Ensure proper z-index stacking */
.z-40 {
  z-index: 40;
}

.z-50 {
  z-index: 50;
}

/* Better shadow for mobile panel */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
/* Smooth panel animations */
.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* Ensure full height on mobile */
@media (max-width: 1023px) {
  .h-screen {
    height: 100vh;
    height: 100dvh; /* Use dynamic viewport height if supported */
  }
}

/* Custom scrollbar for better UX */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.8);
}

/* Smooth backdrop transition */
.transition-opacity {
  transition-property: opacity;
  transition-timing-function: cubic-bezier(0.4, 0.0, 0.2, 1);
}
</style>
