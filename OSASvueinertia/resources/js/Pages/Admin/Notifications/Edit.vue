<template>
  <AuthenticatedLayout :is-admin="true">
    <Head title="Edit Notification" />
    
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
            <h2 class="text-xl font-medium text-gray-800 mb-8">Edit Notification</h2>

            <form @submit.prevent="submit">
              <div class="mb-4">
                <InputLabel for="title" value="Title" />
                <TextInput
                  id="title"
                  v-model="form.title"
                  type="text"
                  class="mt-1 block w-full"
                  required
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

              <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-3 mt-6">
                <button
                  type="button"
                  @click="cancel"
                  class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-xs text-gray-700 uppercase tracking-wider hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                >
                  Cancel
                </button>
                <PrimaryButton
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                >
                  Update Notification
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  notification: Object,
});

const form = useForm({
  title: props.notification.title,
  message: props.notification.message,
  type: props.notification.type || 'info',
});

// Define options for notification types
const notificationTypes = [
  { value: 'info', label: 'Info' },
  { value: 'success', label: 'Success' },
  { value: 'warning', label: 'Warning' },
  { value: 'error', label: 'Error' },
];

const submit = () => {
  form.put(route('admin.notifications.update', props.notification.id));
};

const cancel = () => {
  router.visit(route('admin.notifications.index'));
};
</script>