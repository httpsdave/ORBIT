<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
// REMOVE: import StatusBanner from '@/Components/StatusBanner.vue';

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

// Compute current year and next year for placeholders
const currentYear = computed(() => {
  return new Date().getFullYear().toString().slice(-2);
});

const nextYear = computed(() => {
  return (new Date().getFullYear() + 1).toString().slice(-2);
});

// Add errors ref object
const errors = ref({});

// Add pagination state
const currentPage = ref(1);
const membersPerPage = 8; // 4 rows × 2 columns per page, matching PDF

// Add a function to add a new empty member
const addMember = () => {
    form.members.push({
        student_name: '',
        student_number: '',
        course_year_section: '',
        photo_path: null,
        photo_preview: null
    });
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

            // Clear existing members
            form.members = [];
            
            // Process each row
            dataRows.forEach((row, index) => {
                const columns = row.split(',').map(col => col.trim().replace(/"/g, ''));
                
                // Extract first 3 columns only
                const studentName = columns[0] || '';
                const studentNumber = columns[1] || '';
                const courseYearSection = columns[2] || '';
                
                // Add member if at least one field has data
                if (studentName || studentNumber || courseYearSection) {
                    form.members.push({
                        student_name: studentName,
                        student_number: studentNumber,
                        course_year_section: courseYearSection,
                        photo_path: null,
                        photo_preview: null
                    });
                }
            });
            
            // Reset to first page after upload
            currentPage.value = 1;
            
            alert(`Successfully imported ${form.members.length} members from CSV file.`);
            
        } catch (error) {
            console.error('Error parsing CSV:', error);
            alert('Error reading CSV file. Please check the file format.');
        }
    };
    
    reader.readAsText(file);
    
    // Reset the file input
    event.target.value = '';
};

// Add a function to remove a member
const removeMember = (index) => {
    // Prevent removing the last member
    if (form.members.length <= 1) {
        return;
    }
    // Clean up object URL if it exists
    if (form.members[index].photo_preview) {
        URL.revokeObjectURL(form.members[index].photo_preview);
    }
    form.members.splice(index, 1);
};

// Add a function to remove a member's photo
const removeMemberPhoto = (index) => {
    // Clean up object URL if it exists
    if (form.members[index].photo_preview) {
        URL.revokeObjectURL(form.members[index].photo_preview);
    }
    // Clear photo data
    form.members[index].photo_path = null;
    form.members[index].photo_preview = null;
};

// Current date computed property
const currentDate = computed(() => {
    const today = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return today.toLocaleDateString('en-US', options);
});

// Pagination computed properties
const totalPages = computed(() => Math.ceil(form.members.length / membersPerPage));
const startIndex = computed(() => (currentPage.value - 1) * membersPerPage);
const endIndex = computed(() => Math.min(startIndex.value + membersPerPage, form.members.length));
const currentPageMembers = computed(() => {
    return form.members.slice(startIndex.value, endIndex.value);
});

// Add computed for current page's member input forms
const currentPageMemberInputs = computed(() => {
    return form.members.slice(startIndex.value, endIndex.value);
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
  form_type: 'LSPU-OSAS-SF-005',
 
  organization_name: props.initialFormData.organization_name || '',
  academic_year_start: props.initialFormData.academic_year_start || '',
  academic_year_end: props.initialFormData.academic_year_end || '',
  semester: props.initialFormData.semester || '',
  members: [],
  
  // president_name removed for List of Members Form
  secretary_name: props.initialFormData.secretary_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  second_adviser: props.initialFormData.second_adviser || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
});

const handlePhotoUpload = (event, index, type = 'members') => {
    const file = event.target.files[0];
    if (file) {
        if (type === 'members') {
            // Clean up previous object URL if it exists
            if (form.members[index].photo_preview) {
                URL.revokeObjectURL(form.members[index].photo_preview);
            }
            // Create a temporary URL for preview in the form
            form.members[index].photo_preview = URL.createObjectURL(file);
            // Store the actual file for upload
            form.members[index].photo_path = file;
        }
    }
};

// Helper function to get photo preview URL
const getPhotoPreview = (member) => {
    if (member.photo_preview) {
        return member.photo_preview;
    }
    if (member.photo_path && typeof member.photo_path === 'object') {
        return URL.createObjectURL(member.photo_path);
    }
    // If photo_path is a string (already saved), return the storage URL
    if (member.photo_path && typeof member.photo_path === 'string') {
        return `/storage/${member.photo_path}`;
    }
    return null;
};

// Initialize with data from props if available
if (props.initialFormData?.members && props.initialFormData.members.length > 0) {
  // Copy members from initialFormData
  form.members = [...props.initialFormData.members.map(member => ({
    ...member,
    photo_preview: null
  }))];
} else {
  // Add default empty members
  for(let i = 0; i < 4; i++) {
    addMember();
  }
}

// Validation function
const validateForm = () => {
  errors.value = {};
  
  // Validate main form fields
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization name is required';
  }
  
  // president_name validation removed for List of Members Form
  
  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator name is required';
  }
  
  if (!form.semester.trim()) {
    errors.value.semester = 'Semester is required';
  }
  
  if (!form.academic_year_start.trim()) {
    errors.value.academic_year_start = 'Academic year start is required';
  }
  
  if (!form.academic_year_end.trim()) {
    errors.value.academic_year_end = 'Academic year end is required';
  }
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Faculty adviser name is required';
  }
  
  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean/Assoc. Dean name is required';
  }
  
  if (!form.director_name.trim()) {
    errors.value.director_name = 'Director/Chairperson name is required';
  }
  
  // Validate members
  form.members.forEach((member, index) => {
    if (!member.student_name.trim()) {
      errors.value[`member_${index}_name`] = 'Student name is required';
    }
    
    if (!member.student_number.trim()) {
      errors.value[`member_${index}_number`] = 'Student number is required';
    }
    
    if (!member.course_year_section.trim()) {
      errors.value[`member_${index}_course`] = 'Course - Year & Section is required';
    }
  });
  
  // Return true if no errors
  return Object.keys(errors.value).length === 0;
};

// REMOVE: const statusMessage = ref('');
// REMOVE: const statusType = ref('success');
// REMOVE: const showStatus = ref(false);

// REMOVE: const showBanner = (msg, type = 'success') => {
// REMOVE:   statusMessage.value = msg;
// REMOVE:   statusType.value = type;
// REMOVE:   showStatus.value = true;
// REMOVE:   setTimeout(() => { showStatus.value = false; }, 5000);
// REMOVE: };

const submit = () => {
  if (!validateForm()) {
    emit('error', 'Please fill in all required fields.');
    return;
  }
  if (props.isEdit) {
    emit('submitted', form.data());
  } else {
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

function limitTo2Digits(event) {
  event.target.value = event.target.value.replace(/[^0-9]/g, '').slice(0, 2);
}
</script>

<template>
  <div class="mt-6 form-content">
    <!-- REMOVE: <StatusBanner :show="showStatus" :type="statusType" :message="statusMessage" @close="showStatus = false" /> -->
    <div class="header text-center relative py-4">
      <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-20px] left-[-60px] w-[180px] h-auto">
      <div class="font-normal text-[11pt] leading-tight" style="font-family:Calibri,sans-serif;">
        Republic of the Philippines<br>
        <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="inline-block align-middle h-[22px] max-w-[55%] my-1 university-name" /><br>
        <span class="block mb-2">Province of Laguna</span>
      </div>
      <div class="font-bold text-[11pt] mt-1 mb-1" style="font-family:'Times New Roman',serif;">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
      <div class="font-bold text-[11pt] mt-1 mb-1" style="font-family:'Times New Roman',serif;">LIST OF MEMBERS OF THE ORGANIZATION</div>
    </div>

    <div class="text-center mt-2">
      <div class="inline-block text-[11pt] font-bold mb-1" style="font-family:'Times New Roman',serif;">
        <span class="inline-block border-b border-black px-2 min-w-[48px]">{{ form.semester || '1st' }}</span>
        Semester AY 20<span class="inline-block border-b border-black px-2 min-w-[36px]">{{ form.academic_year_start || currentYear }}</span>-20<span class="inline-block border-b border-black px-2 min-w-[36px]">{{ form.academic_year_end || nextYear }}</span>
      </div>
      <div class="w-full text-center mt-1">
        <span class="font-bold text-[11pt]" style="font-family:'Times New Roman',serif;">Name of Organization</span>
        <span class="block border-b border-black min-w-[200px] mx-auto font-bold text-[11pt]" style="font-family:'Times New Roman',serif;">{{ form.organization_name }}</span>
      </div>
    </div>

    <!-- Member list preview with pagination -->
    <div class="member-section mt-6">
        <!-- Page header for additional pages -->
        <div v-if="currentPage > 1" class="page-break-header text-center mt-8 pt-8 border-t-2">
            <p class="text-sm font-normal mb-0">Republic of the Philippines</p>
            <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
            <p class="text-sm mb-0">Province of Laguna</p>
            <p class="text-sm font-bold mb-0 mt-3">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
            <p class="text-sm font-bold mt-2 mb-0">List of Members</p>
            <div class="semester-section text-center mt-4">
                <p class="mb-0">
                    <span class="border p-1 mr-1">{{ form.semester || '--' }}</span> 
                    Sem. / AY 
                    <span class="border p-1 w-16 mx-1 inline-block">{{ form.academic_year_start || '20__' }}</span>-
                    <span class="border p-1 w-16 mx-1 inline-block">{{ form.academic_year_end || '20__' }}</span>
                </p>
            </div>
            <div class="section text-center mt-4">
                <p class="mb-0">Name of Organization: <span class="signature-line border-b border-black min-w-[250px] inline-block text-center">{{ form.organization_name }}</span></p>
            </div>
        </div>

        <!-- Members for current page (8 per page: 4 rows × 2 columns) -->
        <div class="flex mt-4">
          <!-- Left Column -->
          <div class="w-1/2 pr-4">
            <div v-for="rowIndex in 4" :key="`left-${rowIndex}`" class="flex mb-8">
              <div v-if="startIndex + (rowIndex - 1) < form.members.length" class="w-full flex">
                <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center mr-3 text-xs flex-shrink-0">
                  <img v-if="getPhotoPreview(form.members[startIndex + (rowIndex - 1)])"
                       :src="getPhotoPreview(form.members[startIndex + (rowIndex - 1)])"
                       alt="Member Photo"
                       class="w-[94px] h-[94px] object-cover">
                  <span v-else class="photo-box-text text-center leading-tight">1 x 1<br>PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-center text-center">
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto">
                    <span class="filled-text">{{ form.members[startIndex + (rowIndex - 1)].student_name || '(Signature Over Printed Name)' }}</span>
                  </div>
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto">
                    <span class="filled-text">{{ form.members[startIndex + (rowIndex - 1)].student_number || '(Student Number)' }}</span>
                  </div>
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto">
                    <span class="filled-text">{{ form.members[startIndex + (rowIndex - 1)].course_year_section || '(Course / Year Section)' }}</span>
                  </div>
                </div>
              </div>
              <div v-else class="w-full flex">
                <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center mr-3 text-xs flex-shrink-0">
                  <span class="photo-box-text text-center leading-tight">1 x 1<br>PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-center text-center">
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto"><span class="filled-text">(Signature Over Printed Name)</span></div>
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto"><span class="filled-text">(Student Number)</span></div>
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto"><span class="filled-text">(Course / Year Section)</span></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="w-1/2 pl-4">
            <div v-for="rowIndex in 4" :key="`right-${rowIndex}`" class="flex mb-8">
              <div v-if="startIndex + (rowIndex - 1) + 4 < form.members.length" class="w-full flex">
                <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center mr-3 text-xs flex-shrink-0">
                  <img v-if="getPhotoPreview(form.members[startIndex + (rowIndex - 1) + 4])"
                       :src="getPhotoPreview(form.members[startIndex + (rowIndex - 1) + 4])"
                       alt="Member Photo"
                       class="w-[94px] h-[94px] object-cover">
                  <span v-else class="photo-box-text text-center leading-tight">1 x 1<br>PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-center text-center">
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto">
                    <span class="filled-text">{{ form.members[startIndex + (rowIndex - 1) + 4].student_name || '(Signature Over Printed Name)' }}</span>
                  </div>
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto">
                    <span class="filled-text">{{ form.members[startIndex + (rowIndex - 1) + 4].student_number || '(Student Number)' }}</span>
                  </div>
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto">
                    <span class="filled-text">{{ form.members[startIndex + (rowIndex - 1) + 4].course_year_section || '(Course / Year Section)' }}</span>
                  </div>
                </div>
              </div>
              <div v-else class="w-full flex">
                <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center mr-3 text-xs flex-shrink-0">
                  <span class="photo-box-text text-center leading-tight">1 x 1<br>PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-center text-center">
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto"><span class="filled-text">(Signature Over Printed Name)</span></div>
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto"><span class="filled-text">(Student Number)</span></div>
                  <div class="member-line mb-1 min-h-[20px] py-1 border-b border-black w-4/5 mx-auto"><span class="filled-text">(Course / Year Section)</span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Signatures and approvals: shown on ALL pages, vertical stacking, no overlap -->
        <div v-if="form.members.length > 0" class="mt-4">
          <!-- Advisers and Dates -->
          <table class="w-full" style="border-collapse:collapse;">
            <tr>
              <td class="align-top text-center w-1/2 p-0" style="vertical-align:top; text-align:center;">
                <div style="width:200px; margin:0 auto; margin-left:35px;">
                  <span class="border-b border-black min-w-[200px] inline-block text-center">{{ form.adviser_name }}</span>
                  <div class="text-center">Organization Adviser</div>
                </div>
                <div class="text-left mt-1" style="padding-left:35px;">Date: <span class="border-b border-black min-w-[140px] inline-block text-center">{{ currentDate }}</span></div>
                <!-- Noted / Dean section under left date -->
                <div class="mt-4">
                  <div class="font-bold text-left ml-8">Noted:</div>
                  <span class="border-b border-black min-w-[180px] inline-block text-center">{{ form.dean_name }}</span>
                  <div class="text-center">Dean/Assoc. Dean of College</div>
                </div>
              </td>
              <td class="align-top text-center w-1/2 p-0" style="vertical-align:top; text-align:center;">
                <div style="width:200px; margin:0 auto; margin-left:110px;">
                  <span class="border-b border-black min-w-[200px] inline-block text-center">{{ form.second_adviser }}</span>
                  <div class="text-center">Organization Adviser</div>
                </div>
                <div class="text-left mt-1" style="padding-left:110px;">Date: <span class="border-b border-black min-w-[140px] inline-block text-center">{{ currentDate }}</span></div>
              </td>
            </tr>
          </table>

          <!-- Recommending Approval -->
          <div class="text-center mt-4">
            <div class="font-bold mb-1"><strong>Recommending Approval:</strong></div>
            <div class="border-b border-black min-w-[290px] inline-block text-center">{{ form.coordinator_name }}</div>
            <div class="mt-1 mb-1">Coordinator, Student Organization Unit</div>
          </div>

          <!-- Approved/Disapproved -->
          <div class="text-center mt-4">
            <div class="font-bold mb-1"><strong>Approved/Disapproved:</strong></div>
            <div class="border-b border-black min-w-[415px] inline-block text-center">{{ form.director_name }}</div>
            <div class="mt-1">Director/Chairperson, Office of Student Affairs and Services</div>
          </div>
        </div>

        <!-- Fixed footer to match PDF -->
        <div class="footer fixed bottom-0 left-0 w-full flex justify-between px-8 py-1 text-[11pt] font-normal" style="font-family:Calibri,sans-serif; background:transparent;">
          <div class="footer-left">LSPU-OSAS-SF-005</div>
          <div class="footer-center">Rev. 1</div>
          <div class="footer-right">09 November 2020</div>
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
            Page {{ currentPage }} of {{ totalPages }} • Members {{ startIndex + 1 }}-{{ endIndex }} of {{ form.members.length }}
        </div>
    </div>



    <!-- Form inputs -->
    <div class="mt-8 border-t pt-6">
        <h3 class="text-lg font-bold mb-4">Form Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold">Organization Name</label>
                <input v-model="form.organization_name" class="border p-2 w-full" required>
                <div v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</div>
            </div>

            <!-- President Name field removed for List of Members Form -->

            <div>
                <label class="block font-bold">Coordinator Name</label>
                <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-100 text-gray-600" readonly>
            </div>

            <div>
                <label class="block font-bold">Semester</label>
                <select v-model="form.semester" class="border p-2 w-full" required>
                    <option value="">-- Select Semester --</option>
                    <option value="1st">1st Semester</option>
                    <option value="2nd">2nd Semester</option>
                    <option value="Summer">Summer</option>
                </select>
                <div v-if="errors.semester" class="text-red-500 text-sm mt-1">{{ errors.semester }}</div>
            </div>

            <div>
                <label class="block font-bold">Academic Year Start</label>
                <input v-model="form.academic_year_start" class="border p-2 w-full" :placeholder="currentYear" required inputmode="numeric" pattern="[0-9]{2}" maxlength="2" @input="limitTo2Digits" >
                <div v-if="errors.academic_year_start" class="text-red-500 text-sm mt-1">{{ errors.academic_year_start }}</div>
            </div>

            <div>
                <label class="block font-bold">Academic Year End</label>
                <input v-model="form.academic_year_end" class="border p-2 w-full" :placeholder="nextYear" required inputmode="numeric" pattern="[0-9]{2}" maxlength="2" @input="limitTo2Digits" >
                <div v-if="errors.academic_year_end" class="text-red-500 text-sm mt-1">{{ errors.academic_year_end }}</div>
            </div>

            <div>
                <label class="block font-bold">Faculty Adviser Name</label>
                <input v-model="form.adviser_name" class="border p-2 w-full" required>
                <div v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</div>
            </div>

            <div>
                <label class="block font-bold">Second Faculty Adviser Name (Optional)</label>
                <input v-model="form.second_adviser" class="border p-2 w-full">
            </div>

            <div>
                <label class="block font-bold">Dean/Assoc. Dean Name</label>
                <input v-model="form.dean_name" class="border p-2 w-full" required>
                <div v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</div>
            </div>

            <div>
                <label class="block font-bold">Director/Chairperson Name</label>
                <input v-model="form.director_name" class="border p-2 w-full bg-gray-100 text-gray-600" readonly>
            </div>
        </div>

        <!-- Member List Management -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Members</h3>
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
                    <li>Columns must be in this order: <strong>Student Name, Student Number, Course - Year & Section</strong></li>
                    <li>Additional columns will be ignored</li>
                    <li>File must be in CSV format (.csv extension)</li>
                </ul>
            </div>

            <!-- Member Count Display -->
            <div class="mb-4 p-2 bg-gray-50 border border-gray-200 rounded text-sm">
                <span class="font-semibold">👥 Total Members: {{ form.members.length }}</span>
                <span v-if="form.members.length > 0" class="ml-4 text-gray-600">
                    • Page {{ currentPage }} of {{ totalPages }}
                </span>
            </div>

            <div v-for="(member, idx) in currentPageMemberInputs" :key="startIndex + idx" class="mt-4 p-4 border rounded">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-bold">Member #{{ startIndex + idx + 1 }}</h4>
                    <button 
                        @click="removeMember(startIndex + idx)" 
                        type="button" 
                        :disabled="form.members.length <= 1"
                        :class="[
                            'px-2 py-1 rounded text-sm font-medium transition-colors',
                            form.members.length <= 1 
                                ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
                                : 'text-red-500 hover:text-red-700 hover:bg-red-50'
                        ]"
                    >
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold">Student Name</label>
                        <input v-model="member.student_name" class="border p-2 w-full" required>
                        <div v-if="errors[`member_${startIndex + idx}_name`]" class="text-red-500 text-sm mt-1">{{ errors[`member_${startIndex + idx}_name`] }}</div>
                    </div>

                    <div>
                        <label class="block font-bold">Student Number</label>
                        <input v-model="member.student_number" class="border p-2 w-full" required>
                        <div v-if="errors[`member_${startIndex + idx}_number`]" class="text-red-500 text-sm mt-1">{{ errors[`member_${startIndex + idx}_number`] }}</div>
                    </div>

                    <div>
                        <label class="block font-bold">Course - Year & Section</label>
                        <input v-model="member.course_year_section" class="border p-2 w-full" required>
                        <div v-if="errors[`member_${startIndex + idx}_course`]" class="text-red-500 text-sm mt-1">{{ errors[`member_${startIndex + idx}_course`] }}</div>
                    </div>

                    <div>
                        <label class="block font-bold">1x1 Photo</label>
                        <input type="file" @change="event => handlePhotoUpload(event, startIndex + idx)" class="border p-2 w-full" accept="image/*">
                        <div v-if="getPhotoPreview(member)" class="mt-2">
                            <div class="flex items-center gap-2">
                                <img :src="getPhotoPreview(member)" alt="Preview" class="w-16 h-16 object-cover border">
                                <button 
                                    @click="removeMemberPhoto(startIndex + idx)" 
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

            <!-- Pagination Controls for Member Inputs -->
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
            <button @click="addMember" type="button" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                ➕ Add Member
            </button>
        </div>

        <div class="mt-6 text-center">
            <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
        </div>
    </div>

    <div class="footer mt-8 text-xs flex justify-between">
        <span>LSPU-OSAS-SF-005</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
    </div>
</div>

</template>