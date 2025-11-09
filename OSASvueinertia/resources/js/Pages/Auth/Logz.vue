<script setup>
import { ref, onMounted, onBeforeUnmount, defineAsyncComponent, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useGlobalLoading } from '@/Composables/useGlobalLoading';

// Lazy load components - Only load when actually needed
const Checkbox = defineAsyncComponent(() => import('@/Components/Checkbox.vue'));
const InputError = defineAsyncComponent(() => import('@/Components/InputError.vue'));
const ApplicationLogo = defineAsyncComponent(() => import('@/Components/ApplicationLogo.vue'));
const TextInput = defineAsyncComponent(() => import('@/Components/TextInput.vue'));

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

const { startLoading, stopLoading } = useGlobalLoading();

const passwordVisible = ref(false);
const isLoading = ref(false);
const formElement = ref(null);
// Set dark mode as default - using const since it doesn't change
const isDarkMode = true;
const activeSlide = ref(0);
const slideInterval = ref(null);
const gradientIndex = ref(0);
const gradientInterval = ref(null);
const windowWidth = ref(0);

// Enhanced error handling and user feedback
const showError = ref(false);
const errorMessage = ref('');
const capsLockOn = ref(false);
const clientErrors = ref({});

// Form validation - Optimized for smaller bundle size
const validateEmail = (email) => {
    // Simpler, smaller regex that covers 99% of cases
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

const validateForm = () => {
    const errors = {};
    if (!form.email) {
        errors.email = 'Email is required';
    } else if (!validateEmail(form.email)) {
        errors.email = 'Please enter a valid email address';
    }
    if (!form.password) {
        errors.password = 'Password is required';
    } else if (form.password.length < 8) {
        errors.password = 'Password must be at least 8 characters';
    }
    return errors;
};

// Optimized window width tracking with debounce
let resizeTimeout;
const updateWindowWidth = () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        windowWidth.value = window.innerWidth;
    }, 100); // Debounce to reduce excessive updates
};

// Optimized slideshow images with responsive sources
const slideshowImages = [
    {
        id: 'campus-1',
        src: '/images/optimized/LSPU9.webp',
        mobileSrc: '/images/optimized/LSPU9-mobile.webp',
        alt: 'LSPU Campus aerial view',
    },
    {
        id: 'campus-2',
        src: '/images/optimized/LSPU2.webp',
        mobileSrc: '/images/optimized/LSPU2-mobile.webp',
        alt: 'LSPU Campus student plaza',
    },
    {
        id: 'campus-3',
        src: '/images/optimized/LSPU3.webp',
        mobileSrc: '/images/optimized/LSPU3-mobile.webp',
        alt: 'LSPU campus courtyard',
    },
    {
        id: 'campus-4',
        src: '/images/optimized/LSPU6.webp',
        mobileSrc: '/images/optimized/LSPU6-mobile.webp',
        alt: 'Students at LSPU grounds',
    },
    {
        id: 'campus-5',
        src: '/images/optimized/LSPU5.webp',
        mobileSrc: '/images/optimized/LSPU5-mobile.webp',
        alt: 'LSPU campus facade',
    },
    {
        id: 'campus-6',
        src: '/images/optimized/LSPU7.webp',
        mobileSrc: '/images/optimized/LSPU7-mobile.webp',
        alt: 'LSPU campus skyline',
    },
];

const slideshowSizes = '(max-width: 1024px) 100vw, 55vw';

const getSrcSet = (image) => {
    if (!image || !image.mobileSrc) {
        return undefined;
    }
    return `${image.mobileSrc} 640w, ${image.src} 1920w`;
};

// Optimized preload function
const preloadFirstImage = () => {
    const firstImage = slideshowImages[0];
    if (!firstImage) return;

    const img = new Image();
    img.src = firstImage.src;
};

const togglePasswordVisibility = () => {
    passwordVisible.value = !passwordVisible.value;
};

// Enhanced submit with validation and error handling
const submit = () => {
    if (form.processing || isLoading.value) return;
    
    // Clear previous errors
    clientErrors.value = {};
    showError.value = false;
    
    // Client-side validation
    const errors = validateForm();
    if (Object.keys(errors).length > 0) {
        clientErrors.value = errors;
        return;
    }
    
    isLoading.value = true;
    startLoading('login-form');
    
    form.post(route('login'), {
        onError: (errors) => {
            if (errors.email && errors.email.includes('rate limit')) {
                errorMessage.value = 'Too many login attempts. Please try again later.';
                showError.value = true;
            } else if (errors.email && errors.email.includes('credentials')) {
                errorMessage.value = 'Invalid email or password. Please check your credentials.';
                showError.value = true;
            } else if (Object.keys(errors).length > 0) {
                errorMessage.value = 'Login failed. Please check your credentials and try again.';
                showError.value = true;
            }
        },
        onFinish: () => {
            isLoading.value = false;
            stopLoading('login-form');
            form.reset('password');
        },
    });
};

// Caps lock detection
const detectCapsLock = (event) => {
    capsLockOn.value = event.getModifierState('CapsLock');
};

// Clear client-side errors when user starts typing
const clearClientError = (field) => {
    if (clientErrors.value[field]) {
        delete clientErrors.value[field];
    }
    if (showError.value) {
        showError.value = false;
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
    // Critical path - Preload first image for better LCP
    preloadFirstImage();
    
    // Critical path - Initialize window width
    updateWindowWidth();
    window.addEventListener('resize', updateWindowWidth);
    
    // Critical path - Fade in animation on page load
    if (formElement.value) {
        formElement.value.classList.add('opacity-100');
    }
    
    // Defer non-critical animations to next tick for better performance
    nextTick(() => {
        // Start slideshow after main content is rendered
        startSlideshow();
        
        // Start gradient animation after main content is rendered
        startGradientAnimation();
    });
});

onBeforeUnmount(() => {
    // Remove resize listener
    window.removeEventListener('resize', updateWindowWidth);
    
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
    <Head title="Login | LSPU ORBIT">
        <meta name="description" content="Login to LSPU ORBIT - Organized Records for Better Institutional Tracking. Access your student organization account to manage documents, activities, and submissions for the Office of Student Affairs and Services." />
        
        <!-- Font optimization - Preconnect to font CDN -->
        <link rel="dns-prefetch" href="https://fonts.bunny.net" />
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin />
        
        <!-- Preload the first slideshow image for better LCP - Critical for performance -->
        <link
            rel="preload"
            as="image"
            :href="slideshowImages[0]?.src"
            fetchpriority="high"
            :imagesrcset="getSrcSet(slideshowImages[0])"
            :imagesizes="slideshowSizes"
        />
        
        <!-- Preconnect to image domain if using external CDN -->
        <link rel="dns-prefetch" href="/" />
    </Head>
    
    <!-- Full screen container with split layout -->
    <div class="min-h-screen flex relative overflow-hidden">
        <!-- Background slideshow for left side -->
        <div class="absolute inset-0 right-20 sm:right-24 md:right-32 lg:right-40 xl:right-48 z-0 bg-gray-900">
            <!-- First image - always rendered for LCP optimization -->
            <div 
                v-show="activeSlide === 0"
                class="absolute inset-0"
            >
                <!-- Use img tag for better LCP - First image with highest priority -->
                <img 
                    :src="slideshowImages[0].src"
                    :srcset="getSrcSet(slideshowImages[0])"
                    :sizes="slideshowSizes"
                    :alt="slideshowImages[0].alt"
                    class="w-full h-full object-cover object-center filter brightness-[0.3] contrast-[1.2]"
                    loading="eager"
                    fetchpriority="high"
                    width="1920"
                    height="1080"
                    decoding="async"
                />
            </div>
            
            <!-- Other slideshow images - lazy loaded -->
            <transition-group name="slideshow-fade">
                <div 
                    v-for="(image, index) in slideshowImages.slice(1)" 
                    :key="image.id" 
                    v-show="activeSlide === index + 1"
                    class="absolute inset-0"
                >
                    <img 
                        :src="image.src"
                        :srcset="getSrcSet(image)"
                        :sizes="slideshowSizes"
                        :alt="image.alt"
                        class="w-full h-full object-cover object-center filter brightness-[0.3] contrast-[1.2]"
                        loading="lazy"
                        fetchpriority="low"
                        width="1920"
                        height="1080"
                        decoding="async"
                    />
                </div>
            </transition-group>
            <!-- Subtle overlay -->
            <div class="absolute inset-0 bg-gray-900 bg-opacity-40"></div>
        </div>
        
        <!-- Left side - Main content area -->
        <!-- Left side - Main content area -->
        <div class="flex-1 relative z-20 flex items-center justify-center lg:justify-between xl:justify-between px-4 sm:px-6 md:px-8 lg:px-12 xl:px-16">
            <!-- ORBIT Information - Only visible on screens wider than 1240px -->
            <div v-show="windowWidth > 1240" class="lg:max-w-md xl:max-w-lg mr-8 xl:mr-12">
                <div class="opacity-90 text-white">
                    <!-- ORBIT Heading -->
                    <h2 class="text-3xl xl:text-4xl font-bold mb-4 text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-green-400">
                        What is ORBIT?
                    </h2>
                    
                    <!-- Colored accent line -->
                    <div class="flex mb-6 w-32">
                        <div class="w-1/4 h-1 bg-blue-500"></div>
                        <div class="w-1/4 h-1 bg-green-500"></div>
                        <div class="w-1/4 h-1 bg-blue-500"></div>
                        <div class="w-1/4 h-1 bg-green-500"></div>
                    </div>
                    
                    <!-- ORBIT Definition -->
                    <div class="space-y-4 text-sm xl:text-base leading-relaxed text-gray-300">
                        <p class="font-semibold text-lg xl:text-xl text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-green-300">
                            Organized Records for Better Institutional Tracking
                        </p>
                        
                        <p>
                            ORBIT is an Information System developed for Student Organizations aimed at simplifying the process of preparation, submission, and approval of required documents under the Office of Student Affairs and Services.
                        </p>
                        
                        <!-- Features list -->
                        <div class="space-y-2 mt-6">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3 text-green-400 flex-shrink-0">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22,4 12,14.01 9,11.01"></polyline>
                                </svg>
                                <span class="text-sm xl:text-base">Streamlined document preparation</span>
                            </div>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3 text-blue-400 flex-shrink-0">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22,4 12,14.01 9,11.01"></polyline>
                                </svg>
                                <span class="text-sm xl:text-base">Simplified submission process</span>
                            </div>
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3 text-green-400 flex-shrink-0">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22,4 12,14.01 9,11.01"></polyline>
                                </svg>
                                <span class="text-sm xl:text-base">Efficient approval workflow</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content container positioned to the right -->
            <div 
                ref="formElement"
                class="w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg opacity-0 transition-all duration-700 sm:ml-auto text-white"
            >
                <!-- Header section -->
                <div class="mb-6 sm:mb-8 md:mb-10 lg:mb-12 text-center sm:text-left relative">
                    <!-- Main heading with logo -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-3 sm:mb-1 text-white">
                            Welcome to
                            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-green-400">
                                LSPU ORBIT
                            </span>
                        </h1>
                        <!-- ApplicationLogo - Hidden on mobile, absolutely positioned on sm+ so it doesn't affect layout -->
                        <div class="hidden sm:flex absolute inset-y-0 right-0 items-center justify-center pointer-events-none z-50" style="transform: translate(12px, -36px);">
                            <ApplicationLogo class="w-44 h-44 md:w-52 md:h-52 lg:w-64 lg:h-64" />
                        </div>
                    </div>
                    
                    <!-- Colored accent line -->
                    <div class="flex mb-4 w-24 sm:w-32 mx-auto sm:mx-0">
                        <div class="w-1/4 h-1 bg-blue-500"></div>
                        <div class="w-1/4 h-1 bg-green-500"></div>
                        <div class="w-1/4 h-1 bg-blue-500"></div>
                        <div class="w-1/4 h-1 bg-green-500"></div>
                    </div>
                    
                    <!-- Subtitle -->
                    <p class="text-sm sm:text-base md:text-lg leading-relaxed text-gray-300">
                        Sign in to access your account and manage your organizational records and activities.
                    </p>
                </div>
                
                <!-- Global Error Display -->
                <div v-if="showError" class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-500 bg-opacity-20 border-l-4 border-red-500 text-red-400 text-xs sm:text-sm font-medium animate-fadeIn backdrop-blur-sm rounded-r-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3 flex-shrink-0">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            {{ errorMessage }}
                        </div>
                        <button @click="showError = false" class="ml-2 text-red-400 hover:text-red-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Status message -->
                <div v-if="status" class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-500 bg-opacity-20 border-l-4 border-green-500 text-green-400 text-xs sm:text-sm font-medium animate-fadeIn backdrop-blur-sm rounded-r-lg">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3 flex-shrink-0">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22,4 12,14.01 9,11.01"></polyline>
                        </svg>
                        {{ status }}
                    </div>
                </div>

                <!-- Caps Lock Warning -->
                <div v-if="capsLockOn" class="mb-4 p-3 bg-yellow-500 bg-opacity-20 border-l-4 border-yellow-500 text-yellow-400 text-xs sm:text-sm font-medium animate-fadeIn backdrop-blur-sm rounded-r-lg">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3 flex-shrink-0">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Caps Lock is on
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-4 sm:space-y-6" novalidate>
                    <div>
                        <div class="relative group">
                            <TextInput
                                id="email"
                                type="email"
                                class="peer pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 text-sm sm:text-base rounded-lg w-full border-0 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 transition-all duration-300 bg-white bg-opacity-10 backdrop-blur-sm text-white placeholder-transparent"
                                v-model="form.email"
                                @input="clearClientError('email')"
                                placeholder="Email Address"
                                required
                                autofocus
                                autocomplete="username"
                                aria-label="Email address"
                                :aria-describedby="(form.errors.email || clientErrors.email) ? 'email-error' : null"
                                :aria-invalid="(form.errors.email || clientErrors.email) ? 'true' : 'false'"
                            />
                            <label 
                                for="email" 
                                class="floating-label absolute left-10 sm:left-12 top-3 sm:top-4 text-sm sm:text-base text-gray-300 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
                                :class="[
                                    form.email ? 'floating-label-active' : '',
                                ]"
                            >
                                Email Address
                            </label>
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-focus-within:text-blue-400 transition-colors duration-300 text-gray-500">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                        </div>
                        <InputError id="email-error" class="mt-2 text-red-400 text-sm" :message="form.errors.email || clientErrors.email" />
                    </div>

                    <div>
                        <div class="relative group">
                            <TextInput
                                id="password"
                                :type="passwordVisible ? 'text' : 'password'"
                                class="peer pl-10 sm:pl-12 pr-10 sm:pr-12 py-3 sm:py-4 text-sm sm:text-base rounded-lg w-full border-0 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 transition-all duration-300 backdrop-blur-sm placeholder-transparent bg-white bg-opacity-10 text-white"
                                v-model="form.password"
                                @input="clearClientError('password')"
                                @keydown="detectCapsLock"
                                @keyup="detectCapsLock"
                                placeholder="Password"
                                required
                                autocomplete="current-password"
                                aria-label="Password"
                                :aria-describedby="(form.errors.password || clientErrors.password) ? 'password-error' : capsLockOn ? 'caps-lock-warning' : null"
                                :aria-invalid="(form.errors.password || clientErrors.password) ? 'true' : 'false'"
                            />
                            <label 
                                for="password" 
                                class="floating-label absolute left-10 sm:left-12 top-3 sm:top-4 text-sm sm:text-base text-gray-300 pointer-events-none peer-focus:floating-label-active peer-[:not(:placeholder-shown)]:floating-label-active"
                                :class="[
                                    form.password ? 'floating-label-active' : '',
                                ]"
                            >
                                Password
                            </label>
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-focus-within:text-blue-400 transition-colors duration-300 text-gray-500">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </div>
                            <button 
                                type="button" 
                                @click="togglePasswordVisibility" 
                                class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center hover:text-blue-400 transition-colors duration-300 text-gray-500"
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
                        <InputError id="password-error" class="mt-2 text-red-400 text-sm" :message="form.errors.password || clientErrors.password" />
                    </div>

                    <!-- Remember me and forgot password -->
                    <div class="flex flex-row items-center justify-between space-x-3 sm:space-x-4 min-h-[2.5rem]">
                        <label class="flex items-center group cursor-pointer flex-shrink-0">
                            <Checkbox 
                                name="remember" 
                                v-model:checked="form.remember" 
                                class="text-blue-500 rounded focus:ring-blue-500 border-gray-400" 
                            />
                            <span 
                                class="ml-2 sm:ml-3 text-xs sm:text-sm group-hover:text-gray-200 transition-colors duration-300 text-gray-400"
                            >
                                Remember me
                            </span>
                        </label>
                        
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs sm:text-sm hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 rounded-md transition duration-300 whitespace-nowrap flex-shrink-0 text-gray-400"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <!-- Action buttons -->
                    <div class="space-y-3 sm:space-y-4">
                        <button
                            type="submit"
                            class="w-full text-white font-semibold py-3 sm:py-4 px-4 sm:px-6 rounded-full transition-all duration-300 flex items-center justify-center relative overflow-hidden group shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-50 active:scale-95 bg-gradient-to-r from-blue-500 to-green-600 hover:from-blue-400 hover:to-green-500"
                            :class="{ 'opacity-80 cursor-not-allowed': form.processing || isLoading }"
                            :disabled="form.processing || isLoading"
                            aria-label="Login"
                        >
                            <span class="absolute inset-0 w-full h-full transition-all duration-700 ease-out bg-white rounded-full opacity-0 group-hover:opacity-10 scale-0 group-hover:scale-100"></span>
                            <span v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span :class="{ 'opacity-0': isLoading }" class="flex items-center relative z-10 text-sm sm:text-base">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10 17 15 12 10 7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                                Login
                            </span>
                        </button>
                    </div>
                </form>
                
                <!-- Footer info -->
                <div class="mt-8 sm:mt-12 pt-4 sm:pt-6 border-t border-white border-opacity-20 text-center sm:text-left">
                    <p class="text-xs text-gray-400">
                        Account access is managed by administrators. Contact your administrator for assistance.
                    </p>
                    <p class="text-xs mt-2 text-gray-500">
                        © 2025 Laguna State Polytechnic University
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Right side - Wide gradient panel -->
        <div 
            class="w-20 sm:w-24 md:w-32 lg:w-40 xl:w-48 flex-shrink-0 relative overflow-hidden transition-all duration-1000 ease-in-out"
            :class="gradientIndex === 0 ? 'bg-gradient-to-t from-blue-500 via-green-500 to-blue-500' : 'bg-gradient-to-t from-green-500 via-blue-500 to-green-500'"
        >
            <!-- Animated background pattern -->
            <div class="absolute inset-0 overflow-hidden opacity-20">
                <div class="absolute top-1/6 left-1/2 w-10 h-10 rounded-full bg-white transform -translate-x-1/2 animate-pulse"></div>
                <div class="absolute top-1/3 left-1/2 w-8 h-8 rounded-full bg-white transform -translate-x-1/2 animate-pulse" style="animation-delay: 1s;"></div>
                <div class="absolute top-1/2 left-1/2 w-12 h-12 rounded-full bg-white transform -translate-x-1/2 animate-pulse" style="animation-delay: 2s;"></div>
                <div class="absolute top-2/3 left-1/2 w-9 h-9 rounded-full bg-white transform -translate-x-1/2 animate-pulse" style="animation-delay: 3s;"></div>
                <div class="absolute top-5/6 left-1/2 w-7 h-7 rounded-full bg-white transform -translate-x-1/2 animate-pulse" style="animation-delay: 4s;"></div>
            </div>
            
            <!-- Vertical logo and text -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white p-2 sm:p-3">
                <div class="mb-4 sm:mb-6 lg:mb-8 xl:mb-12">
                    <img
                        src="/images/optimized/lspu_logo_better-96.webp"
                        srcset="/images/optimized/lspu_logo_better-96.webp 96w, /images/optimized/lspu_logo_better-192.webp 192w, /images/optimized/lspu_logo_better-256.webp 256w"
                        sizes="(min-width: 1280px) 96px, (min-width: 1024px) 80px, (min-width: 768px) 64px, (min-width: 640px) 48px, 40px"
                        alt="LSPU Logo"
                        width="96"
                        height="96"
                        decoding="async"
                        class="w-10 h-10 sm:w-12 sm:h-12 md:w-16 md:h-16 lg:w-20 lg:h-20 xl:w-24 xl:h-24 object-cover filter drop-shadow-lg"
                    >
                </div>
                
                <!-- Vertical text -->
                <div class="writing-mode-vertical text-center">
                    <div class="text-sm sm:text-base md:text-lg lg:text-xl font-bold mb-2 sm:mb-3 tracking-widest transform rotate-180" style="writing-mode: vertical-rl;">
                        LOGIN
                    </div>
                    <div class="w-0.5 h-6 sm:h-8 lg:h-10 bg-white opacity-70 mx-auto mb-2 sm:mb-3"></div>
                    <div class="text-xs sm:text-sm md:text-base lg:text-lg font-medium tracking-widest transform rotate-180" style="writing-mode: vertical-rl;">
                        ACCESS
                    </div>
                </div>
            </div>
            
            <!-- Top social link -->
            <div class="absolute top-3 sm:top-4 left-0 right-0 flex justify-center">
                <a href="https://www.facebook.com/SPCC.OSAS" target="_blank" rel="noopener noreferrer" class="bg-white bg-opacity-20 backdrop-blur-sm hover:bg-opacity-40 p-1.5 sm:p-2 rounded-full transition-all duration-300 hover:scale-110" aria-label="Facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                    </svg>
                </a>
            </div>
        </div>
        
        <!-- Slideshow navigation - Top left corner -->
        <div class="absolute top-4 sm:top-6 left-4 sm:left-6 flex space-x-1.5 sm:space-x-2 z-30">
            <button 
                v-for="(_, index) in slideshowImages" 
                :key="slideshowImages[index].id"
                @click="activeSlide = index" 
                class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full transition-all duration-300"
                :class="[
                    activeSlide === index 
                        ? 'bg-blue-400 scale-125' 
                        : 'bg-white bg-opacity-40 hover:bg-opacity-70'
                ]"
                :aria-label="`Go to slide ${index + 1}`"
            ></button>
        </div>
        
        <!-- Gradient indicator - Bottom right -->
        <div class="absolute bottom-4 sm:bottom-6 right-4 sm:right-6 w-3 h-3 sm:w-4 sm:h-4 rounded-full transition-all duration-1000 ease-in-out z-30" 
             :class="gradientIndex === 0 ? 'bg-blue-400' : 'bg-green-400'"></div>
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

/* Floating label animations */
.floating-label {
  transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
  transform-origin: left top;
  will-change: transform, color, background-color;
}

.floating-label-active {
  transform: translateY(-1.75rem) translateX(-2rem) scale(0.75) !important;
  color: #60a5fa !important;
  background-color: rgba(17, 24, 39, 0.9) !important;
  padding: 0.125rem 0.25rem !important;
  border-radius: 0.25rem !important;
  backdrop-filter: blur(4px);
}

/* Peer-based animations for even smoother transitions */
.peer:focus ~ .floating-label {
  transform: translateY(-1.75rem) translateX(-2rem) scale(0.75);
  color: #60a5fa;
  background-color: rgba(17, 24, 39, 0.9);
  padding: 0.125rem 0.25rem;
  border-radius: 0.25rem;
  backdrop-filter: blur(4px);
}

.peer:not(:placeholder-shown) ~ .floating-label {
  transform: translateY(-1.75rem) translateX(-2rem) scale(0.75);
  color: #60a5fa;
  background-color: rgba(17, 24, 39, 0.9);
  padding: 0.125rem 0.25rem;
  border-radius: 0.25rem;
  backdrop-filter: blur(4px);
}

/* Responsive adjustments */
@media (min-width: 640px) {
  .floating-label-active {
    transform: translateY(-1.875rem) translateX(-2.5rem) scale(0.75) !important;
  }
  
  .peer:focus ~ .floating-label,
  .peer:not(:placeholder-shown) ~ .floating-label {
    transform: translateY(-1.875rem) translateX(-2.5rem) scale(0.75);
  }
}

/* Slideshow transition - smooth crossfade */
.slideshow-fade-enter-active {
  transition: opacity 2s ease-in-out;
}

.slideshow-fade-leave-active {
  transition: opacity 2s ease-in-out;
}

.slideshow-fade-enter-from {
  opacity: 0;
}

.slideshow-fade-leave-to {
  opacity: 0;
}

.slideshow-fade-enter-active,
.slideshow-fade-leave-active {
  position: absolute;
  width: 100%;
  height: 100%;
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

/* Enhanced focus states for better accessibility */
input:focus-visible, button:focus-visible, a:focus-visible {
  outline: 2px solid #60a5fa;
  outline-offset: 2px;
  border-radius: 0.5rem;
}

/* Error state styling */
input[aria-invalid="true"] {
  border-color: #ef4444 !important;
  box-shadow: 0 0 0 1px #ef4444 !important;
}

/* Success state for valid inputs */
input:valid:not(:placeholder-shown) {
  border-color: #10b981;
}

/* Loading state improvements */
.loading-overlay {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Animation for error messages */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

.error-shake {
  animation: shake 0.5s ease-in-out;
}

/* Fix autofill styling to maintain consistent appearance */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: white !important;
  background-color: transparent !important;
  box-shadow: inset 0 0 20px 20px rgba(255, 255, 255, 0.1) !important;
  transition: background-color 5000s ease-in-out 0s !important;
}

/* For Firefox */
input:-moz-autofill {
  background-color: transparent !important;
  color: white !important;
}

/* Ensure focus ring is circular */
button:focus {
  border-radius: 9999px;
}

/* Use default cursor for all text content - no text selection cursor */
p, span, h1, h2, h3, h4, h5, h6, div, label:not([for]) {
  cursor: default;
  user-select: none;
}

/* Only show text cursor for actual input fields */
input[type="text"], input[type="email"], input[type="password"], textarea {
  cursor: text;
  user-select: text;
}

/* Pointer cursor for interactive elements */
a, button, label[for], input[type="checkbox"], input[type="radio"] {
  cursor: pointer;
}
</style>