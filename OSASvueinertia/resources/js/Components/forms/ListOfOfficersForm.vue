<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  initialFormData: {
    type: Object,
    default: () => ({})
  }
});

// Add errors ref object
const errors = ref({});

// Add a function to add a new empty officer
const addOfficer = () => {
    form.officers.push({
        student_name: '',
        position: '',
        student_number: '',
        photo_path: null,
        photo_preview: null
    });
};

// Add a function to remove an officer
const removeOfficer = (index) => {
    // Clean up object URL if it exists
    if (form.officers[index].photo_preview) {
        URL.revokeObjectURL(form.officers[index].photo_preview);
    }
    form.officers.splice(index, 1);
};

const emit = defineEmits(['submitted']);

const form = useForm({
  form_type: 'LSPU-OSAS-SF-007',
  organization_name: props.initialFormData.organization_name || '',
  president_name: props.initialFormData.president_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',

  academic_year_end: props.initialFormData.academic_year_end || '',
  academic_year_start: props.initialFormData.academic_year_start || '',
  officers: [],
});

const handlePhotoUpload = (event, index, type = 'officers') => {
    const file = event.target.files[0];
    if (file) {
        if (type === 'officers') {
            // Clean up previous object URL if it exists
            if (form.officers[index].photo_preview) {
                URL.revokeObjectURL(form.officers[index].photo_preview);
            }
            // Create a temporary URL for preview in the form
            form.officers[index].photo_preview = URL.createObjectURL(file);
            // Store the actual file for upload
            form.officers[index].photo_path = file;
        }
    }
};

// Helper function to get photo preview URL
const getPhotoPreview = (officer) => {
    if (officer.photo_preview) {
        return officer.photo_preview;
    }
    if (officer.photo_path && typeof officer.photo_path === 'object') {
        return URL.createObjectURL(officer.photo_path);
    }
    return null;
};

// Initialize with data from props if available
if (props.initialFormData?.officers && props.initialFormData.officers.length > 0) {
  // Copy members from initialFormData
  form.officers = [...props.initialFormData.officers.map(officer => ({
    ...officer,
    photo_preview: null
  }))];
} else {
  // Add default empty members
  for(let i = 0; i < 4; i++) {
    addOfficer();
  }
}

// Validation function
const validateForm = () => {
  errors.value = {};
  
  // Validate main form fields
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization name is required';
  }
  
  if (!form.academic_year_start.trim()) {
    errors.value.academic_year_start = 'Academic year start is required';
  }
  
  if (!form.academic_year_end.trim()) {
    errors.value.academic_year_end = 'Academic year end is required';
  }
  
  if (!form.president_name.trim()) {
    errors.value.president_name = 'President name is required';
  }
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Faculty adviser name is required';
  }
  
  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator name is required';
  }
  
  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean/Assoc. Dean name is required';
  }
  
  // Validate officers
  form.officers.forEach((officer, index) => {
    if (!officer.student_name.trim()) {
      errors.value[`officer_${index}_name`] = 'Officer name is required';
    }
    
    if (!officer.position.trim()) {
      errors.value[`officer_${index}_position`] = 'Officer position is required';
    }
    
    if (!officer.student_number.trim()) {
      errors.value[`officer_${index}_student_number`] = 'Student I.D. number is required';
    }
  });
  
  // Return true if no errors
  return Object.keys(errors.value).length === 0;
};

const submit = () => {
  // Validate form before submitting
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
  <div class="form-container">
    <h2 class="text-lg font-bold mb-4">List of Officers Form</h2>
    
    <!-- Officer list preview -->
    <div class="preview-section border p-4 mb-6">
      <div class="header text-center mb-4">
        <div class="text-center font-bold">
          Republic of the Philippines<br>
          Laguna State Polytechnic University<br>
          Province of Laguna<br>
          <br>
          OFFICE OF STUDENT AFFAIRS AND SERVICES
        </div>
      </div>
      
      <div class="organization-details text-center mb-4">
        <p>Name of Organization: <span class="border-b border-black px-2">{{ form.organization_name }}</span></p>
        <p>A.Y. {{ form.academic_year_start }}-{{ form.academic_year_end }}</p>
      </div>
      
      <div class="list-title text-center font-bold mb-4">LIST OF OFFICERS</div>
      
      <!-- Officers list -->
      <div v-for="(officer, index) in form.officers" :key="index" class="officer-row flex mb-6">
        <div class="photo-box border border-black w-16 h-16 flex items-center justify-center mr-4 text-xs">
          <img v-if="getPhotoPreview(officer)" 
               :src="getPhotoPreview(officer)" 
               alt="Officer Photo" 
               class="w-full h-full object-cover">
          <span v-else>2X2</span>
        </div>
        <div class="officer-details flex-1">
          <div class="field-row flex mb-2">
            <div class="field-label w-24">Name</div>
            <div class="field-colon w-2 mr-1">:</div>
            <div class="field-value flex-1 border-b border-black">{{ officer.student_name || '' }}</div>
          </div>
          <div class="field-row flex mb-2">
            <div class="field-label w-24">Position</div>
            <div class="field-colon w-2 mr-1">:</div>
            <div class="field-value flex-1 border-b border-black">{{ officer.position || '' }}</div>
          </div>
          <div class="field-row flex mb-2">
            <div class="field-label w-24">Student I.D. No.</div>
            <div class="field-colon w-2 mr-1">:</div>
            <div class="field-value flex-1 border-b border-black">{{ officer.student_number || '' }}</div>
          </div>
          <div class="field-row flex mb-2">
            <div class="field-label w-24">Signature</div>
            <div class="field-colon w-2 mr-1">:</div>
            <div class="field-value flex-1 border-b border-black"></div>
          </div>
        </div>
      </div>
      
      <div class="footer mt-8 text-xs flex justify-between">
        <span>LSPU-OSAS-SF-007</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
      </div>
    </div>
    
    <!-- Form inputs for officers -->
    <div class="mt-8 border-t pt-6">
      <h3 class="text-lg font-bold mb-4">Form Details</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block font-bold">Organization Name</label>
          <input v-model="form.organization_name" class="border p-2 w-full" required>
          <div v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</div>
        </div>

        <div>
          <label class="block font-bold">Academic Year Start</label>
          <input v-model="form.academic_year_start" class="border p-2 w-full" placeholder="20__" required>
          <div v-if="errors.academic_year_start" class="text-red-500 text-sm mt-1">{{ errors.academic_year_start }}</div>
        </div>

        <div>
          <label class="block font-bold">Academic Year End</label>
          <input v-model="form.academic_year_end" class="border p-2 w-full" placeholder="20__" required>
          <div v-if="errors.academic_year_end" class="text-red-500 text-sm mt-1">{{ errors.academic_year_end }}</div>
        </div>

        <div>
          <label class="block font-bold">President Name</label>
          <input v-model="form.president_name" class="border p-2 w-full" required>
          <div v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</div>
        </div>

        <div>
          <label class="block font-bold">Faculty Adviser Name</label>
          <input v-model="form.adviser_name" class="border p-2 w-full" required>
          <div v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</div>
        </div>

        <div>
          <label class="block font-bold">Coordinator Name</label>
          <input v-model="form.coordinator_name" class="border p-2 w-full" required>
          <div v-if="errors.coordinator_name" class="text-red-500 text-sm mt-1">{{ errors.coordinator_name }}</div>
        </div>

        <div>
          <label class="block font-bold">Dean/Assoc. Dean Name</label>
          <input v-model="form.dean_name" class="border p-2 w-full" required>
          <div v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</div>
        </div>
      </div>

      <!-- Officer List Management -->
      <div class="mt-6">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-bold">Officers</h3>
          <button @click="addOfficer" type="button" class="bg-blue-500 text-white px-3 py-1 rounded">
            Add Officer
          </button>
        </div>

        <div v-for="(officer, index) in form.officers" :key="index" class="mt-4 p-4 border rounded">
          <div class="flex justify-between items-center mb-2">
            <h4 class="font-bold">Officer #{{ index + 1 }}</h4>
            <button @click="removeOfficer(index)" type="button" class="text-red-500">
              Remove
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-bold">Name</label>
              <input v-model="officer.student_name" class="border p-2 w-full" required>
              <div v-if="errors[`officer_${index}_name`]" class="text-red-500 text-sm mt-1">{{ errors[`officer_${index}_name`] }}</div>
            </div>

            <div>
              <label class="block font-bold">Position</label>
              <input v-model="officer.position" class="border p-2 w-full" required>
              <div v-if="errors[`officer_${index}_position`]" class="text-red-500 text-sm mt-1">{{ errors[`officer_${index}_position`] }}</div>
            </div>

            <div>
              <label class="block font-bold">Student I.D. No.</label>
              <input v-model="officer.student_number" class="border p-2 w-full" required>
              <div v-if="errors[`officer_${index}_student_number`]" class="text-red-500 text-sm mt-1">{{ errors[`officer_${index}_student_number`] }}</div>
            </div>

            <div>
              <label class="block font-bold">2x2 Photo</label>
              <input type="file" @change="event => handlePhotoUpload(event, index, 'officers')" class="border p-2 w-full" accept="image/*">
              <div v-if="getPhotoPreview(officer)" class="mt-2">
                <img :src="getPhotoPreview(officer)" alt="Preview" class="w-16 h-16 object-cover border">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 text-center">
        <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
      </div>
    </div>
  </div>
</template>