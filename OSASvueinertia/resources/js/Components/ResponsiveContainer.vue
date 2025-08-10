<script setup>
import { computed } from 'vue';

const props = defineProps({
  maxWidth: {
    type: String,
    default: '7xl' // Default to max-w-7xl
  },
  padding: {
    type: String,
    default: 'responsive' // 'responsive', 'none', 'sm', 'md', 'lg'
  }
});

const containerClasses = computed(() => {
  const classes = [`max-w-${props.maxWidth}`, 'mx-auto', 'w-full'];
  
  if (props.padding === 'responsive') {
    classes.push('px-4 sm:px-6 lg:px-8');
  } else if (props.padding === 'sm') {
    classes.push('px-4');
  } else if (props.padding === 'md') {
    classes.push('px-6');
  } else if (props.padding === 'lg') {
    classes.push('px-8');
  }
  
  return classes.join(' ');
});
</script>

<template>
  <div :class="containerClasses">
    <slot />
  </div>
</template>
</script>
