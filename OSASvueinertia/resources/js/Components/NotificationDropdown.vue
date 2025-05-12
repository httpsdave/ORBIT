<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { Transition } from 'vue';

const props = defineProps({
  align: {
    type: String,
    default: 'right',
  },
});

const page = usePage();
const dropdownOpen = ref(false);
const dropdownRef = ref(null);

const unreadNotificationsCount = computed(() => page.props.auth.unreadNotificationsCount);
const recentNotifications = computed(() => page.props.auth.recentNotifications || []);

const close = () => {
  dropdownOpen.value = false;
};

const toggle = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const closeOnEscape = (e) => {
  if (e.key === 'Escape' && dropdownOpen.value) {
    close();
  }
};

const closeOnClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    close();
  }
};

// Mark a notification as read
const markAsRead = (notificationId) => {
  router.post(route('notifications.mark-read', notificationId), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      // We don't need to do anything here as the page props will be updated
    },
  });
};

// Mark all notifications as read
const markAllAsRead = () => {
  router.post(route('notifications.mark-all-read'), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      close();
    },
  });
};

// View all notifications
const viewAllNotifications = () => {
  router.get(route('notifications.index'));
  close();
};

// Get notification class based on type
const getNotificationClass = (type) => {
  switch (type) {
    case 'success':
      return 'bg-green-50 border-green-200';
    case 'warning':
      return 'bg-yellow-50 border-yellow-200';
    case 'error':
      return 'bg-red-50 border-red-200';
    case 'info':
    default:
      return 'bg-blue-50 border-blue-200';
  }
};

// Get notification icon based on type
const getNotificationIcon = (type) => {
  switch (type) {
    case 'success':
      return (
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
      );
    case 'warning':
      return (
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      );
    case 'error':
      return (
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
      );
    case 'info':
    default:
      return (
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      );
  }
};

onMounted(() => {
  document.addEventListener('keydown', closeOnEscape);
  document.addEventListener('click', closeOnClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape);
  document.removeEventListener('click', closeOnClickOutside);
});
</script>

<template>
  <div ref="dropdownRef" class="relative">
    <!-- Notification button with badge -->
    <button 
      @click="toggle"
      class="relative p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all duration-300 group"
      aria-label="Notifications"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      
      <!-- Notification badge -->
      <div 
        v-if="unreadNotificationsCount > 0" 
        class="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold animate-pulse"
      >
        {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
      </div>
      
      <!-- Tooltip for notifications -->
      <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
        Notifications
      </span>
    </button>

    <!-- Dropdown panel -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div 
        v-show="dropdownOpen"
        class="absolute z-50 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
        :class="[
          props.align === 'right' ? 'right-0' : 'left-0'
        ]"
      >
        <div class="py-2 px-4 bg-blue-50 rounded-t-md border-b border-blue-100 flex justify-between items-center">
          <h3 class="text-sm font-medium text-blue-800">Notifications</h3>
          <button 
            v-if="unreadNotificationsCount > 0"
            @click="markAllAsRead" 
            class="text-xs text-blue-600 hover:text-blue-800 hover:underline focus:outline-none"
          >
            Mark all as read
          </button>
        </div>
        
        <!-- Notification list -->
        <div class="max-h-80 overflow-y-auto">
          <div v-if="recentNotifications.length === 0" class="py-6 px-4 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <p class="text-gray-500 text-sm">No notifications</p>
          </div>
          
          <div v-else>
            <button
              v-for="notification in recentNotifications"
              :key="notification.id"
              @click="markAsRead(notification.id)"
              class="w-full px-4 py-3 border-b last:border-b-0 hover:bg-gray-50 text-left transition-colors duration-150 focus:outline-none focus:bg-gray-50"
              :class="[!notification.is_read ? 'bg-blue-50' : '', getNotificationClass(notification.type)]"
            >
              <div class="flex items-start">
                <div class="flex-shrink-0 mr-3">
                  <component :is="getNotificationIcon(notification.type)" />
                </div>
                <div class="flex-grow">
                  <div class="flex justify-between">
                    <p class="text-sm font-medium text-gray-900" :class="{'font-semibold': !notification.is_read}">{{ notification.title }}</p>
                    <span class="text-xs text-gray-500 ml-2">{{ notification.created_at }}</span>
                  </div>
                  <p class="text-xs text-gray-600 mt-1 line-clamp-2">{{ notification.message }}</p>
                </div>
                <div v-if="!notification.is_read" class="flex-shrink-0 ml-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                </div>
              </div>
            </button>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="p-2 border-t border-gray-100 bg-gray-50 rounded-b-md">
          <button 
            @click="viewAllNotifications" 
            class="w-full px-4 py-2 text-sm font-medium text-blue-700 hover:text-blue-800 hover:bg-blue-100 rounded-md transition-colors duration-150 focus:outline-none focus:bg-blue-100"
          >
            View all notifications
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
 
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Pulse animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>