<template>
  <!-- Combined Today's and Upcoming Events panel -->
  <div :class="isAdmin ? 'md:col-span-1' : 'md:col-span-2'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-5 min-w-0">
    <h2 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300 break-words">
      {{ todaysEvents.length > 0 ? "Today's Events" : "Upcoming Events" }}
    </h2>
    
    <!-- Today's Events (if any) -->
    <ul v-if="todaysEvents.length > 0" class="divide-y divide-gray-100 dark:divide-gray-700 mb-6 min-w-0">
      <li v-for="event in todaysEvents" :key="'today-' + event.id" class="py-4">
        <div class="flex items-start space-x-4 min-w-0">
          <div class="flex-shrink-0 bg-green-50 dark:bg-green-900 rounded-lg p-3 text-center border-l-4 border-green-500">
            <span class="text-sm font-medium text-green-500 dark:text-green-400">{{ formatDate(event.start_date, 'MMM') }}</span>
            <p class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ formatDate(event.start_date, 'DD') }}</p>
            <span class="text-xs text-green-400">TODAY</span>
          </div>
          <div class="flex-1 min-w-0 overflow-hidden">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate break-words">{{ event.title }}</p>
            <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400 min-w-0">
              <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="truncate">
                {{ formatDate(event.start_date, 'h:mm A') }}
                <span v-if="event.end_date && formatDate(event.start_date, 'YYYY-MM-DD') !== formatDate(event.end_date, 'YYYY-MM-DD')">
                  - {{ formatDate(event.end_date, 'MMM DD, h:mm A') }}
                </span>
                <span v-else-if="event.end_date">
                  - {{ formatDate(event.end_date, 'h:mm A') }}
                </span>
              </span>
            </div>
            <p v-if="event.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2 break-words overflow-hidden">{{ event.description }}</p>
          </div>
          <!-- Admin/User action buttons -->
          <div v-if="isAdmin" class="flex-shrink-0 flex space-x-1">
            <button @click="$emit('edit-event', event)" class="text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 p-1 rounded transition-colors duration-200" title="Edit Event">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button v-if="event.status !== 'cancelled'" @click="$emit('cancel-event', event.id)" class="text-orange-500 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 p-1 rounded transition-colors duration-200" title="Cancel Event">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
            <button @click="$emit('delete-event', event.id)" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 p-1 rounded transition-colors duration-200" title="Delete Event">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
          <div v-else class="flex-shrink-0">
            <button @click="$emit('view-event-details', event)" class="text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 p-1 rounded transition-colors duration-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
        </div>
      </li>
    </ul>
    
    <!-- Upcoming Events Section -->
    <div v-if="todaysEvents.length > 0" class="border-t dark:border-gray-700 pt-4">
      <h3 class="text-md font-medium mb-3 text-gray-600 dark:text-gray-400 break-words">Upcoming Events</h3>
    </div>
    
    <ul class="divide-y divide-gray-100 dark:divide-gray-700 min-w-0">
      <li v-for="event in upcomingEventsFiltered" :key="event.id" class="py-3">
        <div class="flex items-start space-x-3 min-w-0">
          <div class="flex-shrink-0 bg-blue-50 dark:bg-blue-900 rounded-md p-2 text-center border-l-3 border-blue-500">
            <span class="text-xs font-medium text-blue-500 dark:text-blue-400">{{ formatDate(event.start_date, 'MMM') }}</span>
            <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ formatDate(event.start_date, 'DD') }}</p>
            <span class="text-xs text-blue-400">{{ formatDate(event.start_date, 'ddd') }}</span>
          </div>
          <div class="flex-1 min-w-0 overflow-hidden">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate break-words">{{ event.title }}</p>
            <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400 min-w-0">
              <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="truncate">
                {{ formatDate(event.start_date, 'h:mm A') }}
                <span v-if="event.end_date && formatDate(event.start_date, 'YYYY-MM-DD') !== formatDate(event.end_date, 'YYYY-MM-DD')">
                  - {{ formatDate(event.end_date, 'MMM DD, h:mm A') }}
                </span>
                <span v-else-if="event.end_date">
                  - {{ formatDate(event.end_date, 'h:mm A') }}
                </span>
              </span>
            </div>
            <p v-if="event.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2 break-words overflow-hidden">{{ event.description }}</p>
          </div>
          <!-- Admin/User action buttons -->
          <div v-if="isAdmin" class="flex-shrink-0 flex space-x-1">
            <button @click="$emit('edit-event', event)" class="text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 p-1 rounded transition-colors duration-200" title="Edit Event">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button v-if="event.status !== 'cancelled'" @click="$emit('cancel-event', event.id)" class="text-orange-500 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 p-1 rounded transition-colors duration-200" title="Cancel Event">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
            <button @click="$emit('delete-event', event.id)" class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 p-1 rounded transition-colors duration-200" title="Delete Event">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
          <div v-else class="flex-shrink-0">
            <button @click="$emit('view-event-details', event)" class="text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 p-1 rounded transition-colors duration-200">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
        </div>
      </li>
      <li v-if="upcomingEventsFiltered.length === 0" class="py-6 text-center text-gray-500 dark:text-gray-400">
        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-sm">No upcoming events</p>
      </li>
    </ul>
  </div>
</template>
<script>
import { computed } from 'vue';
import dayjs from 'dayjs';

export default {
  props: {
    displayedEvents: {
      type: Array,
      required: true
    },
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  
  emits: ['edit-event', 'delete-event', 'cancel-event', 'view-event-details'],
  
  setup(props) {
    const todaysEvents = computed(() => {
      const today = dayjs().format('YYYY-MM-DD');
      return props.displayedEvents.filter(event => {
        const eventStartDate = dayjs(event.start_date).format('YYYY-MM-DD');
        const eventEndDate = event.end_date ? dayjs(event.end_date).format('YYYY-MM-DD') : eventStartDate;
        
        // Check if today falls within the event's date range
        return today >= eventStartDate && today <= eventEndDate;
      }).sort((a, b) => new Date(a.start_date) - new Date(b.start_date));
    });

    const upcomingEventsFiltered = computed(() => {
      const now = new Date();
      const today = dayjs().format('YYYY-MM-DD');
      
      return props.displayedEvents
        .filter(event => {
          const eventStartDate = dayjs(event.start_date).format('YYYY-MM-DD');
          // Filter out today's events from upcoming events
          return new Date(event.start_date) >= now && eventStartDate !== today;
        })
        .sort((a, b) => new Date(a.start_date) - new Date(b.start_date))
        .slice(0, 5);
    });
    
    function formatDate(dateString, format) {
      return dayjs(dateString).format(format);
    }
    
    return {
      todaysEvents,
      upcomingEventsFiltered,
      formatDate
    };
  }
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.break-words {
  word-wrap: break-word;
  word-break: break-word;
  overflow-wrap: break-word;
}
</style>