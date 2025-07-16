<script setup>
import { ref, computed } from 'vue';
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

// Add errors ref object
const errors = ref({});

// Add pagination state
const currentPage = ref(1);
const membersPerPage = 10;

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
    // Prevent removing the last member
    if (form.members.length <= 1) {
        return;
    }
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

// Pagination computed properties
const totalPages = computed(() => Math.ceil(form.members.length / membersPerPage));
const startIndex = computed(() => (currentPage.value - 1) * membersPerPage);
const endIndex = computed(() => Math.min(startIndex.value + membersPerPage, form.members.length));
const currentPageMembers = computed(() => {
    return form.members.slice(startIndex.value, endIndex.value);
});

// Add computed for current page's member input forms
const currentPageMemberInputs = computed(() => {
    return form.members.slice(startIndex.value, endIndex.value);
});

// Navigation functions
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

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
    // If photo_path is a string (already saved), return the storage URL
    if (member.photo_path && typeof member.photo_path === 'string') {
        return `/storage/${member.photo_path}`;
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

// Validation function
const validateForm = () => {
  errors.value = {};
  
  // Validate main form fields
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization name is required';
  }
  
  if (!form.president_name.trim()) {
    errors.value.president_name = 'President name is required';
  }
  
  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator name is required';
  }
  
  if (!form.semester.trim()) {
    errors.value.semester = 'Semester is required';
  }
  
  if (!form.academic_year_start.trim()) {
    errors.value.academic_year_start = 'Academic year start is required';
  }
  
  if (!form.academic_year_end.trim()) {
    errors.value.academic_year_end = 'Academic year end is required';
  }
  
  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Faculty adviser name is required';
  }
  
  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean/Assoc. Dean name is required';
  }
  
  // Validate members
  form.members.forEach((member, index) => {
    if (!member.student_name.trim()) {
      errors.value[`member_${index}_name`] = 'Student name is required';
    }
    
    if (!member.student_number.trim()) {
      errors.value[`member_${index}_number`] = 'Student number is required';
    }
    
    if (!member.course_year_section.trim()) {
      errors.value[`member_${index}_course`] = 'Course - Year & Section is required';
    }
  });
  
  // Return true if no errors
  return Object.keys(errors.value).length === 0;
};

const submit = () => {
  if (!validateForm()) {
    return;
  }
  
  // Check if we're in edit mode
  if (props.isEdit) {
    // For edit mode, just emit the data - don't make HTTP request here
    emit('submitted', form.data());
  } else {
    // For create mode, make the POST request
    form.post('/applications', {
      onSuccess: () => {
        alert('Form submitted successfully!');
        emit('submitted', form.data());
      },
      onError: (errors) => {
        console.error('Form submission errors:', errors);
      }
    });
  }
};
</script>

<template>
  <div class="mt-6 form-content">
    <div class="header text-center relative">
        <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
        <p class="text-sm font-normal mb-0">Republic of the Philippines</p>
        <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
        <p class="text-sm mb-0">Province of Laguna</p>
        <p class="text-sm font-bold mb-0 mt-3">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
        <p class="text-sm font-bold mt-2 mb-0">List of Members</p>
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

    <!-- Member list preview with pagination -->
    <div class="member-section mt-6">
        <!-- Page header for additional pages -->
        <div v-if="currentPage > 1" class="page-break-header text-center mt-8 pt-8 border-t-2">
            <p class="text-sm font-normal mb-0">Republic of the Philippines</p>
            <p class="text-base font-bold university-name mb-0">Laguna State Polytechnic University</p>
            <p class="text-sm mb-0">Province of Laguna</p>
            <p class="text-sm font-bold mb-0 mt-3">OFFICE OF STUDENT AFFAIRS AND SERVICES</p>
            <p class="text-sm font-bold mt-2 mb-0">List of Members</p>
            <div class="semester-section text-center mt-4">
                <p class="mb-0">
                    <span class="border p-1 mr-1">{{ form.semester || '--' }}</span> 
                    Sem. / AY 
                    <span class="border p-1 w-16 mx-1 inline-block">{{ form.academic_year_start || '20__' }}</span>-
                    <span class="border p-1 w-16 mx-1 inline-block">{{ form.academic_year_end || '20__' }}</span>
                </p>
            </div>
            <div class="section text-center mt-4">
                <p class="mb-0">Name of Organization: <span class="signature-line border-b border-black min-w-[250px] inline-block text-center">{{ form.organization_name }}</span></p>
            </div>
        </div>

        <!-- Members for current page (10 per page: 5 rows × 2 columns) -->
        <div class="flex mt-4">
            <!-- Left Column -->
            <div class="w-1/2 pr-4">
                <div v-for="rowIndex in 5" :key="`left-${rowIndex}`" class="flex mt-4 mb-12">
                    <div v-if="startIndex + (rowIndex - 1) < form.members.length" class="w-full flex">
                        <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center mr-3 text-xs flex-shrink-0">
                            <img v-if="getPhotoPreview(form.members[startIndex + (rowIndex - 1)])" 
                                :src="getPhotoPreview(form.members[startIndex + (rowIndex - 1)])" 
                                alt="Member Photo" 
                                class="w-[94px] h-[94px] object-cover">
                            <span v-else class="text-center leading-tight">1 x 1<br>PICTURE</span>
                        </div>
                        <div class="member-info flex-1 flex flex-col justify-center text-center">
                            <div class="member-line mb-1 min-h-[20px] py-1">
                                {{ form.members[startIndex + (rowIndex - 1)].student_name || '' }}
                            </div>
                            <div class="member-line mb-1 min-h-[20px] py-1">
                                {{ form.members[startIndex + (rowIndex - 1)].student_number || '' }}
                            </div>
                            <div class="member-line mb-1 min-h-[20px] py-1">
                                {{ form.members[startIndex + (rowIndex - 1)].course_year_section || '' }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="w-full flex">
                        <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center mr-3 text-xs flex-shrink-0">
                            <span class="text-center leading-tight">1 x 1<br>PICTURE</span>
                        </div>
                        <div class="member-info flex-1 flex flex-col justify-center text-center">
                            <div class="member-line mb-1 min-h-[20px] py-1"></div>
                            <div class="member-line mb-1 min-h-[20px] py-1">Student Number</div>
                            <div class="member-line mb-1 min-h-[20px] py-1">Course - Year Section</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="w-1/2 pl-4">
                <div v-for="rowIndex in 5" :key="`right-${rowIndex}`" class="flex mt-4 mb-12">
                    <div v-if="startIndex + (rowIndex - 1) + 5 < form.members.length" class="w-full flex">
                        <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center mr-3 text-xs flex-shrink-0">
                            <img v-if="getPhotoPreview(form.members[startIndex + (rowIndex - 1) + 5])" 
                                :src="getPhotoPreview(form.members[startIndex + (rowIndex - 1) + 5])" 
                                alt="Member Photo" 
                                class="w-[94px] h-[94px] object-cover">
                            <span v-else class="text-center leading-tight">1 x 1<br>PICTURE</span>
                        </div>
                        <div class="member-info flex-1 flex flex-col justify-center text-center">
                            <div class="member-line mb-1 min-h-[20px] py-1">
                                {{ form.members[startIndex + (rowIndex - 1) + 5].student_name || '' }}
                            </div>
                            <div class="member-line mb-1 min-h-[20px] py-1">
                                {{ form.members[startIndex + (rowIndex - 1) + 5].student_number || '' }}
                            </div>
                            <div class="member-line mb-1 min-h-[20px] py-1">
                                {{ form.members[startIndex + (rowIndex - 1) + 5].course_year_section || '' }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="w-full flex">
                        <div class="photo-box border border-black w-[96px] h-[96px] flex items-center justify-center mr-3 text-xs flex-shrink-0">
                            <span class="text-center leading-tight">1 x 1<br>PICTURE</span>
                        </div>
                        <div class="member-info flex-1 flex flex-col justify-center text-center">
                            <div class="member-line mb-1 min-h-[20px] py-1"></div>
                            <div class="member-line mb-1 min-h-[20px] py-1">Student Number</div>
                            <div class="member-line mb-1 min-h-[20px] py-1">Course - Year Section</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signatures only on the last page -->
        <div v-if="currentPage === totalPages && form.members.length > 0" class="signature-section flex justify-between mt-10">
            <div class="signature w-1/2 text-center">
                <p class="mb-0">
                    <span class="signature-line border-b border-black min-w-[200px] inline-block text-center">{{ form.adviser_name }}</span>
                </p>
                <p class="mb-0 font-bold">Faculty Adviser</p>
                <p class="mb-0">
                    <span>Date:</span>
                    <span class="signature-line border-b border-black min-w-[150px] inline-block text-center ml-2">{{ currentDate }}</span>
                </p>
            </div>
            <div class="signature w-1/2 text-center">
                <p class="mb-0">
                    <span class="signature-line border-b border-black min-w-[200px] inline-block text-center">{{ form.second_adviser }}</span>
                </p>
                <p class="mb-0 font-bold">Faculty Adviser</p>
                <p class="mb-0">
                    <span>Date:</span>
                    <span class="signature-line border-b border-black min-w-[200px] inline-block text-center ml-2">{{ currentDate }}</span>
                </p>
            </div>
        </div>

        <div v-if="currentPage === totalPages && form.members.length > 0" class="section text-center mt-10">
            <p class="mb-1"><strong>Noted:</strong></p>
            <div class="signature text-center">
                <p class="mb-0"><span class="signature-line border-b border-black min-w-[250px] inline-block text-center">{{ form.dean_name }}</span></p>
                <p class="mb-0">Dean/Assoc. Dean of College</p>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mt-8 gap-4">
            <button 
                @click="prevPage" 
                :disabled="currentPage === 1"
                class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
                Previous
            </button>
            
            <div class="flex gap-2">
                <button 
                    v-for="page in totalPages" 
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                        'px-3 py-1 rounded',
                        currentPage === page 
                            ? 'bg-blue-600 text-white' 
                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                    ]">
                    {{ page }}
                </button>
            </div>
            
            <button 
                @click="nextPage" 
                :disabled="currentPage === totalPages"
                class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
                Next
            </button>
        </div>

        <!-- Page Info -->
        <div v-if="totalPages > 1" class="text-center mt-4 text-sm text-gray-600">
            Page {{ currentPage }} of {{ totalPages }} 
            (Showing members {{ startIndex + 1 }}-{{ endIndex }} of {{ form.members.length }})
        </div>
    </div>



    <!-- Form inputs -->
    <div class="mt-8 border-t pt-6">
        <h3 class="text-lg font-bold mb-4">Form Details</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-bold">Organization Name</label>
                <input v-model="form.organization_name" class="border p-2 w-full" required>
                <div v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</div>
            </div>

            <div>
                <label class="block font-bold">President Name</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
                <div v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</div>
            </div>

            <div>
                <label class="block font-bold">Coordinator Name</label>
                <input v-model="form.coordinator_name" class="border p-2 w-full" required>
                <div v-if="errors.coordinator_name" class="text-red-500 text-sm mt-1">{{ errors.coordinator_name }}</div>
            </div>

            <div>
                <label class="block font-bold">Semester</label>
                <select v-model="form.semester" class="border p-2 w-full" required>
                    <option value="">-- Select Semester --</option>
                    <option value="1st">1st Semester</option>
                    <option value="2nd">2nd Semester</option>
                    <option value="Summer">Summer</option>
                </select>
                <div v-if="errors.semester" class="text-red-500 text-sm mt-1">{{ errors.semester }}</div>
            </div>

            <div>
                <label class="block font-bold">Academic Year Start</label>
                <input v-model="form.academic_year_start" class="border p-2 w-full" placeholder="20__" required>
                <div v-if="errors.academic_year_start" class="text-red-500 text-sm mt-1">{{ errors.academic_year_start }}</div>
            </div>

            <div>
                <label class="block font-bold">Academic Year End</label>
                <input v-model="form.academic_year_end" class="border p-2 w-full" placeholder="20__" required>
                <div v-if="errors.academic_year_end" class="text-red-500 text-sm mt-1">{{ errors.academic_year_end }}</div>
            </div>

            <div>
                <label class="block font-bold">Faculty Adviser Name</label>
                <input v-model="form.adviser_name" class="border p-2 w-full" required>
                <div v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</div>
            </div>

            <div>
                <label class="block font-bold">Second Faculty Adviser Name (Optional)</label>
                <input v-model="form.second_adviser" class="border p-2 w-full">
            </div>

            <div>
                <label class="block font-bold">Dean/Assoc. Dean Name</label>
                <input v-model="form.dean_name" class="border p-2 w-full" required>
                <div v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</div>
            </div>
        </div>

        <!-- Member List Management -->
        <div class="mt-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold">Members</h3>
            </div>

            <div v-for="(member, idx) in currentPageMemberInputs" :key="startIndex + idx" class="mt-4 p-4 border rounded">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="font-bold">Member #{{ startIndex + idx + 1 }}</h4>
                    <button 
                        @click="removeMember(startIndex + idx)" 
                        type="button" 
                        :disabled="form.members.length <= 1"
                        :class="[
                            'px-2 py-1 rounded text-sm font-medium transition-colors',
                            form.members.length <= 1 
                                ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
                                : 'text-red-500 hover:text-red-700 hover:bg-red-50'
                        ]"
                    >
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold">Student Name</label>
                        <input v-model="member.student_name" class="border p-2 w-full" required>
                        <div v-if="errors[`member_${startIndex + idx}_name`]" class="text-red-500 text-sm mt-1">{{ errors[`member_${startIndex + idx}_name`] }}</div>
                    </div>

                    <div>
                        <label class="block font-bold">Student Number</label>
                        <input v-model="member.student_number" class="border p-2 w-full" required>
                        <div v-if="errors[`member_${startIndex + idx}_number`]" class="text-red-500 text-sm mt-1">{{ errors[`member_${startIndex + idx}_number`] }}</div>
                    </div>

                    <div>
                        <label class="block font-bold">Course - Year & Section</label>
                        <input v-model="member.course_year_section" class="border p-2 w-full" required>
                        <div v-if="errors[`member_${startIndex + idx}_course`]" class="text-red-500 text-sm mt-1">{{ errors[`member_${startIndex + idx}_course`] }}</div>
                    </div>

                    <div>
                        <label class="block font-bold">1x1 Photo</label>
                        <input type="file" @change="event => handlePhotoUpload(event, startIndex + idx)" class="border p-2 w-full" accept="image/*">
                        <div v-if="getPhotoPreview(member)" class="mt-2">
                            <img :src="getPhotoPreview(member)" alt="Preview" class="w-16 h-16 object-cover border">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination Controls for Member Inputs -->
            <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mt-8 gap-4">
                <button 
                    @click="prevPage" 
                    :disabled="currentPage === 1"
                    class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
                    Previous
                </button>
                <div class="flex gap-2">
                    <button 
                        v-for="page in totalPages" 
                        :key="page"
                        @click="goToPage(page)"
                        :class="[
                            'px-3 py-1 rounded',
                            currentPage === page 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                        ]">
                        {{ page }}
                    </button>
                </div>
                <button 
                    @click="nextPage" 
                    :disabled="currentPage === totalPages"
                    class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>

            <!-- Add Member Button (moved below member list, left-aligned) -->
            <div class="mt-4 flex justify-start">
                <button @click="addMember" type="button" class="bg-blue-500 text-white px-3 py-1 rounded">
                    Add Member
                </button>
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