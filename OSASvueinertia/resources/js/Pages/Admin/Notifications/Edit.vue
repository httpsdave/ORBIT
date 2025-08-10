<template>
  <AuthenticatedLayout :is-admin="true">
    <Head title="Edit Notification" />
    
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
              <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-gray-100">Edit Notification</h2>
              <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">Update the notification details below</p>
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

              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row pt-4 sm:pt-6 border-t border-gray-100 dark:border-gray-700 space-y-3 sm:space-y-0 sm:space-x-3 sm:justify-end">
                <button
                  type="button"
                  @click="cancel"
                  class="w-full sm:w-auto inline-flex justify-center items-center px-4 sm:px-6 py-2.5 sm:py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-sm sm:text-base text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Cancel
                </button>
                <PrimaryButton
                  :disabled="form.processing"
                  class="w-full sm:w-auto inline-flex justify-center items-center px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 hover:bg-blue-700 border border-transparent rounded-lg font-medium text-sm sm:text-base text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <svg v-else class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Updating...' : 'Update Notification' }}
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