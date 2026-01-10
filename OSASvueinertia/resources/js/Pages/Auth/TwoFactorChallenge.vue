<template>
    <Head title="Two-Factor Authentication | LSPU ORBIT">
        <meta name="description" content="Two-Factor Authentication verification for LSPU ORBIT. Enter your authentication code to securely access your account." />
        
        <!-- Font optimization -->
        <link rel="dns-prefetch" href="https://fonts.bunny.net" />
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin />
        
        <!-- Preload the first slideshow image -->
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
            :class="gradientIndex === 0 ? 'bg-gradient-to-b from-blue-500 via-green-500 to-blue-500' : 'bg-gradient-to-b from-green-500 via-blue-500 to-green-500'"
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
                        2FA
                    </div>
                    <div class="w-0.5 h-6 sm:h-8 lg:h-10 bg-white opacity-70 mx-auto mb-2 sm:mb-3"></div>
                    <div class="text-xs sm:text-sm md:text-base lg:text-lg font-medium tracking-widest transform rotate-180" style="writing-mode: vertical-rl;">
                        LOGIN
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
        
        <!-- Background slideshow for right side -->
        <div class="absolute inset-0 left-20 sm:left-24 md:left-32 lg:left-40 xl:left-48 z-0 bg-gray-900">
            <transition-group name="slideshow-fade">
                <div
                    v-for="(image, index) in slideshowImages"
                    v-show="index === activeSlide"
                    :key="image.key"
                    class="absolute inset-0 slideshow-image"
                    :style="{
                        backgroundImage: `url('${image.fallback.src}')`,
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                        filter: 'brightness(0.3) contrast(1.2)'
                    }"
                ></div>
            </transition-group>
        </div>
        
        <!-- Right side - Main content area -->
        <div class="flex-1 relative z-20 flex items-center justify-center px-3 sm:px-6 md:px-8 lg:px-12 xl:px-16 py-4">
            <div 
                ref="formElement"
                class="w-full max-w-[280px] sm:max-w-xs md:max-w-sm lg:max-w-md opacity-0 transition-opacity duration-1000 ease-out"
            >
                <div class="bg-gray-800/90 backdrop-blur-md shadow-2xl rounded-xl sm:rounded-2xl overflow-hidden border border-gray-700">
                    <!-- Header -->
                    <div class="px-3 sm:px-6 py-4 sm:py-6 md:py-8 text-center border-b border-gray-700">
                        <div class="flex justify-center mb-2 sm:mb-3 md:mb-4">
                            <div class="bg-blue-500/20 rounded-full p-2 sm:p-3 md:p-4 backdrop-blur-sm transition-all duration-500"
                                 :class="{ 'bg-green-500/20': isUnlocked }">
                                <!-- Locked Icon -->
                                <svg v-if="!isUnlocked" class="h-8 w-8 sm:h-10 sm:w-10 md:h-12 md:w-12 text-blue-400 transition-all duration-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <!-- Unlocked Icon -->
                                <svg v-else class="h-8 w-8 sm:h-10 sm:w-10 md:h-12 md:w-12 text-green-400 transition-all duration-500 animate-unlock" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <h2 class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-bold text-white mb-1 sm:mb-2">Two-Factor Authentication</h2>
                        <p class="text-xs sm:text-sm md:text-base text-gray-300 px-2">
                            {{ useRecoveryCode ? 'Enter one of your recovery codes' : 'Enter the code from your authenticator app' }}
                        </p>
                    </div>

                    <!-- Form -->
                    <div class="px-3 sm:px-6 py-4 sm:py-6 md:py-8">
                        <form @submit.prevent="submit">
                            <!-- TOTP Code Input -->
                            <div v-if="!useRecoveryCode" class="space-y-3 sm:space-y-4">
                                <div>
                                    <label class="block text-xs sm:text-sm font-medium text-gray-300 mb-2 sm:mb-3 text-center">
                                        Authentication Code
                                    </label>
                                    
                                    <!-- Individual digit inputs -->
                                    <div class="flex justify-center gap-1.5 sm:gap-2 md:gap-3">
                                        <input
                                            v-for="(digit, index) in 6"
                                            :key="index"
                                            :ref="el => digitRefs[index] = el"
                                            v-model="digits[index]"
                                            type="text"
                                            inputmode="numeric"
                                            pattern="[0-9]"
                                            maxlength="1"
                                            autocomplete="off"
                                            @input="handleDigitInput(index, $event)"
                                            @keydown="handleKeyDown(index, $event)"
                                            @paste="handlePaste"
                                            class="w-9 h-11 sm:w-11 sm:h-13 md:w-12 md:h-14 lg:w-14 lg:h-16 border-2 border-gray-600 bg-gray-900/50 text-white rounded-md sm:rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-xl sm:text-2xl md:text-2xl lg:text-3xl font-mono backdrop-blur-sm transition-all duration-300 font-bold leading-none flex items-center justify-center p-0"
                                            :class="{
                                                'border-red-500 focus:ring-red-500 animate-shake': form.errors.code,
                                                'border-blue-500': digits[index] && !form.errors.code
                                            }"
                                        />
                                    </div>
                                    
                                    <p v-if="form.errors.code" class="mt-3 text-sm text-red-400 text-center animate-fadeIn">
                                        {{ form.errors.code }}
                                    </p>
                                </div>

                                <p class="text-xs text-gray-400 text-center">
                                    Enter the 6-digit code from your authenticator app
                                </p>
                            </div>

                            <!-- Recovery Code Input -->
                            <div v-else class="space-y-4">
                                <div>
                                    <label for="recovery_code" class="block text-sm font-medium text-gray-300 mb-2">
                                        Recovery Code
                                    </label>
                                    <input
                                        id="recovery_code"
                                        v-model="form.code"
                                        type="text"
                                        placeholder="XXXXX-XXXXX"
                                        autofocus
                                        autocomplete="one-time-code"
                                        class="block w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-600 bg-gray-900/50 text-white rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center text-sm sm:text-base font-mono backdrop-blur-sm transition-all duration-300"
                                        :class="{ 'border-red-500 focus:ring-red-500': form.errors.code }"
                                    />
                                    <p v-if="form.errors.code" class="mt-2 text-sm text-red-400">
                                        {{ form.errors.code }}
                                    </p>
                                </div>

                                <div class="bg-yellow-900/30 border border-yellow-700/50 rounded-lg p-3 backdrop-blur-sm">
                                    <p class="text-xs text-yellow-200">
                                        <strong>Warning:</strong> Each recovery code can only be used once. Make sure to generate new codes after using this one.
                                    </p>
                                </div>
                                
                                <!-- Submit Button for Recovery Code -->
                                <div>
                                    <button
                                        type="submit"
                                        class="w-full inline-flex justify-center items-center px-4 py-2.5 sm:py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
                                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                        :disabled="form.processing"
                                    >
                                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ form.processing ? 'Verifying...' : 'Verify Recovery Code' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Toggle Recovery Code -->
                            <div class="mt-3 sm:mt-4 text-center">
                                <button
                                    type="button"
                                    @click="toggleRecoveryCode"
                                    class="text-xs sm:text-sm text-blue-400 hover:text-blue-300 font-medium transition-colors duration-200"
                                >
                                    {{ useRecoveryCode ? 'Use authenticator code instead' : 'Use a recovery code' }}
                                </button>
                            </div>

                            <!-- Back to Login -->
                            <div class="mt-4 sm:mt-6 text-center">
                                <Link
                                    :href="route('login')"
                                    class="inline-flex items-center text-xs sm:text-sm text-gray-400 hover:text-gray-200 transition-colors duration-200"
                                >
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Back to login
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="mt-3 sm:mt-4 md:mt-6 text-center px-2">
                    <p class="text-[10px] sm:text-xs text-gray-400">
                        Having trouble? Contact your system administrator for help.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Slideshow navigation - Top right corner -->
        <div class="absolute top-4 sm:top-6 right-4 sm:right-6 flex space-x-1.5 sm:space-x-2 z-30">
            <button
                v-for="(image, index) in slideshowImages"
                :key="image.key"
                @click="activeSlide = index"
                :class="[
                    'w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full transition-all duration-300',
                    index === activeSlide ? 'bg-white scale-125' : 'bg-white/40 hover:bg-white/60'
                ]"
                :aria-label="`Go to slide ${index + 1}`"
            ></button>
        </div>
        
        <!-- Gradient indicator - Bottom left for mobile -->
        <div class="absolute bottom-4 left-4 w-3 h-3 sm:w-4 sm:h-4 rounded-full transition-all duration-1000 ease-in-out z-30" 
             :class="gradientIndex === 0 ? 'bg-blue-400' : 'bg-green-400'">
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user_id: {
        type: Number,
        required: true,
    },
    remember: {
        type: Boolean,
        default: false,
    },
});

const useRecoveryCode = ref(false);
const formElement = ref(null);
const activeSlide = ref(0);
const slideInterval = ref(null);
const gradientIndex = ref(0);
const gradientInterval = ref(null);
const isUnlocked = ref(false);

// Individual digit refs and values
const digits = ref(['', '', '', '', '', '']);
const digitRefs = ref([]);

const form = useForm({
    code: '',
    user_id: props.user_id,
    remember: props.remember,
});

// Slideshow images configuration
const slideshowImages = [
    {
        key: 'lspu-9',
        alt: 'Laguna State Polytechnic University campus buildings at dusk',
        width: 1920,
        height: 1080,
        sources: [
            {
                type: 'image/webp',
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
const heroImageSizes = '(max-width: 640px) 100vw, (max-width: 768px) 100vw, (max-width: 1024px) calc(100vw - 10rem), calc(100vw - 12rem)';
const logoWebpSrcset = '/images/optimized/lspu_logo_better-96.webp 96w, /images/optimized/lspu_logo_better-192.webp 192w';
const logoSizes = '(max-width: 640px) 4rem, (max-width: 768px) 5rem, (max-width: 1024px) 6rem, (max-width: 1280px) 7rem, 8rem';

// Handle individual digit input
const handleDigitInput = (index, event) => {
    const value = event.target.value;
    
    // Only allow numeric values
    if (value && !/^\d$/.test(value)) {
        digits.value[index] = '';
        return;
    }
    
    // Update the digit
    digits.value[index] = value;
    
    // Move to next input if value is entered
    if (value && index < 5) {
        nextTick(() => {
            digitRefs.value[index + 1]?.focus();
        });
    }
    
    // Auto-submit when all 6 digits are filled
    if (value && index === 5 && digits.value.every(d => d !== '')) {
        const code = digits.value.join('');
        form.code = code;
        nextTick(() => {
            submit();
        });
    }
};

// Handle keydown events (backspace, arrow keys)
const handleKeyDown = (index, event) => {
    if (event.key === 'Backspace') {
        if (!digits.value[index] && index > 0) {
            // Move to previous input if current is empty
            nextTick(() => {
                digitRefs.value[index - 1]?.focus();
            });
        } else {
            digits.value[index] = '';
        }
    } else if (event.key === 'ArrowLeft' && index > 0) {
        event.preventDefault();
        digitRefs.value[index - 1]?.focus();
    } else if (event.key === 'ArrowRight' && index < 5) {
        event.preventDefault();
        digitRefs.value[index + 1]?.focus();
    }
};

// Handle paste event
const handlePaste = (event) => {
    event.preventDefault();
    const pastedData = event.clipboardData.getData('text').trim();
    
    // Only accept 6-digit numeric codes
    if (/^\d{6}$/.test(pastedData)) {
        const pastedDigits = pastedData.split('');
        pastedDigits.forEach((digit, index) => {
            if (index < 6) {
                digits.value[index] = digit;
            }
        });
        
        // Focus last input and auto-submit
        nextTick(() => {
            digitRefs.value[5]?.focus();
            form.code = pastedData;
            submit();
        });
    }
};

// Watch for errors and clear digits
watch(() => form.errors.code, (newError) => {
    if (newError) {
        // Clear all digits on error
        setTimeout(() => {
            digits.value = ['', '', '', '', '', ''];
            form.code = '';
            nextTick(() => {
                digitRefs.value[0]?.focus();
            });
        }, 1000);
    }
});

const toggleRecoveryCode = () => {
    useRecoveryCode.value = !useRecoveryCode.value;
    form.code = '';
    form.errors = {};
    digits.value = ['', '', '', '', '', ''];
};

const submit = () => {
    form.post(route('two-factor.verify'), {
        preserveScroll: true,
        onSuccess: () => {
            // Show unlocked icon only when verification succeeds
            isUnlocked.value = true;
        },
        onError: () => {
            // Keep locked on error
            isUnlocked.value = false;
            form.code = '';
        },
    });
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
    }, 30000);
};

onMounted(() => {
    // Fade in animation
    if (formElement.value) {
        formElement.value.classList.add('opacity-100');
    }
    
    // Focus first digit input
    nextTick(() => {
        if (!useRecoveryCode.value) {
            digitRefs.value[0]?.focus();
        }
    });
    
    // Start animations
    startSlideshow();
    startGradientAnimation();
});

onBeforeUnmount(() => {
    if (slideInterval.value) {
        clearInterval(slideInterval.value);
        slideInterval.value = null;
    }
    
    if (gradientInterval.value) {
        clearInterval(gradientInterval.value);
        gradientInterval.value = null;
    }
});
</script>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.animate-fadeIn {
    animation: fadeIn 0.6s ease-out forwards;
}

.animate-shake {
    animation: shake 0.5s ease-in-out;
}

@keyframes unlock {
    0% { transform: scale(1) rotate(0deg); }
    25% { transform: scale(1.1) rotate(-5deg); }
    50% { transform: scale(1.2) rotate(5deg); }
    75% { transform: scale(1.1) rotate(-5deg); }
    100% { transform: scale(1) rotate(0deg); }
}

.animate-unlock {
    animation: unlock 0.6s ease-in-out;
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

/* Smooth transitions */
* {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

/* Enhanced focus states */
input:focus-visible, button:focus-visible, a:focus-visible {
    outline: 2px solid #60a5fa;
    outline-offset: 2px;
    border-radius: 0.5rem;
}
</style>
