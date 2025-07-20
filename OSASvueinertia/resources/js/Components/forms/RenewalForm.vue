<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useFormAutoSave } from '@/Composables/useFormAutoSave';

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

const form = useForm({
  form_type: 'LSPU-OSAS-SF-002',
  organization_name: props.initialFormData.organization_name || '',
  college: props.initialFormData.college || '',
  academic_year_start: props.initialFormData.academic_year_start || '',
  academic_year_end: props.initialFormData.academic_year_end || '',
  president_name: props.initialFormData.president_name || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
});

// Initialize auto-save functionality
const { isAutoSaving } = useFormAutoSave(form, 'LSPU-OSAS-SF-002');

// Add errors ref object
const errors = ref({});

// Add validateForm function
const validateForm = () => {
  errors.value = {};
  let isValid = true;

  // Check required fields
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
    isValid = false;
  }

  if (!form.college.trim()) {
    errors.value.college = 'College is required';
    isValid = false;
  }

  if (!form.academic_year_start.trim()) {
    errors.value.academic_year_start = 'Academic Year Start is required';
    isValid = false;
  }

  if (!form.academic_year_end.trim()) {
    errors.value.academic_year_end = 'Academic Year End is required';
    isValid = false;
  }

  if (!form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
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

  return isValid;
};

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
        <p class="text-sm mb-0">Office of Student Affairs and Services</p>
        <p class="text-sm font-bold form-title mt-4 mb-4">RENEWAL FORM</p>
    </div>

    <div class="section text-left">
        <p class="mb-0"><strong>THE DIRECTOR/CHAIRPERSON</strong></p>
        <p class="mb-0">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
        <p class="mb-0">LSPU</p>
    </div>

    <div class="section">
        <p class="thru-line text-center italic my-2">Thru: The Coordinator, Student Organization Unit</p>
    </div>

    <div class="section">
        <p class="mb-1">Sir:</p>
        
        <p class="indented">The <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.organization_name }}</span> wishes to seek renewal of its recognition to function as a Student Organization in the College of <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.college }}</span> for Academic Year 20<span class="blank-line text-center border-b border-black inline-block" style="min-width: 50px">{{ form.academic_year_start }}</span>-20<span class="blank-line text-center border-b border-black inline-block" style="min-width: 50px">{{ form.academic_year_end }}</span>.</p>
        
        <p class="indented">In this connection, we respectfully request your good office to grant us permission to operate in our institution, subject to the existing rules & regulation of our University.</p>
        
        <p class="indented">Thank you very much.</p>
    </div>

    <div class="section text-right">
        <p class="mb-1">Very respectfully yours,</p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.president_name }}</span></p>
            <p class="mb-0">Organization President</p>
        </div>
    </div>

    <div class="section text-center">
        <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.organization_name }}</span></p>
        <p class="mb-0">Name of Organization</p>
    </div>

    <div class="section text-left">
        <p class="mb-1">Noted:</p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.adviser_name }}</span></p>
            <p class="mb-0">Adviser's Student Organization</p>
        </div>
    </div>

    <div class="section text-right">
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.dean_name }}</span></p>
            <p class="mb-0">Dean/Assoc. Dean of College</p>
        </div>
    </div>

    <div class="section text-center">
        <p class="mb-1">Recommending Approval:</p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.coordinator_name }}</span></p>
            <p class="mb-0">Coordinator, Student Organization Unit</p>
        </div>
    </div>

    <div class="section text-center">
        <p class="mb-1">Approved / Disapproved:</p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.director_name }}</span></p>
            <p class="mb-0">Chairperson, Office of Student Affairs and Services</p>
        </div>
    </div>

    <!-- Form inputs -->
    <form @submit.prevent="submit">
      <div class="mt-8 border-t pt-6">
          <h3 class="text-lg font-bold mb-4">Form Details</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                  <label class="block font-bold">Organization Name</label>
                  <input v-model="form.organization_name" class="border p-2 w-full" required>
                  <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
              </div>

              <div>
                  <label class="block font-bold">College</label>
                  <input v-model="form.college" class="border p-2 w-full" required>
                  <p v-if="errors.college" class="text-red-500 text-sm mt-1">{{ errors.college }}</p>
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
                  <label class="block font-bold">Chairperson Name</label>
                  <input v-model="form.director_name" class="border p-2 w-full" required>
                  <p v-if="errors.director_name" class="text-red-500 text-sm mt-1">{{ errors.director_name }}</p>
              </div>
          </div>

          <div class="mt-6 text-center">
              <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">{{ props.isEdit ? 'Update' : 'Submit' }}</button>
              
              <!-- Auto-save indicator -->
              <div v-if="isAutoSaving" class="mt-2 text-sm text-gray-600">
                  <span class="inline-flex items-center">
                      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Auto-saving...
                  </span>
              </div>
          </div>
      </div>
    </form>

    <div class="footer mt-8 text-xs flex justify-between">
        <span>LSPU-OSAS-SF-002</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
    </div>
</div>
</template>