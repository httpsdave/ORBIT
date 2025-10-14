<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SidebarLayout from '@/Components/Layout/Sidebar/SidebarLayout.vue';

const props = defineProps({
  activities: {
    type: Array,
    required: true,
  },
  totalActivities: {
    type: Number,
    required: true,
  },
  isAdmin: {
    type: Boolean,
    default: true,
  },
});

// Search and filter state
const searchQuery = ref('');
const statusFilter = ref('all');
const dateFilter = ref('nearest'); // 'nearest', 'upcoming', 'past', 'submission-newest', 'submission-oldest'
const organizationFilter = ref('');

const tableColumns = [
  { key: 'organization', label: 'Organization', adminOnly: true, type: 'text', multiSelect: true },
  { key: 'objective', label: 'Objective', type: 'text', multiSelect: false },
  { key: 'activity_name', label: 'Activity', type: 'text', multiSelect: false },
  { key: 'description', label: 'Brief Description', type: 'text', multiSelect: false },
  { key: 'persons_involved', label: 'Persons Involved', type: 'text', multiSelect: false },
  { key: 'target_date', label: 'Target Date', type: 'date', multiSelect: false },
  { key: 'budget', label: 'Budget', type: 'number', multiSelect: false },
  { key: 'target_participants', label: 'Target Participants', type: 'number', multiSelect: false },
  { key: 'status', label: 'Status', type: 'text', multiSelect: true },
];

const filterOperators = {
  text: [
    { value: 'contains', label: 'Contains' },
    { value: 'equals', label: 'Equals' },
    { value: 'startsWith', label: 'Starts With' },
    { value: 'endsWith', label: 'Ends With' },
  ],
  number: [
    { value: 'equals', label: 'Equals' },
    { value: 'greaterThan', label: 'Greater Than' },
    { value: 'lessThan', label: 'Less Than' },
  ],
  date: [
    { value: 'on', label: 'On' },
    { value: 'before', label: 'Before' },
    { value: 'after', label: 'After' },
  ],
};

const createDefaultColumnFilters = () => {
  return tableColumns.reduce((acc, column) => {
    acc[column.key] = {
      operator: filterOperators[column.type][0].value,
      value: column.multiSelect ? [] : '',
    };
    return acc;
  }, {});
};

const columnFilters = ref(createDefaultColumnFilters());
const activeFilterDropdown = ref(null);
const sortState = ref({ column: null, direction: null });

const visibleColumns = computed(() => tableColumns.filter(column => !column.adminOnly || props.isAdmin));
const columnCount = computed(() => visibleColumns.value.length);

const parseNumericValue = (value) => {
  if (value === null || value === undefined || value === '' || value === 'N/A') {
    return null;
  }

  if (typeof value === 'number' && Number.isFinite(value)) {
    return value;
  }

  const numericString = value.toString().replace(/[^0-9.-]/g, '');
  const parsed = Number(numericString);
  return Number.isNaN(parsed) ? null : parsed;
};

// Get unique values for multi-select columns
const getUniqueColumnValues = (columnKey) => {
  const values = new Set();
  props.activities.forEach(activity => {
    const value = activity[columnKey];
    if (value !== null && value !== undefined && value !== '') {
      values.add(value);
    }
  });
  return Array.from(values).sort();
};

const closeFilterDropdown = () => {
  activeFilterDropdown.value = null;
};

const handleClickOutside = (event) => {
  if (!event.target.closest('.column-filter-wrapper')) {
    closeFilterDropdown();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Get unique organizations from activities
const organizationOptions = computed(() => {
  const uniqueOrgs = [...new Set(props.activities.map(activity => activity.organization))];
  return uniqueOrgs.sort().map(org => ({ value: org, label: org }));
});

// Pagination state
const currentPage = ref(1);
const activitiesPerPage = ref(50);
const pageSizeOptions = [10, 25, 50, 100];

// Filtered activities based on search, status, and organization
const filteredActivities = computed(() => {
  let filtered = [...props.activities];

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(activity => 
      activity.organization.toLowerCase().includes(query) ||
      activity.objective.toLowerCase().includes(query) ||
      activity.activity_name.toLowerCase().includes(query) ||
      activity.description.toLowerCase().includes(query) ||
      activity.persons_involved.toLowerCase().includes(query)
    );
  }

  // Apply status filter
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(activity => 
      activity.status.toLowerCase() === statusFilter.value.toLowerCase()
    );
  }

  // Apply organization filter
  if (organizationFilter.value) {
    filtered = filtered.filter(activity => 
      activity.organization === organizationFilter.value
    );
  }

  // Apply date sorting/filtering
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (dateFilter.value === 'upcoming') {
    // Only show upcoming events
    filtered = filtered.filter(activity => {
      const targetDate = new Date(activity.target_date);
      targetDate.setHours(0, 0, 0, 0);
      return targetDate >= today;
    });
    // Sort by target date ascending (soonest first)
    filtered.sort((a, b) => new Date(a.target_date) - new Date(b.target_date));
  } else if (dateFilter.value === 'past') {
    // Only show past events
    filtered = filtered.filter(activity => {
      const targetDate = new Date(activity.target_date);
      targetDate.setHours(0, 0, 0, 0);
      return targetDate < today;
    });
    // Sort by target date descending (most recent past first)
    filtered.sort((a, b) => new Date(b.target_date) - new Date(a.target_date));
  } else if (dateFilter.value === 'submission-newest') {
    // Sort by submission date descending (newest first)
    filtered.sort((a, b) => new Date(b.target_date) - new Date(a.target_date));
  } else if (dateFilter.value === 'submission-oldest') {
    // Sort by submission date ascending (oldest first)
    filtered.sort((a, b) => new Date(a.target_date) - new Date(b.target_date));
  } else {
    // Default: 'nearest' - sort by proximity to today (nearest events first)
    filtered.sort((a, b) => {
      const dateA = new Date(a.target_date);
      const dateB = new Date(b.target_date);
      
      // Calculate absolute difference from today
      const diffA = Math.abs(today - dateA);
      const diffB = Math.abs(today - dateB);
      
      // If different distances, closer one comes first
      if (diffA !== diffB) {
        return diffA - diffB;
      }
      
      // If same distance, upcoming before past
      return dateB - dateA;
    });
  }

  // Apply column-level filters
  filtered = filtered.filter(activity => {
    return tableColumns.every(column => {
      if (column.adminOnly && !props.isAdmin) {
        return true;
      }

      const { operator, value } = columnFilters.value[column.key];
      if (!value) {
        return true;
      }

      // Handle multi-select
      if (column.multiSelect && Array.isArray(value)) {
        if (value.length === 0) {
          return true;
        }
        const activityValue = activity[column.key];
        if (activityValue === undefined || activityValue === null) {
          return false;
        }
        return value.includes(activityValue.toString());
      }

      const activityValue = activity[column.key];
      if (activityValue === undefined || activityValue === null) {
        return false;
      }

      if (column.type === 'text') {
        const recordStr = activityValue.toString().toLowerCase();
        const searchStr = value.toString().toLowerCase();
        if (operator === 'contains') return recordStr.includes(searchStr);
        if (operator === 'equals') return recordStr === searchStr;
        if (operator === 'startsWith') return recordStr.startsWith(searchStr);
        if (operator === 'endsWith') return recordStr.endsWith(searchStr);
        return true;
      }

      if (column.type === 'number') {
        const numericRecord = parseNumericValue(activityValue);
        const numericFilter = parseNumericValue(value);
        if (numericRecord === null || numericFilter === null) {
          return false;
        }
        if (operator === 'equals') return numericRecord === numericFilter;
        if (operator === 'greaterThan') return numericRecord > numericFilter;
        if (operator === 'lessThan') return numericRecord < numericFilter;
        return true;
      }

      if (column.type === 'date') {
        const recordDate = new Date(activityValue);
        const filterDate = new Date(value);
        if (Number.isNaN(recordDate.getTime()) || Number.isNaN(filterDate.getTime())) {
          return false;
        }
        recordDate.setHours(0, 0, 0, 0);
        filterDate.setHours(0, 0, 0, 0);
        if (operator === 'on') return recordDate.getTime() === filterDate.getTime();
        if (operator === 'before') return recordDate.getTime() < filterDate.getTime();
        if (operator === 'after') return recordDate.getTime() > filterDate.getTime();
        return true;
      }

      return true;
    });
  });

  // Apply column sorting if active
  if (sortState.value.column && sortState.value.direction) {
    const column = tableColumns.find(col => col.key === sortState.value.column);
    if (column) {
      const directionMultiplier = sortState.value.direction === 'asc' ? 1 : -1;
      filtered = [...filtered].sort((a, b) => {
        const aVal = a[column.key];
        const bVal = b[column.key];

        if (column.type === 'number') {
          const aNum = parseNumericValue(aVal);
          const bNum = parseNumericValue(bVal);
          if (aNum === null && bNum === null) return 0;
          if (aNum === null) return 1 * directionMultiplier;
          if (bNum === null) return -1 * directionMultiplier;
          return (aNum - bNum) * directionMultiplier;
        }

        if (column.type === 'date') {
          return (new Date(aVal) - new Date(bVal)) * directionMultiplier;
        }

        const aStr = aVal?.toString().toLowerCase() ?? '';
        const bStr = bVal?.toString().toLowerCase() ?? '';
        if (aStr < bStr) return -1 * directionMultiplier;
        if (aStr > bStr) return 1 * directionMultiplier;
        return 0;
      });
    }
  }

  return filtered;
});

// Pagination computed properties
const totalPages = computed(() => {
  if (activitiesPerPage.value <= 0) return 1;
  return Math.max(1, Math.ceil(filteredActivities.value.length / activitiesPerPage.value));
});
const currentPageActivities = computed(() => {
  const start = (currentPage.value - 1) * activitiesPerPage.value;
  return filteredActivities.value.slice(start, start + activitiesPerPage.value);
});

const displayRange = computed(() => {
  const total = filteredActivities.value.length;
  if (total === 0) {
    return { start: 0, end: 0 };
  }

  const start = (currentPage.value - 1) * activitiesPerPage.value + 1;
  const end = Math.min(start + activitiesPerPage.value - 1, total);
  return { start, end };
});

// Visible pages for pagination controls
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

// Navigation functions - stay at current scroll position
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

// Watch for filter changes and reset to page 1
watch([searchQuery, statusFilter, dateFilter, organizationFilter], () => {
  currentPage.value = 1;
});

watch(columnFilters, () => {
  currentPage.value = 1;
}, { deep: true });

watch(sortState, () => {
  currentPage.value = 1;
}, { deep: true });

watch(activitiesPerPage, (value) => {
  if (!value || value <= 0) {
    activitiesPerPage.value = pageSizeOptions[0];
    return;
  }
  currentPage.value = 1;
});

watch(totalPages, (newTotal) => {
  if (currentPage.value > newTotal) {
    currentPage.value = newTotal;
  }
});

// Format currency
const formatCurrency = (amount) => {
  if (!amount || amount === 'N/A') return 'N/A';
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2
  }).format(amount);
};

// Get status badge color
const getStatusColor = (status) => {
  switch(status?.toLowerCase()) {
    case 'approved':
      return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
    case 'pending':
      return 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300';
    case 'disapproved':
      return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
    default:
      return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
  }
};

// Check if date is in the past
const isPastDate = (dateString) => {
  const targetDate = new Date(dateString);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return targetDate < today;
};

const toggleFilterDropdown = (columnKey) => {
  activeFilterDropdown.value = activeFilterDropdown.value === columnKey ? null : columnKey;
};

const updateSort = (columnKey, direction) => {
  sortState.value = direction ? { column: columnKey, direction } : { column: null, direction: null };
};

const cycleSort = (columnKey) => {
  if (sortState.value.column !== columnKey) {
    updateSort(columnKey, 'asc');
  } else if (sortState.value.direction === 'asc') {
    updateSort(columnKey, 'desc');
  } else {
    updateSort(columnKey, null);
  }
};

const clearColumnFilter = (columnKey) => {
  const column = tableColumns.find(col => col.key === columnKey);
  columnFilters.value[columnKey].value = column?.multiSelect ? [] : '';
  columnFilters.value[columnKey].operator = filterOperators[column.type][0].value;
};

const hasActiveColumnFilter = (columnKey) => {
  const value = columnFilters.value[columnKey].value;
  return Array.isArray(value) ? value.length > 0 : !!value;
};

const isColumnSorted = (columnKey, direction) => {
  if (!sortState.value.column) return false;
  if (sortState.value.column !== columnKey) return false;
  if (!direction) return true;
  return sortState.value.direction === direction;
};

const toggleMultiSelectValue = (columnKey, value) => {
  const filterValue = columnFilters.value[columnKey].value;
  const index = filterValue.indexOf(value);
  if (index === -1) {
    filterValue.push(value);
  } else {
    filterValue.splice(index, 1);
  }
};

const selectAllMultiSelect = (columnKey) => {
  const uniqueValues = getUniqueColumnValues(columnKey);
  columnFilters.value[columnKey].value = [...uniqueValues];
};

const deselectAllMultiSelect = (columnKey) => {
  columnFilters.value[columnKey].value = [];
};

// Export to PDF and DOCX with Preview Modals
const isExporting = ref(false);
const isExportingDocx = ref(false);
const showPdfPreviewModal = ref(false);
const showDocxPreviewModal = ref(false);
const pdfPreviewUrl = ref(null);
const docxPreviewUrl = ref(null);
const showExportDropdown = ref(false);

// Function to build URL params
const buildExportParams = () => {
  const params = new URLSearchParams();
  
  // Add basic filter parameters
  if (searchQuery.value) params.append('search', searchQuery.value);
  if (statusFilter.value && statusFilter.value !== 'all') params.append('status', statusFilter.value);
  if (organizationFilter.value) params.append('organization', organizationFilter.value);
  
  // Add column filters
  Object.entries(columnFilters.value).forEach(([key, filter]) => {
    if (filter.value && !Array.isArray(filter.value) && filter.value !== '') {
      params.append(`filter_${key}`, filter.value);
      params.append(`filter_${key}_op`, filter.operator);
    } else if (Array.isArray(filter.value) && filter.value.length > 0) {
      params.append(`filter_${key}`, filter.value.join(','));
      params.append(`filter_${key}_op`, 'in');
    }
  });
  
  // Add sort parameters
  if (sortState.value.column) {
    params.append('sort_column', sortState.value.column);
    params.append('sort_direction', sortState.value.direction);
  }
  
  return params;
};

const exportToPdf = async () => {
  isExporting.value = true;
  showExportDropdown.value = false;
  
  try {
    const baseUrl = props.isAdmin ? '/admin/plan-of-activities/export-pdf' : '/plan-of-activities/export-pdf';
    const params = buildExportParams();
    
    // Add action=view to display inline instead of download
    params.append('action', 'view');
    
    const pdfUrl = `${baseUrl}?${params.toString()}`;
    pdfPreviewUrl.value = pdfUrl;
    showPdfPreviewModal.value = true;
  } catch (error) {
    console.error('PDF Preview Error:', error);
    alert('Failed to generate PDF preview. Please try again.');
  } finally {
    isExporting.value = false;
  }
};

const closePdfPreviewModal = () => {
  showPdfPreviewModal.value = false;
  pdfPreviewUrl.value = null;
};

const downloadPdfFromPreview = () => {
  if (pdfPreviewUrl.value) {
    // Remove action=view parameter for download
    const downloadUrl = pdfPreviewUrl.value.replace('action=view&', '').replace('&action=view', '').replace('action=view', '');
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `plan-of-activities-${new Date().toISOString().split('T')[0]}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
};

const docxHtmlContent = ref('');
const docxZoom = ref(100);

const exportToDocx = async () => {
  isExportingDocx.value = true;
  showExportDropdown.value = false;
  
  try {
    const baseUrl = props.isAdmin ? '/admin/plan-of-activities/export-docx' : '/plan-of-activities/export-docx';
    const params = buildExportParams();
    
    // Add action=view to display inline instead of download
    params.append('action', 'view');
    
    const docxUrl = `${baseUrl}?${params.toString()}`;
    docxPreviewUrl.value = docxUrl;
    
    // Fetch the HTML content
    const response = await fetch(docxUrl);
    const html = await response.text();
    docxHtmlContent.value = html;
    docxZoom.value = 100; // Reset zoom
    
    showDocxPreviewModal.value = true;
  } catch (error) {
    console.error('DOCX Preview Error:', error);
    alert('Failed to generate DOCX preview. Please try again.');
  } finally {
    isExportingDocx.value = false;
  }
};

const closeDocxPreviewModal = () => {
  showDocxPreviewModal.value = false;
  docxPreviewUrl.value = null;
  docxHtmlContent.value = '';
  docxZoom.value = 100;
};

const downloadDocxFromPreview = () => {
  if (docxPreviewUrl.value) {
    // Remove action=view parameter for download
    const downloadUrl = docxPreviewUrl.value.replace('action=view&', '').replace('&action=view', '').replace('action=view', '');
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `plan-of-activities-${new Date().toISOString().split('T')[0]}.doc`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
};

const zoomIn = () => {
  if (docxZoom.value < 200) {
    docxZoom.value += 10;
  }
};

const zoomOut = () => {
  if (docxZoom.value > 50) {
    docxZoom.value -= 10;
  }
};

const resetZoom = () => {
  docxZoom.value = 100;
};

// Close dropdown when clicking outside
const closeDropdownOnClickOutside = (event) => {
  if (!event.target.closest('.export-dropdown-container')) {
    showExportDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', closeDropdownOnClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdownOnClickOutside);
});
</script>

<template>
  <Head title="Plan of Activities" />

  <SidebarLayout :is-admin="isAdmin">
    <!-- Colored Banner -->
    <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
      <div class="w-1/4 h-1.5 bg-blue-500" style="animation-delay: 0.2s;"></div>
      <div class="w-1/4 h-1.5 bg-green-500" style="animation-delay: 0.4s;"></div>
      <div class="w-1/4 h-1.5 bg-yellow-500" style="animation-delay: 0.6s;"></div>
      <div class="w-1/4 h-1.5 bg-red-500" style="animation-delay: 0.8s;"></div>
    </div>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
      <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 py-8">
        <!-- Header Section -->
          <div class="mb-8">
          <div class="flex items-center justify-between mb-2">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ isAdmin ? 'Plan of Activities' : 'My Plan of Activities' }}
              </h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ isAdmin ? 'Overview of all planned activities from submitted Plan of Activities forms' : 'Overview of your planned activities' }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <!-- Export Dropdown -->
              <div class="relative export-dropdown-container">
                <button
                  @click="showExportDropdown = !showExportDropdown"
                  :disabled="filteredActivities.length === 0"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 disabled:from-gray-400 disabled:to-gray-500 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-blue-300/30 transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-60"
                  :title="filteredActivities.length === 0 ? 'No data to export' : 'Export filtered activities'"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                  </svg>
                  <span>Export</span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" :class="{ 'rotate-180': showExportDropdown }" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>

                <!-- Dropdown Menu -->
                <transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <div
                    v-if="showExportDropdown"
                    class="absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50"
                  >
                    <div class="py-1">
                      <button
                        @click="exportToPdf"
                        :disabled="isExporting"
                        class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <!-- PDF file icon (red) -->
                        <svg v-if="!isExporting" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"></path>
                          <polyline points="14 2 14 8 20 8" stroke-linecap="round" stroke-linejoin="round"></polyline>
                          <rect x="7" y="12" width="3" height="2" fill="currentColor" class="opacity-90"></rect>
                          <rect x="11.5" y="12" width="3" height="2" fill="currentColor" class="opacity-90"></rect>
                          <rect x="16" y="12" width="1.5" height="2" fill="currentColor" class="opacity-90"></rect>
                        </svg>
                        <svg v-else class="animate-spin h-5 w-5 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                          <div class="font-medium">{{ isExporting ? 'Exporting...' : 'Export as PDF' }}</div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">View and download PDF format</div>
                        </div>
                      </button>
                      
                      <button
                        @click="exportToDocx"
                        :disabled="isExportingDocx"
                        class="w-full text-left px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 flex items-center gap-3 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <!-- DOCX/Word file icon (blue) -->
                        <svg v-if="!isExportingDocx" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"></path>
                          <polyline points="14 2 14 8 20 8" stroke-linecap="round" stroke-linejoin="round"></polyline>
                          <!-- Stylized W for Word -->
                          <path d="M8.2 15.5l1.3-6 1.7 5.2 1.7-5.2 1.3 6" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <svg v-else class="animate-spin h-5 w-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                          <div class="font-medium">{{ isExportingDocx ? 'Exporting...' : 'Export as DOCX' }}</div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">View and download Word format</div>
                        </div>
                      </button>
                    </div>
                  </div>
                </transition>
              </div>
              
              <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total Activities:</span>
                <span class="ml-2 text-lg font-bold bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent">{{ totalActivities }}</span>
              </div>
            </div>
          </div>

          <!-- Mobile-only Filter Section -->
          <div class="max-w-4xl mx-auto px-3 sm:px-6 mb-6 space-y-3 block md:hidden">
            <!-- Filter Bar - Mobile Only -->
            <div class="grid grid-cols-2 gap-2">
              <!-- Status Filter -->
              <div class="col-span-1">
                <select 
                  v-model="statusFilter"
                  class="w-full pl-2.5 pr-7 py-1.5 border border-gray-300 dark:border-gray-600 rounded-full text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
                >
                  <option value="all">All Status</option>
                  <option value="approved">Approved</option>
                  <option value="pending">Pending</option>
                  <option value="disapproved">Disapproved</option>
                </select>
              </div>

              <!-- Organization Filter (Admin Only) -->
              <div v-if="isAdmin" class="col-span-1">
                <select 
                  v-model="organizationFilter"
                  class="w-full pl-2.5 pr-7 py-1.5 border border-gray-300 dark:border-gray-600 rounded-full text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
                  title="Filter by Organization"
                >
                  <option value="">All Organizations</option>
                  <option 
                    v-for="option in organizationOptions" 
                    :key="option.value" 
                    :value="option.value"
                    :title="option.label.length > 20 ? option.label : undefined"
                  >
                    {{ option.label.length > 20 ? option.label.substring(0, 20) + '...' : option.label }}
                  </option>
                </select>
              </div>

              <!-- Date Filter -->
              <div :class="isAdmin ? 'col-span-2' : 'col-span-1'">
                <select 
                  v-model="dateFilter"
                  class="w-full pl-2.5 pr-7 py-1.5 border border-gray-300 dark:border-gray-600 rounded-full text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
                  title="Sort by Date"
                >
                  <option value="nearest">Nearest Events</option>
                  <option value="upcoming">Upcoming Events Only</option>
                  <option value="past">Past Events Only</option>
                  <option value="submission-newest">Newest Submission</option>
                  <option value="submission-oldest">Oldest Submission</option>
                </select>
              </div>
            </div>

            <!-- Active Filters Display - Mobile Only -->
            <div v-if="searchQuery || statusFilter !== 'all' || (isAdmin && organizationFilter) || dateFilter !== 'nearest'" class="flex flex-wrap gap-1.5 items-center text-xs">
              <span class="text-gray-600 dark:text-gray-400 font-medium text-xs">Active:</span>
              <span v-if="searchQuery" class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-xs">
                "{{ searchQuery.length > 15 ? searchQuery.substring(0, 15) + '...' : searchQuery }}"
              </span>
              <span v-if="statusFilter !== 'all'" class="px-1.5 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-md text-xs">
                {{ statusFilter }}
              </span>
              <span v-if="isAdmin && organizationFilter" class="px-1.5 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-md text-xs truncate max-w-[120px]" :title="organizationOptions.find(opt => opt.value === organizationFilter)?.label">
                {{ organizationOptions.find(opt => opt.value === organizationFilter)?.label && organizationOptions.find(opt => opt.value === organizationFilter)?.label.length > 15 ? organizationOptions.find(opt => opt.value === organizationFilter)?.label.substring(0, 15) + '...' : organizationOptions.find(opt => opt.value === organizationFilter)?.label }}
              </span>
              <span v-if="dateFilter !== 'nearest'" class="px-1.5 py-0.5 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-md text-xs">
                {{ dateFilter === 'upcoming' ? 'Upcoming Only' : dateFilter === 'past' ? 'Past Only' : dateFilter === 'submission-newest' ? 'Newest First' : 'Oldest First' }}
              </span>
            </div>
          </div>

          <!-- Results count -->
          <div class="mb-6 px-2">
            <div class="text-left text-sm text-gray-600 dark:text-gray-400">
              Showing <span class="font-semibold text-blue-600 dark:text-blue-400">{{ displayRange.start }}-{{ displayRange.end }}</span> of <span class="font-semibold text-blue-600 dark:text-blue-400">{{ filteredActivities.length }}</span> activities
              <span v-if="filteredActivities.length !== totalActivities" class="ml-2 text-gray-500">
                (filtered from {{ totalActivities }} total)
              </span>
            </div>
          </div>
        </div>

        <!-- Table Header - Search Bar (Left) and Entries Per Page (Right) -->
        <div class="mb-4 px-2">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
            <!-- Search Bar - Left Side -->
            <div class="relative w-full sm:w-96 flex-shrink-0">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                type="text"
                v-model="searchQuery"
                class="block w-full pl-9 pr-9 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 transition duration-150 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 shadow-sm"
                placeholder="Search by organization, objective, activity..."
              />
              <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button @click="searchQuery = ''" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Entries Per Page - Right Side -->
            <div class="flex items-center gap-2 justify-center sm:justify-end text-sm text-gray-600 dark:text-gray-400 flex-shrink-0">
              <label class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Entries per page</label>
              <select
                v-model.number="activitiesPerPage"
                class="w-20 pl-2.5 pr-7 py-1.5 sm:pl-3 sm:pr-8 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-full text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
              >
                <option v-for="option in pageSizeOptions" :key="option" :value="option">{{ option }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Pagination Controls - Below Entries Per Page -->
        <div v-if="totalPages > 1" class="flex items-center justify-between mb-4 px-2">
          <!-- Page Info -->
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Page <span class="font-semibold text-gray-900 dark:text-gray-100">{{ currentPage }}</span> of <span class="font-semibold text-gray-900 dark:text-gray-100">{{ totalPages }}</span>
          </div>

          <!-- Pagination Buttons -->
          <nav class="flex items-center gap-2" aria-label="Pagination">
            <!-- Previous Button -->
            <button 
              @click="prevPage" 
              :disabled="currentPage === 1"
              :class="[
                'inline-flex items-center justify-center p-1 text-sm font-medium transition-colors duration-200',
                currentPage === 1
                  ? 'text-gray-300 dark:text-gray-700 cursor-not-allowed'
                  : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'
              ]"
              aria-label="Previous page"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            
            <!-- Page Numbers -->
            <div class="hidden sm:flex items-center gap-1">
              <button 
                v-for="page in visiblePages" 
                :key="page"
                @click="page === '...' ? null : goToPage(page)"
                :disabled="page === '...'"
                :class="[
                  'min-w-[2rem] px-2 py-1 text-sm font-medium transition-colors duration-200',
                  page === '...' 
                    ? 'text-gray-400 dark:text-gray-600 cursor-default' 
                    : currentPage === page 
                      ? 'text-blue-600 dark:text-blue-400 font-bold' 
                      : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'
                ]"
                :aria-label="page === '...' ? 'More pages' : `Go to page ${page}`"
                :aria-current="currentPage === page ? 'page' : undefined"
              >
                {{ page }}
              </button>
            </div>

            <!-- Mobile: Current Page Display -->
            <div class="sm:hidden px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ currentPage }} / {{ totalPages }}
            </div>
            
            <!-- Next Button -->
            <button 
              @click="nextPage" 
              :disabled="currentPage === totalPages"
              :class="[
                'inline-flex items-center justify-center p-1 text-sm font-medium transition-colors duration-200',
                currentPage === totalPages
                  ? 'text-gray-300 dark:text-gray-700 cursor-not-allowed'
                  : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'
              ]"
              aria-label="Next page"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </nav>
        </div>

        <!-- Activities Table (Desktop) & Cards (Mobile) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow duration-300">
          
          <!-- Desktop Table View (hidden on mobile) -->
          <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                <tr>
                  <th
                    v-for="column in visibleColumns"
                    :key="column.key"
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider align-top cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-150"
                    @click="cycleSort(column.key)"
                  >
                    <div class="flex items-center justify-between gap-2">
                      <span class="truncate" :title="column.label">{{ column.label }}</span>
                      <div class="flex items-center gap-1 flex-shrink-0" @click.stop>
                        <button
                          type="button"
                          class="inline-flex items-center justify-center p-1 rounded-md transition-colors duration-150"
                          :class="isColumnSorted(column.key) ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
                          @click.stop="cycleSort(column.key)"
                          :aria-pressed="isColumnSorted(column.key)"
                          :aria-label="`Sort ${column.label}`"
                        >
                          <!-- Ascending Icon -->
                          <svg v-if="isColumnSorted(column.key, 'asc')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M7 12h14M11 18h10" />
                            <circle cx="4" cy="12" r="1" fill="currentColor" />
                            <circle cx="4" cy="18" r="1" fill="currentColor" />
                          </svg>
                          <!-- Descending Icon -->
                          <svg v-else-if="isColumnSorted(column.key, 'desc')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 6h10M7 12h14M3 18h18" />
                            <circle cx="4" cy="6" r="1" fill="currentColor" />
                            <circle cx="4" cy="12" r="1" fill="currentColor" />
                          </svg>
                          <!-- Unsorted Icon (3 bars equal) -->
                          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M3 12h18M3 18h18" />
                          </svg>
                        </button>

                        <div class="relative column-filter-wrapper">
                          <button
                            type="button"
                            class="inline-flex items-center justify-center p-1 rounded-md transition-colors duration-150"
                            :class="hasActiveColumnFilter(column.key) ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            @click.stop="toggleFilterDropdown(column.key)"
                            :aria-expanded="activeFilterDropdown === column.key"
                            :aria-label="`Filter ${column.label}`"
                          >
                            <!-- Filter Icon (funnel) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                          </button>

                          <transition name="fade">
                            <div
                              v-if="activeFilterDropdown === column.key"
                              class="absolute mt-2 w-64 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-40 p-4"
                              :class="column.key === 'organization' || column.key === 'objective' ? 'left-0' : 'right-0'"
                              @click.stop
                            >
                              <div class="flex items-start justify-between gap-2 mb-3">
                                <div>
                                  <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Filter {{ column.label }}</p>
                                  <p class="text-xs text-gray-500 dark:text-gray-400">Apply a condition to this column</p>
                                </div>
                                <button
                                  type="button"
                                  class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                  @click="clearColumnFilter(column.key)"
                                >
                                  Clear
                                </button>
                              </div>

                              <div class="space-y-4">
                                <!-- Multi-Select Mode -->
                                <template v-if="column.multiSelect">
                                  <div>
                                    <div class="flex items-center justify-between mb-2">
                                      <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Select Values</label>
                                      <div class="flex gap-1">
                                        <button
                                          type="button"
                                          class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                          @click="selectAllMultiSelect(column.key)"
                                        >
                                          All
                                        </button>
                                        <span class="text-xs text-gray-400">|</span>
                                        <button
                                          type="button"
                                          class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                          @click="deselectAllMultiSelect(column.key)"
                                        >
                                          None
                                        </button>
                                      </div>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-2 space-y-1">
                                      <label
                                        v-for="option in getUniqueColumnValues(column.key)"
                                        :key="option"
                                        class="flex items-center gap-2 px-2 py-1 hover:bg-gray-50 dark:hover:bg-gray-800 rounded cursor-pointer"
                                      >
                                        <input
                                          type="checkbox"
                                          :checked="columnFilters[column.key].value.includes(option)"
                                          @change="toggleMultiSelectValue(column.key, option)"
                                          class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="text-sm text-gray-700 dark:text-gray-300 truncate" :title="option">{{ option }}</span>
                                      </label>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                      {{ columnFilters[column.key].value.length }} selected
                                    </div>
                                  </div>
                                </template>

                                <!-- Standard Filter Mode -->
                                <template v-else>
                                <div>
                                  <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Condition</label>
                                  <select
                                    v-model="columnFilters[column.key].operator"
                                    class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400"
                                  >
                                    <option
                                      v-for="option in filterOperators[column.type]"
                                      :key="option.value"
                                      :value="option.value"
                                    >
                                      {{ option.label }}
                                    </option>
                                  </select>
                                </div>

                                <div>
                                  <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Value</label>
                                  <template v-if="column.type === 'text'">
                                    <input
                                      type="text"
                                      v-model="columnFilters[column.key].value"
                                      class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400"
                                      placeholder="Enter text"
                                    />
                                  </template>
                                  <template v-else-if="column.type === 'number'">
                                    <input
                                      type="number"
                                      v-model="columnFilters[column.key].value"
                                      class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400"
                                      placeholder="Enter number"
                                    />
                                  </template>
                                  <template v-else>
                                    <input
                                      type="date"
                                      v-model="columnFilters[column.key].value"
                                      class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400"
                                    />
                                  </template>
                                </div>
                                </template>

                                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                                  <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Quick Sort</p>
                                  <div class="flex flex-col gap-1">
                                    <button
                                      type="button"
                                      class="inline-flex items-center justify-between w-full text-left text-xs px-2 py-1 rounded-md transition-colors"
                                      :class="isColumnSorted(column.key, 'asc') ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'"
                                      @click="updateSort(column.key, 'asc'); closeFilterDropdown();"
                                    >
                                      <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                          <path d="M7 18h3v-8h3L8 4 3 10h3v8zm10-6h-3v8h-3l5 6 5-6h-3v-8z" />
                                        </svg>
                                        Sort Ascending
                                      </span>
                                      <svg v-if="isColumnSorted(column.key, 'asc')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.147 7.146a1 1 0 01-1.415 0L3.296 9.01a1 1 0 011.415-1.414L9 11.884l6.296-6.295a1 1 0 011.408-.3z" clip-rule="evenodd" />
                                      </svg>
                                    </button>
                                    <button
                                      type="button"
                                      class="inline-flex items-center justify-between w-full text-left text-xs px-2 py-1 rounded-md transition-colors"
                                      :class="isColumnSorted(column.key, 'desc') ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'"
                                      @click="updateSort(column.key, 'desc'); closeFilterDropdown();"
                                    >
                                      <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                          <path d="M7 6h3v8h3L8 20 3 14h3V6zm13 2h-3V0h-3l5-6 5 6h-3v8z" />
                                        </svg>
                                        Sort Descending
                                      </span>
                                      <svg v-if="isColumnSorted(column.key, 'desc')" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.147 7.146a1 1 0 01-1.415 0L3.296 9.01a1 1 0 011.415-1.414L9 11.884l6.296-6.295a1 1 0 011.408-.3z" clip-rule="evenodd" />
                                      </svg>
                                    </button>
                                    <button
                                      type="button"
                                      class="inline-flex items-center justify-between w-full text-left text-xs px-2 py-1 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                                      @click="updateSort(column.key, null); closeFilterDropdown();"
                                    >
                                      <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                          <path d="M5 5h14v2H5zm0 6h10v2H5zm0 6h6v2H5z" />
                                        </svg>
                                        Clear Sort
                                      </span>
                                    </button>
                                  </div>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                  <button
                                    type="button"
                                    class="text-xs px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700"
                                    @click="closeFilterDropdown"
                                  >
                                    Close
                                  </button>
                                </div>
                              </div>
                            </div>
                          </transition>
                        </div>
                      </div>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr 
                  v-for="activity in currentPageActivities" 
                  :key="activity.id"
                  class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent dark:hover:from-blue-900/20 dark:hover:to-transparent transition-all duration-200"
                >
                  <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="max-w-xs">
                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate relative group/org">
                          {{ activity.organization }}
                          <!-- Tooltip for full organization name -->
                          <span 
                            v-if="activity.organization.length > 30"
                            class="absolute left-0 bottom-full mb-2 bg-gradient-to-r from-gray-900 to-gray-800 dark:from-gray-700 dark:to-gray-600 text-white text-xs rounded-lg py-2 px-3 opacity-0 group-hover/org:opacity-100 transition-opacity duration-300 whitespace-normal w-64 z-50 pointer-events-none shadow-xl"
                          >
                            {{ activity.organization }}
                          </span>
                        </div>
                        <Link 
                          v-if="activity.status.toLowerCase() === 'approved'"
                          :href="`/applications/${activity.application_id}/reports`"
                          class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline transition-colors duration-200"
                        >
                          View Reports →
                        </Link>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 dark:text-white max-w-xs">
                      {{ activity.objective }}
                      <Link 
                        v-if="!isAdmin && activity.status.toLowerCase() === 'approved'"
                        :href="`/applications/${activity.application_id}/reports`"
                        class="block text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline transition-colors duration-200 mt-1"
                      >
                        View Reports →
                      </Link>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ activity.activity_name }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-700 dark:text-gray-300 max-w-md">
                      {{ activity.description }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                      {{ activity.persons_involved }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white font-medium">
                      {{ activity.target_date_formatted }}
                    </div>
                    <div 
                      v-if="isPastDate(activity.target_date)"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 mt-1"
                    >
                      Past Event
                    </div>
                    <div 
                      v-else
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 mt-1"
                    >
                      Upcoming
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ formatCurrency(activity.budget) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ activity.target_participants }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(activity.status)}`"
                    >
                      {{ activity.status }}
                    </span>
                  </td>
                </tr>
                
                <!-- Empty State -->
                <tr v-if="currentPageActivities.length === 0">
                  <td :colspan="columnCount" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                      <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No activities found</h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md">
                        {{ searchQuery || statusFilter !== 'all' ? 'Try adjusting your search or filter to find what you\'re looking for.' : 'No Plan of Activities submissions have been created yet.' }}
                      </p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Card View (visible on mobile only) -->
          <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            <div 
              v-for="activity in currentPageActivities" 
              :key="activity.id"
              class="p-4 hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent dark:hover:from-blue-900/20 dark:hover:to-transparent transition-all duration-200"
            >
              <!-- Organization & Status Header (Admin Only) -->
              <div v-if="isAdmin" class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white break-words">
                    {{ activity.organization }}
                  </h3>
                  <Link 
                    v-if="activity.status.toLowerCase() === 'approved'"
                    :href="`/applications/${activity.application_id}/reports`"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline inline-flex items-center gap-1 mt-1"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    View Reports
                  </Link>
                </div>
                <span 
                  :class="`ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0 ${getStatusColor(activity.status)}`"
                >
                  {{ activity.status }}
                </span>
              </div>

              <!-- Status Header (User Only) -->
              <div v-if="!isAdmin" class="flex items-center justify-between mb-3">
                <span 
                  :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(activity.status)}`"
                >
                  {{ activity.status }}
                </span>
                <Link 
                  v-if="activity.status.toLowerCase() === 'approved'"
                  :href="`/applications/${activity.application_id}/reports`"
                  class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline inline-flex items-center gap-1"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  View Reports
                </Link>
              </div>

              <!-- Activity Details Grid -->
              <div class="space-y-2">
                <!-- Activity Name -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Activity</dt>
                  <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ activity.activity_name }}</dd>
                </div>

                <!-- Objective -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Objective</dt>
                  <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ activity.objective }}</dd>
                </div>

                <!-- Description -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Description</dt>
                  <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ activity.description }}</dd>
                </div>

                <!-- Persons Involved -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Persons Involved</dt>
                  <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ activity.persons_involved }}</dd>
                </div>

                <!-- Key Stats Row -->
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                  <!-- Target Date -->
                  <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Target Date</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ activity.target_date_formatted }}</dd>
                    <div 
                      v-if="isPastDate(activity.target_date)"
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 mt-1"
                    >
                      Past Event
                    </div>
                    <div 
                      v-else
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 mt-1"
                    >
                      Upcoming
                    </div>
                  </div>

                  <!-- Budget -->
                  <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Budget</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ formatCurrency(activity.budget) }}</dd>
                  </div>

                  <!-- Target Participants -->
                  <div class="col-span-2">
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Target Participants</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ activity.target_participants }}</dd>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State for Mobile -->
            <div v-if="currentPageActivities.length === 0" class="p-8 text-center">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">No activities found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                  {{ searchQuery || statusFilter !== 'all' ? 'Try adjusting your search or filter to find what you\'re looking for.' : 'No Plan of Activities submissions have been created yet.' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Pagination Controls - Minimalist Design -->
        <div v-if="totalPages > 1" class="flex items-center justify-between mt-6 px-2">
          <!-- Page Info -->
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Page <span class="font-semibold text-gray-900 dark:text-gray-100">{{ currentPage }}</span> of <span class="font-semibold text-gray-900 dark:text-gray-100">{{ totalPages }}</span>
          </div>

          <!-- Pagination Buttons -->
          <nav class="flex items-center gap-2" aria-label="Pagination">
            <!-- Previous Button -->
            <button 
              @click="prevPage" 
              :disabled="currentPage === 1"
              :class="[
                'inline-flex items-center justify-center p-1 text-sm font-medium transition-colors duration-200',
                currentPage === 1
                  ? 'text-gray-300 dark:text-gray-700 cursor-not-allowed'
                  : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'
              ]"
              aria-label="Previous page"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            
            <!-- Page Numbers -->
            <div class="hidden sm:flex items-center gap-1">
              <button 
                v-for="page in visiblePages" 
                :key="page"
                @click="page === '...' ? null : goToPage(page)"
                :disabled="page === '...'"
                :class="[
                  'min-w-[2rem] px-2 py-1 text-sm font-medium transition-colors duration-200',
                  page === '...' 
                    ? 'text-gray-400 dark:text-gray-600 cursor-default' 
                    : currentPage === page 
                      ? 'text-blue-600 dark:text-blue-400 font-bold' 
                      : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'
                ]"
                :aria-label="page === '...' ? 'More pages' : `Go to page ${page}`"
                :aria-current="currentPage === page ? 'page' : undefined"
              >
                {{ page }}
              </button>
            </div>

            <!-- Mobile: Current Page Display -->
            <div class="sm:hidden px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ currentPage }} / {{ totalPages }}
            </div>
            
            <!-- Next Button -->
            <button 
              @click="nextPage" 
              :disabled="currentPage === totalPages"
              :class="[
                'inline-flex items-center justify-center p-1 text-sm font-medium transition-colors duration-200',
                currentPage === totalPages
                  ? 'text-gray-300 dark:text-gray-700 cursor-not-allowed'
                  : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'
              ]"
              aria-label="Next page"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- PDF Preview Modal -->
    <Teleport to="body">
      <transition name="fade">
        <div v-if="showPdfPreviewModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-60" @click="closePdfPreviewModal">
          <div
            class="relative bg-transparent shadow-2xl flex flex-col w-[95vw] max-w-4xl md:w-[70vw] md:max-w-3xl lg:w-[60vw] lg:max-w-4xl xl:w-[50vw] xl:max-w-5xl h-[75vh] md:h-[85vh] lg:h-[90vh] xl:h-[95vh] max-h-[95vh] overflow-hidden border border-transparent"
            @click.stop
          >
            <!-- Close Button: floating at top-right, outside header -->
            <button
              @click="closePdfPreviewModal"
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
                Plan of Activities Report
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="downloadPdfFromPreview"
                  class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 text-sm font-medium text-white rounded-xl shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group"
                  title="Download PDF"
                  aria-label="Download PDF"
                >
                  <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                  Download
                </button>
              </div>
            </div>
            
            <!-- PDF Iframe -->
            <div class="flex-1 w-full h-full flex items-center justify-center bg-gray-100">
              <iframe
                v-if="pdfPreviewUrl"
                :src="pdfPreviewUrl"
                class="w-full h-full border-0 bg-white"
                style="min-height: 300px;"
                allowfullscreen
              ></iframe>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- DOCX Preview Modal -->
    <Teleport to="body">
      <transition name="fade">
        <div v-if="showDocxPreviewModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-60" @click="closeDocxPreviewModal">
          <div
            class="relative bg-transparent shadow-2xl flex flex-col w-[95vw] max-w-4xl md:w-[70vw] md:max-w-3xl lg:w-[60vw] lg:max-w-4xl xl:w-[50vw] xl:max-w-5xl h-[75vh] md:h-[85vh] lg:h-[90vh] xl:h-[95vh] max-h-[95vh] overflow-hidden border border-transparent"
            @click.stop
          >
            <!-- Close Button: floating at top-right, outside header -->
            <button
              @click="closeDocxPreviewModal"
              class="absolute top-4 right-4 flex items-center justify-center text-white hover:text-gray-200 focus:outline-none transition z-20 opacity-90"
              title="Close Preview"
              aria-label="Close Preview"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 pr-16 bg-gray-900 bg-opacity-90 backdrop-blur-sm relative">
              <div class="font-semibold text-gray-200 text-base truncate opacity-90">
                Plan of Activities Report (DOCX Preview)
              </div>
              <div class="flex items-center gap-2">
                <!-- Zoom Controls -->
                <div class="flex items-center gap-1 bg-gray-800 rounded-lg px-2 py-1">
                  <button
                    @click="zoomOut"
                    class="p-1 text-white hover:bg-gray-700 rounded transition"
                    title="Zoom Out"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <span class="text-white text-xs font-mono px-2">{{ docxZoom }}%</span>
                  <button
                    @click="zoomIn"
                    class="p-1 text-white hover:bg-gray-700 rounded transition"
                    title="Zoom In"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button
                    @click="resetZoom"
                    class="p-1 text-white hover:bg-gray-700 rounded transition text-xs ml-1"
                    title="Reset Zoom"
                  >
                    Reset
                  </button>
                </div>
                
                <button
                  @click="downloadDocxFromPreview"
                  class="inline-flex items-center justify-center px-4 py-2 bg-green-500 text-sm font-medium text-white rounded-xl shadow-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group"
                  title="Download DOCX"
                  aria-label="Download DOCX"
                >
                  <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                  Download
                </button>
              </div>
            </div>
            
            <!-- DOCX Content with Scroll -->
            <div class="flex-1 w-full h-full overflow-auto bg-gray-200">
              <div class="min-h-full p-4">
                <div 
                  v-if="docxHtmlContent"
                  v-html="docxHtmlContent"
                  :style="{ 
                    transform: `scale(${docxZoom / 100})`, 
                    transformOrigin: 'top center',
                    width: docxZoom === 100 ? '100%' : `${100 / (docxZoom / 100)}%`,
                    margin: '0 auto'
                  }"
                  class="bg-white shadow-2xl transition-transform duration-200 docx-preview-content"
                ></div>
                <div v-else class="flex items-center justify-center h-full">
                  <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>
  </SidebarLayout>
</template>

<style scoped>
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.rotate-180 {
  transform: rotate(180deg);
  transition: transform 0.2s ease;
}

/* DOCX Preview Content Styling */
:deep(.docx-preview-content) {
  padding: 20px;
  min-height: 100%;
}

:deep(.docx-preview-content body) {
  margin: 0;
  padding: 0;
}

:deep(.docx-preview-content table) {
  width: 100%;
  border-collapse: collapse;
}

:deep(.docx-preview-content .header) {
  margin-bottom: 20px;
}

:deep(.docx-preview-content .footer) {
  position: static;
  margin-top: 20px;
}
</style>
