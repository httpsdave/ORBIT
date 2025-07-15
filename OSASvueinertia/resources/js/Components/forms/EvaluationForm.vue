<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
  organization_name: '', // required
  president_name: '', // required
  activity_title: '',
  venue: '',
  // Date range fields
  date_start: '',
  date_end: '',
  // Time range fields
  time_start: '',
  time_end: '',
  ratings: Array(statements.length).fill(''),
});

const errors = ref({});

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
  if (!form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
    isValid = false;
  }
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
  
  form.ratings.forEach((r, i) => {
    if (!r) {
      errors.value[`rating_${i}`] = 'Required';
      isValid = false;
    }
  });
  return isValid;
};

const submit = () => {
  if (!validateForm()) return;
  
  // Format the date and time ranges for storage
  const submitData = {
    ...form.data(),
    date: formattedDateRange.value,
    time: formattedTimeRange.value,
  };
  
  form.post('/applications', {
    data: submitData,
    onSuccess: () => {
      alert('Form submitted successfully!');
      emit('submitted', submitData);
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors);
    }
  });
};
</script>

<template>
  <div class="mt-6 form-content max-w-3xl mx-auto bg-white p-8 rounded shadow">
    <div class="header text-center relative mb-6">
      <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[120px] h-auto">
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
      <div>
        <label class="block font-bold mb-1">President Name</label>
        <input v-model="form.president_name" class="border p-2 w-full" required>
        <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
      </div>
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
              <select v-model="form.ratings[i]" class="border p-1 w-20">
                <option value="">-</option>
                <option value="5">5</option>
                <option value="4">4</option>
                <option value="3">3</option>
                <option value="2">2</option>
                <option value="1">1</option>
              </select>
              <span v-if="errors[`rating_${i}`]" class="text-red-500 text-xs block mt-1">{{ errors[`rating_${i}`] }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="flex justify-end mt-6">
      <button @click="submit" type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
        Submit
      </button>
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