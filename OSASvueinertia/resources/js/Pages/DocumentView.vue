<template>
  <SidebarLayout :is-admin="$page.props.auth.user.role === 'admin'">
    <div class="flex flex-col lg:flex-row h-full bg-white -m-4 sm:-m-6 lg:-m-6">
      <!-- PDF Viewer Section - No padding, flush to edges -->
      <div class="flex-1 flex flex-col h-full order-2 lg:order-1">
        <!-- PDF Iframe Container -->
        <div class="flex-1 relative bg-gray-100">
          <iframe
            v-if="application"
            :src="`/applications/${application.id}/view-document`"
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

      <!-- Right Panel -->
      <div class="w-full lg:w-80 bg-white flex flex-col h-auto lg:h-full order-1 lg:order-2 border-b lg:border-b-0 overflow-hidden">
        <!-- Panel Header -->
        <div class="p-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Document Details</h2>
            <Link 
              :href="backUrl" 
              class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Back
            </Link>
          </div>
        </div>

        <!-- Panel Content -->
        <div class="flex-1 overflow-y-auto p-4 space-y-6 max-h-96 lg:max-h-none">
          <!-- Basic Information -->
          <div>
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
          <div>
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

          <!-- Update Status Section (Admin Only) -->
          <div v-if="$page.props.auth.user.role === 'admin'">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Update Status</h3>
            <div class="space-y-3">
              <select 
                v-model="selectedStatus" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
              >
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="disapproved">Disapproved</option>
              </select>
              <button
                @click="updateStatus"
                :disabled="isUpdatingStatus || selectedStatus === application?.status"
                class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 relative overflow-hidden group"
              >
                <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                <svg v-if="isUpdatingStatus" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isUpdatingStatus ? 'Updating...' : 'Update Status' }}
              </button>
            </div>
          </div>

          <!-- Feedback Section -->
          <div>
            <h3 class="text-sm font-medium text-gray-900 mb-3">Feedback</h3>
            <div class="space-y-3">
              <textarea
                v-model="feedbackText"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm resize-none"
                placeholder="Enter feedback for this submission..."
              ></textarea>
              <button
                @click="saveFeedback"
                :disabled="isSavingFeedback || !feedbackText.trim()"
                class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 text-sm font-medium text-white rounded-xl shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 relative overflow-hidden group"
              >
                <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                <svg v-if="isSavingFeedback" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isSavingFeedback ? 'Saving...' : 'Save Feedback' }}
              </button>
            </div>
            
            <!-- Existing Feedback -->
            <div v-if="application?.feedback" class="mt-4 p-3 bg-gray-50 rounded-md">
              <h4 class="text-xs font-medium text-gray-700 mb-1">Current Feedback:</h4>
              <p class="text-sm text-gray-900">{{ application.feedback }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div>
            <h3 class="text-sm font-medium text-gray-900 mb-3">Actions</h3>
            <div class="space-y-2">
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
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SidebarLayout from '@/Components/Layout/Sidebar/SidebarLayout.vue'

const props = defineProps({
  application: Object,
  backUrl: {
    type: String,
    default: '/dashboard'
  }
})

// State
const documentLoading = ref(true)
const documentError = ref(null)
const selectedStatus = ref(props.application?.status || 'pending')
const isUpdatingStatus = ref(false)
const feedbackText = ref(props.application?.feedback || '')
const isSavingFeedback = ref(false)

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
  if (props.application) {
    window.open(`/applications/${props.application.id}/view-document`, '_blank')
  }
}

const downloadDocument = () => {
  if (props.application) {
    window.open(`/applications/${props.application.id}/view-document?download=1`, '_blank')
  }
}

const updateStatus = async () => {
  if (!props.application || isUpdatingStatus.value) return
  
  isUpdatingStatus.value = true
  
  try {
    await router.post(`/admin/applications/${props.application.id}/update-status`, {
      status: selectedStatus.value
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        // Update local state
        props.application.status = selectedStatus.value
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

const saveFeedback = async () => {
  if (!props.application || isSavingFeedback.value || !feedbackText.value.trim()) return
  
  isSavingFeedback.value = true
  
  try {
    await router.post(`/admin/applications/${props.application.id}/feedback`, {
      feedback: feedbackText.value.trim()
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        // Update local state
        props.application.feedback = feedbackText.value.trim()
      },
      onError: (errors) => {
        console.error('Failed to save feedback:', errors)
      }
    })
  } catch (error) {
    console.error('Error saving feedback:', error)
  } finally {
    isSavingFeedback.value = false
  }
}

const getStatusColor = (status) => {
  switch(status?.toLowerCase()) {
    case 'approved':
      return 'bg-green-100 text-green-800'
    case 'pending':
      return 'bg-amber-100 text-amber-800'
    case 'disapproved':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
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

onMounted(() => {
  // Set initial loading state
  documentLoading.value = true
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
</style>
