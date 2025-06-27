<script setup>
import { ref } from 'vue';
import FormSelector from '@/Components/FormSelector.vue';
import StudentOrganizationForm from '@/Components/forms/StudentOrganizationForm.vue';
import RenewalForm from '@/Components/forms/RenewalForm.vue';
import CommitmentForm from '@/Components/forms/CommitmentForm.vue';
import PlanOfActivitiesForm from '@/Components/forms/PlanOfActivitiesForm.vue';
import ListOfMembersForm from '@/Components/forms/ListOfMembersForm.vue';
import StudentCertificationForm from '@/Components/forms/StudentCertificationForm.vue';
import ListOfOfficersForm from '@/Components/forms/ListOfOfficersForm.vue';
import ActivityAttendanceForm from '@/Components/forms/ActivityAttendanceForm.vue';

// Get saved form data from props
const props = defineProps({
    savedFormData: {
        type: Object,
        default: () => ({})
    }
});

const currentForm = ref('');
const formData = ref({});

const formOptions = [
    { value: 'LSPU-OSAS-SF-001', label: 'Student Organization Recognition Form' },
    { value: 'LSPU-OSAS-SF-002', label: 'Renewal Form' },
    { value: 'LSPU-OSAS-SF-003', label: 'Commitment Form' },
    { value: 'LSPU-OSAS-SF-004', label: 'Plan of Activities' },
    { value: 'LSPU-OSAS-SF-005', label: 'List of Members' },
    { value: 'LSPU-OSAS-SF-006', label: 'Student Certification' },
    { value: 'LSPU-OSAS-SF-007', label: 'List of Officers' }, 
    { value: 'LSPU-OSAS-SF-009', label: 'Student Activity Attendance Sheet' },
];

const handleFormSelection = (formId) => {
    currentForm.value = formId;
    
    // Filter out array fields from saved data to prevent issues
    const filteredSavedData = {};
    Object.keys(props.savedFormData).forEach(key => {
        if (!Array.isArray(props.savedFormData[key]) && typeof props.savedFormData[key] !== 'object') {
            filteredSavedData[key] = props.savedFormData[key];
        }
    });
    
    // Initialize form data with saved data if available
    if (formId === 'LSPU-OSAS-SF-004') {
        formData.value = {
            ...filteredSavedData,
            activities: Array(3).fill().map(() => ({
                objective: '',
                name: '',
                description: '',
                persons_involved: '',
                target_date: '',
                budget: 0
            }))
        };
    }
    // Initialize members for List of Members form
    else if (formId === 'LSPU-OSAS-SF-005') {
        formData.value = {
            ...filteredSavedData,
            members: Array(4).fill().map(() => ({
                student_name: '',
                student_number: '',
                course_year_section: '',
                photo_path: null
            }))
        };
    }
    // Initialize officers for List of Officers form
    else if (formId === 'LSPU-OSAS-SF-007') {
        formData.value = {
            ...filteredSavedData,
            officers: Array(4).fill().map(() => ({
                student_name: '',
                position: '',
                student_number: '',
                photo_path: null
            }))
        };
    }
    // Initialize attendees for Student Activity Attendance Sheet
    else if (formId === 'LSPU-OSAS-SF-009') {
        formData.value = {
            ...filteredSavedData,
            attendees: Array(10).fill().map(() => ({
                name: '',
                course_year_section: '',
                signature: null
            }))
        };
    }
    // For other forms, just use saved data
    else {
        formData.value = { ...filteredSavedData };
    }
};

const handleFormSubmitted = (data) => {
    console.log('Form submitted:', data);
    // Reset the form
    currentForm.value = '';
    formData.value = {};
    
};
</script>

<template>
  <div class="p-6 document">
    <!-- Show form selector if no form is selected -->
    <FormSelector 
      v-if="!currentForm" 
      :formOptions="formOptions" 
      @form-selected="handleFormSelection" 
    />

    <!-- Show the selected form -->
    <div v-else>
      <!-- Student Organization Form -->
      <StudentOrganizationForm 
        v-if="currentForm === 'LSPU-OSAS-SF-001'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Renewal Form -->
      <RenewalForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-002'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Commitment Form -->
      <CommitmentForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-003'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Plan of Activities Form -->
      <PlanOfActivitiesForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-004'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- List of Members Form -->
      <ListOfMembersForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-005'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Student Certification Form -->
      <StudentCertificationForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-006'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- List of Officers Form -->
      <ListOfOfficersForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-007'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
      
      <!-- Student Activity Attendance Sheet -->
      <ActivityAttendanceForm 
        v-else-if="currentForm === 'LSPU-OSAS-SF-009'" 
        :initialFormData="formData"
        @submitted="handleFormSubmitted"
      />
    </div>
  </div>
  
</template>
<style scoped>
/* Ensure A4 Paper Size */
.document {
    width: 210mm;
    min-height: 297mm;
    padding: 20mm;
    margin: auto;
    background: white;
}

/* Set Font to Times New Roman, Font Size to 10pt, and Line Spacing to 1.0 */
.form-content {
    font-family: 'Times New Roman', Times, serif;
    font-size: 10pt;
    line-height: 1.0;
}

/* Ensure Proper Printing */
@media print {
    body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 20mm;
    }
    .document {
        page-break-before: always;
    }
}
</style>