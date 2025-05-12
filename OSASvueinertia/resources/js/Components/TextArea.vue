<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
    </label>
    <textarea
      :id="id"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :rows="rows"
      :placeholder="placeholder"
      class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
    ></textarea>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: String,
  label: String,
  id: String,
  rows: {
    type: Number,
    default: 3,
  },
  placeholder: String,
  error: String,
});

defineEmits(['update:modelValue']);
</script>
