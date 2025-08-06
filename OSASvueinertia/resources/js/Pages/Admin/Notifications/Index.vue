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
      return 'bg-green-100 text-green-800';
    case 'warning':
      return 'bg-yellow-100 text-yellow-800';
    case 'error':
      return 'bg-red-100 text-red-800';
    case 'info':
    default:
      return 'bg-blue-100 text-blue-800';
  }
};

// Format notification type
const formatType = (type) => {
  return type.charAt(0).toUpperCase() + type.slice(1);
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
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 sm:mb-8">
              <h2 class="text-lg sm:text-xl font-medium text-gray-800">Manage Notifications</h2>
              <Link
                :href="route('admin.notifications.create')"
                class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out w-full sm:w-auto"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Notification
              </Link>
            </div>

            <!-- Responsive Notifications List -->
            <div class="hidden lg:block overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target Audience</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-if="notifications.data.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                      <div class="flex flex-col items-center justify-center space-y-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span>No notifications found</span>
                      </div>
                    </td>
                  </tr>
                  <tr v-for="notification in notifications.data" :key="notification.id" class="hover:bg-gray-50 transition duration-150">
                    <td class="px-4 py-4">
                      <div class="text-sm font-medium text-gray-900">{{ notification.title }}</div>
                      <div class="text-sm text-gray-500 truncate max-w-xs">{{ notification.message }}</div>
                    </td>
                    <td class="px-4 py-4">
                      <span class="px-2 py-1 inline-flex text-xs leading-4 font-medium rounded-full" :class="getBadgeClass(notification.type)">
                        {{ formatType(notification.type) }}
                      </span>
                    </td>
                    <td class="px-4 py-4">
                      <div class="text-sm text-gray-900">
                        <span class="font-medium">{{ notification.target_audience }}</span>
                      </div>
                    </td>
                    <td class="px-4 py-4">
                      <button @click="toggleActive(notification)" class="group relative flex items-center">
                        <span 
                          class="w-8 h-4 flex items-center flex-shrink-0 p-0.5 rounded-full duration-200 ease-in-out"
                          :class="{ 'bg-blue-500': notification.is_active, 'bg-gray-200': !notification.is_active }"
                        >
                          <span 
                            class="bg-white w-3 h-3 rounded-full shadow-sm transform duration-200 ease-in-out"
                            :class="{ 'translate-x-4': notification.is_active, 'translate-x-0': !notification.is_active }"
                          ></span>
                        </span>
                        <span class="text-xs text-gray-500 ml-2 hidden xl:inline">{{ notification.is_active ? 'Active' : 'Inactive' }}</span>
                      </button>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-500">
                      <div class="truncate max-w-24">{{ notification.created_at }}</div>
                    </td>
                    <td class="px-4 py-4 text-right">
                      <div class="flex justify-end space-x-2">
                        <Link 
                          :href="route('admin.notifications.edit', notification.id)" 
                          class="text-gray-500 hover:text-blue-500 transition-colors duration-150"
                          title="Edit"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>
                        <button 
                          @click="confirmDelete(notification)" 
                          class="text-gray-500 hover:text-red-500 transition-colors duration-150"
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
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-if="notifications.data.length === 0">
                    <td colspan="4" class="px-3 py-8 text-center text-gray-500">
                      <div class="flex flex-col items-center justify-center space-y-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span>No notifications found</span>
                      </div>
                    </td>
                  </tr>
                  <tr v-for="notification in notifications.data" :key="notification.id" class="hover:bg-gray-50 transition duration-150">
                    <td class="px-3 py-4">
                      <div class="text-sm font-medium text-gray-900">{{ notification.title }}</div>
                      <div class="text-xs text-gray-500 truncate max-w-xs mt-1">{{ notification.message }}</div>
                      <div class="text-xs text-gray-400 mt-1">{{ notification.target_audience }}</div>
                    </td>
                    <td class="px-3 py-4">
                      <span class="px-2 py-1 inline-flex text-xs leading-4 font-medium rounded-full" :class="getBadgeClass(notification.type)">
                        {{ formatType(notification.type) }}
                      </span>
                    </td>
                    <td class="px-3 py-4">
                      <button @click="toggleActive(notification)" class="group relative flex items-center">
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
                          class="text-gray-500 hover:text-blue-500 transition-colors duration-150"
                          title="Edit"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>
                        <button 
                          @click="confirmDelete(notification)" 
                          class="text-gray-500 hover:text-red-500 transition-colors duration-150"
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
              <div v-if="notifications.data.length === 0" class="text-center text-gray-500 py-12">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  <span>No notifications found</span>
                </div>
              </div>
              <div v-for="notification in notifications.data" :key="notification.id" class="bg-white rounded-lg shadow border border-gray-100 p-4 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-base font-semibold text-gray-900">{{ notification.title }}</div>
                    <div class="text-sm text-gray-500">{{ notification.message }}</div>
                  </div>
                  <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-medium rounded-full h-fit" :class="getBadgeClass(notification.type)">
                    {{ formatType(notification.type) }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-2 text-xs text-gray-600 mt-1">
                  <span><b>Audience:</b> {{ notification.target_audience }}</span>
                  <span><b>Status:</b> <span :class="notification.is_active ? 'text-blue-600' : 'text-gray-400'">{{ notification.is_active ? 'Active' : 'Inactive' }}</span></span>
                  <span><b>Created:</b> {{ notification.created_at }}</span>
                </div>
                <div class="flex items-center gap-3 mt-2">
                  <button @click="toggleActive(notification)" class="group relative flex items-center">
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
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Link>
                  <button 
                    @click="confirmDelete(notification)" 
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
      <div class="p-6">
        <div class="flex items-center mb-5">
          <div class="flex-shrink-0 bg-red-100 rounded-full p-2 mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900">Delete Notification</h3>
        </div>
        <p class="mb-6 text-sm text-gray-600">
          Are you sure you want to delete this notification? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out"
          >
            Cancel
          </button>
          <button
            @click="deleteNotification"
            class="px-4 py-2 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 ease-in-out"
          >
            Delete
          </button>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>