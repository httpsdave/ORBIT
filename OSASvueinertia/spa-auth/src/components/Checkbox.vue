<template>
    <input
        type="checkbox"
        class="h-4 w-4"
        :checked="checked"
        @change="$emit('update:checked', $event.target.checked)"
    />
</template>

<script setup>
defineProps({
    checked: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['update:checked']);
</script>
