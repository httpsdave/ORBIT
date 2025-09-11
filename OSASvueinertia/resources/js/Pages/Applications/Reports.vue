<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBanner from '@/Components/StatusBanner.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({
  application: Object,
  activityCount: Number,
  reports: Array,
  reportsByPageAndType: Object,
  reportTypes: Object
})

const page = usePage()

const uploading = ref(false)
const uploadingFor = ref(null) // Track which report is being uploaded
const fileInputs = ref({}) // Store file input refs
const selectedFiles = ref({}) // Track selected files for each input
const dragStates = ref({}) // Track drag states for each drop zone

// Success/error messages using StatusBanner
const showMessage = ref(false)
const message = ref('')
const messageType = ref('') // 'success' or 'error'

// Confirmation modal state
const showDeleteModal = ref(false)
const deleteTarget = ref(null)

const showMessageWithType = (text, type = 'success') => {
  message.value = text
  messageType.value = type
  showMessage.value = true
  setTimeout(() => {
    showMessage.value = false
    message.value = ''
    messageType.value = ''
  }, 5000)
}

// Check for flash messages from Laravel
onMounted(() => {
  if (page.props.flash?.success) {
    showMessageWithType(page.props.flash.success, 'success')
  }
  if (page.props.flash?.error) {
    showMessageWithType(page.props.flash.error, 'error')
  }
})

// Generate activity pages data
const activityPages = computed(() => {
  const pages = []
  for (let i = 1; i <= props.activityCount; i++) {
    const pageReports = {}
    Object.keys(props.reportTypes).forEach(reportType => {
      const reportKey = `${i}-${reportType}`
      pageReports[reportType] = props.reportsByPageAndType[i]?.[reportType] || null
    })
    
    pages.push({
      pageNumber: i,
      activityData: props.application.activities[i - 1] || null,
      reports: pageReports
    })
  }
  return pages
})

const uploadReport = async (activityPageNumber, reportType) => {
  const fileInputKey = `${activityPageNumber}-${reportType}`
  const file = selectedFiles.value[fileInputKey]
  
  if (!file) {
    showMessageWithType('Please select a file to upload.', 'error')
    return
  }

  // Validate file type (only PDF)
  if (file.type !== 'application/pdf') {
    showMessageWithType('Please upload a PDF document only.', 'error')
    return
  }

  // Validate file size (20MB)
  if (file.size > 20 * 1024 * 1024) {
    showMessageWithType('File size must be less than 20MB.', 'error')
    return
  }

  uploading.value = true
  uploadingFor.value = fileInputKey

  const formData = new FormData()
  formData.append('activity_page_number', activityPageNumber)
  formData.append('report_type', reportType)
  formData.append('report_file', file)

  // Use Inertia router for form submission with proper CSRF handling
  router.post(`/applications/${props.application.id}/reports`, formData, {
    onSuccess: () => {
      // Clear the selected file after successful upload
      selectedFiles.value[fileInputKey] = null
      const fileInput = fileInputs.value[fileInputKey]
      if (fileInput) {
        fileInput.value = ''
      }
    },
    onError: (errors) => {
      console.error('Upload errors:', errors)
      const errorMessage = errors.message || errors.error || Object.values(errors)[0] || 'Upload failed. Please try again.'
      showMessageWithType(errorMessage, 'error')
    },
    onFinish: () => {
      uploading.value = false
      uploadingFor.value = null
    }
  })
}

const deleteReport = async (reportId, activityPageNumber, reportType) => {
  deleteTarget.value = { reportId, activityPageNumber, reportType }
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (!deleteTarget.value) return
  
  const { reportId } = deleteTarget.value
  showDeleteModal.value = false
  
  router.delete(`/applications/${props.application.id}/reports/${reportId}`, {
    onError: (errors) => {
      console.error('Delete errors:', errors)
      const errorMessage = errors.message || errors.error || Object.values(errors)[0] || 'Delete failed. Please try again.'
      showMessageWithType(errorMessage, 'error')
    }
  })
  
  deleteTarget.value = null
}

const cancelDelete = () => {
  showDeleteModal.value = false
  deleteTarget.value = null
}

const downloadReport = (report) => {
  if (report.file_path) {
    window.open(`/applications/${props.application.id}/reports/${report.id}/download`, '_blank')
  }
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'pending':
      return 'bg-gray-100 text-gray-800'
    case 'submitted':
      return 'bg-blue-100 text-blue-800'
    case 'approved':
      return 'bg-green-100 text-green-800'
    case 'rejected':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const goBack = () => {
  router.visit('/applications')
}

// File handling functions
const handleFileSelect = (event, activityPageNumber, reportType) => {
  const file = event.target.files[0]
  const fileInputKey = `${activityPageNumber}-${reportType}`
  
  if (file) {
    if (validateFile(file)) {
      selectedFiles.value[fileInputKey] = file
    } else {
      // Clear the input if file is invalid
      event.target.value = ''
    }
  } else {
    selectedFiles.value[fileInputKey] = null
  }
}

const validateFile = (file) => {
  // Validate file type (only PDF)
  if (file.type !== 'application/pdf') {
    showMessageWithType('Please upload a PDF document only.', 'error')
    return false
  }

  // Validate file size (20MB)
  if (file.size > 20 * 1024 * 1024) {
    showMessageWithType('File size must be less than 20MB.', 'error')
    return false
  }

  return true
}

// Drag and drop functions
const handleDragEnter = (event, activityPageNumber, reportType) => {
  event.preventDefault()
  const fileInputKey = `${activityPageNumber}-${reportType}`
  dragStates.value[fileInputKey] = true
}

const handleDragLeave = (event, activityPageNumber, reportType) => {
  event.preventDefault()
  const fileInputKey = `${activityPageNumber}-${reportType}`
  // Only set to false if leaving the drop zone completely
  if (!event.currentTarget.contains(event.relatedTarget)) {
    dragStates.value[fileInputKey] = false
  }
}

const handleDragOver = (event) => {
  event.preventDefault()
}

const handleDrop = (event, activityPageNumber, reportType) => {
  event.preventDefault()
  const fileInputKey = `${activityPageNumber}-${reportType}`
  dragStates.value[fileInputKey] = false
  
  const files = event.dataTransfer.files
  if (files.length > 0) {
    const file = files[0]
    if (validateFile(file)) {
      selectedFiles.value[fileInputKey] = file
      // Also update the file input
      const fileInput = fileInputs.value[fileInputKey]
      if (fileInput) {
        const dataTransfer = new DataTransfer()
        dataTransfer.items.add(file)
        fileInput.files = dataTransfer.files
      }
    }
  }
}

const removeSelectedFile = (activityPageNumber, reportType) => {
  const fileInputKey = `${activityPageNumber}-${reportType}`
  selectedFiles.value[fileInputKey] = null
  const fileInput = fileInputs.value[fileInputKey]
  if (fileInput) {
    fileInput.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<template>
  <Head title="Activity Reports" />
  
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 w-full">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight mb-1">
            Activity Reports
          </h2>
          <div class="text-sm text-gray-600 dark:text-gray-400">
            <span class="font-medium">{{ application.organization_name }}</span>
            <span class="mx-2">•</span>
            <span>Plan of Activities</span>
            <span class="mx-2">•</span>
            <span>{{ activityCount }} {{ activityCount === 1 ? 'Activity' : 'Activities' }}</span>
          </div>
        </div>
        <button
          @click="goBack"
          class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-gray-300/30 hover:from-gray-400 hover:to-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 active:from-gray-600 active:to-gray-700 transition-all duration-300 relative overflow-hidden group w-full sm:w-auto"
        >
          <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
          </svg>
          Back to Applications
        </button>
      </div>
    </template>

    <!-- Status Banner -->
    <StatusBanner
      :show="showMessage"
      :type="messageType"
      :message="message"
      @close="showMessage = false"
    />

    <!-- Main Content -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Reports Grid -->
        <div class="space-y-8">
          <div
            v-for="page in activityPages"
            :key="page.pageNumber"
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 rounded-xl border border-gray-100 dark:border-gray-700"
          >
            <div class="p-6">
              <!-- Activity Header -->
              <div class="border-b dark:border-gray-700 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                  Activity {{ page.pageNumber }}
                </h2>
                <div v-if="page.activityData" class="text-sm text-gray-600 dark:text-gray-400">
                  <p><strong>Activity:</strong> {{ page.activityData.name || 'Not specified' }}</p>
                  <p><strong>Target Date:</strong> {{ page.activityData.target_date || 'Not specified' }}</p>
                </div>
              </div>

              <!-- Reports Grid for this Activity -->
              <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5 gap-4 lg:gap-6">
                <div
                  v-for="(reportTypeName, reportType) in reportTypes"
                  :key="reportType"
                  class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 lg:p-5 bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900/70 transition-colors duration-200"
                >
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 text-sm lg:text-base truncate pr-2">{{ reportTypeName }}</h3>
                    <span
                      v-if="page.reports[reportType]"
                      :class="[
                        'inline-flex items-center px-2 lg:px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0',
                        getStatusBadgeClass(page.reports[reportType].status)
                      ]"
                    >
                      {{ page.reports[reportType].status }}
                    </span>
                  </div>

                  <!-- If report exists -->
                  <div v-if="page.reports[reportType]" class="space-y-3">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                      <p class="truncate"><strong>File:</strong> {{ page.reports[reportType].original_filename }}</p>
                      <p><strong>Submitted:</strong> {{ new Date(page.reports[reportType].submitted_at).toLocaleDateString() }}</p>
                      <div v-if="page.reports[reportType].feedback" class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded text-yellow-800 dark:text-yellow-200">
                        <p class="break-words"><strong>Feedback:</strong> {{ page.reports[reportType].feedback }}</p>
                      </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-2">
                      <button
                        @click="downloadReport(page.reports[reportType])"
                        class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
                      >
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View
                      </button>
                      <button
                        @click="deleteReport(page.reports[reportType].id, page.pageNumber, reportType)"
                        class="flex-shrink-0 inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-red-500 to-red-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-red-300/30 hover:from-red-400 hover:to-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-red-600 active:to-red-700 transition-all duration-300 relative overflow-hidden group"
                      >
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <!-- If no report exists -->
                  <div v-else class="space-y-3">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                      No report uploaded yet
                    </div>
                    
                    <!-- Drag and Drop Zone -->
                    <div 
                      :class="[
                        'relative border-2 border-dashed rounded-xl p-4 lg:p-6 transition-all duration-200',
                        dragStates[`${page.pageNumber}-${reportType}`] 
                          ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20' 
                          : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'
                      ]"
                      @dragenter="handleDragEnter($event, page.pageNumber, reportType)"
                      @dragleave="handleDragLeave($event, page.pageNumber, reportType)"
                      @dragover="handleDragOver"
                      @drop="handleDrop($event, page.pageNumber, reportType)"
                    >
                      <input
                        :ref="el => fileInputs[`${page.pageNumber}-${reportType}`] = el"
                        type="file"
                        accept=".pdf"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        @change="handleFileSelect($event, page.pageNumber, reportType)"
                      />
                      
                      <!-- Drop zone content when no file is selected -->
                      <div v-if="!selectedFiles[`${page.pageNumber}-${reportType}`]" class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 lg:h-12 w-8 lg:w-12 mx-auto text-gray-400 dark:text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="text-xs lg:text-sm text-gray-600 dark:text-gray-400 mb-1">
                          <span class="font-medium text-blue-600 dark:text-blue-400">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">
                          PDF files only (Max 20MB)
                        </p>
                      </div>
                      
                      <!-- Selected file preview -->
                      <div v-else class="flex items-center justify-between space-x-2">
                        <div class="flex items-center space-x-2 lg:space-x-3 min-w-0 flex-1">
                          <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 lg:h-8 w-6 lg:w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                          </div>
                          <div class="flex-1 min-w-0">
                            <p class="text-xs lg:text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                              {{ selectedFiles[`${page.pageNumber}-${reportType}`].name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                              {{ formatFileSize(selectedFiles[`${page.pageNumber}-${reportType}`].size) }}
                            </p>
                          </div>
                        </div>
                        <button
                          @click="removeSelectedFile(page.pageNumber, reportType)"
                          class="flex-shrink-0 p-1 text-gray-400 hover:text-red-500 transition-colors"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 lg:h-5 w-4 lg:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </div>
                    </div>
                    
                    <!-- Upload button - only show when file is selected -->
                    <button
                      v-if="selectedFiles[`${page.pageNumber}-${reportType}`]"
                      @click="uploadReport(page.pageNumber, reportType)"
                      :disabled="uploading && uploadingFor === `${page.pageNumber}-${reportType}`"
                      class="upload-button w-full inline-flex items-center justify-center px-3 lg:px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-xs lg:text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-green-600 active:to-green-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none transition-all duration-300 relative overflow-hidden group"
                    >
                      <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                      <svg v-if="uploading && uploadingFor === `${page.pageNumber}-${reportType}`" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                      </svg>
                      <span class="truncate">{{ uploading && uploadingFor === `${page.pageNumber}-${reportType}` ? 'Uploading...' : 'Upload Report' }}</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="activityCount === 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-100 dark:border-gray-700">
          <div class="p-8 text-center">
            <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full p-3 w-16 h-16 mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No activities found</h3>
            <p class="text-gray-500 dark:text-gray-400">This Plan of Activities submission doesn't have any activities yet.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal for Delete -->
    <ConfirmationModal
      :show="showDeleteModal"
      type="danger"
      title="Delete Report"
      :message="`Are you sure you want to delete this report? This action cannot be undone.`"
      confirm-text="Delete Report"
      cancel-text="Cancel"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </AuthenticatedLayout>
</template>

<style scoped>
/* Enhanced drag and drop styling */
.drag-active {
  @apply border-blue-400 bg-blue-50 dark:bg-blue-900/20;
}

/* Smooth transitions for all interactive elements */
* {
  transition: all 0.2s ease;
}

/* Enhanced hover effects for drag zone */
.hover\:border-gray-400:hover {
  transition: border-color 0.2s ease;
}

/* Dark mode enhancements */
.dark .hover\:border-gray-500:hover {
  transition: border-color 0.2s ease;
}

/* File preview card styling */
.file-preview {
  @apply bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg;
}

/* Upload button enhanced styling */
.upload-button {
  box-shadow: 0 4px 14px 0 rgba(34, 197, 94, 0.25);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.upload-button:hover {
  box-shadow: 0 8px 25px 0 rgba(34, 197, 94, 0.35);
  transform: translateY(-1px);
}

.upload-button:disabled {
  box-shadow: none;
  transform: none;
}

/* Drag zone pulse animation */
@keyframes pulse-blue {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

.drag-zone-active {
  animation: pulse-blue 1.5s ease-in-out infinite;
}
</style>
