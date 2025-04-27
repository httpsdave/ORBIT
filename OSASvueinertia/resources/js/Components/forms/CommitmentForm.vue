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
  form_type: 'LSPU-OSAS-SF-003',
  organization_name: props.initialFormData.organization_name || '',
  president_name: props.initialFormData.president_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  adviser_college:props.initialFormData.adviser_college || '',
  adviser_rank:props.initialFormData.adviser_rank || '',
  adviser_address:props.initialFormData.adviser_address || '',
  adviser_contact:props.initialFormData.adviser_contact || '',
  form_date:props.initialFormData.form_date || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
  
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
        <p class="text-sm font-bold form-title mt-4 mb-4">COMMITMENT FORM</p>
    </div>

    <div class="section text-left">
        <p class="mb-0"><strong>THE DIRECTOR/CHAIRPERSON</strong></p>
        <p class="mb-0">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
        <p class="mb-0">LSPU</p>
    </div>

    <div class="section">
        <p class="thru-line text-center italic my-2">Thru: The Coordinator, Student Organization Unit</p>
    </div>

    <div class="section">
        <p class="mb-1">Sir,</p>
        
        <p class="indented">This letter is in connection with the application for recognition of 
        <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.organization_name }}</span> as a LSPU Student Organization.</p>
        
        <p class="indented">I, the undersigned, have committed to serve as the organizations Faculty 
        Adviser for the academic year 20<span class="blank-line text-center border-b border-black inline-block" style="min-width: 50px">{{ form.academic_year_start }}</span>-20<span class="blank-line text-center border-b border-black inline-block" style="min-width: 50px">{{ form.academic_year_end }}</span>, and will therefore assume full responsibility as 
        provided in the guidelines for the recognition of student organizations.</p>
        
        <p class="indented">Furthermore, I certify to the correctness and completeness of the documents 
        attached to the organization application for recognition.</p>
    </div>

    <div class="section">
        <p class="mb-1">Very respectfully yours,</p>
    </div>

    <div class="section mt-4">
        <p class="mb-1">Name: <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.adviser_name }}</span></p>
        <p class="mb-1">Signature: <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.adviser_signature }}</span></p>
        <p class="mb-1">College: <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.adviser_college }}</span></p>
        <p class="mb-1">Academic Rank: <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.adviser_rank }}</span></p>
        <p class="mb-1">Home Address: <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.adviser_address }}</span></p>
        <p class="mb-1">Contact Number(s): <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.adviser_contact }}</span></p>
        <p class="mb-1">Date: <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.form_date }}</span></p>
    </div>

    <div class="section mt-4">
        <p class="mb-1">Noted:</p>
        <p class="mb-0"><span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.dean_name }}</span></p>
        <p class="mb-0">Dean/Assoc. Dean of College</p>
    </div>

    <div class="section mt-4">
        <p class="mb-1">Recommending Approval:</p>
        <p class="mb-0"><span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.coordinator_name }}</span></p>
        <p class="mb-0">Coordinator, Student Organization Unit</p>
    </div>

    <div class="section mt-4">
        <p class="mb-1">Approved / Disapproved:</p>
        <p class="mb-0"><span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.director_name }}</span></p>
        <p class="mb-0">Director, Office of Student Affairs and Services</p>
    </div>

    <!-- Form inputs -->
    <div class="mt-8 border-t pt-6">
        <h3 class="text-lg font-bold mb-4">Form Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold">Organization Name</label>
                <input v-model="form.organization_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Academic Year Start</label>
                <input v-model="form.academic_year_start" class="border p-2 w-full" required placeholder="23">
            </div>

            <div>
                <label class="block font-bold">Academic Year End</label>
                <input v-model="form.academic_year_end" class="border p-2 w-full" required placeholder="24">
            </div>

            <div>
                <label class="block font-bold">Adviser Name</label>
                <input v-model="form.adviser_name" class="border p-2 w-full" required>
            </div>

           <!-- <div>
                <label class="block font-bold">Adviser Signature</label>
                <input v-model="form.adviser_signature" class="border p-2 w-full" required>
            </div>-->

            <div>
                <label class="block font-bold">Adviser College</label>
                <input v-model="form.adviser_college" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Academic Rank</label>
                <input v-model="form.adviser_rank" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Home Address</label>
                <input v-model="form.adviser_address" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Contact Number(s)</label>
                <input v-model="form.adviser_contact" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Form Date</label>
                <input type="date" v-model="form.form_date" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Dean Name</label>
                <input v-model="form.dean_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Coordinator Name</label>
                <input v-model="form.coordinator_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Director Name</label>
                <input v-model="form.director_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">President Name</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
            </div>
        </div>

        <div class="mt-6 text-center">
            <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
        </div>
    </div>

    <div class="footer mt-8 text-xs flex justify-between">
        <span>LSPU-OSAS-SF-003</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
    </div>
</div>

</template>