<script setup>
import InputError from '@/Components/InputError.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const isLoading = ref(false);
const formElement = ref(null);
// Set dark mode as default to match login
const isDarkMode = ref(true);
const activeSlide = ref(0);
const slideInterval = ref(null);

// Different slideshow images for variety
const slideshowImages = [
    '/images/LSPU3.jpg',
    '/images/LSPU5.jpg',
    '/images/LSPU1.jpg',
    '/images/LSPU6.jpg',
    '/images/LSPU2.jpg',
];

const submit = () => {
    if (form.processing) return;
    
    isLoading.value = true;
    form.post(route('password.email'), {
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const startSlideshow = () => {
    slideInterval.value = setInterval(() => {
        activeSlide.value = (activeSlide.value + 1) % slideshowImages.length;
    }, 8000); // Slightly faster than login
};

onMounted(() => {
    // Fade in animation on page load
    if (formElement.value) {
        formElement.value.classList.add('opacity-100');
    }
    
    // Start slideshow
    startSlideshow();
});

onBeforeUnmount(() => {
    // Clear slideshow interval when component is unmounted
    if (slideInterval.value) {
        clearInterval(slideInterval.value);
    }
});
</script>

<template>
    <Head title="Forgot Password | LSPU ORBIT" />
    
    <!-- Main container with slideshow background -->
    <div class="min-h-screen flex items-center justify-center px-4 py-8 relative overflow-hidden">
        <!-- Background slideshow -->
        <div class="absolute inset-0 z-0">
            <transition-group name="fade">
                <div 
                    v-for="(image, index) in slideshowImages" 
                    :key="index" 
                    v-show="activeSlide === index"
                    class="absolute inset-0 bg-cover bg-center transition-opacity duration-1000"
                    :style="{ backgroundImage: `url(${image})`, filter: isDarkMode ? 'brightness(0.6)' : 'brightness(1.2) contrast(1.1)' }"
                >
                </div>
            </transition-group>
            <!-- Overlay with different opacity for distinction -->
            <div class="absolute inset-0" :class="isDarkMode ? 'bg-gray-900 bg-opacity-70' : 'bg-white bg-opacity-50 backdrop-filter backdrop-blur-sm'"></div>
        </div>
        
        <!-- Content wrapper - Responsive centered card -->
        <div 
            ref="formElement" 
            class="w-full max-w-xs sm:max-w-sm md:max-w-md relative opacity-0 transition-all duration-700 z-20"
            :class="isDarkMode ? 'text-white' : 'text-gray-800'"
        >
            <!-- Main card -->
            <div 
                class="p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-3xl shadow-xl transition-all duration-300 border backdrop-filter backdrop-blur-md"
                :class="isDarkMode 
                    ? 'bg-gray-900 bg-opacity-75 border-gray-700' 
                    : 'bg-white bg-opacity-65 border-white border-opacity-50'"
            >
                <!-- Header section -->
                <div class="text-center mb-6 sm:mb-8">
                    <!-- Logo - Clean and prominent -->
                    <div class="mb-4 sm:mb-6">
                        <ApplicationLogo class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 mx-auto" />
                    </div>
                    
                    <!-- Title with gradient -->
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-green-400">LSPU</span>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-blue-400"> ORBIT</span>
                    </h1>
                    
                    <!-- Enhanced banner with different animation -->
                    <div class="flex w-full mb-3 sm:mb-4 overflow-hidden rounded-full shadow-inner">
                        <div class="w-1/4 h-1.5 sm:h-2 bg-blue-500 animate-pulse" style="animation-delay: 0.1s;"></div>
                        <div class="w-1/4 h-1.5 sm:h-2 bg-green-500 animate-pulse" style="animation-delay: 0.3s;"></div>
                        <div class="w-1/4 h-1.5 sm:h-2 bg-yellow-500 animate-pulse" style="animation-delay: 0.5s;"></div>
                        <div class="w-1/4 h-1.5 sm:h-2 bg-red-500 animate-pulse" style="animation-delay: 0.7s;"></div>
                    </div>
                    
                    <p class="text-xs sm:text-sm tracking-wider mb-4 sm:mb-6" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                        INTEGRITY • PROFESSIONALISM • INNOVATION
                    </p>
                </div>

                <!-- Password Reset Specific Content -->
                <div class="text-center mb-4 sm:mb-6">
                    <h2 class="text-lg sm:text-xl font-semibold mb-2 sm:mb-3" :class="isDarkMode ? 'text-white' : 'text-gray-800'">
                        Forgot Your Password?
                    </h2>
                    <p class="text-xs sm:text-sm leading-relaxed mb-4 sm:mb-6" :class="isDarkMode ? 'text-gray-300' : 'text-gray-600'">
                        Enter your email address and we'll send you a password reset link.
                    </p>
                </div>
                
                <!-- Status message -->
                <div v-if="status" class="mb-4 sm:mb-6 py-2.5 sm:py-3 px-3 sm:px-4 bg-green-500 bg-opacity-15 border border-green-500 border-opacity-30 text-green-400 text-xs sm:text-sm font-medium rounded-lg sm:rounded-xl text-center animate-fadeIn backdrop-blur-sm">
                    <div class="flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:w-4 sm:h-4">
                            <path d="M9 12l2 2 4-4"></path>
                            <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                            <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                        </svg>
                        {{ status }}
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-4 sm:space-y-6" novalidate>
                    <div>
                        <label for="email" class="block text-xs sm:text-sm font-medium mb-2" :class="isDarkMode ? 'text-gray-300' : 'text-gray-700'">
                            Email Address
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-focus-within:text-blue-500 transition-colors duration-300 sm:w-5 sm:h-5" :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <TextInput
                                id="email"
                                type="email"
                                class="pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 rounded-lg sm:rounded-xl w-full focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-40 transition-all duration-300 text-sm sm:text-base"
                                :class="isDarkMode 
                                    ? 'bg-gray-800 bg-opacity-60 border-gray-600 text-white placeholder-gray-400' 
                                    : 'bg-white bg-opacity-60 border-white border-opacity-60 text-gray-800 placeholder-gray-500'"
                                v-model="form.email"
                                placeholder="Enter your email address"
                                required
                                autofocus
                                autocomplete="username"
                                aria-label="Email address"
                            />
                        </div>
                        <InputError class="mt-2 text-red-400 text-xs sm:text-sm" :message="form.errors.email" />
                    </div>

                    <!-- Submit button -->
                    <button
                        type="submit"
                        class="w-full text-white font-semibold py-3 sm:py-4 px-4 sm:px-6 rounded-lg sm:rounded-xl transition-all duration-300 flex items-center justify-center relative overflow-hidden group shadow-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50 transform hover:scale-105 text-sm sm:text-base"
                        :class="[
                            isDarkMode 
                                ? 'bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-400 hover:to-orange-500' 
                                : 'bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-400 hover:to-orange-400',
                            { 'opacity-80 cursor-not-allowed transform-none': form.processing || isLoading }
                        ]"
                        :disabled="form.processing || isLoading"
                        aria-label="Send Reset Link"
                    >
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                        <span v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
                            <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span :class="{ 'opacity-0': isLoading }" class="flex items-center relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:w-5 sm:h-5">
                                <path d="M22 2L11 13"></path>
                                <polygon points="22,2 15,22 11,13 2,9"></polygon>
                            </svg>
                            Send Reset Link
                        </span>
                    </button>

                    <!-- Back to login link -->
                    <div class="text-center pt-2 sm:pt-4">
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center text-xs sm:text-sm hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded-md transition-all duration-300 px-2 sm:px-3 py-1 sm:py-2 hover:bg-white hover:bg-opacity-10"
                            :class="isDarkMode ? 'text-gray-400' : 'text-gray-600'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1 sm:mr-2 sm:w-4 sm:h-4">
                                <path d="M19 12H5"></path>
                                <polyline points="12 5 5 12 12 19"></polyline>
                            </svg>
                            Back to Sign In
                        </Link>
                    </div>
                </form>
                
                <!-- Footer -->
                <div class="mt-6 sm:mt-8 text-center border-t pt-4 sm:pt-6" :class="isDarkMode ? 'border-gray-700' : 'border-gray-200 border-opacity-50'">
                    <p class="text-xs" :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'">
                        © 2025 Laguna State Polytechnic University
                    </p>
                    <div class="mt-1 sm:mt-2 flex justify-center space-x-3 sm:space-x-4">
                        <a 
                            href="#" 
                            class="text-xs hover:text-gray-600 transition-colors duration-300" 
                            :class="isDarkMode ? 'text-gray-500 hover:text-gray-400' : 'text-gray-500'"
                        >
                            Privacy Policy
                        </a>
                        <span :class="isDarkMode ? 'text-gray-700' : 'text-gray-400'">•</span>
                        <a 
                            href="#" 
                            class="text-xs hover:text-gray-600 transition-colors duration-300" 
                            :class="isDarkMode ? 'text-gray-500 hover:text-gray-400' : 'text-gray-500'"
                        >
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Slideshow navigation dots - responsive positioning -->
        <div class="absolute bottom-4 sm:bottom-6 md:top-6 md:right-6 left-0 right-0 md:left-auto flex md:flex-col justify-center md:justify-start space-x-2 md:space-x-0 md:space-y-2 z-10">
            <button 
                v-for="(_, index) in slideshowImages" 
                :key="index"
                @click="activeSlide = index" 
                class="w-2 h-2 md:w-1.5 md:h-4 rounded-full transition-all duration-300"
                :class="[
                    activeSlide === index 
                        ? isDarkMode ? 'bg-white scale-110' : 'bg-yellow-500 scale-110' 
                        : isDarkMode ? 'bg-gray-600 hover:bg-gray-400' : 'bg-gray-300 hover:bg-gray-400'
                ]"
                :aria-label="`Go to slide ${index + 1}`"
            ></button>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
  animation: fadeIn 0.6s ease-out forwards;
}

/* Slideshow transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 1s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Improve focus visibility for accessibility */
a:focus, button:focus, input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* Enhanced hover effects */
button:hover:not(:disabled) {
  transform: translateY(-1px);
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
</style>