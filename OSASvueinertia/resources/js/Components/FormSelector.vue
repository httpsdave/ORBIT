<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import { useTheme } from '@/Composables/useTheme';

// Initialize theme
const { isDark } = useTheme();

const props = defineProps({
  formOptions: {
    type: Array,
    required: true
    // Example usage in parent:
    // [
    //   { value: 'LSPU-OSAS-SF-001', label: 'Recognition Form' },
    //   ...
    //   { value: 'LSPU-OSAS-SF-ACCOMPLISHMENT', label: 'Accomplishment Report' },
    //   { value: 'LSPU-OSAS-SF-NARRATIVE', label: 'Narrative Report' },
    //   { value: 'LSPU-OSAS-SF-BYLAWS', label: 'Constitution & By-laws' },
    //   { value: 'LSPU-OSAS-SF-FINANCIAL', label: 'Financial Report' },
    //   { value: 'LSPU-ACAD-RL', label: 'Event Letter' }, // NEW
    // ]
  },
  title: {
    type: String,
    default: 'Select a Form to Fill'
  }
});

const emit = defineEmits(['form-selected']);

const selectedForm = ref('');
const isOpen = ref(false);
const dropdownRef = ref(null);

const selectForm = (value) => {
  selectedForm.value = value;
  isOpen.value = false;
  emit('form-selected', value);
};

watch(selectedForm, (newValue) => {
  if (newValue) {
    emit('form-selected', newValue);
  }
});

const getSelectedLabel = () => {
  if (!selectedForm.value) return 'Choose a form';
  const option = props.formOptions.find(opt => opt.value === selectedForm.value);
  return option ? option.label : 'Choose a form';
};

// Keep hidden options in the prop but filter them out for rendering.
const hiddenValues = [
  'LSPU-OSAS-SF-FINANCIAL',
  'LSPU-OSAS-SF-NARRATIVE',
  'LSPU-OSAS-SF-ACCOMPLISHMENT'
];

const visibleOptions = computed(() => {
  if (!Array.isArray(props.formOptions)) return [];
  return props.formOptions.filter(opt => !hiddenValues.includes(opt?.value));
});

const closeDropdown = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});
</script>

<template>
  <div class="w-full max-w-md mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700" ref="dropdownRef">
    <!-- Animated Color Banner -->
    <div class="flex w-full overflow-hidden rounded-t-lg">
      <div class="w-1/4 h-1.5 bg-blue-500 animate-pulse" style="animation-delay: 0.2s;"></div>
      <div class="w-1/4 h-1.5 bg-green-500 animate-pulse" style="animation-delay: 0.4s;"></div>
      <div class="w-1/4 h-1.5 bg-yellow-500 animate-pulse" style="animation-delay: 0.6s;"></div>
      <div class="w-1/4 h-1.5 bg-red-500 animate-pulse" style="animation-delay: 0.8s;"></div>
    </div>

    <div class="p-4 sm:p-6">
      <h1 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-gray-100 text-center mb-4 sm:mb-6">{{ title }}</h1>

      <div class="relative">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Choose Form</label>

        <!-- Dropdown Button -->
        <button
          @click.stop="isOpen = !isOpen"
          type="button"
          class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-3 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 transition-all duration-150 hover:bg-gray-50 dark:hover:bg-gray-600"
        >
          <span class="block truncate" :class="[selectedForm ? 'text-gray-800 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400']">
            {{ getSelectedLabel() }}
          </span>
          <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <ChevronDown class="h-5 w-5 text-gray-400 dark:text-gray-500" aria-hidden="true" />
          </span>
        </button>

        <!-- Dropdown Menu -->
        <div
          v-show="isOpen"
          class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black dark:ring-gray-600 ring-opacity-5 dark:ring-opacity-25 overflow-auto focus:outline-none transition-all duration-150"
        >
          <div
            v-for="option in visibleOptions"
            :key="option?.value"
            @click="option && selectForm(option.value)"
            class="cursor-pointer select-none relative py-2.5 pl-3 pr-9 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors duration-150 text-gray-900 dark:text-gray-100"
            :class="{ 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300': selectedForm === option?.value }"
          >
              <div class="flex justify-between items-center">
                <span class="font-medium">{{ option?.label }}</span>
                <span v-if="option && !['LSPU-OSAS-SF-EVAL','LSPU-OSAS-SF-ACCOMPLISHMENT','LSPU-OSAS-SF-NARRATIVE','LSPU-OSAS-SF-FINANCIAL','LSPU-OSAS-SF-BYLAWS','LSPU-OSAS-SF-007'].includes(option.value)" class="text-xs text-gray-500 dark:text-gray-400 font-mono ml-2">{{ option.value }}</span>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
