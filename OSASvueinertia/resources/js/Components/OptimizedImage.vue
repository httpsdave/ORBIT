<template>
    <picture>
        <!-- WebP version for modern browsers -->
        <source 
            v-if="webpSrc"
            :srcset="webpSrc" 
            type="image/webp"
        />
        
        <!-- Responsive JPEG versions -->
        <source 
            v-if="srcset"
            :srcset="srcset"
            :sizes="sizes"
            type="image/jpeg"
        />
        
        <!-- Fallback image -->
        <img 
            :src="src"
            :alt="alt"
            :class="imgClass"
            :loading="loading"
            :fetchpriority="fetchpriority"
            :width="width"
            :height="height"
            :decoding="decoding"
            @load="onImageLoad"
            @error="onImageError"
        />
    </picture>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    src: {
        type: String,
        required: true,
    },
    webpSrc: {
        type: String,
        default: '',
    },
    srcset: {
        type: String,
        default: '',
    },
    sizes: {
        type: String,
        default: '100vw',
    },
    alt: {
        type: String,
        required: true,
    },
    imgClass: {
        type: String,
        default: '',
    },
    loading: {
        type: String,
        default: 'lazy',
        validator: (value) => ['lazy', 'eager'].includes(value),
    },
    fetchpriority: {
        type: String,
        default: 'auto',
        validator: (value) => ['high', 'low', 'auto'].includes(value),
    },
    width: {
        type: [String, Number],
        default: undefined,
    },
    height: {
        type: [String, Number],
        default: undefined,
    },
    decoding: {
        type: String,
        default: 'async',
        validator: (value) => ['sync', 'async', 'auto'].includes(value),
    },
});

const emit = defineEmits(['load', 'error']);

const isLoaded = ref(false);
const hasError = ref(false);

const onImageLoad = () => {
    isLoaded.value = true;
    emit('load');
};

const onImageError = () => {
    hasError.value = true;
    emit('error');
};
</script>

<style scoped>
/* Optional: Add fade-in animation when image loads */
img {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
</style>
