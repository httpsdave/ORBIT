<template>
  <div class="relative" ref="dropdownRef">
    <!-- Notification Bell Button -->
    <button
      @click="toggleDropdown"
      class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300 group relative"
      aria-label="Notifications"
      :aria-expanded="isOpen"
    >
      <div class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <!-- Unread notification badge -->
        <span 
          v-if="unreadCount > 0" 
          class="absolute -top-1 -right-1 flex items-center justify-center h-4 w-4 text-xs text-white bg-red-500 rounded-full animate-pulse"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>
      </div>
      <!-- Tooltip -->
      <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
        Notifications
      </span>
    </button>

    <!-- Dropdown Menu -->
    <div 
      v-show="isOpen"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-96 overflow-hidden"
      @click.stop
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200"
          >
            Mark all read
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="px-4 py-6 text-center">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
        <p class="text-sm text-gray-500 mt-2">Loading notifications...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="recentNotifications.length === 0" class="px-4 py-6 text-center">
        <div class="flex justify-center mb-3">
          <div class="rounded-full bg-gray-100 p-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </div>
        </div>
        <p class="text-sm font-medium text-gray-600">No notifications</p>
        <p class="text-xs text-gray-500">You're all caught up!</p>
      </div>

      <!-- Notifications List -->
      <div v-else class="max-h-64 overflow-y-auto">
        <button
          v-for="notification in recentNotifications"
          :key="notification.id"
          @click="handleNotificationClick(notification.id)"
          class="w-full px-4 py-3 hover:bg-gray-50 focus:bg-gray-50 focus:outline-none transition-colors duration-200 border-b border-gray-100 last:border-b-0"
          :class="{ 'bg-blue-50': !notification.is_read }"
        >
          <div class="flex items-start space-x-3">
            <!-- Icon -->
            <div class="flex-shrink-0 pt-1">
              <!-- Success Icon -->
              <svg v-if="notification.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              
              <!-- Warning Icon -->
              <svg v-else-if="notification.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              
              <!-- Error Icon -->
              <svg v-else-if="notification.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              
              <!-- Info Icon (default) -->
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
            
            <!-- Content -->
            <div class="flex-1 min-w-0 text-left">
              <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-medium text-gray-900 truncate" :class="{ 'font-semibold': !notification.is_read }">
                  {{ notification.title }}
                </p>
                <div class="flex items-center space-x-2">
                  <span v-if="!notification.is_read" class="inline-block h-2 w-2 rounded-full bg-blue-500"></span>
                  <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium" 
                    :class="{
                      'bg-blue-100 text-blue-700': notification.type === 'info',
                      'bg-green-100 text-green-700': notification.type === 'success',
                      'bg-yellow-100 text-yellow-700': notification.type === 'warning',
                      'bg-red-100 text-red-700': notification.type === 'error',
                    }"
                  >
                    {{ formatType(notification.type) }}
                  </span>
                </div>
              </div>
              <p class="text-xs text-gray-600 line-clamp-2">{{ notification.message }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ formatTimeAgo(notification.created_at) }}</p>
            </div>
          </div>
        </button>
      </div>

      <!-- Footer -->
      <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
        <button
          @click="viewAllNotifications"
          class="block w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200"
        >
          View all notifications
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

// Define route helper if not available globally
const route = window.route || ((name, params) => {
  // Fallback route helper - you may need to adjust this based on your setup
  const routes = {
    'notifications.recent': '/notifications/recent',
    'notifications.index': '/notifications',
    'notifications.mark-read': (id) => `/notifications/${id}/mark-read`,
    'notifications.mark-all-read': '/notifications/mark-all-read',
    'admin.notifications.index': '/admin/notifications'
  };
  
  if (typeof routes[name] === 'function') {
    return routes[name](params);
  }
  return routes[name] || '#';
});

const props = defineProps({
  isAdmin: {
    type: Boolean,
    default: false
  }
});

// Refs
const dropdownRef = ref(null);
const isOpen = ref(false);
const loading = ref(false);
const recentNotifications = ref([]);

// Get page props
const page = usePage();

// Computed properties
const unreadCount = computed(() => {
  return page.props.auth && 'unreadNotificationsCount' in page.props.auth
    ? page.props.auth.unreadNotificationsCount
    : 0;
});

// Methods
const toggleDropdown = async () => {
  isOpen.value = !isOpen.value;
  
  if (isOpen.value && recentNotifications.value.length === 0) {
    await fetchRecentNotifications();
  }
};

const closeDropdown = () => {
  isOpen.value = false;
};

const fetchRecentNotifications = async () => {
  loading.value = true;
  try {
    // Use the dedicated recent notifications endpoint
    const url = route('notifications.recent');
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin'
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    recentNotifications.value = data.notifications?.data || [];
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
    // Set empty array on error to prevent UI issues
    recentNotifications.value = [];
  } finally {
    loading.value = false;
  }
};

const handleNotificationClick = (notificationId) => {
  try {
    // Mark the notification as read and then navigate
    router.patch(route('notifications.mark-read', notificationId), {}, {
      preserveScroll: true,
      preserveState: true,
      only: ['auth.unreadNotificationsCount'],
      onSuccess: () => {
        // Update the local notification state
        const notification = recentNotifications.value.find(n => n.id === notificationId);
        if (notification) {
          notification.is_read = true;
        }
        
        // Close dropdown and navigate to notifications index
        closeDropdown();
        
        // Navigate to the notifications index page
        const targetRoute = props.isAdmin ? route('admin.notifications.index') : route('notifications.index');
        router.visit(targetRoute);
      },
      onError: (errors) => {
        console.error('Error marking notification as read:', errors);
        // Even if marking as read fails, still navigate to notifications
        closeDropdown();
        const targetRoute = props.isAdmin ? route('admin.notifications.index') : route('notifications.index');
        router.visit(targetRoute);
      }
    });
  } catch (error) {
    console.error('Error in handleNotificationClick:', error);
    // Fallback: just navigate to notifications page
    closeDropdown();
    const fallbackUrl = props.isAdmin ? '/admin/notifications' : '/notifications';
    window.location.href = fallbackUrl;
  }
};

const markAllAsRead = () => {
  try {
    router.post(route('notifications.mark-all-read'), {}, {
      preserveScroll: true,
      preserveState: true,
      only: ['auth.unreadNotificationsCount'],
      onSuccess: () => {
        // Update all local notifications to read
        recentNotifications.value.forEach(notification => {
          notification.is_read = true;
        });
      },
      onError: (errors) => {
        console.error('Error marking all notifications as read:', errors);
      }
    });
  } catch (error) {
    console.error('Error in markAllAsRead:', error);
  }
};

const viewAllNotifications = () => {
  try {
    closeDropdown();
    // Use router.visit to ensure proper Inertia navigation without query params
    const targetRoute = props.isAdmin ? route('admin.notifications.index') : route('notifications.index');
    router.visit(targetRoute);
  } catch (error) {
    console.error('Error navigating to notifications:', error);
    // Fallback to direct navigation
    window.location.href = props.isAdmin ? '/admin/notifications' : '/notifications';
  }
};

const formatType = (type) => {
  return type.charAt(0).toUpperCase() + type.slice(1);
};

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);
  
  if (diffInSeconds < 60) {
    return 'Just now';
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60);
    return `${minutes}m ago`;
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600);
    return `${hours}h ago`;
  } else if (diffInSeconds < 604800) {
    const days = Math.floor(diffInSeconds / 86400);
    return `${days}d ago`;
  } else {
    return date.toLocaleDateString();
  }
};

// Click outside to close
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown();
  }
};

// Keyboard navigation
const handleKeyDown = (event) => {
  if (event.key === 'Escape' && isOpen.value) {
    closeDropdown();
  }
};

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
/* Custom line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth animations */
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}


@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

</style>