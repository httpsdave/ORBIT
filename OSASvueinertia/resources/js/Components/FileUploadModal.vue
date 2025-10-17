<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  showModal: Boolean,
  application: Object,
  isUploading: Boolean,
  uploadProgress: Number,
  allowLinkSubmissions: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['close', 'upload', 'submitLink']);

const isDragging = ref(false);
const selectedFile = ref(null);
const error = ref('');
const submissionType = ref('upload'); // 'upload' or 'link'
const linkUrl = ref('');

// Check if error is about file size
const isFileSizeError = computed(() => {
  return error.value.toLowerCase().includes('too large') || error.value.toLowerCase().includes('size');
});

const resetState = () => {
  selectedFile.value = null;
  error.value = '';
  isDragging.value = false;
  linkUrl.value = '';
  submissionType.value = 'upload';
};

watch(() => props.showModal, (newVal) => {
  if (newVal) {
    resetState();
    // Prevent body scroll
    document.body.classList.add('overflow-hidden');
  } else {
    // Re-enable body scroll
    document.body.classList.remove('overflow-hidden');
  }
});

const validateFile = (file) => {
  error.value = '';
  
  if (!file) {
    error.value = 'Please select a file.';
    return false;
  }
  
  if (file.type !== 'application/pdf') {
    error.value = 'Only PDF files are allowed.';
    return false;
  }
  
  if (file.size > 20 * 1024 * 1024) { // 20MB
    error.value = 'Oh no! File size is too large. Please compress your PDF and try again.';
    return false;
  }
  
  return true;
};

const validateLink = (url) => {
  error.value = '';
  
  if (!url || url.trim() === '') {
    error.value = 'Please enter a Google Drive or Google Docs link.';
    return false;
  }
  
  // Basic validation for Google Drive/Docs links
  const googleDrivePattern = /^https:\/\/(drive\.google\.com|docs\.google\.com)\/.+/;
  if (!googleDrivePattern.test(url.trim())) {
    error.value = 'Must be a valid Google Drive or Google Docs link.';
    return false;
  }
  
  return true;
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (validateFile(file)) {
    selectedFile.value = file;
  }
};

const handleDrop = (event) => {
  event.preventDefault();
  isDragging.value = false;
  
  const files = event.dataTransfer.files;
  if (files.length > 0) {
    const file = files[0];
    if (validateFile(file)) {
      selectedFile.value = file;
    }
  }
};

const handleDragOver = (event) => {
  event.preventDefault();
  isDragging.value = true;
};

const handleDragLeave = (event) => {
  event.preventDefault();
  if (!event.currentTarget.contains(event.relatedTarget)) {
    isDragging.value = false;
  }
};

const handleUpload = () => {
  if (submissionType.value === 'upload') {
    if (selectedFile.value && validateFile(selectedFile.value)) {
      emit('upload', selectedFile.value);
    }
  } else {
    if (validateLink(linkUrl.value)) {
      emit('submitLink', linkUrl.value.trim());
    }
  }
};

const handleClose = () => {
  if (!props.isUploading) {
    emit('close');
  }
};

const switchSubmissionType = (type) => {
  submissionType.value = type;
  error.value = '';
  selectedFile.value = null;
  linkUrl.value = '';
};
</script>

<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
        <!-- Backdrop without blur -->
        <div
          class="absolute inset-0 bg-black bg-opacity-50"
          @click="handleClose"
        ></div>

        <!-- Modal Content -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md relative z-10 overflow-hidden" role="dialog" aria-modal="true">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 p-6 relative overflow-hidden border-b border-gray-200 dark:border-gray-700">
          <!-- Background Illustration -->
          <div class="absolute top-1/2 right-0 transform translate-x-[8%] -translate-y-1/2 opacity-[0.42] dark:opacity-[0.36] w-[180px] h-[180px] pointer-events-none z-0">
            <img 
              src="/images/flatillus2.svg" 
              alt="" 
              class="w-full h-full object-contain"
              role="presentation"
            />
          </div>
          
          <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 relative z-10">
            Upload Signed Document
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 relative z-10">
            {{ application?.user?.name || application?.organization_name }}
          </p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
          <!-- Upload Progress -->
          <div v-if="isUploading" class="space-y-3">
            <div class="text-center">
              <svg class="animate-spin h-8 w-8 text-blue-600 dark:text-blue-400 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="text-sm text-gray-600 dark:text-gray-400">Uploading document...</p>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
              <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: `${uploadProgress}%` }"></div>
            </div>
            <p class="text-center text-sm text-gray-600 dark:text-gray-400">{{ Math.round(uploadProgress) }}% complete</p>
          </div>

          <!-- Submission Options -->
          <div v-else class="space-y-4">
            <!-- Toggle Buttons -->
            <div v-if="allowLinkSubmissions" class="flex space-x-2">
              <button
                @click="switchSubmissionType('upload')"
                :class="[
                  'flex-1 py-3 px-4 rounded-xl font-medium text-sm transition-all duration-300 relative overflow-hidden group',
                  submissionType === 'upload'
                    ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                ]"
              >
                <span v-if="submissionType === 'upload'" class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                Upload PDF
              </button>
              <button
                @click="switchSubmissionType('link')"
                :class="[
                  'flex-1 py-3 px-4 rounded-xl font-medium text-sm transition-all duration-300 relative overflow-hidden group',
                  submissionType === 'link'
                    ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                ]"
              >
                <span v-if="submissionType === 'link'" class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                Submit Link
              </button>
            </div>

            <!-- File Upload Area -->
            <div v-if="submissionType === 'upload'" class="space-y-4">
              <!-- Drag and Drop Area -->
              <div
                @drop="handleDrop"
                @dragover="handleDragOver"
                @dragleave="handleDragLeave"
                @click="$refs.fileInput.click()"
                :class="[
                  'border-2 border-dashed rounded-xl p-6 text-center transition-all duration-200 cursor-pointer',
                  isDragging 
                    ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-500' 
                    : selectedFile 
                      ? 'border-green-400 bg-green-50 dark:bg-green-900/20 dark:border-green-500' 
                      : 'border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                ]"
              >
                <div v-if="selectedFile" class="space-y-2">
                  <svg class="h-12 w-12 text-green-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="text-sm font-medium text-green-700">{{ selectedFile.name }}</p>
                  <p class="text-xs text-green-600">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
                  <button
                    @click="selectedFile = null"
                    class="text-xs text-red-600 hover:text-red-800 underline"
                  >
                    Remove
                  </button>
                </div>
                
                <div v-else class="space-y-2">
                  <svg class="h-12 w-12 text-gray-400 dark:text-gray-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                  </svg>
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                      <span class="font-medium text-blue-600 dark:text-blue-400">Click to upload</span> or drag and drop
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500">PDF files only, max 20MB</p>
                  </div>
                </div>
              </div>

              <!-- Hidden File Input -->
              <input
                type="file"
                ref="fileInput"
                @change="handleFileSelect"
                accept=".pdf"
                class="hidden"
              />
            </div>

            <!-- Link Submission Area -->
            <div v-if="submissionType === 'link'" class="space-y-4">
              <div>
                <label for="link-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Google Drive/Docs Link
                </label>
                <input
                  id="link-input"
                  v-model="linkUrl"
                  type="url"
                  placeholder="Paste Google Drive or Docs link here..."
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  Must be a valid Google Drive or Google Docs link.
                </p>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
              <div class="flex items-start">
                <svg class="h-5 w-5 text-red-400 dark:text-red-500 mr-2 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                  <p class="text-sm text-red-700 dark:text-red-400">{{ error }}</p>
                  <a 
                    v-if="isFileSizeError"
                    href="https://www.ilovepdf.com/compress_pdf" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline font-medium mt-1 inline-flex items-center"
                  >
                    Try compressing your PDF here
                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 dark:bg-gray-900 p-6 flex justify-end space-x-3 border-t border-gray-100 dark:border-gray-700">
          <button
            @click="handleClose"
            :disabled="isUploading"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-300 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancel
          </button>
          <button
            @click="handleUpload"
            :disabled="isUploading || !!error || (submissionType === 'upload' && !selectedFile) || (submissionType === 'link' && !linkUrl.trim())"
            class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white font-medium rounded-xl shadow-md hover:shadow-blue-300/30 transition-all duration-300 text-sm flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden group"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg v-if="isUploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            <span>{{ isUploading ? 'Uploading...' : 'Upload Document' }}</span>
          </button>
        </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease-out;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>