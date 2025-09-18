<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  notification: Object,
  users: Array,
  isEditing: {
    type: Boolean,
    default: false
  }
});

const form = useForm({
  title: props.notification?.title || '',
  message: props.notification?.message || '',
  type: props.notification?.type || 'info',
  is_active: props.notification?.is_active ?? true,
  target_audience: 'all',
  user_ids: [],
});

const showUserModal = ref(false);
const tempUserSelection = ref([]);

// Define options for notification types
const notificationTypes = [
  { value: 'info', label: 'Info' },
  { value: 'success', label: 'Success' },
  { value: 'warning', label: 'Warning' },
  { value: 'error', label: 'Error' },
];

// Define options for target audience
const audienceOptions = [
  { value: 'all', label: 'All Users' },
  { value: 'specific', label: 'Specific Users' },
];

watch(() => form.target_audience, (value) => {
  if (value === 'all') {
    form.user_ids = [];
    showUserModal.value = false;
  } else if (value === 'specific') {
    // Open modal automatically when switching to specific
    tempUserSelection.value = [...form.user_ids];
    showUserModal.value = true;
  }
});

const closeUserSelectionModal = () => {
  showUserModal.value = false;
  // If no users selected, reset target audience to 'all'
  if (tempUserSelection.value.length === 0) {
    form.target_audience = 'all';
  }
};

const applyUserSelection = () => {
  form.user_ids = [...tempUserSelection.value];
  showUserModal.value = false;
};

const toggleUser = (userId) => {
  const index = tempUserSelection.value.indexOf(userId);
  if (index > -1) {
    tempUserSelection.value.splice(index, 1);
  } else {
    tempUserSelection.value.push(userId);
  }
};

const selectAllUsers = () => {
  tempUserSelection.value = props.users.map(user => user.id);
};

const clearAllUsers = () => {
  tempUserSelection.value = [];
};

const isAllUsersSelected = computed(() => {
  return props.users.length > 0 && tempUserSelection.value.length === props.users.length;
});

const submit = () => {
  // Client-side validation
  if (form.target_audience === 'specific' && form.user_ids.length === 0) {
    form.setError('user_ids', 'Please select at least one user when targeting specific users.');
    showUserModal.value = true;
    return;
  }

  if (props.isEditing) {
    form.put(route('admin.notifications.update', props.notification.id));
  } else {
    form.post(route('admin.notifications.store'));
  }
};
</script>

<template>
  <AuthenticatedLayout :is-admin="true">
    <Head :title="isEditing ? 'Edit Notification' : 'Create Notification'" />

    <!-- Colored banner -->
    <div class="flex w-full mb-4 sm:mb-6 overflow-hidden rounded-md shadow-sm">
      <div class="w-1/4 h-1 sm:h-1.5 bg-blue-500" style="animation-delay: 0.2s;"></div>
      <div class="w-1/4 h-1 sm:h-1.5 bg-green-500" style="animation-delay: 0.4s;"></div>
      <div class="w-1/4 h-1 sm:h-1.5 bg-yellow-500" style="animation-delay: 0.6s;"></div>
      <div class="w-1/4 h-1 sm:h-1.5 bg-red-500" style="animation-delay: 0.8s;"></div>
    </div>

    <div class="py-4 sm:py-6 lg:py-8 min-h-screen">
      <div class="max-w-4xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
          <div class="p-4 sm:p-6 lg:p-8">
            <!-- Header Section -->
            <div class="mb-6 sm:mb-8">
              <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-gray-100">
                {{ isEditing ? 'Edit Notification' : 'Create Notification' }}
              </h2>
              <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
                {{ isEditing ? 'Update the notification details below' : 'Create a new notification for your users' }}
              </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4 sm:space-y-6">
              <!-- Title Field -->
              <div class="space-y-2">
                <InputLabel for="title" value="Title" class="text-sm sm:text-base font-medium" />
                <TextInput
                  id="title"
                  v-model="form.title"
                  type="text"
                  class="mt-1 block w-full text-sm sm:text-base p-3 sm:p-3.5"
                  placeholder="Enter notification title"
                  required
                  autofocus
                />
                <InputError :message="form.errors.title" class="mt-2 text-xs sm:text-sm" />
              </div>

              <!-- Message Field -->
              <div class="space-y-2">
                <InputLabel for="message" value="Message" class="text-sm sm:text-base font-medium" />
                <TextArea
                  id="message"
                  v-model="form.message"
                  class="mt-1 block w-full text-sm sm:text-base p-3 sm:p-3.5 min-h-[100px] sm:min-h-[120px]"
                  :rows="5"
                  placeholder="Enter notification message"
                  required
                />
                <InputError :message="form.errors.message" class="mt-2 text-xs sm:text-sm" />
              </div>

              <!-- Type Field -->
              <div class="space-y-2">
                <InputLabel for="type" value="Notification Type" class="text-sm sm:text-base font-medium" />
                <SelectInput
                  id="type"
                  v-model="form.type"
                  class="mt-1 block w-full text-sm sm:text-base p-3 sm:p-3.5"
                  :options="notificationTypes"
                  required
                />
                <InputError :message="form.errors.type" class="mt-2 text-xs sm:text-sm" />
              </div>

              <!-- Active Checkbox -->
              <div class="flex items-center space-x-3 p-3 sm:p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <Checkbox id="is_active" v-model:checked="form.is_active" class="h-4 w-4 sm:h-5 sm:w-5" />
                <InputLabel for="is_active" value="Make this notification active immediately" class="text-sm sm:text-base font-medium cursor-pointer" />
              </div>

              <!-- Target Audience -->
              <div class="space-y-2">
                <InputLabel for="target_audience" value="Target Audience" class="text-sm sm:text-base font-medium" />
                <SelectInput
                  id="target_audience"
                  v-model="form.target_audience"
                  class="mt-1 block w-full text-sm sm:text-base p-3 sm:p-3.5"
                  :options="audienceOptions"
                  required
                />
                <InputError :message="form.errors.target_audience" class="mt-2 text-xs sm:text-sm" />
                <InputError v-if="form.errors.user_ids" :message="form.errors.user_ids" class="mt-2 text-xs sm:text-sm" />
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row pt-4 sm:pt-6 border-t border-gray-100 dark:border-gray-700 space-y-3 sm:space-y-0 sm:space-x-3 sm:justify-end">
                <button
                  type="button"
                  @click="router.visit(route('admin.notifications.index'))"
                  class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-blue-500 dark:border-blue-400 text-blue-700 dark:text-blue-300 text-sm font-medium rounded-xl shadow-md hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-800 dark:hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group"
                >
                  <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-blue-500 rounded-full group-hover:w-96 group-hover:h-96 opacity-5"></span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6.707 7.293a1 1 0 00-1.414 1.414L8.586 12l-3.293 3.293a1 1 0 001.414 1.414L10 13.414l3.293 3.293a1 1 0 001.414-1.414L11.414 12l3.293-3.293a1 1 0 00-1.414-1.414L10 10.586 6.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  Cancel
                </button>

                <button
                  type="submit"
                  :disabled="form.processing"
                  class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                  <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>
                    {{ form.processing ? (isEditing ? 'Updating...' : 'Creating...') : (isEditing ? 'Update Notification' : 'Create Notification') }}
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- User Selection Modal -->
    <Modal :show="showUserModal" @close="closeUserSelectionModal" max-width="lg">
      <div class="p-3 sm:p-4 lg:p-6 bg-white dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2 sm:pb-3 mb-3 sm:mb-4 lg:mb-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
            </div>
            <h3 class="ml-2 text-base sm:text-lg font-bold text-gray-900 dark:text-gray-100">
              Select Users
            </h3>
          </div>
          <button
            @click="closeUserSelectionModal"
            type="button"
            class="rounded-md p-1 bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
          >
            <span class="sr-only">Close</span>
            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Select all option -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4 p-3 sm:p-4 bg-gray-50 dark:bg-gray-700 rounded-lg space-y-2 sm:space-y-0">
          <span class="text-sm sm:text-base font-medium text-gray-700 dark:text-gray-300">Select all users</span>
          <div class="flex items-center justify-between sm:justify-end space-x-3">
            <button
              v-if="tempUserSelection.length > 0"
              @click="clearAllUsers"
              type="button"
              class="text-xs sm:text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium"
            >
              Clear all
            </button>
            <input
              type="checkbox"
              :checked="isAllUsersSelected"
              @change="isAllUsersSelected ? clearAllUsers() : selectAllUsers()"
              class="h-4 w-4 sm:h-5 sm:w-5 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700"
            />
          </div>
        </div>

        <!-- Users list -->
        <div class="max-h-48 sm:max-h-64 lg:max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg">
          <div v-if="props.users.length === 0" class="p-4 sm:p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400">No users available</p>
          </div>
          <label
            v-for="user in props.users"
            :key="user.id"
            class="flex items-center px-3 sm:px-4 py-2.5 sm:py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-b-0 transition duration-150 ease-in-out"
          >
            <input
              type="checkbox"
              :value="user.id"
              :checked="tempUserSelection.includes(user.id)"
              @change="toggleUser(user.id)"
              class="h-4 w-4 sm:h-5 sm:w-5 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 mr-3 sm:mr-4 dark:bg-gray-700"
            />
            <span class="text-sm sm:text-base text-gray-900 dark:text-gray-100 truncate">{{ user.name }}</span>
          </label>
        </div>

        <!-- Selected count -->
        <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
          <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
            <span class="text-sm sm:text-base font-medium text-blue-700 dark:text-blue-300">
              {{ tempUserSelection.length }} of {{ props.users.length }} users selected
            </span>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="flex flex-col sm:flex-row pt-4 sm:pt-6 border-t border-gray-100 dark:border-gray-700 space-y-3 sm:space-y-0 sm:space-x-3 sm:justify-end mt-4 sm:mt-6">
          <button
            @click="closeUserSelectionModal"
            type="button"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-blue-500 dark:border-blue-400 text-blue-700 dark:text-blue-300 text-sm font-medium rounded-xl shadow-md hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-800 dark:hover:text-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-blue-500 rounded-full group-hover:w-96 group-hover:h-96 opacity-5"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M6.707 7.293a1 1 0 00-1.414 1.414L8.586 12l-3.293 3.293a1 1 0 001.414 1.414L10 13.414l3.293 3.293a1 1 0 001.414-1.414L11.414 12l3.293-3.293a1 1 0 00-1.414-1.414L10 10.586 6.707 7.293z" clip-rule="evenodd" />
            </svg>
            Cancel
          </button>
          <button
            @click="applyUserSelection"
            type="button"
            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
          >
            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5 13l4 4L19 7" clip-rule="evenodd" />
            </svg>
            Apply Selection
          </button>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>