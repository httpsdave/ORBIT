<template>
  <!-- Past Events Modal -->
  <div 
    v-if="showModal" 
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="closeModal"
  >
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden">
      <div class="flex justify-between items-center p-6 border-b">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
          <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Event History
        </h3>
        <div class="flex items-center space-x-2">
          <button 
            @click="$emit('export-csv', pastEvents)"
            class="p-2 bg-green-500 text-white rounded-full hover:bg-green-600 transition flex items-center"
            :class="{ 'opacity-60 pointer-events-none bg-gray-200 text-gray-400': pastEvents.length === 0 }"
            :disabled="pastEvents.length === 0"
            title="Export as CSV"
            aria-label="Export as CSV"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
            </svg>
          </button>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <ul class="divide-y divide-gray-100">
          <li v-for="event in pastEvents" :key="'past-' + event.id" class="py-4">
            <div class="flex items-start space-x-4">
              <div class="flex-shrink-0 bg-gray-50 rounded-lg p-3 text-center border-l-4 border-gray-400">
                <span class="text-sm font-medium text-gray-500">{{ formatDate(event.start_date, 'MMM') }}</span>
                <p class="text-xl font-bold text-gray-600">{{ formatDate(event.start_date, 'DD') }}</p>
                <span class="text-xs text-gray-400">{{ formatDate(event.start_date, 'YYYY') }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-700 truncate">{{ event.title }}</p>
                <div class="flex items-center mt-1 text-xs text-gray-500">
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
                <p v-if="event.description" class="text-xs text-gray-500 mt-1 line-clamp-2">{{ event.description }}</p>
              </div>
              <!-- Admin delete button for past events -->
              <div v-if="isAdmin" class="flex-shrink-0">
                <button @click="deletePastEvent(event.id)" class="text-red-500 hover:text-red-700 p-1 rounded transition-colors duration-200" title="Delete past event">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
              <!-- Non-admin view button -->
              <div v-else class="flex-shrink-0">
                <button @click="viewEventDetails(event)" class="text-gray-500 hover:text-gray-700 p-1 rounded transition-colors duration-200">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>
          </li>
          <li v-if="pastEvents.length === 0" class="py-8 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>No past events</p>
          </li>
        </ul>
      </div>
    </div>
  </div>
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
  
  emits: ['close', 'view-event-details', 'event-deleted', 'export-csv'],
  
  setup(props, { emit }) {
    const pastEvents = computed(() => {
      const now = new Date();
      return props.events
        .filter(event => {
          // Show events that have completely ended
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
      
      if (confirm('Are you sure you want to delete this past event? This action cannot be undone.')) {
        axios.delete(`/api/events/${eventId}`)
          .then(() => {
            emit('event-deleted', eventId);
            alert('Past event deleted successfully!');
          })
          .catch(error => {
            if (error.response && error.response.status === 403) {
              alert('Unauthorized: You do not have permission to perform this action.');
            } else {
              console.error('Error deleting past event:', error);
              alert('Failed to delete past event. Please try again.');
            }
          });
      }
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