<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { useForm,router, usePage } from '@inertiajs/vue3';
import StudentOrganizationForm from '@/Components/forms/StudentOrganizationForm.vue';
import RenewalForm from '@/Components/forms/RenewalForm.vue';
import CommitmentForm from '@/Components/forms/CommitmentForm.vue';
import PlanOfActivitiesForm from '@/Components/forms/PlanOfActivitiesForm.vue';
import ListOfMembersForm from '@/Components/forms/ListOfMembersForm.vue';
import StudentCertificationForm from '@/Components/forms/StudentCertificationForm.vue';
import ListOfOfficersForm from '@/Components/forms/ListOfOfficersForm.vue';
import ActivityAttendanceForm from '@/Components/forms/ActivityAttendanceForm.vue';
import EvaluationForm from '@/Components/forms/EvaluationForm.vue';

const props = defineProps({
  application: {
    type: Object,
    required: true
  }
});

const user = usePage().props.auth.user;
const isAdmin = user && (user.role?.slug === 'admin' || user.is_admin || (typeof user.role === 'object' && user.role.id === 1));

// Initialize form data based on the application type
const formData = computed(() => {
  const data = { ...props.application };
  
  console.log('Edit.vue - props.application:', props.application);
  console.log('Edit.vue - studentCertifications:', props.application.studentCertifications);
  
  // Initialize activities for Plan of Activities form if they don't exist
  if (props.application.form_type === 'LSPU-OSAS-SF-004') {
    if (!data.activities || !data.activities.length) {
      data.activities = Array(3).fill().map(() => ({
        objective: '',
        name: '',
        description: '',
        persons_involved: '',
        target_date: '',
        budget: 0
      }));
    }
    // Ensure all required fields are preserved
    const currentYear = new Date().getFullYear().toString().slice(-2);
    const nextYear = (new Date().getFullYear() + 1).toString().slice(-2);
    data.semester = data.semester || '';
    data.academic_year_start = data.academic_year_start || currentYear;
    data.academic_year_end = data.academic_year_end || nextYear;
    data.organization_name = data.organization_name || '';
    data.president_name = data.president_name || '';
    data.secretary_name = data.secretary_name || '';
    data.adviser_name = data.adviser_name || '';
    data.dean_name = data.dean_name || '';
    data.coordinator_name = data.coordinator_name || '';
    data.director_name = data.director_name || '';
    data.application_date = data.application_date || '';
  }
  
  // Initialize members for List of Members form if they don't exist
  else if (props.application.form_type === 'LSPU-OSAS-SF-005') {
    if (!data.members || !data.members.length) {
      data.members = Array(4).fill().map(() => ({
        student_name: '',
        student_number: '',
        course_year_section: '',
        photo_path: null
      }));
    }
    // Ensure all required fields are preserved
    data.semester = data.semester || '';
    data.academic_year_start = data.academic_year_start || '';
    data.academic_year_end = data.academic_year_end || '';
    data.organization_name = data.organization_name || '';
    data.president_name = data.president_name || '';
    data.secretary_name = data.secretary_name || '';
    data.adviser_name = data.adviser_name || '';
    data.second_adviser = data.second_adviser || '';
    data.dean_name = data.dean_name || '';
    data.coordinator_name = data.coordinator_name || '';
    data.director_name = data.director_name || '';
    data.application_date = data.application_date || '';
  }
  
  // Initialize students for Student Certification form if they don't exist
  else if (props.application.form_type === 'LSPU-OSAS-SF-006') {
    console.log('Edit.vue - Processing StudentCertificationForm');
    // Backend now provides students data directly
    if (!data.students || !data.students.length) {
      data.students = Array(1).fill().map(() => ({
        student_name: '',
        course_year_section: '',
        position_rank: '',
      }));
      console.log('Edit.vue - Created default students:', data.students);
    } else {
      console.log('Edit.vue - Using existing students:', data.students);
    }
  }
  
  // Initialize officers for List of Officers form if they don't exist
  else if (props.application.form_type === 'LSPU-OSAS-SF-007') {
    if (!data.officers || !data.officers.length) {
      data.officers = Array(4).fill().map(() => ({
        student_name: '',
        position: '',
        student_number: '',
        photo_path: null
      }));
    }
  }
  
  // Initialize attendees for Student Activity Attendance Sheet if they don't exist
  else if (props.application.form_type === 'LSPU-OSAS-SF-009') {
    if (!data.attendees || !data.attendees.length) {
      data.attendees = Array(10).fill().map(() => ({
        name: '',
        course_year_section: '',
        signature: null
      }));
    }
  }
  
  console.log('Edit.vue - Final formData:', data);
  return data;
});

const handleFormSubmitted = (data) => {
  console.log('Submitting update for application ID:', props.application.id);
  console.log('Update data:', data);

  // Convert to FormData for file uploads
  const formData = new FormData();
  for (const key in data) {
    if (key === 'ratings' && Array.isArray(data[key])) {
      // Ensure ratings are strings
      data[key].forEach((rating, idx) => {
        let stringRating = rating;
        if (rating !== null && rating !== undefined && rating !== '') {
          stringRating = rating.toString();
          // Convert single digit to X.0 format
          if (/^[1-5]$/.test(stringRating)) {
            stringRating = stringRating + '.0';
          }
          // Ensure proper decimal format
          if (/^[1-4]$/.test(stringRating[0])) {
            const decimal = stringRating.includes('.') ? stringRating.split('.')[1] : '0';
            stringRating = `${stringRating[0]}.${decimal}`;
          } else if (stringRating[0] === '5') {
            stringRating = '5.0';
          }
        }
        formData.append(`ratings[${idx}]`, stringRating);
      });
    } else if (Array.isArray(data[key])) {
      data[key].forEach((item, idx) => {
        for (const subKey in item) {
          if (item[subKey] !== null && item[subKey] !== undefined) {
            formData.append(`${key}[${idx}][${subKey}]`, item[subKey]);
          }
        }
      });
    } else {
      if (data[key] !== null && data[key] !== undefined) {
        formData.append(key, data[key]);
      }
    }
  }
  // Add _method=PUT for Laravel method spoofing
  formData.append('_method', 'PUT');

  // For debugging - log the FormData contents
  for (let [key, value] of formData.entries()) {
    console.log(`${key}: ${value}`);
  }

  router.post(`/applications/${props.application.id}`, formData, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      // Do NOT call router.visit here! The backend will redirect to /applications with the flash message.
    },
    onError: (errors) => {
      alert('Update failed. Please check your input.');
      console.log('Update errors:', errors);
      // Log the detailed errors for debugging
      Object.keys(errors).forEach(key => {
        console.log(`${key}: ${errors[key]}`);
      });
    }
  });
};

// Special report form types
const specialReportFormTypes = [
  'LSPU-OSAS-SF-ACCOMPLISHMENT',
  'LSPU-OSAS-SF-NARRATIVE',
  'LSPU-OSAS-SF-BYLAWS',
  'LSPU-OSAS-SF-FINANCIAL',
  'LSPU-ACAD-RL',
];

const uploadFile = ref(null);
const uploadError = ref('');
const uploadProgress = ref(0);
const pdfPreviewUrl = ref(null);

watch(uploadFile, (file) => {
  if (pdfPreviewUrl.value) {
    URL.revokeObjectURL(pdfPreviewUrl.value);
    pdfPreviewUrl.value = null;
  }
  if (file) {
    pdfPreviewUrl.value = URL.createObjectURL(file);
  }
});

onUnmounted(() => {
  if (pdfPreviewUrl.value) {
    URL.revokeObjectURL(pdfPreviewUrl.value);
  }
});

const handleFileDrop = (e) => {
  e.preventDefault();
  const file = e.dataTransfer.files[0];
  if (file && file.type === 'application/pdf') {
    uploadFile.value = file;
    uploadError.value = '';
  } else {
    uploadError.value = 'Please upload a PDF file.';
  }
};
const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (file && file.type === 'application/pdf') {
    uploadFile.value = file;
    uploadError.value = '';
  } else {
    uploadError.value = 'Please upload a PDF file.';
  }
};

const getReportFilePath = computed(() => {
  const app = props.application;
  if (app.form_type === 'LSPU-OSAS-SF-ACCOMPLISHMENT') return app.accomplishment_report_path;
  if (app.form_type === 'LSPU-OSAS-SF-NARRATIVE') return app.narrative_report_path;
  if (app.form_type === 'LSPU-OSAS-SF-BYLAWS') return app.bylaws_path;
  if (app.form_type === 'LSPU-OSAS-SF-FINANCIAL') return app.financial_report_path;
  if (app.form_type === 'LSPU-ACAD-RL') return app.event_letter_path;
  return null;
});

const handleSpecialFormSubmit = () => {
  if (!uploadFile.value) {
    uploadError.value = 'Please select a PDF file.';
    return;
  }
  uploadError.value = '';
  uploadProgress.value = 0;
  const formData = new FormData();
  formData.append('form_type', props.application.form_type);
  formData.append('file', uploadFile.value);
  formData.append('_method', 'PUT');
  // Use the same endpoint as update
  router.post(`/applications/${props.application.id}`, formData, {
    forceFormData: true,
    preserveScroll: true,
    onProgress: (event) => {
      if (event && event.detail && event.detail.progress) {
        uploadProgress.value = event.detail.progress.percentage;
      }
    },
    onSuccess: () => {
      uploadFile.value = null;
      uploadProgress.value = 0;
    },
    onError: (errors) => {
      uploadError.value = errors.file || 'Upload failed.';
      uploadProgress.value = 0;
    }
  });
};
</script>

<template>
  <div class="p-6 document">
    <!-- Flash Messages -->
    <div v-if="$page.props.flash && ($page.props.flash.success || $page.props.flash.error)" class="mb-4">
      <div v-if="$page.props.flash.success" class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash.error" class="bg-red-100 text-red-800 px-4 py-2 rounded mb-2">
        {{ $page.props.flash.error }}
      </div>
    </div>
    <h1 class="text-2xl font-bold mb-6">Edit {{ props.application.organization_name }} Application</h1>
    
    <!-- Status Information -->
    <div class="mb-6 p-4 rounded" :class="{
      'bg-yellow-100': props.application.status === 'Pending',
      'bg-green-100': props.application.status === 'Approved',
      'bg-red-100': props.application.status === 'Disapproved'
    }">
      <h2 class="text-lg font-semibold">Status: {{ props.application.status }}</h2>
      <p v-if="props.application.feedback" class="mt-2">
        <strong>Feedback:</strong> {{ props.application.feedback }}
      </p>
    </div>
    
    <!-- Display the appropriate form based on form_type -->
    <div v-if="isAdmin || props.application.status !== 'Approved'">
      <!-- Student Organization Form -->
      <StudentOrganizationForm 
        v-if="props.application.form_type === 'LSPU-OSAS-SF-001'" 
        :initialFormData="formData"
        :isEdit="true"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Renewal Form -->
      <RenewalForm 
        v-else-if="props.application.form_type === 'LSPU-OSAS-SF-002'" 
        :initialFormData="formData"
        :isEdit="true"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Commitment Form -->
      <CommitmentForm 
        v-else-if="props.application.form_type === 'LSPU-OSAS-SF-003'" 
        :initialFormData="formData"
        :isEdit="true"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Plan of Activities Form -->
      <PlanOfActivitiesForm 
        v-else-if="props.application.form_type === 'LSPU-OSAS-SF-004'" 
        :initialFormData="formData"
        :isEdit="true"
        @submitted="handleFormSubmitted"
      />
      
      <!-- List of Members Form -->
      <ListOfMembersForm 
        v-else-if="props.application.form_type === 'LSPU-OSAS-SF-005'" 
        :initialFormData="formData"
        :isEdit="true"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Student Certification Form -->
      <StudentCertificationForm 
        v-else-if="props.application.form_type === 'LSPU-OSAS-SF-006'" 
        :initialFormData="formData"
        :isEdit="true"
        @submitted="handleFormSubmitted"
      />
      
      <!-- List of Officers Form -->
      <ListOfOfficersForm 
        v-else-if="props.application.form_type === 'LSPU-OSAS-SF-007'" 
        :initialFormData="formData"
        :isEdit="true"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Student Activity Attendance Sheet -->
      <ActivityAttendanceForm 
        v-else-if="props.application.form_type === 'LSPU-OSAS-SF-009'" 
        :initialFormData="formData"
        :isEdit="true"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Evaluation Form -->
      <EvaluationForm
        v-else-if="props.application.form_type === 'LSPU-OSAS-SF-EVAL'"
        :initialFormData="formData"
        :isEdit="true"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Special Report Forms Edit Block -->
      <div v-else-if="specialReportFormTypes.includes(props.application.form_type)">
        <div class="w-full max-w-2xl mx-auto">
          <div
            class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer bg-gray-50 hover:bg-gray-100 transition"
            @drop="handleFileDrop"
            @dragover.prevent
            @click="$refs.fileInput && $refs.fileInput.click()"
          >
            <input type="file" ref="fileInput" accept="application/pdf" class="hidden" @change="handleFileChange" />
            <div v-if="uploadFile">
              <div
                class="text-green-700 font-semibold max-w-full overflow-hidden text-ellipsis whitespace-nowrap"
                :title="uploadFile.name"
                style="max-width: 100%;"
              >
                {{ uploadFile.name }}
              </div>
            </div>
            <div v-else-if="getReportFilePath">
              <a
                :href="`/storage/${getReportFilePath}`"
                target="_blank"
                class="text-blue-600 underline"
              >
                View Existing File
              </a>
            </div>
            <div v-else>
              <p class="text-gray-500">Drag and drop a PDF file here, or click to select</p>
            </div>
            <div v-if="uploadError" class="text-red-600 mt-2">{{ uploadError }}</div>
            <div v-if="uploadProgress > 0 && uploadProgress < 100" class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
              <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: uploadProgress + '%' }"></div>
            </div>
          </div>
          <div v-if="pdfPreviewUrl" class="w-full mt-6">
            <iframe
              :src="pdfPreviewUrl"
              type="application/pdf"
              class="w-full border rounded-lg"
              style="height: 650px;"
            ></iframe>
          </div>
          <div class="flex justify-center mt-8">
            <button
              class="px-6 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition disabled:opacity-50"
              :disabled="!uploadFile || (uploadProgress > 0 && uploadProgress < 100)"
              @click="handleSpecialFormSubmit"
            >
              Update
            </button>
          </div>
        </div>
      </div>
      <!-- Fallback for unknown form types -->
      <div v-else class="bg-red-100 p-4 rounded">
        <p>Unknown form type: {{ props.application.form_type }}</p>
      </div>
    </div>
    <div v-else class="bg-gray-50 p-4 rounded text-gray-500 text-center">
      <p>This application has been approved and can no longer be edited. You may only view or download the application.</p>
    </div>
  </div>
</template>

<style scoped>
/* Ensure A4 Paper Size */
.document {
    width: 210mm;
    min-height: 297mm;
    padding: 20mm;
    margin: auto;
    background: white;
}

/* Set Font to Times New Roman, Font Size to 10pt, and Line Spacing to 1.0 */
.form-content {
    font-family: 'Times New Roman', Times, serif;
    font-size: 10pt;
    line-height: 1.0;
}

/* Ensure Proper Printing */
@media print {
    body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 20mm;
    }
    .document {
        page-break-before: always;
    }
}
</style>