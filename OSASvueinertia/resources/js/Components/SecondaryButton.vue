<script setup>
const props = defineProps({
  type: {
    type: String,
    default: 'button'
  }
});
</script>

<template>
  <button
    :type="props.type"
    v-bind="$attrs"
    class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-200 text-sm font-medium text-gray-700 rounded-xl shadow-sm hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 active:bg-gray-100 transition-all duration-300 relative overflow-hidden group"
  >
    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-gray-500 rounded-full group-hover:w-96 group-hover:h-96 opacity-5"></span>
    <slot />
  </button>
</template>
