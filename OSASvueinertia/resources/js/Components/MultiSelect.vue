<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
    </label>
    
    <!-- Trigger button -->
    <button
      type="button"
      @click="openModal"
      class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
    >
      <span v-if="selectedLabels.length === 0" class="text-gray-500">
        Select options...
      </span>
      <span v-else-if="selectedLabels.length === 1" class="text-gray-900">
        {{ selectedLabels[0] }}
      </span>
      <span v-else class="text-gray-900">
        {{ selectedLabels.length }} items selected
      </span>
      <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </span>
    </button>

    <!-- Modal -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

      <!-- Modal panel -->
      <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg md:max-w-md lg:max-w-lg xl:max-w-xl">
          <!-- Modal header -->
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                Select Users
              </h3>
              <button
                @click="closeModal"
                type="button"
                class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
              >
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Search input -->
            <div class="mb-4">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search users..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>

            <!-- Select all option -->
            <div class="flex items-center justify-between mb-3 p-2 bg-gray-50 rounded-md">
              <span class="text-sm font-medium text-gray-700">Select all</span>
              <div class="flex items-center space-x-3">
                <button
                  v-if="tempSelection.length > 0"
                  @click="clearAll"
                  type="button"
                  class="text-xs text-red-600 hover:text-red-800"
                >
                  Clear all
                </button>
                <input
                  type="checkbox"
                  :checked="isAllSelected"
                  @change="toggleSelectAll"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
              </div>
            </div>

            <!-- Users list -->
            <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md">
              <div v-if="filteredOptions.length === 0" class="p-4 text-center text-gray-500">
                No users found matching "{{ searchQuery }}"
              </div>
              <label
                v-for="option in filteredOptions"
                :key="option.value"
                class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
              >
                <input
                  type="checkbox"
                  :value="option.value"
                  :checked="modelValue.includes(option.value)"
                  @change="toggleOption(option.value)"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3"
                />
                <span class="text-sm text-gray-900">{{ option.label }}</span>
              </label>
            </div>

            <!-- Selected count -->
            <div class="mt-3 text-sm text-gray-600">
              {{ selectedCount }} of {{ options.length }} users selected
            </div>
          </div>

          <!-- Modal footer -->
          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <button
              @click="applySelection"
              type="button"
              class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Apply Selection
            </button>
            <button
              @click="closeModal"
              type="button"
              class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  label: String,
  id: String,
  options: {
    type: Array,
    required: true, // [{ value: 1, label: 'Student A' }, ...]
  },
  error: String,
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const tempSelection = ref([]);

// Computed properties
const selectedLabels = computed(() => {
  return props.options
    .filter(option => props.modelValue.includes(option.value))
    .map(option => option.label);
});

const filteredOptions = computed(() => {
  if (!searchQuery.value) {
    return props.options;
  }
  return props.options.filter(option =>
    option.label.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const isAllSelected = computed(() => {
  return filteredOptions.value.length > 0 && 
         filteredOptions.value.every(option => tempSelection.value.includes(option.value));
});

const selectedCount = computed(() => {
  return tempSelection.value.length;
});

// Methods
const openModal = () => {
  tempSelection.value = [...props.modelValue];
  isOpen.value = true;
  searchQuery.value = '';
};

const closeModal = () => {
  isOpen.value = false;
  searchQuery.value = '';
};

const applySelection = () => {
  emit('update:modelValue', [...tempSelection.value]);
  closeModal();
};

const toggleOption = (value) => {
  const index = tempSelection.value.indexOf(value);
  if (index > -1) {
    tempSelection.value.splice(index, 1);
  } else {
    tempSelection.value.push(value);
  }
};

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    // Deselect all filtered options
    tempSelection.value = tempSelection.value.filter(value => 
      !filteredOptions.value.some(option => option.value === value)
    );
  } else {
    // Select all filtered options
    filteredOptions.value.forEach(option => {
      if (!tempSelection.value.includes(option.value)) {
        tempSelection.value.push(option.value);
      }
    });
  }
};

const clearAll = () => {
  tempSelection.value = [];
};

// Close modal when pressing Escape
const handleKeydown = (event) => {
  if (event.key === 'Escape' && isOpen.value) {
    closeModal();
  }
};

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown);
});
</script>
