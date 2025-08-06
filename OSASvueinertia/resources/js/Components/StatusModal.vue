<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  showModal: Boolean,
  application: Object,
  isAdmin: Boolean,
  isSubmitting: Boolean
});

const emit = defineEmits(['close', 'updateStatus']);

const newStatus = ref(props.application?.status || '');
const feedbackText = ref(props.application?.feedback || '');

watch(() => props.application, (newApp) => {
  if (newApp) {
    newStatus.value = newApp.status || '';
    feedbackText.value = newApp.feedback || '';
  }
}, { immediate: true });

const getStatusColor = (status) => {
  switch (status?.toLowerCase()) {
    case 'approved':
      return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
    case 'pending':
      return 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300';
    case 'disapproved':
      return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
    default:
      return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
  }
};

const handleUpdateStatus = () => {
  emit('updateStatus', {
    status: newStatus.value,
    feedback: feedbackText.value
  });
};
</script>

<template>
  <transition name="fade">
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop without blur -->
      <div
        class="absolute inset-0 bg-black bg-opacity-50"
        @click="emit('close')"
      ></div>

      <!-- Modal Content -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md w-full max-w-md relative z-10 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white">
          <h3 class="text-xl font-bold text-white">
            {{ isAdmin ? 'Update Application Status' : 'Application Feedback' }}
          </h3>
          <p class="text-sm text-indigo-100 mt-1">
            {{ application?.organization_name }}
          </p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 bg-white dark:bg-gray-800">
          <!-- Admin: Status Buttons -->
          <div v-if="isAdmin">
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <div class="grid grid-cols-3 gap-3">
              <button
                @click="newStatus = 'Pending'"
                :class="[
                  'py-2 px-4 rounded-lg border text-sm font-medium transition-all duration-200',
                  newStatus === 'Pending'
                    ? 'bg-amber-100 border-amber-400 text-amber-800 ring-2 ring-amber-200'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-amber-50'
                ]"
              >
                Pending
              </button>
              <button
                @click="newStatus = 'Approved'"
                :class="[
                  'py-2 px-4 rounded-lg border text-sm font-medium transition-all duration-200',
                  newStatus === 'Approved'
                    ? 'bg-green-100 border-green-400 text-green-800 ring-2 ring-green-200'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-green-50'
                ]"
              >
                Approved
              </button>
              <button
                @click="newStatus = 'Disapproved'"
                :class="[
                  'py-2 px-4 rounded-lg border text-sm font-medium transition-all duration-200',
                  newStatus === 'Disapproved'
                    ? 'bg-red-100 border-red-400 text-red-800 ring-2 ring-red-200'
                    : 'bg-white border-gray-200 text-gray-600 hover:bg-red-50'
                ]"
              >
                Rejected
              </button>
            </div>
          </div>

          <!-- Admin: Feedback Input -->
          <div v-if="isAdmin">
            <label for="feedback" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Feedback <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">(Optional)</span>
            </label>
            <textarea
              id="feedback"
              v-model="feedbackText"
              rows="4"
              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 transition duration-200 p-3 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              placeholder="Enter feedback to the organization..."
            ></textarea>
          </div>

          <!-- User: Status Display -->
          <div v-if="!isAdmin">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Status</h4>
            <span
              :class="`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(application?.status)}`"
            >
              {{ application?.status }}
            </span>
          </div>

          <!-- User: Feedback Display -->
          <div v-if="!isAdmin">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Feedback from Admin</h4>
            <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 text-sm text-gray-700 dark:text-gray-300">
              {{ application?.feedback || 'No feedback provided.' }}
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 dark:bg-gray-700 p-6 flex justify-end space-x-3 border-t border-gray-100 dark:border-gray-600">
          <button
            v-if="isAdmin"
            @click="emit('close')"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-600 transition duration-200 text-sm"
          >
            Cancel
          </button>
          <button
            v-if="isAdmin"
            @click="handleUpdateStatus"
            :disabled="isSubmitting"
            class="px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium rounded-lg transition duration-200 text-sm flex items-center space-x-2 disabled:opacity-70"
          >
            <svg
              v-if="isSubmitting"
              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              />
            </svg>
            <span>{{ isSubmitting ? 'Updating...' : 'Update Status' }}</span>
          </button>
          <button
            v-if="!isAdmin"
            @click="emit('close')"
            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium rounded-lg transition duration-200 text-sm"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </transition>
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
