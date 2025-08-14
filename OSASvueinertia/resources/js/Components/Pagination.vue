<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  links: Array,
});

// Clean up the pagination data to match our modern design
const paginationData = computed(() => {
  if (!props.links || props.links.length <= 3) return null;
  
  const pages = [];
  const firstLink = props.links[0]; // Previous
  const lastLink = props.links[props.links.length - 1]; // Next
  const pageLinks = props.links.slice(1, -1); // Page numbers
  
  return {
    prevLink: firstLink,
    nextLink: lastLink,
    pageLinks: pageLinks,
    hasPages: pageLinks.length > 0
  };
});

// Helper to format page numbers properly
const getPageNumber = (link) => {
  if (!link.label) return '';
  
  // If it's a number, return it
  if (/^\d+$/.test(link.label)) {
    return link.label;
  }
  
  // If it contains HTML entities or special characters, extract the number
  const match = link.label.match(/\d+/);
  return match ? match[0] : '...';
};

// Helper to check if it's an ellipsis
const isEllipsis = (link) => {
  return link.label && (link.label.includes('...') || link.label.includes('&hellip;') || !link.url);
};
</script>

<template>
  <div v-if="paginationData" class="flex justify-center items-center gap-2 sm:gap-4 flex-wrap">
    <!-- Previous Button -->
    <Link
      v-if="paginationData.prevLink.url"
      :href="paginationData.prevLink.url"
      class="flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-medium"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      <span class="hidden sm:inline">Previous</span>
      <span class="sm:hidden">Prev</span>
    </Link>
    <span
      v-else
      class="flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed text-sm font-medium"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      <span class="hidden sm:inline">Previous</span>
      <span class="sm:hidden">Prev</span>
    </span>

    <!-- Page Numbers -->
    <div class="flex gap-1 sm:gap-2">
      <template v-for="(link, index) in paginationData.pageLinks" :key="index">
        <!-- Ellipsis -->
        <span
          v-if="isEllipsis(link)"
          class="px-2 py-1 sm:px-3 sm:py-1 text-gray-400 cursor-default text-sm"
        >
          ...
        </span>
        <!-- Page Number -->
        <Link
          v-else-if="link.url"
          :href="link.url"
          :class="[
            'px-2 py-1 sm:px-3 sm:py-1 rounded text-sm font-medium transition-colors duration-200',
            link.active 
              ? 'bg-blue-600 text-white' 
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500'
          ]"
        >
          {{ getPageNumber(link) }}
        </Link>
        <!-- Current Page (non-clickable) -->
        <span
          v-else
          class="px-2 py-1 sm:px-3 sm:py-1 rounded text-sm font-medium bg-blue-600 text-white"
        >
          {{ getPageNumber(link) }}
        </span>
      </template>
    </div>

    <!-- Next Button -->
    <Link
      v-if="paginationData.nextLink.url"
      :href="paginationData.nextLink.url"
      class="flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:bg-gray-300 disabled:cursor-not-allowed text-sm font-medium"
    >
      <span class="hidden sm:inline">Next</span>
      <span class="sm:hidden">Next</span>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 sm:ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </Link>
    <span
      v-else
      class="flex items-center px-3 py-2 sm:px-4 sm:py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed text-sm font-medium"
    >
      <span class="hidden sm:inline">Next</span>
      <span class="sm:hidden">Next</span>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 sm:ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </span>
  </div>
</template>