<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  padding: {
    type: String,
    default: 'responsive' // 'responsive', 'none', 'sm', 'md', 'lg'
  },
  background: {
    type: String,
    default: 'white' // 'white', 'gray', 'transparent'
  },
  shadow: {
    type: Boolean,
    default: true
  },
  border: {
    type: Boolean,
    default: true
  }
});

const cardClasses = computed(() => {
  const classes = ['rounded-lg', 'transition-all', 'duration-300'];
  
  // Background
  if (props.background === 'white') {
    classes.push('bg-white dark:bg-gray-800');
  } else if (props.background === 'gray') {
    classes.push('bg-gray-50 dark:bg-gray-700');
  }
  
  // Shadow
  if (props.shadow) {
    classes.push('shadow-sm hover:shadow-md');
  }
  
  // Border
  if (props.border) {
    classes.push('border border-gray-200 dark:border-gray-700');
  }
  
  return classes.join(' ');
});

const headerClasses = computed(() => {
  const classes = [];
  
  // Responsive padding
  if (props.padding === 'responsive') {
    classes.push('px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6');
  } else if (props.padding === 'sm') {
    classes.push('p-4');
  } else if (props.padding === 'md') {
    classes.push('p-6');
  } else if (props.padding === 'lg') {
    classes.push('p-8');
  }
  
  return classes.join(' ');
});

const contentClasses = computed(() => {
  const classes = [];
  
  // Responsive padding
  if (props.padding === 'responsive') {
    classes.push('px-4 pb-4 sm:px-6 sm:pb-5 lg:px-8 lg:pb-6');
  } else if (props.padding === 'sm') {
    classes.push('p-4 pt-0');
  } else if (props.padding === 'md') {
    classes.push('p-6 pt-0');
  } else if (props.padding === 'lg') {
    classes.push('p-8 pt-0');
  }
  
  return classes.join(' ');
});
</script>

<template>
  <div :class="cardClasses">
    <!-- Header -->
    <div v-if="title || subtitle || $slots.header" :class="headerClasses">
      <slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
          <div>
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">
              {{ title }}
            </h3>
            <p v-if="subtitle" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              {{ subtitle }}
            </p>
          </div>
          <div v-if="$slots.actions" class="flex-shrink-0">
            <slot name="actions" />
          </div>
        </div>
      </slot>
    </div>
    
    <!-- Content -->
    <div v-if="$slots.default" :class="contentClasses">
      <slot />
    </div>
    
    <!-- Footer -->
    <div v-if="$slots.footer" class="border-t border-gray-200 dark:border-gray-700" :class="headerClasses">
      <slot name="footer" />
    </div>
  </div>
</template>
</script>
