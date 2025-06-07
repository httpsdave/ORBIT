<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  initialFormData: {
    type: Object,
    default: () => ({})
  }
});

const initialActivities = props.initialFormData?.activities || [];
// Add a function to add a new empty activity
const addActivity = () => {
    form.activities.push({
        objective: '',
        name: '',
        description: '',
        persons_involved: '',
        target_date: '',
        budget: 0
    });
};

// Add a function to remove an activity
const removeActivity = (index) => {
    form.activities.splice(index, 1);
};

const emit = defineEmits(['submitted']);

const form = useForm({
  form_type: 'LSPU-OSAS-SF-004',
  organization_name: props.initialFormData.organization_name || '',
  academic_year_start:props.initialFormData.academic_year_start || '',
  academic_year_end:props.initialFormData.academic_year_end || '',
  president_name: props.initialFormData.president_name || '',
  secretary_name:props.initialFormData.secretary_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
  activities: [],
});

// Initialize with data from props if available
if (props.initialFormData?.activities && props.initialFormData.activities.length > 0) {
  // Copy activities from initialFormData
  form.activities = [...props.initialFormData.activities];
} else {
  // Add default empty activities
  for (let i = 0; i < 3; i++) {
    addActivity();
  }
}

// Add errors ref object
const errors = ref({});

// Add validateForm function
const validateForm = () => {
  errors.value = {};
  let isValid = true;

  // Check main form required fields
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
    isValid = false;
  }

  if (!form.academic_year_start) {
    errors.value.academic_year_start = 'Academic Year Start is required';
    isValid = false;
  }

  if (!form.academic_year_end) {
    errors.value.academic_year_end = 'Academic Year End is required';
    isValid = false;
  }

  if (!form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
    isValid = false;
  }

  if (!form.secretary_name.trim()) {
    errors.value.secretary_name = 'Secretary Name is required';
    isValid = false;
  }

  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Adviser Name is required';
    isValid = false;
  }

  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean Name is required';
    isValid = false;
  }

  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator Name is required';
    isValid = false;
  }

  if (!form.director_name.trim()) {
    errors.value.director_name = 'Director Name is required';
    isValid = false;
  }

  // Check activities
  if (!errors.value.activities) {
    errors.value.activities = {};
  }

  form.activities.forEach((activity, index) => {
    if (!errors.value.activities[index]) {
      errors.value.activities[index] = {};
    }

    if (!activity.objective.trim()) {
      errors.value.activities[index].objective = 'Objective is required';
      isValid = false;
    }

    if (!activity.name.trim()) {
      errors.value.activities[index].name = 'Activity name is required';
      isValid = false;
    }

    if (!activity.description.trim()) {
      errors.value.activities[index].description = 'Description is required';
      isValid = false;
    }

    if (!activity.persons_involved.trim()) {
      errors.value.activities[index].persons_involved = 'Persons involved is required';
      isValid = false;
    }

    if (!activity.target_date) {
      errors.value.activities[index].target_date = 'Target date is required';
      isValid = false;
    }

    if (!activity.budget || activity.budget < 0) {
      errors.value.activities[index].budget = 'Budget is required and must be 0 or greater';
      isValid = false;
    }
  });

  return isValid;
};

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
                <div style="margin-top: 15px; text-decoration: underline;">{{ form.organization_name }}</div>
                <p class="text-sm font-bold form-title mt-4 mb-4">PLAN OF ACTIVITIES</p>
                <p class="text-sm mb-0">Semester AY {{ form.academic_year_start }}-{{ form.academic_year_end }}</p>
            </div>

            <!-- Form inputs -->
            <div class="mt-8">
                <h3 class="text-lg font-bold mb-4">Form Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold">Organization Name</label>
                        <input v-model="form.organization_name" class="border p-2 w-full" required>
                        <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Academic Year Start</label>
                        <input v-model="form.academic_year_start" class="border p-2 w-full" required placeholder="23">
                        <p v-if="errors.academic_year_start" class="text-red-500 text-sm mt-1">{{ errors.academic_year_start }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Academic Year End</label>
                        <input v-model="form.academic_year_end" class="border p-2 w-full" required placeholder="24">
                        <p v-if="errors.academic_year_end" class="text-red-500 text-sm mt-1">{{ errors.academic_year_end }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">President Name</label>
                        <input v-model="form.president_name" class="border p-2 w-full" required>
                        <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Secretary Name</label>
                        <input v-model="form.secretary_name" class="border p-2 w-full" required>
                        <p v-if="errors.secretary_name" class="text-red-500 text-sm mt-1">{{ errors.secretary_name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Adviser Name</label>
                        <input v-model="form.adviser_name" class="border p-2 w-full" required>
                        <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Dean Name</label>
                        <input v-model="form.dean_name" class="border p-2 w-full" required>
                        <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Coordinator Name</label>
                        <input v-model="form.coordinator_name" class="border p-2 w-full" required>
                        <p v-if="errors.coordinator_name" class="text-red-500 text-sm mt-1">{{ errors.coordinator_name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Director Name</label>
                        <input v-model="form.director_name" class="border p-2 w-full" required>
                        <p v-if="errors.director_name" class="text-red-500 text-sm mt-1">{{ errors.director_name }}</p>
                    </div>
                </div>

                <!-- Activities Table -->
                <div class="mt-6">
                    <h4 class="text-md font-bold mb-2">Activities</h4>
                    
                    <table class="w-full border-collapse border border-gray-300 mb-4">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 p-2">OBJECTIVE</th>
                                <th class="border border-gray-300 p-2">ACTIVITIES</th>
                                <th class="border border-gray-300 p-2">BRIEF DESCRIPTION</th>
                                <th class="border border-gray-300 p-2">PERSONS INVOLVED</th>
                                <th class="border border-gray-300 p-2">TARGET DATE</th>
                                <th class="border border-gray-300 p-2">BUDGET</th>
                                <th class="border border-gray-300 p-2">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(activity, index) in form.activities" :key="index">
                                <td class="border border-gray-300 p-2">
                                    <input v-model="activity.objective" class="w-full p-1" required>
                                    <p v-if="errors.activities?.[index]?.objective" class="text-red-500 text-xs mt-1">{{ errors.activities[index].objective }}</p>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input v-model="activity.name" class="w-full p-1" required>
                                    <p v-if="errors.activities?.[index]?.name" class="text-red-500 text-xs mt-1">{{ errors.activities[index].name }}</p>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <textarea v-model="activity.description" class="w-full p-1" rows="2" required></textarea>
                                    <p v-if="errors.activities?.[index]?.description" class="text-red-500 text-xs mt-1">{{ errors.activities[index].description }}</p>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input v-model="activity.persons_involved" class="w-full p-1" required>
                                    <p v-if="errors.activities?.[index]?.persons_involved" class="text-red-500 text-xs mt-1">{{ errors.activities[index].persons_involved }}</p>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input type="date" v-model="activity.target_date" class="w-full p-1" required>
                                    <p v-if="errors.activities?.[index]?.target_date" class="text-red-500 text-xs mt-1">{{ errors.activities[index].target_date }}</p>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input type="number" v-model="activity.budget" step="0.01" min="0" class="w-full p-1" required>
                                    <p v-if="errors.activities?.[index]?.budget" class="text-red-500 text-xs mt-1">{{ errors.activities[index].budget }}</p>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <button type="button" @click="removeActivity(index)" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <button type="button" @click="addActivity" class="bg-blue-500 text-white px-3 py-1 rounded mb-4">
                        Add Activity Row
                    </button>
                </div>

                <div class="mt-6 text-center">
                    <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
                </div>
            </div>

            <div class="footer mt-8 text-xs flex justify-between">
                <span>LSPU-OSAS-SF-004</span>
                <span>Rev. 1</span>
                <span>09 November 2020</span>
            </div>
        </div>

</template>