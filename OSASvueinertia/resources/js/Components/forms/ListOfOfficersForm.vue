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

// Add errors ref object
const errors = ref({});

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
        alert('Please upload a CSV file only.');
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
                alert('No data found in CSV file.');
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
            
            alert(`Successfully imported ${form.officers.length} officers from CSV file.`);
            
        } catch (error) {
            console.error('Error parsing CSV:', error);
            alert('Error reading CSV file. Please check the file format.');
        }
    };
    
    reader.readAsText(file);
    
    // Reset the file input
    event.target.value = '';
};

const emit = defineEmits(['submitted']);

// Add pagination state
const currentPage = ref(1);
const officersPerPage = 5;

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

const form = useForm({
  form_type: 'LSPU-OSAS-SF-007',
  organization_name: props.initialFormData.organization_name || '',
  president_name: props.initialFormData.president_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',

  academic_year_end: props.initialFormData.academic_year_end || '',
  academic_year_start: props.initialFormData.academic_year_start || '',
  officers: [],
});

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
  
  // Validate main form fields
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization name is required';
  }
  
  if (!form.academic_year_start.trim()) {
    errors.value.academic_year_start = 'Academic year start is required';
  }
  
  if (!form.academic_year_end.trim()) {
    errors.value.academic_year_end = 'Academic year end is required';
  }
  
  if (!form.president_name.trim()) {
    errors.value.president_name = 'President name is required';
  }
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Faculty adviser name is required';
  }
  
  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator name is required';
  }
  
  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean/Assoc. Dean name is required';
  }
  
  // Validate officers
  form.officers.forEach((officer, index) => {
    if (!officer.student_name.trim()) {
      errors.value[`officer_${index}_name`] = 'Officer name is required';
    }
    
    if (!officer.position.trim()) {
      errors.value[`officer_${index}_position`] = 'Officer position is required';
    }
    
    if (!officer.student_number.trim()) {
      errors.value[`officer_${index}_student_number`] = 'Student I.D. number is required';
    }
  });
  
  // Return true if no errors
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
  <div class="form-container">
    <!-- Remove the List of Officers Form heading above the logo -->
    <!-- <h2 class="text-lg font-bold mb-4">List of Officers Form</h2> -->
    
    <!-- Officer list preview -->
    <div class="mt-6 form-content">
      <div class="header text-center relative">
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5rem] left-[-2rem] w-[250px] h-auto">
        <p class="text-sm font-normal mb-0">Republic of the Philippines</p>
        <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
        <p class="text-sm mb-0">Province of Laguna</p>
        <p class="text-sm font-bold mb-0">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
      </div>
      
      <div class="organization-details text-center mt-2 mb-4">
        <p class="mb-1">Name of Organization: {{ form.organization_name }}</p>
        <p class="mb-0">A.Y. 20{{ form.academic_year_start }}-20{{ form.academic_year_end }}</p>
      </div>
      
      <div class="list-title text-center font-bold mb-4 text-lg">LIST OF OFFICERS</div>
      
      <!-- Officers list with pagination -->
      <div v-for="(officer, index) in currentPageOfficers" :key="startIndex + index" class="officer-row mb-8 clearfix">
        <div class="photo-box border border-black float-left mr-4 flex items-center justify-center text-xs">
          <img v-if="getPhotoPreview(officer)" 
               :src="getPhotoPreview(officer)" 
               alt="Officer Photo" 
               class="w-full h-full object-cover">
          <span v-else class="text-gray-500">2X2</span>
        </div>
        <div class="officer-details float-left pt-2">
          <div class="field-row mb-4">
            <span class="field-label">Name</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ officer.student_name || '' }}</span>
          </div>
          <div class="field-row mb-4">
            <span class="field-label">Position</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ officer.position || '' }}</span>
          </div>
          <div class="field-row mb-4">
            <span class="field-label">Student I.D. No.</span>
            <span class="field-colon">:</span>
            <span class="field-value">{{ officer.student_number || '' }}</span>
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
          <input v-model="form.organization_name" class="border p-2 w-full" required>
          <div v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</div>
        </div>

        <div>
          <label class="block font-bold">Academic Year Start</label>
          <input v-model="form.academic_year_start" class="border p-2 w-full" placeholder="20__" required>
          <div v-if="errors.academic_year_start" class="text-red-500 text-sm mt-1">{{ errors.academic_year_start }}</div>
        </div>

        <div>
          <label class="block font-bold">Academic Year End</label>
          <input v-model="form.academic_year_end" class="border p-2 w-full" placeholder="20__" required>
          <div v-if="errors.academic_year_end" class="text-red-500 text-sm mt-1">{{ errors.academic_year_end }}</div>
        </div>

        <div>
          <label class="block font-bold">President Name</label>
          <input v-model="form.president_name" class="border p-2 w-full" required>
          <div v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</div>
        </div>

        <div>
          <label class="block font-bold">Faculty Adviser Name</label>
          <input v-model="form.adviser_name" class="border p-2 w-full" required>
          <div v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</div>
        </div>

        <div>
          <label class="block font-bold">Coordinator Name</label>
          <input v-model="form.coordinator_name" class="border p-2 w-full" required>
          <div v-if="errors.coordinator_name" class="text-red-500 text-sm mt-1">{{ errors.coordinator_name }}</div>
        </div>

        <div>
          <label class="block font-bold">Dean/Assoc. Dean Name</label>
          <input v-model="form.dean_name" class="border p-2 w-full" required>
          <div v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</div>
        </div>
      </div>

      <!-- Officer List Management -->
      <div class="mt-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">Officers</h3>
          <div class="flex gap-2">
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
          </div>
        </div>

        <!-- CSV Format Instructions -->
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
          <p class="font-semibold text-blue-800 mb-1">📋 CSV Format Requirements:</p>
          <ul class="text-blue-700 list-disc list-inside space-y-1">
            <li>First row should contain column headers (will be ignored)</li>
            <li>Columns must be in this order: <strong>Name, Position, Student I.D. No.</strong></li>
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
                    <input v-model="officer.student_name" class="border p-2 w-full" required>
                    <div v-if="errors[`officer_${startIndex + idx}_name`]" class="text-red-500 text-sm mt-1">{{ errors[`officer_${startIndex + idx}_name`] }}</div>
                </div>

                <div>
                    <label class="block font-bold">Position</label>
                    <input v-model="officer.position" class="border p-2 w-full" required>
                    <div v-if="errors[`officer_${startIndex + idx}_position`]" class="text-red-500 text-sm mt-1">{{ errors[`officer_${startIndex + idx}_position`] }}</div>
                </div>

                <div>
                    <label class="block font-bold">Student I.D. No.</label>
                    <input v-model="officer.student_number" class="border p-2 w-full" required>
                    <div v-if="errors[`officer_${startIndex + idx}_student_number`]" class="text-red-500 text-sm mt-1">{{ errors[`officer_${startIndex + idx}_student_number`] }}</div>
                </div>

                <div>
                    <label class="block font-bold">2x2 Photo</label>
                    <input type="file" @change="event => handlePhotoUpload(event, startIndex + idx, 'officers')" class="border p-2 w-full" accept="image/*">
                    <div v-if="getPhotoPreview(officer)" class="mt-2">
                        <div class="flex items-center gap-2">
                            <img :src="getPhotoPreview(officer)" alt="Preview" class="w-16 h-16 object-cover border">
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
          <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
        </div>
      </div>
    </div>
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
</style>