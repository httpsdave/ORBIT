<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

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

// Success/error messages
const message = ref('')
const messageType = ref('') // 'success' or 'error'

const showMessage = (text, type = 'success') => {
  message.value = text
  messageType.value = type
  setTimeout(() => {
    message.value = ''
    messageType.value = ''
  }, 5000)
}

// Check for flash messages from Laravel
onMounted(() => {
  if (page.props.flash?.success) {
    showMessage(page.props.flash.success, 'success')
  }
  if (page.props.flash?.error) {
    showMessage(page.props.flash.error, 'error')
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
  const fileInput = fileInputs.value[fileInputKey]
  
  if (!fileInput || !fileInput.files.length) {
    showMessage('Please select a file to upload.', 'error')
    return
  }

  const file = fileInput.files[0]
  
  // Validate file type
  const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
  if (!allowedTypes.includes(file.type)) {
    showMessage('Please upload a PDF or Word document.', 'error')
    return
  }

  // Validate file size (10MB)
  if (file.size > 10 * 1024 * 1024) {
    showMessage('File size must be less than 10MB.', 'error')
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
    onError: (errors) => {
      console.error('Upload errors:', errors)
      const errorMessage = errors.message || errors.error || Object.values(errors)[0] || 'Upload failed. Please try again.'
      showMessage(errorMessage, 'error')
    },
    onFinish: () => {
      uploading.value = false
      uploadingFor.value = null
    }
  })
}

const deleteReport = async (reportId, activityPageNumber, reportType) => {
  if (!confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
    return
  }

  router.delete(`/applications/${props.application.id}/reports/${reportId}`, {
    onError: (errors) => {
      console.error('Delete errors:', errors)
      const errorMessage = errors.message || errors.error || Object.values(errors)[0] || 'Delete failed. Please try again.'
      showMessage(errorMessage, 'error')
    }
  })
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
</script>

<template>
  <Head title="Activity Reports" />
  
  <AuthenticatedLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <h1 class="text-2xl font-bold mb-2">Activity Reports</h1>
                <p class="text-gray-600 dark:text-gray-400">
                  Manage reports for: <span class="font-semibold">{{ application.organization_name }}</span>
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Plan of Activities • {{ activityCount }} {{ activityCount === 1 ? 'Activity' : 'Activities' }}
                </p>
              </div>
              <button
                @click="goBack"
                class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 transition ease-in-out duration-150"
              >
                ← Back to Applications
              </button>
            </div>
          </div>
        </div>

        <!-- Success/Error Message -->
        <div
          v-if="message"
          :class="[
            'mb-6 p-4 rounded-lg',
            messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'
          ]"
        >
          {{ message }}
        </div>

        <!-- Reports Grid -->
        <div class="space-y-8">
          <div
            v-for="page in activityPages"
            :key="page.pageNumber"
            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
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
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                  v-for="(reportTypeName, reportType) in reportTypes"
                  :key="reportType"
                  class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                >
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ reportTypeName }}</h3>
                    <span
                      v-if="page.reports[reportType]"
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        getStatusBadgeClass(page.reports[reportType].status)
                      ]"
                    >
                      {{ page.reports[reportType].status }}
                    </span>
                  </div>

                  <!-- If report exists -->
                  <div v-if="page.reports[reportType]" class="space-y-3">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                      <p><strong>File:</strong> {{ page.reports[reportType].original_filename }}</p>
                      <p><strong>Submitted:</strong> {{ new Date(page.reports[reportType].submitted_at).toLocaleDateString() }}</p>
                      <div v-if="page.reports[reportType].feedback" class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded text-yellow-800 dark:text-yellow-200">
                        <p><strong>Feedback:</strong> {{ page.reports[reportType].feedback }}</p>
                      </div>
                    </div>
                    
                    <div class="flex gap-2">
                      <button
                        @click="downloadReport(page.reports[reportType])"
                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                      >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View
                      </button>
                      <button
                        @click="deleteReport(page.reports[reportType].id, page.pageNumber, reportType)"
                        class="px-3 py-2 border border-red-300 dark:border-red-600 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                      >
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
                    
                    <div class="space-y-2">
                      <input
                        :ref="el => fileInputs[`${page.pageNumber}-${reportType}`] = el"
                        type="file"
                        accept=".pdf,.doc,.docx"
                        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/20 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/30"
                      />
                      <button
                        @click="uploadReport(page.pageNumber, reportType)"
                        :disabled="uploading && uploadingFor === `${page.pageNumber}-${reportType}`"
                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 dark:bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150"
                      >
                        <svg v-if="uploading && uploadingFor === `${page.pageNumber}-${reportType}`" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ uploading && uploadingFor === `${page.pageNumber}-${reportType}` ? 'Uploading...' : 'Upload Report' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="activityCount === 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No activities found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This Plan of Activities submission doesn't have any activities yet.</p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Custom file input styling */
input[type="file"]::-webkit-file-upload-button {
  visibility: hidden;
}

input[type="file"]::before {
  content: 'Choose File';
  display: inline-block;
  background: linear-gradient(top, #f9f9f9, #e3e3e3);
  border: 1px solid #999;
  border-radius: 3px;
  padding: 5px 8px;
  outline: none;
  white-space: nowrap;
  user-select: none;
  -webkit-user-select: none;
  cursor: pointer;
  text-shadow: 1px 1px #fff;
  font-weight: 700;
  font-size: 10pt;
}

input[type="file"]:hover::before {
  border-color: black;
}

input[type="file"]:active::before {
  background: -webkit-linear-gradient(top, #e3e3e3, #f9f9f9);
}
</style>
