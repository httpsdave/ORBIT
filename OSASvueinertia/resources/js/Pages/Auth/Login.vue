<script setup>
import Checkbox from '@/Components/Checkbox.vue';

import InputError from '@/Components/InputError.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';


defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const passwordVisible = ref(false);
const isLoading = ref(false);
const formElement = ref(null);
// Set dark mode as default
const isDarkMode = ref(true);
const activeSlide = ref(0);
const slideInterval = ref(null);

// Slideshow images
const slideshowImages = [
    '/images/LSPU1.jpg',
    '/images/LSPU2.jpg',
    '/images/LSPU3.jpg',
    '/images/LSPU6.jpg',
    '/images/LSPU5.jpg',
];

const togglePasswordVisibility = () => {
    passwordVisible.value = !passwordVisible.value;
};

const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
};

const submit = () => {
    if (form.processing) return;
    
    isLoading.value = true;
    form.post(route('login'), {
        onFinish: () => {
            isLoading.value = false;
            form.reset('password');
        },
    });
};

const startSlideshow = () => {
    slideInterval.value = setInterval(() => {
        activeSlide.value = (activeSlide.value + 1) % slideshowImages.length;
    }, 10000);
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
    <Head title="Login | LSPU ORBIT" />
    
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
                    :style="{ backgroundImage: `url(${image})`, filter: isDarkMode ? 'brightness(0.7)' : 'brightness(1.1) contrast(1.1)' }"
                >
                </div>
            </transition-group>
            <!-- Overlay to ensure content readability -->
            <div class="absolute inset-0" :class="isDarkMode ? 'bg-gray-900 bg-opacity-60' : 'bg-white bg-opacity-40 backdrop-filter backdrop-blur-sm'"></div>
        </div>
        
        <!-- Content wrapper -->
        <div 
            ref="formElement" 
            class="w-full max-w-4xl flex flex-col md:flex-row gap-4 relative opacity-0 transition-all duration-700 z-20"
            :class="isDarkMode ? 'text-white' : 'text-gray-800'"
        >
            <!-- Left side panel -->
            <div 
                class="hidden md:flex md:w-2/5 lg:w-2/5 flex-col items-center justify-center relative rounded-2xl overflow-hidden transition-all duration-300 shadow-xl z-10"
                :class="isDarkMode 
                    ? 'bg-gradient-to-br from-blue-500/80 via-green-500/70 to-yellow-500/60 hover:shadow-blue-500/20' 
                    : 'bg-gradient-to-br from-blue-500/70 via-green-500/60 to-yellow-500/50 hover:shadow-blue-300/30'"
            >
                <!-- Background image with overlay -->
                <div class="absolute inset-0">
                    <img 
                        src="/images/left_panel.jpg" 
                        alt="LSPU Background" 
                        class="w-full h-full object-cover object-center transition-opacity duration-1000"
                    />
                    <!-- Gradient overlay on top of the image -->
                    <div class="absolute inset-0" 
                        :class="isDarkMode 
                            ? 'bg-gradient-to-br from-blue-500/80 via-green-500/70 to-yellow-500/60' 
                            : 'bg-gradient-to-br from-blue-500/70 via-green-500/60 to-yellow-500/50'">
                    </div>
                </div>
                
                <!-- Animated background pattern -->
                <div class="absolute inset-0 overflow-hidden opacity-10">
                    <div class="absolute top-1/4 left-1/4 w-16 h-16 rounded-full bg-white transform rotate-45 animate-pulse"></div>
                    <div class="absolute bottom-1/3 right-1/5 w-20 h-20 rounded-full bg-white animate-pulse" style="animation-delay: 1s;"></div>
                    <div class="absolute top-3/4 left-1/2 w-12 h-12 rounded-full bg-white animate-pulse" style="animation-delay: 2s;"></div>
                </div>
                
                <div class="relative z-10 flex flex-col items-center transform transition-all duration-500 hover:scale-105 p-6">
                    <div class="px-0.0 bg-white bg-opacity-10 backdrop-blur-sm rounded-full mb-6 shadow-lg hover:shadow-white/20 transition-all duration-300">
                        <img src="/images/lspu_logo_better.png" alt="LSPU Logo" class="w-24 h-24 md:w-28 md:h-28 lg:w-32 lg:h-32 object-cover filter drop-shadow-lg">
                    </div>
                    
                    <div class="text-center mb-8">
                        <h2 class="text-lg md:text-xl font-bold mb-2 text-white tracking-wider font-sans">WELCOME TO</h2>
                        <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold mb-4 text-transparent bg-clip-text bg-gradient-to-r from-white to-blue-100 font-sans tracking-wide">ORBIT</h1>
                        <div class="h-1 w-32 bg-white opacity-70 mx-auto mb-6 rounded-full"></div>
                        <p class="text-white text-opacity-90 text-sm max-w-xs mx-auto leading-relaxed font-sans tracking-wide backdrop-blur-sm bg-white/5 p-3 rounded-lg shadow-inner">
                            🚀 <span class="font-medium">Organized Records for Better Institutional Tracking.</span> 
                            <span class="block mt-2">Streamline creation, submission, and tracking of organizational documents while delivering real-time updates on campus events.</span>
                        </p>
                    </div>
                </div>

                

                <!-- Decorative elements -->
                <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-black opacity-30"></div>
                
                <!-- Social links with hover effect -->
                <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-3">
                    <a href="#" class="bg-white bg-opacity-10 backdrop-blur-sm hover:bg-opacity-30 p-2 rounded-full transition-all duration-300 hover:scale-110 hover:shadow-lg" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="#" class="bg-white bg-opacity-10 backdrop-blur-sm hover:bg-opacity-30 p-2 rounded-full transition-all duration-300 hover:scale-110 hover:shadow-lg" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    <a href="#" class="bg-white bg-opacity-10 backdrop-blur-sm hover:bg-opacity-30 p-2 rounded-full transition-all duration-300 hover:scale-110 hover:shadow-lg" aria-label="Twitter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Right side panel with login form -->
            <div 
                class="w-full md:w-3/5 lg:w-3/5 p-5 md:p-8 rounded-2xl shadow-xl transition-all duration-300 border backdrop-filter backdrop-blur-md"
                :class="isDarkMode 
                    ? 'bg-gray-900 bg-opacity-70 border-gray-800' 
                    : 'bg-white bg-opacity-60 border-white border-opacity-40'"
            >
                
                <!-- Top banner for small screens -->
                <div class="md:hidden w-full bg-gradient-to-r from-green-500 to-blue-500 p-4 flex justify-center items-center rounded-xl mb-6 shadow-lg">
                    <img src="/images/lspu_logo_better.png" alt="LSPU Logo" class="w-12 h-12 mr-3">
                    <div class="text-white">
                        <h2 class="text-lg font-bold">LSPU ORBIT</h2>
                        <div class="h-0.5 w-16 bg-white opacity-70"></div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <!-- Logo and banner (shown only on larger screens) -->
                    <div class="hidden md:flex justify-center items-center mb-6">
                        <div class="relative mr-4">
                            <header class="flex items-center">
                                <ApplicationLogo class="w-20 h-15 scale-150" />
                            </header>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-bold" :class="isDarkMode ? 'text-white' : 'text-gray-800'">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-500 to-green-500">LSPU</span><span class="text-transparent bg-clip-text bg-gradient-to-r from-green-500 to-blue-500"> ORBIT</span>
                        </h1>
                    </div>

                    <!-- Colored banner -->
                    <div class="flex w-full mb-4 overflow-hidden rounded-lg shadow-lg">
                        <div class="w-1/4 h-1.5 bg-blue-500 animate-pulse" style="animation-delay: 0.2s;"></div>
                        <div class="w-1/4 h-1.5 bg-green-500 animate-pulse" style="animation-delay: 0.4s;"></div>
                        <div class="w-1/4 h-1.5 bg-yellow-500 animate-pulse" style="animation-delay: 0.6s;"></div>
                        <div class="w-1/4 h-1.5 bg-red-500 animate-pulse" style="animation-delay: 0.8s;"></div>
                    </div>
                    
                    <p class="text-xs text-center tracking-wider" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                        INTEGRITY • PROFESSIONALISM • INNOVATION
                    </p>
                </div>
                
                <div v-if="status" class="mb-5 py-2.5 px-3 bg-green-500 bg-opacity-10 border border-green-500 border-opacity-20 text-green-500 text-xs font-medium rounded-lg text-center animate-fadeIn">
                    {{ status }}
                </div>

                <h2 class="text-xl font-semibold mb-6 text-center" :class="isDarkMode ? 'text-white' : 'text-gray-800'">
                    Sign in to your account
                </h2>

                <form @submit.prevent="submit" class="space-y-5" novalidate>
                    <div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-focus-within:text-blue-500 transition-colors duration-300" :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <TextInput
                                id="email"
                                type="email"
                                class="pl-10 pr-4 py-3 rounded-xl w-full focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-40 transition duration-300"
                                :class="isDarkMode 
                                    ? 'bg-gray-800 bg-opacity-70 border-gray-700 text-white' 
                                    : 'bg-white bg-opacity-70 border-white border-opacity-50 text-gray-800'"
                                v-model="form.email"
                                placeholder="Email"
                                required
                                autofocus
                                autocomplete="username"
                                aria-label="Email address"
                            />
                        </div>
                        <InputError class="mt-2 text-red-500 text-xs" :message="form.errors.email" />
                    </div>

                    <div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-focus-within:text-blue-500 transition-colors duration-300" :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </div>
                            <TextInput
                                id="password"
                                :type="passwordVisible ? 'text' : 'password'"
                                class="pl-10 pr-10 py-3 rounded-xl w-full focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-40 transition duration-300"
                                :class="isDarkMode 
                                    ? 'bg-gray-800 bg-opacity-70 border-gray-700 text-white' 
                                    : 'bg-white bg-opacity-70 border-white border-opacity-50 text-gray-800'"
                                v-model="form.password"
                                placeholder="Password"
                                required
                                autocomplete="current-password"
                                aria-label="Password"
                            />
                            <button 
                                type="button" 
                                @click="togglePasswordVisibility" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center hover:text-blue-500 transition-colors duration-300"
                                :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'"
                                aria-label="Toggle password visibility"
                            >
                                <svg v-if="!passwordVisible" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-2 text-red-500 text-xs" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center group cursor-pointer">
                            <Checkbox 
                                name="remember" 
                                v-model:checked="form.remember" 
                                class="text-blue-500 rounded focus:ring-blue-500" 
                                :class="isDarkMode ? 'border-gray-700 focus:ring-offset-gray-900' : 'border-gray-300 focus:ring-offset-white'"
                            />
                            <span 
                                class="ml-2 text-xs group-hover:text-gray-700 transition-colors duration-300" 
                                :class="isDarkMode ? 'text-gray-400 group-hover:text-gray-300' : 'text-gray-500'"
                            >
                                Remember me
                            </span>
                        </label>
                        
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs hover:text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded-md transition duration-300"
                            :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        class="w-full text-white font-medium py-3 px-4 rounded-xl transition duration-300 flex items-center justify-center relative overflow-hidden group shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                        :class="[
                            isDarkMode 
                                ? 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600' 
                                : 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-400 hover:to-blue-500',
                            { 'opacity-80 cursor-not-allowed': form.processing || isLoading }
                        ]"
                        :disabled="form.processing || isLoading"
                        aria-label="Sign In"
                    >
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                        <span v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span :class="{ 'opacity-0': isLoading }" class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                <polyline points="10 17 15 12 10 7"></polyline>
                                <line x1="15" y1="12" x2="3" y2="12"></line>
                            </svg>
                            Sign In
                        </span>
                    </button>

                    <div class="text-center">
                        <p class="text-xs" :class="isDarkMode ? 'text-gray-400' : 'text-gray-500'">
                            Account access is managed by administrators. 
                            <span class="text-blue-500">Contact your administrator for access.</span>
                        </p>
                    </div>
                </form>
                
                <div class="mt-8 text-center">
                    <p class="text-xs" :class="isDarkMode ? 'text-gray-500' : 'text-gray-400'">
                        © 2025 Laguna State Polytechnic University
                    </p>
                    <div class="mt-1 flex justify-center space-x-3">
                        <a 
                            href="#" 
                            class="text-xs hover:text-gray-700 transition-colors duration-300" 
                            :class="isDarkMode ? 'text-gray-500 hover:text-gray-400' : 'text-gray-500'"
                        >
                            Privacy Policy
                        </a>
                        <span :class="isDarkMode ? 'text-gray-700' : 'text-gray-400'">•</span>
                        <a 
                            href="#" 
                            class="text-xs hover:text-gray-700 transition-colors duration-300" 
                            :class="isDarkMode ? 'text-gray-500 hover:text-gray-400' : 'text-gray-500'"
                        >
                            Terms of Service
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Slideshow navigation dots -->
        <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2 z-10">
            <button 
                v-for="(_, index) in slideshowImages" 
                :key="index"
                @click="activeSlide = index" 
                class="w-2 h-2 rounded-full transition-all duration-300"
                :class="[
                    activeSlide === index 
                        ? isDarkMode ? 'bg-white scale-125' : 'bg-blue-500 scale-125' 
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
</style>