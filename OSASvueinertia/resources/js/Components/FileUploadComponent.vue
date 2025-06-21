<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
    <h2 class="text-lg font-semibold mb-4 text-gray-700">Upload Document</h2>
    <div class="flex items-center justify-center w-full">
      <label 
        class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200"
        :class="{ 'border-blue-500': isDragging }"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="onFileDrop"
      >
        <div class="flex flex-col items-center justify-center pt-5 pb-6">
          <svg class="w-10 h-10 mb-4 text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
          </svg>
          <p class="mb-2 text-sm text-gray-600">
            <span class="font-medium">Click to upload</span> or drag and drop
          </p>
          <p class="text-xs text-gray-500">PNG, JPG, PDF (MAX. 10MB)</p>
        </div>
        <input 
          id="dropzone-file" 
          type="file" 
          class="hidden" 
          ref="fileInput"
          @change="onFileChange" 
          accept="image/png, image/jpeg, application/pdf"
        />
      </label>
    </div>
  </div>
  
  <div class="mt-4">
    <button 
      @click="createNewEvent" 
      class="w-full flex items-center justify-center px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400 transition-colors duration-200"
    >
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
      </svg>
      Create New Event
    </button>
  </div>

  <div v-if="isProcessing" class="mt-4 bg-white rounded-lg shadow-sm border border-gray-100 p-4">
    <div class="flex items-center space-x-3">
      <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
      <p class="text-gray-600">Processing document...</p>
    </div>
  </div>
</template>
<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  name: 'FileUploadComponent',
  
  emits: ['file-processed', 'create-new-event'],
  
  setup(props, { emit }) {
    const isDragging = ref(false);
    const isProcessing = ref(false);
    const fileInput = ref(null);
    
    function onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        processFile(file);
      }
    }
    
    function onFileDrop(e) {
      isDragging.value = false;
      const file = e.dataTransfer.files[0];
      if (file) {
        processFile(file);
      }
    }
    
    function processFile(file) {
      const formData = new FormData();
      formData.append('document', file);
      
      isProcessing.value = true;
      
      axios.post('/api/extract-event-info', formData)
        .then(response => {
          emit('file-processed', response.data);
        })
        .catch(error => {
          console.error('Error processing document:', error);
          alert('Failed to process document. Please try again.');
        })
        .finally(() => {
          isProcessing.value = false;
        });
    }
    
    function createNewEvent() {
      emit('create-new-event');
    }
    
    return {
      isDragging,
      isProcessing,
      fileInput,
      onFileChange,
      onFileDrop,
      createNewEvent
    };
  }
};
</script>