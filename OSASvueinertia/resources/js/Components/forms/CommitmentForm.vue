<script setup>
const rankOptions = [
  'None',
  'Instructor I',
  'Instructor II',
  'Instructor III',
  'Assistant Professor I',
  'Assistant Professor II',
  'Assistant Professor III',
  'Assistant Professor IV',
  'Associate Professor I',
  'Associate Professor II',
  'Associate Professor III',
  'Associate Professor IV',
  'Associate Professor V',
  'Part Time Instructor'
];
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
function handleCollegeChange(e, adviserIndex) {
  const selected = e.target.value;
  if (selected === 'None') {
    form.advisers[adviserIndex].adviser_college = '';
  } else {
    form.advisers[adviserIndex].adviser_college = selected;
  }
}

// Handler for rank select change
function handleRankChange(e, adviserIndex) {
  const selected = e.target.value;
  if (selected === 'None') {
    form.advisers[adviserIndex].adviser_rank = '';
  } else {
    form.advisers[adviserIndex].adviser_rank = selected;
  }
}

import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

// Add pagination state - maximum 2 pages allowed
const currentPage = ref(1);
const maxPages = 2;

// Add function to add a new adviser (max 2)
const addAdviser = () => {
  if (form.advisers.length < maxPages) {
    form.advisers.push({
      adviser_name: '',
      adviser_prefix: '',
      adviser_suffix: '',
      adviser_college: '',
      adviser_rank: '',
      adviser_address: '',
      adviser_contact: '',
    });
  }
};

// Add function to remove an adviser
const removeAdviser = (index) => {
  if (form.advisers.length > 1) {
    form.advisers.splice(index, 1);
    // Adjust current page if necessary
    if (currentPage.value > form.advisers.length) {
      currentPage.value = form.advisers.length;
    }
  }
};

// Add navigation functions for pagination (max 2 pages)
const goToPage = (page) => {
  if (page >= 1 && page <= Math.min(maxPages, form.advisers.length)) {
    currentPage.value = page;
  }
};

const nextPage = () => {
  if (currentPage.value < Math.min(maxPages, form.advisers.length)) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

// Computed property for current adviser
const currentAdviser = computed(() => {
  return form.advisers[currentPage.value - 1] || {};
});

// Computed property for displaying combined names with prefix/suffix for current adviser
const displayCurrentAdviserName = computed(() => {
  const adviser = currentAdviser.value;
  let name = adviser.adviser_name || '';
  if (adviser.adviser_prefix) {
    name = adviser.adviser_prefix + ' ' + name;
  }
  if (adviser.adviser_suffix) {
    name = name + ', ' + adviser.adviser_suffix;
  }
  return name;
});

// Computed property for total pages
const totalPages = computed(() => {
  return Math.min(maxPages, form.advisers.length);
});

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

// Computed property to format the date
const formattedDate = computed(() => {
  if (!form.form_date) return '';
  const date = new Date(form.form_date);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

// Computed properties for displaying combined names with prefix/suffix
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

const today = (() => {
  const d = new Date();
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
})();

const form = useForm({
  form_type: 'LSPU-OSAS-SF-003',
  organization_name: props.initialFormData.organization_name?.toUpperCase() || '',
  // president_name removed for Commitment Form
  application_date: props.initialFormData.application_date || '',
  advisers: (props.initialFormData.advisers || []).map(adviser => ({
    adviser_name: adviser.adviser_name?.toUpperCase() || '',
    adviser_prefix: adviser.adviser_prefix || '',
    adviser_suffix: adviser.adviser_suffix || '',
    adviser_college: adviser.adviser_college || '',
    adviser_rank: adviser.adviser_rank || '',
    adviser_address: adviser.adviser_address || '',
    adviser_contact: adviser.adviser_contact || '',
  })),
  form_date: today,
  dean_name: props.initialFormData.dean_name?.toUpperCase() || '',
  dean_prefix: props.initialFormData.dean_prefix || '',
  dean_suffix: props.initialFormData.dean_suffix || '',
  coordinator_name: props.initialFormData.coordinator_name?.toUpperCase() || '',
  director_name: props.initialFormData.director_name?.toUpperCase() || '',
  academic_year_start: currentYear.value,
  academic_year_end: nextYear.value,
});

// Initialize with at least one adviser if none exist
if (!form.advisers || form.advisers.length === 0) {
  form.advisers = [{
    adviser_name: props.initialFormData.adviser_name?.toUpperCase() || '',
    adviser_prefix: props.initialFormData.adviser_prefix || '',
    adviser_suffix: props.initialFormData.adviser_suffix || '',
    adviser_college: props.initialFormData.adviser_college || '',
    adviser_rank: props.initialFormData.adviser_rank || '',
    adviser_address: props.initialFormData.adviser_address || '',
    adviser_contact: props.initialFormData.adviser_contact || '',
  }];
}

// Add errors ref object
const errors = ref({});

// Add validation function
const validateForm = () => {
  errors.value = {};
  
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization name is required';
  }
  
  // academic_year_start and academic_year_end are always set, so no need to validate
  
  // Validate all advisers
  form.advisers.forEach((adviser, index) => {
    if (!adviser.adviser_name.trim()) {
      errors.value[`adviser_${index}_name`] = 'Adviser name is required';
    }
    
    if (!adviser.adviser_address.trim()) {
      errors.value[`adviser_${index}_address`] = 'Home address is required';
    }
    
    if (!adviser.adviser_contact.trim()) {
      errors.value[`adviser_${index}_contact`] = 'Contact number is required';
    }
  });
  
  if (!form.form_date.trim()) {
    errors.value.form_date = 'Form date is required';
  }
  
  // Dean name is now optional
  
  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator name is required';
  }
  
  if (!form.director_name.trim()) {
    errors.value.director_name = 'Director name is required';
  }
  
  // president_name validation removed for Commitment Form
  
  return Object.keys(errors.value).length === 0;
};

const submit = () => {
  if (!validateForm()) {
    emit('error', 'Please fill in all required fields.');
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
    <!-- Pagination Controls -->
    <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mb-8 gap-4">
      <button 
        @click="prevPage" 
        :disabled="currentPage === 1"
        class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
        Previous
      </button>
      
      <div class="flex gap-2">
        <button 
          v-for="page in totalPages" 
          :key="page"
          @click="goToPage(page)"
          :class="[
            'px-3 py-1 rounded',
            currentPage === page 
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
    <div v-if="totalPages > 1" class="text-center mb-4 text-sm text-gray-600">
      Page {{ currentPage }} of {{ totalPages }} • Adviser {{ currentPage }} of {{ form.advisers.length }}
    </div>

    <!-- Commitment Form Page Content -->
    <div v-for="(adviser, page) in form.advisers" :key="page" v-show="currentPage === page + 1" class="commitment-page">
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
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
    <p class="header-republic-text mb-0" style="padding-top: 20px; font-family: 'Calibri', sans-serif; font-weight: normal; font-size: 11pt;">Republic of the Philippines</p>
    <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="university-name mb-0" style="max-width: 45%; height: auto; margin: 4px 0; display: inline-block;" />
    <p class="header-province-text mb-0" style="font-family: 'Calibri', sans-serif; font-weight: normal; font-size: 11pt;">Province of Laguna</p>
      <div style="height:10px;"></div>
      <p class="office-title" style="margin-bottom:10px; font-size:12pt; font-family: 'Times New Roman', serif; text-align:center; font-weight:bold;">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
        <p class="commitment-form-title mb-4">ORGANIZATION ADVISER COMMITMENT FORM</p>
      </div>

      <div class="section right-align" style="text-align: right; margin-bottom: 0;margin-top: 25px;">
        <p style="margin: 0;">
          <span class="signature-line date-underline" style="min-width: 150px; display: inline-block; border-bottom: 1px solid black; text-align: center; padding-bottom: 2px;"><strong>{{ formattedDate }}</strong></span>
        </p>
    <p style="margin-top: 0; text-align: left; width: max-content; padding-left: 550px;">Date</p>
      </div>
      <div style="height: -3px;"></div>

      <div class="section text-left"style="font-size: 11pt;margin-top: 20px;">
    <p class="mb-0 font-bold">THE DIRECTOR/CHAIRPERSON</p>
    <p class="mb-0 font-bold">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
    <p class="mb-0 font-bold">LSPU</p>
      </div>

      <div class="section">
        <p class="thru-line font-bold mb-2" style="padding-left: 1.27cm; text-indent: 0;font-size: 11pt;">Thru: The Coordinator, Student Organization Unit</p>
      </div>

      <div class="section commitment-body" style="text-align: justify; font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.1;">
        <p class="sir-greeting" style="margin-top: 10px; margin-bottom: 20px;">Sir/Madam:</p>
        <p class="indented justified" style="text-indent: 1.45cm;">
          This letter is in connection with the application for recognition/renewal of
          <span
            :style="{
              display: 'inline-block',
              minWidth: '300px',
              borderBottom: '1px solid #000',
              verticalAlign: 'middle',
              fontWeight: 'bold',
              whiteSpace: form.organization_name.length > 69 ? 'normal' : 'nowrap',
              overflow: form.organization_name.length > 69 ? 'visible' : 'hidden',
              textOverflow: form.organization_name.length > 69 ? 'clip' : 'ellipsis',
              wordBreak: form.organization_name.length > 69 ? 'break-word' : 'normal',
              lineHeight: '1.1',
              paddingBottom: '2px'
            }"
          >{{ form.organization_name }}</span>
          as a duly recognized LSPU Organization.
        </p>
        <p class="indented" style="text-indent: 1.45cm;">
    I, the undersigned, have committed to serve as the organization's Adviser for the academic year 20<u>{{ form.academic_year_start }}</u>-20<u>{{ form.academic_year_end }}</u>, and shall therefore assume full responsibility as provided in the guidelines for the recognition of student organizations.
        </p>
        <p class="indented" style="text-indent: 1.45cm;">
          Furthermore, I certify to the correctness and completeness of the documents attached to the organization application for recognition.
        </p>
      </div>

      <!-- Signature block: Very respectfully yours -->
      <div class="very-respectfully mt-12 mb-8">
        <p class="mb-2" style="font-weight: bold;">Very respectfully yours,</p>
        <!-- Name signature line: 32 chars, single line, ellipsis -->
        <div class="sig-row">
          <span class="sig-label">Name:</span>
          <span class="sig-line sig-name" style="width:230px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            {{ displayCurrentAdviserName }}
          </span>
        </div>
        <div class="sig-row"><span class="sig-label">Signature:</span><span class="sig-line sig-signature">&nbsp;</span></div>
        <!-- College signature lines: split into up to 3 lines -->
        <div class="sig-row">
          <span class="sig-label">College:</span>
          <span class="sig-line sig-college">
            {{ adviser.adviser_college.length > 25 ? adviser.adviser_college.slice(0, adviser.adviser_college.slice(0,25).lastIndexOf(' ') > 0 ? adviser.adviser_college.slice(0,25).lastIndexOf(' ') : 25) : adviser.adviser_college }}
          </span>
        </div>
        <div v-if="adviser.adviser_college.length > 25" class="sig-row" style="margin-left: -5px;">
          <span class="sig-label"></span>
          <span class="sig-line sig-college">
            {{ (() => {
              const college = adviser.adviser_college;
              const break1 = college.length > 25 ? (college.slice(0,25).lastIndexOf(' ') > 0 ? college.slice(0,25).lastIndexOf(' ') : 25) : college.length;
              const remaining = college.slice(break1).trim();
              if (remaining.length > 42) {
                const break2 = remaining.slice(0,42).lastIndexOf(' ') > 0 ? remaining.slice(0,42).lastIndexOf(' ') : 42;
                return remaining.slice(0,break2);
              } else {
                return remaining;
              }
            })() }}
          </span>
        </div>
        <div v-if="(() => {
          const college = adviser.adviser_college;
          const break1 = college.length > 25 ? (college.slice(0,25).lastIndexOf(' ') > 0 ? college.slice(0,25).lastIndexOf(' ') : 25) : college.length;
          const remaining = college.slice(break1).trim();
          if (remaining.length > 42) {
            const break2 = remaining.slice(0,42).lastIndexOf(' ') > 0 ? remaining.slice(0,42).lastIndexOf(' ') : 42;
            return remaining.slice(break2).trim().length > 0;
          }
          return false;
        })()" class="sig-row" style="margin-left: -5px;">
          <span class="sig-label"></span>
          <span class="sig-line sig-college">
            {{ (() => {
              const college = adviser.adviser_college;
              const break1 = college.length > 25 ? (college.slice(0,25).lastIndexOf(' ') > 0 ? college.slice(0,25).lastIndexOf(' ') : 25) : college.length;
              const remaining = college.slice(break1).trim();
              if (remaining.length > 42) {
                const break2 = remaining.slice(0,42).lastIndexOf(' ') > 0 ? remaining.slice(0,42).lastIndexOf(' ') : 42;
                return remaining.slice(break2).trim();
              }
              return '';
            })() }}
          </span>
        </div>
        <div class="sig-row"><span class="sig-label">Academic Rank:</span><span class="sig-line sig-rank">{{ adviser.adviser_rank }}</span></div>
        <!-- Home Address signature lines: split into up to 3 lines -->
        <div class="sig-row">
          <span class="sig-label">Home Address:</span>
          <span class="sig-line sig-address">
            {{ adviser.adviser_address.length > 25 ? adviser.adviser_address.slice(0, adviser.adviser_address.slice(0,25).lastIndexOf(' ') > 0 ? adviser.adviser_address.slice(0,25).lastIndexOf(' ') : 25) : adviser.adviser_address }}
          </span>
        </div>
        <div v-if="adviser.adviser_address.length > 25" class="sig-row" style="margin-left: -5px;">
          <span class="sig-label"></span>
          <span class="sig-line sig-address">
            {{ (() => {
              const addr = adviser.adviser_address;
              const break1 = addr.length > 25 ? (addr.slice(0,25).lastIndexOf(' ') > 0 ? addr.slice(0,25).lastIndexOf(' ') : 25) : addr.length;
              const remaining = addr.slice(break1).trim();
              if (remaining.length > 42) {
                const break2 = remaining.slice(0,42).lastIndexOf(' ') > 0 ? remaining.slice(0,42).lastIndexOf(' ') : 42;
                return remaining.slice(0,break2);
              } else {
                return remaining;
              }
            })() }}
          </span>
        </div>
        <div v-if="(() => {
          const addr = adviser.adviser_address;
          const break1 = addr.length > 25 ? (addr.slice(0,25).lastIndexOf(' ') > 0 ? addr.slice(0,25).lastIndexOf(' ') : 25) : addr.length;
          const remaining = addr.slice(break1).trim();
          if (remaining.length > 42) {
            const break2 = remaining.slice(0,42).lastIndexOf(' ') > 0 ? remaining.slice(0,42).lastIndexOf(' ') : 42;
            return remaining.slice(break2).trim().length > 0;
          }
          return false;
        })()" class="sig-row" style="margin-left: -5px;">
          <span class="sig-label"></span>
          <span class="sig-line sig-address">
            {{ (() => {
              const addr = adviser.adviser_address;
              const break1 = addr.length > 25 ? (addr.slice(0,25).lastIndexOf(' ') > 0 ? addr.slice(0,25).lastIndexOf(' ') : 25) : addr.length;
              const remaining = addr.slice(break1).trim();
              if (remaining.length > 42) {
                const break2 = remaining.slice(0,42).lastIndexOf(' ') > 0 ? remaining.slice(0,42).lastIndexOf(' ') : 42;
                return remaining.slice(break2).trim();
              }
              return '';
            })() }}
          </span>
        </div>
        <div class="sig-row"><span class="sig-label">Contact Number(s):</span><span class="sig-line sig-contact">{{ adviser.adviser_contact }}</span></div>
        <div class="sig-row"><span class="sig-label">Date:</span><span class="sig-line sig-date">{{ new Date(form.form_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span></div>
      </div>

      <!-- Noted, Recommending Approval, and Approval Section -->
      <div class="mt-2 mb-8">
        <div class="mb-8" style="text-align:left;">
    <div class="noted-label mb-1" style="font-size: 1rem; font-weight: bold; margin-bottom: 10px;">Noted:</div>
          <div class="noted-signature-block" style="width: 350px; margin-left: 65px;">
            <span class="signature-line" style="min-width: 180px; border-bottom: 1px solid #000; display: inline-block; margin-left: 0; font-size: 12pt; font-family: 'Times New Roman', serif; font-weight: bold; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ displayDeanName || '' }}</span>
            <p class="mb-0 text-xs text-center" style="font-size: 11pt; font-family: 'Times New Roman', serif; font-weight: bold; margin-top: 2px; font-weight: bold;margin-left:-175px">Dean/Assoc. Dean of College</p>
          </div>
        </div>
        <div class="approval-center-block text-center" style="margin-top: 30px;">
          <div class="mb-8">
            <p class="mb-1" style="font-size: 12pt; font-family: 'Times New Roman', serif; font-weight: bold;">Recommending Approval:</p>
            <div class="mt-2">
              <span class="signature-line" style="min-width: 270px; border-bottom: 1px solid #000; font-size: 12pt; font-family: 'Times New Roman', serif; font-weight: bold; display: block; margin: 0 auto 2px auto;">{{ form.coordinator_name }}</span>
              <p class="mb-0 text-xs" style="font-size: 11pt; font-family: 'Times New Roman', serif; font-weight: bold; margin-top: 2px;">Coordinator, Student Organization Unit</p>
            </div>
          </div>
          <div>
            <p class="mb-1" style="font-size: 12pt; font-family: 'Times New Roman', serif; font-weight: bold;">Approved / Disapproved:</p>
            <div class="mt-2">
              <span class="signature-line" style="min-width: 340px; border-bottom: 1px solid #000; font-size: 12pt; font-family: 'Times New Roman', serif; font-weight: bold; display: block; margin: 0 auto 2px auto;">{{ form.director_name }}</span>
              <p class="mb-0 text-xs" style="font-size: 11pt; font-family: 'Times New Roman', serif; font-weight: bold; margin-top: 2px;">Director, Office of Student Affairs and Services</p>
            </div>
          </div>
        </div>
      </div>

      <div class="footer mt-8 text-xs flex justify-between font-[Calibri]">
        <span>LSPU-OSAS-SF-003</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
      </div>
    </div>

    <!-- Form inputs -->
    <div class="mt-8 border-t pt-6">
      <h3 class="text-lg font-bold mb-4">Form Details</h3>
      
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Left Column -->
        <div>
          <label class="block font-bold">Organization Name (full)</label>
          <input 
            v-model="form.organization_name" 
            @input="form.organization_name = $event.target.value.toUpperCase()"
            class="border p-2 w-full" 
            style="text-transform: uppercase;" 
            required>
          <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
        </div>
        <!-- Right Column -->
        <div>
          <label class="block font-bold">Academic Year</label>
          <div class="flex items-center space-x-2">
            <input 
              v-model="form.academic_year_start" 
              class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
              required 
              :placeholder="currentYear" 
              readonly 
              tabindex="-1" 
              style="user-select: none; -webkit-user-select: none;" 
            >
            <span class="mx-1">-</span>
            <input 
              v-model="form.academic_year_end" 
              class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
              required 
              :placeholder="nextYear" 
              readonly 
              tabindex="-1" 
              style="user-select: none; -webkit-user-select: none;" 
            >
          </div>
        </div>
        
        <!-- Left Column -->
        <div>
          <label class="block font-bold">Application Date</label>
          <input 
            type="date" 
            :value="form.form_date" 
            class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
            required 
            readonly 
            tabindex="-1" 
            style="user-select: none; -webkit-user-select: none;"
          >
          <p v-if="errors.form_date" class="text-red-500 text-sm mt-1">{{ errors.form_date }}</p>
        </div>
        <!-- Right Column -->
        <div>
          <label class="block font-bold">Coordinator Name</label>
          <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
        </div>
        
        <!-- Left Column -->
        <div>
          <label class="block font-bold">Dean Name</label>
          <div class="flex gap-1 items-center">
            <input 
              v-model="form.dean_prefix" 
              class="border p-2 w-12 text-xs" 
              placeholder="Pre"
              maxlength="6">
            <input 
              v-model="form.dean_name" 
              @input="form.dean_name = $event.target.value.toUpperCase().slice(0, 54)"
              class="border p-2 flex-1" 
              style="text-transform: uppercase;" 
              maxlength="54">
            <input 
              v-model="form.dean_suffix" 
              class="border p-2 w-14 text-xs" 
              placeholder="Suf"
              maxlength="8">
          </div>
          <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
        </div>
        <!-- Right Column -->
        <div>
          <label class="block font-bold">Director Name</label>
          <input v-model="form.director_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
        </div>
      </div>

      <!-- Adviser Management -->
      <div class="mt-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">Advisers (Maximum 2)</h3>
          <div class="flex gap-2">
            <button 
              @click="addAdviser" 
              type="button" 
              :disabled="form.advisers.length >= maxPages"
              :class="[
                'px-4 py-2 rounded transition-colors',
                form.advisers.length >= maxPages 
                  ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                  : 'bg-blue-500 text-white hover:bg-blue-600'
              ]">
              ➕ Add Adviser
            </button>
          </div>
        </div>

        <!-- Adviser Count Display -->
        <div class="mb-4 p-2 bg-gray-50 border border-gray-200 rounded text-sm">
          <span class="font-semibold">👥 Total Advisers: {{ form.advisers.length }}</span>
          <span v-if="form.advisers.length > 1" class="ml-4 text-gray-600">
            • Currently viewing Adviser {{ currentPage }}
          </span>
        </div>

        <!-- Current Adviser Input Form -->
        <div v-if="currentAdviser" class="p-4 border rounded">
          <div class="flex justify-between items-center mb-4">
            <h4 class="font-bold">Adviser #{{ currentPage }}</h4>
            <button 
              @click="removeAdviser(currentPage - 1)" 
              type="button" 
              :disabled="form.advisers.length <= 1"
              :class="[
                'px-2 py-1 rounded text-sm font-medium transition-colors',
                form.advisers.length <= 1 
                  ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
                  : 'text-red-500 hover:text-red-700 hover:bg-red-50'
              ]"
            >
              Remove
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-bold">Adviser Name</label>
              <div class="flex items-center gap-2 md:gap-3">
                <input 
                  v-model="currentAdviser.adviser_prefix" 
                  class="border p-2 w-12 text-xs" 
                  placeholder="Pre"
                  maxlength="6">
                <input 
                  v-model="currentAdviser.adviser_name" 
                  @input="currentAdviser.adviser_name = $event.target.value.toUpperCase().slice(0, 32)"
                  class="border p-2 flex-1 min-w-0" 
                  style="text-transform: uppercase;" 
                  required 
                  maxlength="32">
                <input 
                  v-model="currentAdviser.adviser_suffix" 
                  class="border p-2 w-14 text-xs" 
                  placeholder="Suf"
                  maxlength="8"
                  style="margin-right:8px;">
              </div>
              <p v-if="errors[`adviser_${currentPage - 1}_name`]" class="text-red-500 text-sm mt-1">{{ errors[`adviser_${currentPage - 1}_name`] }}</p>
            </div>

            <div>
              <label class="block font-bold">Adviser College</label>
              <select 
                :value="currentAdviser.adviser_college || 'None'"
                @change="handleCollegeChange($event, currentPage - 1)"
                class="border p-2 w-full text-black"
                required
              >
                <option value="" disabled>Select College</option>
                <option v-for="option in collegeOptions" :key="option" :value="option">{{ option }}</option>
              </select>
            </div>

            <div>
              <label class="block font-bold">Academic Rank</label>
              <select 
                :value="currentAdviser.adviser_rank || 'None'"
                @change="handleRankChange($event, currentPage - 1)"
                class="border p-2 w-full text-black"
                required
              >
                <option value="" disabled>Select Rank</option>
                <option v-for="option in rankOptions" :key="option" :value="option">{{ option }}</option>
              </select>
            </div>

            <div>
              <label class="block font-bold">Home Address</label>
              <input 
                v-model="currentAdviser.adviser_address" 
                class="border p-2 w-full" 
                required 
                maxlength="108">
              <p v-if="errors[`adviser_${currentPage - 1}_address`]" class="text-red-500 text-sm mt-1">{{ errors[`adviser_${currentPage - 1}_address`] }}</p>
            </div>

            <div>
              <label class="block font-bold">Contact Number(s)</label>
              <input 
                v-model="currentAdviser.adviser_contact" 
                class="border p-2 w-full" 
                required 
                maxlength="15"
                @input="e => { e.target.value = e.target.value.replace(/[^0-9+()\-]/g, ''); currentAdviser.adviser_contact = e.target.value.slice(0, 15); }"
                pattern="[0-9+()\-]*"
              >
              <p v-if="errors[`adviser_${currentPage - 1}_contact`]" class="text-red-500 text-sm mt-1">{{ errors[`adviser_${currentPage - 1}_contact`] }}</p>
            </div>
          </div>
        </div>

        <!-- Pagination Controls for Adviser Inputs -->
        <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mt-8 gap-4">
          <button 
            @click="prevPage" 
            :disabled="currentPage === 1"
            class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
            Previous
          </button>
          <div class="flex gap-2">
            <button 
              v-for="page in totalPages" 
              :key="page"
              @click="goToPage(page)"
              :class="[
                'px-3 py-1 rounded',
                currentPage === page 
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

      <div class="mt-6 text-center">
        <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">{{ props.isEdit ? 'Update' : 'Submit' }}</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.header {
  margin-bottom: 0.5rem;
}
.university-name {
  font-size: 1.1rem;
}
.form-title {
  font-size: 1.1rem;
  letter-spacing: 1px;
}
.indented {
  text-indent: 1.27cm;
}
.blank-line {
  display: inline-block;
  min-width: 200px;
  border-bottom: 1px solid black;
  padding-bottom: 2px;
  text-align: center;
}
.signature-section {
  margin-top: 0;
  float: none;
  width: 100%;
  margin-right: 0;
}
.signature-field {
  margin: 2px 0;
  display: table;
  width: 100%;
}
.signature-label {
  display: table-cell;
  vertical-align: bottom;
  padding-right: 5px;
  white-space: nowrap;
  width: 1%;
}
.signature-value {
  display: table-cell;
  border-bottom: 1px solid black;
  padding-bottom: 2px;
  text-align: left;
  min-height: 14px;
  vertical-align: bottom;
}
.sig-name { width: 230px; }
.sig-signature { width: 205px; }
.sig-college { width: 220px; }
.sig-rank { width: 165px; }
.sig-address { width: 170px; }
.sig-contact { width: 143px; }
.sig-date { width: 234px; }
.signature-line {
  display: inline-block;
  min-width: 200px;
  border-bottom: 1px solid black;
  padding-bottom: 2px;
  text-align: center;
}
.section {
  margin-bottom: 3px;
}
.section p {
  margin: 2px 0;
  word-wrap: break-word;
  line-height: 1.1;
}
.noted-label {
  text-align: left;
  font-size: 1rem;
  font-weight: normal;
  margin-top: -0.7em;
}
.approval-center-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.osas-header {
  font-size: 1.35rem;
  font-weight: bold;
  margin-bottom: 0.1rem;
}
.commitment-form-title {
  font-size: 1.05rem;
  font-weight: bold;
  margin-top: 0.1rem;
  letter-spacing: 0.5px;
}
.commitment-body {
  font-family: 'Times New Roman', Times, serif;
  font-size: 12pt;
  text-align: justify;
  line-height: 1.15;
  max-width: 700px;
  margin: 0 auto;
}
.commitment-body p {
  margin-bottom: 0;
  margin-top: 0;
}
.blank-line.org-name {
  min-width: 300px;
  display: inline-block;
  border-bottom: 1px solid #000;
  vertical-align: middle;
}
.blank-line.year {
  min-width: 60px;
  display: inline-block;
  border-bottom: 1px solid #000;
  vertical-align: middle;
}
.very-respectfully {
  font-family: 'Times New Roman', Times, serif;
  font-size: 12pt;
  margin-top: 1.5em;
  margin-bottom: 1.5em;
  margin-left: calc(3em + 220px);
}
.sig-row {
  display: flex;
  align-items: baseline;
  margin-bottom: 0.2em;
  width: 340px; /* Shortened by 60px */
}
.sig-label {
  font-size: 12pt;
  flex-shrink: 0;
}
.sig-line {
  border-bottom: 1px solid #000;
  flex-grow: 1;
  height: 1em;
  margin-left: 0.5em;
  vertical-align: middle;
  display: inline-block;
}
.noted-signature-block {
  width: 250px;
  margin-left: 120px;
  margin-top: 0.2em;
}
</style>