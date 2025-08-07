<template>
  <!-- Past Events Modal -->
  <Transition name="modal">
    <div 
      v-if="showModal" 
      class="fixed inset-0 bg-black bg-opacity-50 dark:bg-black dark:bg-opacity-70 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden">
      <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
          <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Event History
        </h3>
        <div class="flex items-center space-x-2">
          <button 
            @click="$emit('export-csv', pastEvents)"
            class="inline-flex items-center justify-center p-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl shadow-md hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group"
            :class="{ 'opacity-60 pointer-events-none !bg-gray-200 dark:!bg-gray-600 !text-gray-400 dark:!text-gray-500': pastEvents.length === 0 }"
            :disabled="pastEvents.length === 0"
            title="Export as CSV"
            aria-label="Export as CSV"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
            </svg>
          </button>
          <button @click="closeModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Colored banner -->
      <div class="flex w-full overflow-hidden">
        <div class="w-1/4 h-1 bg-blue-500"></div>
        <div class="w-1/4 h-1 bg-green-500"></div>
        <div class="w-1/4 h-1 bg-yellow-500"></div>
        <div class="w-1/4 h-1 bg-red-500"></div>
      </div>
      
      <div class="p-6 overflow-y-auto max-h-[60vh]">
        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
          <li v-for="event in pastEvents" :key="'past-' + event.id" class="py-4">
            <div class="flex items-start space-x-4">
              <div :class="[
                'flex-shrink-0 rounded-lg p-3 text-center border-l-4',
                event.status === 'cancelled' 
                  ? 'bg-red-50 dark:bg-red-900/20 border-red-400' 
                  : 'bg-gray-50 dark:bg-gray-700 border-gray-400 dark:border-gray-600'
              ]">
                <span :class="[
                  'text-sm font-medium',
                  event.status === 'cancelled' ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'
                ]">{{ formatDate(event.start_date, 'MMM') }}</span>
                <p :class="[
                  'text-xl font-bold',
                  event.status === 'cancelled' ? 'text-red-600 dark:text-red-300' : 'text-gray-600 dark:text-gray-300'
                ]">{{ formatDate(event.start_date, 'DD') }}</p>
                <span :class="[
                  'text-xs',
                  event.status === 'cancelled' ? 'text-red-400 dark:text-red-500' : 'text-gray-400 dark:text-gray-500'
                ]">{{ formatDate(event.start_date, 'YYYY') }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2">
                  <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">{{ event.title }}</p>
                  <span v-if="event.status === 'cancelled'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Cancelled
                  </span>
                </div>
                <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ formatDate(event.start_date, 'MMM DD, YYYY h:mm A') }}
                  <span v-if="event.end_date && formatDate(event.start_date, 'YYYY-MM-DD') !== formatDate(event.end_date, 'YYYY-MM-DD')">
                    - {{ formatDate(event.end_date, 'MMM DD, YYYY h:mm A') }}
                  </span>
                  <span v-else-if="event.end_date">
                    - {{ formatDate(event.end_date, 'h:mm A') }}
                  </span>
                </div>
                <p v-if="event.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ event.description }}</p>
              </div>
              <!-- Admin delete button for past events -->
              <div v-if="isAdmin" class="flex-shrink-0">
                <button @click="deletePastEvent(event.id)" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200" title="Delete past event">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
              <!-- Non-admin view button -->
              <div v-else class="flex-shrink-0">
                <button @click="viewEventDetails(event)" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>
          </li>
          <li v-if="pastEvents.length === 0" class="py-8 text-center text-gray-500 dark:text-gray-400">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>No past events</p>
          </li>
        </ul>
      </div>
      </div>
    </div>
  </Transition>
</template>
<script>
import { computed } from 'vue';
import dayjs from 'dayjs';
import axios from 'axios';

export default {
  name: 'EventHistoryModal',
  
  props: {
    showModal: {
      type: Boolean,
      default: false
    },
    events: {
      type: Array,
      required: true
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['close', 'view-event-details', 'event-deleted', 'export-csv', 'delete-past-event'],
  
  setup(props, { emit }) {
    const pastEvents = computed(() => {
      const now = new Date();
      return props.events
        .filter(event => {
          // Show events that have completely ended OR are cancelled
          if (event.status === 'cancelled') return true;
          
          const endDate = event.end_date ? new Date(event.end_date) : new Date(event.start_date);
          return endDate < now;
        })
        .sort((a, b) => new Date(b.start_date) - new Date(a.start_date)); // Most recent first
    });
    
    function formatDate(dateString, format) {
      return dayjs(dateString).format(format);
    }
    
    function closeModal() {
      emit('close');
    }
    
    function viewEventDetails(event) {
      emit('view-event-details', event);
      closeModal();
    }
    
    function deletePastEvent(eventId) {
      if (!props.isAdmin) return;
      
      // Emit event to parent to handle confirmation modal
      emit('delete-past-event', eventId);
    }
    
    return {
      pastEvents,
      formatDate,
      closeModal,
      viewEventDetails,
      deletePastEvent
    };
  }
};
</script>

<style scoped>
/* Modal transition styles */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.1s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>