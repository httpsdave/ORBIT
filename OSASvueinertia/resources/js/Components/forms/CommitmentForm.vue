<script setup>
const rankOptions = [
  'Instructor I',
  'Instructor II',
  'Instructor III',
  'Assistant Professor I',
  'Assistant Professor II',
  'Assistant Professor III',
  'Assistant Professor IV',
  'Associate Professor I',
  'Associate Professor II',
  'Associate Professor III',
  'Associate Professor IV',
  'Associate Professor V',
  'Part Time Instructor'
];
const collegeOptions = [
  'College of Computer Studies',
  'College of Arts and Sciences',
  'College of Engineering',
  'College of Industrial Technology',
  'College of Hospitality Management and Tourism',
  'College of Teacher Education',
  'College of Criminal Justice Education'
];
import { ref, computed } from 'vue';
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

const emit = defineEmits(['submitted', 'error']);

// Compute current year and next year for placeholders
const currentYear = computed(() => {
  return new Date().getFullYear().toString().slice(-2);
});

const nextYear = computed(() => {
  return (new Date().getFullYear() + 1).toString().slice(-2);
});

// Computed property to format the date
const formattedDate = computed(() => {
  if (!form.form_date) return '';
  const date = new Date(form.form_date);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

const form = useForm({
  form_type: 'LSPU-OSAS-SF-003',
  organization_name: props.initialFormData.organization_name || '',
  president_name: props.initialFormData.president_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  adviser_college:props.initialFormData.adviser_college || '',
  adviser_rank:props.initialFormData.adviser_rank || '',
  adviser_address:props.initialFormData.adviser_address || '',
  adviser_contact:props.initialFormData.adviser_contact || '',
  form_date:props.initialFormData.form_date || new Date().toISOString().slice(0, 10),
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
  academic_year_start: props.initialFormData.academic_year_start || '',
  academic_year_end: props.initialFormData.academic_year_end || '',
});

// Add errors ref object
const errors = ref({});

// Add validation function
const validateForm = () => {
  errors.value = {};
  
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization name is required';
  }
  
  if (!form.academic_year_start.trim()) {
    errors.value.academic_year_start = 'Academic year start is required';
  }
  
  if (!form.academic_year_end.trim()) {
    errors.value.academic_year_end = 'Academic year end is required';
  }
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Adviser name is required';
  }
  
  if (!form.adviser_college.trim()) {
    errors.value.adviser_college = 'Adviser college is required';
  }
  
  if (!form.adviser_rank.trim()) {
    errors.value.adviser_rank = 'Academic rank is required';
  }
  
  if (!form.adviser_address.trim()) {
    errors.value.adviser_address = 'Home address is required';
  }
  
  if (!form.adviser_contact.trim()) {
    errors.value.adviser_contact = 'Contact number is required';
  }
  
  if (!form.form_date.trim()) {
    errors.value.form_date = 'Form date is required';
  }
  
  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean name is required';
  }
  
  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator name is required';
  }
  
  if (!form.director_name.trim()) {
    errors.value.director_name = 'Director name is required';
  }
  
  if (!form.president_name.trim()) {
    errors.value.president_name = 'President name is required';
  }
  
  return Object.keys(errors.value).length === 0;
};

const submit = () => {
  if (!validateForm()) {
    emit('error', 'Please fill in all required fields.');
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
        emit('submitted', form.data());
      },
      onError: (errors) => {
        emit('error', 'Form submission failed.');
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
      <p class="osas-header mb-0 mt-4">Office of Student Affairs and Services</p>
      <p class="commitment-form-title mb-4">ORGANIZATION ADVISER COMMITMENT FORM</p>
    </div>

    <div class="mt-6 text-right">
        <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[150px] inline-block">{{ formattedDate }}</span></p>
        <p class="mb-0" style="text-align: center; width: 150px; display: inline-block;">Date</p>
    </div>
    
    <div style="height: 7px;"></div>

    <div class="section text-left">
      <p class="mb-0 font-bold">THE DIRECTOR/CHAIRPERSON</p>
      <p class="mb-0">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
      <p class="mb-0">LSPU</p>
    </div>

    <div class="section">
      <p class="indented font-bold mb-2">Thru: The Coordinator, Student Organization Unit</p>
    </div>

    <div class="section commitment-body">
      <p class="mb-1">Sir,</p>
      <p class="indented mb-0">
        This letter is in connection with the application for recognition of
        <span class="blank-line org-name">{{ form.organization_name }}</span>
        as a LSPU Student Organization.
      </p>
      <p class="indented mb-0">
        I, the undersigned, have committed to serve as the organizations Faculty Adviser for the academic year 20
        <span class="blank-line year">{{ form.academic_year_start }}</span>
        –20
        <span class="blank-line year">{{ form.academic_year_end }}</span>,
        and will therefore assume full responsibility as provided in the guidelines for the recognition of student organizations.
      </p>
      <p class="indented mb-0">
        Furthermore, I certify to the correctness and completeness of the documents attached to the organization application for recognition.
      </p>
    </div>

    <!-- Signature block: Very respectfully yours -->
    <div class="very-respectfully mt-12 mb-8">
      <p class="mb-2" style="font-weight: bold;">Very respectfully yours,</p>
      <div class="sig-row"><span class="sig-label">Name:</span><span class="sig-line">{{ form.adviser_name }}</span></div>
      <div class="sig-row"><span class="sig-label">Signature:</span><span class="sig-line">&nbsp;</span></div>
      <div class="sig-row"><span class="sig-label">College:</span><span class="sig-line">{{ form.adviser_college }}</span></div>
      <div class="sig-row"><span class="sig-label">Academic Rank:</span><span class="sig-line">{{ form.adviser_rank }}</span></div>
      <div class="sig-row"><span class="sig-label">Home Address:</span><span class="sig-line">{{ form.adviser_address }}</span></div>
      <div class="sig-row"><span class="sig-label">Contact Number(s):</span><span class="sig-line">{{ form.adviser_contact }}</span></div>
  <div class="sig-row"><span class="sig-label">Date:</span><span class="sig-line">{{ new Date(form.form_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span></div>
    </div>

    <!-- Noted, Recommending Approval, and Approval Section -->
    <div class="mt-2 mb-8">
      <div class="mb-8">
        <div class="noted-label mb-1">Noted:</div>
        <div class="noted-signature-block" style="width: 250px; margin-left: 120px;">
          <span class="signature-line block text-center" style="width: 100%;">{{ form.dean_name }}</span>
          <p class="mb-0 text-xs text-center">Dean/Assoc. Dean of College</p>
        </div>
      </div>
      <div class="approval-center-block text-center">
        <div class="mb-8">
          <p class="mb-1">Recommending Approval:</p>
          <div class="mt-2">
            <span class="signature-line">{{ form.coordinator_name }}</span>
            <p class="mb-0 text-xs">Coordinator, Student Organization Unit</p>
          </div>
        </div>
        <div>
          <p class="mb-1">Approved / Disapproved:</p>
          <div class="mt-2">
            <span class="signature-line">{{ form.director_name }}</span>
            <p class="mb-0 text-xs">Director, Office of Student Affairs and Services</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Form inputs -->
    <div class="mt-8 border-t pt-6">
      <h3 class="text-lg font-bold mb-4">Form Details</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block font-bold">Organization Name</label>
          <input v-model="form.organization_name" class="border p-2 w-full" required>
          <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
        </div>
        <div class="flex items-end space-x-2">
          <div>
            <label class="block font-bold">Academic Year</label>
            <div class="flex items-center space-x-2">
              <input 
                v-model="form.academic_year_start" 
                class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
                required 
                :placeholder="currentYear" 
                readonly 
                tabindex="-1" 
                style="user-select: none; -webkit-user-select: none;" 
              >
              <span class="mx-1">-</span>
              <input 
                v-model="form.academic_year_end" 
                class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
                required 
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
          <label class="block font-bold">Adviser Name</label>
          <input v-model="form.adviser_name" class="border p-2 w-full" required>
          <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
        </div>
        <div>
          <label class="block font-bold">Adviser College</label>
          <select 
            v-model="form.adviser_college"
            class="border p-2 w-full text-black"
            required
          >
            <option value="" disabled>Select College</option>
            <option v-for="option in collegeOptions" :key="option" :value="option">{{ option }}</option>
          </select>
          <p v-if="errors.adviser_college" class="text-red-500 text-sm mt-1">{{ errors.adviser_college }}</p>
        </div>
        <div>
          <label class="block font-bold">Academic Rank</label>
          <select 
            v-model="form.adviser_rank"
            class="border p-2 w-full text-black"
            required
          >
            <option value="" disabled>Select Rank</option>
            <option v-for="option in rankOptions" :key="option" :value="option">{{ option }}</option>
          </select>
          <p v-if="errors.adviser_rank" class="text-red-500 text-sm mt-1">{{ errors.adviser_rank }}</p>
        </div>
        <div>
          <label class="block font-bold">Home Address</label>
          <input v-model="form.adviser_address" class="border p-2 w-full" required>
          <p v-if="errors.adviser_address" class="text-red-500 text-sm mt-1">{{ errors.adviser_address }}</p>
        </div>
        <div>
          <label class="block font-bold">Contact Number(s)</label>
          <input 
            v-model="form.adviser_contact" 
            class="border p-2 w-full" 
            required 
            @input="e => { e.target.value = e.target.value.replace(/[^0-9+()\-]/g, ''); form.adviser_contact = e.target.value; }"
            pattern="[0-9+()\-]*"
          >
          <p v-if="errors.adviser_contact" class="text-red-500 text-sm mt-1">{{ errors.adviser_contact }}</p>
        </div>
        <div>
          <label class="block font-bold">Form Date</label>
          <input type="date" v-model="form.form_date" class="border p-2 w-full" required>
          <p v-if="errors.form_date" class="text-red-500 text-sm mt-1">{{ errors.form_date }}</p>
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
        <div>
          <label class="block font-bold">President Name</label>
          <input v-model="form.president_name" class="border p-2 w-full" required>
          <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
        </div>
      </div>
      <div class="mt-6 text-center">
        <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">{{ props.isEdit ? 'Update' : 'Submit' }}</button>
      </div>
    </div>

    <div class="footer mt-8 text-xs flex justify-between">
      <span>LSPU-OSAS-SF-003</span>
      <span>Rev. 1</span>
      <span>09 November 2020</span>
    </div>
  </div>
</template>

<style scoped>
.header {
  margin-bottom: 0.5rem;
}
.university-name {
  font-size: 1.1rem;
}
.form-title {
  font-size: 1.1rem;
  letter-spacing: 1px;
}
.indented {
  text-indent: 1.27cm;
}
.blank-line {
  display: inline-block;
  min-width: 200px;
  border-bottom: 1px solid black;
  padding-bottom: 2px;
  text-align: center;
}
.signature-section {
  margin-top: 0;
  float: none;
  width: 100%;
  margin-right: 0;
}
.signature-field {
  margin: 2px 0;
  display: table;
  width: 100%;
}
.signature-label {
  display: table-cell;
  vertical-align: bottom;
  padding-right: 5px;
  white-space: nowrap;
  width: 1%;
}
.signature-value {
  display: table-cell;
  border-bottom: 1px solid black;
  padding-bottom: 2px;
  text-align: left;
  min-height: 14px;
  vertical-align: bottom;
}
.sig-name { width: 230px; }
.sig-signature { width: 205px; }
.sig-college { width: 220px; }
.sig-rank { width: 165px; }
.sig-address { width: 170px; }
.sig-contact { width: 143px; }
.sig-date { width: 234px; }
.signature-line {
  display: inline-block;
  min-width: 200px;
  border-bottom: 1px solid black;
  padding-bottom: 2px;
  text-align: center;
}
.section {
  margin-bottom: 3px;
}
.section p {
  margin: 2px 0;
  word-wrap: break-word;
  line-height: 1.1;
}
.noted-label {
  text-align: left;
  font-size: 1rem;
  font-weight: normal;
  margin-top: -0.7em;
}
.approval-center-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.osas-header {
  font-size: 1.35rem;
  font-weight: bold;
  margin-bottom: 0.1rem;
}
.commitment-form-title {
  font-size: 1.05rem;
  font-weight: bold;
  margin-top: 0.1rem;
  letter-spacing: 0.5px;
}
.commitment-body {
  font-family: 'Times New Roman', Times, serif;
  font-size: 12pt;
  text-align: justify;
  line-height: 1.15;
  max-width: 700px;
  margin: 0 auto;
}
.commitment-body p {
  margin-bottom: 0;
  margin-top: 0;
}
.blank-line.org-name {
  min-width: 300px;
  display: inline-block;
  border-bottom: 1px solid #000;
  vertical-align: middle;
}
.blank-line.year {
  min-width: 60px;
  display: inline-block;
  border-bottom: 1px solid #000;
  vertical-align: middle;
}
.very-respectfully {
  font-family: 'Times New Roman', Times, serif;
  font-size: 12pt;
  margin-top: 1.5em;
  margin-bottom: 1.5em;
  margin-left: calc(3em + 220px);
}
.sig-row {
  display: flex;
  align-items: baseline;
  margin-bottom: 0.2em;
  width: 340px; /* Shortened by 60px */
}
.sig-label {
  font-size: 12pt;
  flex-shrink: 0;
}
.sig-line {
  border-bottom: 1px solid #000;
  flex-grow: 1;
  height: 1em;
  margin-left: 0.5em;
  vertical-align: middle;
  display: inline-block;
}
.noted-signature-block {
  width: 250px;
  margin-left: 120px;
  margin-top: 0.2em;
}
</style>