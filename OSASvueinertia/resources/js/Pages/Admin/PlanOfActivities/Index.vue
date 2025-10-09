<script setup>
import { ref, computed, watch } from 'vue';
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

// Get unique organizations from activities
const organizationOptions = computed(() => {
  const uniqueOrgs = [...new Set(props.activities.map(activity => activity.organization))];
  return uniqueOrgs.sort().map(org => ({ value: org, label: org }));
});

// Pagination state
const currentPage = ref(1);
const activitiesPerPage = 50;

// Filtered activities based on search, status, and organization
const filteredActivities = computed(() => {
  let filtered = props.activities;

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

  return filtered;
});

// Pagination computed properties
const totalPages = computed(() => Math.ceil(filteredActivities.value.length / activitiesPerPage));
const startIndex = computed(() => (currentPage.value - 1) * activitiesPerPage);
const endIndex = computed(() => Math.min(startIndex.value + activitiesPerPage, filteredActivities.value.length));
const currentPageActivities = computed(() => {
  return filteredActivities.value.slice(startIndex.value, endIndex.value);
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
                Plan of Activities
              </h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                Overview of all planned activities from submitted Plan of Activities forms
              </p>
            </div>
            <div class="flex items-center gap-2">
              <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total Activities:</span>
                <span class="ml-2 text-lg font-bold bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent">{{ totalActivities }}</span>
              </div>
            </div>
          </div>

          <!-- Unified Search and Filter Section -->
          <div class="max-w-4xl mx-auto px-3 sm:px-6 mb-6 space-y-3">
            <!-- Search Bar -->
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input
                type="text"
                v-model="searchQuery"
                class="block w-full pl-9 sm:pl-12 pr-9 sm:pr-12 py-2 sm:py-3 text-sm sm:text-base border border-gray-300 dark:border-gray-600 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 transition duration-150 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
                placeholder="Search by organization, objective, activity..."
              />
              <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center">
                <button @click="searchQuery = ''" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Filter Bar - Responsive Grid Layout -->
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2">
              <!-- Status Filter -->
              <div class="col-span-1">
                <select 
                  v-model="statusFilter"
                  class="w-full pl-2.5 pr-7 py-1.5 sm:pl-3 sm:pr-8 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-full text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
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
                  class="w-full pl-2.5 pr-7 py-1.5 sm:pl-3 sm:pr-8 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-full text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
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
              <div class="col-span-2 sm:col-span-1">
                <select 
                  v-model="dateFilter"
                  class="w-full pl-2.5 pr-7 py-1.5 sm:pl-3 sm:pr-8 sm:py-2 border border-gray-300 dark:border-gray-600 rounded-full text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm"
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

            <!-- Active Filters Display - Compact for Mobile -->
            <div v-if="searchQuery || statusFilter !== 'all' || organizationFilter || dateFilter !== 'nearest'" class="flex flex-wrap gap-1.5 sm:gap-2 items-center text-xs sm:text-sm">
              <span class="text-gray-600 dark:text-gray-400 font-medium text-xs sm:text-sm">Active:</span>
              <span v-if="searchQuery" class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-xs">
                "{{ searchQuery.length > 15 ? searchQuery.substring(0, 15) + '...' : searchQuery }}"
              </span>
              <span v-if="statusFilter !== 'all'" class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-md text-xs">
                {{ statusFilter }}
              </span>
              <span v-if="organizationFilter" class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-md text-xs truncate max-w-[120px] sm:max-w-xs" :title="organizationOptions.find(opt => opt.value === organizationFilter)?.label">
                {{ organizationOptions.find(opt => opt.value === organizationFilter)?.label && organizationOptions.find(opt => opt.value === organizationFilter)?.label.length > 15 ? organizationOptions.find(opt => opt.value === organizationFilter)?.label.substring(0, 15) + '...' : organizationOptions.find(opt => opt.value === organizationFilter)?.label }}
              </span>
              <span v-if="dateFilter !== 'nearest'" class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-md text-xs">
                {{ dateFilter === 'upcoming' ? 'Upcoming Only' : dateFilter === 'past' ? 'Past Only' : dateFilter === 'submission-newest' ? 'Newest First' : 'Oldest First' }}
              </span>
            </div>
          </div>

          <!-- Results count -->
          <div class="max-w-4xl mx-auto px-3 sm:px-6 mb-6">
            <div class="text-sm text-gray-600 dark:text-gray-400 text-center">
              Showing <span class="font-semibold text-blue-600 dark:text-blue-400">{{ startIndex + 1 }}-{{ endIndex }}</span> of <span class="font-semibold text-blue-600 dark:text-blue-400">{{ filteredActivities.length }}</span> activities
              <span v-if="filteredActivities.length !== totalActivities" class="ml-2 text-gray-500">
                (filtered from {{ totalActivities }} total)
              </span>
            </div>
          </div>
        </div>

        <!-- Top Pagination Controls - Minimalist Design -->
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

        <!-- Activities Table (Desktop) & Cards (Mobile) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow duration-300">
          
          <!-- Desktop Table View (hidden on mobile) -->
          <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Organization
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Objective
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Activity
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Brief Description
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Persons Involved
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Target Date
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Budget
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Target Participants
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Status
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr 
                  v-for="activity in currentPageActivities" 
                  :key="activity.id"
                  class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent dark:hover:from-blue-900/20 dark:hover:to-transparent transition-all duration-200"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
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
                  <td colspan="9" class="px-6 py-12 text-center">
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
              <!-- Organization & Status Header -->
              <div class="flex items-start justify-between mb-3">
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
  </SidebarLayout>
</template>
