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
          <!-- Date badge - changes color if cancelled -->
          <div :class="[
            'flex-shrink-0 rounded-lg p-3 text-center border-l-4',
            event.status === 'cancelled' 
              ? 'bg-red-50 dark:bg-red-900 border-red-500' 
              : 'bg-green-50 dark:bg-green-900 border-green-500'
          ]">
            <span :class="[
              'text-sm font-medium',
              event.status === 'cancelled' ? 'text-red-500 dark:text-red-400' : 'text-green-500 dark:text-green-400'
            ]">{{ formatDate(event.start_date, 'MMM') }}</span>
            <p class="text-xl font-bold text-gray-800 dark:text-gray-100">{{ formatDate(event.start_date, 'DD') }}</p>
            <span :class="[
              'text-xs',
              event.status === 'cancelled' ? 'text-red-400' : 'text-green-400'
            ]">{{ event.status === 'cancelled' ? 'CANCELLED' : 'TODAY' }}</span>
          </div>
          <div class="flex-1 min-w-0 overflow-hidden">
            <!-- Title with strikethrough if cancelled -->
            <p :class="[
              'text-sm font-medium truncate break-words',
              event.status === 'cancelled' 
                ? 'text-red-600 dark:text-red-400 line-through opacity-75' 
                : 'text-gray-900 dark:text-gray-100'
            ]">{{ event.title }}</p>
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
            <!-- Show cancelled badge if cancelled -->
            <div v-if="event.status === 'cancelled'" class="mt-2">
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Event Cancelled
              </span>
            </div>
            <p v-if="event.description" :class="[
              'text-xs mt-1 line-clamp-2 break-words overflow-hidden',
              event.status === 'cancelled' ? 'text-gray-400 dark:text-gray-500' : 'text-gray-500 dark:text-gray-400'
            ]">{{ event.description }}</p>
          </div>
          <!-- Action dropdown button -->
          <div class="flex-shrink-0 dropdown-container">
            <button 
              @click="toggleDropdown(event, $event)" 
              :aria-label="isAdmin ? 'Actions' : 'View Details'"
              class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-400 transition group"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
              </svg>
              <span class="absolute left-1/2 -bottom-8 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
                Actions
              </span>
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
          <!-- Action dropdown button -->
          <div class="flex-shrink-0 dropdown-container">
            <button 
              @click="toggleDropdown(event, $event)" 
              :aria-label="isAdmin ? 'Actions' : 'View Details'"
              class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-400 transition group"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
              </svg>
              <span class="absolute left-1/2 -bottom-8 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
                Actions
              </span>
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
  
  <!-- Floating Desktop Dropdown -->
  <Teleport to="body">
    <div 
      ref="dropdownRef"
      v-if="activeDropdownEvent"
      class="dropdown-container fixed z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-40"
      :style="{ top: `${dropdownPosition.top}px`, left: `${dropdownPosition.left}px`, visibility: activeDropdownEvent ? 'visible' : 'hidden' }"
      @click.stop
    >
      <!-- View option (for everyone) -->
      <button 
        @click="handleDropdownAction(activeDropdownEvent, 'view')"
        class="w-full text-left px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200"
      >
        <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        View Details
      </button>
      
      <!-- Admin options -->
      <template v-if="isAdmin">
        <button 
          @click="handleDropdownAction(activeDropdownEvent, 'edit')"
          class="w-full text-left px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200"
        >
          <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit
        </button>
        
        <button 
          v-if="activeDropdownEvent.status !== 'cancelled'"
          @click="handleDropdownAction(activeDropdownEvent, 'cancel')"
          class="w-full text-left px-3 py-1.5 text-sm text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/30 flex items-center gap-2 transition duration-200"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Cancel Event
        </button>
        
        <button 
          @click="handleDropdownAction(activeDropdownEvent, 'delete')" 
          class="w-full text-left px-3 py-1.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 flex items-center gap-2 transition duration-200 border-t border-gray-100 dark:border-gray-600 mt-1 pt-1"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Delete
        </button>
      </template>
    </div>
  </Teleport>

  <!-- Mobile Actions Modal -->
  <Teleport to="body">
    <transition
      enter-active-class="transition-opacity ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showMobileActionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-end justify-center z-50" @click="closeMobileActionsModal">
        <transition
          enter-active-class="transition-transform ease-out duration-250"
          enter-from-class="translate-y-full"
          enter-to-class="translate-y-0"
          leave-active-class="transition-transform ease-in duration-200"
          leave-from-class="translate-y-0"
          leave-to-class="translate-y-full"
        >
          <div v-if="showMobileActionsModal" class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-t-lg shadow-xl" @click.stop>
            <!-- Modal Header -->
            <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center space-x-2">
                <div :class="[
                  'flex-shrink-0 rounded-md p-2 text-center',
                  selectedMobileEvent && selectedMobileEvent.status === 'cancelled' 
                    ? 'bg-red-50 dark:bg-red-900' 
                    : 'bg-blue-50 dark:bg-blue-900'
                ]">
                  <span :class="[
                    'text-xs font-medium',
                    selectedMobileEvent && selectedMobileEvent.status === 'cancelled' 
                      ? 'text-red-500 dark:text-red-400' 
                      : 'text-blue-500 dark:text-blue-400'
                  ]">{{ selectedMobileEvent ? formatDate(selectedMobileEvent.start_date, 'MMM') : '' }}</span>
                  <p class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ selectedMobileEvent ? formatDate(selectedMobileEvent.start_date, 'DD') : '' }}</p>
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ selectedMobileEvent ? selectedMobileEvent.title : '' }}
                  </h3>
                  <p v-if="selectedMobileEvent && selectedMobileEvent.status === 'cancelled'" class="text-xs text-red-600 dark:text-red-400">
                    Event Cancelled
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Modal Actions -->
            <div class="py-1">
              <button 
                @click="handleMobileAction('view')"
                class="w-full flex items-center px-3 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-150 active:scale-[0.98]"
              >
                <svg class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span class="text-sm text-gray-900 dark:text-gray-100">View Details</span>
              </button>
              
              <template v-if="isAdmin">
                <button 
                  @click="handleMobileAction('edit')"
                  class="w-full flex items-center px-3 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-150 active:scale-[0.98]"
                >
                  <svg class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  <span class="text-sm text-gray-900 dark:text-gray-100">Edit Event</span>
                </button>
                
                <button 
                  v-if="selectedMobileEvent && selectedMobileEvent.status !== 'cancelled'"
                  @click="handleMobileAction('cancel')"
                  class="w-full flex items-center px-3 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-150 active:scale-[0.98]"
                >
                  <svg class="h-4 w-4 text-orange-600 dark:text-orange-400 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-sm text-orange-600 dark:text-orange-400">Cancel Event</span>
                </button>
                
                <button 
                  @click="handleMobileAction('delete')"
                  class="w-full flex items-center px-3 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-150 active:scale-[0.98]"
                >
                  <svg class="h-4 w-4 text-red-600 dark:text-red-400 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  <span class="text-sm text-red-600 dark:text-red-400">Delete Event</span>
                </button>
              </template>
            </div>
            
            <!-- Cancel Button -->
            <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-700">
              <button 
                @click="closeMobileActionsModal"
                class="w-full py-1.5 text-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors duration-200"
              >
                Cancel
              </button>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </Teleport>
</template>
<script>
import { computed, ref, onMounted, onUnmounted, nextTick } from 'vue';
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
  
  setup(props, { emit }) {
    // Dropdown state management
    const activeDropdownEvent = ref(null);
    const dropdownPosition = ref({ top: 0, left: 0 });
    const dropdownButtonEl = ref(null);
    const dropdownRef = ref(null);
    const dropdownDirection = ref('down');
    
    // Mobile modal state
    const showMobileActionsModal = ref(false);
    const selectedMobileEvent = ref(null);
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
          // Filter out today's events from upcoming events AND filter out cancelled events
          return new Date(event.start_date) >= now && 
                 eventStartDate !== today && 
                 event.status !== 'cancelled';
        })
        .sort((a, b) => new Date(a.start_date) - new Date(b.start_date))
        .slice(0, 5);
    });
    
    function formatDate(dateString, format) {
      return dayjs(dateString).format(format);
    }
    
    // Dropdown functionality
    const toggleDropdown = (event, clickEvent) => {
      if (window.innerWidth < 640) { // Mobile: show modal popup
        selectedMobileEvent.value = event;
        showMobileActionsModal.value = true;
        return;
      }
      // Desktop: floating dropdown
      if (activeDropdownEvent.value && activeDropdownEvent.value.id === event.id) {
        activeDropdownEvent.value = null;
        dropdownButtonEl.value = null;
        removeDropdownListeners();
      } else {
        activeDropdownEvent.value = event;
        dropdownButtonEl.value = clickEvent.currentTarget;
        updateDropdownPosition();
        addDropdownListeners();
      }
    };

    async function updateDropdownPosition() {
      if (!dropdownButtonEl.value) return;
      const rect = dropdownButtonEl.value.getBoundingClientRect();
      let dropdownWidth = 160;
      let left = rect.right - dropdownWidth + 8;
      if (left + dropdownWidth > window.innerWidth) left = window.innerWidth - dropdownWidth - 16;
      if (left < 16) left = 16;

      await nextTick();
      let dropdownHeight = dropdownRef.value ? dropdownRef.value.offsetHeight : 120;

      const spaceBelow = window.innerHeight - rect.bottom;
      const spaceAbove = rect.top;

      let top;
      if (spaceBelow >= dropdownHeight + 16) {
        top = rect.bottom + 2;
        dropdownDirection.value = 'down';
      } else if (spaceAbove >= dropdownHeight + 16) {
        top = rect.top - dropdownHeight - 2;
        dropdownDirection.value = 'up';
      } else if (spaceBelow >= spaceAbove) {
        top = rect.bottom + 2;
        dropdownDirection.value = 'down';
      } else {
        top = Math.max(8, rect.top - dropdownHeight - 2);
        dropdownDirection.value = 'up';
      }

      dropdownPosition.value = { top, left };
    }

    function addDropdownListeners() {
      window.addEventListener('scroll', updateDropdownPosition, true);
      window.addEventListener('resize', updateDropdownPosition);
    }

    function removeDropdownListeners() {
      window.removeEventListener('scroll', updateDropdownPosition, true);
      window.removeEventListener('resize', updateDropdownPosition);
    }

    const closeDropdowns = (event) => {
      if (!event.target.closest('.dropdown-container')) {
        activeDropdownEvent.value = null;
      }
    };

    const handleDropdownAction = (event, action) => {
      // Close dropdown
      activeDropdownEvent.value = null;
      
      // Handle specific actions
      switch(action) {
        case 'view':
          emit('view-event-details', event);
          break;
        case 'edit':
          emit('edit-event', event);
          break;
        case 'cancel':
          emit('cancel-event', event.id);
          break;
        case 'delete':
          emit('delete-event', event.id);
          break;
      }
    };

    // Mobile modal action handlers
    const handleMobileAction = (action) => {
      const event = selectedMobileEvent.value;
      if (!event) return;
      
      // Close mobile modal
      showMobileActionsModal.value = false;
      selectedMobileEvent.value = null;
      
      // Handle specific actions
      switch(action) {
        case 'view':
          emit('view-event-details', event);
          break;
        case 'edit':
          emit('edit-event', event);
          break;
        case 'cancel':
          emit('cancel-event', event.id);
          break;
        case 'delete':
          emit('delete-event', event.id);
          break;
      }
    };

    const closeMobileActionsModal = () => {
      showMobileActionsModal.value = false;
      selectedMobileEvent.value = null;
    };

    onMounted(() => {
      document.addEventListener('click', closeDropdowns);
    });

    onUnmounted(() => {
      document.removeEventListener('click', closeDropdowns);
      removeDropdownListeners();
    });
    
    return {
      todaysEvents,
      upcomingEventsFiltered,
      formatDate,
      // Dropdown
      activeDropdownEvent,
      dropdownPosition,
      dropdownButtonEl,
      dropdownRef,
      dropdownDirection,
      toggleDropdown,
      handleDropdownAction,
      // Mobile
      showMobileActionsModal,
      selectedMobileEvent,
      handleMobileAction,
      closeMobileActionsModal
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