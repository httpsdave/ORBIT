<script setup>
// College options for the select dropdown
const collegeOptions = [
    'None',
    'College of Computer Studies',
    'College of Arts and Sciences',
    'College of Engineering',
    'College of Industrial Technology',
    'College of International Hospitality and Tourism Management',
    'College of Teacher Education',
    'College of Criminal Justice Education',
    'College of Business Administration and Accountancy'
];

// Handler for college select change
function handleCollegeChange(e) {
    const selected = e.target.value;
    if (selected === 'None') {
        form.college = '';
    } else {
        form.college = selected.replace('College of ', '');
    }
}
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useFormAutoSave } from '@/Composables/useFormAutoSave';
import Modal from '@/Components/Modal.vue';
import SubmissionConfirmationModal from '@/Components/SubmissionConfirmationModal.vue';

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

const backHref = computed(() => props.isEdit ? '/applications' : '/applications/select-form');

// Add errors ref object
const errors = ref({
  college: '',
  organization_name: '',
  president_name: ''
});

// Autosave state
const showRestorePrompt = ref(false);
const autosavedData = ref(null);

// Submission confirmation modal state
const showConfirmationModal = ref(false);

// CSV modal states
const showCsvModal = ref(false);
const csvModalTitle = ref('');
const csvModalMessage = ref('');
const csvModalType = ref('success'); // 'success' or 'error'

const closeCsvModal = () => {
  showCsvModal.value = false;
  csvModalTitle.value = '';
  csvModalMessage.value = '';
  csvModalType.value = 'success';
};

// Add a function to add a new attendee
const addAttendee = () => {
    form.attendees.push({
        name: '',
        course_year_section: '',
        signature: null
    });
};

// CSV template download functionality
const downloadCSVTemplate = () => {
  // Create CSV content with headers and sample data
  const csvContent = [
    'Name,Course/Year & Section,Do not fill beyond this point',
    'First Name M.I. Last Name,BSCS-4IS1,',
    'First Name M.I. Last Name,BSCS-4IS2,',
    'First Name M.I. Last Name,BSCS-4GAV1,',
    ',,',
  ].join('\n');
  // Create blob and download
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', 'activity_attendance_template.csv');
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
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
            // Clear existing attendees
            form.attendees = [];
            // Process each row
            dataRows.forEach((row) => {
                const columns = row.split(',').map(col => col.trim().replace(/"/g, ''));
                // Extract first 2 columns only
                const name = columns[0] || '';
                const courseYearSection = columns[1] || '';
                // Add attendee if at least one field has data
                if (name || courseYearSection) {
                    form.attendees.push({
                        name: name,
                        course_year_section: courseYearSection,
                        signature: null
                    });
                }
            });
            // Reset to first page after upload
            currentPage.value = 1;
            csvModalTitle.value = 'Import Successful';
            csvModalMessage.value = `Successfully imported ${form.attendees.length} attendees from CSV file.`;
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

// Add a function to remove an attendee
const removeAttendee = (index) => {
    form.attendees.splice(index, 1);
};

// Add function to clear all attendees
const clearAllAttendees = () => {
    // Keep only the first attendee but clear its fields
    form.attendees = [{
        name: '',
        course_year_section: '',
        signature: null
    }];
    
    // Reset to first page
    currentPage.value = 1;
};

// Add validateForm function
const validateForm = () => {
  // Clear previous errors
  Object.keys(errors.value).forEach(key => {
    errors.value[key] = '';
  });

  let isValid = true;

  // Check each required field
    // College is nullable, so no required validation

  if (!form.organization_name || form.organization_name.trim() === '') {
    errors.value.organization_name = 'Organization Name is required';
    isValid = false;
  }

  // president_name validation removed for Activity Attendance Form

  return isValid;
};

const emit = defineEmits(['submitted']);

const form = useForm({
  form_type: 'LSPU-OSAS-SF-009',
  organization_name: props.initialFormData.organization_name?.toUpperCase() || '',
  // president_name removed for Activity Attendance Form
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
  
  college: props.initialFormData.college?.toUpperCase() || '',
  activity_name: props.initialFormData.activity_name || '',
  activity_date: props.initialFormData.activity_date || '',
  attendees: [],

});

// Initialize with data from props if available
if (props.initialFormData?.attendees && props.initialFormData.attendees.length > 0) {
  // Copy attendees from initialFormData
  form.attendees = [...props.initialFormData.attendees];
} else {
  // Add default empty attendees
  for(let i = 0; i < 4; i++) {
                addAttendee();

  }
}

// Initialize autosave (disabled by default)
const { isAutoSaving, enable: enableAutoSave, disable: disableAutoSave, start: startAutoSave, stop: stopAutoSave } = useFormAutoSave(form, 'activity_attendance', { enabled: false });

// Pagination state (must be after form is defined)
const attendeesPerPage = 10;
const currentPage = ref(1);

const totalPages = computed(() => {
    return Math.ceil(form.attendees.length / attendeesPerPage);
});

const paginatedAttendees = computed(() => {
    const start = (currentPage.value - 1) * attendeesPerPage;
    return form.attendees.slice(start, start + attendeesPerPage);
});

const goToPrevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};
const goToNextPage = () => {
    if (currentPage.value < totalPages.value) currentPage.value++;
};

// Computed for visible page numbers with ellipsis
const visiblePages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const delta = 2;
    if (total <= 7) {
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

const goToPage = (page) => {
    if (typeof page === 'number' && page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// When attendees are added/removed, ensure currentPage is valid
watch(
    () => form.attendees.length,
    (newLen) => {
        if (currentPage.value > totalPages.value) {
            currentPage.value = totalPages.value || 1;
        }
    }
);

const handleSubmitClick = () => {
  if (!validateForm()) {
    return;
  }
  showConfirmationModal.value = true;
};

const handleConfirmSubmit = () => {
  showConfirmationModal.value = false;
  submit();
};

const handleCancelSubmit = () => {
  showConfirmationModal.value = false;
};

const submit = () => {
  // Stop autosave before submission
  stopAutoSave();

  // Check if we're in edit mode
  if (props.isEdit) {
    // For edit mode, just emit the data - don't make HTTP request here
    emit('submitted', form.data());
  } else {
    // For create mode, make the POST request
    form.post('/applications', {
      onSuccess: async () => {
        // Delete autosaved data after successful submission
        try {
          await fetch('/delete-autosaved-form-data', {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ form_type: 'activity_attendance' })
          });
        } catch (error) {
          console.error('Failed to delete autosaved data:', error);
        }
        emit('submitted', form.data());
      },
      onError: (errors) => {
        console.error('Form submission errors:', errors);
      }
    });
  }
};

// Autosave functions
const restoreAutosave = () => {
  if (autosavedData.value) {
    // Store application_date before restore
    const currentApplicationDate = form.application_date;

    Object.assign(form, autosavedData.value);

    // Restore application_date to current value (don't overwrite from autosave)
    form.application_date = currentApplicationDate;

    showRestorePrompt.value = false;
    enableAutoSave();
    startAutoSave();
  }
};

const dismissAutosave = async () => {
  showRestorePrompt.value = false;
  enableAutoSave();
  startAutoSave();

  // Delete the autosaved data
  try {
    await fetch('/delete-autosaved-form-data', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ form_type: 'activity_attendance' })
    });
  } catch (error) {
    console.error('Failed to delete autosaved data:', error);
  }
};

// Lifecycle hooks
onMounted(async () => {
  // Fetch autosaved data
  try {
    const response = await fetch('/get-autosaved-form-data?form_type=activity_attendance', {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      }
    });

    if (response.ok) {
      const data = await response.json();
      if (data.form_data) {
        autosavedData.value = data.form_data;

        // Compare timestamps: if autosaved data is newer than initialized data, show prompt
        const autosavedTimestamp = new Date(data.updated_at).getTime();
        const initializedTimestamp = props.initialFormData?.updated_at 
          ? new Date(props.initialFormData.updated_at).getTime() 
          : 0;

        if (autosavedTimestamp > initializedTimestamp) {
          showRestorePrompt.value = true;
        } else {
          // Initialized data is newer or same, just enable autosave
          enableAutoSave();
          startAutoSave();
        }
      } else {
        // No autosaved data, just enable autosave
        enableAutoSave();
        startAutoSave();
      }
    } else {
      // No autosaved data found, enable autosave
      enableAutoSave();
      startAutoSave();
    }
  } catch (error) {
    console.error('Failed to fetch autosaved data:', error);
    // On error, still enable autosave
    enableAutoSave();
    startAutoSave();
  }
});

onUnmounted(() => {
  stopAutoSave();
});
</script>

<template>
  <div class="mt-6 form-content">
    <!-- Restore Autosave Prompt Modal -->
    <div v-if="showRestorePrompt" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="font-family: system-ui, -apple-system, sans-serif;">
      <div class="bg-white rounded-lg p-6 max-w-md shadow-xl">
        <h3 class="text-lg font-semibold mb-4">Restore Autosaved Data?</h3>
        <p class="text-gray-600 mb-6">We found an autosaved version of this form. Would you like to restore it?</p>
        <div class="flex gap-3 justify-end">
          <button 
            @click="dismissAutosave"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors"
          >
            Dismiss
          </button>
          <button 
            @click="restoreAutosave"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
          >
            Restore
          </button>
        </div>
      </div>
    </div>
    <!-- Header matching blade template -->
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
      <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute logo" style="position: absolute; margin-top: -40px; left: -2cm; width: 250px; height: auto;">
      <div class="font-normal text-[10pt] leading-tight header-text" style="font-family:Calibri,sans-serif;">
        Republic of the Philippines<br>
        <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="inline-block align-middle max-w-[45%] my-1 university-name" style="max-width: 45%; height: auto; margin: 3px 0; display: inline-block;" /><br>
        <span class="block mb-3 province-text" style="margin-bottom: 12px; display: block;">Province of Laguna</span>
      </div>
      <div class="font-bold text-[11pt] office-title" style="font-family:Calibri,sans-serif; font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:30px; display: block;">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
      <div class="font-bold text-[11pt] sub-header" style="font-family:Calibri,sans-serif; font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:6px; display: block; font-style: italic;">STUDENT ACTIVITY ATTENDANCE SHEET</div>
      <div v-if="form.college" class="font-bold text-[11pt] college-header" style="font-family:Calibri,sans-serif; font-size:11pt; font-weight:bold; margin-top:6px; display: block;">
        COLLEGE OF {{ form.college.toUpperCase() }}
      </div>
    </div>

    <!-- Activity and Date row matching blade template -->
    <div class="form-row" style="margin-top: 15px; clear: both;">
      <div class="form-field" style="float: left; width: 60%;">
        <span class="font-bold" style="font-family:'Calibri',sans-serif; font-size:11pt; font-weight:bold;">ACTIVITY: </span>
        <span style="border-bottom: 1px solid black; display: inline-block; min-width: 300px; font-family:'Calibri',sans-serif; font-size:11pt; font-weight:bold;">{{ form.activity_name || '' }}</span>
      </div>
      <div class="form-field" style="float: right; width: 30%; text-align: right;">
        <span class="font-bold" style="font-family:'Calibri',sans-serif; font-size:11pt; font-weight:bold;">DATE: </span>
        <span style="border-bottom: 1px solid black; display: inline-block; min-width: 100px; font-family:'Calibri',sans-serif; font-size:11pt; font-weight:bold;">{{ form.activity_date ? new Date(form.activity_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '' }}</span>
      </div>
      <div style="clear: both;"></div>
    </div>

    <!-- Attendance Table matching blade template -->
    <div class="attendance-table" style="margin-top: 20px;">
      <table style="width: 100%; border-collapse: collapse; border: 1px solid black;">
        <thead>
          <tr>
            <th style="border: 1px solid black; padding: 4px; text-align: center; font-family:'Calibri',sans-serif; font-size:10pt; font-weight:bold; width: 50%;">NAME</th>
            <th style="border: 1px solid black; padding: 4px; text-align: center; font-family:'Calibri',sans-serif; font-size:10pt; font-weight:bold; width: 25%;">COURSE/YEAR &<br>SECTION</th>
            <th style="border: 1px solid black; padding: 4px; text-align: center; font-family:'Calibri',sans-serif; font-size:10pt; font-weight:bold; width: 25%;">SIGNATURE</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(attendee, index) in form.attendees.slice(0, 35)" :key="index">
            <td style="border: 1px solid black; padding: 2px; height: 18px; font-family:'Calibri',sans-serif; font-size:10pt; text-align: left;">
              <span style="float: left; margin-right: 5px;">{{ index + 1 }}.</span>
              {{ attendee.name }}
            </td>
            <td style="border: 1px solid black; padding: 2px; height: 18px; font-family:'Calibri',sans-serif; font-size:10pt; text-align: center;">{{ attendee.course_year_section }}</td>
            <td style="border: 1px solid black; padding: 2px; height: 18px;">&nbsp;</td>
          </tr>
          <!-- Fill remaining rows up to 35 -->
          <tr v-for="i in Math.max(0, 35 - form.attendees.length)" :key="'empty-' + i">
            <td style="border: 1px solid black; padding: 2px; height: 18px; font-family:'Calibri',sans-serif; font-size:10pt;">
              <span style="float: left; margin-right: 5px;">{{ form.attendees.length + i }}.</span>
            </td>
            <td style="border: 1px solid black; padding: 2px; height: 18px;">&nbsp;</td>
            <td style="border: 1px solid black; padding: 2px; height: 18px;">&nbsp;</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer matching blade template -->
    <div class="footer" style="margin-top: 20px; position: relative; height: 20px; font-family:Calibri,sans-serif; font-size:10pt;">
      <div style="position: absolute; left: 0;">LSPU-OSAS-SF-009</div>
      <div style="position: absolute; left: 50%; transform: translateX(-50%);">Rev. 0</div>
      <div style="position: absolute; right: 0;">10 August 2016</div>
    </div>

    <!-- Form inputs -->
    <div class="mt-8 border-t pt-6">
        <h3 class="text-lg font-bold mb-4">Form Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                                <label class="block font-bold">College</label>
                                <select 
                                    :value="form.college ? ('College of ' + form.college) : 'None'"
                                    @change="handleCollegeChange"
                                    class="border p-2 w-full text-black rounded-md"
                                >
                                    <option value="" disabled>Select College</option>
                                    <option v-for="option in collegeOptions" :key="option" :value="option">{{ option }}</option>
                                </select>
                                <p v-if="errors.college" class="text-red-500 text-sm mt-1">{{ errors.college }}</p>
                        </div>

            <div>
                <label class="block font-bold">Activity Name</label>
                <input v-model="form.activity_name" class="border p-2 w-full rounded-md">
            </div>

            <div>
          <label class="block font-bold">Activity Date</label>
            <input type="date" v-model="form.activity_date" class="border p-2 w-full rounded-md">
            </div>

            <div>
                <label class="block font-bold">Organization Name</label>
                <input 
                  v-model="form.organization_name" 
                  @input="form.organization_name = $event.target.value.toUpperCase()"
                  class="border p-2 w-full rounded-md" 
                  style="text-transform: uppercase;" 
                  required>
                <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
            </div>

            <!-- President Name field removed for Activity Attendance Form -->
        </div>

    <!-- Attendees Table -->
    <div class="mt-6">
        <h4 class="text-md font-bold mb-2">Attendees</h4>
        
        <!-- Member Count Display and CSV Upload -->
        <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
      <div class="font-semibold text-sm">👥 Total Attendees: {{ form.attendees.length }}</div>
      <div class="flex items-center gap-2">
        <!-- Download CSV Template Button -->
        <button 
          @click="downloadCSVTemplate" 
          type="button" 
          class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors flex items-center gap-1">
          📥 Download Template
        </button>
        <!-- CSV Upload -->
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
        <!-- Clear All Attendees -->
        <button 
          @click="clearAllAttendees" 
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
                <li>Columns must be in this order: <strong>Name, Course/Year & Section</strong></li>
                <li>Additional columns will be ignored</li>
                <li>File must be in CSV format (.csv extension)</li>
            </ul>
        </div>
        <table class="w-full border-collapse border border-gray-300 mb-4">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 p-2 w-10">NO.</th>
                    <th class="border border-gray-300 p-2 w-1/2">NAME</th>
                    <th class="border border-gray-300 p-2 w-1/4">COURSE/YEAR & SECTION</th>
                    <th class="border border-gray-300 p-2 w-16">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(attendee, index) in paginatedAttendees" :key="(currentPage - 1) * attendeesPerPage + index">
                    <td class="border border-gray-300 p-2 text-center">
                        {{ (currentPage - 1) * attendeesPerPage + index + 1 }}.
                    </td>
            <td class="border border-gray-300 p-2">
            <input v-model="attendee.name" class="w-full p-1 rounded-md">
          </td>
          <td class="border border-gray-300 p-2">
            <input v-model="attendee.course_year_section" class="w-full p-1 rounded-md">
          </td>
                    <td class="border border-gray-300 p-2">
                        <button type="button" @click="removeAttendee((currentPage - 1) * attendeesPerPage + index)" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
        
    <button type="button" @click="addAttendee" class="bg-blue-500 text-white px-3 py-1 rounded mb-4">
            Add Attendee Row
        </button>
    </div>

    <!-- Pagination Controls -->
    <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mt-8 gap-4">
        <button 
            type="button"
            @click="goToPrevPage" 
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
            type="button"
            @click="goToNextPage" 
            :disabled="currentPage === totalPages"
            class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
            Next
        </button>
    </div>
    <!-- Page Info -->
    <div v-if="totalPages > 1" class="text-center mt-4 text-sm text-gray-600">
        Page {{ currentPage }} of {{ totalPages }} • Attendees {{ (currentPage - 1) * attendeesPerPage + 1 }}-{{ Math.min(currentPage * attendeesPerPage, form.attendees.length) }} of {{ form.attendees.length }}
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
          type="button"
          @click="handleSubmitClick"
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

    <!-- Submission Confirmation Modal -->
    <SubmissionConfirmationModal 
      :show="showConfirmationModal"
      :isEdit="isEdit"
      @confirm="handleConfirmSubmit"
      @cancel="handleCancelSubmit"
    />

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

</div>

</template>