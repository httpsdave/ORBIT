<script setup>
import TextInput from '@/Components/TextInput.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const email = ref('');
const isLoading = ref(false);
const message = ref('');
const error = ref('');
const formElement = ref(null);
const activeSlide = ref(0);
const slideInterval = ref(null);
const gradientIndex = ref(0);
const gradientInterval = ref(null);

// Cooldown state
const cooldownSeconds = ref(0);
const cooldownInterval = ref(null);
const lastAttemptTime = ref(null);
const COOLDOWN_DURATION = 30; // 30 seconds cooldown
const showCooldownMessage = ref(false); // Control when to show cooldown UI
const errorDismissTimer = ref(null);

let firebaseAuthModule = null;

const loadFirebaseAuth = async () => {
    // Lazy-load Firebase bits so the main chunk avoids pulling in auth on first paint
    if (firebaseAuthModule) {
        return firebaseAuthModule;
    }

    const [{ auth }, authExports] = await Promise.all([
        import('@/firebase/config'),
        import('firebase/auth'),
    ]);

    firebaseAuthModule = {
        auth,
        sendPasswordResetEmail: authExports.sendPasswordResetEmail,
        createUserWithEmailAndPassword: authExports.createUserWithEmailAndPassword,
        fetchSignInMethodsForEmail: authExports.fetchSignInMethodsForEmail,
    };

    return firebaseAuthModule;
};

const heroImageSizes = '(max-width: 640px) calc(100vw - 5rem), (max-width: 768px) calc(100vw - 6rem), (max-width: 1024px) calc(100vw - 8rem), (max-width: 1280px) calc(100vw - 10rem), calc(100vw - 12rem)';

const slideshowImages = [
    {
        key: 'lspu-9',
        alt: 'Laguna State Polytechnic University campus buildings at dusk',
        width: 1920,
        height: 1080,
        sources: [
            {
                type: 'image/webp',
                src: '/images/optimized/LSPU9.webp',
                srcset: '/images/optimized/LSPU9-mobile.webp 768w, /images/optimized/LSPU9.webp 1920w',
            },
        ],
        fallback: {
            src: '/images/optimized/LSPU9.jpg',
            srcset: '/images/optimized/LSPU9-mobile.jpg 768w, /images/optimized/LSPU9.jpg 1920w',
        },
    },
    {
        key: 'lspu-2',
        alt: 'LSPU campus facade with greenery',
        width: 1920,
        height: 1080,
        sources: [
            {
                type: 'image/webp',
                src: '/images/optimized/LSPU2.webp',
                srcset: '/images/optimized/LSPU2-mobile.webp 768w, /images/optimized/LSPU2.webp 1920w',
            },
        ],
        fallback: {
            src: '/images/optimized/LSPU2.jpg',
            srcset: '/images/optimized/LSPU2-mobile.jpg 768w, /images/optimized/LSPU2.jpg 1920w',
        },
    },
    {
        key: 'lspu-3',
        alt: 'Birds-eye view of the LSPU grounds',
        width: 1920,
        height: 1080,
        sources: [
            {
                type: 'image/webp',
                src: '/images/optimized/LSPU3.webp',
                srcset: '/images/optimized/LSPU3-mobile.webp 768w, /images/optimized/LSPU3.webp 1920w',
            },
        ],
        fallback: {
            src: '/images/optimized/LSPU3.jpg',
            srcset: '/images/optimized/LSPU3-mobile.jpg 768w, /images/optimized/LSPU3.jpg 1920w',
        },
    },
    {
        key: 'lspu-6',
        alt: 'Students walking through the LSPU campus courtyard',
        width: 1920,
        height: 1080,
        sources: [
            {
                type: 'image/webp',
                src: '/images/optimized/LSPU6.webp',
                srcset: '/images/optimized/LSPU6-mobile.webp 768w, /images/optimized/LSPU6.webp 1920w',
            },
        ],
        fallback: {
            src: '/images/optimized/LSPU6.jpg',
            srcset: '/images/optimized/LSPU6-mobile.jpg 768w, /images/optimized/LSPU6.jpg 1920w',
        },
    },
    {
        key: 'lspu-5',
        alt: 'Outdoor view of LSPU academic buildings',
        width: 1920,
        height: 1080,
        sources: [
            {
                type: 'image/webp',
                src: '/images/optimized/LSPU5.webp',
                srcset: '/images/optimized/LSPU5-mobile.webp 768w, /images/optimized/LSPU5.webp 1920w',
            },
        ],
        fallback: {
            src: '/images/optimized/LSPU5.jpg',
            srcset: '/images/optimized/LSPU5-mobile.jpg 768w, /images/optimized/LSPU5.jpg 1920w',
        },
    },
    {
        key: 'lspu-7',
        alt: 'LSPU campus building with landscaped grounds',
        width: 1920,
        height: 1080,
        sources: [
            {
                type: 'image/webp',
                src: '/images/optimized/LSPU7.webp',
                srcset: '/images/optimized/LSPU7-mobile.webp 768w, /images/optimized/LSPU7.webp 1920w',
            },
        ],
        fallback: {
            src: '/images/optimized/LSPU7.jpg',
            srcset: '/images/optimized/LSPU7-mobile.jpg 768w, /images/optimized/LSPU7.jpg 1920w',
        },
    },
];

const heroPreloadImage = slideshowImages[0];
const logoWebpSrcset = '/images/optimized/lspu_logo_better-96.webp 96w, /images/optimized/lspu_logo_better-192.webp 192w';
const logoSizes = '(max-width: 640px) 2.5rem, (max-width: 768px) 3rem, (max-width: 1024px) 4rem, (max-width: 1280px) 5rem, 6rem';

// Clear client-side errors when user starts typing
const clearClientError = (field) => {
    // This function can be expanded later for client-side validation
    if (error.value) {
        error.value = '';
    }
};

const submit = async () => {
    if (isLoading.value) return;
    
    // Check cooldown
    if (cooldownSeconds.value > 0) {
        error.value = `Please wait ${cooldownSeconds.value} seconds before trying again.`;
        return;
    }
    
    // Clear previous messages and timers
    message.value = '';
    error.value = '';
    showCooldownMessage.value = false;
    if (errorDismissTimer.value) {
        clearTimeout(errorDismissTimer.value);
    }
    
    if (!email.value) {
        error.value = 'Please enter your email address.';
        return;
    }
    
    isLoading.value = true;
    
    try {
        const {
            auth,
            sendPasswordResetEmail,
            createUserWithEmailAndPassword,
            fetchSignInMethodsForEmail,
        } = await loadFirebaseAuth();

        // STEP 1: Check if user exists in Laravel database first
        console.log('Checking if user exists in database:', email.value);
        
        const checkResponse = await axios.post('/api/check-email', {
            email: email.value
        });
        
        if (!checkResponse.data.exists) {
            // User doesn't exist in database - show error
            error.value = 'This email address is not registered in our system. Please check your email or contact your administrator.';
            isLoading.value = false;
            
            // Start cooldown even for failed attempts to prevent email enumeration
            startCooldown();
            
            // After 5 seconds, hide error and show cooldown message
            errorDismissTimer.value = setTimeout(() => {
                error.value = '';
                showCooldownMessage.value = true;
            }, 5000);
            
            return;
        }
        
        console.log('User exists in database, proceeding with password reset...');
        
        // STEP 2: User exists in database, now check Firebase
        try {
            const signInMethods = await fetchSignInMethodsForEmail(auth, email.value);
            console.log('Firebase sign-in methods found:', signInMethods);
            
            if (signInMethods.length === 0) {
                // User exists in database but not in Firebase - create Firebase account
                console.log('Creating Firebase account for existing database user...');
                const tempPassword = Math.random().toString(36).slice(-12) + 'A1!';
                await createUserWithEmailAndPassword(auth, email.value, tempPassword);
                console.log('Firebase account created');
            }
            
            // STEP 3: Send password reset email
            console.log('Sending password reset email...');
            await sendPasswordResetEmail(auth, email.value, {
                url: window.location.origin + '/reset-password',
                handleCodeInApp: false
            });
            
            console.log('Password reset email sent successfully');
            message.value = 'Password reset email sent! Please check your inbox (including spam folder) and follow the instructions to reset your password.';
            
            // Start cooldown after successful attempt
            startCooldown();
            
        } catch (firebaseError) {
            console.error('Firebase error:', firebaseError);
            
            // Handle Firebase-specific errors
            if (firebaseError.code === 'auth/email-already-in-use') {
                // User exists in Firebase, send reset email
                try {
                    await sendPasswordResetEmail(auth, email.value, {
                        url: window.location.origin + '/reset-password',
                        handleCodeInApp: false
                    });
                    message.value = 'Password reset email sent! Please check your inbox (including spam folder) and follow the instructions to reset your password.';
                    startCooldown();
                } catch (resetError) {
                    throw resetError;
                }
            } else if (firebaseError.code === 'auth/invalid-email') {
                error.value = 'Please enter a valid email address.';
            } else if (firebaseError.code === 'auth/too-many-requests') {
                error.value = 'Too many requests. Please try again in a few minutes.';
                // Start longer cooldown for rate limit
                startCooldown(300); // 5 minutes
            } else {
                throw firebaseError;
            }
        }
        
    } catch (err) {
        console.error('Error during password reset:', err);
        
        // Handle API/Network errors
        if (err.response) {
            // Server responded with error
            if (err.response.status === 404) {
                error.value = 'This email address is not registered in our system.';
            } else if (err.response.status === 429) {
                // Too many requests from server
                error.value = 'Too many attempts. Please try again later.';
                startCooldown(300); // 5 minutes
            } else {
                error.value = 'An error occurred. Please try again later.';
            }
        } else if (err.code && err.code.startsWith('auth/')) {
            // Firebase error not caught above
            error.value = 'Unable to send password reset email. Please try again later.';
        } else {
            // Network or other error
            error.value = 'Network error. Please check your connection and try again.';
        }
    } finally {
        isLoading.value = false;
    }
};

const startCooldown = (duration = COOLDOWN_DURATION) => {
    // Clear any existing cooldown
    if (cooldownInterval.value) {
        clearInterval(cooldownInterval.value);
    }
    
    // Set cooldown duration
    cooldownSeconds.value = duration;
    lastAttemptTime.value = Date.now();
    
    // Store in localStorage to persist across page reloads
    localStorage.setItem('forgotPasswordCooldown', JSON.stringify({
        expiresAt: Date.now() + (duration * 1000),
        email: email.value
    }));
    
    // Start countdown
    cooldownInterval.value = setInterval(() => {
        cooldownSeconds.value--;
        
        if (cooldownSeconds.value <= 0) {
            clearInterval(cooldownInterval.value);
            cooldownInterval.value = null;
            showCooldownMessage.value = false;
            localStorage.removeItem('forgotPasswordCooldown');
        }
    }, 1000);
};

const checkExistingCooldown = () => {
    const storedCooldown = localStorage.getItem('forgotPasswordCooldown');
    
    if (storedCooldown) {
        try {
            const { expiresAt, email: storedEmail } = JSON.parse(storedCooldown);
            const now = Date.now();
            
            if (expiresAt > now) {
                // Cooldown still active
                const remainingSeconds = Math.ceil((expiresAt - now) / 1000);
                email.value = storedEmail || '';
                startCooldown(remainingSeconds);
                // Show cooldown message immediately if reloading during cooldown
                showCooldownMessage.value = true;
            } else {
                // Cooldown expired, clean up
                localStorage.removeItem('forgotPasswordCooldown');
            }
        } catch (e) {
            // Invalid data, clean up
            localStorage.removeItem('forgotPasswordCooldown');
        }
    }
};

const startSlideshow = () => {
    if (slideInterval.value) {
        return;
    }
    slideInterval.value = setInterval(() => {
        activeSlide.value = (activeSlide.value + 1) % slideshowImages.length;
    }, 10000);
};

const startGradientAnimation = () => {
    if (gradientInterval.value) {
        return;
    }
    gradientInterval.value = setInterval(() => {
        gradientIndex.value = (gradientIndex.value + 1) % 2;
    }, 30000); // 30 seconds
};

onMounted(() => {
    // Fade in animation on page load
    if (formElement.value) {
        formElement.value.classList.add('opacity-100');
    }
    
    // Check for existing cooldown
    checkExistingCooldown();
    
    // Start slideshow
    startSlideshow();
    
    // Start gradient animation
    startGradientAnimation();
});

onBeforeUnmount(() => {
    // Clear slideshow interval when component is unmounted
    if (slideInterval.value) {
        clearInterval(slideInterval.value);
        slideInterval.value = null;
    }
    
    // Clear gradient interval when component is unmounted
    if (gradientInterval.value) {
        clearInterval(gradientInterval.value);
        gradientInterval.value = null;
    }
    
    // Clear cooldown interval
    if (cooldownInterval.value) {
        clearInterval(cooldownInterval.value);
        cooldownInterval.value = null;
    }
    
    // Clear error dismiss timer
    if (errorDismissTimer.value) {
        clearTimeout(errorDismissTimer.value);
        errorDismissTimer.value = null;
    }
});
</script>

<template>
    <Head title="Forgot Password | LSPU ORBIT">
        <!-- Preload primary hero image using responsive metadata for faster LCP -->
        <link
            rel="preload"
            as="image"
            type="image/webp"
            :href="heroPreloadImage.sources[0].src"
            :imagesrcset="heroPreloadImage.sources[0].srcset"
            :imagesizes="heroImageSizes"
            fetchpriority="high"
        />
        <link
            rel="preload"
            as="image"
            :href="heroPreloadImage.fallback.src"
            :imagesrcset="heroPreloadImage.fallback.srcset"
            :imagesizes="heroImageSizes"
            fetchpriority="high"
        />
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
                    <picture>
                        <source :srcset="logoWebpSrcset" type="image/webp" :sizes="logoSizes" />
                        <img
                            src="/images/lspu_logo_better.png"
                            :sizes="logoSizes"
                            width="96"
                            height="96"
                            alt="LSPU Logo"
                            class="w-10 h-10 sm:w-12 sm:h-12 md:w-16 md:h-16 lg:w-20 lg:h-20 xl:w-24 xl:h-24 object-cover filter drop-shadow-lg"
                            loading="lazy"
                            decoding="async"
                        />
                    </picture>
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
        <div class="absolute inset-0 left-20 sm:left-24 md:left-32 lg:left-40 xl:left-48 z-0 bg-gray-900">
            <transition-group name="slideshow-fade">
                <div 
                    v-for="(image, index) in slideshowImages" 
                    :key="image.key" 
                    v-show="activeSlide === index"
                    class="absolute inset-0"
                >
                    <picture class="w-full h-full">
                        <source
                            v-for="(source, sourceIndex) in image.sources"
                            :key="`${image.key}-source-${sourceIndex}`"
                            :type="source.type"
                            :srcset="source.srcset"
                            :sizes="heroImageSizes"
                        />
                        <img
                            :src="image.fallback.src"
                            :srcset="image.fallback.srcset"
                            :sizes="heroImageSizes"
                            :alt="image.alt"
                            class="w-full h-full object-cover object-center filter brightness-[0.3] contrast-[1.2]"
                            :loading="index === 0 ? 'eager' : 'lazy'"
                            :fetchpriority="index === 0 ? 'high' : 'low'"
                            :width="image.width"
                            :height="image.height"
                            decoding="async"
                        />
                    </picture>
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
                        Forgot Your
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-400">
                            Password?
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
                        No worries! Enter your email address and we'll send you instructions to reset your password.
                    </p>
                </div>
                
                <!-- Status message -->
                <div v-if="status || message" class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-500 bg-opacity-20 border-l-4 border-green-500 text-green-400 text-xs sm:text-sm font-medium animate-fadeIn backdrop-blur-sm rounded-r-lg">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3 flex-shrink-0">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22,4 12,14.01 9,11.01"></polyline>
                        </svg>
                        {{ status || message }}
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
                
                <!-- Cooldown indicator - only show when error is dismissed -->
                <div v-else-if="cooldownSeconds > 0 && showCooldownMessage" class="mb-4 sm:mb-6 p-3 sm:p-4 bg-yellow-500 bg-opacity-20 border-l-4 border-yellow-500 text-yellow-400 text-xs sm:text-sm font-medium animate-fadeIn backdrop-blur-sm rounded-r-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3 flex-shrink-0">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Please wait {{ cooldownSeconds }} second{{ cooldownSeconds !== 1 ? 's' : '' }} before trying again</span>
                        </div>
                        <div class="ml-3 flex-shrink-0">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 relative">
                                <!-- Background circle -->
                                <svg class="transform -rotate-90 w-full h-full" viewBox="0 0 40 40">
                                    <circle cx="20" cy="20" r="16" stroke="currentColor" stroke-width="2" fill="none" class="opacity-20" />
                                    <!-- Progress circle -->
                                    <circle 
                                        cx="20" cy="20" r="16" 
                                        stroke="currentColor" 
                                        stroke-width="2" 
                                        fill="none" 
                                        class="transition-all duration-1000 ease-linear"
                                        stroke-linecap="round"
                                        :stroke-dasharray="100.53"
                                        :stroke-dashoffset="100.53 * (cooldownSeconds / COOLDOWN_DURATION)"
                                    />
                                </svg>
                                <!-- Countdown number in center -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-sm font-bold tabular-nums transition-all duration-300">{{ cooldownSeconds }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-4 sm:space-y-6" novalidate>
                    <div>
                        <div class="relative group">
                            <TextInput
                                id="email"
                                type="email"
                                class="peer pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 text-sm sm:text-base rounded-lg w-full border-0 focus:border-orange-400 focus:ring-2 focus:ring-orange-400 focus:ring-opacity-50 transition-all duration-300 bg-white bg-opacity-10 backdrop-blur-sm text-white placeholder-transparent"
                                v-model="email"
                                placeholder=" "
                                required
                                autofocus
                                autocomplete="username"
                                aria-label="Email address"
                                @input="clearClientError('email')"
                            />
                            <label 
                                for="email"
                                class="floating-label absolute left-10 sm:left-12 top-3 sm:top-4 text-xs sm:text-sm text-gray-300 pointer-events-none transition-all duration-300"
                                :class="[ 
                                    email ? 'floating-label-active' : '' 
                                ]"
                            >
                                Email Address
                            </label>
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-focus-within:text-orange-400 transition-colors duration-300 text-gray-500">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="space-y-3 sm:space-y-4">
                        <button
                            type="submit"
                            class="w-full text-white font-semibold py-3 sm:py-4 px-4 sm:px-6 rounded-lg transition-all duration-300 flex items-center justify-center relative overflow-hidden group shadow-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-opacity-50 transform hover:scale-105 disabled:transform-none disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="[
                                isLoading || cooldownSeconds > 0
                                    ? 'bg-gradient-to-r from-gray-500 to-gray-600' 
                                    : 'bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600'
                            ]"
                            :disabled="isLoading || cooldownSeconds > 0"
                            aria-label="Send Reset Link"
                        >
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            <span class="flex items-center relative z-10 text-sm sm:text-base">
                                <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 sm:mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else-if="cooldownSeconds > 0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 sm:mr-3">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22,2 15,22 11,13 2,9"></polygon>
                                </svg>
                                {{ isLoading ? 'Sending...' : cooldownSeconds > 0 ? `Wait ${cooldownSeconds}s` : 'Send Reset Instructions' }}
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
                v-for="(slide, index) in slideshowImages" 
                :key="slide.key"
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

/* Floating label animations (match Logz.vue) */
.floating-label {
    transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    transform-origin: left top;
    will-change: transform, color, background-color;
}

.floating-label-active {
    transform: translateY(-1.5rem) translateX(-1.6rem) scale(0.78) !important;
    color: #fb923c !important; /* orange-400 */
    background-color: rgba(17, 24, 39, 0.9) !important;
    padding: 0.125rem 0.25rem !important;
    border-radius: 0.25rem !important;
    backdrop-filter: blur(4px);
}

/* Peer-based animations for smoother transitions */
.peer:focus ~ .floating-label {
    transform: translateY(-1.5rem) translateX(-1.6rem) scale(0.78);
    color: #fb923c;
    background-color: rgba(17, 24, 39, 0.9);
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    backdrop-filter: blur(4px);
}

.peer:not(:placeholder-shown) ~ .floating-label {
    transform: translateY(-1.5rem) translateX(-1.6rem) scale(0.78);
    color: #fb923c;
    background-color: rgba(17, 24, 39, 0.9);
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    backdrop-filter: blur(4px);
}

@media (min-width: 640px) {
        .floating-label-active {
            transform: translateY(-1.65rem) translateX(-2rem) scale(0.78) !important;
        }
        .peer:focus ~ .floating-label,
        .peer:not(:placeholder-shown) ~ .floating-label {
            transform: translateY(-1.65rem) translateX(-2rem) scale(0.78);
        }
}

/* Tabular numbers for consistent countdown display */
.tabular-nums {
    font-variant-numeric: tabular-nums;
}

</style>