<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  initialFormData: {
    type: Object,
    default: () => ({})
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

const submit = () => {
  // Call validation before posting
  if (!validateForm()) {
    return;
  }

  form.post('/applications', {
    onSuccess: () => {
      alert('Form submitted successfully!');
      emit('submitted', form.data());
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors);
    }
  });
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
                    <th class="border border-gray-300 p-2 w-1/4">SIGNATURE</th>
                    <th class="border border-gray-300 p-2 w-16">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(attendee, index) in form.attendees" :key="index">
                    <td class="border border-gray-300 p-2 text-center">
                        {{ index + 1 }}.
                    </td>
                    <td class="border border-gray-300 p-2">
                        <input v-model="attendee.name" class="w-full p-1">
                    </td>
                    <td class="border border-gray-300 p-2">
                        <input v-model="attendee.course_year_section" class="w-full p-1">
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <!-- Placeholder for signature - in a real app this would be handled differently -->
                        <div class="h-8 border border-dashed border-gray-400 w-full"></div>
                    </td>
                    <td class="border border-gray-300 p-2">
                        <button type="button" @click="removeAttendee(index)" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <button type="button" @click="addAttendee" class="bg-blue-500 text-white px-3 py-1 rounded mb-4">
            Add Attendee Row
        </button>
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