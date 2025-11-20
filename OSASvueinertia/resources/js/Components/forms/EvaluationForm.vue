<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useFormAutoSave } from '@/Composables/useFormAutoSave';
import SubmissionConfirmationModal from '@/Components/SubmissionConfirmationModal.vue';

const props = defineProps({
  initialFormData: {
    type: Object,
    default: () => ({})
  },
  isEdit: {
    type: Boolean,
    default: false
  }
});

const backHref = computed(() => props.isEdit ? '/applications' : '/applications/select-form');

const emit = defineEmits(['submitted']);

const statements = [
  'The activity is well planned and organized.',
  'The time allocation for various activity adequate.',
  'There is a smooth interpersonal relationship among the participants.',
  'The trust and unity among the participants are prevalent.',
  'The objectives of those activity are attained.',
  'The session/activities are congruent with objectives.',
  'The venue is conducive for the activities.',
  'The activity venue is clean, orderly and properly ventilated.',
  'The resource speakers/facilitator/s are competent.',
  'The resource speakers are orderly in preparation.',
  'The resource speaker has successfully met the expectations and needs of the participants.',
  'The speaker/s manifest rapport with the participants.',
  'The various activity/ies is/are interesting and enjoyable.',
  'The officers are professional in dealing with the participants.',
  'The officers and other participants are prompt and enthusiastic enough in attending the training.'
];

const form = useForm({
  form_type: 'LSPU-OSAS-SF-EVAL',
  organization_name: props.initialFormData.organization_name || '',
  // president_name removed for Evaluation Form
  activity_title: '',
  venue: '',
  // Date range fields
  date_start: '',
  date_end: '',
  // Time range fields
  time_start: '',
  time_end: '',
  ratings: Array(statements.length).fill(''),
  comments_suggestions: '',
});

const errors = ref({});
const lastValidRatings = ref([...form.ratings]);

// Autosave state
const showRestorePrompt = ref(false);
const autosavedData = ref(null);

// Submission confirmation modal state
const showConfirmationModal = ref(false);

// Initialize autosave (disabled by default)
const { isAutoSaving, enable: enableAutoSave, disable: disableAutoSave, start: startAutoSave, stop: stopAutoSave } = useFormAutoSave(form, 'evaluation', { enabled: false });

// If in edit mode, update form when initialFormData changes
watch(() => props.initialFormData, (newVal) => {
  if (newVal) {
    // Always update organization name from cached data (president_name removed)
    form.organization_name = newVal.organization_name || '';
    
    // Only populate activity-specific fields if we're in edit mode (editing an existing evaluation)
    if (props.isEdit) {
      form.activity_title = newVal.activity_title || '';
      form.venue = newVal.venue || '';
      form.date_start = newVal.date_start || '';
      form.date_end = newVal.date_end || '';
      form.time_start = newVal.time_start || '';
      form.time_end = newVal.time_end || '';
      
      // Handle ratings - ensure they are strings in the correct format
      if (Array.isArray(newVal.ratings) && newVal.ratings.length === statements.length) {
        form.ratings = newVal.ratings.map(rating => {
          if (rating === null || rating === undefined || rating === '') {
            return '';
          }
          // Convert to string and ensure proper format
          let val = rating.toString();
          if (/^[1-5]$/.test(val)) {
            val = val + '.0';
          }
          if (/^[1-4]$/.test(val[0])) {
            const decimal = val.includes('.') ? val.split('.')[1] : '0';
            val = `${val[0]}.${decimal}`;
          } else if (val[0] === '5') {
            val = '5.0';
          }
          return val;
        });
      } else {
        form.ratings = Array(statements.length).fill('');
      }
      
      form.comments_suggestions = newVal.comments_suggestions || '';
      lastValidRatings.value = [...form.ratings];
    }
  }
}, { immediate: true });

// Computed properties to format date and time ranges for display
const formattedDateRange = computed(() => {
  if (!form.date_start) return '';
  if (!form.date_end || form.date_start === form.date_end) {
    return formatDate(form.date_start);
  }
  return `${formatDate(form.date_start)} - ${formatDate(form.date_end)}`;
});

const formattedTimeRange = computed(() => {
  if (!form.time_start) return '';
  if (!form.time_end || form.time_start === form.time_end) {
    return formatTime(form.time_start);
  }
  return `${formatTime(form.time_start)} - ${formatTime(form.time_end)}`;
});

// Helper functions to format date and time
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatTime = (timeString) => {
  if (!timeString) return '';
  const [hours, minutes] = timeString.split(':');
  const hour = parseInt(hours);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const displayHour = hour % 12 || 12;
  return `${displayHour}:${minutes} ${ampm}`;
};

const validateForm = () => {
  errors.value = {};
  let isValid = true;
  
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
    isValid = false;
  }
  // president_name validation removed for Evaluation Form
  if (!form.activity_title.trim()) {
    errors.value.activity_title = 'Title of the Activity is required';
    isValid = false;
  }
  if (!form.venue.trim()) {
    errors.value.venue = 'Venue is required';
    isValid = false;
  }
  if (!form.date_start) {
    errors.value.date_start = 'Start Date is required';
    isValid = false;
  }
  if (!form.time_start) {
    errors.value.time_start = 'Start Time is required';
    isValid = false;
  }
  
  // Validate date range
  if (form.date_start && form.date_end && form.date_start > form.date_end) {
    errors.value.date_end = 'End date must be after start date';
    isValid = false;
  }
  
  // Validate time range
  if (form.time_start && form.time_end && form.time_start > form.time_end) {
    errors.value.time_end = 'End time must be after start time';
    isValid = false;
  }
  
  // Updated rating validation
  form.ratings.forEach((r, i) => {
    if (!r) {
      errors.value[`rating_${i}`] = 'Required';
      isValid = false;
    } else {
      let val = r.toString();
      // Convert single digit to X.0 format
      if (/^[1-5]$/.test(val)) {
        val = val + '.0';
        form.ratings[i] = val;
      }
      // Validate format and range
      if (!/^(?:[1-4]\.[0-9]|5\.0)$/.test(val)) {
        errors.value[`rating_${i}`] = 'Must be between 1.0 and 5.0';
        isValid = false;
      }
      // Convert to numeric for range validation
      const numVal = parseFloat(val);
      if (numVal < 1.0 || numVal > 5.0) {
        errors.value[`rating_${i}`] = 'Must be between 1.0 and 5.0';
        isValid = false;
      }
    }
  });
  return isValid;
};

const handleSubmitClick = () => {
  if (!validateForm()) return;
  showConfirmationModal.value = true;
};

const handleConfirmSubmit = () => {
  showConfirmationModal.value = false;
  submit();
};

const handleCancelSubmit = () => {
  showConfirmationModal.value = false;
};

const submit = () => {
  // Transform the form data before submission to ensure ratings are strings
  form.transform(data => {
    return {
      ...data,
      ratings: data.ratings.map(rating => {
        if (!rating) return '';
        let val = rating.toString();
        // Convert single digit to X.0 format
        if (/^[1-5]$/.test(val)) {
          val = val + '.0';
        }
        // Ensure proper decimal format
        if (/^[1-4]$/.test(val[0])) {
          const decimal = val.includes('.') ? val.split('.')[1] : '0';
          val = `${val[0]}.${decimal}`;
        } else if (val[0] === '5') {
          val = '5.0';
        }
        return val;
      })
    };
  });

  // Stop autosave before submission
  stopAutoSave();

  if (props.isEdit) {
    emit('submitted', form.data());
  } else {
    form.clearErrors();
    form.post('/applications', {
      preserveScroll: true,
      onSuccess: async () => {
          // Delete autosaved data after successful submission
          try {
            await fetch('/delete-autosaved-form-data', {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              },
              body: JSON.stringify({ form_type: 'evaluation' })
            });
          } catch (error) {
            console.error('Failed to delete autosaved data:', error);
          }
          emit('submitted', form.data());
        },
      onError: (errors) => {
        console.error('Form submission errors:', errors);
      }
    });
  }
};

// Autosave functions
const restoreAutosave = () => {
  if (autosavedData.value) {
    // Preserve date/time fields if present
    const currentDateStart = form.date_start;
    const currentDateEnd = form.date_end;
    const currentTimeStart = form.time_start;
    const currentTimeEnd = form.time_end;

    Object.assign(form, autosavedData.value);

    // Restore preserved fields
    form.date_start = currentDateStart || form.date_start;
    form.date_end = currentDateEnd || form.date_end;
    form.time_start = currentTimeStart || form.time_start;
    form.time_end = currentTimeEnd || form.time_end;

    // Ensure ratings length matches statements
    if (!Array.isArray(form.ratings) || form.ratings.length !== statements.length) {
      form.ratings = Array(statements.length).fill('');
    }

    showRestorePrompt.value = false;
    enableAutoSave();
    startAutoSave();
  }
};

const dismissAutosave = async () => {
  showRestorePrompt.value = false;
  enableAutoSave();
  startAutoSave();

  try {
    await fetch('/delete-autosaved-form-data', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ form_type: 'evaluation' })
    });
  } catch (error) {
    console.error('Failed to delete autosaved data:', error);
  }
};

// Lifecycle hooks
onMounted(async () => {
  try {
    const response = await fetch('/get-autosaved-form-data?form_type=evaluation', {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      }
    });

    if (response.ok) {
      const data = await response.json();
      if (data.form_data) {
        autosavedData.value = data.form_data;
        const autosavedTimestamp = new Date(data.updated_at).getTime();
        const initializedTimestamp = props.initialFormData?.updated_at ? new Date(props.initialFormData.updated_at).getTime() : 0;
        if (autosavedTimestamp > initializedTimestamp) {
          showRestorePrompt.value = true;
        } else {
          enableAutoSave();
          startAutoSave();
        }
      } else {
        enableAutoSave();
        startAutoSave();
      }
    } else {
      enableAutoSave();
      startAutoSave();
    }
  } catch (error) {
    console.error('Failed to fetch autosaved data:', error);
    enableAutoSave();
    startAutoSave();
  }
});

onUnmounted(() => {
  stopAutoSave();
});

const onRatingKeyPress = (e, i) => {
  const val = e.target.value;
  const char = e.key;

  // Only allow 3 characters
  if (val.length >= 3) {
    e.preventDefault();
    return;
  }

  // First character
  if (val.length === 0) {
    if (!/[1-5]/.test(char)) e.preventDefault();
    return;
  }

  // Second character
  if (val.length === 1) {
    if (char !== '.') e.preventDefault();
    return;
  }

  // Third character
  if (val.length === 2) {
    if (val[0] === '5') {
      if (char !== '0') e.preventDefault();
    } else {
      if (!/[0-9]/.test(char)) e.preventDefault();
    }
    return;
  }
};
</script>

<template>
  <div class="mt-6 form-content">
    <!-- Restore Autosave Prompt Modal -->
    <div v-if="showRestorePrompt" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="font-family: system-ui, -apple-system, sans-serif;">
      <div class="bg-white rounded-lg p-6 max-w-md shadow-xl">
        <h3 class="text-lg font-semibold mb-4">Restore Autosaved Data?</h3>
        <p class="text-gray-600 mb-6">We found an autosaved version of this evaluation form. Would you like to restore it?</p>
        <div class="flex gap-3 justify-end">
          <button 
            @click="dismissAutosave"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors"
          >
            Dismiss
          </button>
          <button 
            @click="restoreAutosave"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
          >
            Restore
          </button>
        </div>
      </div>
    </div>
    <div class="header text-center relative mb-6">
      <!-- Back Button positioned above LSPU logo -->
        <div style="position: absolute; top: -0.8cm; left: -2cm; z-index: 10;">
          <a :href="backHref"
           class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
           style="font-family: system-ui, -apple-system, sans-serif;">
          <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Back
        </a>
      </div>
      <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
  <p class="text-sm mb-0 calibri">Republic of the Philippines</p>
      <p class="text-base font-normal university-name mb-0" >Laguna State Polytechnic University</p>
  <p class="text-sm mb-0 calibri">Province of Laguna</p>
  <p class="text-lg font-bold mt-6 mb-2 calibri">Evaluation Sheet for all Programs/Activities</p>
    </div>
    
    <!-- Preview Section -->
    <div class="mb-6 calibri-11">
      <div class="mb-2">
        <span class="font-bold">Title of the Activity:</span>
        <span class="ml-2">{{ form.activity_title || 'Not specified' }}</span>
      </div>
      <div class="mb-2">
        <span class="font-bold">Venue:</span>
        <span class="ml-2">{{ form.venue || 'Not specified' }}</span>
      </div>
      <div class="mb-2">
        <span class="font-bold">Date:</span>
        <span class="ml-2">{{ formattedDateRange || 'Not specified' }}</span>
      </div>
      <div class="mb-6">
        <span class="font-bold">Time:</span>
        <span class="ml-2">{{ formattedTimeRange || 'Not specified' }}</span>
      </div>
    </div>
    
    <div class="mb-4 calibri-11">
      <p class="mb-1">Direction: Please put a check (✓) at the following statements with the corresponding rating scale.</p>
  <p class="mb-1 mt-10px">Rating Scale:</p>
      <ul class="mb-2 ml-6 rating-indent-50 rating-gap-10 rating-item-gap">
        <li>Excellent - 5</li>
        <li>Very Satisfactory - 4</li>
        <li>Satisfactory - 3</li>
        <li>Fairly Satisfactory - 2</li>
        <li>Not Satisfactory - 1</li>
      </ul>
    </div>
    <div class="overflow-x-auto calibri">
  <table class="w-full border border-gray-400 mb-4" style="border-collapse: collapse;">
        <thead>
          <tr class="bg-gray-100">
            <th class="border-t border-b border-l border-gray-400 px-2 py-0 w-12"></th>
            <th class="border-t border-b border-r border-gray-400 px-2 py-0 text-left"></th>
            <th class="border border-gray-400 px-2 py-0 w-32 text-center">Average</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(statement, i) in statements" :key="i">
            <td class="border-t border-b border-l border-gray-400 px-2 py-0 text-center">{{ i + 1 }}.</td>
            <td class="border-t border-b border-r border-gray-400 px-2 py-0">{{ statement }}</td>
            <td class="border border-gray-400 px-2 py-0 text-center">
              <input
                v-model="form.ratings[i]"
                type="text"
                inputmode="decimal"
                maxlength="3"
                class="border p-0.5 h-6 w-20 text-center"
                @keypress="e => onRatingKeyPress(e, i)"
                @input="e => {
                  let val = e.target.value;
                  // If plain 1-5, convert to X.0
                  if (/^[1-5]$/.test(val)) {
                    val = val + '.0';
                  }
                  // If value starts with 5. and is not 5.0, revert
                  if (val.startsWith('5.') && val !== '5.0') {
                    form.ratings[i] = lastValidRatings.value[i] || '';
                    return;
                  }
                  // Only allow 1.0-1.9, 2.0-2.9, 3.0-3.9, 4.0-4.9, 5.0
                  if (/^(?:[1-4]\.[0-9]|5\.0)$/.test(val)) {
                    lastValidRatings.value[i] = val;
                    form.ratings[i] = val;
                  } else {
                    form.ratings[i] = lastValidRatings.value[i] || '';
                  }
                }"
                required
              >
              <span v-if="errors[`rating_${i}`]" class="text-red-500 text-xs block mt-1">{{ errors[`rating_${i}`] }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Comments & Suggestions Section -->
    <div class="mb-4">
      <label class="block font-bold mb-1 calibri">Comments & Suggestions:</label>
      <textarea
        v-model="form.comments_suggestions"
        class="border p-2 w-full calibri"
        rows="5"
        placeholder="Enter each comment or suggestion on a new line. Each line will be a bullet point."
      ></textarea>
      <p class="text-gray-500 text-xs mt-1 calibri">Each line will be displayed as a separate bullet.</p>
    </div>
    
    <!-- Form Details Section -->
    <div class="mt-8 border-t pt-6">
      <h3 class="text-lg font-bold mb-4">Form Details</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block font-bold mb-1">Organization Name</label>
          <input v-model="form.organization_name" class="border p-2 w-full rounded-md" required>
          <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
        </div>
        
        <div>
          <label class="block font-bold mb-1">Title of the Activity</label>
          <input v-model="form.activity_title" class="border p-2 w-full rounded-md" required>
          <p v-if="errors.activity_title" class="text-red-500 text-sm mt-1">{{ errors.activity_title }}</p>
        </div>
        
        <div>
          <label class="block font-bold mb-1">Venue</label>
          <input v-model="form.venue" class="border p-2 w-full rounded-md" required>
          <p v-if="errors.venue" class="text-red-500 text-sm mt-1">{{ errors.venue }}</p>
        </div>
        
        <!-- Date Range -->
        <div>
          <label class="block font-bold mb-1">Date Range</label>
          <div class="flex items-end gap-3 flex-wrap">
            <div class="flex flex-col">
              <label class="block text-sm font-medium mb-1">Start Date</label>
              <input v-model="form.date_start" type="date" class="border p-1 w-36 rounded-md" required>
              <p v-if="errors.date_start" class="text-red-500 text-sm mt-1">{{ errors.date_start }}</p>
            </div>
            <div class="flex flex-col">
              <label class="block text-sm font-medium mb-1">End Date (Optional)</label>
              <input v-model="form.date_end" type="date" :min="form.date_start" class="border p-1 w-36 rounded-md">
              <p v-if="errors.date_end" class="text-red-500 text-sm mt-1">{{ errors.date_end }}</p>
            </div>
          </div>
          <p class="text-sm text-gray-600 mt-1">
            <strong>Display:</strong> {{ formattedDateRange }}
          </p>
        </div>
        
        <!-- Time Range -->
        <div>
          <label class="block font-bold mb-1">Time Range</label>
          <div class="flex items-end gap-3 flex-wrap">
            <div class="flex flex-col">
              <label class="block text-sm font-medium mb-1">Start Time</label>
              <input v-model="form.time_start" type="time" class="border p-1 w-28 rounded-md" required>
              <p v-if="errors.time_start" class="text-red-500 text-sm mt-1">{{ errors.time_start }}</p>
            </div>
            <div class="flex flex-col">
              <label class="block text-sm font-medium mb-1">End Time (Optional)</label>
              <input v-model="form.time_end" type="time" class="border p-1 w-28 rounded-md">
              <p v-if="errors.time_end" class="text-red-500 text-sm mt-1">{{ errors.time_end }}</p>
            </div>
          </div>
          <p class="text-sm text-gray-600 mt-1">
            <strong>Display:</strong> {{ formattedTimeRange }}
          </p>
        </div>
      </div>

      <div class="mt-6 text-center">
        <!-- Autosave indicator -->
        <div v-if="isAutoSaving" class="mb-3 text-sm text-gray-500 flex items-center justify-center gap-2">
          <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>Saving...</span>
        </div>
        <div v-else-if="!isEdit" class="mb-3 text-sm text-green-600 flex items-center justify-center gap-2">
          <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span>Draft saved</span>
        </div>

        <button
          @click="handleSubmitClick"
          type="button"
          class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:from-green-600 active:to-green-700 transition-all duration-300 relative overflow-hidden group"
          style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif;"
        >
          <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
          <span>{{ props.isEdit ? 'Update' : 'Submit' }}</span>
          <!-- Conditional icons: Update vs Create -->
          <svg v-if="props.isEdit" xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#e3e3e3" class="ml-2" aria-hidden="true">
            <path d="M480-120q-75 0-140.5-28.5t-114-77q-48.5-48.5-77-114T120-480q0-75 28.5-140.5t77-114q48.5-48.5 114-77T480-840q82 0 155.5 35T760-706v-94h80v240H600v-80h110q-41-56-101-88t-129-32q-117 0-198.5 81.5T200-480q0 117 81.5 198.5T480-200q105 0 183.5-68T756-440h82q-15 137-117.5 228.5T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z"/>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#e3e3e3" class="ml-2" aria-hidden="true">
            <path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Submission Confirmation Modal -->
    <SubmissionConfirmationModal 
      :show="showConfirmationModal"
      :isEdit="isEdit"
      @confirm="handleConfirmSubmit"
      @cancel="handleCancelSubmit"
    />
  </div>
</template>

<style scoped>
.form-content {
  font-family: 'Times New Roman', Times, serif;
  font-size: 11pt;
  line-height: 1.2;
}
.university-name {
  font-family: 'Old English Text MT', 'Times New Roman', serif;
  font-weight: normal;
}

.calibri {
  font-family: Calibri, 'Helvetica Neue', Arial, sans-serif;
}

.calibri-11 {
  font-family: Calibri, 'Helvetica Neue', Arial, sans-serif;
  font-size: 11pt;
}

.mt-10px {
  margin-top: 30px;
}

.rating-indent-50 {
  margin-left: 90px;
}

.rating-gap-10 {
  margin-top: 10px;
}

.rating-item-gap li {
  margin-bottom: 5px;
}

/* Ensure table content in the calibri container uses Calibri */
.calibri table, .calibri table th, .calibri table td, .calibri table input {
  font-family: Calibri, 'Helvetica Neue', Arial, sans-serif;
}
</style> 