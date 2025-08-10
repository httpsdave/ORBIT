<script setup>
import { computed } from 'vue';

const props = defineProps({
  cols: {
    type: Object,
    default: () => ({
      xs: 1,
      sm: 1, 
      md: 2,
      lg: 3,
      xl: 4
    })
  },
  gap: {
    type: String,
    default: '4' // Tailwind spacing scale
  },
  items: {
    type: String,
    default: 'stretch' // 'start', 'center', 'end', 'stretch'
  }
});

const gridClasses = computed(() => {
  const classes = ['grid', `gap-${props.gap}`, `items-${props.items}`];
  
  // Add responsive column classes
  if (props.cols.xs) classes.push(`grid-cols-${props.cols.xs}`);
  if (props.cols.sm) classes.push(`sm:grid-cols-${props.cols.sm}`);
  if (props.cols.md) classes.push(`md:grid-cols-${props.cols.md}`);
  if (props.cols.lg) classes.push(`lg:grid-cols-${props.cols.lg}`);
  if (props.cols.xl) classes.push(`xl:grid-cols-${props.cols.xl}`);
  
  return classes.join(' ');
});
</script>

<template>
  <div :class="gridClasses">
    <slot />
  </div>
</template>
</script>
