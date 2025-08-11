<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  notifications: Object,
});

// Get page props to safely access auth data
const page = usePage();

// Safely access unreadNotificationsCount with proper fallback
const unreadCount = computed(() => {
  // Check if auth exists and if unreadNotificationsCount exists within auth
  return page.props.auth && 'unreadNotificationsCount' in page.props.auth
    ? page.props.auth.unreadNotificationsCount
    : 0;
});

// Modal state for notification popup
const showModal = ref(false);
const selectedNotification = ref(null);

// Filter options
const filters = ref({
  type: 'all',
  read: 'all'
});

// Apply filtering
const filteredNotifications = computed(() => {
  let result = props.notifications.data;
  
  // Filter by type
  if (filters.value.type !== 'all') {
    result = result.filter(notification => notification.type === filters.value.type);
  }
  
  // Filter by read status
  if (filters.value.read !== 'all') {
    const isRead = filters.value.read === 'read';
    result = result.filter(notification => notification.is_read === isRead);
  }
  
  return result;
});

// Handle filters change
const applyFilters = () => {
  router.get(route('notifications.index'), {
    type: filters.value.type,
    read: filters.value.read
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['notifications']
  });
};

// Show notification popup
const showNotificationPopup = (notification) => {
  selectedNotification.value = notification;
  showModal.value = true;
  
  // Mark as read when viewing
  if (!notification.is_read) {
    markAsRead(notification.id);
  }
};

// Close popup
const closeModal = () => {
  showModal.value = false;
  selectedNotification.value = null;
};

// Mark notification as read - Updated for your custom pivot implementation
const markAsRead = (notificationId) => {
  router.patch(route('notifications.mark-read', notificationId), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      // If you need to do anything after success
    },
    onError: (errors) => {
      console.error(errors);
    }
  });
};

// Mark all as read - Updated for your custom pivot implementation
const markAllAsRead = () => {
  router.post(route('notifications.mark-all-read'), {}, {
    preserveScroll: true,
    preserveState: true,
  });
};

// Get notification class based on type
const getNotificationClass = (type) => {
  switch (type) {
    case 'success':
      return 'border-green-100 dark:border-green-800 bg-green-50 dark:bg-green-900/20';
    case 'warning':
      return 'border-yellow-100 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20';
    case 'error':
      return 'border-red-100 dark:border-red-800 bg-red-50 dark:bg-red-900/20';
    case 'info':
    default:
      return 'border-blue-100 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20';
  }
};

// Human readable format type
const formatType = (type) => {
  return type.charAt(0).toUpperCase() + type.slice(1);
};
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Notifications" />
    
    <!-- Colored banner -->
    <div class="flex w-full mb-6 overflow-hidden rounded-xl shadow-sm">
      <div class="w-1/4 h-1 bg-blue-500 " style="animation-delay: 0.2s;"></div>
      <div class="w-1/4 h-1 bg-green-500 " style="animation-delay: 0.4s;"></div>
      <div class="w-1/4 h-1 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
      <div class="w-1/4 h-1 bg-red-500 " style="animation-delay: 0.8s;"></div>
    </div>

    <div class="py-6">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl">
          <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
              <div class="flex items-center">
                <h2 class="text-lg sm:text-xl font-medium text-gray-800 dark:text-gray-200">Notifications</h2>
                <span v-if="unreadCount > 0" class="ml-2 sm:ml-3 inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                  {{ unreadCount }} unread
                </span>
              </div>
              <button 
                v-if="unreadCount > 0"
                @click="markAllAsRead" 
                class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-500 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-wider hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out"
              >
                <span class="hidden sm:inline">Mark all as read</span>
                <span class="sm:hidden">Mark all read</span>
              </button>
            </div>

            <!-- Filters -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-xl mb-6 border border-gray-100 dark:border-gray-600 overflow-hidden">
              <div class="p-3 sm:p-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-3 sm:gap-4">
                  <div class="w-full sm:w-auto">
                    <label for="type-filter" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Filter by type</label>
                    <select 
                      id="type-filter" 
                      v-model="filters.type" 
                      @change="applyFilters"
                      class="block w-full sm:w-auto pl-3 pr-10 py-2 text-sm border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg dark:bg-gray-600 dark:text-gray-100"
                    >
                      <option value="all">All Types</option>
                      <option value="info">Info</option>
                      <option value="success">Success</option>
                      <option value="warning">Warning</option>
                      <option value="error">Error</option>
                    </select>
                  </div>
                  <div class="w-full sm:w-auto">
                    <label for="read-filter" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Filter by status</label>
                    <select 
                      id="read-filter" 
                      v-model="filters.read" 
                      @change="applyFilters"
                      class="block w-full sm:w-auto pl-3 pr-10 py-2 text-sm border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-lg dark:bg-gray-600 dark:text-gray-100"
                    >
                      <option value="all">All</option>
                      <option value="read">Read</option>
                      <option value="unread">Unread</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notifications List -->
            <div v-if="props.notifications.data.length === 0" class="text-center py-12 px-4">
              <div class="flex justify-center mb-4">
                <div class="rounded-full bg-gray-100 dark:bg-gray-700 p-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                </div>
              </div>
              <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-1">No notifications</h3>
              <p class="text-gray-500 dark:text-gray-400 text-sm">You're all caught up!</p>
            </div>

            <div v-else class="space-y-2 sm:space-y-3">
              <button
                v-for="notification in filteredNotifications"
                :key="notification.id"
                @click="showNotificationPopup(notification)"
                class="w-full block border rounded-xl overflow-hidden transition duration-200 ease-in-out hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transform hover:scale-[1.01]"
                :class="[notification.is_read ? 'border-gray-100 dark:border-gray-700' : 'border-blue-200 dark:border-blue-600 shadow-sm', getNotificationClass(notification.type)]"
              >
                <div class="p-3 sm:p-4">
                  <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 pt-0.5">
                      <!-- Success Icon -->
                      <svg v-if="notification.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      
                      <!-- Warning Icon -->
                      <svg v-else-if="notification.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                      
                      <!-- Error Icon -->
                      <svg v-else-if="notification.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                      </svg>
                      
                      <!-- Info Icon (default) -->
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-2">
                        <div class="flex items-center mb-1 sm:mb-0">
                          <h3 class="text-sm sm:text-base font-medium text-gray-900 dark:text-gray-100 truncate" :class="{'font-semibold': !notification.is_read}">
                            {{ notification.title }}
                          </h3>
                          <span v-if="!notification.is_read" class="inline-block h-2 w-2 ml-2 rounded-full bg-blue-500 dark:bg-blue-400 flex-shrink-0"></span>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end space-x-2 sm:space-x-3 text-xs text-gray-500 dark:text-gray-400">
                          <span class="hidden sm:inline">{{ notification.created_at }}</span>
                          <span class="sm:hidden">{{ notification.created_at.split(' ')[0] }}</span>
                          <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-xs font-medium" 
                            :class="{
                              'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300': notification.type === 'info',
                              'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300': notification.type === 'success',
                              'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300': notification.type === 'warning',
                              'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300': notification.type === 'error',
                            }"
                          >
                            {{ formatType(notification.type) }}
                          </span>
                        </div>
                      </div>
                      <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 line-clamp-2 sm:line-clamp-1">
                        {{ notification.message }}
                      </p>
                      <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 opacity-75">
                        Click to view full message
                      </p>
                    </div>
                  </div>
                </div>
              </button>
            </div>

            <!-- Notification Popup Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
              <div class="flex items-center justify-center min-h-screen px-4 py-4 text-center">
                <!-- Background overlay -->
                <div 
                  class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                  aria-hidden="true" 
                  @click="closeModal"
                ></div>

                <!-- Modal panel -->
                <div class="relative inline-block align-middle bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg mx-auto">
                  <!-- Header with close button -->
                  <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 flex items-center justify-between border-b border-gray-200 dark:border-gray-600">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 truncate pr-4" id="modal-title">
                      {{ selectedNotification?.title }}
                    </h3>
                    <div class="flex items-center space-x-3">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0" 
                        :class="{
                          'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300': selectedNotification?.type === 'info',
                          'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300': selectedNotification?.type === 'success',
                          'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300': selectedNotification?.type === 'warning',
                          'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300': selectedNotification?.type === 'error',
                        }"
                      >
                        {{ formatType(selectedNotification?.type) }}
                      </span>
                      <button
                        type="button"
                        class="bg-gray-50 dark:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-1"
                        @click="closeModal"
                      >
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <!-- Content -->
                  <div v-if="selectedNotification" class="px-4 py-5 sm:px-6">
                    <div class="flex items-start">
                      <div class="flex-shrink-0">
                        <div class="mx-auto flex items-center justify-center h-10 w-10 rounded-full"
                             :class="{
                               'bg-blue-100 dark:bg-blue-900/30': selectedNotification.type === 'info',
                               'bg-green-100 dark:bg-green-900/30': selectedNotification.type === 'success',
                               'bg-yellow-100 dark:bg-yellow-900/30': selectedNotification.type === 'warning',
                               'bg-red-100 dark:bg-red-900/30': selectedNotification.type === 'error',
                             }"
                        >
                          <!-- Success Icon -->
                          <svg v-if="selectedNotification.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                          </svg>
                          
                          <!-- Warning Icon -->
                          <svg v-else-if="selectedNotification.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                          </svg>
                          
                          <!-- Error Icon -->
                          <svg v-else-if="selectedNotification.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                          </svg>
                          
                          <!-- Info Icon (default) -->
                          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                          </svg>
                        </div>
                      </div>
                      
                      <div class="ml-4 w-full">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                          {{ selectedNotification.created_at }}
                        </p>
                        <div class="prose prose-sm max-w-none">
                          <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                            {{ selectedNotification.message }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Footer -->
                  <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200 dark:border-gray-600">
                    <button
                      type="button"
                      class="w-full inline-flex items-center justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-base font-medium text-white hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
                      @click="closeModal"
                    >
                      <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                      Close
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="props.notifications.data.length > 0" class="mt-6">
              <Pagination :links="props.notifications.links" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Line clamp utilities for message truncation */
.line-clamp-1 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 1;
  line-clamp: 1;
}

.line-clamp-2 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
}

/* Responsive line clamp for different screen sizes */
@media (min-width: 640px) {
  .sm\:line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    line-clamp: 1;
  }
}
</style>