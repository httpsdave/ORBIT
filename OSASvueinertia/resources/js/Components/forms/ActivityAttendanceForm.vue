<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

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

// Add errors ref object
const errors = ref({
  college: '',
  organization_name: '',
  president_name: ''
});

// Add a function to add a new attendee
const addAttendee = () => {
    form.attendees.push({
        name: '',
        course_year_section: '',
        signature: null
    });
};

// Add a function to remove an attendee
const removeAttendee = (index) => {
    form.attendees.splice(index, 1);
};

// Add validateForm function
const validateForm = () => {
  // Clear previous errors
  Object.keys(errors.value).forEach(key => {
    errors.value[key] = '';
  });

  let isValid = true;

  // Check each required field
  if (!form.college || form.college.trim() === '') {
    errors.value.college = 'College is required';
    isValid = false;
  }

  if (!form.organization_name || form.organization_name.trim() === '') {
    errors.value.organization_name = 'Organization Name is required';
    isValid = false;
  }

  if (!form.president_name || form.president_name.trim() === '') {
    errors.value.president_name = 'President Name is required';
    isValid = false;
  }

  return isValid;
};

const emit = defineEmits(['submitted']);

const form = useForm({
  form_type: 'LSPU-OSAS-SF-009',
  organization_name: props.initialFormData.organization_name || '',
  president_name: props.initialFormData.president_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
  
  college:props.initialFormData.college || '',
  activity_name:props.initialFormData.activity_name || '',
  activity_date:props.initialFormData.activity_date || '',
  attendees: [],

});

// Initialize with data from props if available
if (props.initialFormData?.attendees && props.initialFormData.attendees.length > 0) {
  // Copy attendees from initialFormData
  form.attendees = [...props.initialFormData.attendees];
} else {
  // Add default empty attendees
  for(let i = 0; i < 4; i++) {
                addAttendee();

  }
}

// Pagination state (must be after form is defined)
const attendeesPerPage = 10;
const currentPage = ref(1);

const totalPages = computed(() => {
    return Math.ceil(form.attendees.length / attendeesPerPage);
});

const paginatedAttendees = computed(() => {
    const start = (currentPage.value - 1) * attendeesPerPage;
    return form.attendees.slice(start, start + attendeesPerPage);
});

const goToPrevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};
const goToNextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

// Computed for visible page numbers with ellipsis
const visiblePages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const delta = 2;
    if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }
    const range = [];
    const rangeWithDots = [];
    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        range.push(i);
    }
    if (current - delta > 2) {
        rangeWithDots.push(1, '...');
    } else {
        rangeWithDots.push(1);
    }
    rangeWithDots.push(...range);
    if (current + delta < total - 1) {
        rangeWithDots.push('...', total);
    } else {
        rangeWithDots.push(total);
    }
    return rangeWithDots;
});

const goToPage = (page) => {
    if (typeof page === 'number' && page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// When attendees are added/removed, ensure currentPage is valid
watch(
    () => form.attendees.length,
    (newLen) => {
        if (currentPage.value > totalPages.value) {
            currentPage.value = totalPages.value || 1;
        }
    }
);

const submit = () => {
  if (!validateForm()) {
    return;
  }
  
  // Check if we're in edit mode
  if (props.isEdit) {
    // For edit mode, just emit the data - don't make HTTP request here
    emit('submitted', form.data());
  } else {
    // For create mode, make the POST request
    form.post('/applications', {
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
</script>

<template>
  <div class="mt-6 form-content">
    <div class="header text-center relative">
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
        <p class="text-sm font-bold mb-0">Republic of the Philippines</p>
        <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
        <p class="text-sm mb-0">Province of Laguna</p>
        <p class="text-sm font-bold mb-0 mt-4">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
        <p class="text-sm font-bold form-title mt-2 mb-2">STUDENT ACTIVITY ATTENDANCE SHEET</p>
    </div>

    <!-- Form inputs -->
    <div class="mt-6">
        <h3 class="text-lg font-bold mb-4">Form Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold">College</label>
                <input v-model="form.college" class="border p-2 w-full" required>
                <p v-if="errors.college" class="text-red-500 text-sm mt-1">{{ errors.college }}</p>
            </div>

            <div>
                <label class="block font-bold">Activity Name</label>
                <input v-model="form.activity_name" class="border p-2 w-full">
            </div>

            <div>
                <label class="block font-bold">Activity Date</label>
                <input type="date" v-model="form.activity_date" class="border p-2 w-full">
            </div>

            <div>
                <label class="block font-bold">Organization Name</label>
                <input v-model="form.organization_name" class="border p-2 w-full" required>
                <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
            </div>

            <div>
                <label class="block font-bold">President Name</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
                <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
            </div>
        </div>

    <!-- Attendees Table -->
    <div class="mt-6">
        <h4 class="text-md font-bold mb-2">Attendees</h4>
        
        <table class="w-full border-collapse border border-gray-300 mb-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 p-2 w-10">NO.</th>
                    <th class="border border-gray-300 p-2 w-1/2">NAME</th>
                    <th class="border border-gray-300 p-2 w-1/4">COURSE/YEAR & SECTION</th>
                    <th class="border border-gray-300 p-2 w-16">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(attendee, index) in paginatedAttendees" :key="(currentPage - 1) * attendeesPerPage + index">
                    <td class="border border-gray-300 p-2 text-center">
                        {{ (currentPage - 1) * attendeesPerPage + index + 1 }}.
                    </td>
                    <td class="border border-gray-300 p-2">
                        <input v-model="attendee.name" class="w-full p-1">
                    </td>
                    <td class="border border-gray-300 p-2">
                        <input v-model="attendee.course_year_section" class="w-full p-1">
                    </td>
                    <td class="border border-gray-300 p-2">
                        <button type="button" @click="removeAttendee((currentPage - 1) * attendeesPerPage + index)" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <button type="button" @click="addAttendee" class="bg-blue-500 text-white px-3 py-1 rounded mb-4">
            Add Attendee Row
        </button>
    </div>

    <!-- Pagination Controls -->
    <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mt-8 gap-4">
        <button 
            type="button"
            @click="goToPrevPage" 
            :disabled="currentPage === 1"
            class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
            Previous
        </button>
        <div class="flex gap-2">
            <button 
                v-for="page in visiblePages" 
                :key="page"
                @click="page === '...' ? null : goToPage(page)"
                :disabled="page === '...'"
                :class="[
                    'px-3 py-1 rounded',
                    page === '...'
                        ? 'text-gray-400 cursor-default'
                        : currentPage === page
                            ? 'bg-blue-600 text-white'
                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                ]">
                {{ page }}
            </button>
        </div>
        <button 
            type="button"
            @click="goToNextPage" 
            :disabled="currentPage === totalPages"
            class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
            Next
        </button>
    </div>
    <!-- Page Info -->
    <div v-if="totalPages > 1" class="text-center mt-4 text-sm text-gray-600">
        Page {{ currentPage }} of {{ totalPages }} • Attendees {{ (currentPage - 1) * attendeesPerPage + 1 }}-{{ Math.min(currentPage * attendeesPerPage, form.attendees.length) }} of {{ form.attendees.length }}
    </div>

    <div class="mt-6 text-center">
        <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
    </div>
</div>

<div class="footer mt-8 text-xs flex justify-between">
    <span>LSPU-OSAS-SF-009</span>
    <span>Rev. 0</span>
    <span>10 August 2016</span>
</div>
</div>

</template>