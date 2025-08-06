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
    <div class="flex w-full mb-6 overflow-hidden rounded-md shadow-sm">
      <div class="w-1/4 h-1 bg-blue-500" style="animation-delay: 0.2s;"></div>
      <div class="w-1/4 h-1 bg-green-500" style="animation-delay: 0.4s;"></div>
      <div class="w-1/4 h-1 bg-yellow-500" style="animation-delay: 0.6s;"></div>
      <div class="w-1/4 h-1 bg-red-500" style="animation-delay: 0.8s;"></div>
    </div>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-xl font-medium text-gray-800 mb-8">
              {{ isEditing ? 'Edit Notification' : 'Create Notification' }}
            </h2>

            <form @submit.prevent="submit">
              <div class="mb-4">
                <InputLabel for="title" value="Title" />
                <TextInput
                  id="title"
                  v-model="form.title"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                />
                <InputError :message="form.errors.title" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="message" value="Message" />
                <TextArea
                  id="message"
                  v-model="form.message"
                  class="mt-1 block w-full"
                  :rows="4"
                  required
                />
                <InputError :message="form.errors.message" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="type" value="Notification Type" />
                <SelectInput
                  id="type"
                  v-model="form.type"
                  class="mt-1 block w-full"
                  :options="notificationTypes"
                  required
                />
                <InputError :message="form.errors.type" class="mt-2" />
              </div>

              <div class="mb-4 flex items-center">
                <Checkbox id="is_active" v-model:checked="form.is_active" />
                <InputLabel for="is_active" value="Active" class="ml-2" />
              </div>

              <!-- Target Audience -->
              <div class="mb-4">
                <InputLabel for="target_audience" value="Target Audience" />
                <SelectInput
                  id="target_audience"
                  v-model="form.target_audience"
                  class="mt-1 block w-full"
                  :options="audienceOptions"
                  required
                />
                <InputError :message="form.errors.target_audience" class="mt-2" />
                <InputError v-if="form.errors.user_ids" :message="form.errors.user_ids" class="mt-2" />
              </div>

              <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-3 mt-6">
                <button
                  type="button"
                  @click="router.visit(route('admin.notifications.index'))"
                  class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-xs text-gray-700 uppercase tracking-wider hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                >
                  Cancel
                </button>
                <PrimaryButton
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                >
                  {{ isEditing ? 'Update Notification' : 'Create Notification' }}
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- User Selection Modal -->
    <Modal :show="showUserModal" @close="closeUserSelectionModal">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium leading-6 text-gray-900">
            Select Users
          </h3>
          <button
            @click="closeUserSelectionModal"
            type="button"
            class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          >
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Select all option -->
        <div class="flex items-center justify-between mb-4 p-3 bg-gray-50 rounded-md">
          <span class="text-sm font-medium text-gray-700">Select all users</span>
          <div class="flex items-center space-x-3">
            <button
              v-if="tempUserSelection.length > 0"
              @click="clearAllUsers"
              type="button"
              class="text-xs text-red-600 hover:text-red-800"
            >
              Clear all
            </button>
            <input
              type="checkbox"
              :checked="isAllUsersSelected"
              @change="isAllUsersSelected ? clearAllUsers() : selectAllUsers()"
              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
          </div>
        </div>

        <!-- Users list -->
        <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md">
          <div v-if="props.users.length === 0" class="p-4 text-center text-gray-500">
            No users available
          </div>
          <label
            v-for="user in props.users"
            :key="user.id"
            class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
          >
            <input
              type="checkbox"
              :value="user.id"
              :checked="tempUserSelection.includes(user.id)"
              @change="toggleUser(user.id)"
              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3"
            />
            <span class="text-sm text-gray-900">{{ user.name }}</span>
          </label>
        </div>

        <!-- Selected count -->
        <div class="mt-3 text-sm text-gray-600">
          {{ tempUserSelection.length }} of {{ props.users.length }} users selected
        </div>

        <!-- Modal footer -->
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="closeUserSelectionModal"
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 ease-in-out"
          >
            Cancel
          </button>
          <button
            @click="applyUserSelection"
            type="button"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 ease-in-out"
          >
            Apply Selection
          </button>
        </div>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>