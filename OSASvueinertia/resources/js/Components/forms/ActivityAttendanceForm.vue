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
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

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

// Add errors ref object
const errors = ref({
  college: '',
  organization_name: '',
  president_name: ''
});

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
        // Removed system alert for successful submission
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
    <!-- Header matching blade template -->
    <div class="header text-center relative">
      <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute logo" style="position: absolute; margin-top: -40px; left: -2cm; width: 250px; height: auto;">
      <div class="font-normal text-[10pt] leading-tight header-text" style="font-family:Calibri,sans-serif;">
        Republic of the Philippines<br>
        <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="inline-block align-middle max-w-[45%] my-1 university-name" style="max-width: 45%; height: auto; margin: 3px 0; display: inline-block;" /><br>
        <span class="block mb-3 province-text" style="margin-bottom: 12px; display: block;">Province of Laguna</span>
      </div>
      <div class="font-bold text-[11pt] office-title" style="font-family:Calibri,sans-serif; font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:30px; display: block;">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
      <div class="font-bold text-[11pt] sub-header" style="font-family:Calibri,sans-serif; font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:6px; display: block; font-style: italic;">STUDENT ACTIVITY ATTENDANCE SHEET</div>
      <div class="font-bold text-[11pt] college-header" style="font-family:Calibri,sans-serif; font-size:11pt; font-weight:bold; margin-top:6px; display: block;">
        COLLEGE OF {{ form.college ? form.college.toUpperCase() : '' }}
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
                                    class="border p-2 w-full text-black"
                                >
                                    <option value="" disabled>Select College</option>
                                    <option v-for="option in collegeOptions" :key="option" :value="option">{{ option }}</option>
                                </select>
                                <p v-if="errors.college" class="text-red-500 text-sm mt-1">{{ errors.college }}</p>
                        </div>

            <div>
                <label class="block font-bold">Activity Name</label>
                <input v-model="form.activity_name" class="border p-2 w-full">
            </div>

            <div>
                <label class="block font-bold">Activity Date</label>
                <input type="date" v-model="form.activity_date" class="border p-2 w-full">
            </div>

            <div>
                <label class="block font-bold">Organization Name</label>
                <input 
                  v-model="form.organization_name" 
                  @input="form.organization_name = $event.target.value.toUpperCase()"
                  class="border p-2 w-full" 
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
                        <input v-model="attendee.name" class="w-full p-1">
                    </td>
                    <td class="border border-gray-300 p-2">
                        <input v-model="attendee.course_year_section" class="w-full p-1">
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
        <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">{{ props.isEdit ? 'Update' : 'Submit' }}</button>
    </div>
</div>

    <!-- CSV Import Modal -->
    <Modal :show="showCsvModal" @close="closeCsvModal">
      <div class="p-6">
        <div class="flex items-center mb-4">
          <div :class="[
            'flex-shrink-0 w-10 h-10 mx-auto rounded-full flex items-center justify-center',
            csvModalType === 'success' ? 'bg-green-100' : 'bg-red-100'
          ]">
            <svg v-if="csvModalType === 'success'" class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
          <div class="ml-4">
            <h3 :class="[
              'text-lg font-medium',
              csvModalType === 'success' ? 'text-green-900' : 'text-red-900'
            ]">{{ csvModalTitle }}</h3>
          </div>
        </div>
        <div class="mb-4">
          <p class="text-sm text-gray-600">{{ csvModalMessage }}</p>
        </div>
        <div class="flex justify-end">
          <button 
            @click="closeCsvModal"
            :class="[
              'px-4 py-2 text-white rounded hover:opacity-90 transition-opacity',
              csvModalType === 'success' ? 'bg-green-600' : 'bg-red-600'
            ]"
          >
            Close
          </button>
        </div>
      </div>
    </Modal>

</div>

</template>