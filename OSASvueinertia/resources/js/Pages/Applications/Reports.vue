<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBanner from '@/Components/StatusBanner.vue'
import StatusModal from '@/Components/StatusModal.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({
  application: Object,
  activityCount: Number,
  reports: Array,
  reportsByPageAndType: Object,
  reportTypes: Object,
  isAdmin: {
    type: Boolean,
    default: false
  }
})

const page = usePage()

const uploading = ref(false)
const uploadingFor = ref(null) // Track which report is being uploaded
const fileInputs = ref({}) // Store file input refs
const selectedFiles = ref({}) // Track selected files for each input
const dragStates = ref({}) // Track drag states for each drop zone

// Edit report state
const editingReport = ref(null) // Track which report is being edited
const editingFiles = ref({}) // Track selected files for editing

// Actions dropdown state
const activeDropdownReport = ref(null)
const dropdownPosition = ref({ top: 0, left: 0 })
const dropdownButtonEl = ref(null)
const dropdownRef = ref(null)
const dropdownDirection = ref('down') // 'down' or 'up'

// Success/error messages using StatusBanner
const showMessage = ref(false)
const message = ref('')
const messageType = ref('') // 'success' or 'error'

// Confirmation modal state
const showDeleteModal = ref(false)
const deleteTarget = ref(null)

// Status modal state
const showStatusModal = ref(false)
const selectedReport = ref(null)

// Preview modal state
const showPreviewModal = ref(false)
const previewReport = ref(null)

// Back-to-top button state
const showBackToTop = ref(false)

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

// Scroll to top handler
const onScroll = () => {
  try {
    const y = window.scrollY || window.pageYOffset
    showBackToTop.value = y > 300
  } catch (e) {
    // ignore for SSR
  }
}

const scrollToTop = (e) => {
  e?.preventDefault()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

/* Clamp long feedback to 3 lines to prevent overflow */
// Check for flash messages from Laravel and add dropdown listeners
onMounted(() => {
  if (page.props.flash?.success) {
    showMessageWithType(page.props.flash.success, 'success')
  }
  if (page.props.flash?.error) {
    showMessageWithType(page.props.flash.error, 'error')
  }
  
  // Add scroll and dropdown event listeners
  window.addEventListener('scroll', onScroll, { passive: true })
  document.addEventListener('click', closeDropdowns)
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
  
  // Find the report being deleted and check if it's approved
  let reportToDelete = null
  for (const page of activityPages.value) {
    for (const [type, report] of Object.entries(page.reports)) {
      if (report && report.id === reportId) {
        reportToDelete = report
        break
      }
    }
    if (reportToDelete) break
  }
  
  if (reportToDelete && isReportApproved(reportToDelete)) {
    showMessageWithType('Cannot delete an approved report.', 'error')
    showDeleteModal.value = false
    deleteTarget.value = null
    return
  }
  
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

const viewReport = (report) => {
  if (report.file_path) {
    // Device detection for mobile vs desktop
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth < 640;
    
    if (isMobile) {
      // Open report in new tab for mobile
      window.open(`/applications/${props.application.id}/reports/${report.id}/download?action=view`, '_blank')
    } else {
      // Use modal for desktop
      openPreviewModal(report)
    }
  }
}

const handleReportContainerClick = (report, event) => {
  // If there's no report or no file path, do nothing
  if (!report || !report.file_path) return

  // If click originated from an interactive element, don't trigger view
  // This keeps buttons, links, inputs, dropdowns, and file inputs working normally
  if (event.target.closest('button, a, input, label, textarea, select, .dropdown-container, .upload-button, .file-preview')) {
    return
  }

  // Don't trigger if we're currently editing this report
  if (editingReport.value && editingReport.value.id === report.id) {
    return
  }

  // View the report
  viewReport(report)
}

const getStatusBadgeClass = (status) => {
  switch (status?.toLowerCase()) {
    case 'pending':
      return 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300';
    case 'approved':
      return 'bg-green-100 text-green-800';
    case 'disapproved':
    case 'rejected':
      return 'bg-red-100 text-red-800';
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

// Format date similar to ApplicationsTable: 'Sept. 18, 2025'
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const d = new Date(dateString)
  if (isNaN(d.getTime())) return 'Invalid Date'
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

// Dropdown functionality
const toggleDropdown = (report, event) => {
  if (activeDropdownReport.value && activeDropdownReport.value.id === report.id) {
    activeDropdownReport.value = null
    dropdownButtonEl.value = null
    removeDropdownListeners()
  } else {
    activeDropdownReport.value = report
    dropdownButtonEl.value = event.currentTarget
    updateDropdownPosition()
    addDropdownListeners()
  }
}

async function updateDropdownPosition() {
  if (!dropdownButtonEl.value) return
  const rect = dropdownButtonEl.value.getBoundingClientRect()
  let dropdownWidth = 192 // Fixed width for w-48 (192px)
  let left = rect.right - dropdownWidth + 8 // Move closer to button by adding 8px offset
  if (left + dropdownWidth > window.innerWidth) left = window.innerWidth - dropdownWidth - 16
  if (left < 16) left = 16

  await nextTick()
  let dropdownHeight = dropdownRef.value ? dropdownRef.value.offsetHeight : 320

  const spaceBelow = window.innerHeight - rect.bottom
  const spaceAbove = rect.top

  let top
  if (spaceBelow >= dropdownHeight + 16) {
    top = rect.bottom + 2 // Reduced gap from 6px to 2px
    dropdownDirection.value = 'down'
  } else if (spaceAbove >= dropdownHeight + 16) {
    top = rect.top - dropdownHeight - 2 // Reduced gap from 6px to 2px
    dropdownDirection.value = 'up'
  } else if (spaceBelow >= spaceAbove) {
    top = rect.bottom + 2
    dropdownDirection.value = 'down'
  } else {
    top = Math.max(8, rect.top - dropdownHeight - 2)
    dropdownDirection.value = 'up'
  }

  dropdownPosition.value = { top, left }
}

function addDropdownListeners() {
  window.addEventListener('scroll', updateDropdownPosition, true)
  window.addEventListener('resize', updateDropdownPosition)
}

function removeDropdownListeners() {
  window.removeEventListener('scroll', updateDropdownPosition, true)
  window.removeEventListener('resize', updateDropdownPosition)
}

const closeDropdowns = (event) => {
  if (!event.target.closest('.dropdown-container')) {
    activeDropdownReport.value = null
  }
}

// Preview modal functions
const openPreviewModal = (report) => {
  previewReport.value = report
  showPreviewModal.value = true
}

const closePreviewModal = () => {
  showPreviewModal.value = false
  previewReport.value = null
}

const openPreviewInNewWindow = () => {
  if (previewReport.value && previewReport.value.file_path) {
    window.open(`/applications/${props.application.id}/reports/${previewReport.value.id}/download?action=view`, '_blank')
  }
}

// Dropdown action handlers
const viewReportFromDropdown = () => {
  if (activeDropdownReport.value) {
    viewReport(activeDropdownReport.value)
    activeDropdownReport.value = null
  }
}

const deleteReportFromDropdown = () => {
  if (activeDropdownReport.value) {
    // Check if report is approved
    if (isReportApproved(activeDropdownReport.value)) {
      showMessageWithType('Cannot delete an approved report.', 'error')
      activeDropdownReport.value = null
      removeDropdownListeners()
      return
    }
    
    // Find the report type and page number from the active dropdown report
    let reportType = null
    let pageNumber = null
    
    // Search through activity pages to find this report
    for (const page of activityPages.value) {
      for (const [type, report] of Object.entries(page.reports)) {
        if (report && report.id === activeDropdownReport.value.id) {
          reportType = type
          pageNumber = page.pageNumber
          break
        }
      }
      if (reportType) break
    }
    
    deleteReport(activeDropdownReport.value.id, pageNumber, reportType)
    activeDropdownReport.value = null
  }
}

const editReportFromDropdown = () => {
  if (activeDropdownReport.value) {
    // Check if report is approved
    if (isReportApproved(activeDropdownReport.value)) {
      showMessageWithType('Cannot edit an approved report.', 'error')
      activeDropdownReport.value = null
      removeDropdownListeners()
      return
    }
    
    // If already editing another report, show message and return
    if (editingReport.value) {
      showMessageWithType('Please finish editing the current report before editing another one.', 'error')
      activeDropdownReport.value = null
      removeDropdownListeners()
      return
    }
    
    // Find the report type and page number from the active dropdown report
    let reportType = null
    let pageNumber = null
    
    // Search through activity pages to find this report
    for (const page of activityPages.value) {
      for (const [type, report] of Object.entries(page.reports)) {
        if (report && report.id === activeDropdownReport.value.id) {
          reportType = type
          pageNumber = page.pageNumber
          break
        }
      }
      if (reportType) break
    }
    
    if (reportType && pageNumber) {
      // Set the editing state
      editingReport.value = {
        id: activeDropdownReport.value.id,
        pageNumber: pageNumber,
        reportType: reportType
      }
    }
    
    // Close dropdown
    activeDropdownReport.value = null
    removeDropdownListeners()
  }
}

const handleAction = (action) => {
  if (action === 'update-status') {
    // Structure the report data to match what StatusModal expects (like an application object)
    // Convert lowercase status to capitalized for display
    let displayStatus = 'Pending'
    if (activeDropdownReport.value.status) {
      const status = activeDropdownReport.value.status.toLowerCase()
      if (status === 'approved') displayStatus = 'Approved'
      else if (status === 'disapproved' || status === 'rejected') displayStatus = 'Disapproved'
      else displayStatus = 'Pending'
    }
    
    selectedReport.value = {
      ...activeDropdownReport.value,
      organization_name: `Activity Report - ${activeDropdownReport.value.original_filename}`,
      status: displayStatus
    }
    showStatusModal.value = true
  } else if (action === 'view-feedback') {
    viewFeedback(activeDropdownReport.value)
  }
  
  // Close dropdown
  activeDropdownReport.value = null
  removeDropdownListeners()
}

const updateStatus = async (statusData) => {
  try {
    await router.put(`/applications/${props.application.id}/reports/${selectedReport.value.id}/status`, {
      status: statusData.status,
      feedback: statusData.feedback || ''
    })
    
    showMessageWithType(`Report status updated to ${statusData.status}`, 'success')
    showStatusModal.value = false
    selectedReport.value = null
    
    // Refresh the page to show updated data
    router.reload()
  } catch (error) {
    console.error('Error updating report status:', error)
    showMessageWithType('Failed to update report status', 'error')
  }
}

// Add new method for viewing feedback
const viewFeedback = (report) => {
  // Close any open dropdowns
  activeDropdownReport.value = null
  removeDropdownListeners()
  
  // Navigate to the feedback view page (like ApplicationsTable.vue)
  router.visit(`/applications/${props.application.id}/reports/${report.id}/feedback`)
}

// Helper function to properly check if feedback exists
const hasFeedback = (report) => {
  if (!report || !report.feedback) return false
  
  // Check if feedback is a string and has content after trimming
  if (typeof report.feedback === 'string') {
    return report.feedback.trim().length > 0
  }
  
  return false
}

// Helper function to check if a report is approved
const isReportApproved = (report) => {
  if (!report || !report.status) return false
  return report.status.toLowerCase() === 'approved'
}

const cancelEdit = () => {
  editingReport.value = null
  // Clear any selected editing files
  Object.keys(editingFiles.value).forEach(key => {
    editingFiles.value[key] = null
  })
}

const handleEditFileSelect = (event, activityPageNumber, reportType) => {
  const file = event.target.files[0]
  const editKey = `${activityPageNumber}-${reportType}`
  
  if (file) {
    if (validateFile(file)) {
      editingFiles.value[editKey] = file
    } else {
      // Clear the input if file is invalid
      event.target.value = ''
    }
  } else {
    editingFiles.value[editKey] = null
  }
}

const updateReport = async (reportId, activityPageNumber, reportType) => {
  // Find the report being updated and check if it's approved
  let reportToUpdate = null
  for (const page of activityPages.value) {
    for (const [type, report] of Object.entries(page.reports)) {
      if (report && report.id === reportId) {
        reportToUpdate = report
        break
      }
    }
    if (reportToUpdate) break
  }
  
  if (reportToUpdate && isReportApproved(reportToUpdate)) {
    showMessageWithType('Cannot update an approved report.', 'error')
    return
  }
  
  const editKey = `${activityPageNumber}-${reportType}`
  const file = editingFiles.value[editKey]
  
  if (!file) {
    showMessageWithType('Please select a file to replace the current report.', 'error')
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
  uploadingFor.value = editKey

  const formData = new FormData()
  formData.append('report_file', file)
  formData.append('_method', 'PUT') // Laravel method spoofing for PUT request

  // Use Inertia router for form submission with proper CSRF handling
  router.post(`/applications/${props.application.id}/reports/${reportId}`, formData, {
    onSuccess: () => {
      // Clear the editing state
      editingReport.value = null
      editingFiles.value[editKey] = null
      showMessageWithType('Report updated successfully!', 'success')
    },
    onError: (errors) => {
      console.error('Update errors:', errors)
      const errorMessage = errors.message || errors.error || Object.values(errors)[0] || 'Update failed. Please try again.'
      showMessageWithType(errorMessage, 'error')
    },
    onFinish: () => {
      uploading.value = false
      uploadingFor.value = null
    }
  })
}

// Add mounted and unmounted lifecycle hooks
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  document.removeEventListener('click', closeDropdowns)
  removeDropdownListeners()
})

// Watch for modal open/close to lock body scroll
watch(showPreviewModal, (val) => {
  if (val) {
    document.body.classList.add('overflow-hidden')
  } else {
    document.body.classList.remove('overflow-hidden')
  }
})

// Watch for status modal open/close to lock body scroll
watch(showStatusModal, (val) => {
  if (val) {
    document.body.classList.add('overflow-hidden')
  } else {
    document.body.classList.remove('overflow-hidden')
  }
})
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
          class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group w-full sm:w-auto"
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
                <h2 
                  class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2 truncate"
                  :title="page.activityData?.name && page.activityData.name.length > 50 ? page.activityData.name : undefined"
                >
                  {{ page.activityData?.name || `Activity ${page.pageNumber}` }}
                </h2>
                <div v-if="page.activityData" class="text-sm text-gray-600 dark:text-gray-400">
                  <p><strong>Target Date:</strong> {{ page.activityData.target_date || 'Not specified' }}</p>
                  <p><strong>Target No. of Participants:</strong> {{ page.activityData.target_participants || 'Not specified' }}</p>
                </div>
              </div>

              <!-- Reports Grid for this Activity -->
              <div class="responsive-reports-grid gap-4 lg:gap-6">
                <div
                  v-for="(reportTypeName, reportType) in reportTypes"
                  :key="reportType"
                  @click="handleReportContainerClick(page.reports[reportType], $event)"
                  :class="[
                    'border border-gray-200 dark:border-gray-700 rounded-xl p-3 sm:p-4 lg:p-5 bg-gray-50 dark:bg-gray-900/50 transition-colors duration-200',
                    page.reports[reportType] 
                      ? 'hover:bg-gray-100 dark:hover:bg-gray-900/70 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-md' 
                      : 'hover:bg-gray-100 dark:hover:bg-gray-900/70'
                  ]"
                >
                  <div class="flex items-start justify-between mb-2 sm:mb-3 gap-2">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 text-sm sm:text-base leading-tight">{{ reportTypeName }}</h3>
                    <span
                      v-if="page.reports[reportType]"
                      :class="[
                        'inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0',
                        getStatusBadgeClass(page.reports[reportType].status)
                      ]"
                    >
                      {{ page.reports[reportType].status }}
                    </span>
                  </div>

                  <!-- If report exists -->
                  <div v-if="page.reports[reportType]" class="space-y-3">
                    <!-- Editing Mode -->
                    <div 
                      v-if="editingReport && editingReport.id === page.reports[reportType].id"
                      class="space-y-3 border-2 border-amber-300 dark:border-amber-600 rounded-lg p-3 bg-amber-50 dark:bg-amber-900/20"
                    >
                      <div class="flex items-center gap-2 text-amber-700 dark:text-amber-300 text-sm font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Editing Report
                      </div>
                      
                      <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        <p class="truncate"><strong>Current File:</strong> {{ page.reports[reportType].original_filename }}</p>
                        <p><span class="font-semibold text-gray-700 dark:text-gray-200">Submitted:</span> {{ formatDate(page.reports[reportType].submitted_at) }}</p>
                      </div>

                      <!-- File Upload for Edit -->
                      <div class="border-2 border-dashed border-amber-300 dark:border-amber-600 rounded-xl p-4 bg-white dark:bg-gray-800">
                        <input
                          type="file"
                          accept=".pdf"
                          class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100"
                          @change="handleEditFileSelect($event, page.pageNumber, reportType)"
                        />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                          PDF files only (Max 20MB) - This will replace the current report
                        </p>
                      </div>

                      <!-- Edit Actions -->
                      <div class="flex gap-2">
                        <button
                          v-if="editingFiles[`${page.pageNumber}-${reportType}`]"
                          @click="updateReport(page.reports[reportType].id, page.pageNumber, reportType)"
                          :disabled="uploading && uploadingFor === `${page.pageNumber}-${reportType}`"
                          class="inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-amber-500 to-amber-600 text-xs font-medium text-white rounded-lg shadow-md hover:shadow-amber-300/30 hover:from-amber-400 hover:to-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-amber-600 active:to-amber-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300"
                        >
                          <svg v-if="uploading && uploadingFor === `${page.pageNumber}-${reportType}`" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                          </svg>
                          {{ uploading && uploadingFor === `${page.pageNumber}-${reportType}` ? 'Updating...' : 'Update Report' }}
                        </button>
                        <button
                          @click="cancelEdit()"
                          class="inline-flex items-center justify-center px-3 py-2 bg-gray-500 hover:bg-gray-600 text-xs font-medium text-white rounded-lg transition-colors duration-200"
                        >
                          Cancel
                        </button>
                      </div>
                    </div>

                    <!-- Normal View Mode -->
                    <div 
                      v-else
                      class="cursor-pointer"
                      :title="'Click to view ' + page.reports[reportType].original_filename"
                      @click="handleReportContainerClick(page.reports[reportType], $event)"
                    >
                      <div class="flex items-center justify-between min-w-0">
                        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 flex-1 min-w-0 space-y-1">
                          <p class="break-words"><strong>File:</strong> <span class="text-gray-900 dark:text-gray-100">{{ page.reports[reportType].original_filename }}</span></p>
                          <p><span class="font-semibold text-gray-700 dark:text-gray-200">Submitted:</span> <span class="text-gray-900 dark:text-gray-100">{{ formatDate(page.reports[reportType].submitted_at) }}</span></p>
                          <!-- Feedback is handled on the Report Feedback page; inline display removed -->
                          <!-- Visual indicator for clickability -->
                          <div class="mt-2 flex items-center text-xs text-blue-600 dark:text-blue-400 opacity-75">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Click to view
                          </div>
                        </div>
                        
                        <!-- Actions Dropdown Button -->
                        <div class="relative dropdown-container ml-3 flex-shrink-0">
                          <button
                            @click="toggleDropdown(page.reports[reportType], $event)"
                            :data-dropdown-trigger="page.reports[reportType].id"
                            class="flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                          >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- If no report exists -->
                  <div v-else class="space-y-2 sm:space-y-3">
                    <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-2 sm:mb-3">
                      No report uploaded yet
                    </div>
                    
                    <!-- Drag and Drop Zone -->
                    <div 
                      :class="[
                        'relative border-2 border-dashed rounded-xl p-3 sm:p-4 lg:p-6 transition-all duration-200',
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 sm:h-8 lg:h-12 w-6 sm:w-8 lg:w-12 mx-auto text-gray-400 dark:text-gray-500 mb-1 sm:mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-1">
                          <span class="font-medium text-blue-600 dark:text-blue-400">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">
                          PDF files only (Max 20MB)
                        </p>
                      </div>
                      
                      <!-- Selected file preview -->
                      <div v-else class="flex items-center justify-between space-x-2">
                        <div class="flex items-center space-x-2 min-w-0 flex-1">
                          <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 sm:h-6 lg:h-8 w-5 sm:w-6 lg:w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                          </div>
                          <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 break-words">
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
                      class="upload-button w-full inline-flex items-center justify-center px-3 sm:px-4 py-2 sm:py-2.5 bg-gradient-to-r from-green-500 to-green-600 text-xs sm:text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-green-600 active:to-green-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none transition-all duration-300 relative overflow-hidden group"
                    >
                      <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                      <svg v-if="uploading && uploadingFor === `${page.pageNumber}-${reportType}`" class="animate-spin -ml-1 mr-2 h-3 sm:h-4 w-3 sm:w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3 sm:h-4 w-3 sm:w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                      <span>{{ uploading && uploadingFor === `${page.pageNumber}-${reportType}` ? 'Uploading...' : 'Upload Report' }}</span>
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

    <!-- Status Modal -->
    <StatusModal
      :show-modal="showStatusModal"
      :application="selectedReport"
      :is-admin="isAdmin"
      :is-submitting="uploading"
      @update-status="updateStatus"
      @close="showStatusModal = false; selectedReport = null"
    />

    <!-- Render the dropdown only once, outside the table -->
    <Teleport to="body">
      <div 
        ref="dropdownRef"
        v-if="activeDropdownReport"
        class="report-dropdown fixed z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-48"
        :style="{ top: `${dropdownPosition.top}px`, left: `${dropdownPosition.left}px`, visibility: activeDropdownReport ? 'visible' : 'hidden' }"
        @click.stop
      >
        <!-- Admin-only Status Update Option -->
        <button 
          v-if="isAdmin"
          @click="handleAction('update-status')"
          class="w-full text-left px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600 dark:text-purple-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3-9a1 1 0 10-2 0v4a1 1 0 102 0V9z" clip-rule="evenodd" />
            <path d="M10 6a1 1 0 100 2 1 1 0 000-2z" />
          </svg>
          Update Status
        </button>
        <!-- View Report -->
        <button 
          @click="viewReportFromDropdown()"
          class="w-full text-left px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200 font-medium"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600 dark:text-teal-400" viewBox="0 0 20 20" fill="currentColor">
            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2h-1.528A6 6 0 004 9.528V4z" />
            <path fill-rule="evenodd" d="M8 10a4 4 0 00-3.446 6.032l-1.261 1.26a1 1 0 101.415 1.415l1.261-1.261A4 4 0 006 10z" clip-rule="evenodd" />
          </svg>
          View Report
        </button>
        <!-- View Feedback -->
        <button 
          v-if="hasFeedback(activeDropdownReport)"
          @click="handleAction('view-feedback')"
          class="w-full text-left px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200 font-medium"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600 dark:text-purple-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
          </svg>
          View Feedback
        </button>
        <!-- Edit Report - Only show if not approved -->
        <button 
          v-if="!isReportApproved(activeDropdownReport)"
          @click="editReportFromDropdown()"
          class="w-full text-left px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200 font-medium"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500 dark:text-amber-400" viewBox="0 0 20 20" fill="currentColor">
            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
          </svg>
          Edit Report
        </button>
        <!-- Delete Report - Only show if not approved -->
        <button 
          v-if="!isReportApproved(activeDropdownReport)"
          @click="deleteReportFromDropdown()"
          class="w-full text-left px-3 py-1.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 flex items-center gap-2 transition duration-200 border-t border-gray-100 dark:border-gray-600 mt-1 pt-1 font-medium"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          Delete Report
        </button>
      </div>
    </Teleport>

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
              {{ previewReport ? `${previewReport.report_type} Report - ${previewReport.original_filename}` : 'Report Preview' }}
            </div>
            <div class="flex items-center gap-2">
              <button
                v-if="previewReport"
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
              v-if="previewReport && previewReport.file_path"
              :src="`/applications/${props.application.id}/reports/${previewReport.id}/download?action=view`"
              class="w-full h-full border-0 bg-white"
              style="min-height: 300px;"
              allowfullscreen
            ></iframe>
            <div v-else class="text-gray-500 text-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="text-lg">No report file available</p>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Back to top floating button -->
    <button
      v-if="showBackToTop"
      @click="scrollToTop"
      aria-label="Back to top"
      class="fixed z-50 right-6 bottom-8 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 shadow-lg hover:shadow-2xl rounded-full p-3 transition transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
      title="Back to top"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 5a1 1 0 01.707.293l5 5a1 1 0 01-1.414 1.414L10 7.414 5.707 11.707A1 1 0 014.293 10.293l5-5A1 1 0 0110 5z" clip-rule="evenodd" />
      </svg>
    </button>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Responsive grid for report cards - mobile-first approach */
.responsive-reports-grid {
  display: grid;
  grid-template-columns: 1fr;
}

/* Small tablets and up */
@media (min-width: 640px) {
  .responsive-reports-grid {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  }
}

/* Medium screens - prevent too narrow cards */
@media (min-width: 768px) {
  .responsive-reports-grid {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  }
}

/* Large screens - ensure readability */
@media (min-width: 1024px) {
  .responsive-reports-grid {
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  }
}

/* Extra large screens - prevent too many columns */
@media (min-width: 1280px) {
  .responsive-reports-grid {
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
  }
}

/* 2XL screens - maximum readability */
@media (min-width: 1536px) {
  .responsive-reports-grid {
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  }
}

/* Enhanced drag and drop styling */
.drag-active {
  border-color: #60a5fa;
  background-color: #dbeafe;
}

.dark .drag-active {
  background-color: rgba(59, 130, 246, 0.2);
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
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
}

.dark .file-preview {
  background-color: #1f2937;
  border-color: #374151;
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

/* Fade transition for modals */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.fade-enter-to, .fade-leave-from {
  opacity: 1;
}

/* Clamp long feedback to 3 lines to prevent overflow */
.report-feedback p {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  line-clamp: 3;
  -webkit-line-clamp: 3;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word;
  margin: 0;
}

/* Dropdown positioning: avoid animating from (0,0) by not transitioning top/left */
.report-dropdown {
  will-change: opacity, transform;
  transition-property: opacity, transform;
  transition-duration: 180ms;
  transition-timing-function: cubic-bezier(0.4,0,0.2,1);
  /* explicitly prevent transitions on top/left to avoid origin animation */
}

/* When opening, slightly fade/scale instead of moving from top-left */
.report-dropdown[style] {
  transform-origin: top right;
}

.report-dropdown-enter-active, .report-dropdown-leave-active {
  transition: opacity 160ms ease, transform 160ms ease;
}

.report-dropdown-enter-from {
  opacity: 0;
  transform: scale(0.98);
}

.report-dropdown-enter-to {
  opacity: 1;
  transform: scale(1);
}

</style>
