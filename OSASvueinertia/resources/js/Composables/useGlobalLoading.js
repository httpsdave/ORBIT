import { ref, readonly } from 'vue';

// Global loading state
const isGlobalLoading = ref(false);
const loadingTasks = ref(new Set());

export function useGlobalLoading() {
  
  const startLoading = (taskId = 'default') => {
    loadingTasks.value.add(taskId);
    isGlobalLoading.value = true;
    
    // Trigger a custom event for the LoadingBar component
    window.dispatchEvent(new CustomEvent('global-loading-start'));
  };

  const stopLoading = (taskId = 'default') => {
    loadingTasks.value.delete(taskId);
    
    if (loadingTasks.value.size === 0) {
      isGlobalLoading.value = false;
      // Trigger a custom event for the LoadingBar component
      window.dispatchEvent(new CustomEvent('global-loading-finish'));
    }
  };

  const isLoading = () => {
    return isGlobalLoading.value;
  };

  return {
    startLoading,
    stopLoading,
    isLoading,
    isGlobalLoading: readonly(isGlobalLoading)
  };
}
