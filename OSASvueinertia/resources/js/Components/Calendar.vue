<template>
  <h1 class="text-2xl font-bold mb-4">Event Calendar</h1>
  
  <div class="mb-6 flex gap-4">
    <!-- Left panel - only visible to admins -->
    <div v-if="isAdmin" class="w-1/2">
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-semibold mb-2">Upload Document</h2>
        <div class="flex items-center justify-center w-full">
          <label 
            class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100"
            :class="{ 'border-blue-500': isDragging }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onFileDrop"
          >
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
              <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
              </svg>
              <p class="mb-2 text-sm text-gray-500">
                <span class="font-semibold">Click to upload</span> or drag and drop
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
      
     
      <div v-if="isAdmin" class="mt-4 bg-white rounded-lg shadow p-4">
        <button 
          @click="createNewEvent" 
          class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Create New Event
        </button>
      </div>

      <div v-if="isProcessing" class="mt-4 bg-white rounded-lg shadow p-4">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-700"></div>
          <p>Processing document...</p>
        </div>
      </div>
      
      <!-- Update this portion of the template in the EventForm modal section -->
<div 
  v-if="extractedData || isEditing" 
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  @click.self="cancelEdit"
>
  <div class="bg-white rounded-lg shadow p-4 max-w-md w-full mx-4">
    <h2 class="text-lg font-semibold mb-2">
      {{ isEditing ? 'Edit Event' : 'Create New Event' }}
    </h2>
    <div class="space-y-2">
      <div>
        <label class="block text-sm font-medium text-gray-700">Event Title</label>
        <input v-model="eventForm.title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Start Date</label>
        <input v-model="eventForm.date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Start Time</label>
        <input v-model="eventForm.start_time" type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">End Date</label>
        <input v-model="eventForm.end_date" type="date" :min="eventForm.date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">End Time</label>
        <input v-model="eventForm.end_time" type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea v-model="eventForm.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
      </div>
      <div class="flex justify-end space-x-2">
        <button 
          @click="cancelEdit" 
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
        >
          Cancel
        </button>
        <button 
          @click="isEditing ? updateEvent() : saveEvent()" 
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          {{ isEditing ? 'Update Event' : 'Save Event' }}
        </button>
      </div>
    </div>
  </div>
</div>
    </div>
    
    <!-- Upcoming Events panel (width changes based on admin status) -->
    <div :class="isAdmin ? 'w-1/2' : 'w-full'" class="bg-white rounded-lg shadow p-4">
      <h2 class="text-lg font-semibold mb-2">Upcoming Events</h2>
      <ul class="divide-y divide-gray-200">
        <li v-for="event in upcomingEvents" :key="event.id" class="py-3">
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0 bg-blue-100 rounded-md p-2 text-center">
              <span class="text-sm font-medium text-blue-800">{{ formatDate(event.start_date, 'MMM') }}</span>
              <p class="text-lg font-bold text-blue-800">{{ formatDate(event.start_date, 'DD') }}</p>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ event.title }}</p>
              <p class="text-sm text-gray-500">{{ formatDate(event.start_date, 'h:mm A') }}</p>
            </div>
            <!-- Only show edit/delete buttons to admins -->
            <div v-if="isAdmin" class="flex-shrink-0 flex space-x-2">
              <button @click="editEvent(event)" class="text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button @click="deleteEvent(event.id)" class="text-red-600 hover:text-red-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
            <!-- Show view details button to all users -->
            <div v-else class="flex-shrink-0">
              <button @click="viewEventDetails(event)" class="text-blue-600 hover:text-blue-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
          </div>
        </li>
        <li v-if="upcomingEvents.length === 0" class="py-3 text-center text-gray-500">
          No upcoming events
        </li>
      </ul>
    </div>
  </div>
  
  <div class="bg-white rounded-lg shadow p-4">
    <FullCalendar
      ref="fullCalendar"
      :options="calendarOptions"
    />
  </div>
  
  <!-- Event Details Modal for non-admin users -->
  <div 
  v-if="showEventDetailsModal" 
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  @click.self="closeEventDetailsModal"
>
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">{{selectedEvent.title}}</h3>
        <button @click="closeEventDetailsModal" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="space-y-3">
        <p><span class="font-medium">Date:</span> {{formatDate(selectedEvent.start_date, 'MMMM D, YYYY')}}</p>
        <p><span class="font-medium">Time:</span> {{formatDate(selectedEvent.start_date, 'h:mm A')}} 
          <span v-if="selectedEvent.end_date">- {{formatDate(selectedEvent.end_date, 'h:mm A')}}</span>
        </p>
        <div v-if="selectedEvent.description">
          <p class="font-medium">Description:</p>
          <p class="whitespace-pre-line">{{selectedEvent.description}}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed, watch, onUnmounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import axios from 'axios';
import dayjs from 'dayjs';

export default {
  components: {
    FullCalendar
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
    const isDragging = ref(false);
    const isProcessing = ref(false);
    const extractedData = ref(null);
    const fileInput = ref(null);
    const isEditing = ref(false);
    const currentEditId = ref(null);
    const checkEventsTimer = ref(null);
    
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
    
    function onFileChange(e) {
      if (!props.isAdmin) return; // Safety check
      
      const file = e.target.files[0];
      if (file) {
        processFile(file);
      }
    }
    
    function onFileDrop(e) {
      if (!props.isAdmin) return; // Safety check
      
      isDragging.value = false;
      const file = e.dataTransfer.files[0];
      if (file) {
        processFile(file);
      }
    }
    
    function processFile(file) {
      if (!props.isAdmin) return; // Safety check
      
      const formData = new FormData();
      formData.append('document', file);
      
      isProcessing.value = true;
      
      axios.post('/api/extract-event-info', formData)
        .then(response => {
          extractedData.value = response.data;
          
          // Populate the form with extracted data
          eventForm.title = response.data.title || '';
          eventForm.date = response.data.date || '';
          eventForm.start_time = response.data.start_time || '';
          eventForm.end_time = response.data.end_time || '';
          eventForm.description = response.data.description || '';
        })
        .catch(error => {
          console.error('Error processing document:', error);
          alert('Failed to process document. Please try again.');
        })
        .finally(() => {
          isProcessing.value = false;
        });
    }
    
    function saveEvent() {
  if (!props.isAdmin) return; // Safety check
  
  const startDate = `${eventForm.date}T${eventForm.start_time}`;
  // Use end_date if provided, otherwise fall back to start date
  const endDateStr = eventForm.end_date || eventForm.date;
  const endDate = eventForm.end_time ? `${endDateStr}T${eventForm.end_time}` : null;
  
  axios.post('/api/events', {
    title: eventForm.title,
    start_date: startDate,
    end_date: endDate,
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
  
  const startDate = `${eventForm.date}T${eventForm.start_time}`;
  // Use end_date if provided, otherwise fall back to start date
  const endDateStr = eventForm.end_date || eventForm.date;
  const endDate = eventForm.end_time ? `${endDateStr}T${eventForm.end_time}` : null;
  
  axios.put(`/api/events/${currentEditId.value}`, {
    title: eventForm.title,
    start_date: startDate,
    end_date: endDate,
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
    
    function handleEventDrop(info) {
      if (!props.isAdmin) {
        info.revert();
        return;
      }
      
      const eventId = info.event.id;
      const newStartDate = info.event.start;
      const newEndDate = info.event.end;
      
      axios.put(`/api/events/${eventId}`, {
        start_date: newStartDate,
        end_date: newEndDate
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


    return {
      events,
      displayedEvents,
      calendarOptions,
      isDragging,
      isProcessing,
      extractedData,
      eventForm,
      fileInput,
      upcomingEvents,
      isEditing,
      isAdmin: props.isAdmin,
      showEventDetailsModal,
      selectedEvent,
      onFileChange,
      onFileDrop,
      saveEvent,
      editEvent,
      updateEvent,
      cancelEdit,
      deleteEvent,
      formatDate,
      viewEventDetails,
      closeEventDetailsModal,
      createNewEvent // Add this line
    };
  }
};
</script>