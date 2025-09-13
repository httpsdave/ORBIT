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
    form.college = selected.replace('College of ', '').toUpperCase();
  }
}
import { ref, computed } from 'vue';
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

const emit = defineEmits(['submitted', 'error']);

// Add errors ref object
const errors = ref({});

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

// Computed properties for displaying combined names with prefix/suffix
const displayAdviserName = computed(() => {
  let name = form.adviser_name || '';
  if (form.adviser_prefix) {
    name = form.adviser_prefix + ' ' + name;
  }
  if (form.adviser_suffix) {
    name = name + ', ' + form.adviser_suffix;
  }
  return name;
});

const displayDeanName = computed(() => {
  let name = form.dean_name || '';
  if (form.dean_prefix) {
    name = form.dean_prefix + ' ' + name;
  }
  if (form.dean_suffix) {
    name = name + ', ' + form.dean_suffix;
  }
  return name;
});

// Add pagination state
const currentPage = ref(1);
const studentsPerPage = 1; // 1 student per page since each certification is a full page

// Add a function to add a new empty student
const addStudent = () => {
  form.students.push({
    student_name: '',
    course: '',
    year_section: '',
    course_year_section: '',
    position_rank: '',
    college: form.college || '',
    certification_date: form.certification_date, // Always include certification_date
  });
};

// Add a function to remove a student
const removeStudent = (index) => {
    // Prevent removing the last student
    if (form.students.length <= 1) {
        return;
    }
    form.students.splice(index, 1);
};

// CSV template download functionality
const downloadCSVTemplate = () => {
  // Create CSV content with headers and sample data
  const csvContent = [
    'Student Name,Course,Year & Section,Position/Rank,Do not fill beyond this point',
    'First Name M.I. Last Name,BSCS,4IS1,President,',
    'First Name M.I. Last Name,BSCS,4IS2,Vice President,',
    'First Name M.I. Last Name,BSCS,4GAV1,Treasurer,',
    ',,,,',
  ].join('\n');
  // Create blob and download
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', 'student_certification_template.csv');
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

            // Clear existing students
            form.students = [];
            
            // Process each row
            dataRows.forEach((row, index) => {
                const columns = row.split(',').map(col => col.trim().replace(/"/g, ''));
                
                // Extract columns: Student Name, Course, Year Section, Position/Rank
                const studentName = columns[0] || '';
                const course = columns[1] || '';
                const yearSection = columns[2] || '';
                const positionRank = columns[3] || '';
                
          // Add student if at least one field has data
          if (studentName || course || yearSection) {
            form.students.push({
              student_name: studentName.toUpperCase(),
              course: course.toUpperCase(),
              year_section: yearSection.toUpperCase(),
              course_year_section: course && yearSection ? `${course.toUpperCase()}, ${yearSection.toUpperCase()}` : (course || yearSection).toUpperCase(),
              position_rank: positionRank.toUpperCase(),
              certification_date: form.certification_date, // Always include certification_date
            });
          }
            });
            
            // Reset to first page after upload
            currentPage.value = 1;
            
            csvModalTitle.value = 'Import Successful';
            csvModalMessage.value = `Successfully imported ${form.students.length} students from CSV file.`;
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

// Pagination computed properties
const totalPages = computed(() => Math.ceil(form.students.length / studentsPerPage));
const startIndex = computed(() => (currentPage.value - 1) * studentsPerPage);
const endIndex = computed(() => Math.min(startIndex.value + studentsPerPage, form.students.length));
const currentPageStudents = computed(() => {
    return form.students.slice(startIndex.value, endIndex.value);
});

// Add computed for current page's student input forms
const currentPageStudentInputs = computed(() => {
    return form.students.slice(startIndex.value, endIndex.value);
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

// Function to update course_year_section when course or year_section changes
const updateCourseYearSection = (studentIndex) => {
  const student = form.students[studentIndex];
  if (student.course && student.year_section) {
    student.course_year_section = `${student.course}, ${student.year_section}`;
  } else {
    student.course_year_section = student.course || student.year_section || '';
  }
};

const form = useForm({
  form_type: 'LSPU-OSAS-SF-006',
  organization_name: props.initialFormData.organization_name?.toUpperCase() || '',
  // president_name removed for Student Certification Form
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name?.toUpperCase() || '',
  adviser_prefix: props.initialFormData.adviser_prefix || '',
  adviser_suffix: props.initialFormData.adviser_suffix || '',
  dean_name: props.initialFormData.dean_name?.toUpperCase() || '',
  dean_prefix: props.initialFormData.dean_prefix || '',
  dean_suffix: props.initialFormData.dean_suffix || '',
  director_name: props.initialFormData.director_name?.toUpperCase() || '',
  coordinator_name: props.initialFormData.coordinator_name?.toUpperCase() || '',
  college: props.initialFormData.college?.toUpperCase() || '',
  certification_date: new Date().toISOString().slice(0, 10), // Always current date
  students: (props.initialFormData.students || []).map(student => ({
    ...student,
    student_name: student.student_name?.toUpperCase() || '',
    course: student.course?.toUpperCase() || '',
    year_section: student.year_section?.toUpperCase() || '',
    course_year_section: student.course_year_section?.toUpperCase() || '',
    position_rank: student.position_rank?.toUpperCase() || '',
    certification_date: student.certification_date || new Date().toISOString().slice(0, 10), // Ensure each student has certification_date
  })),
});

// Initialize with data from props if available
if (!form.students || form.students.length === 0) {
  addStudent();
}

const validateForm = () => {
  errors.value = {};
  
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
  }
  
  // president_name validation removed for Student Certification Form
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Faculty Adviser is required';
  }
  
  // Dean name is now optional

  if (!form.director_name.trim()) {
    errors.value.director_name = 'Director/Chairperson is required';
  }
  
  // Validate students
  form.students.forEach((student, index) => {
    if (!student.student_name.trim()) {
      errors.value[`student_${index}_name`] = 'Student Name is required';
    }
    if (!student.course.trim()) {
      errors.value[`student_${index}_course`] = 'Course is required';
    }
    if (!student.year_section.trim()) {
      errors.value[`student_${index}_year_section`] = 'Year and Section is required';
    }
    // college is optional (can be "None")
  });
  
  return Object.keys(errors.value).length === 0;
};

// REMOVE: statusMessage, statusType, showStatus, showBanner

const submit = () => {
  if (!validateForm()) {
    emit('error', 'Please fill in all required fields.');
    return;
  }
  // Only send student fields that exist, and ensure organization_name and college are included in each student
  const data = {
    ...form.data(),
    students: form.students.map(student => ({
      ...student,
      course_year_section: student.course && student.year_section ? `${student.course}, ${student.year_section}` : (student.course || student.year_section || ''),
      organization_name: form.organization_name,
      college: form.college,
      certification_date: form.certification_date, // Always set to current date from form
      is_bonafide: 0,
      is_not_academic_probation: 0,
      is_not_disciplinary_probation: 0,
      has_position: 0,
    }))
  };
  if (props.isEdit) {
    emit('submitted', data);
  } else {
    form.post('/applications', {
      data,
      onSuccess: () => {
        emit('submitted', data);
      },
      onError: (errors) => {
        emit('error', 'Form submission failed.');
        console.error('Form submission errors:', errors);
        errors.value = errors;
      }
    });
  }
};
</script>

<template>
  <div class="mt-6 form-content relative font-[Times_New_Roman]">
    <div v-for="(student, index) in currentPageStudents" :key="startIndex + index" class="student-certification-page">
      <!-- Header -->
      <div class="header text-center relative mb-2">
        <!-- Back Button positioned above LSPU logo -->
        <div style="position: absolute; top: -0.8cm; left: -2cm; z-index: 10;">
          <a href="/applications/select-form"
             class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
             style="font-family: system-ui, -apple-system, sans-serif;">
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
          </a>
        </div>
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
        <div class="font-[Calibri] mb-0" style="font-size:10pt;">Republic of the Philippines</div>
        <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="university-name mb-0" style="max-width:45%;height:auto;margin:4px 0;display:inline-block;">
        <div class="font-[Calibri] mb-0" style="font-size:10pt;">Province of Laguna</div>
        <div class="text-xl font-bold mb-0" style="font-size:11pt; margin-top:11px; margin-bottom:0; position:relative; top:-5px;">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
      </div>

      <!-- Certification Title -->
  <div class="cert-title text-center font-bold my-8" style="margin-top:0px; font-size:14pt;">CERTIFICATION</div>

      <!-- Date line -->
      <div class="date-line" style="text-align:right; margin-bottom:20px;">
        <div style="display:inline-block; text-align:right;">
          <span style="display:inline-block; min-width:120px; border-bottom:1px solid black; padding-bottom:2px; text-align:center; font-weight:bold;">{{ new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
          <br>
          <span style="display:inline-block; text-align:center; width:100%; margin-top:2px; font-weight:bold;">Date</span>
        </div>
      </div>

      <!-- Certification Content -->
        <div class="cert-content" style="text-align:justify; margin:20px 0; line-height:1.5; padding-left:0.5cm; padding-right:0;">
          <span style="display:inline-block; text-indent:6em;">
            This certifies that
          </span>
          <span style="display:inline-block; vertical-align:bottom; position:relative; top:10px;">
    <span style="min-width:435px; border-bottom:1px solid black; text-align:center; display:inline-block; position:relative; top:10px; font-weight:bold;">{{ student.student_name }}</span>
          <span style="display:block; text-align:center; font-size:10pt; margin-top:6px;">(LAST NAME, FIRST NAME, MIDDLE INITIAL)</span>
          </span>, a
          <br>
          <div style="padding-left: 0px; text-align: left; margin-top: -5px;">
        student taking up 
        <span style="display: inline-block; vertical-align: bottom; position: relative; top: 11px;">
          <span class="course-blank" style="min-width: 420px; border-bottom: 1px solid black; text-align: center; display: inline-block; font-weight:bold;">{{ student.course_year_section }}</span>
          <div style="text-align: center; font-size: 10pt; margin-top: -5px;">
            <span>(course, year and section)</span>
          </div>
        </span> from the College of <span class="signature-line" style="min-width:445px; border-bottom: 1px solid black; display: inline-block; margin-top: 20px;text-align:center; font-weight:bold;">{{ form.college }}</span> is a bonafide LSPU Student, not
      </div>
          <div style="margin-top: 0.2em;"></div>
          <span style="text-indent:6em;word-spacing: 17px">under academic probation, not under disciplinary probation, and the elected/appointed</span>
          <div style="height:0.2em;"></div>
          <div style="display: flex; align-items: flex-start; margin-top: 2px;">
            <div style="flex: 1; min-width: 230px;">
              <span style="display: block; min-width: 230px; border-bottom: 1px solid black; text-align: center; font-weight:bold;">{{ student.position_rank }}</span>
              <span style="display: block; text-align: center; font-size: 10pt; margin-top: 0.5px; width: 120px;margin-left:50px">(position/rank)</span>
            </div>
            <span style="margin: 0 10px;">of</span>
            <div style="flex: 1; min-width: 315px; text-align: right;">
              <span style="display: block; min-width: 315px; border-bottom: 1px solid black; text-align: center; font-weight:bold;">{{ form.organization_name }}</span>
              <span style="display: block; text-align: right; font-size: 10pt; margin-top: 0.5px; width: 150px;margin-left:30px">(organization)</span>
            </div>
            <span style="margin-left: 10px;">.</span>
          </div>
        </div>

      <!-- Signature Section -->
      <div class="signature-section" style="margin-top:100px;">
  <div class="college-is-text" style="padding-left:10px; margin-bottom:40px;">Certified true and correct:</div>
        <div class="noted-section" style="padding-left:40px;">
          <div class="faculty-adviser-signature" style="margin-top:0; margin-left:-42px;">
            <div style="text-align:left;">
              <span style="display:inline-block; min-width:200px; width:auto; border-bottom:1px solid black; padding-bottom:2px; text-align:center; margin-left:0px; text-transform: uppercase; font-weight:bold;">{{ displayAdviserName }}</span>
              <span style="display:block; text-align:left; margin-left:25px;">Organization Adviser(s)</span>
            </div>
          </div>
          <div style="margin-top:40px; text-align:left; margin-left:-42px;">
            <span style="display:inline-block; min-width:220px; width:auto; border-bottom:1px solid black; padding-bottom:2px; text-align:center; text-transform: uppercase; font-weight:bold;">{{ displayDeanName }}</span>
            <span style="display:block; text-align:left; margin-left:25px;">Dean/Assoc. Dean of College</span>
          </div>
          <div style="text-align:center; margin-top:40px; margin-left:0;">Noted:</div>
        </div>
        <div style="margin-top:40px; text-align:center;">
          <span style="min-width:415px; border-bottom:1px solid black; display:inline-block; margin-bottom:0px; text-transform: uppercase; font-weight:bold;">{{ form.director_name }}</span>
          <span style="display:block; text-align:center; margin-top:2px;">Director/Chairperson, Office of Student Affairs and Services</span>
        </div>
      </div>

      <!-- Footer -->
  <div class="footer mt-8 text-xs flex justify-between font-[Calibri]">
        <span>LSPU-OSAS-SF-006</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
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
      Page {{ currentPage }} of {{ totalPages }} • Student {{ startIndex + 1 }} of {{ form.students.length }}
    </div>

    <!-- Form inputs -->
    <div class="mt-8 border-t pt-6">
        <h3 class="text-lg font-bold mb-4">Form Details</h3>
        
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block font-bold">College</label>
        <select 
          :value="form.college ? collegeOptions.find(option => option.replace('College of ', '').toUpperCase() === form.college) || 'None' : 'None'"
          @change="handleCollegeChange"
          class="border p-2 w-full text-black"
        >
          <option value="" disabled>Select College</option>
          <option v-for="option in collegeOptions" :key="option" :value="option">{{ option }}</option>
        </select>
        <p v-if="errors.college" class="text-red-500 text-sm mt-1">{{ errors.college }}</p>
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

            <!-- President Name field removed for Student Certification Form -->

            <div>
                <label class="block font-bold">Organization Adviser(s)</label>
                <div class="flex gap-1 items-center">
                  <input 
                    v-model="form.adviser_prefix" 
                    class="border p-2 w-12 text-xs" 
                    placeholder="Pre"
                    maxlength="6">
                  <input 
                    v-model="form.adviser_name" 
                    @input="form.adviser_name = $event.target.value.toUpperCase()"
                    class="border p-2 flex-1" 
                    style="text-transform: uppercase;" 
                    required>
                  <input 
                    v-model="form.adviser_suffix" 
                    class="border p-2 w-14 text-xs" 
                    placeholder="Suf"
                    maxlength="8">
                </div>
                <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Dean/Assoc. Dean Name</label>
                <div class="flex gap-1 items-center">
                  <input 
                    v-model="form.dean_prefix" 
                    class="border p-2 w-12 text-xs" 
                    placeholder="Pre"
                    maxlength="6">
                  <input 
                    v-model="form.dean_name" 
                    @input="form.dean_name = $event.target.value.toUpperCase()"
                    class="border p-2 flex-1" 
                    style="text-transform: uppercase;">
                  <input 
                    v-model="form.dean_suffix" 
                    class="border p-2 w-14 text-xs" 
                    placeholder="Suf"
                    maxlength="8">
                </div>
                <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Director/Chairperson, OSAS</label>
                <input v-model="form.director_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
            </div>
        </div>

        <!-- Student List Management -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Students</h3>
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
                </div>
            </div>

            <!-- CSV Format Instructions -->
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm">
                <p class="font-semibold text-blue-800 mb-1">📋 CSV Format Requirements:</p>
                <ul class="text-blue-700 list-disc list-inside space-y-1">
                    <li>First row should contain column headers (will be ignored)</li>
                    <li>Columns must be in this order: <strong>Student Name, Course, Year & Section, Position/Rank</strong></li>
                    <li>Additional columns will be ignored</li>
                    <li>File must be in CSV format (.csv extension)</li>
                </ul>
            </div>

            <!-- Student Count Display -->
            <div class="mb-4 p-2 bg-gray-50 border border-gray-200 rounded text-sm">
                <span class="font-semibold">👥 Total Students: {{ form.students.length }}</span>
                <span v-if="form.students.length > 0" class="ml-4 text-gray-600">
                    • Page {{ currentPage }} of {{ totalPages }}
                </span>
            </div>

            <div v-for="(student, idx) in currentPageStudentInputs" :key="startIndex + idx" class="mt-4 p-4 border rounded">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-bold">Student #{{ startIndex + idx + 1 }}</h4>
                    <button 
                        @click="removeStudent(startIndex + idx)" 
                        type="button" 
                        :disabled="form.students.length <= 1"
                        :class="[
                            'px-2 py-1 rounded text-sm font-medium transition-colors',
                            form.students.length <= 1 
                                ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
                                : 'text-red-500 hover:text-red-700 hover:bg-red-50'
                        ]"
                    >
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Certification Date input removed -->

          <div>
            <label class="block font-bold">Student Name (Last name, First Name, M.I.)</label>
            <input 
              v-model="student.student_name" 
              @input="student.student_name = $event.target.value.toUpperCase()"
              class="border p-2 w-full" 
              style="text-transform: uppercase;" 
              required>
            <p v-if="errors[`student_${startIndex + idx}_name`]" class="text-red-500 text-sm mt-1">{{ errors[`student_${startIndex + idx}_name`] }}</p>
          </div>

                    <div>
                        <label class="block font-bold">Course</label>
                        <input 
                          v-model="student.course" 
                          @input="student.course = $event.target.value.toUpperCase(); updateCourseYearSection(startIndex + idx)"
                          class="border p-2 w-full" 
                          style="text-transform: uppercase;" 
                          required>
                        <p v-if="errors[`student_${startIndex + idx}_course`]" class="text-red-500 text-sm mt-1">{{ errors[`student_${startIndex + idx}_course`] }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Year and Section</label>
                        <input 
                          v-model="student.year_section" 
                          @input="student.year_section = $event.target.value.toUpperCase(); updateCourseYearSection(startIndex + idx)"
                          class="border p-2 w-full" 
                          style="text-transform: uppercase;" 
                          required>
                        <p v-if="errors[`student_${startIndex + idx}_year_section`]" class="text-red-500 text-sm mt-1">{{ errors[`student_${startIndex + idx}_year_section`] }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Position/rank</label>
                        <input 
                          v-model="student.position_rank" 
                          @input="student.position_rank = $event.target.value.toUpperCase()"
                          class="border p-2 w-full" 
                          style="text-transform: uppercase;">
                    </div>
                    
                    <!-- Student Status fields removed -->
                </div>
            </div>

            <!-- Pagination Controls for Student Inputs -->
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
        </div>

        <div class="mt-6 flex justify-between items-center">
            <button @click="addStudent" type="button" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                ➕ Add Student
            </button>
        </div>

        <div class="mt-6 text-center">
            <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">
                {{ props.isEdit ? 'Update' : 'Submit' }}
            </button>
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
</div>
</template>