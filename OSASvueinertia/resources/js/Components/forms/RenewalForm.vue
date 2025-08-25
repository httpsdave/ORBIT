<script setup>
// College options for the select dropdown
const collegeOptions = [
  'College of Computer Studies',
  'College of Arts and Sciences',
  'College of Engineering',
  'College of Industrial Technology',
  'College of Hospitality Management and Tourism',
  'College of Teacher Education',
  'College of Criminal Justice Education'
];

// Handler for college select change
function handleCollegeChange(e) {
  const selected = e.target.value;
  form.college = selected.replace('College of ', '');
}
import { ref, onUnmounted, computed } from 'vue';
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

// Compute current year and next year for placeholders
const currentYear = computed(() => {
  return new Date().getFullYear().toString().slice(-2);
});

const nextYear = computed(() => {
  return (new Date().getFullYear() + 1).toString().slice(-2);
});

// Computed property to format the date
const formattedDate = computed(() => {
  if (!form.application_date) return '';
  const date = new Date(form.application_date);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

const form = useForm({
  form_type: 'LSPU-OSAS-SF-002',
  organization_name: props.initialFormData.organization_name || '',
  college: props.initialFormData.college || '',
  application_date: props.initialFormData.application_date || new Date().toISOString().slice(0, 10),
  academic_year_start: props.initialFormData.academic_year_start || currentYear.value,
  academic_year_end: props.initialFormData.academic_year_end || nextYear.value,
  president_name: props.initialFormData.president_name || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
});

// Initialize auto-save functionality
const { isAutoSaving, autoSaveFormData, stop } = useFormAutoSave(form, 'LSPU-OSAS-SF-002');

// Add a flag to disable auto-save during submit
const isSubmitting = ref(false);
let autoSaveTimeout = null;

// Clean up auto-save watcher and timeout on unmount
onUnmounted(() => {
  if (autoSaveTimeout) clearTimeout(autoSaveTimeout);
});

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
  
  if (!form.application_date.trim()) {
    errors.value.application_date = 'Application Date is required';
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
  stop(); // Stop auto-save before submitting!
  isSubmitting.value = true;
  if (autoSaveTimeout) clearTimeout(autoSaveTimeout); // Stop any pending auto-save

  // Check if we're in edit mode
  if (props.isEdit) {
    // For edit mode, just emit the data - don't make HTTP request here
    emit('submitted', form.data());
    isSubmitting.value = false;
  } else {
    // For create mode, make the POST request
    form.post('/applications', {
      onSuccess: () => {
        emit('submitted', form.data());
        isSubmitting.value = false;
      },
      onError: (errors) => {
        console.error('Form submission errors:', errors);
        isSubmitting.value = false;
      }
    });
  }
};
</script>

<template>
  <div class="mt-6 form-content" style="font-size:11pt;">
    <div class="header text-center relative">
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
  <p class="calibri-font" style="font-size:11pt;">Republic of the Philippines</p>
  <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="university-name" style="font-size:11pt;"><br>
  <p class="calibri-font" style="font-size:11pt;">Province of Laguna</p>
        <br>
  <p class="office-title" style="font-size:11pt;">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
  <p class="form-title" style="font-size:11pt;">ORGANIZATION RENEWAL FORM</p>
        <br>
    </div>

    <div class="mt-6 text-right">
  <p class="mb-0" style="font-size:11pt;"><span class="signature-line text-center border-b border-black min-w-[150px] inline-block" style="font-size:11pt;">{{ formattedDate }}</span></p>
  <p class="mb-0" style="text-align: center; width: 150px; display: inline-block; font-size:11pt;">Date</p>
    </div>
    
    <div style="height: 7px;"></div>

    <div class="section text-left">
  <p class="mb-0" style="font-size:11pt; font-weight:bold;"><strong>THE DIRECTOR/CHAIRPERSON</strong></p>
  <p class="mb-0" style="font-size:11pt; font-weight:bold;"><strong>OFFICE OF STUDENT AFFAIRS AND SERVICES</strong></p>
  <p class="mb-0" style="font-size:11pt; font-weight:bold;"><strong>LSPU</strong></p>
    </div>

    <div class="section">
  <p class="thru-line text-center my-2" style="font-size:11pt; font-weight:bold;"><strong>Thru: The Coordinator, Student Organization Unit</strong></p>
    </div>

  <div class="section" style="margin-bottom:10px;">
  <p style="margin-top:5px; font-family:'Times New Roman', serif; font-size:11pt; font-weight:normal;">Sir/Madam:</p>
  <div style="height:15px; font-size:11pt;"></div>
  <p style="text-indent:1.45cm; margin-bottom:20px; font-family:'Times New Roman', serif; font-size:11pt; font-weight:normal;">The <span class="dynamic-text signature-line" style="min-width:200px; display:inline-flex; align-items:center; justify-content:center; border-bottom:1px solid #000; padding-bottom:2px; vertical-align:middle; font-size:11pt;"><span style="border-bottom:none; display:inline-block; width:100%; text-align:center; font-size:11pt;">{{ form.organization_name }}</span></span> <span style="display:inline-block; width:12px; font-size:11pt;"></span>wishes<span style="display:inline-block; width:12px; font-size:11pt;"></span>to<span style="display:inline-block; width:12px; font-size:11pt;"></span>seek<span style="display:inline-block; width:12px; font-size:11pt;"></span>renewal<span style="display:inline-block; width:12px; font-size:11pt;"></span>of<span style="display:inline-block; width:12px; font-size:11pt;"></span>its<span style="display:inline-block; width:12px; font-size:11pt;"></span>recognition<span style="display:inline-block; width:12px; font-size:11pt;"></span>to function as a duly recognized LSPU Organization for Academic Year 20<span class="dynamic-text" style="font-size:11pt;"><u>{{ form.academic_year_start }}</u></span> - 20<span class="dynamic-text" style="font-size:11pt;"><u>{{ form.academic_year_end }}</u></span>.</p>
  <p class="indented" style="text-indent:1.45cm; margin-bottom:20px; font-family:'Times New Roman', serif; font-size:11pt; font-weight:normal; word-spacing:0.8em;">In this connection, we are respectfully requesting from your good office to grant us permission to operate in our institution, subject to the existing rules & regulations of our University.</p>
    <br>
  <p class="indented" style="margin-top:-10px; margin-left:30px; font-family:'Times New Roman', serif; font-size:11pt; font-weight:normal;">Thank you very much.</p>
  </div>

    <div class="section text-right">
  <p class="respectfully-text" style="font-size:11pt; position:relative; left:-10px; top:-10px;">Very respectfully yours,</p>

        <div class="signature">
            <p><span class="signature-line" style="min-width:160px; font-size:11pt;">{{ form.president_name }}</span></p>
            <p><span class="title-under-signature title-left-adjust" style="font-size:11pt;"><strong>Organization President</strong></span></p>
        </div>
    </div>

    <div class="section text-right">
        <div class="signature">
            <p><span class="signature-line" style="min-width:160px; font-size:11pt;">{{ form.organization_name }}</span></p>
            <p><span class="title-under-signature title-left-adjust-more" style="font-size:11pt;"><strong>Name of Organization</strong></span></p>
        </div>
    </div>

    <div class="section text-left">
  <p style="font-size:11pt; font-weight:bold; margin-bottom:0; position:relative; top:-20px;"><strong>NOTED:</strong></p>
    <div class="signature" style="margin-top:0; margin-bottom:10px;">
      <p style="margin-bottom:0;"><span class="signature-line" style="min-width:220px; font-size:11pt;"><strong>{{ form.adviser_name }}</strong></span></p>
      <p style="margin-top:0;"><span class="title-under-signature title-right-adjust" style="font-size:11pt;"><strong>Adviser/s, Student Organization</strong></span></p>
    </div>
    <div class="signature" style="margin-top:0; margin-bottom:15px;">
      <p style="margin-bottom:0;"><span class="signature-line" style="min-width:305px; font-size:11pt;"><strong>{{ form.dean_name }}</strong></span></p>
      <p style="margin-top:0;"><span class="title-under-signature" style="font-size:11pt;"><strong>Dean/Assoc. Dean, College of</strong> <span class="signature-line signature-line-inline" style="min-width:120px; font-size:11pt;">{{ form.college }}</span></span></p>
    </div>
    </div>

    <div class="section text-center">
    <p style="margin-left:-380px; font-size:11pt; font-weight:bold; margin-bottom:0;">Recommending Approval:</p>
    <div class="signature" style="margin-top:0; margin-bottom:15px;">
      <p style="margin-bottom:0;"><span class="signature-line" style="min-width:270px; font-size:11pt; font-weight:bold;"><strong>{{ form.coordinator_name }}</strong></span></p>
      <p style="margin-top:0; font-size:11pt; font-weight:bold;"><strong>Coordinator, Student Organization Unit</strong></p>
    </div>
    </div>

    <div class="section text-center">
    <p style="margin-left:-380px; font-size:11pt; font-weight:bold; margin-bottom:0;">Approved / Disapproved:</p>
    <div class="signature" style="margin-top:0; margin-bottom:15px;">
      <p style="margin-bottom:0;"><span class="signature-line" style="min-width:390px; font-size:11pt; font-weight:bold;"><strong>{{ form.director_name }}</strong></span></p>
      <p style="margin-top:0; font-size:11pt; font-weight:bold;"><strong>Director/Chairperson, Office of Student Affairs and Services</strong></p>
    </div>
    </div>

    <!-- Form inputs -->
    <form @submit.prevent="submit">
      <div class="mt-8 border-t pt-6">
          <h3 class="text-lg font-bold mb-4">Form Details</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                  <label class="block font-bold">Organization Name</label>
                  <input v-model="form.organization_name" class="border p-2 w-full">
                  <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
              </div>

              <div>
                  <label class="block font-bold">College</label>
                  <select 
                    :value="'College of ' + form.college"
                    @change="handleCollegeChange"
                    class="border p-2 w-full text-black"
                  >
                    <option value="" disabled>Select College</option>
                    <option v-for="option in collegeOptions" :key="option" :value="option">{{ option }}</option>
                  </select>
                  <p v-if="errors.college" class="text-red-500 text-sm mt-1">{{ errors.college }}</p>
              </div>

              <div>
                  <label class="block font-bold">Application Date</label>
                  <input 
                    type="date" 
                    :value="form.application_date" 
                    class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
                    readonly 
                    tabindex="-1" 
                    style="user-select: none; -webkit-user-select: none;" 
                  >
                  <p v-if="errors.application_date" class="text-red-500 text-sm mt-1">{{ errors.application_date }}</p>
              </div>

              <div class="flex items-end space-x-2">
                <div>
                  <label class="block font-bold">Academic Year</label>
                  <div class="flex items-center space-x-2">
                    <input 
                      v-model="form.academic_year_start" 
                      class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
                      :placeholder="currentYear" 
                      readonly 
                      tabindex="-1" 
                      style="user-select: none; -webkit-user-select: none;" 
                    >
                    <span class="mx-1">-</span>
                    <input 
                      v-model="form.academic_year_end" 
                      class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
                      :placeholder="nextYear" 
                      readonly 
                      tabindex="-1" 
                      style="user-select: none; -webkit-user-select: none;" 
                    >
                  </div>
                  <div class="flex space-x-2">
                    <p v-if="errors.academic_year_start" class="text-red-500 text-sm mt-1">{{ errors.academic_year_start }}</p>
                    <p v-if="errors.academic_year_end" class="text-red-500 text-sm mt-1">{{ errors.academic_year_end }}</p>
                  </div>
                </div>
              </div>

              <div>
                  <label class="block font-bold">President Name</label>
                  <input v-model="form.president_name" class="border p-2 w-full">
                  <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
              </div>

              <div>
                  <label class="block font-bold">Adviser Name</label>
                  <input v-model="form.adviser_name" class="border p-2 w-full">
                  <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
              </div>

              <div>
                  <label class="block font-bold">Dean Name</label>
                  <input v-model="form.dean_name" class="border p-2 w-full">
                  <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
              </div>

              <div>
                  <label class="block font-bold">Coordinator Name</label>
                  <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-100 text-gray-600" readonly>
              </div>

              <div>
                  <label class="block font-bold">Chairperson Name</label>
                  <input v-model="form.director_name" class="border p-2 w-full bg-gray-100 text-gray-600" readonly>
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

<style scoped>
/* Set Font to Times New Roman, Font Size to 11pt, and Line Spacing to 1.1 */
.form-content {
    font-family: 'Times New Roman', Times, serif;
    font-size: 11pt;
    line-height: 1.1;
}

/* Calibri font for specific elements */
.calibri-font {
    font-family: 'Calibri', 'Arial', sans-serif;
}

.office-title {
    font-size: 16px;
    font-weight: bold;
    margin-top: 0px;
    margin-bottom: 10px;
}

.form-title {
    font-size: 16px;
    font-weight: bold;
    margin: 0;
}

.university-name {
    max-width: 55%;
    height: auto;
    margin: 4px 0;
    display: inline-block;
}

.indented {
    text-indent: 1.27cm;
    margin-bottom: 20px;
}

.signature-line {
    display: inline-block;
    width: fit-content;
    border-bottom: 1px solid black;
    margin-bottom: 2px;
    text-align: center;
    overflow-wrap: break-word;
    word-wrap: break-word;
}

.title-under-signature {
    display: inline-block;
    width: fit-content;
    text-align: center;
    margin: 0;
    padding: 0;
}

.title-left-adjust {
    transform: translateX(-5px);
}

.title-left-adjust-more {
    transform: translateX(-10px);
}

.signature-line-inline {
    vertical-align: text-bottom !important;
    position: relative;
    top: 0px;
}

.respectfully-text {
    text-align: left;
    margin-left: 59%;
    display: block;
}

.dynamic-text {
    display: inline;
    word-break: break-word;
}

.thru-line {
    text-align: left;
    font-style: bold;
    margin: 10px 0;
    margin-left: 70px;
}

/* Basic input focus styling */
input:focus {
    outline: 2px solid #2563eb;
    outline-offset: -2px;
}

/* Ensure proper printing */
@media print {
    .form-content {
        page-break-before: always;
    }
    
    .signature-line {
        background: transparent !important;
    }
}
</style>