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

const backHref = computed(() => props.isEdit ? '/applications' : '/applications/select-form');

const emit = defineEmits(['submitted', 'error']);

const form = useForm({
  form_type: 'LSPU-OSAS-SF-001',
  organization_name: props.initialFormData.organization_name?.toUpperCase() || '',
  president_name: props.initialFormData.president_name?.toUpperCase() || '',
  application_date: (() => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  })(),
  adviser_name: props.initialFormData.adviser_name?.toUpperCase() || '',
  adviser_prefix: props.initialFormData.adviser_prefix || '',
  adviser_suffix: props.initialFormData.adviser_suffix || '',
  dean_name: props.initialFormData.dean_name?.toUpperCase() || '',
  dean_prefix: props.initialFormData.dean_prefix || '',
  dean_suffix: props.initialFormData.dean_suffix || '',
  coordinator_name: props.initialFormData.coordinator_name?.toUpperCase() || '',
  director_name: props.initialFormData.director_name?.toUpperCase() || '',
  status: props.initialFormData.status || 'Pending',
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

// Dean signature display logic to match blade rules:
// <=32 chars: normal
// 33-39 chars: 10pt, no wrapping
// >39 chars: 10pt, allow double stacking (split into two lines)
const deanSignatureHtml = computed(() => {
  const full = displayDeanName.value || '';
  const len = full.length;
  if (len > 39) {
    const words = full.split(' ').filter(Boolean);
    const total = words.length;
    const perLine = Math.ceil(total / 2);
    const line1 = words.slice(0, perLine).join(' ');
    const line2 = words.slice(perLine).join(' ');
    return `<strong>${line1}<br>${line2}</strong>`;
  }
  return `<strong>${full}</strong>`;
});

const deanSignatureStyle = computed(() => {
  const full = displayDeanName.value || '';
  const len = full.length;
  if (len > 39) {
    return {
      'font-size': '10pt',
      'text-align': 'center',
      'line-height': '0.9'
    };
  } else if (len > 32) {
    return {
      'font-size': '10pt',
      'white-space': 'nowrap'
    };
  }
  return {};
});

// Adviser signature display logic to match blade rules (same as dean):
const adviserSignatureHtml = computed(() => {
  const full = displayAdviserName.value || '';
  const len = full.length;
  if (len > 39) {
    const words = full.split(' ').filter(Boolean);
    const total = words.length;
    const perLine = Math.ceil(total / 2);
    const line1 = words.slice(0, perLine).join(' ');
    const line2 = words.slice(perLine).join(' ');
    return `<strong>${line1}<br>${line2}</strong>`;
  }
  return `<strong>${full}</strong>`;
});

const adviserSignatureStyle = computed(() => {
  const full = displayAdviserName.value || '';
  const len = full.length;
  if (len > 39) {
    return {
      'font-size': '11pt',
      'text-align': 'center',
      'line-height': '0.9'
    };
  } else if (len > 32) {
    return {
      'font-size': '11pt',
      'white-space': 'nowrap'
    };
  }
  return {};
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

const errors = ref({});

const validateForm = () => {
  errors.value = {};
  
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
  }
  
  if (!form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
  }
  
  if (!form.application_date.trim()) {
    errors.value.application_date = 'Application Date is required';
  }
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Adviser Name is required';
  }
  
  // Dean name is now optional
  
  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator Name is required';
  }
  
  if (!form.director_name.trim()) {
    errors.value.director_name = 'Director Name is required';
  }
  
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

<div class="form-content" style="font-family: 'Times New Roman', serif; font-size: 11pt; line-height: 1.1; margin: 0; padding: 0; box-sizing: border-box; min-height: 100vh;">
  <div class="header text-center relative" style="font-size: 15px; margin: 0 0 0.5cm 0; padding-top: 0.5cm;">
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
    <img src="/images/lspu-logo.png" alt="LSPU Logo" style="position: absolute; top: -0.5cm; left: -2cm; width: 250px; height: auto;">
    <span style="font-family: Calibri, sans-serif;">Republic of the Philippines</span><br>
    <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="university-name" style="max-width: 45%; height: auto; margin: 4px 0; display: inline-block;" />
    <br>
    <span style="font-family: Calibri, sans-serif;">Province of Laguna</span><br>
    <br>
    <strong>OFFICE OF STUDENT AFFAIRS AND SERVICES</strong><br>
    <br>
    <span class="sub-header" style="font-weight: bold; font-size: 13pt;">APPLICATION FOR ORGANIZATION RECOGNITION/RENEWAL OF ACCREDITED STUDENT ORGANIZATION</span>
  </div>

  <div class="section right-align" style="text-align: right; margin-top: 0.5cm;">
  <p style="margin-bottom: 0; margin-top: 0.1cm;"><span class="signature-line" style="display: inline-block; min-width: 150px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;"><strong>{{ formattedDate }}</strong></span></p>
    <p style="margin-top: 0; text-align: left; width: 150px; display: inline-block; padding-left: 70px;">Date</p>
  </div>

  <div class="section" style="margin-bottom: 0.3cm;">
    <p><strong>THE DIRECTOR/CHAIRPERSON</strong><br>Office of Student Affairs and Services<br>LSPU</p>
  </div>

  <div style="height: 0.2cm;"></div>
  <div class="section justified" style="margin-bottom: 0.3cm; text-align: justify;">
    <p>Sir/Madam:</p>
  <div style="height: 0.3cm;"></div>
  <p class="indented" style="text-indent: 1.27cm;"><span style="word-spacing: 15px;">I have the honor to apply for recognition/renewal of the organization</span> <u><strong>{{ form.organization_name }}</strong></u>, to be duly recognized by Laguna State Polytechnic University.</p>
  <div style="height: 0.15cm;"></div>
    <p class="indented" style="text-indent: 1.27cm;">In compliance with CHED Memo Order No. 9s. 2013, Subj.: Enhanced Policies & Guidelines on Student Affairs and Services (Article VIII-Student Development, Section 19. Student Organizations and Activities), I am submitting for proper action the following requirements for recognition and accreditation, to wit:</p>
  </div>

  <div class="section list-indented" style="margin-bottom: 0.3cm;">
    <div style="margin-left: 1.27cm;">
      <p style="position: relative; left: 10px;">1. Letter of application for Organization Recognition (for new organizations) / Organization </p>
      <p style="position: relative; left: 20px;"> Renewal Form (for organizations seeking renewal) <span style="position: absolute; right: 60px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">2. Constitution and By-Laws of the Organization <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">3. Plan of activities for one (1) year <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">4. Accomplishment reports (for renewal of accreditation) <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">5. Adviser(s) Commitment Form <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">6. Certification from respective Dean/Associate Dean <span style="position: absolute; right: 50px;">- 4 copies</span></p>
      <p style="position: relative; left: 10px;">7. Financial Report (if any) <span style="position: absolute; right: 50px;">- 4 copies</span></p>
    </div>
  </div>

  <div class="section justified" style="margin-bottom: 0.3cm; text-align: justify;">
    <p class="indented" style="text-indent: 1.27cm;">It is understood that the provision to the LSPU Supplementary Rules and Regulations Governing Student Organization in this official Recognition is good only for one (1) school year, subject to renewal unless revoked prior its expiration.</p>
  </div>

  <div class="section respectfully-yours" style="text-align: right; padding-right: 90px; margin-top: 0.5cm;">
    <p>Respectfully yours,</p>
  </div>

  <div class="signature right-align" style="margin-top: 0.5cm; text-align: right;">
  <p><span class="signature-line" style="display: inline-block; min-width: 200px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;"><strong>{{ form.president_name }}</strong></span></p>
  <p style="margin: 0; padding: 0;"><span class="title-text" style="display: block; width: 200px; margin-left: 440px; text-align: center; white-space: nowrap; font-size: 11pt;">Organization President</span></p>
  </div>

  <div class="signature right-align" style="margin-top: 0.3cm; text-align: right;">
  <p style="margin-bottom: 2px;">
    <span class="signature-line" style="display: inline-block; min-width: 200px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;"
          :style="{
            'font-size': form.organization_name.length > 84 ? '9pt' : 
                        form.organization_name.length > 74 ? '9pt' : 
                        form.organization_name.length > 65 ? '10pt' : '11pt',
            'text-align': form.organization_name.length > 74 ? 'center' : 'center',
            'line-height': form.organization_name.length > 74 ? '0.9' : 'normal'
          }"
          v-html="(() => {
            const orgName = form.organization_name;
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
  <p style="margin: 0; padding: 0;"><span class="title-text" style="display: block; width: 200px; margin-left: 440px; text-align: center; white-space: nowrap; font-size: 11pt;">Name of Organization</span></p>
  </div>

  <div class="section left-align" style="text-align: left; margin-top: 0.5cm; margin-bottom: 0;">
    <p style="margin-top: 0.5cm; margin-bottom: 0;">NOTED:</p>
  </div>

  <div style="width: 100%; margin-top: 0.1cm; display: flex;">
    <div style="width: 50%; text-align: left;">
  <div class="signature left-align" style="margin-top: 0.3cm; text-align: left;">
  <p><span class="signature-line" style="display: inline-block; min-width: 200px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;" :style="adviserSignatureStyle" v-html="adviserSignatureHtml"></span></p>
    <p><span class="title-text" style="display: block; width: 200px; text-align: center; white-space: nowrap; font-size: 11pt;">Adviser, Student Organization</span></p>
  </div>
    </div>
    <div style="width: 50%; text-align: right;">
    <div class="signature right-align" style="margin-top: 0.3cm; text-align: right;">
  <p><span class="signature-line" style="display: inline-block; min-width: 200px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;" :style="deanSignatureStyle" v-html="deanSignatureHtml"></span></p>
  <p style="margin: 0; padding: 0;"><span class="title-text" style="display: block; width: 200px; margin-left: 120px; text-align: center; white-space: nowrap; font-size: 11pt;">Dean/Assoc. Dean of College</span></p>
    </div>
    </div>
  </div>

  <div class="section center-align" style="margin-bottom: 0.5cm; text-align: center; margin-top: 0.5cm;">
    <p style="margin-bottom: 0;"><strong>Recommending Approval:</strong></p>
  </div>
  <div class="signature center-align" style="margin-top: 0.3cm; text-align: center;">
    <p style="margin-bottom: 0; margin-top: -10px;"><strong><span class="signature-line" style="display: inline-block; min-width: 260px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ form.coordinator_name }}</span></strong></p>
  <p style="margin-top: 0; margin-bottom: 0; text-align: center;"><span class="title-text long-title" style="display: block; width: 260px; margin-left: auto; margin-right: auto; text-align: center; white-space: nowrap; font-size: 11pt;">Coordinator, Student Organization Unit</span></p>
    <div style="height: 5px;"></div>
  </div>

  <div class="section center-align last-section" style="margin-bottom: 0.5cm; text-align: center; padding-bottom: 0.3cm; margin-top: 0.5cm;">
    <p style="margin-bottom: 0;"><strong>Approved/Disapproved:</strong></p>
  </div>
  <div class="signature center-align last-signature" style="margin-top: 0.3cm; text-align: center; margin-bottom: 10px;">
    <p style="margin-bottom: 0; margin-top: -6px;"><strong><span class="signature-line" style="display: inline-block; min-width: 390px; border-bottom: 1px solid black; padding-bottom: 2px; text-align: center;">{{ form.director_name }}</span></strong></p>
  <p style="margin-top: 0; margin-bottom: 0; text-align: center;"><span class="title-text long-title" style="display: block; width: 390px; margin-left: auto; margin-right: auto; text-align: center; white-space: nowrap; font-size: 11pt;">Director/Chairperson, Office of Student Affairs and Services</span></p>
  </div>

  <div class="footer" style="margin-top: 0.5cm; font-size: 10pt; font-family: Calibri, sans-serif; display: flex; justify-content: space-between;">
    <span>LSPU-OSAS-SF-001</span>
    <span>Rev. 1</span>
    <span>09 November 2020</span>
  </div>

  <!-- Form inputs -->
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
          style="text-transform: uppercase;" 
          required>
        <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
      </div>
      <!-- Right Column -->
      <div>
        <label class="block font-bold">Application Date</label>
        <input 
          type="date" 
          :value="form.application_date" 
          class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none text-center rounded-md" 
          required 
          readonly 
          tabindex="-1" 
          style="user-select: none; -webkit-user-select: none;" 
        >
        <p v-if="errors.application_date" class="text-red-500 text-sm mt-1">{{ errors.application_date }}</p>
      </div>
      <!-- Left Column -->
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
      <!-- Right Column -->
      <div>
        <label class="block font-bold">Coordinator Name</label>
  <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none rounded-md" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
      </div>
      <!-- Left Column -->
      <div>
        <label class="block font-bold">Adviser Name</label>
        <div class="flex gap-1">
          <input 
            v-model="form.adviser_prefix" 
            class="border p-2 w-12 text-xs rounded-md" 
            placeholder="Pre"
            maxlength="6">
          <input 
            v-model="form.adviser_name" 
            @input="form.adviser_name = $event.target.value.toUpperCase()"
            class="border p-2 flex-1 rounded-md" 
            style="text-transform: uppercase;" 
            required>
          <input 
            v-model="form.adviser_suffix" 
            class="border p-2 w-14 text-xs rounded-md" 
            placeholder="Suf"
            maxlength="8">
        </div>
        <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
      </div>
      <!-- Right Column -->
      <div>
        <label class="block font-bold">Director Name</label>
  <input v-model="form.director_name" class="border p-2 w-full bg-gray-200 text-gray-500 select-none pointer-events-none rounded-md" readonly tabindex="-1" style="user-select: none; -webkit-user-select: none; text-transform: uppercase;">
      </div>
      <!-- Left Column -->
      <div>
        <label class="block font-bold">Dean Name</label>
        <div class="flex gap-1">
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
      <button
        type="submit"
        @click="submit"
        class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-green-300/30 hover:from-green-400 hover:to-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:from-green-600 active:to-green-700 transition-all duration-300 relative overflow-hidden group"
        style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif;"
      >
        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
        <span>{{ props.isEdit ? 'Update' : 'Submit' }}</span>
        <!-- Show Update icon when editing, otherwise show Create icon -->
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