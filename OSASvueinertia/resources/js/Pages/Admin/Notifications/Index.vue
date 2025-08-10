<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  notifications: Object,
});

// For delete confirmation
const showDeleteModal = ref(false);
const notificationToDelete = ref(null);

// For notification popup modal
const showNotificationModal = ref(false);
const selectedNotification = ref(null);

// Show confirmation dialog before deletion
const confirmDelete = (notification) => {
  notificationToDelete.value = notification;
  showDeleteModal.value = true;
};

// Delete notification
const deleteNotification = () => {
  router.delete(route('admin.notifications.destroy', notificationToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      notificationToDelete.value = null;
    },
  });
};

// Toggle active status of a notification
const toggleActive = (notification) => {
  router.patch(route('admin.notifications.toggle-active', notification.id), {}, {
    preserveScroll: true,
    preserveState: true,
  });
};

// Get badge class based on notification type
const getBadgeClass = (type) => {
  switch (type) {
    case 'success':
      return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
    case 'warning':
      return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300';
    case 'error':
      return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
    case 'info':
    default:
      return 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300';
  }
};

// Format notification type
const formatType = (type) => {
  if (!type) return '';
  return type.charAt(0).toUpperCase() + type.slice(1);
};

// Show notification popup
const showNotificationPopup = (notification) => {
  selectedNotification.value = notification;
  showNotificationModal.value = true;
};

// Close notification popup
const closeNotificationModal = () => {
  showNotificationModal.value = false;
  // Clear selected notification after a short delay to allow transition to complete
  setTimeout(() => {
    selectedNotification.value = null;
  }, 300);
};
</script>

<template>
  <AuthenticatedLayout :is-admin="true">
    <Head title="Manage Notifications" />
    
    <!-- Colored banner -->
    <div class="flex w-full mb-6 overflow-hidden rounded-md shadow-sm">
      <div class="w-1/4 h-1 bg-blue-500 " style="animation-delay: 0.2s;"></div>
      <div class="w-1/4 h-1 bg-green-500 " style="animation-delay: 0.4s;"></div>
      <div class="w-1/4 h-1 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
      <div class="w-1/4 h-1 bg-red-500 " style="animation-delay: 0.8s;"></div>
    </div>
    
    <div class="py-4 sm:py-6">
      <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
          <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 sm:mb-8">
              <h2 class="text-lg sm:text-xl font-medium text-gray-800 dark:text-gray-200">Manage Notifications</h2>
              <Link
                :href="route('admin.notifications.create')"
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out w-full sm:w-auto"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Notification
              </Link>
            </div>

            <!-- Responsive Notifications List -->
            <div class="hidden lg:block overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Target Audience</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-if="notifications.data.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                      <div class="flex flex-col items-center justify-center space-y-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span>No notifications found</span>
                      </div>
                    </td>
                  </tr>
                  <tr v-for="notification in notifications.data" :key="notification.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 cursor-pointer" @click="showNotificationPopup(notification)">
                    <td class="px-4 py-4">
                      <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ notification.title }}</div>
                      <div class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ notification.message }}</div>
                      <div class="text-xs text-blue-600 dark:text-blue-400 mt-1 opacity-75">Click to view full message</div>
                    </td>
                    <td class="px-4 py-4">
                      <span class="px-2 py-1 inline-flex text-xs leading-4 font-medium rounded-full" :class="getBadgeClass(notification.type)">
                        {{ formatType(notification.type) }}
                      </span>
                    </td>
                    <td class="px-4 py-4">
                      <div class="text-sm text-gray-900 dark:text-gray-100">
                        <span class="font-medium">{{ notification.target_audience }}</span>
                      </div>
                    </td>
                    <td class="px-4 py-4">
                      <button @click.stop="toggleActive(notification)" class="group relative flex items-center">
                        <span 
                          class="w-8 h-4 flex items-center flex-shrink-0 p-0.5 rounded-full duration-200 ease-in-out"
                          :class="{ 'bg-blue-500': notification.is_active, 'bg-gray-200 dark:bg-gray-600': !notification.is_active }"
                        >
                          <span 
                            class="bg-white w-3 h-3 rounded-full shadow-sm transform duration-200 ease-in-out"
                            :class="{ 'translate-x-4': notification.is_active, 'translate-x-0': !notification.is_active }"
                          ></span>
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2 hidden xl:inline">{{ notification.is_active ? 'Active' : 'Inactive' }}</span>
                      </button>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                      <div class="truncate max-w-24">{{ notification.created_at }}</div>
                    </td>
                    <td class="px-4 py-4 text-right">
                      <div class="flex justify-end space-x-2">
                        <Link 
                          :href="route('admin.notifications.edit', notification.id)" 
                          class="text-gray-500 dark:text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors duration-150"
                          title="Edit"
                          @click.stop
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>
                        <button 
                          @click.stop="confirmDelete(notification)" 
                          class="text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors duration-150"
                          title="Delete"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Medium Screen Table (Tablet) -->
            <div class="hidden md:block lg:hidden overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-if="notifications.data.length === 0">
                    <td colspan="4" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                      <div class="flex flex-col items-center justify-center space-y-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span>No notifications found</span>
                      </div>
                    </td>
                  </tr>
                  <tr v-for="notification in notifications.data" :key="notification.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 cursor-pointer" @click="showNotificationPopup(notification)">
                    <td class="px-3 py-4">
                      <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ notification.title }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs mt-1">{{ notification.message }}</div>
                      <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ notification.target_audience }}</div>
                      <div class="text-xs text-blue-600 dark:text-blue-400 mt-1 opacity-75">Click to view full message</div>
                    </td>
                    <td class="px-3 py-4">
                      <span class="px-2 py-1 inline-flex text-xs leading-4 font-medium rounded-full" :class="getBadgeClass(notification.type)">
                        {{ formatType(notification.type) }}
                      </span>
                    </td>
                    <td class="px-3 py-4">
                      <button @click.stop="toggleActive(notification)" class="group relative flex items-center">
                        <span 
                          class="w-8 h-4 flex items-center flex-shrink-0 p-0.5 rounded-full duration-200 ease-in-out"
                          :class="{ 'bg-blue-500': notification.is_active, 'bg-gray-200': !notification.is_active }"
                        >
                          <span 
                            class="bg-white w-3 h-3 rounded-full shadow-sm transform duration-200 ease-in-out"
                            :class="{ 'translate-x-4': notification.is_active, 'translate-x-0': !notification.is_active }"
                          ></span>
                        </span>
                      </button>
                    </td>
                    <td class="px-3 py-4 text-right">
                      <div class="flex justify-end space-x-2">
                        <Link 
                          :href="route('admin.notifications.edit', notification.id)" 
                          class="text-gray-500 dark:text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors duration-150"
                          title="Edit"
                          @click.stop
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>
                        <button 
                          @click.stop="confirmDelete(notification)" 
                          class="text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors duration-150"
                          title="Delete"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Mobile/Tablet Responsive Cards -->
            <div class="md:hidden flex flex-col gap-4">
              <div v-if="notifications.data.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-12">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  <span>No notifications found</span>
                </div>
              </div>
              <div v-for="notification in notifications.data" :key="notification.id" class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-100 dark:border-gray-700 p-4 flex flex-col gap-2 cursor-pointer hover:shadow-md transition-shadow duration-200" @click="showNotificationPopup(notification)">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ notification.title }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ notification.message }}</div>
                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1 opacity-75">Click to view full message</div>
                  </div>
                  <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-medium rounded-full h-fit" :class="getBadgeClass(notification.type)">
                    {{ formatType(notification.type) }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-400 mt-1">
                  <span><b class="text-gray-700 dark:text-gray-300">Audience:</b> {{ notification.target_audience }}</span>
                  <span><b class="text-gray-700 dark:text-gray-300">Status:</b> <span :class="notification.is_active ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500'">{{ notification.is_active ? 'Active' : 'Inactive' }}</span></span>
                  <span><b class="text-gray-700 dark:text-gray-300">Created:</b> {{ notification.created_at }}</span>
                </div>
                <div class="flex items-center gap-3 mt-2">
                  <button @click.stop="toggleActive(notification)" class="group relative flex items-center">
                    <span 
                      class="w-8 h-4 flex items-center flex-shrink-0 p-0.5 rounded-full duration-200 ease-in-out"
                      :class="{ 'bg-blue-500': notification.is_active, 'bg-gray-200': !notification.is_active }"
                    >
                      <span 
                        class="bg-white w-3 h-3 rounded-full shadow-sm transform duration-200 ease-in-out"
                        :class="{ 'translate-x-4': notification.is_active, 'translate-x-0': !notification.is_active }"
                      ></span>
                    </span>
                  </button>
                  <Link 
                    :href="route('admin.notifications.edit', notification.id)" 
                    class="text-gray-500 hover:text-blue-500 transition-colors duration-150"
                    title="Edit"
                    @click.stop
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Link>
                  <button 
                    @click.stop="confirmDelete(notification)" 
                    class="text-gray-500 hover:text-red-500 transition-colors duration-150"
                    title="Delete"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
              <Pagination :links="notifications.links" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="showDeleteModal = false">
      <div class="p-6 bg-white dark:bg-gray-800">
        <div class="flex items-center mb-5">
          <div class="flex-shrink-0 bg-red-100 dark:bg-red-900/20 rounded-full p-2 mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Notification</h3>
        </div>
        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
          Are you sure you want to delete this notification? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out"
          >
            Cancel
          </button>
          <button
            @click="deleteNotification"
            class="px-4 py-2 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out"
          >
            Delete
          </button>
        </div>
      </div>
    </Modal>

    <!-- Notification Details Modal -->
    <Modal :show="showNotificationModal" @close="closeNotificationModal" max-width="lg" :closeable="true">
      <div v-if="selectedNotification" class="p-3 sm:p-4 lg:p-6 max-h-[90vh] overflow-y-auto">
        <!-- Header with close button -->
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 mb-4 sm:mb-6">
          <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100 truncate pr-4">
            {{ selectedNotification?.title }}
          </h3>
          <div class="flex items-center space-x-3">
            <span v-if="selectedNotification?.type" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0" :class="getBadgeClass(selectedNotification.type)">
              {{ formatType(selectedNotification.type) }}
            </span>
            <button
              type="button"
              class="bg-gray-50 dark:bg-gray-700 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-1"
              @click="closeNotificationModal"
            >
              <span class="sr-only">Close</span>
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <!-- Content -->
        <div class="space-y-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div class="mx-auto flex items-center justify-center h-10 w-10 rounded-full"
                   :class="{
                     'bg-blue-100 dark:bg-blue-900/30': selectedNotification?.type === 'info',
                     'bg-green-100 dark:bg-green-900/30': selectedNotification?.type === 'success',
                     'bg-yellow-100 dark:bg-yellow-900/30': selectedNotification?.type === 'warning',
                     'bg-red-100 dark:bg-red-900/30': selectedNotification?.type === 'error',
                   }"
              >
                <!-- Success Icon -->
                <svg v-if="selectedNotification?.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                
                <!-- Warning Icon -->
                <svg v-else-if="selectedNotification?.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                
                <!-- Error Icon -->
                <svg v-else-if="selectedNotification?.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                
                <!-- Info Icon (default) -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            
            <div class="ml-4 w-full">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 text-sm">
                <div>
                  <span class="font-medium text-gray-700 dark:text-gray-300">Target Audience:</span>
                  <span class="text-gray-600 dark:text-gray-400 ml-1">{{ selectedNotification?.target_audience || 'N/A' }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-700 dark:text-gray-300">Created:</span>
                  <span class="text-gray-600 dark:text-gray-400 ml-1">{{ selectedNotification?.created_at || 'N/A' }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                  <span class="ml-1" :class="selectedNotification?.is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500'">
                    {{ selectedNotification?.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
              
              <div class="prose prose-sm max-w-none">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Message:</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                  {{ selectedNotification?.message || 'No message available' }}
                </p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="flex flex-col sm:flex-row pt-4 mt-6 border-t border-gray-200 dark:border-gray-700 space-y-3 sm:space-y-0 sm:space-x-3">
          <button
            type="button"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg shadow-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-300"
            @click="closeNotificationModal"
          >
            Close
          </button>
          <Link
            :href="route('admin.notifications.edit', selectedNotification?.id)"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-lg shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300"
            v-if="selectedNotification"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Notification
          </Link>
        </div>
      </div>
      
      <!-- Fallback content when no notification is selected -->
      <div v-else class="p-6 text-center">
        <div class="text-gray-500 dark:text-gray-400">
          Loading notification details...
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>