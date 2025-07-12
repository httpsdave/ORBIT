<script setup>
import { ref } from 'vue';
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

const emit = defineEmits(['submitted']);

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
  form_date:props.initialFormData.form_date || '',
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
        <p class="text-sm font-bold form-title mt-4 mb-4">COMMITMENT FORM</p>
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
        <p class="mb-1">Sir,</p>
        
        <p class="indented">This letter is in connection with the application for recognition of 
        <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.organization_name }}</span> as a LSPU Student Organization.</p>
        
        <p class="indented">I, the undersigned, have committed to serve as the organizations Faculty 
        Adviser for the academic year 20<span class="blank-line text-center border-b border-black inline-block" style="min-width: 50px">{{ form.academic_year_start }}</span>-20<span class="blank-line text-center border-b border-black inline-block" style="min-width: 50px">{{ form.academic_year_end }}</span>, and will therefore assume full responsibility as 
        provided in the guidelines for the recognition of student organizations.</p>
        
        <p class="indented">Furthermore, I certify to the correctness and completeness of the documents 
        attached to the organization application for recognition.</p>
    </div>

    <!-- Noted, Approval, and Signature Section in Flex Layout -->
    <div class="flex flex-row justify-between mt-12 mb-8">
      <!-- Left column: Noted, Recommending Approval, Approved/Disapproved -->
      <div class="flex flex-col items-center flex-1">
        <div class="mb-12 text-center">
          <p class="mb-1">Noted:</p>
          <div class="mt-6">
            <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.dean_name }}</span>
            <p class="mb-0">Dean/Assoc. Dean of College</p>
          </div>
        </div>
        <div class="mb-12 text-center">
          <p class="mb-1">Recommending Approval:</p>
          <div class="mt-6">
            <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.coordinator_name }}</span>
            <p class="mb-0">Coordinator, Student Organization Unit</p>
          </div>
        </div>
        <div class="text-center">
          <p class="mb-1">Approved / Disapproved:</p>
          <div class="mt-6">
            <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.director_name }}</span>
            <p class="mb-0">Director, Office of Student Affairs and Services</p>
          </div>
        </div>
      </div>
      <!-- Right column: Signature block -->
      <div class="flex flex-col items-end flex-1">
        <div class="signature-section w-full max-w-[400px]">
          <p class="mb-4"><strong>Very respectfully yours,</strong></p>
          <div class="signature-field">
            <span class="signature-label">Name:</span>
            <span class="signature-value sig-name">{{ form.adviser_name }}</span>
          </div>
          <div class="signature-field">
            <span class="signature-label">Signature:</span>
            <span class="signature-value sig-signature">{{ form.adviser_signature || '' }}</span>
          </div>
          <div class="signature-field">
            <span class="signature-label">College:</span>
            <span class="signature-value sig-college">{{ form.adviser_college }}</span>
          </div>
          <div class="signature-field">
            <span class="signature-label">Academic Rank:</span>
            <span class="signature-value sig-rank">{{ form.adviser_rank }}</span>
          </div>
          <div class="signature-field">
            <span class="signature-label">Home Address:</span>
            <span class="signature-value sig-address">{{ form.adviser_address }}</span>
          </div>
          <div class="signature-field">
            <span class="signature-label">Contact Number(s):</span>
            <span class="signature-value sig-contact">{{ form.adviser_contact }}</span>
          </div>
          <div class="signature-field">
            <span class="signature-label">Date:</span>
            <span class="signature-value sig-date">{{ form.form_date }}</span>
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
                <label class="block font-bold">Adviser Name</label>
                <input v-model="form.adviser_name" class="border p-2 w-full" required>
                <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Adviser College</label>
                <input v-model="form.adviser_college" class="border p-2 w-full" required>
                <p v-if="errors.adviser_college" class="text-red-500 text-sm mt-1">{{ errors.adviser_college }}</p>
            </div>

            <div>
                <label class="block font-bold">Academic Rank</label>
                <input v-model="form.adviser_rank" class="border p-2 w-full" required>
                <p v-if="errors.adviser_rank" class="text-red-500 text-sm mt-1">{{ errors.adviser_rank }}</p>
            </div>

            <div>
                <label class="block font-bold">Home Address</label>
                <input v-model="form.adviser_address" class="border p-2 w-full" required>
                <p v-if="errors.adviser_address" class="text-red-500 text-sm mt-1">{{ errors.adviser_address }}</p>
            </div>

            <div>
                <label class="block font-bold">Contact Number(s)</label>
                <input v-model="form.adviser_contact" class="border p-2 w-full" required>
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
/* Signature section styling to match blade template */
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

/* Individual width controls for each signature field */
.sig-name { width: 230px; }
.sig-signature { width: 205px; }
.sig-college { width: 220px; }
.sig-rank { width: 165px; }
.sig-address { width: 170px; }
.sig-contact { width: 143px; }
.sig-date { width: 234px; }

/* Signature styling for other sections */
.signature {
    margin-top: 10px;
}

.signature-line {
    display: inline-block;
    min-width: 250px;
    border-bottom: 1px solid black;
    padding-bottom: 2px;
    text-align: center;
}

/* General form styling */
.indented {
    text-indent: 1.27cm;
}

.thru-line {
    margin-bottom: 15px;
    padding-left: 1.27cm;
    text-indent: 0;
}

.blank-line {
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
</style>