<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';

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

const submit = () => {
  if (!validateForm()) return;

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

  if (props.isEdit) {
    emit('submitted', form.data());
  } else {
    form.clearErrors();
    form.post('/applications', {
      preserveScroll: true,
      onSuccess: () => {
        alert('Form submitted successfully!');
        emit('submitted', form.data());
      },
      onError: (errors) => {
        console.error('Form submission errors:', errors);
      }
    });
  }
};

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
    <div class="header text-center relative mb-6">
      <!-- Back Button positioned above LSPU logo -->
      <div style="position: absolute; top: -0.8cm; left: -2cm; z-index: 10;">
        <a href="/applications/select-form"
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
      <p class="text-sm font-bold mb-0">Republic of the Philippines</p>
      <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
      <p class="text-sm mb-0">Province of Laguna</p>
      <p class="text-lg font-bold mt-6 mb-2">Evaluation Sheet for all Programs/Activities</p>
    </div>
    <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block font-bold mb-1">Organization Name</label>
        <input v-model="form.organization_name" class="border p-2 w-full" required>
        <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
      </div>
      <!-- President Name field removed for Evaluation Form -->
      <div>
        <label class="block font-bold mb-1">Title of the Activity</label>
        <input v-model="form.activity_title" class="border p-2 w-full" required>
        <p v-if="errors.activity_title" class="text-red-500 text-sm mt-1">{{ errors.activity_title }}</p>
      </div>
      <div>
        <label class="block font-bold mb-1">Venue</label>
        <input v-model="form.venue" class="border p-2 w-full" required>
        <p v-if="errors.venue" class="text-red-500 text-sm mt-1">{{ errors.venue }}</p>
      </div>
      
      <!-- Date Range -->
      <div class="md:col-span-2">
        <label class="block font-bold mb-1">Date Range</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Start Date</label>
            <input v-model="form.date_start" type="date" class="border p-2 w-full" required>
            <p v-if="errors.date_start" class="text-red-500 text-sm mt-1">{{ errors.date_start }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">End Date (Optional)</label>
            <input v-model="form.date_end" type="date" :min="form.date_start" class="border p-2 w-full">
            <p v-if="errors.date_end" class="text-red-500 text-sm mt-1">{{ errors.date_end }}</p>
          </div>
        </div>
        <p class="text-sm text-gray-600 mt-1">
          <strong>Display:</strong> {{ formattedDateRange }}
        </p>
      </div>
      
      <!-- Time Range -->
      <div class="md:col-span-2">
        <label class="block font-bold mb-1">Time Range</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Start Time</label>
            <input v-model="form.time_start" type="time" class="border p-2 w-full" required>
            <p v-if="errors.time_start" class="text-red-500 text-sm mt-1">{{ errors.time_start }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">End Time (Optional)</label>
            <input v-model="form.time_end" type="time" class="border p-2 w-full">
            <p v-if="errors.time_end" class="text-red-500 text-sm mt-1">{{ errors.time_end }}</p>
          </div>
        </div>
        <p class="text-sm text-gray-600 mt-1">
          <strong>Display:</strong> {{ formattedTimeRange }}
        </p>
      </div>
    </div>
    
    <div class="mb-4">
      <p class="mb-1">Direction: Please put a check (✓) at the following statements with the corresponding rating scale.</p>
      <p class="mb-1 font-bold">Rating Scale:</p>
      <ul class="mb-2 ml-6">
        <li>Excellent - 5</li>
        <li>Very Satisfactory - 4</li>
        <li>Satisfactory - 3</li>
        <li>Fairly Satisfactory - 2</li>
        <li>Not Satisfactory - 1</li>
      </ul>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full border border-gray-400 mb-6">
        <thead>
          <tr class="bg-gray-100">
            <th class="border border-gray-400 px-2 py-1 w-12">#</th>
            <th class="border border-gray-400 px-2 py-1 text-left">Statement</th>
            <th class="border border-gray-400 px-2 py-1 w-32 text-center">Rating</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(statement, i) in statements" :key="i">
            <td class="border border-gray-400 px-2 py-1 text-center">{{ i + 1 }}</td>
            <td class="border border-gray-400 px-2 py-1">{{ statement }}</td>
            <td class="border border-gray-400 px-2 py-1 text-center">
              <input
                v-model="form.ratings[i]"
                type="text"
                inputmode="decimal"
                maxlength="3"
                class="border p-1 w-20 text-center"
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
      <label class="block font-bold mb-1">Comments & Suggestions:</label>
      <textarea
        v-model="form.comments_suggestions"
        class="border p-2 w-full"
        rows="5"
        placeholder="Enter each comment or suggestion on a new line. Each line will be a bullet point."
      ></textarea>
      <p class="text-gray-500 text-xs mt-1">Each line will be displayed as a separate bullet.</p>
    </div>
    <div class="flex justify-end mt-6">
      <button @click="submit" type="button" class="bg-green-500 text-white px-4 py-2 rounded mx-auto block">Submit</button>
    </div>
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
  font-weight: bold;
}
</style> 