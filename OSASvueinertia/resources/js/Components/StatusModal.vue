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
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md relative z-10 overflow-hidden">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 p-6 relative overflow-hidden border-b border-gray-200 dark:border-gray-700">
          <!-- Background Illustration -->
          <div class="absolute top-1/2 right-0 transform translate-x-[16%] -translate-y-1/2 opacity-[0.42] dark:opacity-[0.36] w-[180px] h-[180px] pointer-events-none z-0">
            <img 
              src="/images/flatillus3.svg" 
              alt="" 
              class="w-full h-full object-contain"
              role="presentation"
            />
          </div>
          
          <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 relative z-10">
            {{ isAdmin ? 'Update Application Status' : 'Application Feedback' }}
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 relative z-10">
            {{ application?.organization_name }}
          </p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 bg-white dark:bg-gray-800">
          <!-- Admin: Status Buttons -->
          <div v-if="isAdmin">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
            <div class="grid grid-cols-3 gap-2 min-w-0">
              <button
                @click="newStatus = 'Pending'"
                :class="[
                  'py-2 px-2 rounded-lg border text-xs font-medium transition-all duration-200 w-full min-w-0 truncate',
                  newStatus === 'Pending'
                    ? 'bg-amber-100 border-amber-400 text-amber-800 ring-2 ring-amber-200'
                    : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-amber-900/20'
                ]"
              >
                Pending
              </button>
              <button
                @click="newStatus = 'Approved'"
                :class="[
                  'py-2 px-2 rounded-lg border text-xs font-medium transition-all duration-200 w-full min-w-0 truncate',
                  newStatus === 'Approved'
                    ? 'bg-green-100 border-green-400 text-green-800 ring-2 ring-green-200'
                    : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-green-900/20'
                ]"
              >
                Approved
              </button>
              <button
                @click="newStatus = 'Disapproved'"
                :class="[
                  'py-2 px-2 rounded-lg border text-xs font-medium transition-all duration-200 w-full min-w-0 truncate',
                  newStatus === 'Disapproved'
                    ? 'bg-red-100 border-red-400 text-red-800 ring-2 ring-red-200'
                    : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20'
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
              :placeholder="newStatus === 'Approved' ? 'Goodjob! thank you for your submission. Keep it up.' : 'Enter feedback to the organization...'"
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
        <div class="bg-gray-50 dark:bg-gray-900 p-6 flex justify-end space-x-3 border-t border-gray-100 dark:border-gray-700">
          <button
            v-if="isAdmin"
            @click="emit('close')"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-300 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Cancel
          </button>
          <button
            v-if="isAdmin"
            @click="handleUpdateStatus"
            :disabled="isSubmitting"
            class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white font-medium rounded-xl shadow-md hover:shadow-blue-300/30 transition-all duration-300 text-sm flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden group"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
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
            class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500 text-white font-medium rounded-xl shadow-md hover:shadow-blue-300/30 transition-all duration-300 text-sm relative overflow-hidden group"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
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
