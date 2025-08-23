<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import FormSelector from '@/Components/FormSelector.vue';
import StudentOrganizationForm from '@/Components/forms/StudentOrganizationForm.vue';
import RenewalForm from '@/Components/forms/RenewalForm.vue';
import CommitmentForm from '@/Components/forms/CommitmentForm.vue';
import PlanOfActivitiesForm from '@/Components/forms/PlanOfActivitiesForm.vue';
import ListOfMembersForm from '@/Components/forms/ListOfMembersForm.vue';
import StudentCertificationForm from '@/Components/forms/StudentCertificationForm.vue';
import ListOfOfficersForm from '@/Components/forms/ListOfOfficersForm.vue';
import ActivityAttendanceForm from '@/Components/forms/ActivityAttendanceForm.vue';
import EvaluationForm from '@/Components/forms/EvaluationForm.vue';
import { router } from '@inertiajs/vue3';

// Get saved form data from props
const props = defineProps({
    savedFormData: {
        type: Object,
        default: () => ({})
    }
});

const currentForm = ref('');
const formData = ref({});
const uploadFile = ref(null);
const uploadError = ref('');
const uploadProgress = ref(0);
const uploadSuccess = ref('');
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

const formOptions = [
    { value: 'LSPU-OSAS-SF-001', label: 'Recognition Form' },
    { value: 'LSPU-OSAS-SF-002', label: 'Renewal Form' },
    { value: 'LSPU-OSAS-SF-003', label: 'Commitment Form' },
    { value: 'LSPU-OSAS-SF-004', label: 'Plan of Activities' },
    { value: 'LSPU-OSAS-SF-005', label: 'List of Members' },
    { value: 'LSPU-OSAS-SF-006', label: 'Student Certification' },
    { value: 'LSPU-OSAS-SF-007', label: 'List of Officers' }, 
    { value: 'LSPU-OSAS-SF-009', label: 'Student Activity Attendance Sheet' },
    { value: 'LSPU-OSAS-SF-EVAL', label: 'Evaluation Form' },
    { value: 'LSPU-OSAS-SF-ACCOMPLISHMENT', label: 'Accomplishment Report' },
    { value: 'LSPU-OSAS-SF-NARRATIVE', label: 'Narrative Report' },
    { value: 'LSPU-OSAS-SF-BYLAWS', label: 'Constitution & By-laws' },
    { value: 'LSPU-OSAS-SF-FINANCIAL', label: 'Financial Report' },
    { value: 'LSPU-ACAD-RL', label: 'Event Letter' },
];

const isDirectUploadForm = computed(() => [
    'LSPU-OSAS-SF-ACCOMPLISHMENT',
    'LSPU-OSAS-SF-NARRATIVE',
    'LSPU-OSAS-SF-BYLAWS',
    'LSPU-OSAS-SF-FINANCIAL',
    'LSPU-ACAD-RL'
].includes(currentForm.value));

// Helper to format date to yyyy-MM-dd
function formatDateForInput(dateStr) {
    if (!dateStr) return '';
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return '';
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

const handleFormSelection = (formId) => {
    currentForm.value = formId;
    
    // Filter out array fields from saved data to prevent issues
    const filteredSavedData = {};
    Object.keys(props.savedFormData).forEach(key => {
        // Exclude academic year fields and array/object fields
        if (!Array.isArray(props.savedFormData[key]) && 
            typeof props.savedFormData[key] !== 'object' &&
            key !== 'academic_year_start' && 
            key !== 'academic_year_end') {
            filteredSavedData[key] = props.savedFormData[key];
        }
    });
    
    // Initialize form data with saved data if available
    if (formId === 'LSPU-OSAS-SF-004') {
        let activities = [];
        if (Array.isArray(props.savedFormData.activities) && props.savedFormData.activities.length > 0) {
            activities = props.savedFormData.activities.map(act => ({
                ...act,
                target_date: formatDateForInput(act.target_date)
            }));
        } else {
            activities = [
                {
                    objective: '',
                    name: '',
                    description: '',
                    persons_involved: '',
                    target_date: '',
                    budget: 0
                }
            ];
        }
        formData.value = {
            ...filteredSavedData,
            activities
        };
    }
    // Initialize members for List of Members form
    else if (formId === 'LSPU-OSAS-SF-005') {
        formData.value = {
            ...filteredSavedData,
            members: Array(4).fill().map(() => ({
                student_name: '',
                student_number: '',
                course_year_section: '',
                photo_path: null
            }))
        };
    }
    // Initialize students for Student Certification form
    else if (formId === 'LSPU-OSAS-SF-006') {
        formData.value = {
            ...filteredSavedData,
            students: Array(1).fill().map(() => ({
                student_name: '',
                course_year_section: '',
                position_rank: '',
                is_bonafide: false,
                is_not_academic_probation: false,
                is_not_disciplinary_probation: false,
                has_position: false,
                certification_date: '',
            }))
        };
    }
    // Initialize officers for List of Officers form
    else if (formId === 'LSPU-OSAS-SF-007') {
        formData.value = {
            ...filteredSavedData,
            officers: Array(4).fill().map(() => ({
                student_name: '',
                position: '',
                student_number: '',
                photo_path: null
            }))
        };
    }
    // Initialize attendees for Student Activity Attendance Sheet
    else if (formId === 'LSPU-OSAS-SF-009') {
        formData.value = {
            ...filteredSavedData,
            attendees: Array(10).fill().map(() => ({
                name: '',
                course_year_section: '',
                signature: null
            }))
        };
    }
    // For other forms, just use saved data
    else {
        formData.value = { ...filteredSavedData };
    }
};

const handleFormSubmitted = (data) => {
    console.log('Form submitted:', data);
    // Reset the form
    currentForm.value = '';
    formData.value = {};
};

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
const handleDirectUploadSubmit = () => {
    if (!uploadFile.value) {
        uploadError.value = 'Please select a PDF file.';
        return;
    }
    uploadError.value = '';
    uploadProgress.value = 0;
    const formData = new FormData();
    formData.append('form_type', currentForm.value);
    formData.append('file', uploadFile.value);
    router.post('/applications/upload-report', formData, {
        forceFormData: true,
        onProgress: (event) => {
            if (event && event.detail && event.detail.progress) {
                uploadProgress.value = event.detail.progress.percentage;
            }
        },
        onSuccess: () => {
            // Do NOT call router.visit here! Let the backend redirect and Inertia handle the flash message.
            // Reset local state
            currentForm.value = '';
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
    <!-- Show form selector if no form is selected -->
    <FormSelector 
      v-if="!currentForm" 
      :formOptions="formOptions" 
      @form-selected="handleFormSelection" 
    />

    <!-- Show the selected form -->
    <div v-else-if="isDirectUploadForm">
      <div class="w-full max-w-2xl mx-auto">
        <div
          class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer bg-gray-50 hover:bg-gray-100 transition"
          @drop="handleFileDrop"
          @dragover.prevent
          @click="$refs.fileInput.click()"
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
          <div v-else>
            <p class="text-gray-500">Drag and drop a PDF file here, or click to select</p>
          </div>
          <div v-if="uploadError" class="text-red-600 mt-2">{{ uploadError }}</div>
          <div v-if="uploadProgress > 0 && uploadProgress < 100" class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: uploadProgress + '%' }"></div>
          </div>
          <div v-if="uploadSuccess" class="text-green-600 mt-2">{{ uploadSuccess }}</div>
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
            :disabled="!uploadFile || uploadProgress > 0 && uploadProgress < 100"
            @click="handleDirectUploadSubmit"
          >
            Submit
          </button>
        </div>
      </div>
    </div>
    <div v-else>
      <!-- Student Organization Form -->
      <StudentOrganizationForm 
        v-if="currentForm === 'LSPU-OSAS-SF-001'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Renewal Form -->
      <RenewalForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-002'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Commitment Form -->
      <CommitmentForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-003'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Plan of Activities Form -->
      <PlanOfActivitiesForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-004'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- List of Members Form -->
      <ListOfMembersForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-005'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Student Certification Form -->
      <StudentCertificationForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-006'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- List of Officers Form -->
      <ListOfOfficersForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-007'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Student Activity Attendance Sheet -->
      <ActivityAttendanceForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-009'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      <!-- Evaluation Form -->
      <EvaluationForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-EVAL'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
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