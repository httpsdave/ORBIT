<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import MultiSelect from '@/Components/MultiSelect.vue';

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

const showUserSelection = ref(form.target_audience === 'specific');

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
  showUserSelection.value = value === 'specific';
  if (value === 'all') {
    form.user_ids = [];
  }
});

const submit = () => {
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

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">
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

              <!-- Only show audience targeting options when creating a new notification -->
              <template v-if="!isEditing">
                <div class="mb-4">
                  <InputLabel for="target_audience" value="Target Audience" />
                  <SelectInput
                    id="target_audience"
                    v-model="form.target_audience"
                    class="mt-1 block w-full"
                    :options="audienceOptions"
                    required
                  />
                </div>

                <div v-if="showUserSelection" class="mb-4">
                  <InputLabel for="user_ids" value="Select Users" />
                  <MultiSelect
                    id="user_ids"
                    v-model="form.user_ids"
                    :options="users.map(user => ({ value: user.id, label: user.name }))"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.user_ids" class="mt-2" />
                </div>
              </template>

              <div class="flex items-center justify-end mt-6">
                <PrimaryButton
                  class="ml-4"
                  :disabled="form.processing"
                >
                  {{ isEditing ? 'Update' : 'Create' }}
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>