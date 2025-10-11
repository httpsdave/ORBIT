<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
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

// Get current user and check if admin
const user = usePage().props.auth.user;
const isAdmin = user && (user.role?.slug === 'admin' || user.is_admin || (typeof user.role === 'object' && user.role.id === 1));

// Get saved form data and selected form type from props
const props = defineProps({
    savedFormData: {
        type: Object,
        default: () => ({})
    },
    selectedFormType: {
        type: String,
        required: true
    }
});

const currentForm = ref(props.selectedFormType);
const formData = ref({});
const uploadFile = ref(null);
const uploadError = ref('');
const uploadProgress = ref(0);
const uploadSuccess = ref('');
const pdfPreviewUrl = ref(null);

// Store original viewport for restoration
let originalViewport = null;

// Function to automatically set desktop view for optimal form filling experience
const setDesktopView = () => {
  try {
    // For mobile browsers that support viewport meta tag manipulation
    let viewport = document.querySelector('meta[name=viewport]');
    if (!viewport) {
      viewport = document.createElement('meta');
      viewport.name = 'viewport';
      document.head.appendChild(viewport);
    }
    // Set fixed width to force desktop view on mobile devices
    viewport.content = 'width=1024, initial-scale=0.5, user-scalable=yes';
    
    // Additional mobile-specific adjustments
    if (/Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      // Force zoom out to show full desktop layout
      setTimeout(() => {
        if (window.visualViewport) {
          // Modern approach for supported browsers
          try {
            window.scrollTo(0, 0);
          } catch (e) {
            console.log('Visual viewport adjustment not supported');
          }
        }
      }, 100);
    }
  } catch (error) {
    console.log('Desktop view adjustment not supported on this browser');
  }
};

// Function to restore original viewport settings
const restoreViewport = () => {
  try {
    const viewport = document.querySelector('meta[name=viewport]');
    if (viewport && originalViewport) {
      viewport.content = originalViewport;
    }
  } catch (error) {
    console.log('Viewport restoration not supported');
  }
};

watch(uploadFile, (file) => {
    if (pdfPreviewUrl.value) {
        URL.revokeObjectURL(pdfPreviewUrl.value);
        pdfPreviewUrl.value = null;
    }
    if (file) {
        pdfPreviewUrl.value = URL.createObjectURL(file);
    }
});

onMounted(() => {
  // Store original viewport content
  const viewport = document.querySelector('meta[name=viewport]');
  if (viewport) {
    originalViewport = viewport.content;
  }
  
  // Automatically set desktop view when form page loads
  setDesktopView();
});

onUnmounted(() => {
  if (pdfPreviewUrl.value) {
    URL.revokeObjectURL(pdfPreviewUrl.value);
  }
  // Restore original viewport when leaving the form
  restoreViewport();
});const isDirectUploadForm = computed(() => [
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

// Initialize form data based on selected form type
const initializeFormData = () => {
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
    
    // Initialize form data based on form type
    if (currentForm.value === 'LSPU-OSAS-SF-004') {
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
                    budget: ''
                }
            ];
        }
        formData.value = {
            ...filteredSavedData,
            activities
        };
    }
    // Initialize members for List of Members form
    else if (currentForm.value === 'LSPU-OSAS-SF-005') {
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
    else if (currentForm.value === 'LSPU-OSAS-SF-006') {
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
    else if (currentForm.value === 'LSPU-OSAS-SF-007') {
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
    else if (currentForm.value === 'LSPU-OSAS-SF-009') {
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

// Initialize form data when component mounts
initializeFormData();

const handleFormSubmitted = (data) => {
    console.log('Form submitted:', data);
    // Reset the form
    currentForm.value = '';
    formData.value = {};
};

const handleFileDrop = (e) => {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    
    if (!file) {
        uploadError.value = '';
        return;
    }
    
    // Check file type
    if (file.type !== 'application/pdf') {
        uploadError.value = 'Only PDF files are allowed.';
        return;
    }
    
    // Check file size (20MB = 20 * 1024 * 1024 bytes)
    const maxSize = 20 * 1024 * 1024; // 20MB in bytes
    if (file.size > maxSize) {
        uploadError.value = 'The file you\'re attempting to upload is over the limit (20MB). Please compress your file and try again.';
        return;
    }
    
    uploadFile.value = file;
    uploadError.value = '';
};
const handleFileChange = (e) => {
    const file = e.target.files[0];
    
    if (!file) {
        uploadError.value = '';
        return;
    }
    
    // Check file type
    if (file.type !== 'application/pdf') {
        uploadError.value = 'Only PDF files are allowed.';
        return;
    }
    
    // Check file size (20MB = 20 * 1024 * 1024 bytes)
    const maxSize = 20 * 1024 * 1024; // 20MB in bytes
    if (file.size > maxSize) {
        uploadError.value = 'The file you\'re attempting to upload is over the limit (20MB). Please compress your file and try again.';
        return;
    }
    
    uploadFile.value = file;
    uploadError.value = '';
};
const handleDirectUploadSubmit = () => {
    if (!uploadFile.value) {
        uploadError.value = 'Please select a PDF file.';
        return;
    }
    
    // Double-check file size before submitting
    const maxSize = 20 * 1024 * 1024; // 20MB in bytes
    if (uploadFile.value.size > maxSize) {
        uploadError.value = 'The file you\'re attempting to upload is over the limit (20MB). Please compress your file and try again.';
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
    <!-- Show the selected form -->
    <div v-if="isDirectUploadForm">
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
            <div class="text-sm text-gray-500 mt-1">
              Size: {{ (uploadFile.size / (1024 * 1024)).toFixed(2) }} MB
            </div>
          </div>
          <div v-else>
            <p class="text-gray-500">Drag and drop a PDF file here, or click to select</p>
            <p class="text-xs text-gray-400 mt-1">Maximum file size: 20MB</p>
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
            @click="handleDirectUploadSubmit"
            :disabled="!uploadFile || uploadProgress > 0 && uploadProgress < 100"
            class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:from-green-600 active:to-green-700 transition-all duration-300 relative overflow-hidden group"
            style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif;"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <span>Submit</span>
            <!-- Create icon (file/submit) -->
            <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#e3e3e3" class="ml-2" aria-hidden="true">
              <path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
    <div v-else>
      <!-- Student Organization Form -->
      <StudentOrganizationForm 
        v-if="currentForm === 'LSPU-OSAS-SF-001'" 
        :initialFormData="formData"
        :isAdmin="isAdmin"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Renewal Form -->
      <RenewalForm 
        v-if="currentForm === 'LSPU-OSAS-SF-002'" 
        :initialFormData="formData"
        :isAdmin="isAdmin"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Commitment Form -->
      <CommitmentForm 
        v-if="currentForm === 'LSPU-OSAS-SF-003'" 
        :initialFormData="formData"
        :isAdmin="isAdmin"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Plan of Activities Form -->
      <PlanOfActivitiesForm 
        v-if="currentForm === 'LSPU-OSAS-SF-004'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- List of Members Form -->
      <ListOfMembersForm 
        v-if="currentForm === 'LSPU-OSAS-SF-005'" 
        :initialFormData="formData"
        :isAdmin="isAdmin"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Student Certification Form -->
      <StudentCertificationForm 
        v-if="currentForm === 'LSPU-OSAS-SF-006'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- List of Officers Form -->
      <ListOfOfficersForm 
        v-if="currentForm === 'LSPU-OSAS-SF-007'" 
        :initialFormData="formData"
        :isAdmin="isAdmin"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Student Activity Attendance Sheet -->
      <ActivityAttendanceForm 
        v-if="currentForm === 'LSPU-OSAS-SF-009'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      <!-- Evaluation Form -->
      <EvaluationForm 
        v-if="currentForm === 'LSPU-OSAS-SF-EVAL'" 
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