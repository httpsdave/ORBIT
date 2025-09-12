<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
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

const displaySecondAdviserName = computed(() => {
  let name = form.second_adviser || '';
  if (form.second_adviser_prefix) {
    name = form.second_adviser_prefix + ' ' + name;
  }
  if (form.second_adviser_suffix) {
    name = name + ', ' + form.second_adviser_suffix;
  }
  return name;
});

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
            
            csvModalTitle.value = 'Import Successful';
            csvModalMessage.value = `Successfully imported ${form.members.length} members from CSV file.`;
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
 
  organization_name: props.initialFormData.organization_name?.toUpperCase() || '',
  academic_year_start: props.initialFormData.academic_year_start || currentYear.value,
  academic_year_end: props.initialFormData.academic_year_end || nextYear.value,
  semester: props.initialFormData.semester || '',
  members: [],
  
  // president_name removed for List of Members Form
  secretary_name: props.initialFormData.secretary_name?.toUpperCase() || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name?.toUpperCase() || '',
  adviser_prefix: props.initialFormData.adviser_prefix || '',
  adviser_suffix: props.initialFormData.adviser_suffix || '',
  second_adviser: props.initialFormData.second_adviser?.toUpperCase() || '',
  second_adviser_prefix: props.initialFormData.second_adviser_prefix || '',
  second_adviser_suffix: props.initialFormData.second_adviser_suffix || '',
  dean_name: props.initialFormData.dean_name?.toUpperCase() || '',
  dean_prefix: props.initialFormData.dean_prefix || '',
  dean_suffix: props.initialFormData.dean_suffix || '',
  coordinator_name: props.initialFormData.coordinator_name?.toUpperCase() || '',
  director_name: props.initialFormData.director_name?.toUpperCase() || '',
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
    student_name: member.student_name?.toUpperCase() || '',
    course_year_section: member.course_year_section?.toUpperCase() || '',
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
  
  // Dean name is now optional - no validation required
  
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
</script>

<template>
  <div class="mt-6 form-content">
    <!-- REMOVE: <StatusBanner :show="showStatus" :type="statusType" :message="statusMessage" @close="showStatus = false" /> -->

    <!-- Uniform header for all pages -->
    <div class="header text-center relative">
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
      <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute logo" style="position: absolute; margin-top: -40px ; left: -2cm; width: 250px; height: auto;">
      <div class="font-normal text-[10pt] leading-tight header-text" style="font-family:Calibri,sans-serif;">
        Republic of the Philippines<br>
        <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="inline-block align-middle max-w-[45%] my-1 university-name" style="max-width: 45%; height: auto; margin: 3px 0; display: inline-block;" /><br>
        <span class="block mb-3 province-text" style="margin-bottom: 12px; display: block;">Province of Laguna</span>
      </div>
      <div class="font-bold text-[11pt] office-title" style="font-family:'Times New Roman',serif; font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:30px; display: block;">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
      <div class="font-bold text-[11pt] sub-header" style="font-family:'Times New Roman',serif; font-size:11pt; font-weight:bold; margin-bottom:10px; margin-top:6px; display: block;">LIST OF MEMBERS OF THE ORGANIZATION</div>
    </div>

    <div class="text-center" style="margin-top: 2px;">
      <div class="inline-block text-[11pt] font-bold mb-4" style="font-family:'Times New Roman',serif; font-size: 11pt; font-weight: bold; position: relative; top: 5px;">
        <span class="inline-block border-b border-black text-center" style="display:inline-block !important; min-width:0.5cm !important; margin:0 !important; margin-right:0.05cm !important; padding:0 !important; border-bottom:1px solid black !important; line-height:10px; font-family: Times New Roman, serif; font-size: 11pt; font-weight: bold; text-align:center; position:relative; top:0px;">{{ form.semester || '1st' }}</span>Semester AY 20<span class="inline-block border-b border-black text-center" style="display:inline-block !important; min-width:0.4cm !important; margin:0 !important; margin-left:0.02cm !important; margin-right:0.02cm !important; padding:0 !important; border-bottom:1px solid black !important; line-height:10px; font-family: Times New Roman, serif; font-size: 11pt; font-weight: bold; text-align:center; position:relative; top:0px; left:-0.03cm;">{{ form.academic_year_start || currentYear }}</span>-20<span class="inline-block border-b border-black text-center" style="display:inline-block !important; min-width:0.4cm !important; margin:0 !important; margin-left:0.02cm !important; padding:0 !important; border-bottom:1px solid black !important; line-height:10px; font-family: Times New Roman, serif; font-size: 11pt; font-weight: bold; text-align:center; position:relative; top:0px; left:-0.03cm;">{{ form.academic_year_end || nextYear }}</span>
      </div>
      <div class="w-full text-center" style="margin-top: 7px;">
        <div style="display: flex; flex-direction: row; align-items: center; justify-content: center; width: 100%;">
          <span class="font-bold text-[11pt] mr-2" style="font-family:'Times New Roman',serif; font-size: 11pt; font-weight: bold; text-align: left;">Name of Organization</span>
          <span class="signature-line" style="margin-bottom:0px; width:360px; font-family:'Times New Roman',serif; font-size: 11pt; text-align: center; border-bottom: 1px solid #000; display: inline-block; font-weight: bold;">{{ form.organization_name }}</span>
        </div>
        <!-- SAMPLE FORMAT section -->
        <div style="width:340px; margin:0 auto -5px auto;">
          <div style="text-align:center; font-weight:normal; margin-bottom:4px; margin-top:10px; margin-left:100px;">SAMPLE FORMAT:</div>
          <div style="display:flex; justify-content:center; gap:4px;">
            <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center text-xs flex-shrink-0">
              <span class="photo-box-text text-center leading-tight">1 x 1<br>PICTURE</span>
            </div>
            <div class="member-info flex flex-col justify-center text-center" style="width:220px;">
              <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width:260px;">
                <span class="filled-text">(Signature Over Printed Name)</span>
              </div>
              <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width:260px;">
                <span class="filled-text">(Student Number)</span>
              </div>
              <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width:260px;">
                <span class="filled-text">(Course / Year Section)</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  <!-- Member list preview with pagination -->
  <div class="member-section mt-6">

        <!-- Members for current page (8 per page: 4 rows × 2 columns) -->
        <div class="flex mt-4">
          <!-- Left Column -->
          <div class="w-1/2 pr-4">
            <div v-for="rowIndex in 4" :key="`left-${rowIndex}`" class="flex mb-8" style="gap:8px;">
              <div v-if="startIndex + (rowIndex - 1) < form.members.length" class="w-full flex" style="gap:8px;">
                <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center text-xs flex-shrink-0">
                  <img v-if="allowImageUploads && getPhotoPreview(form.members[startIndex + (rowIndex - 1)])"
                       :src="getPhotoPreview(form.members[startIndex + (rowIndex - 1)])"
                       alt="Member Photo"
                       class="w-[94px] h-[94px] object-cover">
                  <span v-else class="photo-box-text text-center leading-tight">1 x 1<br>PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-center text-center" style="max-width: calc(100% - 106px);">
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ form.members[startIndex + (rowIndex - 1)].student_name }}</span>
                  </div>
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ form.members[startIndex + (rowIndex - 1)].student_number }}</span>
                  </div>
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ form.members[startIndex + (rowIndex - 1)].course_year_section }}</span>
                  </div>
                </div>
              </div>
              <div v-else class="w-full flex" style="gap:8px;">
                <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center text-xs flex-shrink-0">
                  <span class="photo-box-text text-center leading-tight">1 x 1<br>PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-center text-center" style="max-width: calc(100% - 106px);">
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"></span></div>
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"></span></div>
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"></span></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="w-1/2 pl-4">
            <div v-for="rowIndex in 4" :key="`right-${rowIndex}`" class="flex mb-8" style="gap:8px;">
              <div v-if="startIndex + (rowIndex - 1) + 4 < form.members.length" class="w-full flex" style="gap:8px;">
                <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center text-xs flex-shrink-0">
                  <img v-if="allowImageUploads && getPhotoPreview(form.members[startIndex + (rowIndex - 1) + 4])"
                       :src="getPhotoPreview(form.members[startIndex + (rowIndex - 1) + 4])"
                       alt="Member Photo"
                       class="w-[94px] h-[94px] object-cover">
                  <span v-else class="photo-box-text text-center leading-tight">1 x 1<br>PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-center text-center" style="max-width: calc(100% - 106px);">
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ form.members[startIndex + (rowIndex - 1) + 4].student_name }}</span>
                  </div>
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ form.members[startIndex + (rowIndex - 1) + 4].student_number }}</span>
                  </div>
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ form.members[startIndex + (rowIndex - 1) + 4].course_year_section }}</span>
                  </div>
                </div>
              </div>
              <div v-else class="w-full flex" style="gap:8px;">
                <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center text-xs flex-shrink-0">
                  <span class="photo-box-text text-center leading-tight">1 x 1<br>PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-center text-center" style="max-width: calc(100% - 106px);">
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"></span></div>
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"></span></div>
                  <div class="member-line mb-0 min-h-[18px] py-0 border-b border-black mx-auto" style="width: calc(80% + 40px); max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><span class="filled-text" style="display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"></span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Signatures and approvals: shown on ALL pages, vertical stacking, no overlap -->
        <div v-if="form.members.length > 0" class="mt-4">
          <!-- Advisers and Dates -->
          <table class="w-full" style="border-collapse:collapse;">
            <tbody>
              <tr>
                <td class="align-top text-center w-1/2 p-0" style="vertical-align:top; text-align:center;">
                  <div style="width:200px; margin:0 auto; margin-left:35px;">
                    <span class="border-b border-black min-w-[150px] inline-block text-center"><strong>{{ displayAdviserName }}</strong></span>
                    <div class="text-center">Organization Adviser</div>
                  </div>
                  <div class="text-left mt-1" style="padding-left:35px;">Date: <span class="border-b border-black min-w-[140px] inline-block text-center"><strong>{{ currentDate }}</strong></span></div>
                  <!-- Noted / Dean section under left date -->
                  <div class="mt-4">
                    <div class="font-bold text-left ml-8">Noted:</div>
                    <span class="border-b border-black min-w-[180px] inline-block text-center"><strong>{{ displayDeanName }}</strong></span>
                    <div class="text-center">Dean/Assoc. Dean of College</div>
                  </div>
                </td>
                <td class="align-top text-center w-1/2 p-0" style="vertical-align:top; text-align:center;">
                  <div style="width:200px; margin:0 auto; margin-left:110px;">
                    <span class="border-b border-black min-w-[150px] inline-block text-center"><strong>{{ displaySecondAdviserName }}</strong></span>
                    <div class="text-center">Organization Adviser</div>
                  </div>
                  <div class="text-left mt-1" style="padding-left:110px;">Date: <span class="border-b border-black min-w-[140px] inline-block text-center"><strong>{{ currentDate }}</strong></span></div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Recommending Approval -->
          <div class="text-center mt-4">
            <div class="font-bold mb-1"><strong>Recommending Approval:</strong></div>
            <div class="border-b border-black min-w-[230px] inline-block text-center"><strong>{{ form.coordinator_name }}</strong></div>
            <div class="mt-1 mb-1">Coordinator, Student Organization Unit</div>
          </div>

      <!-- Approved/Disapproved -->
      <div class="text-center mt-4">
      <div class="font-bold mb-1"><strong>Approved/Disapproved:</strong></div>
      <div class="border-b border-black min-w-[340px] inline-block text-center"><strong>{{ form.director_name }}</strong></div>
      <div class="mt-1">Director/Chairperson, Office of Student Affairs and Services</div>
      </div>
      <!-- Footer moved here -->
      <div class="footer mt-8 text-xs flex justify-between" style="font-family:Calibri,sans-serif;">
      <span>LSPU-OSAS-SF-005</span>
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
      Page {{ currentPage }} of {{ totalPages }} • Members {{ startIndex + 1 }}-{{ endIndex }} of {{ form.members.length }}
    </div>
  </div>



  <!-- Form inputs -->
  <div class="mt-8 border-t pt-6">
    <h3 class="text-lg font-bold mb-4">Form Details</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- 1st Column -->
      <div>
        <label class="block font-bold">Organization Name</label>
        <input 
          v-model="form.organization_name" 
          @input="form.organization_name = $event.target.value.toUpperCase()"
          class="border p-2 w-full" 
          style="text-transform: uppercase;" 
          required>
        <div v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</div>

        <label class="block font-bold mt-4">Semester</label>
          <select v-model="form.semester" class="border p-2 w-full" required>
            <option value="1st">1st Semester</option>
            <option value="2nd">2nd Semester</option>
            <option value="Inter">Inter Semester</option>
          </select>
        <div v-if="errors.semester" class="text-red-500 text-sm mt-1">{{ errors.semester }}</div>


  <label class="block font-bold mt-4 mb-2">Organization Adviser</label>
  <div class="flex gap-1 mb-1">
    <input 
      v-model="form.adviser_prefix" 
      class="border p-2 w-12 text-xs" 
      placeholder="Pre"
      maxlength="10">
    <input 
      v-model="form.adviser_name" 
      @input="form.adviser_name = $event.target.value.toUpperCase()"
      class="border p-2 flex-1" 
      style="text-transform: uppercase;">
    <input 
      v-model="form.adviser_suffix" 
      class="border p-2 w-14 text-xs" 
      placeholder="Suf"
      maxlength="15">
  </div>
  
  <div v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</div>

  <label class="block font-bold mt-4">Secondary Organization Adviser (Optional)</label>
  <div class="flex gap-1 mb-1">
    <input 
      v-model="form.second_adviser_prefix" 
      class="border p-2 w-12 text-xs" 
      placeholder="Pre"
      maxlength="10">
    <input 
      v-model="form.second_adviser" 
      @input="form.second_adviser = $event.target.value.toUpperCase()"
      class="border p-2 flex-1" 
      style="text-transform: uppercase;">
    <input 
      v-model="form.second_adviser_suffix" 
      class="border p-2 w-14 text-xs" 
      placeholder="Suf"
      maxlength="15">
  </div>

        <label class="block font-bold mt-4 mb-2">Dean/Assoc. Dean Name</label>
        <div class="flex gap-1 mb-1">
          <input 
            v-model="form.dean_prefix" 
            class="border p-2 w-12 text-xs" 
            placeholder="Pre"
            maxlength="10">
          <input 
            v-model="form.dean_name" 
            @input="form.dean_name = $event.target.value.toUpperCase()"
            class="border p-2 flex-1" 
            style="text-transform: uppercase;">
          <input 
            v-model="form.dean_suffix" 
            class="border p-2 w-14 text-xs" 
            placeholder="Suf"
            maxlength="15">
        </div>
     
        <div v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</div>
      </div>

      <!-- 2nd Column -->
      <div>
        <label class="block font-bold">Academic Year</label>
        <div class="flex items-center space-x-2">
          <input 
            v-model="form.academic_year_start" 
            class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
            :placeholder="currentYear" 
            readonly 
            tabindex="-1" 
            style="user-select: none; -webkit-user-select: none;" 
          >
          <span class="mx-1">-</span>
          <input 
            v-model="form.academic_year_end" 
            class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
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

        <label class="block font-bold mt-4">Coordinator Name</label>
        <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-100 text-gray-600" readonly style="text-transform: uppercase;">

        <label class="block font-bold mt-4">Director/Chairperson Name</label>
        <input v-model="form.director_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
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
                        <input 
                          v-model="member.student_name" 
                          @input="member.student_name = $event.target.value.toUpperCase()"
                          class="border p-2 w-full" 
                          style="text-transform: uppercase;" 
                          required>
                        <div v-if="errors[`member_${startIndex + idx}_name`]" class="text-red-500 text-sm mt-1">{{ errors[`member_${startIndex + idx}_name`] }}</div>
                    </div>

                    <div>
                        <label class="block font-bold">Student Number</label>
                        <input v-model="member.student_number" class="border p-2 w-full" required>
                        <div v-if="errors[`member_${startIndex + idx}_number`]" class="text-red-500 text-sm mt-1">{{ errors[`member_${startIndex + idx}_number`] }}</div>
                    </div>

                    <div>
                        <label class="block font-bold">Course - Year & Section</label>
                        <input 
                          v-model="member.course_year_section" 
                          @input="member.course_year_section = $event.target.value.toUpperCase()"
                          class="border p-2 w-full" 
                          style="text-transform: uppercase;" 
                          required>
                        <div v-if="errors[`member_${startIndex + idx}_course`]" class="text-red-500 text-sm mt-1">{{ errors[`member_${startIndex + idx}_course`] }}</div>
                    </div>

                    <div v-if="allowImageUploads">
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