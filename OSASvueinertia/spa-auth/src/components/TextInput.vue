<template>
    <input
        v-bind="$attrs"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>

<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
});

defineEmits(['update:modelValue']);
</script>
