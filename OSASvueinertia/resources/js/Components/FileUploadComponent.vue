<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-5">
   
    
    <h2 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300">Upload Document</h2>
    
    <!-- Scanning Modal -->
    <div 
      v-if="isProcessing && selectedFile" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-2 sm:p-4 modal-backdrop"
      @click.self="showCancelConfirmation = true"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-4xl w-full max-h-[95vh] sm:max-h-[90vh] overflow-hidden scanning-modal">
        <!-- Modal Header -->
        <div class="flex justify-between items-center p-3 sm:p-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center space-x-2 sm:space-x-3">
            <div class="flex space-x-1">
              <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
              <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
              <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
            </div>
            <h3 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100">
              {{ showExtractedText ? 'Extracted Text' : isExtracting ? 'Extracting Event Details' : 'Scanning Document' }}
            </h3>
          </div>
          <button 
            @click="closeScanningModal" 
            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors duration-200"
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
        <div class="relative bg-white dark:bg-gray-800 overflow-hidden" style="min-height: 300px; max-height: calc(95vh - 100px);">
          <!-- File Preview -->
          <div v-if="!showExtractedText && !isExtracting" class="relative h-full">
            <img 
              v-if="filePreview && (fileType === 'image' || fileType === 'pdf')" 
              :src="filePreview" 
              alt="File preview"
              class="w-full h-full object-contain bg-gray-50 dark:bg-gray-700 transition-opacity duration-300"
            />
            <div 
              v-else-if="fileType === 'pdf' && !filePreview"
              class="w-full h-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center transition-opacity duration-300"
              style="min-height: 400px;"
            >
              <div class="text-center text-gray-700 dark:text-gray-300">
                <svg class="w-24 h-24 sm:w-32 sm:h-32 mx-auto mb-6 text-red-500 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                <p class="text-2xl font-medium mb-2 text-gray-800 dark:text-gray-100">{{ selectedFile.name }}</p>
                <p class="text-lg text-gray-600 dark:text-gray-300">PDF Document</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ formatFileSize(selectedFile.size) }}</p>
              </div>
            </div>
            
            <!-- Scanning overlay -->
            <div class="absolute inset-0 bg-blue-50 dark:bg-blue-900 bg-opacity-30 dark:bg-opacity-40 pointer-events-none">
              <!-- Scanning line -->
              <div 
                class="absolute left-0 right-0 h-1 sm:h-2 bg-gradient-to-r from-transparent via-blue-400 to-transparent shadow-lg scan-line"
                style="box-shadow: 0 0 30px rgba(59, 130, 246, 0.9), 0 0 60px rgba(59, 130, 246, 0.5);"
              ></div>
            </div>
          </div>
          
          <!-- Extracting Animation -->
          <div v-if="isExtracting" class="relative h-full bg-white dark:bg-gray-800 flex items-center justify-center">
            <div class="text-center text-gray-700 dark:text-gray-300">
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
                <h4 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Extracting Event Details</h4>
                <p class="text-lg text-gray-600 dark:text-gray-300 mb-2">Analyzing extracted text...</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Identifying dates, times, and event information</p>
              </div>
            </div>
          </div>
          
          <!-- Extracted Text Display -->
          <div v-if="showExtractedText && !isExtracting" class="h-full bg-white dark:bg-gray-800 p-3 sm:p-6 overflow-y-auto">
            <div class="mb-3 sm:mb-4">
              <h4 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Extracted Text from Document</h4>
              <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mb-3 sm:mb-4">Review the extracted text before proceeding to event extraction:</p>
              
              <!-- Warning Note -->
              <div class="flex items-start space-x-2 p-2 sm:p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-md mb-3 sm:mb-4">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600 dark:text-yellow-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <p class="text-xs sm:text-sm text-yellow-800 dark:text-yellow-200">
                  <strong>Note:</strong> Please review the extracted text carefully as it may not always be accurate. Verify all information before proceeding.
                </p>
              </div>
            </div>
            
            <div class="p-3 sm:p-6 bg-gray-50 dark:bg-gray-700 max-h-48 sm:max-h-64 overflow-y-auto rounded-md border border-gray-200 dark:border-gray-600">
              <pre class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono leading-relaxed">{{ extractedText }}</pre>
            </div>
            
            <!-- Countdown timer centered below extracted text -->
            <div v-if="countdown > 0" class="w-full flex justify-center my-3 sm:my-4">
              <span class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Proceeding in {{ countdown }} second<span v-if="countdown !== 1">s</span>...</span>
            </div>
            
            <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
              <button 
                @click="showCancelConfirmation = true"
                class="w-full sm:w-auto px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-gray-400 transition-colors duration-200"
              >
                Cancel
              </button>
              <button 
                @click="proceedToEventExtraction"
                class="w-full sm:w-auto px-4 py-2 text-sm bg-blue-500 dark:bg-blue-600 text-white rounded-md hover:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
              >
                Extract Event Details
              </button>
            </div>
          </div>
          
          <!-- Processing indicator overlay -->
          <div v-if="!showExtractedText && !isExtracting" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-white dark:from-gray-800 via-white/90 dark:via-gray-800/90 to-transparent p-3 sm:p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between text-gray-700 dark:text-gray-300 space-y-2 sm:space-y-0">
              <div class="flex items-center space-x-2 sm:space-x-4">
                <div class="flex space-x-2">
                  <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                  <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                </div>
                <div>
                  <p class="text-sm sm:text-lg font-medium text-gray-800 dark:text-gray-100">Analyzing document content...</p>
                  <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">Extracting text from document</p>
                </div>
              </div>
              <div class="text-left sm:text-right">
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 truncate max-w-[200px] sm:max-w-none">{{ selectedFile.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatFileSize(selectedFile.size) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Cancel Confirmation Modal -->
    <div 
      v-if="showCancelConfirmation" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] p-4"
      @click.self="dismissCancel"
    >
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-start space-x-3 mb-4">
          <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Cancel Document Scan?</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Are you sure you want to cancel the scanning process? All progress will be lost.
            </p>
          </div>
        </div>
        
        <div class="flex space-x-3 justify-end">
          <button
            @click="dismissCancel"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200"
          >
            Continue Scanning
          </button>
          <button
            @click="confirmCancel"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200"
          >
            Yes, Cancel
          </button>
        </div>
      </div>
    </div>
    
    <!-- Upload Area -->
    <div v-if="!isProcessing" class="flex items-center justify-center w-full">
      <label 
        class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
        :class="{ 'border-blue-500 bg-blue-50 dark:bg-blue-900': isDragging, 'border-gray-300 dark:border-gray-600': !isDragging }"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="onFileDrop"
      >
        <div class="flex flex-col items-center justify-center pt-5 pb-6">
          <svg 
            class="w-8 h-8 sm:w-10 sm:h-10 mb-3 sm:mb-4 transition-colors duration-200" 
            :class="isDragging ? 'text-blue-600 dark:text-blue-400' : 'text-blue-500 dark:text-blue-400'"
            aria-hidden="true" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 20 16"
          >
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
          </svg>
          <p class="mb-2 text-sm text-gray-600 dark:text-gray-300 text-center px-2">
            <span class="font-medium">Click to upload</span> or drag and drop
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 text-center px-2">PNG, JPG, PDF (MAX. 10MB)</p>
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
      class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group disabled:opacity-50 disabled:cursor-not-allowed"
      :disabled="isProcessing"
    >
      <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
      </svg>
      <span class="relative z-10">Create New Event</span>
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
    const showCancelConfirmation = ref(false);
    
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
      // Validate file size (20MB limit)
      const maxSize = 20 * 1024 * 1024; // 20MB in bytes
      if (file.size > maxSize) {
        alert('File size exceeds 20MB limit. Please choose a smaller file.');
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
      // Show confirmation modal before canceling
      showCancelConfirmation.value = true;
    }
    
    function confirmCancel() {
      // User confirmed cancellation
      showCancelConfirmation.value = false;
      cancelExtraction();
    }
    
    function dismissCancel() {
      // User dismissed cancellation
      showCancelConfirmation.value = false;
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
      countdown,
      showCancelConfirmation,
      confirmCancel,
      dismissCancel
    };
  }
};
</script>