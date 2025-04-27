<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  initialFormData: {
    type: Object,
    default: () => ({})
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

  is_bonafide:props.initialFormData.is_bonafide || '',
  is_not_academic_probation:props.initialFormData.is_not_academic_probation || '',
  is_not_disciplinary_probation:props.initialFormData.is_not_disciplinary_probation || '',
  has_position:props.initialFormData.has_position || '',

  certification_date:props.initialFormData.certification_date || '',
  student_name:props.initialFormData.certification_date || '',
  course_year_section:props.initialFormData.course_year_section || '',
  

});

const submit = () => {
  form.post('/applications', {
    onSuccess: () => {
      alert('Form submitted successfully!');
      emit('submitted', form.data());
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors);
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
        <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[200px] inline-block">{{ form.formatted_date }}</span></p>
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
            <p class="mb-0"><span class="text-center border-b border-black min-w-[250px] inline-block">{{ form.faculty_adviser }}</span></p>
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
            </div>

            <div>
                <label class="block font-bold">Student Name</label>
                <input v-model="form.student_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Course/Year and Section</label>
                <input v-model="form.course_year_section" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Position/Rank in Organization</label>
                <input v-model="form.position_rank" class="border p-2 w-full">
            </div>

            <div>
                <label class="block font-bold">Faculty Adviser(s)</label>
                <input v-model="form.adviser_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Dean/Assoc. Dean Name</label>
                <input v-model="form.dean_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Coordinator Name</label>
                <input v-model="form.coordinator_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Organization Name</label>
                <input v-model="form.organization_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">President Name</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
            </div>
            
            <div class="md:col-span-2">
                <label class="block font-bold">Student Status</label>
                <div class="flex flex-col gap-2 mt-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.is_bonafide" class="mr-2">
                        <span>Bonafide Student</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.is_not_academic_probation" class="mr-2">
                        <span>Not Under Academic Probation</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.is_not_disciplinary_probation" class="mr-2">
                        <span>Not Under Disciplinary Probation</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" v-model="form.has_position" class="mr-2">
                        <span>Has Position/Rank in Organization</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center">
            <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
        </div>
    </div>

    <div class="footer mt-8 text-xs flex justify-between">
        <span>LSPU-OSAS-SF-006</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
    </div>
</div>
</template>