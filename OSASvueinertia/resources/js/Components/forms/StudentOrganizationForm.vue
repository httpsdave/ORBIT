<script setup>
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

const form = useForm({
  form_type: 'LSPU-OSAS-SF-001',
  organization_name: props.initialFormData.organization_name || '',
  president_name: props.initialFormData.president_name || '',
  application_date: (() => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  })(),
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
  status: props.initialFormData.status || 'Pending',
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

const errors = ref({});

const validateForm = () => {
  errors.value = {};
  
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
  }
  
  if (!form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
  }
  
  if (!form.application_date.trim()) {
    errors.value.application_date = 'Application Date is required';
  }
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Adviser Name is required';
  }
  
  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean Name is required';
  }
  
  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator Name is required';
  }
  
  if (!form.director_name.trim()) {
    errors.value.director_name = 'Director Name is required';
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

<div class="form-content" style="font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.1; margin: 0; padding: 0; box-sizing: border-box; min-height: 100vh;">
  <div class="header text-center relative" style="font-size: 15px; margin: 0 0 0.5cm 0; padding-top: 0.5cm;">
    <img src="/images/lspu-logo.png" alt="LSPU Logo" style="position: absolute; top: -0.5cm; left: -2cm; width: 250px; height: auto;">
    <span style="font-family: Calibri, sans-serif;">Republic of the Philippines</span><br>
    <span class="university-name" style="max-width: 55%; height: auto; margin: 4px 0; display: inline-block; font-weight: bold; font-size: 15px;">Laguna State Polytechnic University</span><br>
    <span style="font-family: Calibri, sans-serif;">Province of Laguna</span><br>
    <br>
    <strong>OFFICE OF STUDENT AFFAIRS AND SERVICES</strong><br>
    <br>
    <span class="sub-header" style="font-weight: bold; font-size: 13pt;">APPLICATION FOR ORGANIZATION RECOGNITION/RENEWAL OF ACCREDITED STUDENT ORGANIZATION</span>
  </div>

  <div class="section right-align" style="text-align: right; margin-top: 0.5cm;">
    <p style="margin-bottom: 0; margin-top: 0.1cm;"><span class="signature-line" style="display: inline-block; min-width: 150px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ formattedDate }}</span></p>
    <p style="margin-top: 0; text-align: left; width: 150px; display: inline-block; padding-left: 70px;">Date</p>
  </div>

  <div class="section" style="margin-bottom: 0.3cm;">
    <p><strong>THE DIRECTOR/CHAIRPERSON</strong><br>Office of Student Affairs and Services<br>LSPU</p>
  </div>

  <div style="height: 0.2cm;"></div>
  <div class="section justified" style="margin-bottom: 0.3cm; text-align: justify;">
    <p>Sir/Madam:</p>
  <div style="height: 0.3cm;"></div>
    <p class="indented" style="text-indent: 1.27cm;"><span style="word-spacing: 15px;">I have the honor to apply for recognition/renewal of the organization</span> <u>{{ form.organization_name }}</u>, to be duly recognized by Laguna State Polytechnic University.</p>
  <div style="height: 0.15cm;"></div>
    <p class="indented" style="text-indent: 1.27cm;">In compliance with CHED Memo Order No. 9s. 2013, Subj.: Enhanced Policies & Guidelines on Student Affairs and Services (Article VIII-Student Development, Section 19. Student Organizations and Activities), I am submitting for proper action the following requirements for recognition and accreditation, to wit:</p>
  </div>

  <div class="section list-indented" style="margin-bottom: 0.3cm;">
    <div style="margin-left: 1.27cm;">
      <p style="position: relative; left: 10px;">1. Letter of application for Organization Recognition (for new organizations) / Organization </p>
      <p style="position: relative; left: 20px;"> Renewal Form (for organizations seeking renewal) <span style="position: absolute; right: 60px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">2. Constitution and By-Laws of the Organization <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">3. Plan of activities for one (1) year <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">4. Accomplishment reports (for renewal of accreditation) <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">5. Adviser(s) Commitment Form <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">6. Certification from respective Dean/Associate Dean <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">7. Financial Report (if any) <span style="position: absolute; right: 50px;">- 4 copies</span></p>
    </div>
  </div>

  <div class="section justified" style="margin-bottom: 0.3cm; text-align: justify;">
    <p class="indented" style="text-indent: 1.27cm;">It is understood that the provision to the LSPU Supplementary Rules and Regulations Governing Student Organization in this official Recognition is good only for one (1) school year, subject to renewal unless revoked prior its expiration.</p>
  </div>

  <div class="section respectfully-yours" style="text-align: right; padding-right: 90px; margin-top: 0.5cm;">
    <p>Respectfully yours,</p>
  </div>

  <div class="signature right-align" style="margin-top: 0.5cm; text-align: right;">
    <p><span class="signature-line" style="display: inline-block; min-width: 200px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ form.president_name }}</span></p>
  <p style="margin: 0; padding: 0;"><span class="title-text" style="display: block; width: 200px; margin-left: 440px; text-align: center; white-space: nowrap; font-size: 11pt;">Organization President</span></p>
  </div>

  <div class="signature right-align" style="margin-top: 0.3cm; text-align: right;">
    <p><span class="signature-line" style="display: inline-block; min-width: 200px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ form.organization_name }}</span></p>
  <p style="margin: 0; padding: 0;"><span class="title-text" style="display: block; width: 200px; margin-left: 440px; text-align: center; white-space: nowrap; font-size: 11pt;">Name of Organization</span></p>
  </div>

  <div class="section left-align" style="text-align: left; margin-top: 0.5cm; margin-bottom: 0;">
    <p style="margin-top: 0.5cm; margin-bottom: 0;">NOTED:</p>
  </div>

  <div style="width: 100%; margin-top: 0.1cm; display: flex;">
    <div style="width: 50%; text-align: left;">
      <div class="signature left-align" style="margin-top: 0.3cm; text-align: left;">
        <p><span class="signature-line" style="display: inline-block; min-width: 200px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ form.adviser_name }}</span></p>
        <p><span class="title-text" style="display: block; width: 200px; text-align: center; white-space: nowrap; font-size: 11pt;">Adviser, Student Organization</span></p>
      </div>
    </div>
    <div style="width: 50%; text-align: right;">
      <div class="signature right-align" style="margin-top: 0.3cm; text-align: right;">
        <p><span class="signature-line" style="display: inline-block; min-width: 200px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ form.dean_name }}</span></p>
  <p style="margin: 0; padding: 0;"><span class="title-text" style="display: block; width: 200px; margin-left: 120px; text-align: center; white-space: nowrap; font-size: 11pt;">Dean/Assoc. Dean of College</span></p>
      </div>
    </div>
  </div>

  <div class="section center-align" style="margin-bottom: 0.5cm; text-align: center; margin-top: 0.5cm;">
    <p style="margin-bottom: 0;"><strong>Recommending Approval:</strong></p>
  </div>
  <div class="signature center-align" style="margin-top: 0.3cm; text-align: center;">
    <p style="margin-bottom: 0; margin-top: -10px;"><strong><span class="signature-line" style="display: inline-block; min-width: 260px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ form.coordinator_name }}</span></strong></p>
  <p style="margin-top: 0; margin-bottom: 0; text-align: center;"><span class="title-text long-title" style="display: block; width: 260px; margin-left: auto; margin-right: auto; text-align: center; white-space: nowrap; font-size: 11pt;">Coordinator, Student Organization Unit</span></p>
    <div style="height: 5px;"></div>
  </div>

  <div class="section center-align last-section" style="margin-bottom: 0.5cm; text-align: center; padding-bottom: 0.3cm; margin-top: 0.5cm;">
    <p style="margin-bottom: 0;"><strong>Approved/Disapproved:</strong></p>
  </div>
  <div class="signature center-align last-signature" style="margin-top: 0.3cm; text-align: center; margin-bottom: 10px;">
    <p style="margin-bottom: 0; margin-top: -6px;"><strong><span class="signature-line" style="display: inline-block; min-width: 390px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ form.director_name }}</span></strong></p>
  <p style="margin-top: 0; margin-bottom: 0; text-align: center;"><span class="title-text long-title" style="display: block; width: 390px; margin-left: auto; margin-right: auto; text-align: center; white-space: nowrap; font-size: 11pt;">Director/Chairperson, Office of Student Affairs and Services</span></p>
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
        <label class="block font-bold">Application Date</label>
        <input 
          type="date" 
          :value="form.application_date" 
          class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
          required 
          readonly 
          tabindex="-1" 
          style="user-select: none; -webkit-user-select: none;" 
        >
        <p v-if="errors.application_date" class="text-red-500 text-sm mt-1">{{ errors.application_date }}</p>
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
        <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none;">
      </div>
      <div>
        <label class="block font-bold">Director Name</label>
        <input v-model="form.director_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none;">
      </div>
    </div>
    <div class="mt-6 text-center">
      <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">{{ props.isEdit ? 'Update' : 'Submit' }}</button>
    </div>
  </div>

  <div class="footer" style="margin-top: 2cm; font-size: 10pt; font-family: Calibri, sans-serif; display: flex; justify-content: space-between;">
    <span>LSPU-OSAS-SF-001</span>
    <span>Rev. 1</span>
    <span>09 November 2020</span>
  </div>
</div>

</template>