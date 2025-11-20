<script setup>
import { ref } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  isEdit: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['confirm', 'cancel']);

const handleConfirm = () => {
  emit('confirm');
};

const handleCancel = () => {
  emit('cancel');
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-4">
      <div class="flex items-start mb-4">
        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Confirm Submission</h3>
          <p class="mt-2 text-sm text-gray-600">
            Are you sure you want to submit this application? You can always edit this later.
          </p>
        </div>
      </div>
      <div class="flex justify-end gap-3 mt-6">
        <button
          @click="handleCancel"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
        >
          Cancel
        </button>
        <button
          @click="handleConfirm"
          class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors"
        >
          {{ isEdit ? 'Update' : 'Submit' }}
        </button>
      </div>
    </div>
  </div>
</template>
