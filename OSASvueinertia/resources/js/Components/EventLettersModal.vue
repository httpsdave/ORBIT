<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div 
        v-if="showModal" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4"
        @click.self="closeModal"
      >
        <div 
          class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col"
          @click.stop
        >
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3">
              <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
                  Event Letter Submissions
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                  {{ eventLetters.length }} {{ eventLetters.length === 1 ? 'submission' : 'submissions' }} found
                </p>
              </div>
            </div>
            <button 
              @click="closeModal"
              class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            >
              <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center py-20">
            <div class="flex flex-col items-center space-y-4">
              <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
              <p class="text-gray-500 dark:text-gray-400">Loading event letters...</p>
            </div>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="flex items-center justify-center py-20">
            <div class="text-center">
              <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-gray-900 dark:text-gray-100 font-semibold mb-2">Failed to load event letters</p>
              <p class="text-gray-500 dark:text-gray-400 text-sm">{{ error }}</p>
              <button 
                @click="fetchEventLetters"
                class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
              >
                Try Again
              </button>
            </div>
          </div>

          <!-- Event Letters List -->
          <div v-else class="flex-1 overflow-y-auto p-4 sm:p-6">
            <!-- Empty State -->
            <div v-if="eventLetters.length === 0" class="text-center py-20">
              <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No Event Letters Found</h3>
              <p class="text-gray-500 dark:text-gray-400">There are no event letter submissions yet.</p>
            </div>

            <!-- Letters Grid/List -->
            <div v-else class="space-y-3">
              <div 
                v-for="letter in eventLetters" 
                :key="letter.id"
                class="relative bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 hover:shadow-lg transition cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-4 p-5"
                @click="viewDocument(letter)"
              >
                <!-- Organization Profile Photo -->
                <div class="flex-shrink-0">
                  <div v-if="letter.user?.profile_photo_url" class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200 dark:border-gray-600">
                    <img :src="letter.user.profile_photo_url" :alt="letter.user.name" class="w-full h-full object-cover" />
                  </div>
                  <div v-else class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                    {{ (letter.user?.name || 'O').charAt(0).toUpperCase() }}
                  </div>
                </div>

                <!-- Letter Details -->
                <div class="min-w-0 flex-1">
                  <div class="font-medium text-base text-gray-900 dark:text-gray-100">
                    <div class="flex items-center gap-2 min-w-0">
                      <span class="truncate flex-shrink-0">Event Letter</span>
                      <span class="inline-flex items-center text-sm text-gray-700 dark:text-gray-200 min-w-0" :title="`Organization: ${letter.user?.name || 'Unknown Organization'}`">
                        <svg class="mx-1 text-gray-400 dark:text-gray-500 w-2.5 h-2.5 flex-shrink-0" width="10" height="10" viewBox="0 0 10 10" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><polygon points="0,0 10,5 0,10"/></svg>
                        <span class="truncate max-w-[40ch]">{{ letter.user?.name || 'Unknown Organization' }}</span>
                      </span>
                    </div>
                  </div>
                  <div class="text-sm text-gray-500 dark:text-gray-400 font-medium truncate">LSPU-ACAD-RL</div>
                  <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">
                    <span><span class="font-semibold text-gray-700 dark:text-gray-200">Submitted:</span> {{ formatDate(letter.created_at) }}</span>
                    <span>&bull; <span :class="`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${getStatusClass(letter.status)}`">{{ letter.status }}</span></span>
                    <span v-if="letter.event_date" class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      {{ formatDate(letter.event_date) }}
                    </span>
                    <span v-if="letter.event_letter_path" class="text-green-600 dark:text-green-400 flex items-center gap-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      Document Available
                    </span>
                  </div>
                </div>

                <!-- Actions Dropdown Button -->
                <div class="flex-shrink-0 dropdown-container">
                  <button
                    @click.stop="toggleDropdown(letter.id, $event)"
                    :aria-label="`Actions for ${letter.user?.name || 'letter'}`"
                    class="relative inline-flex items-center justify-center rounded-full p-2 transition group text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-400"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <circle cx="10" cy="4" r="2.2"/>
                      <circle cx="10" cy="10" r="2.2"/>
                      <circle cx="10" cy="16" r="2.2"/>
                    </svg>
                    <span class="absolute left-1/2 -bottom-8 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
                      Actions
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700">
            <button 
              @click="closeModal"
              class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Dropdown Menu (Teleported to body for better positioning) -->
    <Teleport to="body">
      <div 
        v-if="activeDropdown"
        class="fixed z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-48"
        :style="{ top: `${dropdownPosition.top}px`, left: `${dropdownPosition.left}px` }"
        @click.stop
        ref="dropdownRef"
      >
        <button 
          v-if="activeDropdownLetter?.event_letter_path"
          @click="downloadDocument(activeDropdownLetter)"
          class="w-full text-left px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200 font-medium"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <span>Download PDF</span>
        </button>
      </div>
    </Teleport>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  showModal: {
    type: Boolean,
    required: true
  }
});

const emit = defineEmits(['close', 'error']);

const eventLetters = ref([]);
const loading = ref(false);
const error = ref(null);
const activeDropdown = ref(null);
const dropdownPosition = ref({ top: 0, left: 0 });
const dropdownButtonEl = ref(null);
const dropdownRef = ref(null);

const activeDropdownLetter = computed(() => {
  if (!activeDropdown.value) return null;
  return eventLetters.value.find(letter => letter.id === activeDropdown.value);
});

// Watch for modal open to fetch data
watch(() => props.showModal, (newValue) => {
  if (newValue) {
    fetchEventLetters();
  } else {
    activeDropdown.value = null;
  }
});

const fetchEventLetters = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await axios.get('/api/event-letters');
    eventLetters.value = response.data.data || [];
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load event letters';
    console.error('Error fetching event letters:', err);
  } finally {
    loading.value = false;
  }
};

const closeModal = () => {
  emit('close');
  activeDropdown.value = null;
};

const toggleDropdown = async (letterId, event) => {
  if (activeDropdown.value === letterId) {
    activeDropdown.value = null;
    dropdownButtonEl.value = null;
    removeDropdownListeners();
  } else {
    activeDropdown.value = letterId;
    dropdownButtonEl.value = event?.currentTarget || event?.target;
    await updateDropdownPosition();
    addDropdownListeners();
  }
};

async function updateDropdownPosition() {
  if (!dropdownButtonEl.value) return;
  
  const rect = dropdownButtonEl.value.getBoundingClientRect();
  let dropdownWidth = 192; // w-48 = 192px
  let left = rect.right - dropdownWidth + 8;
  
  if (left + dropdownWidth > window.innerWidth) {
    left = window.innerWidth - dropdownWidth - 16;
  }
  if (left < 16) left = 16;

  await nextTick();
  let dropdownHeight = dropdownRef.value ? dropdownRef.value.offsetHeight : 100;

  const spaceBelow = window.innerHeight - rect.bottom;
  const spaceAbove = rect.top;

  let top;
  if (spaceBelow >= dropdownHeight + 16) {
    top = rect.bottom + 2;
  } else if (spaceAbove >= dropdownHeight + 16) {
    top = rect.top - dropdownHeight - 2;
  } else if (spaceBelow >= spaceAbove) {
    top = rect.bottom + 2;
  } else {
    top = Math.max(8, rect.top - dropdownHeight - 2);
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
    activeDropdown.value = null;
    removeDropdownListeners();
  }
};

onMounted(() => {
  document.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdowns);
  removeDropdownListeners();
});

const getStatusClass = (status) => {
  const statusLower = status?.toLowerCase();
  switch(statusLower) {
    case 'approved':
      return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
    case 'pending':
      return 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300';
    case 'disapproved':
    case 'rejected':
      return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
    default:
      return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const viewDocument = (letter) => {
  if (letter.event_letter_path) {
    window.open(`/storage/${letter.event_letter_path}`, '_blank');
  }
};

const downloadDocument = (letter) => {
  if (letter.event_letter_path) {
    const link = document.createElement('a');
    link.href = `/storage/${letter.event_letter_path}`;
    link.download = `Event_Letter_${letter.user?.name || 'Document'}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    activeDropdown.value = null;
    removeDropdownListeners();
  }
};
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .bg-white,
.modal-fade-leave-active .bg-white {
  transition: transform 0.3s ease;
}

.modal-fade-enter-from .bg-white {
  transform: scale(0.95);
}

.modal-fade-leave-to .bg-white {
  transform: scale(0.95);
}
</style>
