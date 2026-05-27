<template>
    <p v-if="message" class="text-sm text-red-400">
        {{ message }}
    </p>
</template>

<script setup>
defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>
