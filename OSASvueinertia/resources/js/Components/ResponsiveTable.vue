<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  mobileCardView: {
    type: Boolean,
    default: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  emptyMessage: {
    type: String,
    default: 'No data available'
  }
});

const emit = defineEmits(['row-click']);

const isMobile = ref(false);

const checkScreenSize = () => {
  isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize);
});

const visibleColumns = computed(() => {
  return props.columns.filter(col => {
    if (isMobile.value && col.hideOnMobile) return false;
    return true;
  });
});

const handleRowClick = (item, index) => {
  emit('row-click', item, index);
};
</script>

<template>
  <div class="overflow-hidden">
    <!-- Desktop Table View -->
    <div v-if="!isMobile || !mobileCardView" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th 
              v-for="column in visibleColumns" 
              :key="column.key"
              class="px-3 py-3 sm:px-6 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              :class="column.headerClass"
            >
              {{ column.label }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="loading">
            <td :colspan="visibleColumns.length" class="px-6 py-12 text-center">
              <div class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-500 dark:text-gray-400">Loading...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="data.length === 0">
            <td :colspan="visibleColumns.length" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
              {{ emptyMessage }}
            </td>
          </tr>
          <tr 
            v-else
            v-for="(item, index) in data" 
            :key="index"
            class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200 cursor-pointer"
            @click="handleRowClick(item, index)"
          >
            <td 
              v-for="column in visibleColumns" 
              :key="column.key"
              class="px-3 py-4 sm:px-6 sm:py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100"
              :class="column.cellClass"
            >
              <slot 
                :name="`cell-${column.key}`" 
                :item="item" 
                :value="item[column.key]"
                :index="index"
              >
                {{ item[column.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Card View -->
    <div v-else class="space-y-4">
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-500 dark:text-gray-400">Loading...</span>
      </div>
      
      <div v-else-if="data.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
        {{ emptyMessage }}
      </div>
      
      <div 
        v-else
        v-for="(item, index) in data" 
        :key="index"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 cursor-pointer hover:shadow-md transition-all duration-200"
        @click="handleRowClick(item, index)"
      >
        <slot name="mobile-card" :item="item" :index="index">
          <div class="space-y-2">
            <div 
              v-for="column in columns" 
              :key="column.key"
              v-show="!column.hideOnMobile"
              class="flex justify-between items-start"
            >
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mr-2">
                {{ column.label }}:
              </span>
              <span class="text-sm text-gray-900 dark:text-gray-100 text-right flex-1">
                <slot 
                  :name="`cell-${column.key}`" 
                  :item="item" 
                  :value="item[column.key]"
                  :index="index"
                >
                  {{ item[column.key] }}
                </slot>
              </span>
            </div>
          </div>
        </slot>
      </div>
    </div>
  </div>
</template>
