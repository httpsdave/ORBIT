<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { useFormAutoSave } from '@/Composables/useFormAutoSave';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  initialFormData: {
    type: Object,
    default: () => ({})
  },
  isEdit: {
    type: Boolean,
    default: false
  },
  isAdmin: {
    type: Boolean,
    default: false
  }
});

const backHref = computed(() => props.isEdit ? '/applications' : '/applications/select-form');

// Get current user data including system settings
const page = usePage();
const allowImageUploads = computed(() => {
  return page.props.auth.user?.allow_image_uploads !== false;
});

// Compute current year and next year for placeholders
const currentYear = computed(() => {
  return new Date().getFullYear().toString().slice(-2);
});

const nextYear = computed(() => {
  return (new Date().getFullYear() + 1).toString().slice(-2);
});

// Add errors ref object
const errors = ref({});

// Autosave state
const showRestorePrompt = ref(false);
const autosavedData = ref(null);

// CSV modal states
const showCsvModal = ref(false);
const csvModalTitle = ref('');
const csvModalMessage = ref('');
const csvModalType = ref('success'); // 'success' or 'error'

// Error navigation modal states
const showErrorModal = ref(false);
const errorPages = ref([]);
const totalErrors = ref(0);

const closeErrorModal = () => {
  showErrorModal.value = false;
  errorPages.value = [];
  totalErrors.value = 0;
};

// CSV template download functionality
const downloadCSVTemplate = () => {
  // Create CSV content with headers and sample data
  const csvContent = [
    'Officer Name,Position,Student Number,Do not fill beyond this point',
    'First Name M.I. Last Name,President,0325-001,',
    'First Name M.I. Last Name,Vice President,0322-002,',
    'First Name M.I. Last Name,Treasurer,0324-003,',
    ',,,'
  ].join('\n');
  // Create blob and download
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', 'officers_template.csv');
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

const closeCsvModal = () => {
  showCsvModal.value = false;
  csvModalTitle.value = '';
  csvModalMessage.value = '';
  csvModalType.value = 'success';
};

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
    // Prevent removing the last officer
    if (form.officers.length <= 1) {
        return;
    }
    // Clean up object URL if it exists
    if (form.officers[index].photo_preview) {
        URL.revokeObjectURL(form.officers[index].photo_preview);
    }
    form.officers.splice(index, 1);
};

// Add function to clear all officers
const clearAllOfficers = () => {
    // Clean up all photo preview URLs
    form.officers.forEach(officer => {
        if (officer.photo_preview) {
            URL.revokeObjectURL(officer.photo_preview);
        }
    });
    
    // Keep only the first officer but clear its fields
    form.officers = [{
        student_name: '',
        position: '',
        student_number: '',
        photo_path: null,
        photo_preview: null
    }];
    
    // Reset to first page
    currentPage.value = 1;
};

// Add a function to remove an officer's photo
const removeOfficerPhoto = (index) => {
    // Clean up object URL if it exists
    if (form.officers[index].photo_preview) {
        URL.revokeObjectURL(form.officers[index].photo_preview);
    }
    // Clear photo data
    form.officers[index].photo_path = null;
    form.officers[index].photo_preview = null;
};

// CSV upload functionality
const handleCSVUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
        csvModalTitle.value = 'Invalid File Type';
        csvModalMessage.value = 'Please upload a CSV file only.';
        csvModalType.value = 'error';
        showCsvModal.value = true;
        return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            const csvContent = e.target.result;
            const lines = csvContent.split('\n');
            
            // Skip the first row (header) and process data rows
            const dataRows = lines.slice(1).filter(line => line.trim() !== '');
            
            if (dataRows.length === 0) {
                csvModalTitle.value = 'No Data Found';
                csvModalMessage.value = 'No data found in CSV file.';
                csvModalType.value = 'error';
                showCsvModal.value = true;
                return;
            }

            // Clear existing officers
            form.officers = [];
            
            // Process each row
            dataRows.forEach((row, index) => {
                const columns = row.split(',').map(col => col.trim().replace(/"/g, ''));
                
                // Extract first 3 columns only
                const studentName = columns[0] || '';
                const position = columns[1] || '';
                const studentNumber = columns[2] || '';
                
                // Add officer if at least one field has data
                if (studentName || position || studentNumber) {
                    form.officers.push({
                        student_name: studentName,
                        position: position,
                        student_number: studentNumber,
                        photo_path: null,
                        photo_preview: null
                    });
                }
            });
            
            // Reset to first page after upload
            currentPage.value = 1;
            
            csvModalTitle.value = 'Import Successful';
            csvModalMessage.value = `Successfully imported ${form.officers.length} officers from CSV file.`;
            csvModalType.value = 'success';
            showCsvModal.value = true;
            
        } catch (error) {
            console.error('Error parsing CSV:', error);
            csvModalTitle.value = 'Import Error';
            csvModalMessage.value = 'Error reading CSV file. Please check the file format.';
            csvModalType.value = 'error';
            showCsvModal.value = true;
        }
    };
    
    reader.readAsText(file);
    
    // Reset the file input
    event.target.value = '';
};

const emit = defineEmits(['submitted', 'error']);

// Add pagination state
const currentPage = ref(1);
const officersPerPage = 4;

// Pagination computed properties
const totalPages = computed(() => Math.ceil(form.officers.length / officersPerPage));
const startIndex = computed(() => (currentPage.value - 1) * officersPerPage);
const endIndex = computed(() => Math.min(startIndex.value + officersPerPage, form.officers.length));
const currentPageOfficers = computed(() => {
    return form.officers.slice(startIndex.value, endIndex.value);
});

// Add computed for current page's officer input forms
const currentPageOfficerInputs = computed(() => {
    return form.officers.slice(startIndex.value, endIndex.value);
});

// Add computed for pagination display
const visiblePages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const delta = 2; // Number of pages to show on each side of current page
    
    if (total <= 7) {
        // If 7 or fewer pages, show all
        return Array.from({ length: total }, (_, i) => i + 1);
    }
    
    const range = [];
    const rangeWithDots = [];
    
    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        range.push(i);
    }
    
    if (current - delta > 2) {
        rangeWithDots.push(1, '...');
    } else {
        rangeWithDots.push(1);
    }
    
    rangeWithDots.push(...range);
    
    if (current + delta < total - 1) {
        rangeWithDots.push('...', total);
    } else {
        rangeWithDots.push(total);
    }
    
    return rangeWithDots;
});

// Navigation functions
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

// Error detection functions
const getPageErrors = (pageNumber) => {
  const startIdx = (pageNumber - 1) * officersPerPage;
  const endIdx = Math.min(startIdx + officersPerPage, form.officers.length);
  const pageErrors = [];
  
  // Check officers on this page
  for (let i = startIdx; i < endIdx; i++) {
    const officer = form.officers[i];
    const officerErrors = [];
    
    if (!officer.student_name?.trim()) {
      officerErrors.push('Officer name');
    }
    if (!officer.position?.trim()) {
      officerErrors.push('Position');
    }
    if (!officer.student_number?.trim()) {
      officerErrors.push('Student I.D. number');
    }
    
    if (officerErrors.length > 0) {
      pageErrors.push({
        officerIndex: i + 1,
        errors: officerErrors
      });
    }
  }
  
  return pageErrors;
};

const getFormFieldErrors = () => {
  const formErrors = [];
  
  if (!form.organization_name?.trim()) {
    formErrors.push('Organization name');
  }
  if (!form.academic_year_start?.trim()) {
    formErrors.push('Academic year start');
  }
  if (!form.academic_year_end?.trim()) {
    formErrors.push('Academic year end');
  }
  if (!form.adviser_name?.trim()) {
    formErrors.push('Faculty adviser name');
  }
  if (!form.coordinator_name?.trim()) {
    formErrors.push('Coordinator name');
  }
  
  return formErrors;
};

const goToErrorPage = (pageNumber) => {
  goToPage(pageNumber);
  closeErrorModal();
};

const form = useForm({
  form_type: 'LSPU-OSAS-SF-007',
  organization_name: props.initialFormData.organization_name?.toUpperCase() || '',
  // president_name removed for List of Officers Form
  application_date: props.initialFormData.application_date || (() => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  })(),
  adviser_name: props.initialFormData.adviser_name?.toUpperCase() || '',
  coordinator_name: props.initialFormData.coordinator_name?.toUpperCase() || '',
  director_name: props.initialFormData.director_name?.toUpperCase() || '',

  academic_year_start: props.initialFormData.academic_year_start || currentYear.value,
  academic_year_end: props.initialFormData.academic_year_end || nextYear.value,
  officers: [],
});

// Initialize autosave (disabled by default)
const { isAutoSaving, enable: enableAutoSave, disable: disableAutoSave, start: startAutoSave, stop: stopAutoSave } = useFormAutoSave(form, 'list_of_officers', { enabled: false });

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
    // If photo_path is a string (already saved), return the storage URL
    if (officer.photo_path && typeof officer.photo_path === 'string') {
        return `/storage/${officer.photo_path}`;
    }
    return null;
};

// Initialize with data from props if available
if (props.initialFormData?.officers && props.initialFormData.officers.length > 0) {
  // Copy members from initialFormData
  form.officers = [...props.initialFormData.officers.map(officer => ({
    ...officer,
    student_name: officer.student_name?.toUpperCase() || '',
    position: officer.position?.toUpperCase() || '',
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
  
  // Check if we should use modal-based error reporting (when more than 3 pages)
  const useErrorModal = totalPages.value > 3;
  
  // Always validate and set individual field errors for visual feedback
  // Validate main form fields
  if (!form.organization_name || !form.organization_name.trim()) {
    errors.value.organization_name = 'Organization name is required';
  }
  
  if (!form.academic_year_start || !form.academic_year_start.trim()) {
    errors.value.academic_year_start = 'Academic year start is required';
  }
  
  if (!form.academic_year_end || !form.academic_year_end.trim()) {
    errors.value.academic_year_end = 'Academic year end is required';
  }
  
  // president_name validation removed for List of Officers Form
  
  if (!form.adviser_name || !form.adviser_name.trim()) {
    errors.value.adviser_name = 'Faculty adviser name is required';
  }
  
  if (!form.coordinator_name || !form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator name is required';
  }
  
  // Validate officers and set individual field errors
  form.officers.forEach((officer, index) => {
    if (!officer.student_name || !officer.student_name.trim()) {
      errors.value[`officer_${index}_name`] = 'Officer name is required';
    }
    
    if (!officer.position || !officer.position.trim()) {
      errors.value[`officer_${index}_position`] = 'Officer position is required';
    }
    
    if (!officer.student_number || !officer.student_number.trim()) {
      errors.value[`officer_${index}_student_number`] = 'Student I.D. number is required';
    }
  });
  
  // Check if there are any errors
  const hasErrors = Object.keys(errors.value).length > 0;
  
  if (useErrorModal && hasErrors) {
    // For > 3 pages: show both individual field errors AND the navigation modal
    errorPages.value = [];
    totalErrors.value = 0;
    
    // Check form field errors
    const formFieldErrors = getFormFieldErrors();
    let hasFormErrors = formFieldErrors.length > 0;
    
    // Check each page for officer errors
    const pagesWithErrors = [];
    for (let page = 1; page <= totalPages.value; page++) {
      const pageErrors = getPageErrors(page);
      if (pageErrors.length > 0) {
        pagesWithErrors.push({
          pageNumber: page,
          errors: pageErrors,
          errorCount: pageErrors.reduce((sum, officer) => sum + officer.errors.length, 0)
        });
        totalErrors.value += pageErrors.reduce((sum, officer) => sum + officer.errors.length, 0);
      }
    }
    
    // Build error pages array for modal
    errorPages.value = pagesWithErrors;
    if (hasFormErrors) {
      totalErrors.value += formFieldErrors.length;
      // Add form errors as "page 0" for navigation
      errorPages.value.unshift({
        pageNumber: 0,
        isFormFields: true,
        errors: formFieldErrors.map(error => ({ officerIndex: null, errors: [error] })),
        errorCount: formFieldErrors.length
      });
    }
    
    // Show the modal for easy navigation
    showErrorModal.value = true;
    return false;
  }
  
  // Return true if no errors (works for both modal and non-modal cases)
  return !hasErrors;
};

// Autosave restore function
const restoreAutosave = () => {
  if (autosavedData.value) {
    // Store application_date before restore
    const currentApplicationDate = form.application_date;
    
    // Restore all form fields from autosaved data
    Object.assign(form, autosavedData.value);
    
    // Restore application_date to current value (don't overwrite from autosave)
    form.application_date = currentApplicationDate;
  }
  showRestorePrompt.value = false;
  enableAutoSave();
};

// Autosave dismiss function
const dismissAutosave = async () => {
  showRestorePrompt.value = false;
  autosavedData.value = null;
  
  // Clear the old autosaved data since user chose to dismiss
  try {
    await fetch('/delete-autosaved-form-data?form_type=list_of_officers', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
      },
      body: JSON.stringify({ form_type: 'list_of_officers' }),
    });
  } catch (error) {
    console.error('Failed to clear autosaved data:', error);
  }
  
  enableAutoSave();
};

// Check if autosaved data is newer than initialized data
const isAutosavedDataNewer = (autosavedTimestamp) => {
  if (!autosavedTimestamp) return false;
  
  if (props.initialFormData?.updated_at || props.initialFormData?.created_at) {
    const initialTimestamp = new Date(props.initialFormData.updated_at || props.initialFormData.created_at);
    const autosaveTimestamp = new Date(autosavedTimestamp);
    return autosaveTimestamp > initialTimestamp;
  }
  
  return true;
};

// Fetch autosaved data on mount
onMounted(async () => {
  if (!props.isEdit) {
    try {
      const response = await fetch('/get-autosaved-form-data?form_type=list_of_officers', {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.form_data) {
          if (isAutosavedDataNewer(data.updated_at)) {
            autosavedData.value = data.form_data;
            showRestorePrompt.value = true;
          } else {
            enableAutoSave();
          }
        } else {
          enableAutoSave();
        }
      } else {
        enableAutoSave();
      }
    } catch (error) {
      console.error('Failed to fetch autosaved data:', error);
      enableAutoSave();
    }
  }
});

// Cleanup on unmount
onUnmounted(() => {
  stopAutoSave();
});

// REMOVE: statusMessage, statusType, showStatus, showBanner

const submit = async () => {
  if (!validateForm()) {
    emit('error', 'Please fill in all required fields.');
    return;
  }
  
  stopAutoSave();
  
  if (props.isEdit) {
    emit('submitted', form.data());
  } else {
    form.post('/applications', {
      onSuccess: async () => {
        // Clear autosaved data after successful submission
        try {
          await fetch('/delete-autosaved-form-data?form_type=list_of_officers', {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
            },
            body: JSON.stringify({ form_type: 'list_of_officers' }),
          });
        } catch (error) {
          console.error('Failed to clear autosaved data:', error);
        }
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
<!-- Restore Prompt Modal -->
<div v-if="showRestorePrompt" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
  <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-4">
    <div class="flex items-start mb-4">
      <svg class="w-6 h-6 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <h3 class="text-lg font-semibold text-gray-900">Restore Unsaved Changes?</h3>
        <p class="mt-2 text-sm text-gray-600">
          We found unsaved changes from your previous session. Would you like to restore them?
        </p>
      </div>
    </div>
    <div class="flex justify-end gap-3 mt-6">
      <button
        @click="dismissAutosave"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
      >
        Start Fresh
      </button>
      <button
        @click="restoreAutosave"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
      >
        Restore Changes
      </button>
    </div>
  </div>
</div>

  <div class="form-container">
    <!-- Remove the List of Officers Form heading above the logo -->
    <!-- <h2 class="text-lg font-bold mb-4">List of Officers Form</h2> -->
    
    <!-- Officer list preview -->
    <div class="mt-6 form-content">
      <!-- REMOVE: <StatusBanner ... /> -->
  <div class="header text-center relative">
        <!-- Back Button positioned above LSPU logo -->
        <div style="position: absolute; top: -0.8cm; left: -2cm; z-index: 10;">
          <a :href="backHref"
             class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
             style="font-family: system-ui, -apple-system, sans-serif;">
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
          </a>
        </div>
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5rem] left-[-2rem] w-[250px] h-auto">
        <div style="padding-top: 30px;">
          <p class="text-sm font-normal mb-0" style="font-family: Calibri, sans-serif; font-size:10pt;">Republic of the Philippines</p>
          <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="university-name mb-0" style="max-width:45%; height:auto; display:inline-block;" />
          <p class="text-sm mb-0" style="font-family: Calibri, sans-serif; font-size:10pt;">Province of Laguna</p>
          <p class="text-sm font-bold mb-0" style ="padding-top: 10px;">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
        </div>
      </div>
      
      <div class="organization-details text-center mt-2 mb-4">
        <p class="mb-1">{{ form.organization_name.toUpperCase() }}</p>
        <p class="mb-0">A.Y. 20{{ form.academic_year_start }}-20{{ form.academic_year_end }}</p>
      </div>
      
      <div class="list-title text-center font-bold mb-4 text-lg">LIST OF OFFICERS</div>
      
      <!-- Officers list with pagination -->
      <div v-for="(officer, index) in currentPageOfficers" :key="startIndex + index" class="officer-row mb-8 clearfix">
        <div class="photo-box border border-black float-left mr-4 flex items-center justify-center text-xs">
          <img v-if="allowImageUploads && getPhotoPreview(officer)" 
               :src="getPhotoPreview(officer)" 
               alt="Officer Photo" 
               class="w-full h-full object-cover">
          <span v-else class="text-gray-500">2X2</span>
        </div>
        <div class="officer-details float-left pt-2">
          <div class="field-row mb-4">
            <span class="field-label">Name</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ (officer.student_name || '').toUpperCase() }}</span>
          </div>
          <div class="field-row mb-4">
            <span class="field-label">Position</span>
            <span class="field-colon">:</span>
            <span class="field-value" 
                  :style="{ 
                    fontSize: (officer.position || '').length > 32 ? '9pt' : '11pt',
                    lineHeight: '1.2',
                    wordWrap: 'break-word',
                    overflowWrap: 'break-word',
                    whiteSpace: 'normal',
                    maxWidth: '200px',
                    display: 'inline-block'
                  }">{{ (officer.position || '').toUpperCase() }}</span>
          </div>
          <div class="field-row mb-4">
            <span class="field-label">Student I.D. No.</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ (officer.student_number || '').toUpperCase() }}</span>
          </div>
          <div class="field-row mb-4">
            <span class="field-label">Signature</span>
            <span class="field-colon">:</span>
            <span class="field-value"></span>
          </div>
        </div>
      </div>

      <!-- Pagination Controls -->
      <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mt-8 gap-4">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1"
          class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
          Previous
        </button>
        
        <div class="flex gap-2">
          <button 
            v-for="page in visiblePages" 
            :key="page"
            @click="page === '...' ? null : goToPage(page)"
            :disabled="page === '...'"
            :class="[
              'px-3 py-1 rounded',
              page === '...' 
                ? 'text-gray-400 cursor-default' 
                : currentPage === page 
                  ? 'bg-blue-600 text-white' 
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]">
            {{ page }}
          </button>
        </div>
        
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages"
          class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
          Next
        </button>
      </div>

      <!-- Page Info -->
      <div v-if="totalPages > 1" class="text-center mt-4 text-sm text-gray-600">
        Page {{ currentPage }} of {{ totalPages }} • Officers {{ startIndex + 1 }}-{{ endIndex }} of {{ form.officers.length }}
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
          <input 
            v-model="form.organization_name" 
            @input="form.organization_name = $event.target.value.toUpperCase()"
            class="border p-2 w-full rounded-md" 
            style="text-transform: uppercase;" 
            required>
          <div v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</div>
        </div>

        <div class="flex items-end space-x-2">
          <div>
            <label class="block font-bold">Academic Year</label>
            <div class="flex items-center space-x-2">
              <input 
                v-model="form.academic_year_start" 
                class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center rounded-md" 
                :placeholder="currentYear" 
                readonly 
                tabindex="-1" 
                style="user-select: none; -webkit-user-select: none;" 
              >
              <span class="mx-1">-</span>
              <input 
                v-model="form.academic_year_end" 
                class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center rounded-md" 
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
          <label class="block font-bold">Faculty Adviser Name</label>
          <input 
            v-model="form.adviser_name" 
            @input="form.adviser_name = $event.target.value.toUpperCase()"
            class="border p-2 w-full rounded-md" 
            style="text-transform: uppercase;" 
            required>
          <div v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</div>
        </div>

        <div>
          <label class="block font-bold">Coordinator Name</label>
          <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none rounded-md" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
        </div>
      </div>

      <!-- Officer List Management -->
      <div class="mt-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">Officers</h3>
          <div class="flex gap-2">
            <!-- Download CSV Template Button -->
            <button 
                @click="downloadCSVTemplate" 
                type="button" 
                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors flex items-center gap-1">
                📥 Download Template
            </button>
            <!-- CSV Upload -->
            <div class="flex items-center">
              <label for="csv-upload" class="bg-green-500 text-white px-3 py-1 rounded cursor-pointer hover:bg-green-600 transition-colors">
                📄 Upload CSV
              </label>
              <input 
                id="csv-upload" 
                type="file" 
                @change="handleCSVUpload" 
                accept=".csv,text/csv" 
                class="hidden"
              >
            </div>
            <!-- Clear All Officers -->
            <button 
              @click="clearAllOfficers" 
              type="button" 
              class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors flex items-center gap-1">
              🗑️ Clear All
            </button>
          </div>
        </div>

        <!-- CSV Format Instructions -->
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
          <p class="font-semibold text-blue-800 mb-1">📋 CSV Format Requirements:</p>
          <ul class="text-blue-700 list-disc list-inside space-y-1">
            <li>First row should contain column headers (will be ignored)</li>
            <li>Columns must be in this order: <strong>Officer Name, Position, Student Number</strong></li>
            <li>Additional columns will be ignored</li>
            <li>File must be in CSV format (.csv extension)</li>
          </ul>
        </div>

        <!-- Officer Count Display -->
        <div class="mb-4 p-2 bg-gray-50 border border-gray-200 rounded text-sm">
          <span class="font-semibold">👥 Total Officers: {{ form.officers.length }}</span>
          <span v-if="form.officers.length > 0" class="ml-4 text-gray-600">
            • Page {{ currentPage }} of {{ totalPages }}
          </span>
        </div>

        <div v-for="(officer, idx) in currentPageOfficerInputs" :key="startIndex + idx" class="mt-4 p-4 border rounded">
            <div class="flex justify-between items-center mb-2">
                <h4 class="font-bold">Officer #{{ startIndex + idx + 1 }}</h4>
                <button 
                    @click="removeOfficer(startIndex + idx)" 
                    type="button" 
                    :disabled="form.officers.length <= 1"
                    :class="[
                        'px-2 py-1 rounded text-sm font-medium transition-colors',
                        form.officers.length <= 1 
                            ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
                            : 'text-red-500 hover:text-red-700 hover:bg-red-50'
                    ]"
                >
                    Remove
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold">Name</label>
                    <input 
                      v-model="officer.student_name" 
                      @input="officer.student_name = $event.target.value.toUpperCase()"
                      class="border p-2 w-full rounded-md" 
                      style="text-transform: uppercase;" 
                      required>
                    <div v-if="errors[`officer_${startIndex + idx}_name`]" class="text-red-500 text-sm mt-1">{{ errors[`officer_${startIndex + idx}_name`] }}</div>
                </div>

                <div>
                    <label class="block font-bold">Position</label>
                    <input 
                      v-model="officer.position" 
                      @input="officer.position = $event.target.value.toUpperCase()"
                      class="border p-2 w-full rounded-md" 
                      style="text-transform: uppercase;" 
                      required>
                    <div v-if="errors[`officer_${startIndex + idx}_position`]" class="text-red-500 text-sm mt-1">{{ errors[`officer_${startIndex + idx}_position`] }}</div>
                </div>

                <div>
                    <label class="block font-bold">Student I.D. No.</label>
                    <input v-model="officer.student_number" class="border p-2 w-full rounded-md" required>
                    <div v-if="errors[`officer_${startIndex + idx}_student_number`]" class="text-red-500 text-sm mt-1">{{ errors[`officer_${startIndex + idx}_student_number`] }}</div>
                </div>

                <div v-if="allowImageUploads">
                    <label class="block font-bold">2x2 Photo</label>
                    <input type="file" @change="event => handlePhotoUpload(event, startIndex + idx, 'officers')" class="border p-2 w-full rounded-md" accept="image/*">
                    <div v-if="getPhotoPreview(officer)" class="mt-2">
                        <div class="flex items-center gap-2">
                            <img :src="getPhotoPreview(officer)" alt="Preview" class="w-16 h-16 object-cover border rounded-md">
                            <button 
                                @click="removeOfficerPhoto(startIndex + idx)" 
                                type="button" 
                                class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600 transition-colors flex items-center gap-1"
                                title="Remove photo"
                            >
                                <span class="text-white">✕</span>
                                <span>Remove</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination Controls for Officer Inputs -->
        <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mt-8 gap-4">
            <button 
                @click="prevPage" 
                :disabled="currentPage === 1"
                class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
                Previous
            </button>
            <div class="flex gap-2">
                <button 
                    v-for="page in visiblePages" 
                    :key="page"
                    @click="page === '...' ? null : goToPage(page)"
                    :disabled="page === '...'"
                    :class="[
                        'px-3 py-1 rounded',
                        page === '...' 
                            ? 'text-gray-400 cursor-default' 
                            : currentPage === page 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                    ]">
                    {{ page }}
                </button>
            </div>
            <button 
                @click="nextPage" 
                :disabled="currentPage === totalPages"
                class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
                Next
            </button>
        </div>

        <!-- Add Officer Button (moved below officer list, left-aligned) -->
        <div class="mt-4 flex justify-start">
            <button @click="addOfficer" type="button" class="bg-blue-500 text-white px-3 py-1 rounded">
                Add Officer
            </button>
        </div>

        <div class="mt-6 text-center">
          <!-- Autosave indicator -->
          <div v-if="isAutoSaving" class="mb-3 text-sm text-gray-500 flex items-center justify-center gap-2">
            <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Saving...</span>
          </div>
          <div v-else-if="!isEdit" class="mb-3 text-sm text-green-600 flex items-center justify-center gap-2">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Draft saved</span>
          </div>
          
          <button
            type="submit"
            @click="submit"
            class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:from-green-600 active:to-green-700 transition-all duration-300 relative overflow-hidden group"
            style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif;"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <span>{{ props.isEdit ? 'Update' : 'Submit' }}</span>
            <!-- Conditional icons: Update vs Create -->
            <svg v-if="props.isEdit" xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#e3e3e3" class="ml-2" aria-hidden="true">
              <path d="M480-120q-75 0-140.5-28.5t-114-77q-48.5-48.5-77-114T120-480q0-75 28.5-140.5t77-114q48.5-48.5 114-77T480-840q82 0 155.5 35T760-706v-94h80v240H600v-80h110q-41-56-101-88t-129-32q-117 0-198.5 81.5T200-480q0 117 81.5 198.5T480-200q105 0 183.5-68T756-440h82q-15 137-117.5 228.5T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z"/>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#e3e3e3" class="ml-2" aria-hidden="true">
              <path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- CSV Import Modal -->
    <Modal :show="showCsvModal" @close="closeCsvModal">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 sm:p-6 w-full max-w-xs sm:max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div :class="[
              'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-3',
              csvModalType === 'success' ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900'
            ]">
              <svg v-if="csvModalType === 'success'" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <svg v-else class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            <h3 :class="[
              'text-lg font-semibold',
              csvModalType === 'success' ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100'
            ]">{{ csvModalTitle }}</h3>
          </div>
          <button @click="closeCsvModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="mb-6">
          <p class="text-sm text-gray-600 dark:text-gray-400">{{ csvModalMessage }}</p>
        </div>
        <div class="flex justify-end">
          <button 
            @click="closeCsvModal"
            :class="[
              'inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-xl shadow-sm transition-all duration-300 relative overflow-hidden group',
              csvModalType === 'success' 
                ? 'bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800' 
                : 'bg-gradient-to-r from-red-500 to-red-600 text-white hover:from-red-600 hover:to-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800'
            ]"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <span class="relative z-10">Close</span>
          </button>
        </div>
      </div>
    </Modal>

    <!-- Error Navigation Modal -->
    <Modal :show="showErrorModal" @close="closeErrorModal">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 sm:p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-3 bg-red-100 dark:bg-red-900">
              <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Validation Errors Found</h3>
          </div>
          <button @click="closeErrorModal" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="mb-4">
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
            Found <strong class="text-red-600 dark:text-red-400">{{ totalErrors }}</strong> error(s) across {{ errorPages.length }} location(s). 
            Click on a location below to navigate and fix the errors.
          </p>
        </div>

        <div class="space-y-3 max-h-96 overflow-y-auto">
          <div 
            v-for="(errorPage, index) in errorPages" 
            :key="index"
            class="border border-red-200 dark:border-red-800 rounded-lg p-3 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer"
            @click="errorPage.isFormFields ? closeErrorModal() : goToErrorPage(errorPage.pageNumber)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center mb-2">
                  <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ errorPage.isFormFields ? 'Form Details' : `Page ${errorPage.pageNumber}` }}
                  </span>
                  <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-full">
                    {{ errorPage.errorCount }} error{{ errorPage.errorCount > 1 ? 's' : '' }}
                  </span>
                </div>
                
                <div class="ml-6 space-y-1">
                  <div v-for="(error, errIdx) in errorPage.errors" :key="errIdx" class="text-sm">
                    <span v-if="error.officerIndex" class="font-medium text-gray-700 dark:text-gray-300">
                      Officer #{{ error.officerIndex }}:
                    </span>
                    <span class="text-red-600 dark:text-red-400">
                      {{ error.errors.join(', ') }}
                    </span>
                  </div>
                </div>
              </div>
              
              <svg v-if="!errorPage.isFormFields" class="w-5 h-5 text-gray-400 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button 
            @click="closeErrorModal"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 rounded-xl shadow-sm hover:from-red-600 hover:to-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 relative overflow-hidden group"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <span class="relative z-10">Close</span>
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<style scoped>
.form-container {
  font-family: "Times New Roman", Times, serif;
}

.form-content {
  font-family: "Times New Roman", Times, serif;
}

input, textarea, select {
  font-family: "Times New Roman", Times, serif;
}

button {
  font-family: "Times New Roman", Times, serif;
}

.photo-box {
  width: 2in;
  height: 2in;
}

.officer-details {
  padding-top: 0.4cm;
}

.field-label {
  display: inline-block;
  width: 120px;
}

.field-colon {
  display: inline-block;
  width: 10px;
  margin-right: 10px;
}

.field-value {
  display: inline-block;
  min-width: 200px;
}

.clearfix::after {
  content: "";
  clear: both;
  display: table;
}

.officer-row {
  height: 5.2cm;
  clear: both;
}

.university-name {
  max-width: 60%;
  height: auto;
  margin: 4px 0;
  display: inline-block;
}

.footer {
  font-family: Calibri, 'Calibri', 'Helvetica Neue', Arial, sans-serif;
}
</style>