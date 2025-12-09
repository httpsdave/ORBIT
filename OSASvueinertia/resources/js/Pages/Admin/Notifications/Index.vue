<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  notifications: Object,
});

// For delete confirmation
const showDeleteModal = ref(false);
const notificationToDelete = ref(null);

// For bulk delete
const showBulkDeleteModal = ref(false);
const selectedNotifications = ref([]);

// For notification popup modal
const showNotificationModal = ref(false);
const selectedNotification = ref(null);

// Refs for checkboxes to handle indeterminate state
const selectAllCheckboxes = ref([]);

// Computed properties for bulk operations
const isAllSelected = computed(() => {
  return props.notifications.data.length > 0 && selectedNotifications.value.length === props.notifications.data.length;
});

const isSomeSelected = computed(() => {
  return selectedNotifications.value.length > 0 && selectedNotifications.value.length < props.notifications.data.length;
});

const hasSelectedNotifications = computed(() => {
  return selectedNotifications.value.length > 0;
});

// Watch for changes in selection state to update indeterminate property
watch([isAllSelected, isSomeSelected], () => {
  nextTick(() => {
    // Update all select-all checkboxes
    document.querySelectorAll('input[type="checkbox"][data-select-all]').forEach(checkbox => {
      checkbox.indeterminate = isSomeSelected.value;
    });
  });
});

// Toggle all notifications selection
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedNotifications.value = [];
  } else {
    selectedNotifications.value = props.notifications.data.map(notification => notification.id);
  }
};

// Toggle individual notification selection
const toggleNotificationSelection = (notificationId) => {
  const index = selectedNotifications.value.indexOf(notificationId);
  if (index > -1) {
    selectedNotifications.value.splice(index, 1);
  } else {
    selectedNotifications.value.push(notificationId);
  }
};

// Show confirmation dialog before deletion
const confirmDelete = (notification) => {
  notificationToDelete.value = notification;
  showDeleteModal.value = true;
};

// Show confirmation dialog before bulk deletion
const confirmBulkDelete = () => {
  showBulkDeleteModal.value = true;
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

// Bulk delete notifications
const bulkDeleteNotifications = () => {
  router.delete(route('admin.notifications.bulk-destroy'), {
    data: {
      notification_ids: selectedNotifications.value
    },
    preserveScroll: true,
    onSuccess: () => {
      showBulkDeleteModal.value = false;
      selectedNotifications.value = [];
    },
  });
};

// Toggle active status of a notification (optimistic, in-place update)
const toggleActive = (notification) => {
  // Optimistically update UI
  const originalState = notification.is_active;
  notification.is_active = !originalState;

  router.patch(route('admin.notifications.toggle-active', notification.id), {}, {
    preserveScroll: true,
    preserveState: true,
    onError: () => {
      // Revert UI if request fails
      notification.is_active = originalState;
    },
    onSuccess: (page) => {
      // If the server returns updated notification data, try to sync fields
      // Look for the notification in page.props (Inertia response) and update local props if present
      try {
        const updated = page.props?.notifications?.data?.find(n => n.id === notification.id);
        if (updated) {
          // copy relevant fields
          notification.is_active = updated.is_active;
        }
      } catch (e) {
        // ignore errors - we already applied optimistic update
      }
    }
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

<style scoped>
/* Handle indeterminate checkbox state */
input[type="checkbox"]:indeterminate {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M4 8h8'/%3e%3c/svg%3e");
}

/* Line clamp for message truncation */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Extra small screens breakpoint */
@media (max-width: 374px) {
  /* Ensure containers don't overflow */
  .notification-card {
    margin-left: 0;
    margin-right: 0;
  }
  
  /* Reduce padding on very small screens */
  .notification-card-content {
    padding: 0.5rem;
  }
  
  /* Stack elements more tightly */
  .notification-metadata {
    gap: 0.25rem;
  }
  
  /* Make badges smaller */
  .notification-badge {
    padding: 0.125rem 0.375rem;
    font-size: 0.625rem;
  }
}

/* Handle xs breakpoint for flexbox */
@media (min-width: 375px) {
  .xs\:flex-row {
    flex-direction: row;
  }
  
  .xs\:justify-between {
    justify-content: space-between;
  }
  
  .xs\:items-start {
    align-items: flex-start;
  }
  
  .xs\:items-center {
    align-items: center;
  }
  
  .xs\:w-auto {
    width: auto;
  }
  
  .xs\:inline {
    display: inline;
  }
  
  .xs\:hidden {
    display: none;
  }
}
</style>

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
      <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 xl:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
          <div class="p-3 sm:p-6">
            <div class="flex flex-col gap-3 sm:gap-4 mb-4 sm:mb-8">
              <!-- Header with title and create button -->
              <div class="flex flex-col xs:flex-row xs:justify-between xs:items-start gap-3">
                <h2 class="text-lg sm:text-xl font-medium text-gray-800 dark:text-gray-200">Manage Notifications</h2>
                <Link
                  :href="route('admin.notifications.create')"
                  class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group w-full xs:w-auto"
                >
                  <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                  </svg>
                  <span class="hidden xs:inline">Make an Announcement</span>
                  <span class="xs:hidden">Announce</span>
                </Link>
              </div>
              
              <!-- Selection info and bulk delete -->
              <div v-if="hasSelectedNotifications" class="flex flex-col xs:flex-row xs:items-center gap-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <div class="flex items-center gap-2 flex-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    {{ selectedNotifications.length }} notification{{ selectedNotifications.length !== 1 ? 's' : '' }} selected
                  </span>
                </div>
                <button
                  @click="confirmBulkDelete"
                  class="inline-flex items-center justify-center px-3 py-1.5 bg-red-500 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 ease-in-out w-full xs:w-auto"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Delete Selected
                </button>
              </div>
            </div>

            <!-- Responsive Notifications List -->
            <div class="hidden lg:block overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-left">
                      <input
                        type="checkbox"
                        :checked="isAllSelected"
                        :indeterminate="isSomeSelected"
                        @change="toggleSelectAll"
                        data-select-all
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Target Audience</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
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
                  <tr v-for="notification in notifications.data" :key="notification.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                    <td class="px-4 py-4" @click.stop>
                      <input
                        type="checkbox"
                        :checked="selectedNotifications.includes(notification.id)"
                        @change="toggleNotificationSelection(notification.id)"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                    </td>
                    <td class="px-4 py-4 cursor-pointer" @click="showNotificationPopup(notification)">
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
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Medium Screen Table (Tablet) -->
            <div class="hidden md:block lg:hidden overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                  <tr>
                    <th scope="col" class="px-3 py-3 text-left">
                      <input
                        type="checkbox"
                        :checked="isAllSelected"
                        :indeterminate="isSomeSelected"
                        @change="toggleSelectAll"
                        data-select-all
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                    </th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Details</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
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
                  <tr v-for="notification in notifications.data" :key="notification.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                    <td class="px-3 py-4" @click.stop>
                      <input
                        type="checkbox"
                        :checked="selectedNotifications.includes(notification.id)"
                        @change="toggleNotificationSelection(notification.id)"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                    </td>
                    <td class="px-3 py-4 cursor-pointer" @click="showNotificationPopup(notification)">
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
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Mobile/Tablet Responsive Cards -->
            <div class="md:hidden">
              <!-- Mobile selection header - Only show select all when no items are selected -->
              <div v-if="notifications.data.length > 0 && !hasSelectedNotifications" class="flex items-center justify-between mb-3 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center gap-2">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    :indeterminate="isSomeSelected"
                    @change="toggleSelectAll"
                    data-select-all
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Select all
                  </span>
                </div>
              </div>

              <div class="flex flex-col gap-2">
                <div v-if="notifications.data.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="text-sm">No notifications found</span>
                  </div>
                </div>
                <div v-for="notification in notifications.data" :key="notification.id" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-2.5 hover:shadow-md transition-shadow duration-200">
                  <!-- Main content row -->
                  <div class="flex items-start gap-2">
                    <input
                      type="checkbox"
                      :checked="selectedNotifications.includes(notification.id)"
                      @change="toggleNotificationSelection(notification.id)"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-0.5 flex-shrink-0"
                      @click.stop
                    />
                    <div class="flex-1 min-w-0 cursor-pointer" @click="showNotificationPopup(notification)">
                      <!-- Title and badge row -->
                      <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 leading-tight flex-1 min-w-0 pr-1">{{ notification.title }}</h3>
                        <span class="px-1.5 py-0.5 inline-flex text-xs leading-3 font-medium rounded-full flex-shrink-0" :class="getBadgeClass(notification.type)">
                          {{ formatType(notification.type) }}
                        </span>
                      </div>
                      
                      <!-- Message -->
                      <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 line-clamp-2">{{ notification.message }}</p>
                      
                      <!-- Metadata row -->
                      <div class="flex flex-wrap gap-1 mb-2">
                        <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded text-xs">{{ notification.target_audience }}</span>
                        <span class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-xs" :class="notification.is_active ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500'">{{ notification.is_active ? 'Active' : 'Inactive' }}</span>
                        <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded text-xs">{{ notification.created_at }}</span>
                      </div>
                      
                      <!-- Action hint -->
                      <div class="text-xs text-blue-600 dark:text-blue-400 opacity-75">Tap to view full message</div>
                    </div>
                  </div>
                  
                  <!-- Status toggle row -->
                  <div class="flex items-center justify-end mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-1">
                      <span class="text-xs text-gray-500 dark:text-gray-400">Status:</span>
                      <button @click.stop="toggleActive(notification)" class="group relative flex items-center">
                        <span 
                          class="w-6 h-3 flex items-center flex-shrink-0 p-0.5 rounded-full duration-200 ease-in-out"
                          :class="{ 'bg-blue-500': notification.is_active, 'bg-gray-200 dark:bg-gray-600': !notification.is_active }"
                        >
                          <span 
                            class="bg-white w-2 h-2 rounded-full shadow-sm transform duration-200 ease-in-out"
                            :class="{ 'translate-x-3': notification.is_active, 'translate-x-0': !notification.is_active }"
                          ></span>
                        </span>
                      </button>
                    </div>
                  </div>
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

    <!-- Bulk Delete Confirmation Modal -->
    <Modal :show="showBulkDeleteModal" @close="showBulkDeleteModal = false">
      <div class="p-6 bg-white dark:bg-gray-800">
        <div class="flex items-center mb-5">
          <div class="flex-shrink-0 bg-red-100 dark:bg-red-900/20 rounded-full p-2 mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Multiple Notifications</h3>
        </div>
        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
          Are you sure you want to delete {{ selectedNotifications.length }} notification{{ selectedNotifications.length !== 1 ? 's' : '' }}? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showBulkDeleteModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out"
          >
            Cancel
          </button>
          <button
            @click="bulkDeleteNotifications"
            class="px-4 py-2 text-sm font-medium text-white bg-red-500 border border-transparent rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out"
          >
            Delete {{ selectedNotifications.length }} Notification{{ selectedNotifications.length !== 1 ? 's' : '' }}
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
              
              <!-- Show specific users if available -->
              <div v-if="selectedNotification?.target_users && selectedNotification.target_users.length > 0" class="mb-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Target Users:</h4>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 max-h-40 overflow-y-auto">
                  <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    <li v-for="(user, index) in selectedNotification.target_users" :key="index" class="flex items-start">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 mt-0.5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      <span>{{ user }}</span>
                    </li>
                  </ul>
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