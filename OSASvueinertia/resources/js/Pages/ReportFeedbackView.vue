<template>
  <SidebarLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
      <!-- Header -->
      <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-4">
              <button
                @click="goBack"
                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Reports
              </button>
              <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                  Report Feedback
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ reportData?.report_type }} Report - {{ application?.organization_name }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
          <!-- Report Info -->
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Report Details</h3>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Organization</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ application?.organization_name }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Report Type</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ getReportTypeDisplay(reportData?.report_type) }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Activity Page</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">Activity {{ reportData?.activity_page_number }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd>
                      <span :class="getStatusClasses(reportData?.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                        {{ reportData?.status }}
                      </span>
                    </dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Submitted</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(reportData?.submitted_at) }}</dd>
                  </div>
                </dl>
              </div>
              
              <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">File Information</h3>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Original Filename</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ reportData?.original_filename }}</dd>
                  </div>
                  <div v-if="reportData?.file_size">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">File Size</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ formatFileSize(reportData.file_size) }}</dd>
                  </div>
                  <div v-if="reportData?.reviewed_at">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Reviewed</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(reportData.reviewed_at) }}</dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>

          <!-- Feedback Section -->
          <div class="p-6">
            <div v-if="isAdmin" class="space-y-6">
              <!-- Admin: Status Update & Feedback Section -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Update Status & Feedback</h3>
                <div class="space-y-4">
                  <!-- Status Selection -->
                  <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Report Status
                    </label>
                    <select
                      id="status"
                      v-model="selectedStatus"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                    >
                      <option value="Pending">Pending</option>
                      <option value="Approved">Approved</option>
                      <option value="Disapproved">Disapproved</option>
                    </select>
                  </div>

                  <!-- Feedback Input -->
                  <div>
                    <label for="feedback" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Feedback <span class="text-xs text-gray-500 dark:text-gray-400">(Optional)</span>
                    </label>
                    <textarea
                      id="feedback"
                      v-model="feedbackText"
                      rows="4"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                      :placeholder="selectedStatus === 'Approved' ? 'Goodjob! thank you for your submission. Keep it up.' : 'Enter feedback to the organization...'"
                    ></textarea>
                  </div>

                  <!-- Update Button -->
                  <button
                    @click="updateStatus"
                    :disabled="isUpdatingStatus || (selectedStatus === reportData?.status && feedbackText.trim() === (reportData?.feedback || ''))"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                  >
                    <svg v-if="isUpdatingStatus" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ isUpdatingStatus ? 'Updating...' : 'Update Status' }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- User: Feedback Display Section -->
            <div v-if="!isAdmin">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Feedback from Admin</h3>
              <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap" :class="{ 'italic text-gray-500 dark:text-gray-400': !reportData?.feedback && reportData?.status?.toLowerCase() !== 'approved' }">{{ getDisplayFeedback(reportData) }}</p>
              </div>
            </div>

            <!-- Admin: Existing Feedback Display -->
            <div v-if="isAdmin && reportData?.feedback" class="mt-6">
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Feedback</h3>
              <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ reportData.feedback }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Components/Layout/Sidebar/SidebarLayout.vue';

const props = defineProps({
  application: Object,
  report: Object,
  backUrl: {
    type: String,
    default: '/applications'
  },
  isAdmin: Boolean,
})

// Make report reactive so updates are reflected
const reportData = reactive({ ...props.report })

// Reactive data
const selectedStatus = ref('Pending')
const feedbackText = ref(props.report?.feedback || '')
const isUpdatingStatus = ref(false)

// Helper function to normalize status to proper format
const normalizeStatus = (status) => {
  if (!status) return 'Pending'
  const statusLower = status.toLowerCase()
  if (statusLower === 'pending') return 'Pending'
  if (statusLower === 'approved') return 'Approved'
  if (statusLower === 'disapproved' || statusLower === 'rejected') return 'Disapproved'
  return 'Pending'
}

// Computed properties
const hasChanges = computed(() => {
  return normalizeStatus(selectedStatus.value) !== normalizeStatus(reportData.status) || 
         feedbackText.value.trim() !== (reportData.feedback || '')
})

// Methods
const goBack = () => {
  router.visit(props.backUrl, { preserveState: false, preserveScroll: true })
}

const getDefaultFeedback = (status) => {
  if (status?.toLowerCase() === 'approved') {
    return 'Goodjob! thank you for your submission. Keep it up.'
  }
  return ''
}

const getDisplayFeedback = (report) => {
  if (report?.feedback) {
    return report.feedback
  }
  if (report?.status?.toLowerCase() === 'approved') {
    return getDefaultFeedback(report.status)
  }
  return 'No feedback provided yet.'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatFileSize = (bytes) => {
  if (!bytes) return 'N/A'
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getStatusClasses = (status) => {
  switch(status?.toLowerCase()) {
    case 'approved':
      return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
    case 'pending':
      return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300'
    case 'disapproved':
    case 'rejected':
      return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
  }
}

const getReportTypeDisplay = (reportType) => {
  switch(reportType) {
    case 'LSPU-OSAS-SF-FINANCIAL':
      return 'Financial Report'
    case 'LSPU-OSAS-SF-NARRATIVE':
      return 'Narrative Report'
    case 'LSPU-OSAS-SF-ACCOMPLISHMENT':
      return 'Accomplishment Report'
    case 'LSPU-OSAS-SF-EVAL':
      return 'Evaluation Summary'
    case 'LSPU-OSAS-SF-009':
      return 'Activity Attendance Sheet'
    default:
      return reportType
  }
}

const updateStatus = async () => {
  if (!hasChanges.value) return

  isUpdatingStatus.value = true

  try {
    await router.put(`/applications/${props.application.id}/reports/${reportData.id}/status`, {
      status: selectedStatus.value,
      feedback: feedbackText.value.trim()
    }, {
      onSuccess: (page) => {
        // Update the local report data to reflect the changes
        reportData.status = selectedStatus.value
        reportData.feedback = feedbackText.value.trim()
        reportData.reviewed_at = new Date().toISOString()
        
        console.log('Report status updated successfully')
      },
      onError: (errors) => {
        console.error('Error updating report status:', errors)
        console.error('Error details:', JSON.stringify(errors, null, 2))
        
        // Show user-friendly error message
        const errorMessage = errors.message || errors.error || 
                            (typeof errors === 'object' ? Object.values(errors)[0] : 'Failed to update status')
        
        console.error('User-friendly error:', errorMessage)
      },
      onFinish: () => {
        isUpdatingStatus.value = false
      }
    })
  } catch (error) {
    console.error('Error updating report status:', error)
    isUpdatingStatus.value = false
  }
}

// Initialize form data
onMounted(() => {
  selectedStatus.value = normalizeStatus(reportData.status)
  feedbackText.value = reportData.feedback || ''
})
</script>