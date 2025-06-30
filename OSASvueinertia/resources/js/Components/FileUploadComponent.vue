<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
   
    
    <h2 class="text-lg font-semibold mb-4 text-gray-700">Upload Document</h2>
    
    <!-- Scanning Modal -->
    <div 
      v-if="isProcessing && selectedFile" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 modal-backdrop"
      @click.self="closeScanningModal"
    >
      <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden scanning-modal">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-4 border-b border-gray-200">
          <div class="flex items-center space-x-3">
            <div class="flex space-x-1">
              <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
              <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
              <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">
              {{ showExtractedText ? 'Extracted Text' : isExtracting ? 'Extracting Event Details' : 'Scanning Document' }}
            </h3>
          </div>
          <button 
            @click="closeScanningModal" 
            class="text-gray-500 hover:text-gray-700 transition-colors duration-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Colored banner -->
        <div class="flex w-full overflow-hidden">
          <div class="w-1/4 h-1 bg-blue-500"></div>
          <div class="w-1/4 h-1 bg-green-500"></div>
          <div class="w-1/4 h-1 bg-yellow-500"></div>
          <div class="w-1/4 h-1 bg-red-500"></div>
        </div>

        <!-- File Preview Container -->
        <div class="relative bg-white overflow-hidden" style="min-height: 400px; max-height: calc(90vh - 120px);">
          <!-- File Preview -->
          <div v-if="!showExtractedText && !isExtracting" class="relative h-full">
            <img 
              v-if="filePreview && fileType === 'image'" 
              :src="filePreview" 
              alt="File preview"
              class="w-full h-full object-contain bg-gray-50 transition-opacity duration-300"
            />
            <div 
              v-else-if="fileType === 'pdf'"
              class="w-full h-full bg-gray-50 flex items-center justify-center transition-opacity duration-300"
              style="min-height: 400px;"
            >
              <div class="text-center text-gray-700">
                <svg class="w-24 h-24 sm:w-32 sm:h-32 mx-auto mb-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                <p class="text-2xl font-medium mb-2 text-gray-800">{{ selectedFile.name }}</p>
                <p class="text-lg text-gray-600">PDF Document</p>
                <p class="text-sm text-gray-500 mt-2">{{ formatFileSize(selectedFile.size) }}</p>
              </div>
            </div>
            
            <!-- Scanning overlay -->
            <div class="absolute inset-0 bg-blue-50 bg-opacity-50 pointer-events-none">
              <!-- Scanning line -->
              <div 
                class="absolute left-0 right-0 h-1 sm:h-2 bg-gradient-to-r from-transparent via-blue-400 to-transparent shadow-lg scan-line"
                style="box-shadow: 0 0 30px rgba(59, 130, 246, 0.9), 0 0 60px rgba(59, 130, 246, 0.5);"
              ></div>
            </div>
          </div>
          
          <!-- Extracting Animation -->
          <div v-if="isExtracting" class="relative h-full bg-white flex items-center justify-center">
            <div class="text-center text-gray-700">
              <div class="mb-8">
                <svg class="w-24 h-24 sm:w-32 sm:h-32 mx-auto mb-6 text-blue-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              
              <!-- Extracting line -->
              <div 
                class="w-full h-1 bg-gradient-to-r from-transparent via-green-500 to-transparent shadow-lg scan-line"
                style="box-shadow: 0 0 30px rgba(34, 197, 94, 0.9), 0 0 60px rgba(34, 197, 94, 0.5);"
              ></div>
              
              <div class="mt-8">
                <h4 class="text-2xl font-semibold mb-4 text-gray-800">Extracting Event Details</h4>
                <p class="text-lg text-gray-600 mb-2">Analyzing extracted text...</p>
                <p class="text-sm text-gray-500">Identifying dates, times, and event information</p>
              </div>
            </div>
          </div>
          
          <!-- Extracted Text Display -->
          <div v-if="showExtractedText && !isExtracting" class="h-full bg-white p-6 overflow-y-auto">
            <div class="mb-4">
              <h4 class="text-lg font-semibold text-gray-800 mb-2">Extracted Text from Document</h4>
              <p class="text-sm text-gray-600 mb-4">Review the extracted text before proceeding to event extraction:</p>
            </div>
            
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 max-h-96 overflow-y-auto">
              <pre class="text-sm text-gray-700 whitespace-pre-wrap font-mono">{{ extractedText }}</pre>
            </div>
            
            <!-- Countdown timer centered below extracted text -->
            <div v-if="countdown > 0" class="w-full flex justify-center my-4">
              <span class="text-sm text-gray-500">Proceeding in {{ countdown }} second<span v-if="countdown !== 1">s</span>...</span>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
              <button 
                @click="cancelExtraction"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
              >
                Cancel
              </button>
              <button 
                @click="proceedToEventExtraction"
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
              >
                Extract Event Details
              </button>
            </div>
          </div>
          
          <!-- Processing indicator overlay -->
          <div v-if="!showExtractedText && !isExtracting" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-white via-white/90 to-transparent p-6">
            <div class="flex items-center justify-between text-gray-700">
              <div class="flex items-center space-x-4">
                <div class="flex space-x-2">
                  <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                  <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                  <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                </div>
                <div>
                  <p class="text-lg font-medium text-gray-800">Analyzing document content...</p>
                  <p class="text-sm text-gray-600">Extracting text from document</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-600">{{ selectedFile.name }}</p>
                <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Upload Area -->
    <div v-if="!isProcessing" class="flex items-center justify-center w-full">
      <label 
        class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200"
        :class="{ 'border-blue-500 bg-blue-50': isDragging, 'border-gray-300': !isDragging }"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="onFileDrop"
      >
        <div class="flex flex-col items-center justify-center pt-5 pb-6">
          <svg 
            class="w-8 h-8 sm:w-10 sm:h-10 mb-3 sm:mb-4 transition-colors duration-200" 
            :class="isDragging ? 'text-blue-600' : 'text-blue-500'"
            aria-hidden="true" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 20 16"
          >
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
          </svg>
          <p class="mb-2 text-sm text-gray-600 text-center px-2">
            <span class="font-medium">Click to upload</span> or drag and drop
          </p>
          <p class="text-xs text-gray-500 text-center px-2">PNG, JPG, PDF (MAX. 10MB)</p>
        </div>
        <input 
          id="dropzone-file" 
          type="file" 
          class="hidden" 
          ref="fileInput"
          @change="onFileChange" 
          accept="image/png, image/jpeg, image/jpg, application/pdf"
        />
      </label>
    </div>
  </div>
  
  <div class="mt-4">
    <button 
      @click="createNewEvent" 
      class="w-full flex items-center justify-center px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      :disabled="isProcessing"
    >
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
      </svg>
      Create New Event
    </button>
  </div>
</template>

<style scoped>
@keyframes scan {
  0% {
    top: 0;
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    top: 100%;
    opacity: 0;
  }
}

.scan-line {
  animation: scan 3s ease-in-out infinite;
}

/* Modal animations */
@keyframes modalBackdropFadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-backdrop {
  animation: modalBackdropFadeIn 0.3s ease-out;
}

.scanning-modal {
  animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Pulse animation with delay */
@keyframes pulse-delayed {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse-delayed 1.5s ease-in-out infinite;
}

/* Enhanced scanning line for modal */
@media (min-width: 640px) {
  .scan-line {
    height: 4px;
  }
}
</style>

<script>
import { ref, watch } from 'vue';
import axios from 'axios';

export default {
  name: 'FileUploadComponent',
  
  emits: ['file-processed', 'create-new-event'],
  
  setup(props, { emit }) {
    const isDragging = ref(false);
    const isProcessing = ref(false);
    const fileInput = ref(null);
    const selectedFile = ref(null);
    const filePreview = ref(null);
    const fileType = ref(null);
    const showExtractedText = ref(false);
    const extractedText = ref('');
    const extractedData = ref(null);
    const isExtracting = ref(false);
    let autoExtractTimer = null;
    const countdown = ref(5);
    
    function onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        handleFileSelection(file);
      }
    }
    
    function onFileDrop(e) {
      isDragging.value = false;
      const file = e.dataTransfer.files[0];
      if (file) {
        handleFileSelection(file);
      }
    }
    
    function handleFileSelection(file) {
      // Validate file size (10MB limit)
      const maxSize = 10 * 1024 * 1024; // 10MB in bytes
      if (file.size > maxSize) {
        alert('File size exceeds 10MB limit. Please choose a smaller file.');
        return;
      }
      
      // Validate file type
      const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
      if (!allowedTypes.includes(file.type)) {
        alert('Please upload a PNG, JPG, or PDF file.');
        return;
      }
      
      selectedFile.value = file;
      
      // Determine file type and create preview
      if (file.type.startsWith('image/')) {
        fileType.value = 'image';
        const reader = new FileReader();
        reader.onload = (e) => {
          filePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
      } else if (file.type === 'application/pdf') {
        fileType.value = 'pdf';
        filePreview.value = null;
      }
      
      // Start processing immediately
      processFile(file);
    }
    
    function processFile(file) {
      const formData = new FormData();
      formData.append('document', file);
      
      isProcessing.value = true;
      showExtractedText.value = false;
      isExtracting.value = false;
      
      axios.post('/api/extract-event-info', formData)
        .then(response => {
          // Store the extracted text and data
          extractedText.value = response.data.raw_text || 'No text extracted';
          extractedData.value = response.data;
          
          // Show the extracted text step
          showExtractedText.value = true;
        })
        .catch(error => {
          console.error('Error processing document:', error);
          alert('Failed to process document. Please try again.');
          resetFileState();
        });
    }
    
    function resetFileState() {
      isProcessing.value = false;
      selectedFile.value = null;
      filePreview.value = null;
      fileType.value = null;
      showExtractedText.value = false;
      extractedText.value = '';
      extractedData.value = null;
      isExtracting.value = false;
      if (fileInput.value) {
        fileInput.value.value = '';
      }
      clearInterval(autoExtractTimer);
      countdown.value = 5;
    }
    
    function createNewEvent() {
      emit('create-new-event');
    }
    
    function closeScanningModal() {
      // Allow closing modal, but keep processing in background
      // The modal will automatically close when processing completes
    }
    
    function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function cancelExtraction() {
      clearInterval(autoExtractTimer);
      resetFileState();
    }
    
    function proceedToEventExtraction() {
      clearInterval(autoExtractTimer);
      // Show extracting animation
      isExtracting.value = true;
      showExtractedText.value = false;
      // Simulate extraction time (2 seconds)
      setTimeout(() => {
        // Emit the extracted data to parent component for event creation
        if (extractedData.value) {
          emit('file-processed', extractedData.value);
        }
        resetFileState();
      }, 2000);
    }
    
    // --- Add watcher for auto-extract logic ---
    watch(showExtractedText, (val) => {
      if (val) {
        countdown.value = 5;
        autoExtractTimer = setInterval(() => {
          if (countdown.value > 1) {
            countdown.value--;
          } else {
            clearInterval(autoExtractTimer);
            proceedToEventExtraction();
          }
        }, 1000);
      } else {
        clearInterval(autoExtractTimer);
      }
    });
    
    return {
      isDragging,
      isProcessing,
      fileInput,
      selectedFile,
      filePreview,
      fileType,
      showExtractedText,
      extractedText,
      onFileChange,
      onFileDrop,
      createNewEvent,
      closeScanningModal,
      formatFileSize,
      cancelExtraction,
      proceedToEventExtraction,
      isExtracting,
      countdown
    };
  }
};
</script>