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
    <div class="flex w-full mb-6 overflow-hidden rounded-md shadow-sm">
      <div class="w-1/4 h-1 bg-blue-500 " style="animation-delay: 0.2s;"></div>
      <div class="w-1/4 h-1 bg-green-500 " style="animation-delay: 0.4s;"></div>
      <div class="w-1/4 h-1 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
      <div class="w-1/4 h-1 bg-red-500 " style="animation-delay: 0.8s;"></div>
    </div>

    <div class="py-6">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
              <div class="flex items-center">
                <h2 class="text-xl font-medium text-gray-800 dark:text-gray-200">Notifications</h2>
                <span v-if="unreadCount > 0" class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                  {{ unreadCount }} unread
                </span>
              </div>
              <button 
                v-if="unreadCount > 0"
                @click="markAllAsRead" 
                class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out"
              >
                Mark all as read
              </button>
            </div>

            <!-- Filters -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg mb-6 border border-gray-100 dark:border-gray-600 overflow-hidden">
              <div class="p-4">
                <div class="flex flex-wrap items-end gap-4">
                  <div>
                    <label for="type-filter" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Filter by type</label>
                    <select 
                      id="type-filter" 
                      v-model="filters.type" 
                      @change="applyFilters"
                      class="block w-full pl-3 pr-10 py-2 text-sm border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md dark:bg-gray-600 dark:text-gray-100"
                    >
                      <option value="all">All Types</option>
                      <option value="info">Info</option>
                      <option value="success">Success</option>
                      <option value="warning">Warning</option>
                      <option value="error">Error</option>
                    </select>
                  </div>
                  <div>
                    <label for="read-filter" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Filter by status</label>
                    <select 
                      id="read-filter" 
                      v-model="filters.read" 
                      @change="applyFilters"
                      class="block w-full pl-3 pr-10 py-2 text-sm border-gray-200 dark:border-gray-600 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md dark:bg-gray-600 dark:text-gray-100"
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

            <div v-else class="space-y-3">
              <button
                v-for="notification in filteredNotifications"
                :key="notification.id"
                @click="markAsRead(notification.id)"
                class="w-full block border rounded-lg overflow-hidden transition duration-200 ease-in-out hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
                :class="[notification.is_read ? 'border-gray-100 dark:border-gray-700' : 'border-blue-200 dark:border-blue-600 shadow-sm', getNotificationClass(notification.type)]"
              >
                <div class="p-4">
                  <div class="flex">
                    <div class="flex-shrink-0 pt-0.5">
                      <!-- Success Icon -->
                      <svg v-if="notification.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      
                      <!-- Warning Icon -->
                      <svg v-else-if="notification.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                      
                      <!-- Error Icon -->
                      <svg v-else-if="notification.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                      </svg>
                      
                      <!-- Info Icon (default) -->
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex flex-col sm:flex-row sm:justify-between mb-1">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100" :class="{'font-semibold': !notification.is_read}">
                          {{ notification.title }}
                          <span v-if="!notification.is_read" class="inline-block h-2 w-2 ml-2 rounded-full bg-blue-500 dark:bg-blue-400"></span>
                        </h3>
                        <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 mt-1 sm:mt-0">
                          <span class="mr-2">{{ notification.created_at }}</span>
                          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" 
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
                      <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ notification.message }}</p>
                    </div>
                  </div>
                </div>
              </button>
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