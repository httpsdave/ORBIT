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
const submit = () => {
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
                    </div>

                    <div>
                        <label class="block font-bold">Academic Year Start</label>
                        <input v-model="form.academic_year_start" class="border p-2 w-full" required placeholder="23">
                    </div>

                    <div>
                        <label class="block font-bold">Academic Year End</label>
                        <input v-model="form.academic_year_end" class="border p-2 w-full" required placeholder="24">
                    </div>

                    <div>
                        <label class="block font-bold">President Name</label>
                        <input v-model="form.president_name" class="border p-2 w-full" required>
                    </div>

                    <div>
                        <label class="block font-bold">Secretary Name</label>
                        <input v-model="form.secretary_name" class="border p-2 w-full">
                    </div>

                    <div>
                        <label class="block font-bold">Adviser Name</label>
                        <input v-model="form.adviser_name" class="border p-2 w-full">
                    </div>

                    <div>
                        <label class="block font-bold">Dean Name</label>
                        <input v-model="form.dean_name" class="border p-2 w-full">
                    </div>

                    <div>
                        <label class="block font-bold">Coordinator Name</label>
                        <input v-model="form.coordinator_name" class="border p-2 w-full">
                    </div>

                    <div>
                        <label class="block font-bold">Director Name</label>
                        <input v-model="form.director_name" class="border p-2 w-full">
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
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input v-model="activity.name" class="w-full p-1" required>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <textarea v-model="activity.description" class="w-full p-1" rows="2" required></textarea>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input v-model="activity.persons_involved" class="w-full p-1" required>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input type="date" v-model="activity.target_date" class="w-full p-1" required>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input type="number" v-model="activity.budget" step="0.01" min="0" class="w-full p-1" required>
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