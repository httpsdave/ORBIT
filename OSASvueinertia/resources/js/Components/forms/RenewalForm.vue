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
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useFormAutoSave } from '@/Composables/useFormAutoSave';
import SubmissionConfirmationModal from '@/Components/SubmissionConfirmationModal.vue';

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

const emit = defineEmits(['submitted']);

// Compute current year and next year for placeholders
const currentYear = computed(() => {
  return new Date().getFullYear().toString().slice(-2);
});

const nextYear = computed(() => {
  return (new Date().getFullYear() + 1).toString().slice(-2);
});

// Computed property to format the date
const formattedDate = computed(() => {
  if (!form.application_date) return '';
  const date = new Date(form.application_date);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
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

const form = useForm({
  form_type: 'LSPU-OSAS-SF-002',
  organization_name: props.initialFormData.organization_name?.toUpperCase() || '',
  college: props.initialFormData.college || '',
  application_date: props.initialFormData.application_date || new Date().toISOString().slice(0, 10),
  academic_year_start: props.initialFormData.academic_year_start || currentYear.value,
  academic_year_end: props.initialFormData.academic_year_end || nextYear.value,
  president_name: props.initialFormData.president_name?.toUpperCase() || '',
  adviser_name: props.initialFormData.adviser_name?.toUpperCase() || '',
  adviser_prefix: props.initialFormData.adviser_prefix || '',
  adviser_suffix: props.initialFormData.adviser_suffix || '',
  dean_name: props.initialFormData.dean_name?.toUpperCase() || '',
  dean_prefix: props.initialFormData.dean_prefix || '',
  dean_suffix: props.initialFormData.dean_suffix || '',
  coordinator_name: props.initialFormData.coordinator_name?.toUpperCase() || '',
  director_name: props.initialFormData.director_name?.toUpperCase() || '',
});

// Add errors ref object
const errors = ref({});
const showRestorePrompt = ref(false);
const autosavedData = ref(null);
const showConfirmationModal = ref(false);

// Initialize autosave - DISABLED by default until we determine what to do
const formDataForAutosave = computed(() => form.data());
const { isAutoSaving, enable, stop } = useFormAutoSave(formDataForAutosave, 'renewal_form', { enabled: false });

// Check if autosaved data is newer than initialized data
const isAutosavedDataNewer = (autosavedTimestamp) => {
  if (!autosavedTimestamp) return false;
  
  // If we have initialFormData with a timestamp, compare
  if (props.initialFormData?.updated_at || props.initialFormData?.created_at) {
    const initialTimestamp = new Date(props.initialFormData.updated_at || props.initialFormData.created_at);
    const autosaveTimestamp = new Date(autosavedTimestamp);
    return autosaveTimestamp > initialTimestamp;
  }
  
  // If no initial timestamp but we have autosaved data, it's "newer"
  return true;
};

// Fetch autosaved data on mount
onMounted(async () => {
  // Try to fetch autosaved data (works for both new and edit scenarios)
  if (!props.isEdit) {
    try {
      const response = await fetch('/get-autosaved-form-data?form_type=renewal_form', {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.form_data) {
          // Check if autosaved data is newer than initialized data
          if (isAutosavedDataNewer(data.updated_at)) {
            autosavedData.value = data.form_data;
            showRestorePrompt.value = true;
          } else {
            // Autosaved data is older, enable autosave with current initialized data
            enable();
          }
        } else {
          // No autosaved data, enable autosave
          enable();
        }
      } else {
        // No autosaved data found (404), enable autosave
        enable();
      }
    } catch (error) {
      console.error('Failed to fetch autosaved data:', error);
      // On error, still enable autosave
      enable();
    }
  }
});

// Cleanup on unmount
onUnmounted(() => {
  stop();
});

const restoreAutosavedData = () => {
  if (autosavedData.value) {
    // Restore all form fields from autosaved data
    Object.keys(autosavedData.value).forEach(key => {
      if (key in form) {
        form[key] = autosavedData.value[key];
      }
    });
  }
  showRestorePrompt.value = false;
  enable(); // Enable autosave after restoring
};

const dismissRestorePrompt = async () => {
  showRestorePrompt.value = false;
  autosavedData.value = null;
  
  // Clear the old autosaved data since user chose to dismiss
  try {
    await fetch('/delete-autosaved-form-data?form_type=renewal_form', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
      },
      body: JSON.stringify({ form_type: 'renewal_form' }),
    });
  } catch (error) {
    console.error('Failed to clear autosaved data:', error);
  }
  
  enable(); // Enable autosave after dismissing
};

// Initialize auto-save functionality
// const { isAutoSaving, autoSaveFormData, stop } = useFormAutoSave(form, 'LSPU-OSAS-SF-002');

// Add a flag to disable auto-save during submit
const isSubmitting = ref(false);
let autoSaveTimeout = null;

// Clean up auto-save watcher and timeout on unmount
// onUnmounted(() => {
//   if (autoSaveTimeout) clearTimeout(autoSaveTimeout);
// });

// Add validateForm function
const validateForm = () => {
  errors.value = {};
  let isValid = true;

  // Check required fields
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
    isValid = false;
  }

  // College is now optional
  
  if (!form.application_date.trim()) {
    errors.value.application_date = 'Application Date is required';
    isValid = false;
  }

  if (!form.academic_year_start.trim()) {
    errors.value.academic_year_start = 'Academic Year Start is required';
    isValid = false;
  }

  if (!form.academic_year_end.trim()) {
    errors.value.academic_year_end = 'Academic Year End is required';
    isValid = false;
  }

  if (!form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
    isValid = false;
  }

  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Adviser Name is required';
    isValid = false;
  }

  // Dean name is now optional

  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator Name is required';
    isValid = false;
  }

  if (!form.director_name.trim()) {
    errors.value.director_name = 'Director Name is required';
    isValid = false;
  }

  return isValid;
};

const handleSubmitClick = () => {
  if (!validateForm()) {
    return;
  }
  
  // Show confirmation modal
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
  stop(); // Stop auto-save before submitting!
  isSubmitting.value = true;
  if (autoSaveTimeout) clearTimeout(autoSaveTimeout); // Stop any pending auto-save

  // Check if we're in edit mode
  if (props.isEdit) {
    // For edit mode, just emit the data - don't make HTTP request here
    emit('submitted', form.data());
    isSubmitting.value = false;
  } else {
    // For create mode, make the POST request
    form.post('/applications', {
      onSuccess: async () => {
        // Clear autosaved data after successful submission
        try {
          await fetch('/delete-autosaved-form-data?form_type=renewal_form', {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
            },
            body: JSON.stringify({ form_type: 'renewal_form' }),
          });
        } catch (error) {
          console.error('Failed to clear autosaved data:', error);
        }
        emit('submitted', form.data());
        isSubmitting.value = false;
      },
      onError: (errors) => {
        console.error('Form submission errors:', errors);
        isSubmitting.value = false;
      }
    });
  }
};
</script>

<template>

<!-- Submission Confirmation Modal -->
<SubmissionConfirmationModal
  :show="showConfirmationModal"
  :isEdit="props.isEdit"
  @confirm="handleConfirmSubmit"
  @cancel="handleCancelSubmit"
/>

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
        @click="dismissRestorePrompt"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
      >
        Start Fresh
      </button>
      <button
        @click="restoreAutosavedData"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
      >
        Restore Changes
      </button>
    </div>
  </div>
</div>

  <div class="mt-6 form-content" style="font-size:11pt;">
    <div class="header text-center relative">
      <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
  <p class="calibri-font" style="font-size:11pt;">Republic of the Philippines</p>
  <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="university-name" style="font-size:11pt;"><br>
  <p class="calibri-font" style="font-size:11pt;">Province of Laguna</p>
        <br>
  <p class="office-title" style="font-size:11pt;">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
  <p class="form-title" style="font-size:11pt;">ORGANIZATION RENEWAL FORM</p>
        <br>
    </div>

    <div class="mt-6 text-right">
  <p class="mb-0" style="font-size:11pt;"><span class="signature-line text-center border-b border-black min-w-[150px] inline-block" style="font-size:11pt;"><strong>{{ formattedDate }}</strong></span></p>
  <p class="mb-0" style="text-align: center; width: 150px; display: inline-block; font-size:11pt;">Date</p>
    </div>
    
    <div style="height: 7px;"></div>

    <div class="section text-left">
  <p class="mb-0" style="font-size:11pt; font-weight:bold;"><strong>THE DIRECTOR/CHAIRPERSON</strong></p>
  <p class="mb-0" style="font-size:11pt; font-weight:bold;"><strong>OFFICE OF STUDENT AFFAIRS AND SERVICES</strong></p>
  <p class="mb-0" style="font-size:11pt; font-weight:bold;"><strong>LSPU</strong></p>
    </div>

    <div class="section">
  <p class="thru-line text-center my-2" style="font-size:11pt; font-weight:bold;"><strong>Thru: The Coordinator, Student Organization Unit</strong></p>
    </div>

  <div class="section" style="margin-bottom:10px;">
  <p style="margin-top:5px; font-family:'Times New Roman', serif; font-size:11pt; font-weight:normal;">Sir/Madam:</p>
  <div style="height:15px; font-size:11pt;"></div>
  <p style="margin-bottom:20px; font-family:'Times New Roman', serif; font-size:11pt; font-weight:normal;">The <span class="dynamic-text signature-line" style="min-width:200px; display:inline-flex; align-items:center; justify-content:center; border-bottom:1px solid #000; padding-bottom:2px; vertical-align:middle; font-size:11pt;"><span style="border-bottom:none; display:inline-block; width:100%; text-align:center; font-size:11pt;"><strong>{{ form.organization_name }}</strong></span></span> <span style="display:inline-block; width:12px; font-size:11pt;"></span>wishes<span style="display:inline-block; width:12px; font-size:11pt;"></span>to<span style="display:inline-block; width:12px; font-size:11pt;"></span>seek<span style="display:inline-block; width:12px; font-size:11pt;"></span>renewal<span style="display:inline-block; width:12px; font-size:11pt;"></span>of<span style="display:inline-block; width:12px; font-size:11pt;"></span>its<span style="display:inline-block; width:12px; font-size:11pt;"></span>recognition<span style="display:inline-block; width:12px; font-size:11pt;"></span>to function as a duly recognized LSPU Organization for Academic Year 20<span class="dynamic-text" style="font-size:11pt;"><u><strong>{{ form.academic_year_start }}</strong></u></span> - 20<span class="dynamic-text" style="font-size:11pt;"><u><strong>{{ form.academic_year_end }}</strong></u></span>.</p>
  <p class="indented" style="text-indent:1.45cm; margin-bottom:20px; font-family:'Times New Roman', serif; font-size:11pt; font-weight:normal; word-spacing:0.8em;">In this connection, we are respectfully requesting from your good office to grant us permission to operate in our institution, subject to the existing rules & regulations of our University.</p>
    <br>
  <p class="indented" style="margin-top:-10px; margin-left:30px; font-family:'Times New Roman', serif; font-size:11pt; font-weight:normal;">Thank you very much.</p>
  </div>

    <div class="section text-right">
  <p class="respectfully-text" style="font-size:11pt; margin-left:calc(59% - 45px); margin-bottom:10px;">Very respectfully yours,</p>
    <div class="signature" style="margin-bottom:20px;">
    <p><span class="signature-line" style="min-width:160px; font-size:11pt;"><strong>{{ form.president_name }}</strong></span></p>
    <p><span class="title-under-signature title-left-adjust" style="font-size:11pt;"><strong>Organization President</strong></span></p>
  </div>
    </div>

    <div class="section text-right">
  <div class="signature" style="margin-bottom:20px;">
    <p>
      <span class="signature-line" style="min-width:160px; font-size:11pt;" 
            :style="{
              'font-size': (form.organization_name || '').length > 84 ? '9pt' : 
                          (form.organization_name || '').length > 74 ? '9pt' : 
                          (form.organization_name || '').length > 65 ? '10pt' : '11pt',
              'text-align': (form.organization_name || '').length > 74 ? 'center' : 'center',
              'line-height': (form.organization_name || '').length > 74 ? '0.9' : 'normal'
            }"
            v-html="(() => {
              const orgName = form.organization_name || '';
              const orgNameLength = orgName.length;
              
              if (orgNameLength > 84) {
                // Triple stack for names over 84 characters
                const words = orgName.split(' ');
                const totalWords = words.length;
                const wordsPerLine = Math.ceil(totalWords / 3);
                const line1 = words.slice(0, wordsPerLine).join(' ');
                const line2 = words.slice(wordsPerLine, wordsPerLine * 2).join(' ');
                const line3 = words.slice(wordsPerLine * 2).join(' ');
                return '<strong>' + line1 + '<br>' + line2 + '<br>' + line3 + '</strong>';
              } else if (orgNameLength > 74) {
                // Double stack for names over 74 characters
                const words = orgName.split(' ');
                const totalWords = words.length;
                const wordsPerLine = Math.ceil(totalWords / 2);
                const line1 = words.slice(0, wordsPerLine).join(' ');
                const line2 = words.slice(wordsPerLine).join(' ');
                return '<strong>' + line1 + '<br>' + line2 + '</strong>';
              } else {
                return '<strong>' + orgName + '</strong>';
              }
            })()">
      </span>
    </p>
    <p><span class="title-under-signature title-left-adjust-more" style="font-size:11pt;"><strong>Name of Organization</strong></span></p>
  </div>
    </div>

        <div class="section text-left">
  <p style="font-size:11pt; font-weight:bold; margin-bottom:10px;"><strong>NOTED:</strong></p>
  <div class="signature" style="margin-bottom:20px;">
    <p><span class="signature-line" style="min-width:220px; font-size:11pt;"><strong>{{ displayAdviserName }}</strong></span></p>
    <p><span class="title-under-signature title-right-adjust" style="font-size:11pt;margin-left:5px"><strong>Adviser/s, Student Organization</strong></span></p>
  </div>
  <div class="signature" style="margin-bottom:30px;">
    <p><span class="signature-line" style="min-width:305px; font-size:11pt;"><strong>{{ displayDeanName }}</strong></span></p>
    <p><span class="title-under-signature" style="font-size:11pt;"><strong>Dean/Assoc. Dean, College of</strong> <span class="signature-line signature-line-inline" style="min-width:120px; font-size:11pt;"><strong>{{ form.college }}</strong></span></span></p>
  </div>
    </div>    <div class="section text-center">
  <p style="margin-left:-380px; font-size:11pt; font-weight:bold; margin-bottom:10px;">Recommending Approval:</p>
  <div class="signature" style="margin-bottom:30px;">
    <p><span class="signature-line" style="min-width:270px; font-size:11pt; font-weight:bold;"><strong>{{ form.coordinator_name }}</strong></span></p>
    <p style="font-size:11pt; font-weight:bold;"><strong>Coordinator, Student Organization Unit</strong></p>
  </div>
    </div>

    <div class="section text-center">
  <p style="margin-left:-380px; font-size:11pt; font-weight:bold; margin-bottom:10px;">Approved / Disapproved:</p>
  <div class="signature" style="margin-bottom:30px;">
    <p><span class="signature-line" style="min-width:390px; font-size:11pt; font-weight:bold;"><strong>{{ form.director_name }}</strong></span></p>
    <p style="font-size:11pt; font-weight:bold;"><strong>Director/Chairperson, Office of Student Affairs and Services</strong></p>
  </div>
      <div class="footer mt-8 text-xs flex justify-between calibri-font">
        <span>LSPU-OSAS-SF-002</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
      </div>
    </div>

    <!-- Form inputs -->
    <form @submit.prevent="submit">
      <div class="mt-8 border-t pt-6">
          <h3 class="text-lg font-bold mb-4">Form Details</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Left Column -->
              <div>
                   <label class="block font-bold">Organization Name (full)</label>
                  <input 
                    v-model="form.organization_name" 
                    @input="form.organization_name = $event.target.value.toUpperCase()"
                    class="border p-2 w-full rounded-md" 
                    style="text-transform: uppercase;">
                  <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
              </div>

              <!-- Right Column -->
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

              <!-- Left Column -->
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

              <!-- Right Column -->
              <div>
                  <label class="block font-bold">Coordinator Name</label>
                  <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none rounded-md" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
              </div>

              <!-- Left Column -->
              <div>
                  <label class="block font-bold">President Name</label>
                  <input 
                    v-model="form.president_name" 
                    @input="form.president_name = $event.target.value.toUpperCase()"
                    class="border p-2 w-full rounded-md" 
                    style="text-transform: uppercase;">
                  <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
              </div>

              <!-- Right Column -->
              <div>
                  <label class="block font-bold">Chairperson Name</label>
                  <input v-model="form.director_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none rounded-md" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
              </div>

              <!-- Left Column -->
              <div>
                  <label class="block font-bold">Adviser Name</label>
                  <div class="flex gap-1 items-center">
                    <input 
                      v-model="form.adviser_prefix" 
                      class="border p-2 w-12 text-xs rounded-md" 
                      placeholder="Pre"
                      maxlength="6">
                    <input 
                      v-model="form.adviser_name" 
                      @input="form.adviser_name = $event.target.value.toUpperCase()"
                      class="border p-2 flex-1 rounded-md" 
                      style="text-transform: uppercase;">
                    <input 
                      v-model="form.adviser_suffix" 
                      class="border p-2 w-14 text-xs rounded-md" 
                      placeholder="Suf"
                      maxlength="8">
                  </div>
                  <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
              </div>

              <!-- Right Column - Empty placeholder for grid alignment -->
              <div v-if="props.isAdmin">
                  <label class="block font-bold">
                    Application Date
                    <span class="text-sm text-blue-600 font-normal ml-2">(Admin only)</span>
                  </label>
                  <input 
                    type="date" 
                    v-model="form.application_date" 
                    class="border p-2 w-full rounded-md bg-white text-gray-900 cursor-pointer" 
                    required
                  >
                  <p v-if="errors.application_date" class="text-red-500 text-sm mt-1">{{ errors.application_date }}</p>
              </div>

              <!-- Left Column -->
              <div>
                  <label class="block font-bold">Dean Name</label>
                  <div class="flex gap-1 items-center">
                    <input 
                      v-model="form.dean_prefix" 
                      class="border p-2 w-12 text-xs rounded-md" 
                      placeholder="Pre"
                      maxlength="6">
                    <input 
                      v-model="form.dean_name" 
                      @input="form.dean_name = $event.target.value.toUpperCase()"
                      class="border p-2 flex-1 rounded-md" 
                      style="text-transform: uppercase;">
                    <input 
                      v-model="form.dean_suffix" 
                      class="border p-2 w-14 text-xs rounded-md" 
                      placeholder="Suf"
                      maxlength="8">
                  </div>
                  <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
              </div>
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
        
        <div class="flex items-center justify-center gap-3">
          <a :href="backHref"
             class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
             style="font-family: system-ui, -apple-system, sans-serif;">
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
          </a>

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
    </div>
  </form>
</div>
</template>

<style scoped>
/* Set Font to Times New Roman, Font Size to 11pt, and Line Spacing to 1.1 */
.form-content {
    font-family: 'Times New Roman', Times, serif;
    font-size: 11pt;
    line-height: 1.1;
}

/* Calibri font for specific elements */
.calibri-font {
    font-family: 'Calibri', 'Arial', sans-serif;
}

.office-title {
    font-size: 16px;
    font-weight: bold;
    margin-top: 0px;
    margin-bottom: 10px;
}

.form-title {
    font-size: 16px;
    font-weight: bold;
    margin: 0;
}

.university-name {
    max-width: 45%;
    height: auto;
    margin: 4px 0;
    display: inline-block;
}

.indented {
    text-indent: 1.27cm;
    margin-bottom: 20px;
}

.signature-line {
    display: inline-block;
    width: fit-content;
    border-bottom: 1px solid black;
    margin-bottom: 2px;
    text-align: center;
    overflow-wrap: break-word;
    word-wrap: break-word;
}

.title-under-signature {
    display: inline-block;
    width: fit-content;
    text-align: center;
    margin: 0;
    padding: 0;
}

.title-left-adjust {
    transform: translateX(-5px);
}

.title-left-adjust-more {
    transform: translateX(-10px);
}

.signature-line-inline {
    vertical-align: text-bottom !important;
    position: relative;
    top: 0px;
}

.respectfully-text {
    text-align: left;
    margin-left: 59%;
    display: block;
}

.dynamic-text {
    display: inline;
    word-break: break-word;
}

.thru-line {
    text-align: left;
    font-style: bold;
    margin: 10px 0;
    margin-left: 70px;
}

/* Basic input focus styling */
input:focus {
    outline: 2px solid #2563eb;
    outline-offset: -2px;
}

/* Ensure proper printing */
@media print {
    .form-content {
        page-break-before: always;
    }
    
    .signature-line {
        background: transparent !important;
    }
}
</style>