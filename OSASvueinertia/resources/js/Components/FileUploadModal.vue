<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  showModal: Boolean,
  application: Object,
  isUploading: Boolean,
  uploadProgress: Number
});

const emit = defineEmits(['close', 'upload', 'submitLink']);

const isDragging = ref(false);
const selectedFile = ref(null);
const error = ref('');
const submissionType = ref('upload'); // 'upload' or 'link'
const linkUrl = ref('');

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
    error.value = 'File size must be less than 20MB.';
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
        <div class="bg-white rounded-2xl shadow-md w-full max-w-md relative z-10 overflow-hidden" role="dialog" aria-modal="true">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-6 text-white">
          <h3 class="text-xl font-bold">
            Upload Signed Document
          </h3>
          <p class="text-sm text-indigo-100 mt-1">
            {{ application?.user?.name || application?.organization_name }}
          </p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
          <!-- Upload Progress -->
          <div v-if="isUploading" class="space-y-3">
            <div class="text-center">
              <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="text-sm text-gray-600">Uploading document...</p>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" :style="{ width: `${uploadProgress}%` }"></div>
            </div>
            <p class="text-center text-sm text-gray-600">{{ Math.round(uploadProgress) }}% complete</p>
          </div>

          <!-- Submission Options -->
          <div v-else class="space-y-4">
            <!-- Toggle Buttons -->
            <div class="flex space-x-2">
              <button
                @click="switchSubmissionType('upload')"
                :class="[
                  'flex-1 py-3 px-4 rounded-lg font-medium text-sm transition-all duration-200',
                  submissionType === 'upload'
                    ? 'bg-indigo-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                Upload PDF
              </button>
              <button
                @click="switchSubmissionType('link')"
                :class="[
                  'flex-1 py-3 px-4 rounded-lg font-medium text-sm transition-all duration-200',
                  submissionType === 'link'
                    ? 'bg-indigo-600 text-white shadow-md'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
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
                  'border-2 border-dashed rounded-lg p-6 text-center transition-all duration-200 cursor-pointer',
                  isDragging 
                    ? 'border-indigo-400 bg-indigo-50' 
                    : selectedFile 
                      ? 'border-green-400 bg-green-50' 
                      : 'border-gray-300 hover:border-indigo-400 hover:bg-gray-50'
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
                  <svg class="h-12 w-12 text-gray-400 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                  </svg>
                  <div>
                    <p class="text-sm text-gray-600">
                      <span class="font-medium text-indigo-600">Click to upload</span> or drag and drop
                    </p>
                    <p class="text-xs text-gray-500">PDF files only, max 20MB</p>
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
                <label for="link-input" class="block text-sm font-medium text-gray-700 mb-2">
                  Google Drive/Docs Link
                </label>
                <input
                  id="link-input"
                  v-model="linkUrl"
                  type="url"
                  placeholder="Paste Google Drive or Docs link here..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                />
                <p class="text-xs text-gray-500 mt-1">
                  Must be a valid Google Drive or Google Docs link.
                </p>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
              <div class="flex items-center">
                <svg class="h-5 w-5 text-red-400 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-red-700">{{ error }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 p-6 flex justify-end space-x-3 border-t border-gray-100">
          <button
            @click="handleClose"
            :disabled="isUploading"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition duration-200 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancel
          </button>
          <button
            @click="handleUpload"
            :disabled="isUploading || !!error || (submissionType === 'upload' && !selectedFile) || (submissionType === 'link' && !linkUrl.trim())"
            class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-medium rounded-lg transition duration-200 text-sm flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
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