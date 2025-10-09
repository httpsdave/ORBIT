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

// Pagination state
const currentPage = ref(1);
const activitiesPerPage = 50;

// Filtered activities based on search and status
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

// Navigation functions
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    // Scroll to top of table
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// Watch for filter changes and reset to page 1
watch([searchQuery, statusFilter, dateFilter], () => {
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
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
      <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                <span class="ml-2 text-lg font-bold text-blue-600 dark:text-blue-400">{{ totalActivities }}</span>
              </div>
            </div>
          </div>

          <!-- Filters Section -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mt-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Search Input -->
              <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Search Activities
                </label>
                <div class="relative">
                  <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <input
                    id="search"
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by organization, objective, activity..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                  />
                </div>
              </div>

              <!-- Status Filter -->
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Filter by Status
                </label>
                <select
                  id="status"
                  v-model="statusFilter"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                >
                  <option value="all">All Status</option>
                  <option value="approved">Approved</option>
                  <option value="pending">Pending</option>
                  <option value="disapproved">Disapproved</option>
                </select>
              </div>

              <!-- Date Filter -->
              <div>
                <label for="dateFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Sort by Date
                </label>
                <select
                  id="dateFilter"
                  v-model="dateFilter"
                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent dark:bg-gray-700 dark:text-white"
                >
                  <option value="nearest">Nearest Events (Default)</option>
                  <option value="upcoming">Upcoming Events Only</option>
                  <option value="past">Past Events Only</option>
                  <option value="submission-newest">Newest Submission First</option>
                  <option value="submission-oldest">Oldest Submission First</option>
                </select>
              </div>
            </div>

            <!-- Results count -->
            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
              Showing <span class="font-semibold">{{ startIndex + 1 }}-{{ endIndex }}</span> of <span class="font-semibold">{{ filteredActivities.length }}</span> activities
              <span v-if="filteredActivities.length !== totalActivities" class="ml-2 text-gray-500">
                (filtered from {{ totalActivities }} total)
              </span>
            </div>
          </div>
        </div>

        <!-- Top Pagination Controls -->
        <div v-if="totalPages > 1" class="flex items-center justify-between mt-6 px-2">
          <!-- Page Info -->
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Page <span class="font-semibold text-gray-900 dark:text-gray-100">{{ currentPage }}</span> of <span class="font-semibold text-gray-900 dark:text-gray-100">{{ totalPages }}</span>
          </div>

          <!-- Pagination Buttons -->
          <nav class="flex items-center gap-1" aria-label="Pagination">
            <!-- Previous Button -->
            <button 
              @click="prevPage" 
              :disabled="currentPage === 1"
              :class="[
                'inline-flex items-center justify-center w-8 h-8 text-sm font-medium rounded-lg transition-all duration-200',
                currentPage === 1
                  ? 'text-gray-400 dark:text-gray-600 cursor-not-allowed'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
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
                  'min-w-[2rem] h-8 px-3 text-sm font-medium rounded-lg transition-all duration-200',
                  page === '...' 
                    ? 'text-gray-400 dark:text-gray-600 cursor-default' 
                    : currentPage === page 
                      ? 'bg-blue-600 text-white' 
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
                :aria-label="page === '...' ? 'More pages' : `Go to page ${page}`"
                :aria-current="currentPage === page ? 'page' : undefined"
              >
                {{ page }}
              </button>
            </div>

            <!-- Mobile: Current Page Display -->
            <div class="sm:hidden px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ currentPage }} / {{ totalPages }}
            </div>
            
            <!-- Next Button -->
            <button 
              @click="nextPage" 
              :disabled="currentPage === totalPages"
              :class="[
                'inline-flex items-center justify-center w-8 h-8 text-sm font-medium rounded-lg transition-all duration-200',
                currentPage === totalPages
                  ? 'text-gray-400 dark:text-gray-600 cursor-not-allowed'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
              ]"
              aria-label="Next page"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </nav>
        </div>

        <!-- Activities Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Organization
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Objective
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Activity
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Brief Description
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Persons Involved
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Target Date
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Budget
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Target Participants
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Status
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr 
                  v-for="activity in currentPageActivities" 
                  :key="activity.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="max-w-xs">
                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate relative group/org">
                          {{ activity.organization }}
                          <!-- Tooltip for full organization name -->
                          <span 
                            v-if="activity.organization.length > 30"
                            class="absolute left-0 bottom-full mb-2 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded py-2 px-3 opacity-0 group-hover/org:opacity-100 transition-opacity duration-300 whitespace-normal w-64 z-50 pointer-events-none shadow-lg"
                          >
                            {{ activity.organization }}
                          </span>
                        </div>
                        <Link 
                          :href="`/applications/${activity.application_id}/reports`"
                          class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                        >
                          View Reports
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
                      class="text-xs text-red-600 dark:text-red-400 font-medium"
                    >
                      Past Event
                    </div>
                    <div 
                      v-else
                      class="text-xs text-green-600 dark:text-green-400 font-medium"
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No activities found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                      {{ searchQuery || statusFilter !== 'all' ? 'Try adjusting your search or filter to find what you\'re looking for.' : 'No Plan of Activities submissions have been created yet.' }}
                    </p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Bottom Pagination Controls -->
        <div v-if="totalPages > 1" class="flex items-center justify-between mt-6 px-2">
          <!-- Page Info -->
          <div class="text-sm text-gray-600 dark:text-gray-400">
            Page <span class="font-semibold text-gray-900 dark:text-gray-100">{{ currentPage }}</span> of <span class="font-semibold text-gray-900 dark:text-gray-100">{{ totalPages }}</span>
          </div>

          <!-- Pagination Buttons -->
          <nav class="flex items-center gap-1" aria-label="Pagination">
            <!-- Previous Button -->
            <button 
              @click="prevPage" 
              :disabled="currentPage === 1"
              :class="[
                'inline-flex items-center justify-center w-8 h-8 text-sm font-medium rounded-lg transition-all duration-200',
                currentPage === 1
                  ? 'text-gray-400 dark:text-gray-600 cursor-not-allowed'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
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
                  'min-w-[2rem] h-8 px-3 text-sm font-medium rounded-lg transition-all duration-200',
                  page === '...' 
                    ? 'text-gray-400 dark:text-gray-600 cursor-default' 
                    : currentPage === page 
                      ? 'bg-blue-600 text-white' 
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
                :aria-label="page === '...' ? 'More pages' : `Go to page ${page}`"
                :aria-current="currentPage === page ? 'page' : undefined"
              >
                {{ page }}
              </button>
            </div>

            <!-- Mobile: Current Page Display -->
            <div class="sm:hidden px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ currentPage }} / {{ totalPages }}
            </div>
            
            <!-- Next Button -->
            <button 
              @click="nextPage" 
              :disabled="currentPage === totalPages"
              :class="[
                'inline-flex items-center justify-center w-8 h-8 text-sm font-medium rounded-lg transition-all duration-200',
                currentPage === totalPages
                  ? 'text-gray-400 dark:text-gray-600 cursor-not-allowed'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
              ]"
              aria-label="Next page"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </nav>
        </div>

        <!-- Legend -->
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Legend:</h3>
          <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-green-500 rounded-full"></div>
              <span class="text-xs text-gray-600 dark:text-gray-400">Upcoming Event</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-red-500 rounded-full"></div>
              <span class="text-xs text-gray-600 dark:text-gray-400">Past Event</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                Approved
              </span>
            </div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">
                Pending
              </span>
            </div>
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                Disapproved
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>
