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
  coordinator_name: props.initialFormData.coordinator_name || '',
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
  
  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator Name is required';
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
  
  // Fixed: Use appropriate HTTP method for edit vs create
  const method = props.isEdit ? 'put' : 'post';
  const url = props.isEdit ? `/applications/${props.initialFormData.id}` : '/applications';
  
  form[method](url, {
    onSuccess: () => {
      alert(props.isEdit ? 'Form updated successfully!' : 'Form submitted successfully!');
      emit('submitted', form.data());
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors);
      // Set server validation errors
      errors.value = errors;
    }
  });
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
        <!-- Fixed: Use certification_date instead of formatted_date -->
        <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[200px] inline-block">{{ form.certification_date }}</span></p>
        <p class="mb-0">DATE</p>
    </div>

    <div class="section text-center mt-4">
        <p class="text-xl font-bold mb-4 underline">CERTIFICATION</p>
    </div>

    <div class="section mt-6">
        <p class="mb-4">This certifies that <span class="blank-line text-center border-b border-black min-w-[300px] inline-block">{{ form.student_name }}</span>, a 
        <span class="blank-line text-center border-b border-black min-w-[300px] inline-block">{{ form.course_year_section }}</span>,</p>
        
        <p class="mb-4">student of this College is:</p>
        
        <div class="mt-4">
            <p class="mb-2">
                <input type="checkbox" v-model="form.is_bonafide" class="mr-2">
                a bonafide student;
            </p>
            <p class="mb-2">
                <input type="checkbox" v-model="form.is_not_academic_probation" class="mr-2">
                not under academic probation;
            </p>
            <p class="mb-2">
                <input type="checkbox" v-model="form.is_not_disciplinary_probation" class="mr-2">
                not under disciplinary probation;
            </p>
            <p class="mb-2">
                <input type="checkbox" v-model="form.has_position" class="mr-2">
                position/rank in the organization <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.position_rank }}</span>;
            </p>
        </div>
    </div>

    <div class="section mt-6">
        <p class="mb-2">Noted:</p>
    </div>

    <div class="section mt-6">
        <div class="signature-line mt-6 mb-1 text-center">
            <!-- Fixed: Use adviser_name instead of faculty_adviser -->
            <p class="mb-0"><span class="text-center border-b border-black min-w-[250px] inline-block">{{ form.adviser_name }}</span></p>
            <p class="mb-1 text-center">Faculty Adviser(s)</p>
        </div>
        
        <div class="signature-line mt-6 mb-1 text-center">
            <p class="mb-0"><span class="text-center border-b border-black min-w-[250px] inline-block">{{ form.dean_name }}</span></p>
            <p class="mb-1 text-center">Dean/Assoc. Dean of College</p>
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
                <label class="block font-bold">Coordinator Name</label>
                <input v-model="form.coordinator_name" class="border p-2 w-full" required>
                <p v-if="errors.coordinator_name" class="text-red-500 text-sm mt-1">{{ errors.coordinator_name }}</p>
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