<script setup>
import { ref } from 'vue';
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

const form = useForm({
  form_type: 'LSPU-OSAS-SF-001',
  organization_name: props.initialFormData.organization_name || '',
  president_name: props.initialFormData.president_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
  status: props.initialFormData.status || 'Pending',
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
  
  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean Name is required';
  }
  
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
  <div class="mt-6 form-content">
    <div class="header text-center relative">
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
        <p class="text-sm font-bold mb-0">Republic of the Philippines</p>
        <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
        <p class="text-sm mb-0">Province of Laguna</p>
        <p class="text-sm mb-0">Office of Student Affairs and Services</p>
    </div>

    <div class="mt-6 text-right">
        <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.formatted_date }}</span></p>
        <p class="mb-0">Date</p>
    </div>

    <div class="section text-left mt-4">
        <p class="mb-0"><strong>THE DIRECTOR/CHAIRPERSON</strong></p>
        <p class="mb-0">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
        <p class="mb-0">LSPU</p>
    </div>

    <div class="section mt-4">
        <p class="mb-1">Sir/Madam:</p>
        
        <p class="indented">I have the honor to apply for recognition/renewal of <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.organization_name }}</span>, a duly recognized student organization in this University.</p>
    </div>

    <div class="section mt-4">
        <p class="indented">In compliance with CHED Memo Order #9 s. 2013, Subj.: Enhanced Policies & Guidelines on Student Affairs and Services (Article VIII - Student Development, Section 19. Student Organizations and Activities), I am submitting for proper action the following requirements for recognition and accreditation:</p>
        
        <ul class="list-disc pl-10 mt-2">
            <li>Letter for application for recognition (4 copies)</li>
            <li>Constitution and By-Laws of the Organization (4 copies)</li>
            <li>Program of activities for one (1) year (4 copies)</li>
            <li>List of officers with signature, student I.D. Nos. and attached 2x2 I.D. picture (4 copies)</li>
            <li>List of members with signature, student I.D. number and attached 1x1 ID picture (4 copies)</li>
            <li>Accomplishment report (for renewal of accreditation) (4 copies)</li>
        </ul>
    </div>

    <div class="section mt-4">
        <p class="indented">It is understood that the provision to the LSPU Supplementary Rules and Regulations Governing Student Organization in this official recognition is good only for one (1) school year, subject to renewal unless revoked prior to this expiration.</p>
    </div>

    <div class="section text-right mt-6">
        <p class="mb-1">Respectfully yours,</p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.president_name }}</span></p>
            <p class="mb-0">Organization President</p>
        </div>
    </div>

    <div class="section text-left mt-6">
        <p class="mb-1"><strong>Noted:</strong></p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.adviser_name }}</span></p>
            <p class="mb-0">Adviser, Student Organization</p>
        </div>
    </div>

    <div class="section text-right mt-6">
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.dean_name }}</span></p>
            <p class="mb-0">Dean/Associate Dean</p>
        </div>
    </div>

    <div class="section text-center mt-6">
        <p class="mb-1"><strong>Recommending Approval:</strong></p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.coordinator_name }}</span></p>
            <p class="mb-0">Coordinator, Student Organization Unit</p>
        </div>
    </div>

    <div class="section text-center mt-6">
        <p class="mb-1"><strong>Approved/Disapproved:</strong></p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.director_name }}</span></p>
            <p class="mb-0">Director, Office of Student Affairs and Services</p>
        </div>
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
                <label class="block font-bold">Application Date</label>
                <input type="date" v-model="form.application_date" class="border p-2 w-full" required>
                <p v-if="errors.application_date" class="text-red-500 text-sm mt-1">{{ errors.application_date }}</p>
            </div>

            <div>
                <label class="block font-bold">President Name</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
                <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Adviser Name</label>
                <input v-model="form.adviser_name" class="border p-2 w-full" required>
                <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Dean Name</label>
                <input v-model="form.dean_name" class="border p-2 w-full" required>
                <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Coordinator Name</label>
                <input v-model="form.coordinator_name" class="border p-2 w-full" required>
                <p v-if="errors.coordinator_name" class="text-red-500 text-sm mt-1">{{ errors.coordinator_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Director Name</label>
                <input v-model="form.director_name" class="border p-2 w-full" required>
                <p v-if="errors.director_name" class="text-red-500 text-sm mt-1">{{ errors.director_name }}</p>
            </div>
        </div>

        <div class="mt-6 text-center">
            <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
        </div>
    </div>

    <div class="footer mt-8 text-xs flex justify-between">
        <span>LSPU-OSAS-SF-001</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
    </div>
</div>

</template>