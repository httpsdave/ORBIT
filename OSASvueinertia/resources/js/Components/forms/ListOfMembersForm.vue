<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  initialFormData: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['submitted']);

// Add a function to add a new empty member
const addMember = () => {
    form.members.push({
        student_name: '',
        student_number: '',
        course_year_section: '',
        photo_path: null,
        photo_preview: null
    });
};

// Add a function to remove a member
const removeMember = (index) => {
    // Clean up object URL if it exists
    if (form.members[index].photo_preview) {
        URL.revokeObjectURL(form.members[index].photo_preview);
    }
    form.members.splice(index, 1);
};

// Current date computed property
const currentDate = computed(() => {
    const today = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return today.toLocaleDateString('en-US', options);
});

const form = useForm({
  form_type: 'LSPU-OSAS-SF-005',
 
  organization_name: props.initialFormData.organization_name || '',
  academic_year_start: props.initialFormData.academic_year_start || '',
  academic_year_end: props.initialFormData.academic_year_end || '',
  semester: props.initialFormData.semester || '',
  members: [],
  
  president_name: props.initialFormData.president_name || '',
  secretary_name: props.initialFormData.secretary_name || '',
  application_date: props.initialFormData.application_date || '',
  adviser_name: props.initialFormData.adviser_name || '',
  second_adviser: props.initialFormData.second_adviser || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
});

const handlePhotoUpload = (event, index, type = 'members') => {
    const file = event.target.files[0];
    if (file) {
        if (type === 'members') {
            // Clean up previous object URL if it exists
            if (form.members[index].photo_preview) {
                URL.revokeObjectURL(form.members[index].photo_preview);
            }
            // Create a temporary URL for preview in the form
            form.members[index].photo_preview = URL.createObjectURL(file);
            // Store the actual file for upload
            form.members[index].photo_path = file;
        }
    }
};

// Helper function to get photo preview URL
const getPhotoPreview = (member) => {
    if (member.photo_preview) {
        return member.photo_preview;
    }
    if (member.photo_path && typeof member.photo_path === 'object') {
        return URL.createObjectURL(member.photo_path);
    }
    return null;
};

// Initialize with data from props if available
if (props.initialFormData?.members && props.initialFormData.members.length > 0) {
  // Copy members from initialFormData
  form.members = [...props.initialFormData.members.map(member => ({
    ...member,
    photo_preview: null
  }))];
} else {
  // Add default empty members
  for(let i = 0; i < 4; i++) {
    addMember();
  }
}

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
            <!-- Left member -->
            <div v-if="pairIndex * 2 < form.members.length" class="w-1/2 flex">
                <div class="photo-box border border-black w-[70px] h-[70px] flex items-center justify-center mr-2 text-xs">
                    <img v-if="getPhotoPreview(form.members[pairIndex * 2])" 
                        :src="getPhotoPreview(form.members[pairIndex * 2])" 
                        alt="Member Photo" 
                        class="w-[68px] h-[68px] object-cover">
                    <span v-else>1 x 1 PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-between">
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">
                        {{ form.members[pairIndex * 2].student_name || '' }}
                    </div>
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">
                        ({{ form.members[pairIndex * 2].student_number || '' }})
                    </div>
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">
                        ({{ form.members[pairIndex * 2].course_year_section || '' }})
                    </div>
                </div>
            </div>
            
            <!-- Right member -->
            <div v-if="pairIndex * 2 + 1 < form.members.length" class="w-1/2 flex">
                <div class="photo-box border border-black w-[70px] h-[70px] flex items-center justify-center mr-2 text-xs">
                    <img v-if="getPhotoPreview(form.members[pairIndex * 2 + 1])" 
                        :src="getPhotoPreview(form.members[pairIndex * 2 + 1])" 
                        alt="Member Photo" 
                        class="w-[68px] h-[68px] object-cover">
                    <span v-else>1 x 1 PICTURE</span>
                </div>
                <div class="member-info flex-1 flex flex-col justify-between">
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">
                        {{ form.members[pairIndex * 2 + 1].student_name || '' }}
                    </div>
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">
                        ({{ form.members[pairIndex * 2 + 1].student_number || '' }})
                    </div>
                    <div class="member-line border-b border-black mb-1 min-h-[20px]">
                        ({{ form.members[pairIndex * 2 + 1].course_year_section || '' }})
                    </div>
                </div>
            </div>
            
            <!-- Empty placeholder for right side if odd number of members -->
            <div v-else-if="pairIndex * 2 < form.members.length" class="w-1/2 flex">
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
                        <div v-if="getPhotoPreview(member)" class="mt-2">
                            <img :src="getPhotoPreview(member)" alt="Preview" class="w-16 h-16 object-cover border">
                        </div>
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

</template>