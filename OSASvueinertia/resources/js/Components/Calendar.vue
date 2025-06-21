<template>
  <!-- Colored banner -->
  <div class="flex w-full mb-4 overflow-hidden rounded-lg shadow-sm">
    <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
    <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
    <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
    <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
  </div>
  
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Event Calendar</h1>
  
  <div class="mb-6 grid gap-6 md:grid-cols-2">
    <!-- Left panel - only visible to admins -->
    <div v-if="isAdmin" class="md:col-span-1">
        <div v-if="isAdmin" class="md:col-span-1">
          <FileUploadComponent 
            @file-processed="handleFileProcessed"
            @create-new-event="createNewEvent"
          />
        </div>
    </div>
    
   <TodayUpcomingEvents 
  :displayed-events="displayedEvents"
  :is-admin="isAdmin"
  @edit-event="editEvent"
  @delete-event="deleteEvent"
  @view-event-details="viewEventDetails"
/>
    
</div>

<!-- Event History Toggle -->
<div class="flex justify-end mb-6">
  <button 
    @click="showPastEventsModal = true" 
    class="flex items-center space-x-2 px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200"
  >
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
    </svg>
    <span class="text-sm font-medium">View Past Events</span>
  </button>
</div>

  <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
    <!-- Custom calendar header with your color scheme -->
    <div class="mb-6">
      <!-- Colored banner for the calendar -->
      <div class="flex w-full mb-4 overflow-hidden rounded-lg">
        <div class="w-1/4 h-1.5 bg-blue-500"></div>
        <div class="w-1/4 h-1.5 bg-green-500"></div>
        <div class="w-1/4 h-1.5 bg-yellow-500"></div>
        <div class="w-1/4 h-1.5 bg-red-500"></div>
      </div>
    </div>
    
    <FullCalendar
      ref="fullCalendar"
      :options="calendarOptions"
      class="full-calendar-custom"
    />
    
    
  </div>
  
  <!-- Event Form Modal -->
  <div 
    v-if="extractedData || isEditing" 
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="cancelEdit"
  >
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-800">
          {{ isEditing ? 'Edit Event' : 'Create New Event' }}
        </h2>
        <button @click="cancelEdit" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
          <input v-model="eventForm.title" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
            <input v-model="eventForm.date" type="date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
            <input v-model="eventForm.start_time" type="time" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
            <input v-model="eventForm.end_date" type="date" :min="eventForm.date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
            <input v-model="eventForm.end_time" type="time" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea v-model="eventForm.description" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
        </div>
        <div class="pt-4 flex justify-end space-x-3">
          <button 
            @click="cancelEdit" 
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
          >
            Cancel
          </button>
          <button 
            @click="isEditing ? updateEvent() : saveEvent()" 
            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
          >
            {{ isEditing ? 'Update Event' : 'Save Event' }}
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Event Details Modal for non-admin users -->
  <div 
    v-if="showEventDetailsModal" 
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="closeEventDetailsModal"
  >
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">{{selectedEvent.title}}</h3>
        <button @click="closeEventDetailsModal" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <!-- Colored banner -->
      <div class="flex w-full mb-4 overflow-hidden rounded-md">
        <div class="w-1/4 h-1 bg-blue-500"></div>
        <div class="w-1/4 h-1 bg-green-500"></div>
        <div class="w-1/4 h-1 bg-yellow-500"></div>
        <div class="w-1/4 h-1 bg-red-500"></div>
      </div>
      
      <div class="space-y-4">
        <div class="flex space-x-4">
          <div class="flex-shrink-0 bg-blue-50 rounded-lg p-3 text-center border-l-4 border-blue-500">
            <span class="text-sm font-medium text-blue-500">{{ formatDate(selectedEvent.start_date, 'MMM') }}</span>
            <p class="text-xl font-bold text-gray-800">{{ formatDate(selectedEvent.start_date, 'DD') }}</p>
          </div>
          <div>
            <p class="font-medium text-gray-700">{{formatDate(selectedEvent.start_date, 'dddd, MMMM D, YYYY')}}</p>
            <p class="text-gray-600">{{formatDate(selectedEvent.start_date, 'h:mm A')}} 
              <span v-if="selectedEvent.end_date">- {{formatDate(selectedEvent.end_date, 'h:mm A')}}</span>
            </p>
          </div>
        </div>
        
        <div v-if="selectedEvent.description" class="mt-4 bg-gray-50 p-4 rounded-lg">
          <p class="text-sm text-gray-600 font-medium mb-2">Description:</p>
          <p class="text-gray-700 whitespace-pre-line">{{selectedEvent.description}}</p>
        </div>
      </div>
    </div>
  </div>

  <EventHistoryModal 
  :show-modal="showPastEventsModal"
  :events="events"
  :is-admin="isAdmin"
  @close="closePastEventsModal"
  @view-event-details="viewEventDetails"
  @event-deleted="handleEventDeleted"
/>
  
</template>
<style scoped>
    /* Custom FullCalendar styling to match your color scheme */
    :deep(.full-calendar-custom) {
      --fc-border-color: #e5e7eb; /* gray-200 */
      --fc-button-bg-color: #3B82F6; /* blue-500 */
      --fc-button-border-color: #3B82F6; /* blue-500 */
      --fc-button-hover-bg-color: #2563EB; /* blue-600 */
      --fc-button-hover-border-color: #2563EB; /* blue-600 */
      --fc-button-active-bg-color: #1D4ED8; /* blue-700 */
      --fc-button-active-border-color: #1D4ED8; /* blue-700 */
      --fc-event-bg-color: #3B82F6; /* blue-500 */
      --fc-event-border-color: #3B82F6; /* blue-500 */
      --fc-today-bg-color: #EFF6FF; /* blue-50 */
      --fc-highlight-color: #F3F4F6; /* gray-100 */
      --fc-list-event-hover-bg-color: #F3F4F6; /* gray-100 */
    }
    
    :deep(.fc .fc-button) {
      font-weight: 500;
      border-radius: 0.375rem;
      padding: 0.5rem 1rem;
      transition: all 0.2s;
    }
    
    :deep(.fc .fc-toolbar-title) {
      font-size: 1.25rem;
      font-weight: 600;
      color: #1F2937; /* gray-800 */
    }
    
    :deep(.fc .fc-daygrid-day.fc-day-today) {
      background-color: var(--fc-today-bg-color);
    }
    
    :deep(.fc .fc-col-header-cell-cushion) {
      font-weight: 600;
    }
    
    :deep(.fc-event) {
      border-radius: 0.25rem;
      font-size: 0.875rem;
    }
    
    :deep(.fc .fc-daygrid-day-number) {
      font-weight: 500;
      color: #4B5563; /* gray-600 */
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      :deep(.fc .fc-toolbar) {
        flex-direction: column;
        gap: 0.75rem;
      }
      
      :deep(.fc .fc-toolbar-title) {
        font-size: 1rem;
      }
    }
    </style>
<script>
import { ref, reactive, onMounted, computed, watch, onUnmounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import FileUploadComponent from '@/Components/FileUploadComponent.vue';
import TodayUpcomingEvents from '@/Components/TodayUpcomingEvents.vue';
import EventHistoryModal from '@/Components/EventHistoryModal.vue';
import axios from 'axios';
import dayjs from 'dayjs';

export default {
  components: {
    FullCalendar,
    FileUploadComponent,
    TodayUpcomingEvents,
    EventHistoryModal
  },
  
  props: {
    initialEvents: Array,
    isAdmin: {
      type: Boolean,
      default: false
    }
  },
  
  setup(props) {
    const events = ref(props.initialEvents || []);
    const displayedEvents = ref([]);
    
  
    const extractedData = ref(null);
    
    const isEditing = ref(false);
    const currentEditId = ref(null);
    const checkEventsTimer = ref(null);
    const showPastEventsModal = ref(false);
    
    // For event details modal (non-admin users)
    const showEventDetailsModal = ref(false);
    const selectedEvent = ref({});
    
    const eventForm = reactive({
      title: '',
      date: '',       // Start date
      end_date: '',   // New field for end date
      start_time: '',
      end_time: '',
      description: ''
    });
    
    // Filter out expired events for display
    const filterExpiredEvents = () => {
      const now = new Date();
      displayedEvents.value = events.value.filter(event => {
        // Keep events with no end date
        if (!event.end_date) return true;
        
        // Filter out events that have ended
        return new Date(event.end_date) > now;
      });
    };
    
    // Apply initial filter
    watch(events, () => {
      filterExpiredEvents();
    }, { immediate: true });
    
    // Set up interval to check for expired events every minute
    onMounted(() => {
      checkEventsTimer.value = setInterval(() => {
        filterExpiredEvents();
      }, 60000); // Check every minute
      
      // Initial events loading
      if (!props.initialEvents) {
        axios.get('/api/events')
          .then(response => {
            events.value = response.data;
            filterExpiredEvents();
          })
          .catch(error => {
            console.error('Error fetching events:', error);
          });
      } else {
        filterExpiredEvents();
      }
    });
    
    // Clean up timer on component unmount
    onUnmounted(() => {
      if (checkEventsTimer.value) {
        clearInterval(checkEventsTimer.value);
      }
    });
    
    const calendarOptions = reactive({
      plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      events: computed(() => {
        return displayedEvents.value.map(event => ({
          id: event.id,
          title: event.title,
          start: event.start_date,
          end: event.end_date,
          description: event.description
        }));
      }),
      eventClick: info => {
        const eventId = parseInt(info.event.id);
        const event = events.value.find(e => e.id === eventId);
        
        if (props.isAdmin) {
          // Show event details with option to edit for admins
          const shouldEdit = confirm(`Event: ${info.event.title}\nTime: ${dayjs(info.event.start).format('YYYY-MM-DD HH:mm')}\n\nWould you like to edit this event?`);
          if (shouldEdit && event) {
            editEvent(event);
          }
        } else {
          // Show event details modal for non-admins
          if (event) {
            viewEventDetails(event);
          }
        }
      },
      editable: props.isAdmin, // Only allow dragging for admins
      eventDrop: handleEventDrop
    });
    
    const upcomingEvents = computed(() => {
      const now = new Date();
      return displayedEvents.value
        .filter(event => new Date(event.start_date) >= now)
        .sort((a, b) => new Date(a.start_date) - new Date(b.start_date))
        .slice(0, 5);
    });

    function handleEventDeleted(eventId) {
      events.value = events.value.filter(event => event.id !== eventId);
      filterExpiredEvents();
    }
    
    function saveEvent() {
      if (!props.isAdmin) return; // Safety check
      
      // Make sure end_date has a value, defaulting to start date if not provided
      const endDateStr = eventForm.end_date || eventForm.date;
      
      const startDate = `${eventForm.date}T${eventForm.start_time || '00:00'}`;
      const endDate = `${endDateStr}T${eventForm.end_time || '23:59'}`;
      
      axios.post('/api/events', {
        title: eventForm.title,
        start_date: startDate,
        end_date: endDate, // Always include end_date
        description: eventForm.description
      })
        .then(response => {
          // Add the new event to the list
          events.value.push(response.data);
          filterExpiredEvents();
          
          // Reset the form
          resetForm();
          alert('Event saved successfully!');
        })
        .catch(error => {
          if (error.response && error.response.status === 403) {
            alert('Unauthorized: You do not have permission to perform this action.');
          } else {
            console.error('Error saving event:', error);
            alert('Failed to save event. Please try again.');
          }
        });
    }
    
    function editEvent(event) {
      if (!props.isAdmin) return; // Safety check
      
      // Switch to edit mode
      isEditing.value = true;
      currentEditId.value = event.id;
      extractedData.value = null;
      
      // Parse the date and times from the event
      const eventDate = dayjs(event.start_date);
      const eventEndDate = event.end_date ? dayjs(event.end_date) : null;
      
      // Fill the form with the event data
      eventForm.title = event.title;
      eventForm.date = eventDate.format('YYYY-MM-DD');
      eventForm.start_time = eventDate.format('HH:mm');
      
      if (eventEndDate) {
        eventForm.end_date = eventEndDate.format('YYYY-MM-DD');
        eventForm.end_time = eventEndDate.format('HH:mm');
      } else {
        eventForm.end_date = eventDate.format('YYYY-MM-DD'); // Default to same day
        eventForm.end_time = '';
      }
      
      eventForm.description = event.description || '';
    }
    
    function updateEvent() {
      if (!props.isAdmin) return; // Safety check
      
      // Make sure end_date has a value, defaulting to start date if not provided
      const endDateStr = eventForm.end_date || eventForm.date;
      
      const startDate = `${eventForm.date}T${eventForm.start_time || '00:00'}`;
      const endDate = `${endDateStr}T${eventForm.end_time || '23:59'}`;
      
      axios.put(`/api/events/${currentEditId.value}`, {
        title: eventForm.title,
        start_date: startDate,
        end_date: endDate, // Always include end_date
        description: eventForm.description
      })
        .then(response => {
          // Update the event in our local state
          const index = events.value.findIndex(e => e.id === currentEditId.value);
          if (index !== -1) {
            events.value[index] = response.data;
            filterExpiredEvents();
          }
          
          // Reset the form and exit edit mode
          resetForm();
          alert('Event updated successfully!');
        })
        .catch(error => {
          if (error.response && error.response.status === 403) {
            alert('Unauthorized: You do not have permission to perform this action.');
          } else {
            console.error('Error updating event:', error);
            alert('Failed to update event. Please try again.');
          }
        });
    }
    
    function cancelEdit() {
      resetForm();
    }
    
    function resetForm() {
      extractedData.value = null;
      isEditing.value = false;
      currentEditId.value = null;
      eventForm.title = '';
      eventForm.date = '';
      eventForm.end_date = ''; // Reset end date
      eventForm.start_time = '';
      eventForm.end_time = '';
      eventForm.description = '';
    }
    
    function deleteEvent(eventId) {
      if (!props.isAdmin) return; // Safety check
      
      if (confirm('Are you sure you want to delete this event?')) {
        axios.delete(`/api/events/${eventId}`)
          .then(() => {
            // Remove the event from the list
            events.value = events.value.filter(event => event.id !== eventId);
            filterExpiredEvents();
            
            // If we were editing this event, reset the form
            if (currentEditId.value === eventId) {
              resetForm();
            }
            
            alert('Event deleted successfully!');
          })
          .catch(error => {
            if (error.response && error.response.status === 403) {
              alert('Unauthorized: You do not have permission to perform this action.');
            } else {
              console.error('Error deleting event:', error);
              alert('Failed to delete event. Please try again.');
            }
          });
      }
    }

    
        function closePastEventsModal() {
      showPastEventsModal.value = false;
    }
    
    function handleEventDrop(info) {
      if (!props.isAdmin) {
        info.revert();
        return;
      }
      
      const eventId = info.event.id;
      const newStartDate = info.event.start;
      const newEndDate = info.event.end || info.event.start; // Default to start date if no end date
      
      axios.put(`/api/events/${eventId}`, {
        start_date: newStartDate,
        end_date: newEndDate // Always include end_date
      })
        .then(() => {
          // Update the event in our local state
          const index = events.value.findIndex(e => e.id === parseInt(eventId));
          if (index !== -1) {
            events.value[index].start_date = newStartDate;
            events.value[index].end_date = newEndDate;
            filterExpiredEvents();
          }
        })
        .catch(error => {
          if (error.response && error.response.status === 403) {
            alert('Unauthorized: You do not have permission to perform this action.');
          } else {
            console.error('Error updating event date:', error);
          }
          info.revert(); // Revert the drag if there was an error
        });
    }
    
    function formatDate(dateString, format) {
      return dayjs(dateString).format(format);
    }
    
    // Functions for non-admin event details modal
    function viewEventDetails(event) {
      selectedEvent.value = event;
      showEventDetailsModal.value = true;
    }
    
    function closeEventDetailsModal() {
      showEventDetailsModal.value = false;
    }
    
    function createNewEvent() {
      isEditing.value = false;
      currentEditId.value = null;
      extractedData.value = {};
      
      // Set default values for the form
      const today = dayjs().format('YYYY-MM-DD');
      eventForm.title = '';
      eventForm.date = today;
      eventForm.end_date = today; // Default end date to same day
      eventForm.start_time = '';
      eventForm.end_time = '';
      eventForm.description = '';
    }

    function handleFileProcessed(data) {
  extractedData.value = data;
  
  // Populate the form with extracted data
  eventForm.title = data.title || '';
  eventForm.date = data.date || '';
  eventForm.end_date = data.end_date || data.date || '';
  eventForm.start_time = data.start_time || '';
  eventForm.end_time = data.end_time || '';
  eventForm.description = data.description || '';
}
    return {
      showPastEventsModal,
      closePastEventsModal,
      handleEventDeleted,
      events,
      displayedEvents,
      calendarOptions,
      extractedData,
      eventForm,
      upcomingEvents,
      isEditing,
      isAdmin: props.isAdmin,
      showEventDetailsModal,
      selectedEvent,
      saveEvent,
      editEvent,
      updateEvent,
      cancelEdit,
      deleteEvent,
      formatDate,
      viewEventDetails,
      closeEventDetailsModal,
      createNewEvent,
      handleFileProcessed
    };
  }
};


</script>