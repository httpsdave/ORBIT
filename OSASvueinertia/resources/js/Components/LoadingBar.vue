<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const isLoading = ref(false);
const progress = ref(0);
const animationFrame = ref(null);

// Simulate realistic loading progress
const simulateProgress = () => {
  if (progress.value < 90) {
    // Fast initial progress
    if (progress.value < 30) {
      progress.value += Math.random() * 15 + 5;
    }
    // Slower middle progress
    else if (progress.value < 70) {
      progress.value += Math.random() * 8 + 2;
    }
    // Very slow final progress
    else {
      progress.value += Math.random() * 3 + 0.5;
    }
    
    animationFrame.value = requestAnimationFrame(simulateProgress);
  }
};

const startLoading = () => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  progress.value = 0;
  
  // Start progress simulation
  animationFrame.value = requestAnimationFrame(simulateProgress);
};

const finishLoading = () => {
  if (animationFrame.value) {
    cancelAnimationFrame(animationFrame.value);
  }
  
  // Complete the progress bar
  progress.value = 100;
  
  // Hide after a short delay
  setTimeout(() => {
    isLoading.value = false;
    progress.value = 0;
  }, 200);
};

const cancelLoading = () => {
  if (animationFrame.value) {
    cancelAnimationFrame(animationFrame.value);
  }
  isLoading.value = false;
  progress.value = 0;
};

// Listen to Inertia events and custom events
onMounted(() => {
  // Inertia navigation events
  router.on('start', startLoading);
  router.on('finish', finishLoading);
  router.on('error', cancelLoading);
  router.on('cancel', cancelLoading);
  
  // Custom global loading events
  window.addEventListener('global-loading-start', startLoading);
  window.addEventListener('global-loading-finish', finishLoading);
});

onUnmounted(() => {
  // Remove Inertia event listeners
  router.off('start', startLoading);
  router.off('finish', finishLoading);
  router.off('error', cancelLoading);
  router.off('cancel', cancelLoading);
  
  // Remove custom event listeners
  window.removeEventListener('global-loading-start', startLoading);
  window.removeEventListener('global-loading-finish', finishLoading);
  
  if (animationFrame.value) {
    cancelAnimationFrame(animationFrame.value);
  }
});

// Computed styles for the progress bar
const progressStyle = computed(() => ({
  transform: `translateX(${progress.value - 100}%)`,
  transition: progress.value === 100 ? 'transform 0.2s ease-out' : 'none'
}));
</script>

<template>
  <div 
    v-show="isLoading" 
    class="fixed top-0 left-0 right-0 z-[9999] h-1"
  >
    <!-- Background track -->
    <div class="w-full h-full bg-gray-200 dark:bg-gray-700 opacity-50"></div>
    
    <!-- Animated gradient progress bar -->
    <div 
      class="absolute top-0 left-0 h-full w-full bg-gradient-to-r from-blue-500 via-green-500 to-blue-500 shadow-lg"
      :style="progressStyle"
    >
      <!-- Shimmer effect -->
      <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-30 animate-shimmer"></div>
    </div>
    
    <!-- Glow effect -->
    <div 
      class="absolute top-0 left-0 h-2 w-full bg-gradient-to-r from-blue-400 via-green-400 to-blue-400 opacity-60 blur-sm"
      :style="progressStyle"
    ></div>
  </div>
</template>

<style scoped>
@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.animate-shimmer {
  animation: shimmer 1.5s infinite;
}
</style>
