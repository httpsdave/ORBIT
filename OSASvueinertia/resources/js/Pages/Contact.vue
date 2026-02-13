<template>
  <AuthenticatedLayout :user="$page.props.auth.user">
    <Head title="Contact Us" />

    <div class="py-8 min-h-screen" :class="isDarkMode ? 'bg-gray-900' : 'bg-gray-50'">
      <!-- Colored banner -->
      <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
        <div class="w-1/4 h-1.5 bg-blue-500"></div>
        <div class="w-1/4 h-1.5 bg-green-500"></div>
        <div class="w-1/4 h-1.5 bg-yellow-500"></div>
        <div class="w-1/4 h-1.5 bg-red-500"></div>
      </div>

      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-10">
          <div class="inline-flex items-center justify-center p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h1 class="text-3xl sm:text-4xl font-bold mb-2" :class="isDarkMode ? 'text-white' : 'text-gray-900'">
            Contact Us
          </h1>
          <p class="text-base sm:text-lg" :class="isDarkMode ? 'text-gray-400' : 'text-gray-600'">
            Have feedback, suggestions, or experiencing issues? Let us know.
          </p>
        </div>

        <!-- Success Message -->
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <div v-if="showSuccessMessage" class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-green-700 dark:text-green-300">{{ $page.props.flash.success }}</span>
              </div>
              <button @click="showSuccessMessage = false" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </Transition>

        <!-- Error Message -->
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <div v-if="showErrorMessage" class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-red-700 dark:text-red-300">{{ $page.props.flash.error }}</span>
              </div>
              <button @click="showErrorMessage = false" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </Transition>

        <!-- Contact Form -->
        <form @submit.prevent="submitForm" class="rounded-xl shadow-sm overflow-hidden" :class="isDarkMode ? 'bg-gray-800 border border-gray-700' : 'bg-white border border-gray-200'">
          <div class="p-6 sm:p-8 space-y-6">
            <!-- Category -->
            <div>
              <label class="block text-sm font-medium mb-2" :class="isDarkMode ? 'text-gray-300' : 'text-gray-700'">
                Category <span class="text-red-500">*</span>
              </label>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="cat in categories"
                  :key="cat.value"
                  type="button"
                  @click="form.category = cat.value"
                  class="px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-200 border"
                  :class="form.category === cat.value
                    ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                    : isDarkMode
                      ? 'bg-gray-700 text-gray-300 border-gray-600 hover:bg-gray-600'
                      : 'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100'
                  "
                >
                  {{ cat.label }}
                </button>
              </div>
              <p v-if="form.errors.category" class="mt-1 text-sm text-red-500">{{ form.errors.category }}</p>
            </div>

            <!-- Subject -->
            <div>
              <label for="subject" class="block text-sm font-medium mb-2" :class="isDarkMode ? 'text-gray-300' : 'text-gray-700'">
                Subject <span class="text-red-500">*</span>
              </label>
              <input
                id="subject"
                v-model="form.subject"
                type="text"
                placeholder="Brief description of your concern"
                maxlength="255"
                class="w-full px-4 py-2.5 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                :class="isDarkMode 
                  ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' 
                  : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'"
              />
              <p v-if="form.errors.subject" class="mt-1 text-sm text-red-500">{{ form.errors.subject }}</p>
            </div>

            <!-- Message -->
            <div>
              <label for="message" class="block text-sm font-medium mb-2" :class="isDarkMode ? 'text-gray-300' : 'text-gray-700'">
                Message <span class="text-red-500">*</span>
              </label>
              <textarea
                id="message"
                v-model="form.message"
                rows="6"
                placeholder="Describe your feedback, suggestion, or issue in detail..."
                maxlength="5000"
                class="w-full px-4 py-2.5 rounded-lg border focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 resize-none"
                :class="isDarkMode 
                  ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' 
                  : 'bg-white border-gray-300 text-gray-900 placeholder-gray-500'"
              />
              <div class="flex justify-between mt-1">
                <p v-if="form.errors.message" class="text-sm text-red-500">{{ form.errors.message }}</p>
                <span class="text-xs ml-auto" :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'">
                  {{ form.message.length }} / 5000
                </span>
              </div>
            </div>

            <!-- Sender Info (read-only) -->
            <div class="p-3 rounded-lg" :class="isDarkMode ? 'bg-gray-700/50' : 'bg-gray-50'">
              <p class="text-xs mb-1" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                Sending as
              </p>
              <p class="text-sm font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-700'">
                {{ $page.props.auth.user.name || $page.props.auth.user.email }} 
                <span class="font-normal" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                  ({{ $page.props.auth.user.email }})
                </span>
              </p>
            </div>
          </div>

          <!-- Submit -->
          <div class="px-6 sm:px-8 py-4 border-t" :class="isDarkMode ? 'bg-gray-800/50 border-gray-700' : 'bg-gray-50 border-gray-200'">
            <div class="flex items-center justify-between">
              <p class="text-xs" :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'">
                Your message will be sent to the ORBIT team.
              </p>
              <button
                type="submit"
                :disabled="form.processing || !form.subject || !form.message || !form.category"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium text-sm text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                :class="[
                  form.processing || !form.subject || !form.message || !form.category
                    ? 'bg-blue-400 cursor-not-allowed opacity-60'
                    : 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-sm hover:shadow',
                  isDarkMode ? 'focus:ring-offset-gray-800' : 'focus:ring-offset-white'
                ]"
              >
                <svg v-if="form.processing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                {{ form.processing ? 'Sending...' : 'Send Message' }}
              </button>
            </div>
          </div>
        </form>

        <!-- Direct Email Fallback -->
        <div class="mt-6 text-center">
          <p class="text-sm" :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'">
            You can also email us directly at 
            <a href="mailto:lspuorbit@gmail.com" class="underline font-medium" :class="isDarkMode ? 'text-blue-400 hover:text-blue-300' : 'text-blue-600 hover:text-blue-700'">
              lspuorbit@gmail.com
            </a>
          </p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useTheme } from '@/Composables/useTheme';
import { ref, computed, watch } from 'vue';

const { isDark: isDarkMode } = useTheme();
const page = usePage();

const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);

// Watch for flash messages
watch(() => page.props.flash?.success, (newVal) => {
  if (newVal) showSuccessMessage.value = true;
}, { immediate: true });

watch(() => page.props.flash?.error, (newVal) => {
  if (newVal) showErrorMessage.value = true;
}, { immediate: true });

const categories = [
  { value: 'Bug Report', label: 'Bug Report' },
  { value: 'Suggestion', label: 'Suggestion' },
  { value: 'Difficulty', label: 'Difficulty' },
  { value: 'General Inquiry', label: 'General Inquiry' },
];

const form = useForm({
  category: '',
  subject: '',
  message: '',
});

const submitForm = () => {
  showSuccessMessage.value = false;
  showErrorMessage.value = false;
  
  form.post(route('contact.send'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    },
  });
};
</script>
