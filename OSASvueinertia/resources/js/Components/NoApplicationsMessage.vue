<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="max-w-4xl mx-auto p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 overflow-hidden">
      <!-- Colored banner attached to the container -->
      <div class="flex w-full">
        <div class="w-1/4 h-1.5 bg-blue-600 animate-pulse" style="animation-delay: 0.2s;"></div>
        <div class="w-1/4 h-1.5 bg-green-500 animate-pulse" style="animation-delay: 0.4s;"></div>
        <div class="w-1/4 h-1.5 bg-amber-500 animate-pulse" style="animation-delay: 0.6s;"></div>
        <div class="w-1/4 h-1.5 bg-red-500 animate-pulse" style="animation-delay: 0.8s;"></div>
      </div>
      
      <div class="p-8 text-center">
        <div class="flex flex-col items-center justify-center space-y-6">
          <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No submissions found</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Get started and make your first submission.</p>
            <Link href="/applications/select-form" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium px-6 py-3 rounded-xl transition duration-300 inline-flex items-center shadow-md relative overflow-hidden group">
              <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
              Create New
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>