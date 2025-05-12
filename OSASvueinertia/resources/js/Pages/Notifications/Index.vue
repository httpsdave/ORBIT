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
    only: ['notifications', 'auth.unreadNotificationsCount'],
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
      return 'border-green-200 bg-green-50';
    case 'warning':
      return 'border-yellow-200 bg-yellow-50';
    case 'error':
      return 'border-red-200 bg-red-50';
    case 'info':
    default:
      return 'border-blue-200 bg-blue-50';
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

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-800">Notifications</h2>
              <button 
                v-if="unreadCount > 0"
                @click="markAllAsRead" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
              >
                Mark all as read
              </button>
            </div>

            <!-- Filters -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
              <div class="flex flex-wrap gap-4">
                <div>
                  <label for="type-filter" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                  <select 
                    id="type-filter" 
                    v-model="filters.type" 
                    @change="applyFilters"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                  >
                    <option value="all">All Types</option>
                    <option value="info">Info</option>
                    <option value="success">Success</option>
                    <option value="warning">Warning</option>
                    <option value="error">Error</option>
                  </select>
                </div>
                <div>
                  <label for="read-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select 
                    id="read-filter" 
                    v-model="filters.read" 
                    @change="applyFilters"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                  >
                    <option value="all">All</option>
                    <option value="read">Read</option>
                    <option value="unread">Unread</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Notifications List -->
            <div v-if="props.notifications.data.length === 0" class="text-center py-12">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900 mb-1">No notifications</h3>
              <p class="text-gray-500">You don't have any notifications at the moment.</p>
            </div>

            <div v-else class="space-y-4">
              <button
                v-for="notification in filteredNotifications"
                :key="notification.id"
                @click="markAsRead(notification.id)"
                class="w-full block border rounded-lg overflow-hidden transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                :class="[notification.is_read ? 'border-gray-200' : 'border-blue-300 shadow-sm', getNotificationClass(notification.type)]"
              >
                <div class="p-4">
                  <div class="flex items-start">
                    <div class="flex-shrink-0 mr-4">
                      <!-- Success Icon -->
                      <svg v-if="notification.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      
                      <!-- Warning Icon -->
                      <svg v-else-if="notification.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                      
                      <!-- Error Icon -->
                      <svg v-else-if="notification.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                      </svg>
                      
                      <!-- Info Icon (default) -->
                      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </div>
                    <div class="flex-grow">
                      <div class="flex justify-between items-center mb-1">
                        <h3 class="text-sm font-semibold text-gray-900" :class="{'font-bold': !notification.is_read}">
                          {{ notification.title }}
                        </h3>
                        <div class="flex items-center space-x-2">
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                            :class="{
                              'bg-blue-100 text-blue-800': notification.type === 'info',
                              'bg-green-100 text-green-800': notification.type === 'success',
                              'bg-yellow-100 text-yellow-800': notification.type === 'warning',
                              'bg-red-100 text-red-800': notification.type === 'error',
                            }"
                          >
                            {{ formatType(notification.type) }}
                          </span>
                          <span class="text-xs text-gray-500">{{ notification.created_at }}</span>
                          <div v-if="!notification.is_read" class="h-2 w-2 rounded-full bg-blue-500"></div>
                        </div>
                      </div>
                      <p class="text-sm text-gray-600">{{ notification.message }}</p>
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