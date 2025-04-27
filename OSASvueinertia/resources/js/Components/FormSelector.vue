<script setup>
import { ref } from 'vue';

const props = defineProps({
  formOptions: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['form-selected']);

const selectedForm = ref('');

const selectForm = () => {
  if (selectedForm.value) {
    emit('form-selected', selectedForm.value);
  }
};
</script>

<template>
  <div class="p-6">
    <h1 class="text-xl font-bold text-center">Select a Form to Fill</h1>
    <div class="mt-4">
      <label class="block font-bold">Choose Form</label>
      <select v-model="selectedForm" @change="selectForm" class="border p-2 w-full">
        <option value="">-- Select Form --</option>
        <option v-for="option in formOptions" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>
    </div>
  </div>
</template>