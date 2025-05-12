<template>
  <div>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
    </label>
    <select
      :id="id"
      multiple
      :value="modelValue"
      @change="updateValue"
      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
    >
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  label: String,
  id: String,
  options: {
    type: Array,
    required: true, // [{ value: 1, label: 'Student A' }, ...]
  },
  error: String,
});

const emit = defineEmits(['update:modelValue']);

function updateValue(event) {
  const selected = Array.from(event.target.selectedOptions).map(option => option.value);
  emit('update:modelValue', selected);
}
</script>
