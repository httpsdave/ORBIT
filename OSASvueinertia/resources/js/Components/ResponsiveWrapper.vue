<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  mobileBreakpoint: {
    type: Number,
    default: 768
  }
});

const emit = defineEmits(['mobile-changed']);

const isMobile = ref(false);

const checkScreenSize = () => {
  const newIsMobile = window.innerWidth < props.mobileBreakpoint;
  if (newIsMobile !== isMobile.value) {
    isMobile.value = newIsMobile;
    emit('mobile-changed', isMobile.value);
  }
};

onMounted(() => {
  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize);
});
</script>

<template>
  <div>
    <!-- Mobile Layout -->
    <div v-if="isMobile" class="md:hidden">
      <slot name="mobile" :is-mobile="isMobile" />
    </div>
    
    <!-- Desktop Layout -->
    <div v-else class="hidden md:block">
      <slot name="desktop" :is-mobile="isMobile" />
    </div>
    
    <!-- Default slot for responsive content -->
    <slot :is-mobile="isMobile" />
  </div>
</template>
</script>
