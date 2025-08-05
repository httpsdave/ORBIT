<template>
  <div 
    v-if="show" 
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="cancel"
  >
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-100">
      <!-- Icon and Header -->
      <div class="flex items-center justify-center mb-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
             :class="iconClass">
          <svg class="w-6 h-6" :class="iconColorClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="type === 'warning'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
            <path v-else-if="type === 'danger'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>

      <div class="text-center mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ title }}</h3>
        <p class="text-sm text-gray-600">{{ message }}</p>
      </div>

      <!-- Colored banner -->
      <div class="flex w-full mb-4 overflow-hidden rounded-md">
        <div class="w-1/4 h-1 bg-blue-500"></div>
        <div class="w-1/4 h-1 bg-green-500"></div>
        <div class="w-1/4 h-1 bg-yellow-500"></div>
        <div class="w-1/4 h-1 bg-red-500"></div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3">
        <button 
          @click="cancel"
          class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
        >
          {{ cancelText }}
        </button>
        <button 
          @click="confirm"
          :class="[
            'px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200',
            confirmButtonClass
          ]"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'ConfirmationModal',
  
  props: {
    show: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      required: true
    },
    message: {
      type: String,
      required: true
    },
    type: {
      type: String,
      default: 'warning', // warning, danger, success
      validator: (value) => ['warning', 'danger', 'success'].includes(value)
    },
    confirmText: {
      type: String,
      default: 'Confirm'
    },
    cancelText: {
      type: String,
      default: 'Cancel'
    }
  },

  emits: ['confirm', 'cancel'],

  setup(props, { emit }) {
    const iconClass = computed(() => {
      switch (props.type) {
        case 'warning':
          return 'bg-yellow-100';
        case 'danger':
          return 'bg-red-100';
        default:
          return 'bg-green-100';
      }
    });

    const iconColorClass = computed(() => {
      switch (props.type) {
        case 'warning':
          return 'text-yellow-600';
        case 'danger':
          return 'text-red-600';
        default:
          return 'text-green-600';
      }
    });

    const confirmButtonClass = computed(() => {
      switch (props.type) {
        case 'warning':
          return 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-yellow-500';
        case 'danger':
          return 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500';
        default:
          return 'bg-green-500 text-white hover:bg-green-600 focus:ring-green-500';
      }
    });

    const confirm = () => {
      emit('confirm');
    };

    const cancel = () => {
      emit('cancel');
    };

    return {
      iconClass,
      iconColorClass,
      confirmButtonClass,
      confirm,
      cancel
    };
  }
};
</script>

<style scoped>
/* Animation for modal entrance */
@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.bg-white {
  animation: fadeInScale 0.3s ease-out;
}
</style>