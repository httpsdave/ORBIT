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
  form_type: 'LSPU-OSAS-SF-006',
  organization_name: props.initialFormData.organization_name || '',
  president_name: props.initialFormData.president_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
 
  director_name: props.initialFormData.director_name || '',

  // Fixed: Convert boolean values properly for checkboxes
  is_bonafide: props.initialFormData.is_bonafide === true || props.initialFormData.is_bonafide === 1 || props.initialFormData.is_bonafide === '1',
  is_not_academic_probation: props.initialFormData.is_not_academic_probation === true || props.initialFormData.is_not_academic_probation === 1 || props.initialFormData.is_not_academic_probation === '1',
  is_not_disciplinary_probation: props.initialFormData.is_not_disciplinary_probation === true || props.initialFormData.is_not_disciplinary_probation === 1 || props.initialFormData.is_not_disciplinary_probation === '1',
  has_position: props.initialFormData.has_position === true || props.initialFormData.has_position === 1 || props.initialFormData.has_position === '1',

  certification_date: props.initialFormData.certification_date || '',
  student_name: props.initialFormData.student_name || '',
  course_year_section: props.initialFormData.course_year_section || '',
  position_rank: props.initialFormData.position_rank || '',
});

const errors = ref({});

const validateForm = () => {
  errors.value = {};
  
  if (!form.certification_date.trim()) {
    errors.value.certification_date = 'Certification Date is required';
  }
  
  if (!form.student_name.trim()) {
    errors.value.student_name = 'Student Name is required';
  }
  
  if (!form.course_year_section.trim()) {
    errors.value.course_year_section = 'Course/Year and Section is required';
  }
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Faculty Adviser is required';
  }
  
  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean/Assoc. Dean Name is required';
  }
  
  
  
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
  }
  
  if (!form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
  }
  
  if (!form.is_bonafide) {
    errors.value.is_bonafide = 'Bonafide Student certification is required';
  }
  
  if (!form.is_not_academic_probation) {
    errors.value.is_not_academic_probation = 'Academic probation status is required';
  }
  
  if (!form.is_not_disciplinary_probation) {
    errors.value.is_not_disciplinary_probation = 'Disciplinary probation status is required';
  }
  
  if (!form.has_position) {
    errors.value.has_position = 'Position/rank status is required';
  }
  
  return Object.keys(errors.value).length === 0;
};

const submit = () => {
  if (!validateForm()) {
    return;
  }

  // Cast boolean fields to 1/0 for backend compatibility
  const data = {
    ...form.data(),
    is_bonafide: form.is_bonafide ? 1 : 0,
    is_not_academic_probation: form.is_not_academic_probation ? 1 : 0,
    is_not_disciplinary_probation: form.is_not_disciplinary_probation ? 1 : 0,
    has_position: form.has_position ? 1 : 0,
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
        <span class="inline-block border-b border-black min-w-[200px] text-center">{{ form.certification_date }}</span>
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
          <span class="border-b border-black min-w-[300px] px-2 text-center inline-block">{{ form.student_name }}</span>
          <span class="absolute left-1/2 -translate-x-1/2 top-full text-xs font-bold mt-[-2px] whitespace-nowrap">student name</span>
        </span>
        <span>, a</span>
      </div>
      <div class="flex items-center flex-wrap mt-2">
        <span class="relative inline-block align-middle mx-2">
          <span class="border-b border-black min-w-[300px] px-2 text-center inline-block">{{ form.course_year_section }}</span>
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
        <span class="inline-block w-6 text-center">({{ form.is_bonafide ? '/' : ' ' }})</span> a bonafide student;
      </div>
      <div class="checkbox-item my-4">
        <span class="inline-block w-6 text-center">({{ form.is_not_academic_probation ? '/' : ' ' }})</span> not under academic probation;
      </div>
      <div class="checkbox-item my-4">
        <span class="inline-block w-6 text-center">({{ form.is_not_disciplinary_probation ? '/' : ' ' }})</span> not under disciplinary probation;
      </div>
      <div class="checkbox-item my-4">
        <span class="inline-block w-6 text-center">({{ form.has_position ? '/' : ' ' }})</span> position/rank in the organization
        <span class="border-b border-black min-w-[150px] inline-block text-center mx-2">{{ form.position_rank }}</span>;
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

    <!-- Form inputs -->
    <div class="mt-8 border-t pt-6">
        <h3 class="text-lg font-bold mb-4">Form Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold">Certification Date</label>
                <input type="date" v-model="form.certification_date" class="border p-2 w-full" required>
                <p v-if="errors.certification_date" class="text-red-500 text-sm mt-1">{{ errors.certification_date }}</p>
            </div>

            <div>
                <label class="block font-bold">Student Name</label>
                <input v-model="form.student_name" class="border p-2 w-full" required>
                <p v-if="errors.student_name" class="text-red-500 text-sm mt-1">{{ errors.student_name }}</p>
            </div>

            <div>
                <label class="block font-bold">Course/Year and Section</label>
                <input v-model="form.course_year_section" class="border p-2 w-full" required>
                <p v-if="errors.course_year_section" class="text-red-500 text-sm mt-1">{{ errors.course_year_section }}</p>
            </div>

            <div>
                <label class="block font-bold">Position/Rank in Organization</label>
                <input v-model="form.position_rank" class="border p-2 w-full">
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
            
            <div class="md:col-span-2">
                <label class="block font-bold">Student Status</label>
                <div class="flex flex-col gap-2 mt-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.is_bonafide" class="mr-2" required>
                        <span>Bonafide Student</span>
                    </label>
                    <p v-if="errors.is_bonafide" class="text-red-500 text-sm mt-1">{{ errors.is_bonafide }}</p>
                    
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.is_not_academic_probation" class="mr-2" required>
                        <span>Not Under Academic Probation</span>
                    </label>
                    <p v-if="errors.is_not_academic_probation" class="text-red-500 text-sm mt-1">{{ errors.is_not_academic_probation }}</p>
                    
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.is_not_disciplinary_probation" class="mr-2" required>
                        <span>Not Under Disciplinary Probation</span>
                    </label>
                    <p v-if="errors.is_not_disciplinary_probation" class="text-red-500 text-sm mt-1">{{ errors.is_not_disciplinary_probation }}</p>
                    
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.has_position" class="mr-2" required>
                        <span>Has Position/Rank in Organization</span>
                    </label>
                    <p v-if="errors.has_position" class="text-red-500 text-sm mt-1">{{ errors.has_position }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center">
            <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">
                {{ props.isEdit ? 'Update' : 'Submit' }}
            </button>
        </div>
    </div>

    <div class="footer mt-8 text-xs flex justify-between">
        <span>LSPU-OSAS-SF-006</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
    </div>
</div>
</template>