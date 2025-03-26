<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const selectedForm = ref('');
const showForm = ref(false);

const formOptions = [
    { value: 'LSPU-OSAS-SF-001', label: 'Student Organization Recognition Form' },
    { value: 'LSPU-OSAS-SF-002', label: 'Event Proposal Form' },
    { value: 'LSPU-OSAS-SF-003', label: 'Budget Request Form' },
];

const form = useForm({
    form_type: '',
    organization_name: '',
    president_name: '',
    application_date: '',
    adviser_name: '',
    dean_name: '',
    coordinator_name: '',
    director_name: '',
    status: 'Pending'
});

const selectForm = () => {
    if (selectedForm.value) {
        form.form_type = selectedForm.value;
        showForm.value = true;
    }
};

const submit = () => form.post('/applications');
</script>

<template>
    <div class="p-6 document">
        <!-- Select Form Type -->
        <h1 class="text-xl font-bold text-center">Select a Form to Fill</h1>
        <div class="mt-4">
            <label class="block font-bold">Choose Form</label>
            <select v-model="selectedForm" @change="selectForm" class="border p-2 w-full">
                <option value="">-- Select Form --</option>
                <option v-for="option in formOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
        </div>

        <!-- Form Fields (Visible Only After Selection) -->
        <div v-if="showForm" class="mt-6 form-content">
            <h1 class="text-xl font-bold text-center">Republic of the Philippines</h1>
            <h2 class="text-lg text-center">Laguna State Polytechnic University</h2>
            <h3 class="text-md text-center font-semibold">Province of Laguna</h3>
            <h3 class="text-md text-center font-semibold">OFFICE OF STUDENT AFFAIRS AND SERVICES</h3>

            <div class="mt-6 right-align">
                <p>____________________</p>
                <label class="block font-bold">Date</label>
                <input type="date" v-model="form.application_date" class="border p-2 w-full" required>
            </div>

            <div class="mt-4">
                <p>The Director/Chairperson<br>Office of Student Affairs and Services<br>LSPU</p>
            </div>

            <div class="mt-4">
                <p>Sir/Madam,</p>
                <p>I have the honor to apply for recognition/renewal of 
                    <input v-model="form.organization_name" class="border p-2 w-full" required>, 
                    a duly recognized student organization in this University.</p>
            </div>

            <div class="mt-4">
                <p>In compliance with CHED Memo Order #9 s. 2013, Subj.: Enhanced Policies & Guidelines on Student Affairs and Services (Article VIII - Student Development, Section 19. Student Organizations and Activities), I am submitting for proper action the following requirements for recognition and accreditation:</p>
                <ul class="list-disc pl-6">
                    <li>Letter for application for recognition (4 copies)</li>
                    <li>Constitution and By-Laws of the Organization (4 copies)</li>
                    <li>Program of activities for one (1) year (4 copies)</li>
                    <li>List of officers with signature, student I.D. Nos. and attached 2x2 I.D. picture (4 copies)</li>
                    <li>List of members with signature, student I.D. number and attached 1x1 ID picture (4 copies)</li>
                    <li>Accomplishment report (for renewal of accreditation) (4 copies)</li>
                </ul>
            </div>

            <div class="mt-4">
                <p>It is understood that the provision to the LSPU Supplementary Rules and Regulations Governing Student Organization in this official recognition is good only for one (1) school year, subject to renewal unless revoked prior to this expiration.</p>
            </div>

            <div class="mt-6 text-right">
                <p>Respectfully yours,</p>
                <p>________________________</p>
                <label class="block font-bold">Organization President</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
            </div>

            <div class="mt-6">
                <p><strong>Noted:</strong></p>
                <p>________________________</p>
                <label class="block font-bold">Adviser, Student Organization</label>
                <input v-model="form.adviser_name" class="border p-2 w-full">
            </div>

            <div class="mt-6 text-right">
                <p>________________________</p>
                <label class="block font-bold">Dean/Associate Dean</label>
                <input v-model="form.dean_name" class="border p-2 w-full">
            </div>

            <div class="mt-6 text-center">
                <p><strong>Recommending Approval:</strong></p>
                <p>________________________</p>
                <label class="block font-bold">Coordinator, Student Organization Unit</label>
                <input v-model="form.coordinator_name" class="border p-2 w-full">
            </div>

            <div class="mt-6 text-center">
                <p><strong>Approved/Disapproved:</strong></p>
                <p>________________________</p>
                <label class="block font-bold">Director, Office of Student Affairs and Services</label>
                <input v-model="form.director_name" class="border p-2 w-full">
            </div>

            <div class="mt-6 text-center">
                <p>LSPU-OSAS-SF-001 Rev.1 09 November 2020</p>
            </div>

            <div class="mt-6 text-center">
                <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
            </div>
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
