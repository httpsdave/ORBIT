<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    archivedApplications: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
    academicYears: {
        type: Array,
        default: () => [],
    },
    currentUserFilter: {
        type: String,
        default: '',
    },
    currentAcademicYearFilter: {
        type: String,
        default: '',
    },
    successMessage: {
        type: String,
        default: '',
    },
    errorMessage: {
        type: String,
        default: '',
    },
});

const showRestoreModal = ref(false);
const applicationToRestore = ref(null);

// Add dropdown state management
const activeDropdownApp = ref(null);
const activeMobileDropdownId = ref(null);
const dropdownPosition = ref({ top: 0, left: 0 });
const dropdownButtonEl = ref(null);
const dropdownRef = ref(null);
const dropdownDirection = ref('down');

const filterForm = ref({
    user_filter: props.currentUserFilter,
    academic_year_filter: props.currentAcademicYearFilter,
});

const applyFilters = () => {
    router.get(route('admin.archive.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        user_filter: '',
        academic_year_filter: '',
    };
    applyFilters();
};

const confirmRestore = (application) => {
    applicationToRestore.value = application;
    showRestoreModal.value = true;
};

const restoreApplication = () => {
    if (applicationToRestore.value) {
        router.patch(route('admin.archive.restore', applicationToRestore.value.id), {}, {
            onSuccess: () => {
                showRestoreModal.value = false;
                applicationToRestore.value = null;
            },
        });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status) => {
    switch (status?.toLowerCase()) {
        case 'approved':
            return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
        case 'disapproved':
            return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
        case 'pending':
            return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300';
        default:
            return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
    }
};

const getFormTypeLabel = (formType) => {
    const formTypes = {
        'LSPU-OSAS-SF-001': 'Student Organization Application',
        'LSPU-OSAS-SF-002': 'Student Organization Renewal',
        'LSPU-OSAS-SF-003': 'Commitment Form',
        'LSPU-OSAS-SF-004': 'Plan of Activities',
        'LSPU-OSAS-SF-005': 'List of Members',
        'LSPU-OSAS-SF-006': 'Student Certification',
        'LSPU-OSAS-SF-007': 'List of Officers',
        'LSPU-OSAS-SF-009': 'Activity Attendance Sheet',
    };
    return formTypes[formType] || formType;
};

// Toggle action dropdown
const toggleDropdown = (app, event) => {
    if (window.innerWidth < 640) { // Mobile: show inline dropdown
        if (activeMobileDropdownId.value === app.id) {
            activeMobileDropdownId.value = null;
        } else {
            activeMobileDropdownId.value = app.id;
        }
        return;
    }
    // Desktop/table: floating dropdown
  // Prevent the document click listener from closing the dropdown immediately
  event.stopPropagation();

  if (activeDropdownApp.value && activeDropdownApp.value.id === app.id) {
        activeDropdownApp.value = null;
        dropdownButtonEl.value = null;
        removeDropdownListeners();
    } else {
        activeDropdownApp.value = app;
        dropdownButtonEl.value = event.currentTarget;
        updateDropdownPosition();
        addDropdownListeners();
    }
};

async function updateDropdownPosition() {
    if (!dropdownButtonEl.value) return;
    const rect = dropdownButtonEl.value.getBoundingClientRect();
    let dropdownWidth = window.innerWidth < 640 ? Math.min(window.innerWidth - 32, 320) : 256;
    let left = rect.right - dropdownWidth;
    if (left + dropdownWidth > window.innerWidth) left = window.innerWidth - dropdownWidth - 16;
    if (left < 16) left = 16;

    await nextTick();
    let dropdownHeight = dropdownRef.value ? dropdownRef.value.offsetHeight : 320;

    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;

    let top;
    if (spaceBelow >= dropdownHeight + 16) {
        top = rect.bottom + 6;
        dropdownDirection.value = 'down';
    } else if (spaceAbove >= dropdownHeight + 16) {
        top = rect.top - dropdownHeight - 6;
        dropdownDirection.value = 'up';
    } else if (spaceBelow >= spaceAbove) {
        top = rect.bottom + 6;
        dropdownDirection.value = 'down';
    } else {
        top = Math.max(8, rect.top - dropdownHeight - 6);
        dropdownDirection.value = 'up';
    }

    dropdownPosition.value = { top, left };
}

function addDropdownListeners() {
    window.addEventListener('scroll', updateDropdownPosition, true);
    window.addEventListener('resize', updateDropdownPosition);
}

function removeDropdownListeners() {
    window.removeEventListener('scroll', updateDropdownPosition, true);
    window.removeEventListener('resize', updateDropdownPosition);
}

// Close dropdowns when clicking outside
const closeDropdowns = (event) => {
  try {
    // If click happened inside the dropdown element, ignore
    if (dropdownRef.value && dropdownRef.value.contains(event.target)) return;

    // If click happened on a dropdown trigger button, ignore (so toggle still works)
    if (event.target.closest && event.target.closest('[data-dropdown-trigger]')) return;

    // Otherwise close any open floating dropdown
    activeDropdownApp.value = null;
    dropdownButtonEl.value = null;
    removeDropdownListeners();
  } catch (e) {
    // ignore non-browser environments
  }
};

const closeMobileDropdowns = (event) => {
  try {
    if (window.innerWidth >= 640) return; // not mobile
    // If click happened inside the mobile dropdown, ignore
    if (event.target.closest && event.target.closest('.mobile-dropdown-menu')) return;
    // If click happened on a dropdown trigger button, ignore
    if (event.target.closest && event.target.closest('[data-dropdown-trigger]')) return;

    // Close mobile inline dropdown
    activeMobileDropdownId.value = null;
  } catch (e) {
    // ignore
  }
};

// Back-to-top button state and handler (same as OrganizationApplications Index.vue)
const showBackToTop = ref(false);
const onScroll = () => {
    try {
        const y = window.scrollY || window.pageYOffset;
        showBackToTop.value = y > 300;
    } catch (e) {
        // ignore in non-browser environments
    }
};

// Preview modal functionality (same as ApplicationsTable.vue)
const showPreviewModal = ref(false);
const previewApp = ref(null);

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    removeDropdownListeners();
    window.removeEventListener('scroll', onScroll);
});

// Ensure dropdowns close when clicking outside
onMounted(() => {
  document.addEventListener('click', closeDropdowns);
  document.addEventListener('click', closeMobileDropdowns);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdowns);
  document.removeEventListener('click', closeMobileDropdowns);
});

// Smooth scroll helper
const scrollToTop = (e) => {
    e?.preventDefault();
    try {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (err) {
        // ignore if window is not available
    }
};

// Preview modal functions (from ApplicationsTable.vue)
const formTypeToName = (formType) => {
  switch(formType) {
    case 'LSPU-OSAS-SF-001':
      return 'Organization Recognition';
    case 'LSPU-OSAS-SF-002':
      return 'Renewal Application';
    case 'LSPU-OSAS-SF-003':
      return 'Commitment Form';
    case 'LSPU-OSAS-SF-004':
      return 'Plan of Activities';
    case 'LSPU-OSAS-SF-005':
      return 'Members List';
    case 'LSPU-OSAS-SF-006':
      return 'Certification Form';
    case 'LSPU-OSAS-SF-007':
      return 'Officers List';
    case 'LSPU-OSAS-SF-009':
      return 'Student Activity Attendance Sheet';
    case 'LSPU-OSAS-SF-EVAL':
      return 'Evaluation Summary';
    case 'LSPU-OSAS-SF-ACCOMPLISHMENT':
      return 'Accomplishment Report';
    case 'LSPU-OSAS-SF-NARRATIVE':
      return 'Narrative Report';
    case 'LSPU-OSAS-SF-BYLAWS':
      return 'Constitution & By-Laws';
    case 'LSPU-OSAS-SF-FINANCIAL':
      return 'Financial Report';
    case 'LSPU-ACAD-RL':
      return 'Event Letter';
    default:
      return formType;
  }
};

const getPdfRoute = (app, action = 'download') => {
  const queryParams = action === 'view' ? '?action=view' : '';

  // Direct-upload forms: no generated PDF route needed
  const directUploadTypes = [
    'LSPU-OSAS-SF-ACCOMPLISHMENT',
    'LSPU-OSAS-SF-NARRATIVE',
    'LSPU-OSAS-SF-BYLAWS',
    'LSPU-OSAS-SF-FINANCIAL',
    'LSPU-ACAD-RL',
  ];
  if (directUploadTypes.includes(app.form_type)) {
    // No PDF route for these types
    return null;
  }

  // Check the form type directly
  if (app.form_type === 'LSPU-OSAS-SF-002') {
    return `/applications/${app.id}/export-renewal${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-001') {
    return `/applications/${app.id}/pdf${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-003') {
    return `/applications/${app.id}/export-commitment${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-004') {
    return `/applications/${app.id}/export-plan${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-006') {
    return `/applications/${app.id}/export-certification${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-005') {
    return `/applications/${app.id}/export-members${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-007') {
    return `/applications/${app.id}/export-officers${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-009') {
    return `/applications/${app.id}/export-attendance${queryParams}`;
  } else if (app.form_type === 'LSPU-OSAS-SF-EVAL') {
    return `/applications/${app.id}/export-evaluation${queryParams}`;
  } else {
    // Default case: do not warn for unknown direct-upload types
    return `/applications/${app.id}/pdf${queryParams}`;
  }
};

const getReportPath = (app) => {
  let path = null;
  switch(app.form_type) {
    case 'LSPU-OSAS-SF-ACCOMPLISHMENT':
      path = app.accomplishment_report_path;
      break;
    case 'LSPU-OSAS-SF-NARRATIVE':
      path = app.narrative_report_path;
      break;
    case 'LSPU-OSAS-SF-BYLAWS':
      path = app.bylaws_path;
      break;
    case 'LSPU-OSAS-SF-FINANCIAL':
      path = app.financial_report_path;
      break;
    case 'LSPU-ACAD-RL':
      path = app.event_letter_path;
      break;
    default:
      path = app.signed_document_path;
  }
  return path && path !== '' ? path : null;
};

const getViewUrl = (app) => {
  // For direct-upload forms, link directly to the PDF
  const reportPath = getReportPath(app);
  if ([
    'LSPU-OSAS-SF-ACCOMPLISHMENT',
    'LSPU-OSAS-SF-NARRATIVE',
    'LSPU-OSAS-SF-BYLAWS',
    'LSPU-OSAS-SF-FINANCIAL',
    'LSPU-ACAD-RL',
  ].includes(app.form_type) && reportPath) {
    return `/storage/${reportPath}`;
  }
  // Otherwise, use the generated PDF route (if available)
  const pdfRoute = getPdfRoute(app, 'view');
  return pdfRoute ? pdfRoute : '#';
};

const openPreview = (app) => {
  previewApp.value = app;
  showPreviewModal.value = true;
};

const closePreviewModal = () => {
  showPreviewModal.value = false;
  previewApp.value = null;
};

const openPreviewInNewWindow = () => {
  if (typeof window !== 'undefined' && previewApp.value) {
    const url = getViewUrl(previewApp.value);
    if (url && url !== '#') {
      window.open(url, '_blank');
    }
  }
};

const viewPdf = (app) => {
  const url = getViewUrl(app);
  if (url && url !== '#') {
    // For mobile screens, open in new window
    if (window.innerWidth < 640) {
      window.open(url, '_blank');
    } else {
      // For desktop screens, use modal
      openPreview(app);
    }
  }
};

// Watch for modal open/close to lock body scroll
watch(showPreviewModal, (val) => {
  if (val) {
    document.body.classList.add('overflow-hidden');
  } else {
    document.body.classList.remove('overflow-hidden');
  }
});
</script>

<template>
    <Head title="Archive Management" />

    <AuthenticatedLayout>
        <template #header>
            <!-- Segmented Animated Color Banner for Consistency -->
            <div class="flex w-full overflow-hidden shadow-md rounded-t-lg mb-2">
              <div class="w-1/4 h-1.5 bg-blue-600 animate-pulse" style="animation-delay: 0.2s;"></div>
              <div class="w-1/4 h-1.5 bg-green-500 animate-pulse" style="animation-delay: 0.4s;"></div>
              <div class="w-1/4 h-1.5 bg-amber-500 animate-pulse" style="animation-delay: 0.6s;"></div>
              <div class="w-1/4 h-1.5 bg-red-500 animate-pulse" style="animation-delay: 0.8s;"></div>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 w-full">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Archive Management
                </h2>
            </div>
        </template>

        <!-- Success/Error Messages -->
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
            {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded">
            {{ errorMessage }}
        </div>

        <!-- Filters -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 mb-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4">Filters</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Organization</label>
                            <SelectInput
                                v-model="filterForm.user_filter"
                                :options="[
                                    { value: '', label: 'All Organizations' },
                                    ...users.map(user => ({ value: user.id.toString(), label: user.name }))
                                ]"
                                class="w-full"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Academic Year</label>
                            <SelectInput
                                v-model="filterForm.academic_year_filter"
                                :options="[
                                    { value: '', label: 'All Years' },
                                    ...academicYears.map(year => ({ value: year, label: year }))
                                ]"
                                class="w-full"
                            />
                        </div>
                        <div class="flex flex-col justify-end sm:col-span-2 lg:col-span-2">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <PrimaryButton @click="applyFilters" class="flex-1 justify-center">
                                    Apply Filters
                                </PrimaryButton>
                                <SecondaryButton @click="clearFilters" class="flex-1 justify-center">
                                    Clear
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Archived Applications -->
        <div class="relative">
            <!-- Colored banner -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <div class="flex w-full overflow-hidden shadow-md rounded-t-2xl">
                    <div class="w-1/4 h-1.5 bg-blue-600 animate-pulse rounded-tl-2xl" style="animation-delay: 0.2s;"></div>
                    <div class="w-1/4 h-1.5 bg-green-500 animate-pulse" style="animation-delay: 0.4s;"></div>
                    <div class="w-1/4 h-1.5 bg-amber-500 animate-pulse" style="animation-delay: 0.6s;"></div>
                    <div class="w-1/4 h-1.5 bg-red-500 animate-pulse rounded-tr-2xl" style="animation-delay: 0.8s;"></div>
                </div>
            </div>

            <div v-if="archivedApplications.length === 0" class="text-center py-8 max-w-4xl mx-auto px-4 sm:px-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="mt-4 text-gray-600 dark:text-gray-400">No archived applications found.</p>
            </div>

            <!-- MOBILE CARD LAYOUT -->
            <div v-if="archivedApplications.length > 0" class="sm:hidden p-2 space-y-4 max-w-4xl mx-auto">
                <div v-for="application in archivedApplications" :key="application.id" 
          class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-4 flex flex-col gap-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition"
          @click="viewPdf(application)">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#2563eb"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h168q13-36 43.5-58t68.5-22q38 0 68.5 22t43.5 58h168q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm80-80h280v-80H280v80Zm0-160h400v-80H280v80Zm0-160h400v-80H280v80Zm200-190q13 0 21.5-8.5T510-820q0-13-8.5-21.5T480-850q-13 0-21.5 8.5T450-820q0 13 8.5 21.5T480-790ZM200-200v-560 560Z"/></svg>
              <div class="text-base font-semibold text-gray-800 dark:text-gray-200">{{ formTypeToName(application.form_type) }}</div>
            </div>
            <span :class="`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${getStatusColor(application.status)}`">
              {{ application.status }}
            </span>
          </div>
                            <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 font-medium">
                                <span><span class="font-semibold text-gray-700 dark:text-gray-200">Organization:</span> {{ application.user?.name || 'N/A' }}</span>
                                <span><span class="font-semibold text-gray-700 dark:text-gray-200">Academic Year:</span> {{ application.academic_year_archived || 'N/A' }}</span>
                            </div>
              <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 font-medium">
                <span><span class="font-semibold text-gray-700 dark:text-gray-200">Archived At:</span> {{ formatDate(application.archived_at) }}</span>
              </div>
                    <div class="flex gap-2 mt-2 flex-wrap">
                        <button
                            @click.stop="toggleDropdown(application, $event)"
                            :aria-label="'Actions for ' + getFormTypeLabel(application.form_type)"
                            class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-400 transition group"
                            :data-dropdown-trigger="application.id"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <circle cx="10" cy="4" r="2.2"/>
                                <circle cx="10" cy="10" r="2.2"/>
                                <circle cx="10" cy="16" r="2.2"/>
                            </svg>
                            <span class="absolute left-1/2 -bottom-8 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
                                Actions
                            </span>
                        </button>
                    </div>
                    <!-- MOBILE INLINE DROPDOWN -->
                    <div v-if="activeMobileDropdownId === application.id" class="mobile-dropdown-menu mt-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow p-3 flex flex-col gap-2 z-10" @click.stop>
              <!-- View PDF removed for archive actions -->
                        <button 
                            @click="activeMobileDropdownId = null; confirmRestore(application)" 
                            class="w-full text-left px-2 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l-4-4m0 0l4-4m-4 4h11a4 4 0 110 8h-1" />
                            </svg>
                            Restore
                        </button>
                    </div>
                        </div>
                    </div>

                    <!-- Desktop Stacked List Layout -->
                    <div v-if="archivedApplications.length > 0" class="hidden sm:block p-4 max-w-4xl mx-auto">
                        <div
                            v-for="application in archivedApplications"
                            :key="application.id"
                            class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 mb-4 flex flex-col md:flex-row md:items-center md:justify-between hover:shadow-lg transition cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                            @click="viewPdf(application)"
                        >
                            <div class="flex items-center gap-4 p-5 flex-1 min-w-0">
                                <div class="flex-shrink-0">
                                    <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full p-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="28px" viewBox="0 -960 960 960" width="28px" fill="#2563eb">
                                            <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h168q13-36 43.5-58t68.5-22q38 0 68.5 22t43.5 58h168q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm80-80h280v-80H280v80Zm0-160h400v-80H280v80Zm0-160h400v-80H280v80Zm200-190q13 0 21.5-8.5T510-820q0-13-8.5-21.5T480-850q-13 0-21.5 8.5T450-820q0 13 8.5 21.5T480-790ZM200-200v-560 560Z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-base text-gray-900 dark:text-gray-100">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="truncate flex-shrink-0">{{ formTypeToName(application.form_type) }}</span>
                                            <span class="inline-flex items-center text-sm text-gray-700 dark:text-gray-200 min-w-0">
                                                <svg class="mx-1 text-gray-400 dark:text-gray-500 w-2.5 h-2.5 flex-shrink-0" width="10" height="10" viewBox="0 0 10 10" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><polygon points="0,0 10,5 0,10"/></svg>
                                                <span class="truncate max-w-[40ch]">{{ application.user?.name || 'N/A' }}</span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 font-medium truncate">{{ application.form_type }}</div>
                                    <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">
                                        <span><span class="font-semibold text-gray-700 dark:text-gray-200">Academic Year:</span> {{ application.academic_year_archived || 'N/A' }}</span>
                                        <span>&bull; <span :class="`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${getStatusColor(application.status)}`">{{ application.status }}</span></span>
                                        <span><span class="font-semibold text-gray-700 dark:text-gray-200">Archived At:</span> {{ formatDate(application.archived_at) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 p-5 pt-0 md:pt-5 md:pl-0 md:pr-6 md:flex-col md:items-end">
                                <button
                                    @click.stop="toggleDropdown(application, $event)"
                                    :aria-label="'Actions for ' + getFormTypeLabel(application.form_type)"
                                    class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-400 transition group"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <circle cx="10" cy="4" r="2.2"/>
                                        <circle cx="10" cy="10" r="2.2"/>
                                        <circle cx="10" cy="16" r="2.2"/>
                                    </svg>
                                    <span class="absolute left-1/2 -bottom-8 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
                                        Actions
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
        </div>

        <!-- Render the dropdown only once, outside the table -->
        <Teleport to="body">
            <div 
                ref="dropdownRef"
                v-if="activeDropdownApp"
                class="fixed z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-full max-w-xs sm:w-64"
                :style="{ top: `${dropdownPosition.top}px`, left: `${dropdownPosition.left}px`, visibility: activeDropdownApp ? 'visible' : 'hidden' }"
                @click.stop
            >
        <!-- View PDF option removed for archive actions -->
                <!-- Restore option -->
                <button 
                    @click="confirmRestore(activeDropdownApp); activeDropdownApp = null;"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200 font-medium"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l-4-4m0 0l4-4m-4 4h11a4 4 0 110 8h-1" />
                    </svg>
                    Restore
                </button>
            </div>
        </Teleport>

        <!-- Restore Confirmation Modal -->
        <Modal :show="showRestoreModal" @close="showRestoreModal = false">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Restore Application
                        </h3>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Are you sure you want to restore this application? It will become active again and users will be able to edit it.
                    </p>
                </div>
                <div class="mt-4 flex justify-end space-x-3">
                    <SecondaryButton @click="showRestoreModal = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="restoreApplication" class="bg-green-600 hover:bg-green-700">
                        Restore
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

                <!-- Floating Back to top button -->
                        <button
                            v-if="showBackToTop"
                            @click="scrollToTop"
                            aria-label="Back to top"
                            class="fixed z-50 right-6 bottom-8 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 shadow-lg hover:shadow-2xl rounded-full p-3 transition transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            title="Back to top"
                        >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 01.707.293l5 5a1 1 0 01-1.414 1.414L10 7.414 5.707 11.707A1 1 0 014.293 10.293l5-5A1 1 0 0110 5z" clip-rule="evenodd" />
                    </svg>
                </button>

                <!-- Add a subtle, center-aligned back link at the bottom -->
                <div class="flex justify-center mt-10 mb-6">
          <Link
            :href="route('applications.index')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg shadow-sm transition duration-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
            aria-label="Back to Applications"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <span>Back to Applications</span>
          </Link>
        </div>

        <!-- PDF Preview Modal (exact copy from ApplicationsTable.vue) -->
        <transition name="fade">
          <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60" @click="closePreviewModal">
            <div
              class="relative bg-transparent shadow-2xl flex flex-col w-[95vw] max-w-4xl md:w-[70vw] md:max-w-3xl lg:w-[60vw] lg:max-w-4xl xl:w-[50vw] xl:max-w-5xl h-[75vh] md:h-[85vh] lg:h-[90vh] xl:h-[95vh] max-h-[95vh] overflow-hidden border border-transparent"
              @click.stop
            >
              <!-- Close Button: floating at top-right, outside header -->
              <button
                @click="closePreviewModal"
                class="absolute top-4 right-4 flex items-center justify-center text-white hover:text-gray-200 focus:outline-none transition z-20 opacity-90"
                title="Close Preview"
                aria-label="Close Preview"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <!-- Header -->
              <div class="flex items-center justify-between px-4 py-3 pr-16 bg-transparent relative">
                <div class="font-semibold text-gray-200 text-base truncate opacity-90">
                  {{ previewApp ? formTypeToName(previewApp.form_type) : '' }}
                </div>
                <div class="flex items-center gap-2">
                  <button
                    v-if="previewApp"
                    @click="openPreviewInNewWindow"
                    class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 text-sm font-medium text-white rounded-xl shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group"
                    title="Open in New Window"
                    aria-label="Open in New Window"
                  >
                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7v7m0 0L10 21l-7-7 11-11z" />
                    </svg>
                    New Window
                  </button>
                </div>
              </div>
              <!-- PDF Iframe -->
              <div class="flex-1 w-full h-full flex items-center justify-center bg-gray-100">
                <iframe
                  v-if="previewApp"
                  :src="getViewUrl(previewApp)"
                  class="w-full h-full border-0 bg-white"
                  style="min-height: 300px;"
                  allowfullscreen
                  title="PDF Preview"
                >
                </iframe>
                <!-- Fallback message for browsers that don't support iframes (shown outside iframe) -->
                <div v-if="previewApp" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                  <p class="text-gray-500 text-sm pointer-events-auto hidden">
                    Your browser does not support PDFs. 
                    <a :href="getViewUrl(previewApp)" class="text-blue-600 hover:underline">Download the PDF</a>.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </transition>
    </AuthenticatedLayout>
</template> 