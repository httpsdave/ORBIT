<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const selectedForm = ref('');
const showForm = ref(false);

const formOptions = [
    { value: 'LSPU-OSAS-SF-001', label: 'Student Organization Recognition Form' },
    { value: 'LSPU-OSAS-SF-002', label: 'Renewal Form' },
    { value: 'LSPU-OSAS-SF-003', label: 'Commitment Form' },
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
    status: 'Pending',

    college: '',
    academic_year_start: '',
    academic_year_end: '',
    chairperson_name: '',

    // New fields for Commitment Form
    adviser_signature: '',
    adviser_college: '',
    adviser_rank: '',
    adviser_address: '',
    adviser_contact: '',
    form_date: '',

    
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
<div v-if="showForm && selectedForm === 'LSPU-OSAS-SF-001'" class="mt-6 form-content">
    <div class="header text-center">
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
            </div>

            <div>
                <label class="block font-bold">Application Date</label>
                <input type="date" v-model="form.application_date" class="border p-2 w-full" required>
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
                <label class="block font-bold">Director Name</label>
                <input v-model="form.director_name" class="border p-2 w-full" required>
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

        <!-- RENEWAL FORM TEMPLATE -->
<div v-if="showForm && selectedForm === 'LSPU-OSAS-SF-002'" class="mt-6 form-content">
    <div class="header text-center">
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
      
    <!-- COMMITMENT FORM -->
<div v-if="showForm && selectedForm === 'LSPU-OSAS-SF-003'" class="mt-6 form-content">
    <div class="header text-center">
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

            <div>
                <label class="block font-bold">Adviser Signature</label>
                <input v-model="form.adviser_signature" class="border p-2 w-full" required>
            </div>

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
