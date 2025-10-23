<script setup>
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { auth } from '@/firebase/config';
import { confirmPasswordReset, verifyPasswordResetCode } from 'firebase/auth';

const props = defineProps({
    mode: {
        type: String,
        default: 'resetPassword'
    },
    oobCode: {
        type: String,
        required: true,
    },
    continueUrl: {
        type: String,
        default: null,
    },
});

const email = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const isLoading = ref(false);
const message = ref('');
const error = ref('');
const isCodeValid = ref(false);
const formElement = ref(null);
// Set dark mode as default
const isDarkMode = ref(true);
const activeSlide = ref(0);
const slideInterval = ref(null);
const gradientIndex = ref(0);
const gradientInterval = ref(null);

// Slideshow images matching login page
const slideshowImages = [
    '/images/LSPU9.jpg',
    '/images/LSPU2.jpg',
    '/images/LSPU3.jpg',
    '/images/LSPU6.jpg',
    '/images/LSPU5.jpg',
    '/images/LSPU7.jpg',
];

const submit = async () => {
    if (isLoading.value) return;
    
    // Clear previous messages
    message.value = '';
    error.value = '';
    
    // Validate password
    if (!password.value) {
        error.value = 'Please enter your new password.';
        return;
    }
    
    if (password.value.length < 8) {
        error.value = 'Password must be at least 8 characters long.';
        return;
    }
    
    if (password.value !== passwordConfirmation.value) {
        error.value = 'Passwords do not match.';
        return;
    }
    
    isLoading.value = true;
    
    try {
        // Confirm password reset with Firebase
        await confirmPasswordReset(auth, props.oobCode, password.value);
        
        // Sync the new password with Laravel database
        const syncResponse = await fetch('/firebase/sync-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                email: email.value, 
                password: password.value 
            })
        });
        
        const syncResult = await syncResponse.json();
        
        if (syncResult.success) {
            message.value = 'Password reset successfully! You can now sign in with your new password.';
            
            // Redirect to login after a short delay
            setTimeout(() => {
                router.visit('/login', {
                    data: { 
                        message: 'Password reset successfully! You can now sign in with your new password.' 
                    }
                });
            }, 2000);
        } else {
            error.value = 'Password reset failed. Please try again or contact support.';
        }
        
    } catch (firebaseError) {
        console.error('Firebase error:', firebaseError);
        
        // Handle specific Firebase error codes
        switch (firebaseError.code) {
            case 'auth/expired-action-code':
                error.value = 'The reset link has expired. Please request a new password reset.';
                break;
            case 'auth/invalid-action-code':
                error.value = 'Invalid or already used reset link. Please request a new password reset.';
                break;
            case 'auth/weak-password':
                error.value = 'Password is too weak. Please choose a stronger password.';
                break;
            default:
                error.value = 'An error occurred while resetting your password. Please try again.';
                break;
        }
    } finally {
        isLoading.value = false;
    }
};

// Verify the reset code on component mount
const verifyResetCode = async () => {
    try {
        const emailFromCode = await verifyPasswordResetCode(auth, props.oobCode);
        email.value = emailFromCode;
        isCodeValid.value = true;
    } catch (verifyError) {
        console.error('Code verification error:', verifyError);
        isCodeValid.value = false;
        
        switch (verifyError.code) {
            case 'auth/expired-action-code':
                error.value = 'The reset link has expired. Please request a new password reset.';
                break;
            case 'auth/invalid-action-code':
                error.value = 'Invalid reset link. Please request a new password reset.';
                break;
            default:
                error.value = 'Invalid reset link. Please request a new password reset.';
                break;
        }
    }
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
    
    // Verify the reset code
    verifyResetCode();
    
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
    <Head title="Reset Password | LSPU ORBIT">
        <!-- Preload the first slideshow image for better LCP -->
        <link rel="preload" as="image" :href="slideshowImages[0]" fetchpriority="high" />
    </Head>
    
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
                    class="absolute inset-0 transition-opacity duration-1000"
                >
                    <!-- Use img tag for better LCP -->
                    <img 
                        :src="image"
                        alt="LSPU Campus"
                        class="w-full h-full object-cover object-center filter brightness-[0.3] contrast-[1.2]"
                        :loading="index === 0 ? 'eager' : 'lazy'"
                        :fetchpriority="index === 0 ? 'high' : 'low'"
                        width="1920"
                        height="1080"
                    />
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

                <!-- Success message -->
                <div v-if="message" class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-500 bg-opacity-20 border-l-4 border-green-500 text-green-400 text-xs sm:text-sm font-medium animate-fadeIn backdrop-blur-sm rounded-r-lg">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3 flex-shrink-0">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22,4 12,14.01 9,11.01"></polyline>
                        </svg>
                        {{ message }}
                    </div>
                </div>
                
                <!-- Error message -->
                <div v-if="error" class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-500 bg-opacity-20 border-l-4 border-red-500 text-red-400 text-xs sm:text-sm font-medium animate-fadeIn backdrop-blur-sm rounded-r-lg">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3 flex-shrink-0">
                            <path d="M18 6L6 18"></path>
                            <path d="M6 6l12 12"></path>
                        </svg>
                        {{ error }}
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-4 sm:space-y-6" novalidate v-if="isCodeValid">
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
                                v-model="email"
                                readonly
                                autocomplete="username"
                                aria-label="Email address"
                            />
                        </div>
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
                                v-model="password"
                                placeholder="Enter your new password"
                                required
                                autocomplete="new-password"
                                aria-label="New password"
                            />
                        </div>
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
                                v-model="passwordConfirmation"
                                placeholder="Confirm your new password"
                                required
                                autocomplete="new-password"
                                aria-label="Confirm new password"
                            />
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="space-y-3 sm:space-y-4">
                        <button
                            type="submit"
                            class="w-full text-white font-semibold py-3 sm:py-4 px-4 sm:px-6 rounded-lg transition-all duration-300 flex items-center justify-center relative overflow-hidden group shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-opacity-50 transform hover:scale-105 disabled:transform-none disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="[
                                isLoading 
                                    ? 'bg-gradient-to-r from-gray-500 to-gray-600' 
                                    : 'bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600'
                            ]"
                            :disabled="isLoading"
                            aria-label="Reset Password"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white dark:bg-gray-800 rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <span class="flex items-center relative z-10 text-sm sm:text-base">
                                <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 sm:mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                </svg>
                                {{ isLoading ? 'Resetting...' : 'Reset Password' }}
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