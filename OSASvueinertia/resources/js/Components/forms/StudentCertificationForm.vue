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

const emit = defineEmits(['submitted']);

// Add errors ref object
const errors = ref({});

// Add pagination state
const currentPage = ref(1);
const studentsPerPage = 1; // 1 student per page since each certification is a full page

// Add a function to add a new empty student
const addStudent = () => {
    form.students.push({
        student_name: '',
        course_year_section: '',
        position_rank: '',
        is_bonafide: false,
        is_not_academic_probation: false,
        is_not_disciplinary_probation: false,
        has_position: false,
        certification_date: '',
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

            // Clear existing students
            form.students = [];
            
            // Process each row
            dataRows.forEach((row, index) => {
                const columns = row.split(',').map(col => col.trim().replace(/"/g, ''));
                
                // Extract columns: Student Name, Course/Year Section, Position/Rank
                const studentName = columns[0] || '';
                const courseYearSection = columns[1] || '';
                const positionRank = columns[2] || '';
                
                // Add student if at least one field has data
                if (studentName || courseYearSection) {
                    form.students.push({
                        student_name: studentName,
                        course_year_section: courseYearSection,
                        position_rank: positionRank,
                        is_bonafide: false,
                        is_not_academic_probation: false,
                        is_not_disciplinary_probation: false,
                        has_position: false,
                        certification_date: '',
                    });
                }
            });
            
            // Reset to first page after upload
            currentPage.value = 1;
            
            alert(`Successfully imported ${form.students.length} students from CSV file.`);
            
        } catch (error) {
            console.error('Error parsing CSV:', error);
            alert('Error reading CSV file. Please check the file format.');
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

const form = useForm({
  form_type: 'LSPU-OSAS-SF-006',
  organization_name: props.initialFormData.organization_name || '',
  president_name: props.initialFormData.president_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  director_name: props.initialFormData.director_name || '',
  students: props.initialFormData.students || [],
});

// Initialize with data from props if available
if (!form.students || form.students.length === 0) {
  addStudent();
} else {
  // Convert boolean values properly for checkboxes
  form.students = form.students.map(student => ({
    ...student,
    is_bonafide: student.is_bonafide === true || student.is_bonafide === 1 || student.is_bonafide === '1',
    is_not_academic_probation: student.is_not_academic_probation === true || student.is_not_academic_probation === 1 || student.is_not_academic_probation === '1',
    is_not_disciplinary_probation: student.is_not_disciplinary_probation === true || student.is_not_disciplinary_probation === 1 || student.is_not_disciplinary_probation === '1',
    has_position: student.has_position === true || student.has_position === 1 || student.has_position === '1',
  }));
}

const validateForm = () => {
  errors.value = {};
  
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
  }
  
  if (!form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
  }
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Faculty Adviser is required';
  }
  
  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean/Assoc. Dean Name is required';
  }
  
  // Validate students
  form.students.forEach((student, index) => {
    if (!student.certification_date) {
      errors.value[`student_${index}_certification_date`] = 'Certification Date is required';
    }
    
    if (!student.student_name.trim()) {
      errors.value[`student_${index}_name`] = 'Student Name is required';
    }
    
    if (!student.course_year_section.trim()) {
      errors.value[`student_${index}_course`] = 'Course/Year and Section is required';
    }
  });
  
  return Object.keys(errors.value).length === 0;
};

const submit = () => {
  if (!validateForm()) {
    return;
  }

  // Cast boolean fields to 1/0 for backend compatibility
  const data = {
    ...form.data(),
    students: form.students.map(student => ({
      ...student,
      is_bonafide: student.is_bonafide ? 1 : 0,
      is_not_academic_probation: student.is_not_academic_probation ? 1 : 0,
      is_not_disciplinary_probation: student.is_not_disciplinary_probation ? 1 : 0,
      has_position: student.has_position ? 1 : 0,
    }))
  };

  if (props.isEdit) {
    emit('submitted', data);
  } else {
    form.post('/applications', {
      data,
      onSuccess: () => {
        alert('Form submitted successfully!');
        emit('submitted', data);
      },
      onError: (errors) => {
        console.error('Form submission errors:', errors);
        errors.value = errors;
      }
    });
  }
};
</script>

<template>
  <div class="mt-6 form-content relative font-[Times_New_Roman]">
    <!-- Student certification preview with pagination -->
    <div v-for="(student, index) in currentPageStudents" :key="startIndex + index" class="student-certification-page">
      <!-- Page header for additional pages -->
      <div v-if="currentPage > 1" class="page-break-header text-center mt-8 pt-8 border-t-2">
        <p class="text-sm font-normal mb-0">Republic of the Philippines</p>
        <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
        <p class="text-sm mb-0">Province of Laguna</p>
        <p class="text-sm font-bold mb-0 mt-3">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
      </div>

      <!-- Header -->
      <div class="header text-center relative mb-2">
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
        <div class="font-[Calibri] text-base mb-0">Republic of the Philippines</div>
        <div class="text-xl font-bold university-name mb-0">Laguna State Polytechnic University</div>
        <div class="font-[Calibri] text-base mb-0">Province of Laguna</div>
        <div class="text-xl font-bold mt-4 mb-0">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
      </div>

      <!-- Date line -->
      <div class="date-line text-right mt-6 mb-2 pr-2">
        <div>
          <span class="inline-block border-b border-black min-w-[200px] text-center">{{ student.certification_date }}</span>
        </div>
        <div class="text-right pr-2">DATE</div>
      </div>

      <!-- Certification Title -->
      <div class="cert-title text-center text-2xl font-bold my-8">CERTIFICATION</div>

      <!-- Certification Content: Two lines, long underlines, labels below, left-aligned -->
      <div class="px-10 mb-2">
        <div class="flex items-center flex-wrap">
          <span>This&nbsp;certifies&nbsp;that</span>
          <span class="relative inline-block align-middle mx-2">
            <span class="border-b border-black min-w-[300px] px-2 text-center inline-block">{{ student.student_name }}</span>
            <span class="absolute left-1/2 -translate-x-1/2 top-full text-xs font-bold mt-[-2px] whitespace-nowrap">student name</span>
          </span>
          <span>, a</span>
        </div>
        <div class="flex items-center flex-wrap mt-6">
          <span class="relative inline-block align-middle mx-2">
            <span class="border-b border-black min-w-[300px] px-2 text-center inline-block">{{ student.course_year_section }}</span>
            <span class="absolute left-1/2 -translate-x-1/2 top-full text-xs font-bold mt-[-2px] whitespace-nowrap">course/year and section</span>
          </span>
          <span>.</span>
        </div>
      </div>

      <!-- 'student of this College is:' line -->
      <div class="college-is-text px-10 my-8 leading-[1.5]">student of this College is:</div>

      <!-- Checkboxes as text-based -->
      <div class="checkbox-container mt-10 px-10">
        <div class="checkbox-item my-4">
          <span class="inline-block w-6 text-center">({{ student.is_bonafide ? '/' : ' ' }})</span> a bonafide student;
        </div>
        <div class="checkbox-item my-4">
          <span class="inline-block w-6 text-center">({{ student.is_not_academic_probation ? '/' : ' ' }})</span> not under academic probation;
        </div>
        <div class="checkbox-item my-4">
          <span class="inline-block w-6 text-center">({{ student.is_not_disciplinary_probation ? '/' : ' ' }})</span> not under disciplinary probation;
        </div>
        <div class="checkbox-item my-4">
          <span class="inline-block w-6 text-center">({{ student.has_position ? '/' : ' ' }})</span> position/rank in the organization
          <span class="border-b border-black min-w-[150px] inline-block text-center mx-2">{{ student.position_rank }}</span>;
        </div>
      </div>

      <!-- Noted and Signatures -->
      <div class="signature-section relative mt-24">
        <div class="noted-section pl-20 mb-10">
          <p class="font-bold mb-8">Noted:</p>
          <div class="faculty-adviser-signature mt-[-15px]">
            <div class="border-b border-black w-[200px] text-center ml-[-40px]">{{ form.adviser_name }}</div>
            <div class="text-left text-base mt-1 ml-2">Faculty Adviser(s)</div>
          </div>
        </div>
        <div class="dean-signature-section text-center mt-24 mb-8">
          <div class="border-b border-black w-[200px] mx-auto">{{ form.dean_name }}</div>
          <div class="text-center text-base mt-1">Dean/Assoc. Dean of College</div>
        </div>
      </div>

      <!-- Footer -->
      <div class="footer mt-8 text-xs flex justify-between">
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
                <label class="block font-bold">Organization Name</label>
                <input v-model="form.organization_name" class="border p-2 w-full" required>
                <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
            </div>

            <div>
                <label class="block font-bold">President Name</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
                <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Faculty Adviser(s)</label>
                <input v-model="form.adviser_name" class="border p-2 w-full" required>
                <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Dean/Assoc. Dean Name</label>
                <input v-model="form.dean_name" class="border p-2 w-full" required>
                <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
            </div>
        </div>

        <!-- Student List Management -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Students</h3>
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
                    <li>Columns must be in this order: <strong>Student Name, Course/Year & Section, Position/Rank</strong></li>
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
                    <div>
                        <label class="block font-bold">Certification Date</label>
                        <input type="date" v-model="student.certification_date" class="border p-2 w-full" required>
                        <p v-if="errors[`student_${startIndex + idx}_certification_date`]" class="text-red-500 text-sm mt-1">{{ errors[`student_${startIndex + idx}_certification_date`] }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Student Name</label>
                        <input v-model="student.student_name" class="border p-2 w-full" required>
                        <p v-if="errors[`student_${startIndex + idx}_name`]" class="text-red-500 text-sm mt-1">{{ errors[`student_${startIndex + idx}_name`] }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Course/Year and Section</label>
                        <input v-model="student.course_year_section" class="border p-2 w-full" required>
                        <p v-if="errors[`student_${startIndex + idx}_course`]" class="text-red-500 text-sm mt-1">{{ errors[`student_${startIndex + idx}_course`] }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Position/Rank in Organization</label>
                        <input v-model="student.position_rank" class="border p-2 w-full">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block font-bold">Student Status</label>
                        <div class="flex flex-col gap-2 mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="student.is_bonafide" class="mr-2">
                                <span>Bonafide Student</span>
                            </label>
                            
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="student.is_not_academic_probation" class="mr-2">
                                <span>Not Under Academic Probation</span>
                            </label>
                            
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="student.is_not_disciplinary_probation" class="mr-2">
                                <span>Not Under Disciplinary Probation</span>
                            </label>
                            
                            <label class="inline-flex items-center">
                                <input type="checkbox" v-model="student.has_position" class="mr-2">
                                <span>Has Position/Rank in Organization</span>
                            </label>
                        </div>
                    </div>
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
</div>
</template>