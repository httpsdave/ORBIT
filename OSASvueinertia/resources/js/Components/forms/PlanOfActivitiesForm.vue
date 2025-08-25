<script setup>
import { ref, nextTick, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
// REMOVE: import StatusBanner from '@/Components/StatusBanner.vue';

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

const emit = defineEmits(['submitted', 'error']);

// Add pagination state
const currentPage = ref(1);
const activitiesPerPage = 1; // 1 activity per page since each activity is a full page
const isChangingPage = ref(false);

// Compute current year and next year for placeholders
const currentYear = computed(() => {
  return new Date().getFullYear().toString().slice(-2);
});

const nextYear = computed(() => {
  return (new Date().getFullYear() + 1).toString().slice(-2);
});

// Rich text editor state
const showToolbar = ref(false);
const toolbarPosition = ref({ x: 0, y: 0 });
const activeField = ref(null);

// Initialize activities first, before the form
const initializeActivities = () => {
  if (props.initialFormData?.activities && props.initialFormData.activities.length > 0) {
    // Copy activities from initialFormData and format target_date properly
    return props.initialFormData.activities.map(act => ({
      ...act,
      target_date: formatDateForInput(act.target_date)
    }));
  } else {
    // Add only one default empty activity
    return [
      {
        objective: '',
        name: '',
        description: '',
        persons_involved: '',
        target_date: '',
        budget: 0
      }
    ];
  }
};

const form = useForm({
  form_type: 'LSPU-OSAS-SF-004',
  organization_name: props.initialFormData.organization_name || '',
  academic_year_start: props.initialFormData.academic_year_start || '',
  academic_year_end: props.initialFormData.academic_year_end || '',
  semester: props.initialFormData.semester || '',
  president_name: props.initialFormData.president_name || '',
  secretary_name: props.initialFormData.secretary_name || '',
  // Removed application_date for Plan of Activities
  adviser_name: props.initialFormData.adviser_name || '',
  dean_name: props.initialFormData.dean_name || '',
  coordinator_name: props.initialFormData.coordinator_name || '',
  director_name: props.initialFormData.director_name || '',
  activities: initializeActivities(),
});

// Helper to format date to yyyy-MM-dd
function formatDateForInput(dateStr) {
  if (!dateStr) return '';
  // If already in yyyy-MM-dd, return as is
  if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
  
  // Handle different date formats without timezone issues
  try {
    // If it's a date string like "2025-08-25T00:00:00.000Z" or similar
    if (dateStr.includes('T') || dateStr.includes('Z')) {
      // Extract just the date part before 'T'
      const datePart = dateStr.split('T')[0];
      if (/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
        return datePart;
      }
    }
    
    // Try to parse as a regular date but avoid timezone shifts
    const d = new Date(dateStr + 'T12:00:00'); // Add midday to avoid timezone issues
    if (isNaN(d.getTime())) return '';
    
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  } catch (error) {
    console.warn('Date formatting error:', error, 'for date:', dateStr);
    return '';
  }
}

// Watch for changes in initialFormData to update form fields ONLY on initial load
watch(() => props.initialFormData, (newData) => {
  if (!newData || !props.isEdit) return; // Only update for edit mode
  
  // Only update if the form field is currently empty to avoid overwriting user input
  if (!form.organization_name) form.organization_name = newData.organization_name || '';
  if (!form.academic_year_start) form.academic_year_start = newData.academic_year_start || '';
  if (!form.academic_year_end) form.academic_year_end = newData.academic_year_end || '';
  if (!form.semester) form.semester = newData.semester || '';
  if (!form.president_name) form.president_name = newData.president_name || '';
  if (!form.secretary_name) form.secretary_name = newData.secretary_name || '';
  if (!form.adviser_name) form.adviser_name = newData.adviser_name || '';
  if (!form.dean_name) form.dean_name = newData.dean_name || '';
  if (!form.coordinator_name) form.coordinator_name = newData.coordinator_name || '';
  if (!form.director_name) form.director_name = newData.director_name || '';
  
  if (Array.isArray(newData.activities) && (!form.activities || form.activities.length === 0)) {
    form.activities = newData.activities.map(act => ({
      ...act,
      target_date: formatDateForInput(act.target_date)
    }));
    // Update contenteditable divs after data changes
    nextTick(() => {
      updateContentEditableDivs();
    });
  }
}, { immediate: true });

// Watch for page changes to update contenteditable divs
watch(() => currentPage.value, () => {
  nextTick(() => {
    updateContentEditableDivs();
  });
});

// Function to update contenteditable divs with form data
const updateContentEditableDivs = () => {
  // Update only the current page activities to avoid performance issues
  currentPageActivities.value.forEach((activity, idx) => {
    const actualIndex = startIndex.value + idx;
    const objectiveDiv = document.querySelector(`[data-field="objective-${actualIndex}"]`);
    const nameDiv = document.querySelector(`[data-field="name-${actualIndex}"]`);
    const descriptionDiv = document.querySelector(`[data-field="description-${actualIndex}"]`);
    const personsDiv = document.querySelector(`[data-field="persons_involved-${actualIndex}"]`);
    
    if (objectiveDiv) objectiveDiv.innerHTML = activity.objective || '';
    if (nameDiv) nameDiv.innerHTML = activity.name || '';
    if (descriptionDiv) descriptionDiv.innerHTML = activity.description || '';
    if (personsDiv) personsDiv.innerHTML = activity.persons_involved || '';
  });
};

// Rich text editor functions

// Debounce utility
function debounce(fn, delay) {
  let timer = null;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

// Helper to find if selection is inside a contenteditable field and which one
const getActiveFieldFromSelection = () => {
  const selection = window.getSelection();
  if (!selection.rangeCount) return null;
  const range = selection.getRangeAt(0);
  let node = range.startContainer;
  // Traverse up until we find a node with data-field
  while (node && node.nodeType !== 9) { // 9 = DOCUMENT_NODE
    if (node.nodeType === 1 && node.hasAttribute && node.hasAttribute('data-field')) {
      const dataField = node.getAttribute('data-field');
      const match = dataField.match(/^(\w+)-(\d+)$/);
      if (match) {
        return { fieldType: match[1], activityIndex: parseInt(match[2], 10), node };
      }
    }
    node = node.parentNode;
  }
  return null;
};

const showToolbarForSelection = () => {
  const selection = window.getSelection();
  if (!selection || !selection.toString().length) {
    hideToolbar();
    return;
  }
  const active = getActiveFieldFromSelection();
  if (active) {
    const range = selection.getRangeAt(0);
    const rect = range.getBoundingClientRect();
    toolbarPosition.value = {
      x: rect.left + (rect.width / 2) - 100,
      y: rect.top - 50
    };
    activeField.value = { fieldType: active.fieldType, activityIndex: active.activityIndex };
    showToolbar.value = true;
  } else {
    hideToolbar();
  }
};

const debouncedShowToolbarForSelection = debounce(showToolbarForSelection, 30);

// Mouseup handler for contenteditable fields
const handleMouseUp = () => {
  setTimeout(showToolbarForSelection, 0); // Let selection update first
};

const hideToolbar = () => {
  showToolbar.value = false;
  activeField.value = null;
};

const applyFormat = (command, value = null) => {
  if (!activeField.value) return;
  
  const { fieldType, activityIndex } = activeField.value;
  const field = document.querySelector(`[data-field="${fieldType}-${activityIndex}"]`);
  
  if (field) {
    field.focus();
    document.execCommand(command, false, value);
    
    // Update the form data with the formatted content
    const formattedContent = field.innerHTML;
    if (fieldType === 'objective') {
      form.activities[activityIndex].objective = formattedContent;
    } else if (fieldType === 'name') {
      form.activities[activityIndex].name = formattedContent;
    } else if (fieldType === 'description') {
      form.activities[activityIndex].description = formattedContent;
    } else if (fieldType === 'persons_involved') {
      form.activities[activityIndex].persons_involved = formattedContent;
    }
  }
  
  hideToolbar();
};

const insertList = (type) => {
  if (!activeField.value) return;
  
  const { fieldType, activityIndex } = activeField.value;
  const field = document.querySelector(`[data-field="${fieldType}-${activityIndex}"]`);
  
  if (field) {
    field.focus();
    const selection = window.getSelection();
    const selectedText = selection.toString();
    
    if (selectedText) {
      const listItem = type === 'ul' ? `<li>${selectedText}</li>` : `<ol><li>${selectedText}</li></ol>`;
      document.execCommand('insertHTML', false, listItem);
    } else {
      const listItem = type === 'ul' ? '<li></li>' : '<ol><li></li></ol>';
      document.execCommand('insertHTML', false, listItem);
    }
    
    // Update form data
    const formattedContent = field.innerHTML;
    if (fieldType === 'objective') {
      form.activities[activityIndex].objective = formattedContent;
    } else if (fieldType === 'name') {
      form.activities[activityIndex].name = formattedContent;
    } else if (fieldType === 'description') {
      form.activities[activityIndex].description = formattedContent;
    } else if (fieldType === 'persons_involved') {
      form.activities[activityIndex].persons_involved = formattedContent;
    }
  }
  
  hideToolbar();
};

// Pagination computed properties
const totalPages = computed(() => Math.ceil(form.activities.length / activitiesPerPage));
const startIndex = computed(() => (currentPage.value - 1) * activitiesPerPage);
const endIndex = computed(() => Math.min(startIndex.value + activitiesPerPage, form.activities.length));
const currentPageActivities = computed(() => {
    return form.activities.slice(startIndex.value, endIndex.value);
});

// Add computed for pagination display
const visiblePages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const delta = 2; // Number of pages to show on each side of current page
    
    if (total <= 7) {
        // If 7 or fewer pages, show all
        return Array.from({ length: total }, (_, i) => i + 1);
    }
    
    const range = [];
    const rangeWithDots = [];
    
    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        range.push(i);
    }
    
    if (current - delta > 2) {
        rangeWithDots.push(1, '...');
    } else {
        rangeWithDots.push(1);
    }
    
    rangeWithDots.push(...range);
    
    if (current + delta < total - 1) {
        rangeWithDots.push('...', total);
    } else {
        rangeWithDots.push(total);
    }
    
    return rangeWithDots;
});

// Navigation functions
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value && !isChangingPage.value) {
        isChangingPage.value = true;
        currentPage.value = page;
        nextTick(() => {
            isChangingPage.value = false;
        });
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value && !isChangingPage.value) {
        goToPage(currentPage.value + 1);
    }
};

const prevPage = () => {
    if (currentPage.value > 1 && !isChangingPage.value) {
        goToPage(currentPage.value - 1);
    }
};

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
    // Go to the new activity page
    currentPage.value = totalPages.value;
    // Update contenteditable divs after adding new activity
    nextTick(() => {
      updateContentEditableDivs();
    });
};

// Add a function to remove an activity
const removeActivity = (index) => {
    if (form.activities.length > 1) { // Prevent removing all activities
        form.activities.splice(index, 1);
        // Adjust current page if necessary
        if (currentPage.value > totalPages.value && totalPages.value > 0) {
            currentPage.value = totalPages.value;
        }
        // Update contenteditable divs after removing activity
        nextTick(() => {
            updateContentEditableDivs();
        });
    }
};

// Add errors ref object
const errors = ref({});

// Add validateForm function
const validateForm = () => {
  errors.value = {};
  let isValid = true;

  // Check main form required fields
  if (!form.organization_name.trim()) {
    errors.value.organization_name = 'Organization Name is required';
    isValid = false;
  }

  // Academic year start/end are always set to current/next year, so skip required validation

  if (!form.semester.trim()) {
    errors.value.semester = 'Semester is required';
    isValid = false;
  }

  if (!form.president_name.trim()) {
    errors.value.president_name = 'President Name is required';
    isValid = false;
  }

  if (!form.secretary_name.trim()) {
    errors.value.secretary_name = 'Secretary Name is required';
    isValid = false;
  }

  if (!form.adviser_name.trim()) {
    errors.value.adviser_name = 'Adviser Name is required';
    isValid = false;
  }

  if (!form.dean_name.trim()) {
    errors.value.dean_name = 'Dean Name is required';
    isValid = false;
  }

  if (!form.coordinator_name.trim()) {
    errors.value.coordinator_name = 'Coordinator Name is required';
    isValid = false;
  }

  if (!form.director_name.trim()) {
    errors.value.director_name = 'Director Name is required';
    isValid = false;
  }

  // Validate that we have at least one activity
  if (!form.activities || form.activities.length === 0) {
    errors.value.activities_general = 'At least one activity is required';
    isValid = false;
  }

  // Check activities
  if (!errors.value.activities) {
    errors.value.activities = {};
  }

  form.activities.forEach((activity, index) => {
    if (!errors.value.activities[index]) {
      errors.value.activities[index] = {};
    }

    // Strip HTML tags for validation
    const stripHtml = (html) => {
      const tmp = document.createElement('div');
      tmp.innerHTML = html;
      return tmp.textContent || tmp.innerText || '';
    };

    // Character limit validation
    const MAX_CHAR_GENERAL = 144;
    const MAX_CHAR_ACTIVITIES = 99;
    const MAX_CHAR_PERSONS = 99;
    if (!activity.objective || !stripHtml(activity.objective).trim()) {
      errors.value.activities[index].objective = 'Objective is required';
      isValid = false;
    } else if (stripHtml(activity.objective).length > MAX_CHAR_GENERAL) {
      errors.value.activities[index].objective = `Objective must be ${MAX_CHAR_GENERAL} characters or less.`;
      isValid = false;
    }

    if (!activity.name || !stripHtml(activity.name).trim()) {
      errors.value.activities[index].name = 'Activity name is required';
      isValid = false;
    } else if (stripHtml(activity.name).length > MAX_CHAR_ACTIVITIES) {
      errors.value.activities[index].name = `Activity name must be ${MAX_CHAR_ACTIVITIES} characters or less.`;
      isValid = false;
    }

    if (!activity.description || !stripHtml(activity.description).trim()) {
      errors.value.activities[index].description = 'Description is required';
      isValid = false;
    } else if (stripHtml(activity.description).length > MAX_CHAR_GENERAL) {
      errors.value.activities[index].description = `Description must be ${MAX_CHAR_GENERAL} characters or less.`;
      isValid = false;
    }

    if (!activity.persons_involved || !stripHtml(activity.persons_involved).trim()) {
      errors.value.activities[index].persons_involved = 'Persons involved is required';
      isValid = false;
    } else if (stripHtml(activity.persons_involved).length > MAX_CHAR_PERSONS) {
      errors.value.activities[index].persons_involved = `Persons involved must be ${MAX_CHAR_PERSONS} characters or less.`;
      isValid = false;
    }

    if (!activity.target_date) {
      errors.value.activities[index].target_date = 'Target date is required';
      isValid = false;
    }

   if (activity.budget === null || activity.budget === undefined || activity.budget < 0) {
      errors.value.activities[index].budget = 'Budget is required and must be 0 or greater';
      isValid = false;
    } else if (activity.budget > 9999999999999.99) {
      errors.value.activities[index].budget = 'Budget cannot exceed 9,999,999,999,999.99';
      isValid = false;
    }
  });

  return isValid;
};

// REMOVE: statusMessage, statusType, showStatus, showBanner

const submit = () => {
  if (!validateForm()) {
    emit('error', 'Please fill in all required fields.');
    return;
  }
  
  console.log('Submitting form data:', form.data());
  console.log('Semester value:', form.semester);
  
  // Check if we're in edit mode
  if (props.isEdit) {
    // For edit mode, just emit the data - don't make HTTP request here
    emit('submitted', form.data());
  } else {
    // For create mode, make the POST request
    form.post('/applications', {
      onSuccess: () => {
        emit('submitted', form.data());
      },
      onError: (errors) => {
        emit('error', 'Form submission failed.');
        console.error('Form submission errors:', errors);
      }
    });
  }
};

// Function to handle contenteditable input without disrupting cursor position
const handleContentEditableInput = (event, activityIndex, fieldType) => {
  const content = event.target.innerHTML;
  
  // Update the form data directly without triggering reactive updates that would rewrite the DOM
  if (fieldType === 'objective') {
    form.activities[activityIndex].objective = content;
  } else if (fieldType === 'name') {
    form.activities[activityIndex].name = content;
  } else if (fieldType === 'description') {
    form.activities[activityIndex].description = content;
  } else if (fieldType === 'persons_involved') {
    form.activities[activityIndex].persons_involved = content;
  }
};

// Hide toolbar when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.rich-text-toolbar') && !event.target.closest('[contenteditable]')) {
    hideToolbar();
  }
};

function limitTo2Digits(event) {
  event.target.value = event.target.value.replace(/[^0-9]/g, '').slice(0, 2);
}

// Add event listeners
nextTick(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('selectionchange', debouncedShowToolbarForSelection);
  // Initialize contenteditable divs with existing data
  updateContentEditableDivs();
});
</script>

<template>
  <div class="mt-6 form-content">
    <!-- Rich Text Toolbar -->
    <div 
      v-if="showToolbar" 
      class="rich-text-toolbar fixed z-50 bg-white border border-gray-300 rounded-lg shadow-lg p-2 flex items-center gap-1"
      :style="{ 
        left: toolbarPosition.x + 'px', 
        top: toolbarPosition.y + 'px' 
      }"
    >
      <!-- Bold -->
      <button 
        @click="applyFormat('bold')" 
        class="p-2 hover:bg-gray-100 rounded"
        title="Bold"
      >
        <strong>B</strong>
      </button>
      
      <!-- Italic -->
      <button 
        @click="applyFormat('italic')" 
        class="p-2 hover:bg-gray-100 rounded"
        title="Italic"
      >
        <em>I</em>
      </button>
      
      <!-- Underline -->
      <button 
        @click="applyFormat('underline')" 
        class="p-2 hover:bg-gray-100 rounded border-b-2 border-black"
        title="Underline"
      >
        U
      </button>
      
      <!-- Divider -->
      <div class="w-px h-6 bg-gray-300 mx-1"></div>
      
      <!-- Bullet List -->
      <button 
        @click="insertList('ul')" 
        class="p-2 hover:bg-gray-100 rounded"
        title="Bullet List"
      >
        •
      </button>
      
      <!-- Numbered List -->
      <button 
        @click="insertList('ol')" 
        class="p-2 hover:bg-gray-100 rounded"
        title="Numbered List"
      >
        1.
      </button>
    </div>

    <!-- Document Header (similar to blade template) -->
    <div class="header text-center relative py-4">
  <img src="/images/lspu-logo.png" alt="LSPU Logo" class="absolute top-[-0.5cm] left-[-2cm] w-[250px] h-auto">
      <div class="font-normal text-[11pt] leading-tight" style="font-family:Calibri,sans-serif;">
        Republic of the Philippines<br>
        <img src="/images/lspu-name.png" alt="Laguna State Polytechnic University" class="inline-block align-middle h-[22px] max-w-[55%] my-1 university-name" /><br>
        <span class="block mb-2">Province of Laguna</span>
      </div>
      <div class="font-bold text-[11pt] mt-1 mb-1" style="font-family:'Times New Roman',serif;">OFFICE OF STUDENT AFFAIRS AND SERVICES</div>
      <div class="font-bold text-[15pt] mt-4 mb-4" style="font-family:'Times New Roman',serif;">PLAN OF ACTIVITIES</div>
      
      <!-- Organization Name Section -->
      <div class="mt-4 text-center">
        <div class="border-b border-black min-w-[330px] inline-block text-center font-bold text-[11pt] pb-1" style="font-family:'Times New Roman',serif;">
          {{ form.organization_name }}
        </div>
        <div class="text-[11pt] mt-1 font-bold" style="font-family:'Times New Roman',serif;">Name of Organization</div>
      </div>
      <!-- Add spacing between Name of Organization and Semester section -->
      <div style="height: .5rem;"></div>
      <!-- Academic Year Section -->
      <div class="text-center mt-3 text-[11pt] font-bold" style="font-family:'Times New Roman',serif;">
        <span class="border-b border-black px-2 min-w-[48px] inline-block">{{ form.semester }}</span>
        Semester AY 20<span class="border-b border-black px-2 min-w-[24px] inline-block">{{ form.academic_year_start || '__' }}</span>-20<span class="border-b border-black px-2 min-w-[24px] inline-block">{{ form.academic_year_end || '__' }}</span>
      </div>
    </div>

    <!-- Reduce spacing below semester section -->
    <div style="margin-bottom: 0.5rem;"></div>

    <!-- Activities Table (similar to blade template) -->
    <div class="mt-2">
      <div class="mb-2">
        <h4 class="text-md font-bold">Activities</h4>
      </div>
      
      <p v-if="errors.activities_general" class="text-red-500 text-sm mb-2">{{ errors.activities_general }}</p>
      
      <!-- Activity Count Display -->
      <div class="mb-4 p-2 bg-gray-50 border border-gray-200 rounded text-sm">
        <span class="font-semibold">📋 Total Activities: {{ form.activities.length }}</span>
        <span v-if="form.activities.length > 0" class="ml-4 text-gray-600">
          • Page {{ currentPage }} of {{ totalPages }}
        </span>
      </div>

      <!-- Pagination Controls (Top) -->
      <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mb-4 gap-4">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1 || isChangingPage"
          class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
          Previous
        </button>
        
        <div class="flex gap-2">
          <template v-for="page in visiblePages" :key="page">
            <button 
              v-if="page !== '...'"
              @click="goToPage(page)" 
              :class="[
                'px-3 py-2 rounded',
                currentPage === page 
                  ? 'bg-blue-600 text-white' 
                  : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
              ]"
              :disabled="isChangingPage"
            >
              {{ page }}
            </button>
            <span v-else class="px-3 py-2 text-gray-500">...</span>
          </template>
        </div>
        
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages || isChangingPage"
          class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed">
          Next
        </button>
      </div>

      <!-- Page Info -->
  <!-- Removed duplicate Page Info -->
      
      <!-- Activities Table (matching blade template structure) -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse table-fixed" style="margin: 0.5cm 0;">
          <thead>
            <tr>
              <th class="border font-bold text-[10pt]" style="width:15%;padding:5px;">OBJECTIVE</th>
              <th class="border font-bold text-[10pt]" style="width:15%;padding:5px;">ACTIVITIES</th>
              <th class="border font-bold text-[10pt]" style="width:25%;padding:5px;">BRIEF <br> DESCRIPTION</th>
              <th class="border font-bold text-[10pt]" style="width:15%;padding:5px;">PERSONS INVOLVED</th>
              <th class="border font-bold text-[10pt]" style="width:15%;padding:5px;">TARGET DATE</th>
              <th class="border font-bold text-[10pt]" style="width:15%;padding:5px;">BUDGET</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(activity, idx) in currentPageActivities" :key="startIndex + idx" style="height: 150px;">
              <td class="border align-top" style="min-height:150px;padding:8px;vertical-align:top;">
                <div 
                  :data-field="`objective-${startIndex + idx}`"
                  contenteditable="true"
                  :placeholder="'Objective'"
                  class="w-full min-h-[40px]"
                  @input="(e) => handleContentEditableInput(e, startIndex + idx, 'objective')"
                  @mouseup="handleMouseUp"
                  style="outline:none;word-break:break-word;overflow-wrap:break-word;"
                ></div>
                <p v-if="errors.activities && errors.activities[startIndex + idx] && errors.activities[startIndex + idx].objective" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].objective }}</p>
              </td>
              <td class="border align-top" style="min-height:150px;padding:8px;vertical-align:top;">
                <div 
                  :data-field="`name-${startIndex + idx}`"
                  contenteditable="true"
                  :placeholder="'Activity'"
                  class="w-full min-h-[40px]"
                  @input="(e) => handleContentEditableInput(e, startIndex + idx, 'name')"
                  @mouseup="handleMouseUp"
                  style="outline:none;word-break:break-word;overflow-wrap:break-word;"
                ></div>
                <p v-if="errors.activities && errors.activities[startIndex + idx] && errors.activities[startIndex + idx].name" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].name }}</p>
              </td>
              <td class="border align-top" style="min-height:150px;padding:8px;vertical-align:top;">
                <div 
                  :data-field="`description-${startIndex + idx}`"
                  contenteditable="true"
                  :placeholder="'Brief Description'"
                  class="w-full min-h-[40px]"
                  @input="(e) => handleContentEditableInput(e, startIndex + idx, 'description')"
                  @mouseup="handleMouseUp"
                  style="outline:none;word-break:break-word;overflow-wrap:break-word;"
                ></div>
                <p v-if="errors.activities && errors.activities[startIndex + idx] && errors.activities[startIndex + idx].description" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].description }}</p>
              </td>
              <td class="border align-top" style="min-height:150px;padding:8px;vertical-align:top;">
                <div 
                  :data-field="`persons_involved-${startIndex + idx}`"
                  contenteditable="true"
                  :placeholder="'Persons Involved'"
                  class="w-full min-h-[40px]"
                  @input="(e) => handleContentEditableInput(e, startIndex + idx, 'persons_involved')"
                  @mouseup="handleMouseUp"
                  style="outline:none;word-break:break-word;overflow-wrap:break-word;"
                ></div>
                <p v-if="errors.activities && errors.activities[startIndex + idx] && errors.activities[startIndex + idx].persons_involved" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].persons_involved }}</p>
              </td>
              <td class="border align-top" style="min-height:150px;padding:8px;vertical-align:top;">
                <input 
                  type="date"
                  v-model="activity.target_date"
                  class="w-full border p-1 text-center"
                  style="min-height:40px;"
                >
                <p v-if="errors.activities && errors.activities[startIndex + idx] && errors.activities[startIndex + idx].target_date" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].target_date }}</p>
              </td>
              <td class="border align-top" style="min-height:150px;padding:8px;vertical-align:top;">
                <input 
                  type="number"
                  v-model.number="activity.budget"
                  min="0"
                  step="0.01"
                  class="w-full border p-1 text-right"
                  style="min-height:40px;"
                >
                <p v-if="errors.activities && errors.activities[startIndex + idx] && errors.activities[startIndex + idx].budget" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].budget }}</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add Activity Button -->
      <div class="text-center mb-6">
        <button type="button" @click="addActivity" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
          Add Activity
        </button>
        <button
          type="button"
          @click="removeActivity(startIndex)"
          class="bg-red-500 text-white px-3 py-1 rounded text-sm ml-2"
          :disabled="form.activities.length <= 1"
        >
          Remove Activity
        </button>
      </div>
    </div>

    <!-- Signatures Section (similar to blade template) -->
    <div v-if="form.activities.length > 0" class="mt-6 signatures-section">
      <!-- Prepared by label -->
      <div class="text-left mb-2 text-[11pt]" style="font-family:'Times New Roman',serif;">Prepared by:</div>
      
      <!-- First signature row with President and Secretary -->
      <div class="flex justify-between mb-6">
        <div class="text-center" style="width: 45%;">
          <div class="border-b border-black min-w-[200px] inline-block text-center pb-1 text-[11pt]" style="font-family:'Times New Roman',serif;">
            {{ form.president_name }}
          </div>
          <div class="text-[11pt] mt-1" style="font-family:'Times New Roman',serif;">Organization President</div>
        </div>
        <div class="text-center" style="width: 45%;">
          <div class="border-b border-black min-w-[200px] inline-block text-center pb-1 text-[11pt]" style="font-family:'Times New Roman',serif;">
            {{ form.secretary_name }}
          </div>
          <div class="text-[11pt] mt-1" style="font-family:'Times New Roman',serif;">Organization Secretary</div>
        </div>
      </div>
      
      <!-- Noted label -->
      <div class="text-left mb-2 text-[11pt]" style="font-family:'Times New Roman',serif;"><strong>Noted:</strong></div>
      
      <!-- Second signature row with Faculty Adviser -->
      <div class="text-left mb-6" style="width: 45%;">
        <div class="border-b border-black min-w-[200px] inline-block text-center pb-1 text-[11pt]" style="font-family:'Times New Roman',serif;">
          {{ form.adviser_name }}
        </div>
        <div class="text-[11pt] mt-1" style="font-family:'Times New Roman',serif; margin-left: 25px">Organization Adviser(s)</div>
      </div>
      
      <!-- Third signature row with Dean -->
      <div class="text-left mb-6" style="width: 45%;">
        <div class="border-b border-black min-w-[200px] inline-block text-center pb-1 text-[11pt]" style="font-family:'Times New Roman',serif;">
          {{ form.dean_name }}
        </div>
        <div class="text-[11pt] mt-1" style="font-family:'Times New Roman',serif;margin-left: 45px">Dean/Assoc. Dean</div>
      </div>
      
      <!-- Recommending Approval -->
      <div class="text-center mt-6 mb-6">
        <div class="font-bold mb-2 text-[11pt]" style="font-family:'Times New Roman',serif;"><strong>Recommending Approval:</strong></div>
        <div class="border-b border-black min-w-[290px] inline-block text-center pb-1 text-[11pt]" style="font-family:'Times New Roman',serif;">
          {{ form.coordinator_name }}
        </div>
        <div class="text-[11pt] mt-1" style="font-family:'Times New Roman',serif;">Coordinator, Student Organization Unit</div>
      </div>
      
      <!-- Approved/Disapproved -->
      <div class="text-center mt-6 mb-6">
        <div class="font-bold mb-2 text-[11pt]" style="font-family:'Times New Roman',serif;"><strong>Approved/Disapproved:</strong></div>
        <div class="border-b border-black min-w-[415px] inline-block text-center pb-1 text-[11pt]" style="font-family:'Times New Roman',serif;">
          {{ form.director_name }}
        </div>
        <div class="text-[11pt] mt-1" style="font-family:'Times New Roman',serif;">Director/Chairperson, Office of Student Affairs and Services</div>
      </div>
    </div>

    <!-- Form inputs section -->
    <div class="mt-8 border-t pt-6">
      <h3 class="text-lg font-bold mb-4">Form Details</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block font-bold">Organization Name</label>
          <input v-model="form.organization_name" class="border p-2 w-full" required>
          <p v-if="errors.organization_name" class="text-red-500 text-sm mt-1">{{ errors.organization_name }}</p>
        </div>

        <div>
          <label class="block font-bold">Semester</label>
          <select v-model="form.semester" class="border p-2 w-full" required>
            <option value="">Select Semester</option>
            <option value="1st">1st</option>
            <option value="2nd">2nd</option>
            <option value="Inter">Inter semester</option>
          </select>
          <p v-if="errors.semester" class="text-red-500 text-sm mt-1">{{ errors.semester }}</p>
        </div>

        <div class="flex items-end space-x-2">
          <div>
            <label class="block font-bold">Academic Year</label>
            <div class="flex items-center space-x-2">
              <input 
                v-model="form.academic_year_start" 
                class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
                :placeholder="currentYear" 
                readonly 
                tabindex="-1" 
                style="user-select: none; -webkit-user-select: none;" 
              >
              <span class="mx-1">-</span>
              <input 
                v-model="form.academic_year_end" 
                class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
                :placeholder="nextYear" 
                readonly 
                tabindex="-1" 
                style="user-select: none; -webkit-user-select: none;" 
              >
            </div>
            <div class="flex space-x-2">
              <p v-if="errors.academic_year_start" class="text-red-500 text-sm mt-1">{{ errors.academic_year_start }}</p>
              <p v-if="errors.academic_year_end" class="text-red-500 text-sm mt-1">{{ errors.academic_year_end }}</p>
            </div>
          </div>
        </div>

        <div>
          <label class="block font-bold">President Name</label>
          <input v-model="form.president_name" class="border p-2 w-full" required>
          <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
        </div>

        <div>
          <label class="block font-bold">Secretary Name</label>
          <input v-model="form.secretary_name" class="border p-2 w-full" required>
          <p v-if="errors.secretary_name" class="text-red-500 text-sm mt-1">{{ errors.secretary_name }}</p>
        </div>

        <div>
          <label class="block font-bold">Adviser Name</label>
          <input v-model="form.adviser_name" class="border p-2 w-full" required>
          <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
        </div>

        <div>
          <label class="block font-bold">Dean Name</label>
          <input v-model="form.dean_name" class="border p-2 w-full" required>
          <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
        </div>

        <div>
          <label class="block font-bold">Coordinator Name</label>
          <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-100 text-gray-600" readonly>
        </div>

        <div>
          <label class="block font-bold">Director Name</label>
          <input v-model="form.director_name" class="border p-2 w-full bg-gray-100 text-gray-600" readonly>
        </div>
      </div>

      <div class="mt-6 text-center">
        <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">Submit</button>
      </div>
    </div>

    <!-- Footer (matching blade template) -->
    <div class="footer mt-8 text-xs flex justify-between text-[10pt]" style="font-family: Calibri, sans-serif;">
      <span>LSPU-OSAS-SF-004</span>
      <span>Rev. 1</span>
      <span>09 November 2020</span>
    </div>
  </div>
</template>
                            <input 
                              v-model="form.academic_year_start" 
                              class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
                              :placeholder="currentYear" 
                              readonly 
                              tabindex="-1" 
                              style="user-select: none; -webkit-user-select: none;" 
                            >
                            <span class="mx-1">-</span>
                            <input 
                              v-model="form.academic_year_end" 
                              class="border p-2 w-16 bg-gray-200 text-gray-500 select-none pointer-events-none text-center" 
                              :placeholder="nextYear" 
                              readonly 
                              tabindex="-1" 
                              style="user-select: none; -webkit-user-select: none;" 
                            >
                          </div>
                          <div class="flex space-x-2">
                            <p v-if="errors.academic_year_start" class="text-red-500 text-sm mt-1">{{ errors.academic_year_start }}</p>
                            <p v-if="errors.academic_year_end" class="text-red-500 text-sm mt-1">{{ errors.academic_year_end }}</p>
                          </div>
                        </div>
                      </div>

                    <div>
                        <label class="block font-bold">President Name</label>
                        <input v-model="form.president_name" class="border p-2 w-full" required>
                        <p v-if="errors.president_name" class="text-red-500 text-sm mt-1">{{ errors.president_name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Secretary Name</label>
                        <input v-model="form.secretary_name" class="border p-2 w-full" required>
                        <p v-if="errors.secretary_name" class="text-red-500 text-sm mt-1">{{ errors.secretary_name }}</p>
                    </div>

                    <!-- Application Date field removed for Plan of Activities -->

                    <div>
                        <label class="block font-bold">Adviser Name</label>
                        <input v-model="form.adviser_name" class="border p-2 w-full" required>
                        <p v-if="errors.adviser_name" class="text-red-500 text-sm mt-1">{{ errors.adviser_name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Dean Name</label>
                        <input v-model="form.dean_name" class="border p-2 w-full" required>
                        <p v-if="errors.dean_name" class="text-red-500 text-sm mt-1">{{ errors.dean_name }}</p>
                    </div>

                    <div>
                        <label class="block font-bold">Coordinator Name</label>
                        <input v-model="form.coordinator_name" class="border p-2 w-full bg-gray-100 text-gray-600" readonly>
                    </div>

                    <div>
                        <label class="block font-bold">Director Name</label>
                        <input v-model="form.director_name" class="border p-2 w-full bg-gray-100 text-gray-600" readonly>
                    </div>
                </div>

                <!-- Activities Table -->
                <div class="mt-6">
                    <div class="mb-2">
                        <h4 class="text-md font-bold">Activities</h4>
                    </div>
                    
                    <p v-if="errors.activities_general" class="text-red-500 text-sm mb-2">{{ errors.activities_general }}</p>
                    
                    <!-- Activity Count Display -->
                    <div class="mb-4 p-2 bg-gray-50 border border-gray-200 rounded text-sm">
                        <span class="font-semibold">📋 Total Activities: {{ form.activities.length }}</span>
                        <span v-if="form.activities.length > 0" class="ml-4 text-gray-600">
                            • Page {{ currentPage }} of {{ totalPages }}
                        </span>
                    </div>

                    <!-- Pagination Controls (Top) -->

                    <!-- Page Info -->
                    <div v-if="totalPages > 1" class="text-center mb-4 text-sm text-gray-600">
                        Page {{ currentPage }} of {{ totalPages }} • Activity {{ startIndex + 1 }} of {{ form.activities.length }}
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-300 mb-4 min-w-[1000px]">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 p-2 text-xs">OBJECTIVE</th>
                                    <th class="border border-gray-300 p-2 text-xs">ACTIVITIES</th>
                                    <th class="border border-gray-300 p-2 text-xs">BRIEF DESCRIPTION</th>
                                    <th class="border border-gray-300 p-2 text-xs">PERSONS INVOLVED</th>
                                    <th class="border border-gray-300 p-2 text-xs">TARGET DATE</th>
                                    <th class="border border-gray-300 p-2 text-xs">BUDGET</th>
                                    <th class="border border-gray-300 p-2 text-xs">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(activity, idx) in currentPageActivities" :key="startIndex + idx">
                                    <td class="border border-gray-300 p-2">
                                        <div 
                                            :data-field="`objective-${startIndex + idx}`"
                                            contenteditable="true"
                                            @input="handleContentEditableInput($event, startIndex + idx, 'objective')"
                                            @mouseup="handleMouseUp"
                                            class="w-full p-1 text-sm min-h-[20px] focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Enter objective..."
                                        ></div>
                                        <p v-if="errors.activities?.[startIndex + idx]?.objective" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].objective }}</p>
                                    </td>
                                    <td class="border border-gray-300 p-2">
                                        <div 
                                            :data-field="`name-${startIndex + idx}`"
                                            contenteditable="true"
                                            @input="handleContentEditableInput($event, startIndex + idx, 'name')"
                                            @mouseup="handleMouseUp"
                                            class="w-full p-1 text-sm min-h-[20px] focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Enter activity name..."
                                        ></div>
                                        <p v-if="errors.activities?.[startIndex + idx]?.name" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].name }}</p>
                                    </td>
                                    <td class="border border-gray-300 p-2">
                                        <div 
                                            :data-field="`description-${startIndex + idx}`"
                                            contenteditable="true"
                                            @input="handleContentEditableInput($event, startIndex + idx, 'description')"
                                            @mouseup="handleMouseUp"
                                            class="w-full p-1 text-sm min-h-[40px] focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Enter description..."
                                        ></div>
                                        <p v-if="errors.activities?.[startIndex + idx]?.description" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].description }}</p>
                                    </td>
                                    <td class="border border-gray-300 p-2">
                                        <div 
                                            :data-field="`persons_involved-${startIndex + idx}`"
                                            contenteditable="true"
                                            @input="handleContentEditableInput($event, startIndex + idx, 'persons_involved')"
                                            @mouseup="handleMouseUp"
                                            class="w-full p-1 text-sm min-h-[20px] focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Enter persons involved..."
                                        ></div>
                                        <p v-if="errors.activities?.[startIndex + idx]?.persons_involved" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].persons_involved }}</p>
                                    </td>
                                    <td class="border border-gray-300 p-2">
                                        <input type="date" v-model="activity.target_date" class="w-full p-1 text-sm" required>
                                        <p v-if="errors.activities?.[startIndex + idx]?.target_date" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].target_date }}</p>
                                    </td>
                                    <td class="border border-gray-300 p-2">
                                        <input type="number" v-model.number="activity.budget" step="0.01" min="0" max="9999999999999.99" class="w-full p-1 text-sm" required>
                                        <p v-if="errors.activities?.[startIndex + idx]?.budget" class="text-red-500 text-xs mt-1">{{ errors.activities[startIndex + idx].budget }}</p>
                                    </td>
                                    <td class="border border-gray-300 p-2 text-center">
                                        <button 
                                            type="button" 
                                            @click="removeActivity(startIndex + idx)" 
                                            class="bg-red-500 text-white px-2 py-1 rounded text-xs"
                                            :disabled="form.activities.length <= 1"
                                            :class="{ 'opacity-50 cursor-not-allowed': form.activities.length <= 1 }"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls (Bottom) -->
                    <div v-if="totalPages > 1" class="pagination-controls flex justify-center items-center mt-6 gap-4">
                        <button 
                            @click="prevPage" 
                            :disabled="currentPage === 1 || isChangingPage"
                            class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                            Previous
                        </button>
                        
                        <div class="flex gap-2">
                            <button 
                                v-for="page in visiblePages" 
                                :key="page"
                                @click="page === '...' ? null : goToPage(page)"
                                :disabled="page === '...' || isChangingPage"
                                :class="[
                                    'px-3 py-1 rounded transition-colors',
                                    page === '...' 
                                        ? 'text-gray-400 cursor-default' 
                                        : currentPage === page 
                                            ? 'bg-blue-600 text-white' 
                                            : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                                    isChangingPage ? 'opacity-50' : ''
                                ]">
                                {{ page }}
                            </button>
                        </div>
                      
                        <button 
                            @click="nextPage" 
                            :disabled="currentPage === totalPages || isChangingPage"
                            class="px-4 py-2 bg-blue-500 text-white rounded disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                            Next
                        </button>
                    </div>
                </div>

                <div class="mt-6 flex items-center">
                  <div class="flex-1">
                    <button type="button" @click="addActivity" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                      ➕ Add Activity
                    </button>
                  </div>
                  <div class="flex-1 text-center">
                    <button type="submit" @click="submit" class="bg-green-500 text-white px-4 py-2 rounded">
                      {{ props.isEdit ? 'Update' : 'Submit' }}
                    </button>
                  </div>
                  <div class="flex-1"></div>
                </div>
            </div>

            <div class="footer mt-8 text-xs flex justify-between">
                <span>LSPU-OSAS-SF-004</span>
                <span>Rev. 1</span>
                <span>09 November 2020</span>
            </div>
        </div>
</template>

<style scoped>
/* Ensure A4 Paper Size */
.form-content {
    width: 210mm;
    min-height: 297mm;
    padding: 20mm;
    margin: auto;
    background: white;
    font-family: 'Times New Roman', serif;
    font-size: 11pt;
    line-height: 1.1;
}

[contenteditable]:empty:before {
  content: attr(placeholder);
  color: #9ca3af;
  pointer-events: none;
}

[contenteditable]:focus {
  outline: none;
}

.rich-text-toolbar button {
  font-size: 12px;
  min-width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Table styling to match blade template */
table {
  table-layout: fixed;
  overflow-wrap: break-word;
}

table td {
  vertical-align: top;
  word-wrap: break-word;
  padding: 8px;
}

/* Header styling */
.header {
  font-family: 'Times New Roman', serif;
}

.university-name {
  max-width: 55%;
  height: auto;
  margin: 4px 0;
  display: inline-block;
}

/* Signature styling */
.signatures-section {
  font-family: 'Times New Roman', serif;
  font-size: 11pt;
}

/* Footer styling */
.footer {
  font-family: Calibri, sans-serif;
  font-size: 10pt;
}

/* Ensure proper printing */
@media print {
  .form-content {
    width: 210mm;
    height: 297mm;
    margin: 0;
    padding: 20mm;
  }
  
  table { 
    page-break-inside: avoid; 
    margin-bottom: 20px;
  }
  
  tr { 
    page-break-inside: avoid; 
    page-break-after: auto; 
  }
  
  td { 
    vertical-align: top; 
    word-wrap: break-word;
  }
  
  .signatures-section {
    page-break-inside: avoid;
  }
}
</style>