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
  form_type: 'LSPU-OSAS-SF-002',
  president_name:props.initialFormData.president_name || '',
  organization_name:props.initialFormData.organization_name || '',
  adviser_name:props.initialFormData.adviser_name || '',
  dean_name:props.initialFormData.dean_name || '',
  coordinator_name:props.initialFormData.coordinator_name || '',
  chairperson_name:props.initialFormData.chairperson_name || '',

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
        <p class="text-sm font-bold form-title mt-4 mb-4">RENEWAL FORM</p>
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
        <p class="mb-1">Sir:</p>
        
        <p class="indented">The <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.organization_name }}</span> wishes to seek renewal of its recognition to function as a Student Organization in the College of <span class="blank-line text-center border-b border-black min-w-[200px] inline-block">{{ form.college }}</span> for Academic Year 20<span class="blank-line text-center border-b border-black inline-block" style="min-width: 50px">{{ form.academic_year_start }}</span>-20<span class="blank-line text-center border-b border-black inline-block" style="min-width: 50px">{{ form.academic_year_end }}</span>.</p>
        
        <p class="indented">In this connection, we respectfully request your good office to grant us permission to operate in our institution, subject to the existing rules & regulation of our University.</p>
        
        <p class="indented">Thank you very much.</p>
    </div>

    <div class="section text-right">
        <p class="mb-1">Very respectfully yours,</p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.president_name }}</span></p>
            <p class="mb-0">Organization President</p>
        </div>
    </div>

    <div class="section text-center">
        <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.organization_name }}</span></p>
        <p class="mb-0">Name of Organization</p>
    </div>

    <div class="section text-left">
        <p class="mb-1">Noted:</p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.adviser_name }}</span></p>
            <p class="mb-0">Adviser's Student Organization</p>
        </div>
    </div>

    <div class="section text-right">
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.dean_name }}</span></p>
            <p class="mb-0">Dean/Assoc. Dean of College</p>
        </div>
    </div>

    <div class="section text-center">
        <p class="mb-1">Recommending Approval:</p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.coordinator_name }}</span></p>
            <p class="mb-0">Coordinator, Student Organization Unit</p>
        </div>
    </div>

    <div class="section text-center">
        <p class="mb-1">Approved / Disapproved:</p>
        <div class="signature">
            <p class="mb-0"><span class="signature-line text-center border-b border-black min-w-[250px] inline-block">{{ form.chairperson_name }}</span></p>
            <p class="mb-0">Chairperson, Office of Student Affairs and Services</p>
        </div>
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
                <label class="block font-bold">College</label>
                <input v-model="form.college" class="border p-2 w-full" required>
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
                <label class="block font-bold">President Name</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Adviser Name</label>
                <input v-model="form.adviser_name" class="border p-2 w-full" required>
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
                <label class="block font-bold">Chairperson Name</label>
                <input v-model="form.chairperson_name" class="border p-2 w-full" required>
            </div>
        </div>

        <div class="mt-6 text-center">
            <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
        </div>
    </div>

    <div class="footer mt-8 text-xs flex justify-between">
        <span>LSPU-OSAS-SF-002</span>
        <span>Rev. 1</span>
        <span>09 November 2020</span>
    </div>
</div>

</template>
