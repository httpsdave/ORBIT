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
    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-800">Manage Notifications</h2>
              <Link
                :href="route('admin.notifications.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Notification
              </Link>
            </div>

            <!-- Notifications Table -->
            <div class="overflow-x-auto">
              <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-if="notifications.data.length === 0">
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No notifications found</td>
                  </tr>
                  <tr v-for="notification in notifications.data" :key="notification.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ notification.title }}</div>
                      <div class="text-sm text-gray-500 truncate max-w-xs">{{ notification.message }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getBadgeClass(notification.type)">
                        {{ formatType(notification.type) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <button @click="toggleActive(notification)" class="group relative">
                        <span 
                          class="w-10 h-5 flex items-center flex-shrink-0 ml-2 p-0.5 rounded-full duration-300 ease-in-out"
                          :class="{ 'bg-blue-600': notification.is_active, 'bg-gray-300': !notification.is_active }"
                        >
                          <span 
                            class="bg-white w-4 h-4 rounded-full shadow-md transform duration-300 ease-in-out"
                            :class="{ 'translate-x-5': notification.is_active, 'translate-x-0': !notification.is_active }"
                          ></span>
                        </span>
                        <span class="text-xs text-gray-500 ml-2">{{ notification.is_active ? 'Active' : 'Inactive' }}</span>
                      </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ notification.created_at }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex justify-end space-x-2">
                        <Link 
                          :href="route('admin.notifications.edit', notification.id)" 
                          class="text-blue-600 hover:text-blue-900 focus:outline-none focus:underline"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>
                        <button 
                          @click="confirmDelete(notification)" 
                          class="text-red-600 hover:text-red-900 focus:outline-none focus:underline"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
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
        <h2 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h2>
        <p class="mb-6 text-sm text-gray-600">
          Are you sure you want to delete this notification? This action cannot be undone.
        </p>
        <div class="flex justify-end">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md mr-2 hover:bg-gray-400 focus:outline-none focus:bg-gray-400"
          >
            Cancel
          </button>
          <button
            @click="deleteNotification"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:bg-red-700"
          >
            Delete
          </button>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>