<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  type: { type: String, default: 'success' }, // 'success', 'update', 'delete', 'error'
  message: { type: [String, null], default: '' },
});
const emit = defineEmits(['close']);

const bannerStyle = computed(() => {
  switch (props.type) {
    case 'success':
      return 'from-green-500 to-emerald-500';
    case 'update':
      return 'from-yellow-400 to-amber-400';
    case 'delete':
    case 'error':
      return 'from-red-500 to-pink-500';
    default:
      return 'from-blue-500 to-indigo-500';
  }
});

const icon = computed(() => {
  switch (props.type) {
    case 'success':
      return `<svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 mr-3' viewBox='0 0 20 20' fill='currentColor'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'/></svg>`;
    case 'update':
      return `<svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 mr-3' viewBox='0 0 20 20' fill='currentColor'><path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v3a1 1 0 11-2 0V9z' clip-rule='evenodd'/></svg>`;
    case 'delete':
    case 'error':
      return `<svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 mr-3' viewBox='0 0 20 20' fill='currentColor'><path fill-rule='evenodd' d='M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z' clip-rule='evenodd'/></svg>`;
    default:
      return '';
  }
});
</script>

<template>
  <transition name="fade">
    <div v-if="show" :class="`bg-gradient-to-r ${bannerStyle} text-white py-4 px-6 rounded-lg shadow-md flex items-center justify-between mb-6`">
      <div class="flex items-center">
        <span v-html="icon"></span>
        <span>{{ message }}</span>
      </div>
      <button @click="$emit('close')" class="text-white hover:text-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style> 