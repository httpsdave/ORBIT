<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const selectedForm = ref('');
const showForm = ref(false);

const formOptions = [
    { value: 'LSPU-OSAS-SF-001', label: 'Student Organization Recognition Form' },
    { value: 'LSPU-OSAS-SF-002', label: 'Renewal Form' },
    { value: 'LSPU-OSAS-SF-003', label: 'Commitment Form' },
    { value: 'LSPU-OSAS-SF-004', label: 'Plan of Activities' },
    { value: 'LSPU-OSAS-SF-005', label: 'List of Members' },
    { value: 'LSPU-OSAS-SF-006', label: 'Student Certification' }, 
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

    secretary_name: '',
    activities: [], // Array to store multiple activities


    // New fields for Certification Form
    
    student_name: '',
    course_year_section: '',
    position_rank: '',
    is_bonafide: false,
    is_not_academic_probation: false,
    is_not_disciplinary_probation: false,
    has_position: false,
    
    // New fields for List of   Members
    semester:'',
    second_adviser: '',
    members: [] // Array to store multiple members
});

// Add a function to add a new empty activity
const addActivity = () => {
    form.activities.push({
        objective: '',
        name: '',
        description: '',
        persons_involved: '',
        target_date: '',
        budget: 0
    });
};

// Add a function to remove an activity
const removeActivity = (index) => {
    form.activities.splice(index, 1);
};

// Add a function to add a new empty member
const addMember = () => {
    form.members.push({
        student_name: '',
        student_number: '',
        course_year_section: '',
        photo_path: null
    });
};

// Add a function to remove a member
const removeMember = (index) => {
    form.members.splice(index, 1);
};

const selectForm = () => {
    if (selectedForm.value) {
        form.form_type = selectedForm.value;
        showForm.value = true;
        
        // Add default activities for Plan of Activities form
        if (selectedForm.value === 'LSPU-OSAS-SF-004' && form.activities.length === 0) {
            // Add some empty rows
            for(let i = 0; i < 3; i++) {
                addActivity();
            }
        }
        
        // Add default members for List of Members form
        if (selectedForm.value === 'LSPU-OSAS-SF-005' && form.members.length === 0) {
            // Add some empty rows
            for(let i = 0; i < 4; i++) {
                addMember();
            }
        }
    }
};

const handlePhotoUpload = (event, index) => {
    const file = event.target.files[0];
    if (file) {
        // Create a temporary URL for preview in the form
        form.members[index].photo_preview = URL.createObjectURL(file);
        
        // Store the actual file for upload
        form.members[index].photo_path = file;  // Change from photo to photo_path to match blade template
    }
};

const submit = () => {
    form.post('/applications', {
        onSuccess: () => {
            alert('Form submitted successfully!');
            // Reset form or redirect
            showForm.value = false;
            selectedForm.value = '';
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
            // Display errors to user
        }
    });
};
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
      
    <!-- COMMITMENT FORM -->
<div v-if="showForm && selectedForm === 'LSPU-OSAS-SF-003'" class="mt-6 form-content">
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

        <!-- PLAN OF ACTIVITIES FORM -->
        <div v-if="showForm && selectedForm === 'LSPU-OSAS-SF-004'" class="mt-6 form-content">
            <div class="header text-center relative">
                <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
                <p class="text-sm font-bold mb-0">Republic of the Philippines</p>
                <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
                <p class="text-sm mb-0">Province of Laguna</p>
                <div style="margin-top: 15px; text-decoration: underline;">{{ form.organization_name }}</div>
                <p class="text-sm font-bold form-title mt-4 mb-4">PLAN OF ACTIVITIES</p>
                <p class="text-sm mb-0">Semester AY {{ form.academic_year_start }}-{{ form.academic_year_end }}</p>
            </div>

            <!-- Form inputs -->
            <div class="mt-8">
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
                        <label class="block font-bold">President Name</label>
                        <input v-model="form.president_name" class="border p-2 w-full" required>
                    </div>

                    <div>
                        <label class="block font-bold">Secretary Name</label>
                        <input v-model="form.secretary_name" class="border p-2 w-full">
                    </div>

                    <div>
                        <label class="block font-bold">Adviser Name</label>
                        <input v-model="form.adviser_name" class="border p-2 w-full">
                    </div>

                    <div>
                        <label class="block font-bold">Dean Name</label>
                        <input v-model="form.dean_name" class="border p-2 w-full">
                    </div>

                    <div>
                        <label class="block font-bold">Coordinator Name</label>
                        <input v-model="form.coordinator_name" class="border p-2 w-full">
                    </div>

                    <div>
                        <label class="block font-bold">Director Name</label>
                        <input v-model="form.director_name" class="border p-2 w-full">
                    </div>
                </div>

                <!-- Activities Table -->
                <div class="mt-6">
                    <h4 class="text-md font-bold mb-2">Activities</h4>
                    
                    <table class="w-full border-collapse border border-gray-300 mb-4">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 p-2">OBJECTIVE</th>
                                <th class="border border-gray-300 p-2">ACTIVITIES</th>
                                <th class="border border-gray-300 p-2">BRIEF DESCRIPTION</th>
                                <th class="border border-gray-300 p-2">PERSONS INVOLVED</th>
                                <th class="border border-gray-300 p-2">TARGET DATE</th>
                                <th class="border border-gray-300 p-2">BUDGET</th>
                                <th class="border border-gray-300 p-2">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(activity, index) in form.activities" :key="index">
                                <td class="border border-gray-300 p-2">
                                    <input v-model="activity.objective" class="w-full p-1" required>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input v-model="activity.name" class="w-full p-1" required>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <textarea v-model="activity.description" class="w-full p-1" rows="2" required></textarea>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input v-model="activity.persons_involved" class="w-full p-1" required>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input type="date" v-model="activity.target_date" class="w-full p-1" required>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <input type="number" v-model="activity.budget" step="0.01" min="0" class="w-full p-1" required>
                                </td>
                                <td class="border border-gray-300 p-2">
                                    <button type="button" @click="removeActivity(index)" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <button type="button" @click="addActivity" class="bg-blue-500 text-white px-3 py-1 rounded mb-4">
                        Add Activity Row
                    </button>
                </div>

                <div class="mt-6 text-center">
                    <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
                </div>
            </div>

            <div class="footer mt-8 text-xs flex justify-between">
                <span>LSPU-OSAS-SF-004</span>
                <span>Rev. 1</span>
                <span>09 November 2020</span>
            </div>
        </div>

        <!-- CERTIFICATION FORM -->
<div v-if="showForm && selectedForm === 'LSPU-OSAS-SF-006'" class="mt-6 form-content">
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

<!-- List of Members Form (Updated) -->
<div v-if="showForm && selectedForm === 'LSPU-OSAS-SF-005'" class="mt-6 form-content">
    <div class="header text-center relative">
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
        <p class="text-sm font-bold mb-0">Republic of the Philippines</p>
        <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
        <p class="text-sm mb-0">Province of Laguna</p>
        <p class="text-sm font-bold mb-0">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
        <p class="text-sm font-bold mt-4 mb-0">List of Members</p>
    </div>

    <div class="semester-section text-center mt-4">
        <p class="mb-0">
            <select v-model="form.semester" class="border p-1 mr-1">
                <option value="">--</option>
                <option value="1st">1st</option>
                <option value="2nd">2nd</option>
                <option value="Summer">Summer</option>
            </select> 
            Sem. / AY 
            <input v-model="form.academic_year_start" type="text" class="border p-1 w-16 mx-1" placeholder="20__">-
            <input v-model="form.academic_year_end" type="text" class="border p-1 w-16 mx-1" placeholder="20__">
        </p>
    </div>

    <div class="section text-center mt-4">
        <p class="mb-0">Name of Organization: <span class="signature-line border-b border-black min-w-[250px] inline-block text-center">{{ form.organization_name }}</span></p>
    </div>

    <!-- Member list preview -->
    <div class="member-section mt-6">
        <div v-for="(pair, pairIndex) in Array.from({ length: Math.ceil(form.members.length / 2) })" :key="pairIndex" class="flex mt-4 mb-8">
            <div v-for="(_, offset) in [0, 1]" :key="offset" class="w-1/2 flex" 
                v-if="pairIndex * 2 + offset < form.members.length">
                <div class="photo-box border border-black w-[70px] h-[70px] flex items-center justify-center mr-2 text-xs">
                    <img v-if="form.members[pairIndex * 2 + offset].photo_path && typeof form.members[pairIndex * 2 + offset].photo_path === 'object'" 
                        :src="URL.createObjectURL(form.members[pairIndex * 2 + offset].photo_path)" 
                        alt="Member Photo" class="w-[68px] h-[68px]">
                    <span v-else>1 x 1 PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-between">
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">
                        {{ form.members[pairIndex * 2 + offset].student_name || '' }}
                    </div>
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">
                        ({{ form.members[pairIndex * 2 + offset].student_number || '' }})
                    </div>
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">
                        ({{ form.members[pairIndex * 2 + offset].course_year_section || '' }})
                    </div>
                </div>
            </div>
            <div v-if="(pairIndex * 2 + 1) >= form.members.length" class="w-1/2 flex">
                <div class="photo-box border border-black w-[70px] h-[70px] flex items-center justify-center mr-2 text-xs">
                    1 x 1 PICTURE
                </div>
                <div class="member-info flex-1 flex flex-col justify-between">
                    <div class="member-line border-b border-black mb-1 min-h-[20px]"></div>
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">(Student Number)</div>
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">(Course - Year Section)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="signature-section flex justify-between mt-10">
        <div class="signature text-left">
            <p class="mb-0"><span class="signature-line border-b border-black min-w-[200px] inline-block text-center">{{ form.adviser_name }}</span></p>
            <p class="mb-0">Faculty Adviser</p>
            <p class="mb-0">Date: <span class="signature-line border-b border-black min-w-[150px] inline-block text-center">{{ currentDate }}</span></p>
        </div>

        <div class="signature text-right">
            <p class="mb-0"><span class="signature-line border-b border-black min-w-[200px] inline-block text-center">{{ form.second_adviser }}</span></p>
            <p class="mb-0">Faculty Adviser</p>
            <p class="mb-0">Date: <span class="signature-line border-b border-black min-w-[150px] inline-block text-center">{{ currentDate }}</span></p>
        </div>
    </div>

    <div class="section text-center mt-10">
        <p class="mb-1"><strong>Noted:</strong></p>
        <div class="signature text-center">
            <p class="mb-0"><span class="signature-line border-b border-black min-w-[250px] inline-block text-center">{{ form.dean_name }}</span></p>
            <p class="mb-0">Dean/Assoc. Dean of College</p>
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
                <label class="block font-bold">President Name</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Coordinator Name</label>
                <input v-model="form.coordinator_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Semester</label>
                <select v-model="form.semester" class="border p-2 w-full" required>
                    <option value="">-- Select Semester --</option>
                    <option value="1st">1st Semester</option>
                    <option value="2nd">2nd Semester</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>

            <div>
                <label class="block font-bold">Academic Year Start</label>
                <input v-model="form.academic_year_start" class="border p-2 w-full" placeholder="20__" required>
            </div>

            <div>
                <label class="block font-bold">Academic Year End</label>
                <input v-model="form.academic_year_end" class="border p-2 w-full" placeholder="20__" required>
            </div>

            <div>
                <label class="block font-bold">Faculty Adviser Name</label>
                <input v-model="form.adviser_name" class="border p-2 w-full" required>
            </div>

            <div>
                <label class="block font-bold">Second Faculty Adviser Name (Optional)</label>
                <input v-model="form.second_adviser" class="border p-2 w-full">
            </div>

            <div>
                <label class="block font-bold">Dean/Assoc. Dean Name</label>
                <input v-model="form.dean_name" class="border p-2 w-full" required>
            </div>
        </div>

        <!-- Member List Management -->
        <div class="mt-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold">Members</h3>
                <button @click="addMember" type="button" class="bg-blue-500 text-white px-3 py-1 rounded">
                    Add Member
                </button>
            </div>

            <div v-for="(member, index) in form.members" :key="index" class="mt-4 p-4 border rounded">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-bold">Member #{{ index + 1 }}</h4>
                    <button @click="removeMember(index)" type="button" class="text-red-500">
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold">Student Name</label>
                        <input v-model="member.student_name" class="border p-2 w-full" required>
                    </div>

                    <div>
                        <label class="block font-bold">Student Number</label>
                        <input v-model="member.student_number" class="border p-2 w-full" required>
                    </div>

                    <div>
                        <label class="block font-bold">Course - Year & Section</label>
                        <input v-model="member.course_year_section" class="border p-2 w-full" required>
                    </div>

                    <div>
                        <label class="block font-bold">1x1 Photo</label>
                        <input type="file" @change="event => handlePhotoUpload(event, index)" class="border p-2 w-full" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center">
            <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
        </div>
    </div>

    <div class="footer mt-8 text-xs flex justify-between">
        <span>LSPU-OSAS-SF-005</span>
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
