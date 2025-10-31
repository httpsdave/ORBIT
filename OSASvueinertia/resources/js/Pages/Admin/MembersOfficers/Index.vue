<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, shallowRef } from 'vue';
import { Head } from '@inertiajs/vue3';
import SidebarLayout from '@/Components/Layout/Sidebar/SidebarLayout.vue';

const props = defineProps({
  members: {
    type: Array,
    required: true,
  },
  officers: {
    type: Array,
    required: true,
  },
  totalMembers: {
    type: Number,
    required: true,
  },
  totalOfficers: {
    type: Number,
    required: true,
  },
  isAdmin: {
    type: Boolean,
    default: true,
  },
});

// Tab state
const activeTab = ref('officers'); // 'officers' or 'members'

// Search and filter state
const searchQuery = ref('');
const statusFilter = ref('all');
const organizationFilter = ref('');
const semesterFilter = ref('all');

// Table columns configuration for Members
const membersTableColumns = [
  { key: 'organization', label: 'Organization', type: 'text', multiSelect: true },
  { key: 'student_name', label: 'Student Name', type: 'text', multiSelect: false },
  { key: 'student_number', label: 'Student Number', type: 'text', multiSelect: false },
  { key: 'course_year_section', label: 'Course - Year & Section', type: 'text', multiSelect: false },
  { key: 'semester', label: 'Semester', type: 'text', multiSelect: true },
  { key: 'academic_year', label: 'Academic Year', type: 'text', multiSelect: true },
  { key: 'status', label: 'Status', type: 'text', multiSelect: true },
];

// Table columns configuration for Officers
const officersTableColumns = [
  { key: 'organization', label: 'Organization', type: 'text', multiSelect: true },
  { key: 'student_name', label: 'Student Name', type: 'text', multiSelect: false },
  { key: 'position', label: 'Position', type: 'text', multiSelect: true },
  { key: 'student_number', label: 'Student Number', type: 'text', multiSelect: false },
  { key: 'status', label: 'Status', type: 'text', multiSelect: true },
];

// Get current table columns based on active tab
const currentTableColumns = computed(() => {
  return activeTab.value === 'members' ? membersTableColumns : officersTableColumns;
});

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

const createDefaultColumnFilters = (columns) => {
  return columns.reduce((acc, column) => {
    acc[column.key] = {
      operator: filterOperators[column.type][0].value,
      value: column.multiSelect ? [] : '',
    };
    return acc;
  }, {});
};

const columnFilters = ref(createDefaultColumnFilters(membersTableColumns));
const activeFilterDropdown = ref(null);
const sortState = ref({ column: null, direction: null });

const closeFilterDropdown = () => {
  activeFilterDropdown.value = null;
};

const handleClickOutside = (event) => {
  if (!event.target.closest('.column-filter-wrapper')) {
    closeFilterDropdown();
  }
};

// Back-to-top button state and handler
const showBackToTop = ref(false);
const onScroll = () => {
  try {
    const y = window.scrollY || window.pageYOffset;
    showBackToTop.value = y > 300;
  } catch (e) {
    // ignore for SSR
  }
};

const scrollToTop = (e) => {
  e?.preventDefault();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

let clickOutsideHandler = null;

onMounted(() => {
  clickOutsideHandler = handleClickOutside;
  document.addEventListener('click', clickOutsideHandler, { passive: true });
  window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
  if (clickOutsideHandler) {
    document.removeEventListener('click', clickOutsideHandler);
  }
  window.removeEventListener('scroll', onScroll);
});

// Get unique organizations - memoized
const organizationOptions = computed(() => {
  const allOrgs = activeTab.value === 'members' 
    ? [...new Set(props.members.map(m => m.organization))]
    : [...new Set(props.officers.map(o => o.organization))];
  return allOrgs.sort().map(org => ({ value: org, label: org }));
});

// Get unique values for multi-select columns - optimized
const getUniqueColumnValues = (columnKey) => {
  const values = new Set();
  const data = activeTab.value === 'members' ? props.members : props.officers;
  data.forEach(item => {
    const value = item[columnKey];
    if (value !== null && value !== undefined && value !== '') {
      values.add(value);
    }
  });
  return Array.from(values).sort();
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
  const column = currentTableColumns.value.find(col => col.key === columnKey);
  columnFilters.value[columnKey].value = column?.multiSelect ? [] : '';
  columnFilters.value[columnKey].operator = filterOperators[column.type][0].value;
};

const hasActiveColumnFilter = (columnKey) => {
  const value = columnFilters.value[columnKey]?.value;
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

// Pagination state
const currentPage = ref(1);
const activitiesPerPage = ref(50);
const pageSizeOptions = [10, 25, 50, 100];

// Optimized filter function to reduce redundant string operations
const applyFilters = (data, tableColumns) => {
  let filtered = data;
  const searchLower = searchQuery.value?.toLowerCase();

  // Early return if no filters
  const hasFilters = searchQuery.value || 
                     statusFilter.value !== 'all' || 
                     organizationFilter.value || 
                     semesterFilter.value !== 'all' ||
                     Object.values(columnFilters.value).some(f => 
                       Array.isArray(f.value) ? f.value.length > 0 : !!f.value
                     );
  
  if (!hasFilters && !sortState.value.column) {
    return data;
  }

  // Apply search filter (for members only)
  if (searchLower && tableColumns === membersTableColumns) {
    filtered = filtered.filter(member => {
      const orgLower = member.organization?.toLowerCase() ?? '';
      const nameLower = member.student_name?.toLowerCase() ?? '';
      const numLower = member.student_number?.toLowerCase() ?? '';
      const courseLower = member.course_year_section?.toLowerCase() ?? '';
      
      return orgLower.includes(searchLower) ||
             nameLower.includes(searchLower) ||
             numLower.includes(searchLower) ||
             courseLower.includes(searchLower);
    });
  } else if (searchLower) {
    // For officers
    filtered = filtered.filter(officer => {
      const orgLower = officer.organization?.toLowerCase() ?? '';
      const nameLower = officer.student_name?.toLowerCase() ?? '';
      const numLower = officer.student_number?.toLowerCase() ?? '';
      const posLower = officer.position?.toLowerCase() ?? '';
      
      return orgLower.includes(searchLower) ||
             nameLower.includes(searchLower) ||
             numLower.includes(searchLower) ||
             posLower.includes(searchLower);
    });
  }

  // Apply status filter
  if (statusFilter.value !== 'all') {
    const statusLower = statusFilter.value.toLowerCase();
    filtered = filtered.filter(item => 
      item.status?.toLowerCase() === statusLower
    );
  }

  // Apply organization filter
  if (organizationFilter.value) {
    filtered = filtered.filter(item => 
      item.organization === organizationFilter.value
    );
  }

  // Apply semester filter (members only)
  if (semesterFilter.value !== 'all' && tableColumns === membersTableColumns) {
    filtered = filtered.filter(member => 
      member.semester === semesterFilter.value
    );
  }

  // Apply column-level filters - optimized
  const activeFilters = tableColumns.filter(column => {
    const filterData = columnFilters.value[column.key];
    if (!filterData) return false;
    const { value } = filterData;
    return Array.isArray(value) ? value.length > 0 : !!value;
  });

  if (activeFilters.length > 0) {
    filtered = filtered.filter(item => {
      return activeFilters.every(column => {
        const filterData = columnFilters.value[column.key];
        const { operator, value } = filterData;

        // Handle multi-select
        if (column.multiSelect && Array.isArray(value)) {
          const itemValue = item[column.key];
          if (itemValue === undefined || itemValue === null) {
            return false;
          }
          return value.includes(String(itemValue));
        }

        const itemValue = item[column.key];
        if (itemValue === undefined || itemValue === null) {
          return false;
        }

        if (column.type === 'text') {
          const recordStr = String(itemValue).toLowerCase();
          const searchStr = String(value).toLowerCase();
          
          switch(operator) {
            case 'contains': return recordStr.includes(searchStr);
            case 'equals': return recordStr === searchStr;
            case 'startsWith': return recordStr.startsWith(searchStr);
            case 'endsWith': return recordStr.endsWith(searchStr);
            default: return true;
          }
        }

        return true;
      });
    });
  }

  // Apply column sorting if active
  if (sortState.value.column && sortState.value.direction) {
    const column = tableColumns.find(col => col.key === sortState.value.column);
    if (column) {
      const directionMultiplier = sortState.value.direction === 'asc' ? 1 : -1;
      filtered = [...filtered].sort((a, b) => {
        const aVal = a[column.key];
        const bVal = b[column.key];

        const aStr = String(aVal ?? '').toLowerCase();
        const bStr = String(bVal ?? '').toLowerCase();
        
        if (aStr < bStr) return -1 * directionMultiplier;
        if (aStr > bStr) return 1 * directionMultiplier;
        return 0;
      });
    }
  }

  return filtered;
};

// Filtered members - using optimized function
const filteredMembers = computed(() => {
  return applyFilters(props.members, membersTableColumns);
});

// Filtered officers - using optimized function
const filteredOfficers = computed(() => {
  return applyFilters(props.officers, officersTableColumns);
});

// Current data based on active tab
const currentData = computed(() => {
  return activeTab.value === 'members' ? filteredMembers.value : filteredOfficers.value;
});

// Pagination computed properties
const totalPages = computed(() => {
  if (activitiesPerPage.value <= 0) return 1;
  return Math.max(1, Math.ceil(currentData.value.length / activitiesPerPage.value));
});

const currentPageData = computed(() => {
  const start = (currentPage.value - 1) * activitiesPerPage.value;
  return currentData.value.slice(start, start + activitiesPerPage.value);
});

const displayRange = computed(() => {
  const total = currentData.value.length;
  if (total === 0) {
    return { start: 0, end: 0 };
  }

  const start = (currentPage.value - 1) * activitiesPerPage.value + 1;
  const end = Math.min(start + activitiesPerPage.value - 1, total);
  return { start, end };
});

// Pagination methods
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

// Switch tab
const switchTab = (tab) => {
  activeTab.value = tab;
  currentPage.value = 1; // Reset to first page when switching tabs
  searchQuery.value = '';
  statusFilter.value = 'all';
  organizationFilter.value = '';
  semesterFilter.value = 'all';
  
  // Reset column filters for new tab
  const columns = tab === 'members' ? membersTableColumns : officersTableColumns;
  columnFilters.value = createDefaultColumnFilters(columns);
  sortState.value = { column: null, direction: null };
};

// Watch for filter changes and reset to page 1
watch([searchQuery, statusFilter, organizationFilter, semesterFilter], () => {
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

// Clear all filters
const clearAllFilters = () => {
  searchQuery.value = '';
  statusFilter.value = 'all';
  organizationFilter.value = '';
  semesterFilter.value = 'all';
  currentPage.value = 1;
};

// Check if any filters are active
const hasActiveFilters = computed(() => {
  return searchQuery.value || statusFilter.value !== 'all' || organizationFilter.value || semesterFilter.value !== 'all';
});

// Status badge color
const getStatusColor = (status) => {
  const statusLower = status.toLowerCase();
  if (statusLower === 'approved') {
    return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
  } else if (statusLower === 'pending') {
    return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300';
  } else if (statusLower === 'disapproved') {
    return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
  }
  return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
};

// Visible pages for pagination
const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const delta = 2;
  
  if (total <= 7) {
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
</script>

<template>
  <Head title="Officers & Members" />

  <SidebarLayout :is-admin="isAdmin">
    <!-- Colored Banner -->
    <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
      <div class="w-1/4 h-1.5 bg-blue-500"></div>
      <div class="w-1/4 h-1.5 bg-green-500"></div>
      <div class="w-1/4 h-1.5 bg-yellow-500"></div>
      <div class="w-1/4 h-1.5 bg-red-500"></div>
    </div>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
      <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 py-8">
        <!-- Header Section -->
        <div class="mb-8">
          <div class="flex items-center justify-between mb-2">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Officers & Members
              </h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                Overview of all members and officers from submitted forms
              </p>
            </div>
            <div class="flex items-center gap-2">
              <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total:</span>
                <span class="ml-2 text-lg font-bold bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent">
                  {{ activeTab === 'members' ? totalMembers : totalOfficers }}
                </span>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center text-sm font-medium">
              <button
                :class="[
                  'px-4 py-3 border-b-2 transition-colors',
                  activeTab === 'officers'
                    ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                ]"
                @click="switchTab('officers')"
              >
                List of Officers ({{ totalOfficers }})
              </button>
              <button
                :class="[
                  'px-4 py-3 border-b-2 transition-colors ml-4',
                  activeTab === 'members'
                    ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:border-gray-300 dark:hover:border-gray-600'
                ]"
                @click="switchTab('members')"
              >
                List of Members ({{ totalMembers }})
              </button>
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

              <!-- Organization Filter -->
              <div class="col-span-1">
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

              <!-- Semester Filter (Members Only) -->
              <div v-if="activeTab === 'members'" class="col-span-2">
                <select 
                  v-model="semesterFilter"
                  class="w-full pl-2.5 pr-7 py-1.5 border border-gray-300 dark:border-gray-600 rounded-full text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
                >
                  <option value="all">All Semesters</option>
                  <option value="1st">1st Semester</option>
                  <option value="2nd">2nd Semester</option>
                  <option value="Inter">Inter Semester</option>
                </select>
              </div>
            </div>

            <!-- Active Filters Display - Mobile Only -->
            <div v-if="searchQuery || statusFilter !== 'all' || organizationFilter || semesterFilter !== 'all'" class="flex flex-wrap gap-1.5 items-center text-xs">
              <span class="text-gray-600 dark:text-gray-400 font-medium text-xs">Active:</span>
              <span v-if="searchQuery" class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-xs">
                "{{ searchQuery.length > 15 ? searchQuery.substring(0, 15) + '...' : searchQuery }}"
              </span>
              <span v-if="statusFilter !== 'all'" class="px-1.5 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-md text-xs">
                {{ statusFilter }}
              </span>
              <span v-if="organizationFilter" class="px-1.5 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-md text-xs truncate max-w-[120px]" :title="organizationOptions.find(opt => opt.value === organizationFilter)?.label">
                {{ organizationOptions.find(opt => opt.value === organizationFilter)?.label && organizationOptions.find(opt => opt.value === organizationFilter)?.label.length > 15 ? organizationOptions.find(opt => opt.value === organizationFilter)?.label.substring(0, 15) + '...' : organizationOptions.find(opt => opt.value === organizationFilter)?.label }}
              </span>
              <span v-if="semesterFilter !== 'all'" class="px-1.5 py-0.5 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-md text-xs">
                {{ semesterFilter }} Semester
              </span>
            </div>
          </div>

          <!-- Results count -->
          <div class="mb-6 px-2">
            <div class="text-left text-sm text-gray-600 dark:text-gray-400">
              Showing <span class="font-semibold text-blue-600 dark:text-blue-400">{{ displayRange.start }}-{{ displayRange.end }}</span> of <span class="font-semibold text-blue-600 dark:text-blue-400">{{ currentData.length }}</span> {{ activeTab === 'members' ? 'members' : 'officers' }}
              <span v-if="currentData.length !== (activeTab === 'members' ? totalMembers : totalOfficers)" class="ml-2 text-gray-500">
                (filtered from {{ activeTab === 'members' ? totalMembers : totalOfficers }} total)
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
                :placeholder="activeTab === 'members' ? 'Search by organization, student name, number, course...' : 'Search by organization, student name, position...'"
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

        <!-- Members Table -->
        <div v-if="activeTab === 'members'" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow duration-300">
          
          <!-- Desktop Table View (hidden on mobile) -->
          <div class="hidden md:block overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 sticky top-0 z-10">
                <tr>
                  <th
                    v-for="column in membersTableColumns"
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
                              :class="column.key === 'organization' || column.key === 'student_name' ? 'left-0' : 'right-0'"
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
                                  <input
                                    type="text"
                                    v-model="columnFilters[column.key].value"
                                    class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400"
                                    placeholder="Enter text"
                                  />
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
                  v-for="member in currentPageData" 
                  :key="member.id"
                  class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent dark:hover:from-blue-900/20 dark:hover:to-transparent transition-all duration-200"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ member.organization }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ member.submitted_at }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ member.student_name }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white font-mono">
                      {{ member.student_number }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ member.course_year_section }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ member.semester }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ member.academic_year }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(member.status)}`"
                    >
                      {{ member.status }}
                    </span>
                  </td>
                </tr>
                
                <!-- Empty State -->
                <tr v-if="currentPageData.length === 0">
                  <td colspan="7" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                      <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No members found</h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md">
                        {{ hasActiveFilters ? 'Try adjusting your search or filter to find what you\'re looking for.' : 'No List of Members submissions have been created yet.' }}
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
              v-for="member in currentPageData" 
              :key="member.id"
              class="p-4 hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent dark:hover:from-blue-900/20 dark:hover:to-transparent transition-all duration-200"
            >
              <!-- Organization & Status Header -->
              <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white break-words">
                    {{ member.organization }}
                  </h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ member.submitted_at }}
                  </p>
                </div>
                <span 
                  :class="`ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0 ${getStatusColor(member.status)}`"
                >
                  {{ member.status }}
                </span>
              </div>

              <!-- Member Details Grid -->
              <div class="space-y-2">
                <!-- Student Name -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student Name</dt>
                  <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ member.student_name }}</dd>
                </div>

                <!-- Student Number -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student Number</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ member.student_number }}</dd>
                </div>

                <!-- Course, Year & Section -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Course - Year & Section</dt>
                  <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ member.course_year_section }}</dd>
                </div>

                <!-- Key Info Row -->
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                  <!-- Semester -->
                  <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Semester</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ member.semester }}</dd>
                  </div>

                  <!-- Academic Year -->
                  <div>
                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Academic Year</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ member.academic_year }}</dd>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State for Mobile -->
            <div v-if="currentPageData.length === 0" class="p-8 text-center">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">No members found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                  {{ hasActiveFilters ? 'Try adjusting your search or filter to find what you\'re looking for.' : 'No List of Members submissions have been created yet.' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Officers Table -->
        <div v-if="activeTab === 'officers'" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow duration-300">
          
          <!-- Desktop Table View (hidden on mobile) -->
          <div class="hidden md:block overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 sticky top-0 z-10">
                <tr>
                  <th
                    v-for="column in officersTableColumns"
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
                              :class="column.key === 'organization' || column.key === 'student_name' ? 'left-0' : 'right-0'"
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
                                  <input
                                    type="text"
                                    v-model="columnFilters[column.key].value"
                                    class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800 px-2 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400"
                                    placeholder="Enter text"
                                  />
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
                  v-for="officer in currentPageData" 
                  :key="officer.id"
                  class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent dark:hover:from-blue-900/20 dark:hover:to-transparent transition-all duration-200"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ officer.organization }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      {{ officer.submitted_at }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ officer.student_name }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-blue-600 dark:text-blue-400">
                      {{ officer.position }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white font-mono">
                      {{ officer.student_number }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(officer.status)}`"
                    >
                      {{ officer.status }}
                    </span>
                  </td>
                </tr>
                
                <!-- Empty State -->
                <tr v-if="currentPageData.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                      <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                      </div>
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No officers found</h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md">
                        {{ hasActiveFilters ? 'Try adjusting your search or filter to find what you\'re looking for.' : 'No List of Officers submissions have been created yet.' }}
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
              v-for="officer in currentPageData" 
              :key="officer.id"
              class="p-4 hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent dark:hover:from-blue-900/20 dark:hover:to-transparent transition-all duration-200"
            >
              <!-- Organization & Status Header -->
              <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-white break-words">
                    {{ officer.organization }}
                  </h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ officer.submitted_at }}
                  </p>
                </div>
                <span 
                  :class="`ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0 ${getStatusColor(officer.status)}`"
                >
                  {{ officer.status }}
                </span>
              </div>

              <!-- Officer Details Grid -->
              <div class="space-y-2">
                <!-- Student Name -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student Name</dt>
                  <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ officer.student_name }}</dd>
                </div>

                <!-- Position -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Position</dt>
                  <dd class="mt-1 text-sm font-medium text-blue-600 dark:text-blue-400">{{ officer.position }}</dd>
                </div>

                <!-- Student Number -->
                <div>
                  <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student Number</dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ officer.student_number }}</dd>
                </div>
              </div>
            </div>

            <!-- Empty State for Mobile -->
            <div v-if="currentPageData.length === 0" class="p-8 text-center">
              <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                  </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">No officers found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                  {{ hasActiveFilters ? 'Try adjusting your search or filter to find what you\'re looking for.' : 'No List of Officers submissions have been created yet.' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Pagination Controls -->
        <div v-if="totalPages > 1" class="flex items-center justify-between mt-6 px-2">
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Page <span class="font-semibold text-gray-900 dark:text-gray-100">{{ currentPage }}</span> of <span class="font-semibold text-gray-900 dark:text-gray-100">{{ totalPages }}</span>
          </div>

          <nav class="flex items-center gap-2" aria-label="Pagination">
            <button 
              @click="prevPage" 
              :disabled="currentPage === 1"
              :class="[
                'inline-flex items-center justify-center p-1 text-sm font-medium transition-colors duration-200',
                currentPage === 1
                  ? 'text-gray-300 dark:text-gray-700 cursor-not-allowed'
                  : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'
              ]"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            
            <div class="hidden sm:flex items-center gap-1">
              <template v-for="(page, index) in visiblePages" :key="index">
                <button
                  v-if="page !== '...'"
                  @click="goToPage(page)"
                  :class="[
                    'px-3 py-1 text-sm font-medium transition-colors duration-200',
                    currentPage === page
                      ? 'text-blue-600 dark:text-blue-400'
                      : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'
                  ]"
                >
                  {{ page }}
                </button>
                <span v-else class="px-2 text-gray-400">...</span>
              </template>
            </div>

            <div class="sm:hidden px-2 py-1 text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ currentPage }} / {{ totalPages }}
            </div>
            
            <button 
              @click="nextPage" 
              :disabled="currentPage === totalPages"
              :class="[
                'inline-flex items-center justify-center p-1 text-sm font-medium transition-colors duration-200',
                currentPage === totalPages
                  ? 'text-gray-300 dark:text-gray-700 cursor-not-allowed'
                  : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'
              ]"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </nav>
        </div>

      </div>
    </div>

    <!-- Back to top floating button -->
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
</style>
