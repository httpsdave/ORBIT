<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useFormAutoSave } from '@/Composables/useFormAutoSave';

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

const emit = defineEmits(['submitted', 'error']);

// Autosave state
const showRestorePrompt = ref(false);
const autosavedData = ref(null);

// Compute current year and next year for placeholders
const currentYear = computed(() => {
  return new Date().getFullYear().toString().slice(-2);
});

const nextYear = computed(() => {
  return (new Date().getFullYear() + 1).toString().slice(-2);
});

// Initialize approved activities
const initializeApprovedActivities = () => {
  if (props.initialFormData?.approved_activities && props.initialFormData.approved_activities.length > 0) {
    return props.initialFormData.approved_activities;
  } else {
    return Array(4).fill(null).map(() => ({
      title: '',
      planned_date: '',
      actual_date: '',
      proposed_budget: '',
      actual_expenditure: '',
      target_participants: '',
      actual_participants: '',
      status: '',
      justification: ''
    }));
  }
};

// Initialize unapproved activities
const initializeUnapprovedActivities = () => {
  if (props.initialFormData?.unapproved_activities && props.initialFormData.unapproved_activities.length > 0) {
    return props.initialFormData.unapproved_activities;
  } else {
    return Array(4).fill(null).map(() => ({
      title: '',
      planned_date: '',
      actual_date: '',
      proposed_budget: '',
      actual_expenditure: '',
      target_participants: '',
      actual_participants: '',
      status: '',
      justification: ''
    }));
  }
};

const form = useForm({
  form_type: 'LSPU-OSAS-SF-STATUS-REPORT',
  organization_name: props.initialFormData.organization_name?.toUpperCase() || '',
  academic_year_start: props.initialFormData.academic_year_start || currentYear.value,
  academic_year_end: props.initialFormData.academic_year_end || nextYear.value,
  report_date: props.initialFormData.report_date || (() => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  })(),
  president_name: props.initialFormData.president_name?.toUpperCase() || '',
  adviser_name: props.initialFormData.adviser_name?.toUpperCase() || '',
  dean_name: props.initialFormData.dean_name?.toUpperCase() || '',
  coordinator_name: props.initialFormData.coordinator_name?.toUpperCase() || '',
  director_name: props.initialFormData.director_name?.toUpperCase() || '',
  approved_activities: initializeApprovedActivities(),
  unapproved_activities: initializeUnapprovedActivities(),
});

// Initialize autosave (disabled by default)
const { isAutoSaving, enable: enableAutoSave, disable: disableAutoSave, start: startAutoSave, stop: stopAutoSave } = useFormAutoSave(form, 'activity_status_report', { enabled: false });

// Add errors ref object
const errors = ref({});

// Computed property to format the date
const formattedDate = computed(() => {
  if (!form.report_date) return '';
  const date = new Date(form.report_date);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

// Autosave functions
const restoreAutosave = () => {
  if (autosavedData.value) {
    Object.assign(form, autosavedData.value);
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
      body: JSON.stringify({ form_type: 'activity_status_report' })
    });
  } catch (error) {
    console.error('Failed to delete autosaved data:', error);
  }
};

// Add validateForm function
const validateForm = () => {
  errors.value = {};
  let isValid = true;

  // Check main form required fields
  if (!form.organization_name || !form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
    isValid = false;
  }

  if (!form.report_date || !form.report_date.trim()) {
    errors.value.report_date = 'Report Date is required';
    isValid = false;
  }

  if (!form.president_name || !form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
    isValid = false;
  }

  if (!form.adviser_name || !form.adviser_name.trim()) {
    errors.value.adviser_name = 'Adviser Name is required';
    isValid = false;
  }

  if (!form.coordinator_name || !form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator Name is required';
    isValid = false;
  }

  if (!form.director_name || !form.director_name.trim()) {
    errors.value.director_name = 'Director Name is required';
    isValid = false;
  }

  return isValid;
};

const submit = async () => {
  if (!validateForm()) {
    emit('error', 'Please fill in all required fields.');
    return;
  }
  
  // Stop autosave before submission
  stopAutoSave();
  
  console.log('Submitting form data:', form.data());
  
  // Check if we're in edit mode
  if (props.isEdit) {
    // For edit mode, just emit the data
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
            body: JSON.stringify({ form_type: 'activity_status_report' })
          });
        } catch (error) {
          console.error('Failed to delete autosaved data:', error);
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

// Lifecycle hooks
onMounted(async () => {
  // Fetch autosaved data
  try {
    const response = await fetch('/get-autosaved-form-data?form_type=activity_status_report', {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      if (data.form_data) {
        autosavedData.value = data.form_data;
        
        // Compare timestamps
        const autosavedTimestamp = new Date(data.updated_at).getTime();
        const initializedTimestamp = props.initialFormData?.updated_at 
          ? new Date(props.initialFormData.updated_at).getTime() 
          : 0;
        
        if (autosavedTimestamp > initializedTimestamp) {
          showRestorePrompt.value = true;
        } else {
          enableAutoSave();
          startAutoSave();
        }
      } else {
        enableAutoSave();
        startAutoSave();
      }
    } else {
      enableAutoSave();
      startAutoSave();
    }
  } catch (error) {
    console.error('Failed to fetch autosaved data:', error);
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

    <!-- Document Header -->
    <div class="header text-center relative py-4">
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
      <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
      <div class="font-normal text-[11pt] leading-tight" style="font-family:Calibri,sans-serif;">
        Republic of the Philippines<br>
        <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="inline-block align-middle h-[22px] max-w-[23%] my-1 university-name" /><br>
        Province of Laguna
      </div>
      <div class="font-bold text-[11pt] mt-2" style="font-family:'Times New Roman',serif;">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
      <div class="font-bold text-[13pt] mt-2" style="font-family:'Times New Roman',serif;">ACTIVITY STATUS REPORT</div>
    </div>

    <!-- Date Section -->
    <div class="text-right mt-4" style="font-family:'Times New Roman',serif;">
      <div class="inline-block text-center">
        <p class="border-b-2 border-black font-bold text-[11pt] px-4">{{ formattedDate }}</p>
        <p class="text-[11pt] mt-1 pr-12">Date</p>
      </div>
    </div>

    <!-- Addressee Section -->
    <div class="text-left mt-4 mb-4" style="font-family:'Times New Roman',serif;font-size:11pt;">
      <p><strong>THE DIRECTOR/CHAIRPERSON</strong></p>
      <p>Office of Student Affairs and Services</p>
      <p>LSPU</p>
    </div>

    <!-- Greeting -->
    <div class="text-left mb-4" style="font-family:'Times New Roman',serif;font-size:11pt;">
      <p>Sir/Madam:</p>
    </div>

    <!-- Intro Paragraph -->
    <div class="text-justify mb-4" style="font-family:'Times New Roman',serif;font-size:11pt;text-indent:1.27cm;line-height:1.15;">
      <p>In compliance with the requirements of the Office of Student Affairs and Services, we respectfully submit the Status Report on the Plan of Activities for Academic Year {{ form.academic_year_start }}-{{ form.academic_year_end }}. The report presents activities conducted as planned, those not implemented with justifications, and additional activities carried out beyond the approved plan.</p>
    </div>

    <!-- Approved Activities Table -->
    <div class="text-center font-bold mt-4 mb-2" style="font-family:'Times New Roman',serif;font-size:11pt;">
      ACTIVITIES UNDER THE APPROVED PLAN OF ACTIVITIES
    </div>
    <table class="w-full border-collapse border border-black mb-6" style="font-family:'Times New Roman',serif;font-size:10pt;">
      <thead>
        <tr class="bg-gray-100">
          <th class="border border-black p-2" style="width:18%;">Title of Activity / Program</th>
          <th class="border border-black p-2" style="width:10%;">Planned / Target Date</th>
          <th class="border border-black p-2" style="width:10%;">Actual Date Conducted</th>
          <th class="border border-black p-2" style="width:8%;">Proposed Budget</th>
          <th class="border border-black p-2" style="width:8%;">Actual Expenditure</th>
          <th class="border border-black p-2" style="width:12%;">Target No. of Participants</th>
          <th class="border border-black p-2" style="width:12%;">Actual No. of Participants</th>
          <th class="border border-black p-2" style="width:10%;">Remarks/ Status</th>
          <th class="border border-black p-2" style="width:12%;">Justification / Notes</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(activity, index) in form.approved_activities" :key="'approved-' + index">
          <td class="border border-black p-2 text-left">
            <input v-model="activity.title" class="w-full p-1 text-sm" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.planned_date" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.actual_date" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.proposed_budget" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.actual_expenditure" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.target_participants" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.actual_participants" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.status" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-left">
            <input v-model="activity.justification" class="w-full p-1 text-sm" type="text">
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Unapproved Activities Table -->
    <div class="text-center font-bold mt-6 mb-2" style="font-family:'Times New Roman',serif;font-size:11pt;">
      ACTIVITIES NOT IN THE APPROVED PLAN OF ACTIVITIES
    </div>
    <table class="w-full border-collapse border border-black mb-6" style="font-family:'Times New Roman',serif;font-size:10pt;">
      <thead>
        <tr class="bg-gray-100">
          <th class="border border-black p-2" style="width:18%;">Title of Activity / Program</th>
          <th class="border border-black p-2" style="width:10%;">Planned / Target Date</th>
          <th class="border border-black p-2" style="width:10%;">Actual Date Conducted</th>
          <th class="border border-black p-2" style="width:8%;">Proposed Budget</th>
          <th class="border border-black p-2" style="width:8%;">Actual Expenditure</th>
          <th class="border border-black p-2" style="width:12%;">Target No. of Participants</th>
          <th class="border border-black p-2" style="width:12%;">Actual No. of Participants</th>
          <th class="border border-black p-2" style="width:10%;">Remarks/ Status</th>
          <th class="border border-black p-2" style="width:12%;">Justification / Notes</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(activity, index) in form.unapproved_activities" :key="'unapproved-' + index">
          <td class="border border-black p-2 text-left">
            <input v-model="activity.title" class="w-full p-1 text-sm" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.planned_date" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.actual_date" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.proposed_budget" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.actual_expenditure" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.target_participants" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.actual_participants" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-center">
            <input v-model="activity.status" class="w-full p-1 text-sm text-center" type="text">
          </td>
          <td class="border border-black p-2 text-left">
            <input v-model="activity.justification" class="w-full p-1 text-sm" type="text">
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Signatures Section -->
    <div class="mt-8 signatures-section" style="font-family:'Times New Roman',serif;">
      <!-- Respectfully yours -->
      <div class="text-left mb-4" style="font-size:11pt;">
        <p>Respectfully yours,</p>
      </div>

      <!-- President Signature -->
      <div class="text-left mb-6">
        <div class="border-b border-black min-w-[220px] inline-block text-center pb-1 font-bold text-[11pt]">
          {{ form.president_name }}
        </div>
        <div class="text-[11pt] mt-1">Organization President</div>
      </div>

      <!-- Noted Section -->
      <div class="text-left mb-2 text-[11pt]">
        <p><strong>NOTED:</strong></p>
      </div>

      <!-- Adviser and Dean Signatures (side by side) -->
      <div class="flex justify-between mb-6">
        <div class="text-left" style="width: 48%;">
          <div class="border-b border-black min-w-[220px] inline-block text-center pb-1 font-bold text-[11pt]">
            {{ form.adviser_name }}
          </div>
          <div class="text-[11pt] mt-1">Adviser, Student Organization</div>
        </div>
        <div class="text-right" style="width: 48%;">
          <div class="border-b border-black min-w-[220px] inline-block text-center pb-1 font-bold text-[11pt]">
            {{ form.dean_name }}
          </div>
          <div class="text-[11pt] mt-1 text-right">Dean/Assoc. Dean of College</div>
        </div>
      </div>

      <!-- Recommending Approval -->
      <div class="text-center mt-8 mb-6">
        <div class="font-bold mb-2 text-[11pt]"><strong>Recommending Approval:</strong></div>
        <div class="border-b border-black min-w-[270px] inline-block text-center pb-1 font-bold text-[11pt]">
          {{ form.coordinator_name }}
        </div>
        <div class="text-[11pt] mt-1">Coordinator, Student Organization Unit</div>
      </div>

      <!-- Approved/Disapproved -->
      <div class="text-center mt-8 mb-6">
        <div class="font-bold mb-2 text-[11pt]"><strong>Approved/Disapproved:</strong></div>
        <div class="border-b border-black min-w-[380px] inline-block text-center pb-1 font-bold text-[11pt]">
          {{ form.director_name }}
        </div>
        <div class="text-[11pt] mt-1">Director/Chairperson, Office of Student Affairs and Services</div>
      </div>
    </div>

    <!-- Form inputs section -->
    <div class="mt-8 border-t pt-6">
      <h3 class="text-lg font-bold mb-4">Form Details</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Left Column -->
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

        <!-- Right Column -->
        <div>
          <label class="block font-bold">Report Date</label>
          <input 
            type="date" 
            v-model="form.report_date" 
            class="border p-2 w-full rounded-md" 
            required>
          <p v-if="errors.report_date" class="text-red-500 text-sm mt-1">{{ errors.report_date }}</p>
        </div>

        <!-- Left Column -->
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
        </div>

        <!-- Right Column -->
        <div>
          <label class="block font-bold">President Name</label>
          <input 
            v-model="form.president_name" 
            @input="form.president_name = $event.target.value.toUpperCase()"
            class="border p-2 w-full rounded-md" 
            style="text-transform: uppercase;" 
            required>
          <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
        </div>

        <!-- Left Column -->
        <div>
          <label class="block font-bold">Adviser Name</label>
          <input 
            v-model="form.adviser_name" 
            @input="form.adviser_name = $event.target.value.toUpperCase()"
            class="border p-2 w-full rounded-md" 
            style="text-transform: uppercase;" 
            required>
          <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
        </div>

        <!-- Right Column -->
        <div>
          <label class="block font-bold">Dean Name</label>
          <input 
            v-model="form.dean_name" 
            @input="form.dean_name = $event.target.value.toUpperCase()"
            class="border p-2 w-full rounded-md" 
            style="text-transform: uppercase;">
          <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
        </div>

        <!-- Left Column -->
        <div>
          <label class="block font-bold">Coordinator Name</label>
          <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none rounded-md" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
        </div>

        <!-- Right Column -->
        <div>
          <label class="block font-bold">Director Name</label>
          <input v-model="form.director_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none rounded-md" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
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
        
        <button
          type="submit"
          @click="submit"
          class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:from-green-600 active:to-green-700 transition-all duration-300 relative overflow-hidden group"
          style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif;"
        >
          <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
          <span>{{ props.isEdit ? 'Update' : 'Submit' }}</span>
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
</template>

<style scoped>
/* Ensure A4 Paper Size - Landscape */
.form-content {
  width: 297mm;
  min-height: 210mm;
  padding: 1.5cm;
  margin: auto;
  background: white;
  font-family: 'Times New Roman', serif;
  font-size: 11pt;
  line-height: 1.1;
}

/* Header styling */
.header {
  font-family: 'Times New Roman', serif;
}

.university-name {
  max-width: 23%;
  height: auto;
  margin: 4px 0;
  display: inline-block;
}

/* Table styling to match blade template */
table {
  table-layout: fixed;
  overflow-wrap: break-word;
}

table td {
  vertical-align: top;
  word-wrap: break-word;
}

table input {
  border: none;
  outline: none;
  background: transparent;
}

table input:focus {
  background: #f3f4f6;
  border-radius: 2px;
}

/* Signature styling */
.signatures-section {
  font-family: 'Times New Roman', serif;
  font-size: 11pt;
}

/* Ensure proper printing */
@media print {
  .form-content {
    width: 297mm;
    height: 210mm;
    margin: 0;
    padding: 1.5cm;
  }
  
  table { 
    page-break-inside: avoid; 
    margin-bottom: 20px;
  }
  
  tr { 
    page-break-inside: avoid; 
    page-break-after: auto; 
  }
  
  td { 
    vertical-align: top; 
    word-wrap: break-word;
  }
  
  .signatures-section {
    page-break-inside: avoid;
  }
}
</style>
