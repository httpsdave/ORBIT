<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const isLoading = ref(false);
const formElement = ref(null);
// Set dark mode as default
const isDarkMode = ref(true);
const activeSlide = ref(0);
const slideInterval = ref(null);
const gradientIndex = ref(0);
const gradientInterval = ref(null);

// Slideshow images matching login page
const slideshowImages = [
    '/images/LSPU1.jpg',
    '/images/LSPU2.jpg',
    '/images/LSPU3.jpg',
    '/images/LSPU6.jpg',
    '/images/LSPU5.jpg',
    '/images/LSPU7.jpg',
];

const submit = () => {
    if (form.processing) return;
    
    isLoading.value = true;
    form.post(route('password.store'), {
        onFinish: () => {
            isLoading.value = false;
            form.reset('password', 'password_confirmation');
        },
    });
};

const startSlideshow = () => {
    slideInterval.value = setInterval(() => {
        activeSlide.value = (activeSlide.value + 1) % slideshowImages.length;
    }, 10000);
};

const startGradientAnimation = () => {
    gradientInterval.value = setInterval(() => {
        gradientIndex.value = (gradientIndex.value + 1) % 2;
    }, 30000); // 30 seconds
};

onMounted(() => {
    // Fade in animation on page load
    if (formElement.value) {
        formElement.value.classList.add('opacity-100');
    }
    
    // Start slideshow
    startSlideshow();
    
    // Start gradient animation
    startGradientAnimation();
});

onBeforeUnmount(() => {
    // Clear slideshow interval when component is unmounted
    if (slideInterval.value) {
        clearInterval(slideInterval.value);
    }
    
    // Clear gradient interval when component is unmounted
    if (gradientInterval.value) {
        clearInterval(gradientInterval.value);
    }
});
</script>

<template>
    <Head title="Reset Password | LSPU ORBIT" />
    
    <!-- Full screen container with split layout -->
    <div class="min-h-screen flex relative overflow-hidden">
        <!-- Left side - Narrow gradient panel -->
        <div 
            class="w-20 sm:w-24 md:w-32 lg:w-40 xl:w-48 flex-shrink-0 relative overflow-hidden transition-all duration-1000 ease-in-out"
            :class="[
                gradientIndex === 0 
                    ? 'bg-gradient-to-b from-orange-500 via-yellow-500 to-red-500' 
                    : 'bg-gradient-to-b from-purple-500 via-pink-500 to-orange-500'
            ]"
        >
            <!-- Animated background pattern -->
            <div class="absolute inset-0 overflow-hidden opacity-20">
                <div class="absolute top-1/6 left-1/2 w-8 h-8 rounded-full bg-white transform -translate-x-1/2 animate-pulse"></div>
                <div class="absolute top-1/3 left-1/2 w-6 h-6 rounded-full bg-white transform -translate-x-1/2 animate-pulse" style="animation-delay: 1s;"></div>
                <div class="absolute top-1/2 left-1/2 w-10 h-10 rounded-full bg-white transform -translate-x-1/2 animate-pulse" style="animation-delay: 2s;"></div>
                <div class="absolute top-2/3 left-1/2 w-7 h-7 rounded-full bg-white transform -translate-x-1/2 animate-pulse" style="animation-delay: 3s;"></div>
                <div class="absolute top-5/6 left-1/2 w-5 h-5 rounded-full bg-white transform -translate-x-1/2 animate-pulse" style="animation-delay: 4s;"></div>
            </div>
            
            <!-- Vertical logo and text -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white p-2 sm:p-3">
                <div class="mb-4 sm:mb-6 lg:mb-8 xl:mb-12">
                    <img src="/images/lspu_logo_better.png" alt="LSPU Logo" class="w-10 h-10 sm:w-12 sm:h-12 md:w-16 md:h-16 lg:w-20 lg:h-20 xl:w-24 xl:h-24 object-cover filter drop-shadow-lg">
                </div>
                
                <!-- Vertical text -->
                <div class="writing-mode-vertical text-center">
                    <div class="text-sm sm:text-base md:text-lg lg:text-xl font-bold mb-2 sm:mb-3 tracking-widest transform rotate-180" style="writing-mode: vertical-rl;">
                        RESET
                    </div>
                    <div class="w-0.5 h-6 sm:h-8 lg:h-10 bg-white opacity-70 mx-auto mb-2 sm:mb-3"></div>
                    <div class="text-xs sm:text-sm md:text-base lg:text-lg font-medium tracking-widest transform rotate-180" style="writing-mode: vertical-rl;">
                        PASSWORD
                    </div>
                </div>
            </div>
            
            <!-- Bottom social link -->
            <div class="absolute bottom-3 sm:bottom-4 left-0 right-0 flex justify-center">
                <a href="https://www.facebook.com/SPCC.OSAS" target="_blank" rel="noopener noreferrer" class="bg-white bg-opacity-20 backdrop-blur-sm hover:bg-opacity-40 p-1.5 sm:p-2 rounded-full transition-all duration-300 hover:scale-110" aria-label="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                    </svg>
                </a>
            </div>
        </div>
        
        <!-- Background slideshow for right side -->
        <div class="absolute inset-0 left-20 sm:left-24 md:left-32 lg:left-40 xl:left-48 z-0">
            <transition-group name="fade">
                <div 
                    v-for="(image, index) in slideshowImages" 
                    :key="index" 
                    v-show="activeSlide === index"
                    class="absolute inset-0 bg-cover bg-center transition-opacity duration-1000"
                    :style="{ backgroundImage: `url(${image})`, filter: 'brightness(0.3) contrast(1.2)' }"
                >
                </div>
            </transition-group>
            <!-- Subtle overlay -->
            <div class="absolute inset-0 bg-gray-900 bg-opacity-40"></div>
        </div>
        
        <!-- Right side - Main content area -->
        <div class="flex-1 relative z-20 flex items-center justify-center sm:justify-start px-4 sm:px-6 md:px-8 lg:px-12 xl:px-16">
            <!-- Content container -->
            <div 
                ref="formElement"
                class="w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg opacity-0 transition-all duration-700 sm:mr-auto text-white"
            >
                <!-- Header section -->
                <div class="mb-6 sm:mb-8 md:mb-10 lg:mb-12 text-center sm:text-left">
                    <!-- Main heading -->
                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-3 text-white">
                        Reset Your
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-400">
                            Password
                        </span>
                    </h1>
                    
                    <!-- Colored accent line -->
                    <div class="flex mb-4 w-24 sm:w-32 mx-auto sm:mx-0">
                        <div class="w-1/4 h-1 bg-orange-500"></div>
                        <div class="w-1/4 h-1 bg-yellow-500"></div>
                        <div class="w-1/4 h-1 bg-red-500"></div>
                        <div class="w-1/4 h-1 bg-purple-500"></div>
                    </div>
                    
                    <!-- Subtitle -->
                    <p class="text-sm sm:text-base md:text-lg leading-relaxed text-gray-300">
                        Enter your new password below to complete the password reset process.
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-4 sm:space-y-6" novalidate>
                    <!-- Email field (read-only) -->
                    <div>
                        <label for="email" class="block text-xs sm:text-sm font-medium mb-2 sm:mb-3 text-gray-300">
                            Email Address
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-focus-within:text-orange-400 transition-colors duration-300 text-gray-500">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                            <TextInput
                                id="email"
                                type="email"
                                class="pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 text-sm sm:text-base rounded-lg w-full border-0 focus:border-orange-400 focus:ring-2 focus:ring-orange-400 focus:ring-opacity-50 transition-all duration-300 bg-white bg-opacity-10 backdrop-blur-sm text-white placeholder-gray-300"
                                v-model="form.email"
                                readonly
                                autocomplete="username"
                                aria-label="Email address"
                            />
                        </div>
                        <InputError class="mt-2 text-red-400 text-sm" :message="form.errors.email" />
                    </div>

                    <!-- New Password field -->
                    <div>
                        <label for="password" class="block text-xs sm:text-sm font-medium mb-2 sm:mb-3 text-gray-300">
                            New Password
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-focus-within:text-orange-400 transition-colors duration-300 text-gray-500">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <circle cx="12" cy="16" r="1"></circle>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                class="pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 text-sm sm:text-base rounded-lg w-full border-0 focus:border-orange-400 focus:ring-2 focus:ring-orange-400 focus:ring-opacity-50 transition-all duration-300 bg-white bg-opacity-10 backdrop-blur-sm text-white placeholder-gray-300"
                                v-model="form.password"
                                placeholder="Enter your new password"
                                required
                                autocomplete="new-password"
                                aria-label="New password"
                            />
                        </div>
                        <InputError class="mt-2 text-red-400 text-sm" :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password field -->
                    <div>
                        <label for="password_confirmation" class="block text-xs sm:text-sm font-medium mb-2 sm:mb-3 text-gray-300">
                            Confirm New Password
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-focus-within:text-orange-400 transition-colors duration-300 text-gray-500">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <circle cx="12" cy="16" r="1"></circle>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </div>
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 text-sm sm:text-base rounded-lg w-full border-0 focus:border-orange-400 focus:ring-2 focus:ring-orange-400 focus:ring-opacity-50 transition-all duration-300 bg-white bg-opacity-10 backdrop-blur-sm text-white placeholder-gray-300"
                                v-model="form.password_confirmation"
                                placeholder="Confirm your new password"
                                required
                                autocomplete="new-password"
                                aria-label="Confirm new password"
                            />
                        </div>
                        <InputError class="mt-2 text-red-400 text-sm" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Action buttons -->
                    <div class="space-y-3 sm:space-y-4">
                        <button
                            type="submit"
                            class="w-full text-white font-semibold py-3 sm:py-4 px-4 sm:px-6 rounded-lg transition-all duration-300 flex items-center justify-center relative overflow-hidden group shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-opacity-50 transform hover:scale-105"
                            :class="[
                                'bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-400 hover:to-red-500',
                                { 'opacity-80 cursor-not-allowed transform-none': form.processing || isLoading }
                            ]"
                            :disabled="form.processing || isLoading"
                            aria-label="Reset Password"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <span v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span :class="{ 'opacity-0': isLoading }" class="flex items-center relative z-10 text-sm sm:text-base">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                </svg>
                                Reset Password
                            </span>
                        </button>

                        <!-- Back to login -->
                        <Link
                            :href="route('login')"
                            class="w-full inline-flex items-center justify-center py-2.5 sm:py-3 px-4 sm:px-6 border border-white border-opacity-30 rounded-lg text-xs sm:text-sm font-medium hover:bg-white hover:bg-opacity-10 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 transition-all duration-300 text-gray-300 hover:text-white"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5 sm:mr-2">
                                <path d="M19 12H5"></path>
                                <polyline points="12 5 5 12 12 19"></polyline>
                            </svg>
                            Back to Sign In
                        </Link>
                    </div>
                </form>
                
                <!-- Footer info -->
                <div class="mt-8 sm:mt-12 pt-4 sm:pt-6 border-t border-white border-opacity-20 text-center sm:text-left">
                    <p class="text-xs text-gray-400">
                        Need help? Contact your system administrator for assistance.
                    </p>
                    <p class="text-xs mt-2 text-gray-500">
                        © 2025 Laguna State Polytechnic University
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Slideshow navigation - Top right corner -->
        <div class="absolute top-4 sm:top-6 right-4 sm:right-6 flex space-x-1.5 sm:space-x-2 z-30">
            <button 
                v-for="(_, index) in slideshowImages" 
                :key="index"
                @click="activeSlide = index" 
                class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full transition-all duration-300"
                :class="[
                    activeSlide === index 
                        ? 'bg-orange-400 scale-125' 
                        : 'bg-white bg-opacity-40 hover:bg-opacity-70'
                ]"
                :aria-label="`Go to slide ${index + 1}`"
            ></button>
        </div>
        
        <!-- Gradient indicator - Bottom right -->
        <div class="absolute bottom-4 sm:bottom-6 right-4 sm:right-6 w-3 h-3 sm:w-4 sm:h-4 rounded-full transition-all duration-1000 ease-in-out z-30" 
             :class="gradientIndex === 0 ? 'bg-orange-400' : 'bg-purple-400'"></div>
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