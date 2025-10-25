<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    isAdmin: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close']);

const currentStep = ref(0);
const isCompleting = ref(false);
const isAnimating = ref(false);

// Tutorial steps for regular users
const userSteps = [
    {
        title: 'Welcome to ORBIT! 🎉',
        description: 'ORBIT (Organized Records for Better Institutional Tracking) simplifies document submission and management for student organizations.',
        iconClass: 'text-blue-500',
        iconPath: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        highlight: null
    },
    {
        title: 'Dashboard Overview 📊',
        description: 'Your dashboard shows your applications, events, and recent activity at a glance. All your important information in one place.',
        iconClass: 'text-green-500',
        iconPath: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        highlight: 'dashboard'
    },
    {
        title: 'Submit Applications 📝',
        description: 'Create and submit organization applications through the Applications menu. Track their status and receive notifications on updates.',
        iconClass: 'text-purple-500',
        iconPath: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        highlight: 'applications'
    },
    {
        title: 'View Events 📅',
        description: 'View your organization\'s events through the Calendar. Stay updated on important dates and upcoming activities.',
        iconClass: 'text-orange-500',
        iconPath: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        highlight: 'calendar'
    },
    {
        title: 'Check Notifications 🔔',
        description: 'Stay informed with real-time notifications about application updates, events, and important announcements.',
        iconClass: 'text-yellow-500',
        iconPath: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        highlight: 'notifications'
    },
    {
        title: 'You\'re All Set! ✨',
        description: 'You\'re ready to start using ORBIT! Explore the system at your own pace. You can always access help from the sidebar.',
        iconClass: 'text-green-500',
        iconPath: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        highlight: null
    }
];

// Tutorial steps for admins
const adminSteps = [
    {
        title: 'Welcome, Administrator! 👨‍💼',
        description: 'As an admin, you have full control over ORBIT. Let\'s walk through your administrative capabilities.',
        iconClass: 'text-blue-500',
        iconPath: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        highlight: null
    },
    {
        title: 'Admin Dashboard 📊',
        description: 'Your dashboard provides an overview of all organizations, applications, events, and system statistics. Monitor everything at a glance.',
        iconClass: 'text-green-500',
        iconPath: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        highlight: 'dashboard'
    },
    {
        title: 'Manage Applications 📋',
        description: 'Review, approve, or reject student organization applications. Track submission status and provide feedback.',
        iconClass: 'text-purple-500',
        iconPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        highlight: 'applications'
    },
    {
        title: 'User Management 👥',
        description: 'Manage user accounts, roles, and permissions. Create new users and assign them to organizations.',
        iconClass: 'text-indigo-500',
        iconPath: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        highlight: 'users'
    },
    {
        title: 'Colleges & Organizations 🏛️',
        description: 'Manage colleges, student organizations, and their hierarchical structure. Organize your institution\'s structure efficiently.',
        iconClass: 'text-orange-500',
        iconPath: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        highlight: 'colleges'
    },
    {
        title: 'You\'re Ready! 🚀',
        description: 'You now have the knowledge to manage ORBIT effectively. Explore the admin features and customize the system as needed.',
        iconClass: 'text-green-500',
        iconPath: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        highlight: null
    }
];

const steps = computed(() => props.isAdmin ? adminSteps : userSteps);
const totalSteps = computed(() => steps.value.length);
const progress = computed(() => ((currentStep.value + 1) / totalSteps.value) * 100);
const currentStepData = computed(() => steps.value[currentStep.value]);

const nextStep = () => {
    if (currentStep.value < totalSteps.value - 1 && !isAnimating.value) {
        isAnimating.value = true;
        currentStep.value++;
        setTimeout(() => {
            isAnimating.value = false;
        }, 300);
    }
};

const prevStep = () => {
    if (currentStep.value > 0 && !isAnimating.value) {
        isAnimating.value = true;
        currentStep.value--;
        setTimeout(() => {
            isAnimating.value = false;
        }, 300);
    }
};

const completeTutorial = async () => {
    isCompleting.value = true;
    
    try {
        await axios.post('/api/tutorial/complete');
        emit('close');
    } catch (error) {
        console.error('Failed to complete tutorial:', error);
        // Still close the modal even if the API call fails
        emit('close');
    } finally {
        isCompleting.value = false;
    }
};

const skipTutorial = () => {
    completeTutorial();
};

// Keyboard navigation
const handleKeydown = (e) => {
    if (!props.show) return;
    
    if (e.key === 'ArrowRight' || e.key === 'Enter') {
        if (currentStep.value === totalSteps.value - 1) {
            completeTutorial();
        } else {
            nextStep();
        }
    } else if (e.key === 'ArrowLeft') {
        prevStep();
    } else if (e.key === 'Escape') {
        skipTutorial();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="tutorial-title" role="dialog" aria-modal="true">
            <!-- Backdrop - Removed backdrop-blur for better performance -->
            <div class="fixed inset-0 bg-gray-900/80 transition-opacity"></div>

            <!-- Modal -->
            <div class="flex min-h-full items-center justify-center p-4">
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="show" class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl w-full max-w-2xl">
                        <!-- Progress Bar -->
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gray-200 dark:bg-gray-700 overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-blue-500 to-green-500 transition-all duration-300 ease-out"
                                :style="{ width: progress + '%' }"
                            ></div>
                        </div>

                        <!-- Skip Button -->
                        <button
                            @click="skipTutorial"
                            type="button"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-150 z-10 group"
                            :disabled="isCompleting"
                        >
                            <span class="sr-only">Skip tutorial</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        <!-- Content -->
                        <div class="px-6 py-8 sm:px-10 sm:py-12">
                            <!-- Step Indicator -->
                            <div class="text-center mb-6">
                                <span class="inline-block px-3 py-1 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                                    Step {{ currentStep + 1 }} of {{ totalSteps }}
                                </span>
                            </div>

                            <!-- Icon - Optimized SVG rendering -->
                            <div class="mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" :class="currentStepData.iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="currentStepData.iconPath" />
                                </svg>
                            </div>

                            <!-- Title -->
                            <h3 id="tutorial-title" class="text-2xl sm:text-3xl font-bold text-center text-gray-900 dark:text-white mb-4 select-none">
                                {{ currentStepData.title }}
                            </h3>

                            <!-- Description -->
                            <p class="text-base sm:text-lg text-center text-gray-600 dark:text-gray-300 leading-relaxed mb-8 select-none">
                                {{ currentStepData.description }}
                            </p>

                            <!-- Navigation Buttons -->
                            <div class="flex items-center justify-between gap-4">
                                <button
                                    @click="prevStep"
                                    type="button"
                                    :disabled="currentStep === 0 || isAnimating"
                                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150 cursor-pointer"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                    Previous
                                </button>

                                <!-- Step Dots -->
                                <div class="flex items-center gap-2">
                                    <button
                                        v-for="(step, index) in steps"
                                        :key="index"
                                        @click="!isAnimating && (currentStep = index)"
                                        type="button"
                                        class="w-2.5 h-2.5 rounded-full transition-all duration-200 cursor-pointer"
                                        :class="[
                                            index === currentStep 
                                                ? 'bg-blue-500 w-8' 
                                                : index < currentStep 
                                                    ? 'bg-green-500' 
                                                    : 'bg-gray-300 dark:bg-gray-600'
                                        ]"
                                        :aria-label="`Go to step ${index + 1}`"
                                        :disabled="isAnimating"
                                    ></button>
                                </div>

                                <button
                                    v-if="currentStep < totalSteps - 1"
                                    @click="nextStep"
                                    type="button"
                                    :disabled="isAnimating"
                                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-green-500 rounded-lg hover:from-blue-600 hover:to-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                >
                                    Next
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>

                                <button
                                    v-else
                                    @click="completeTutorial"
                                    type="button"
                                    :disabled="isCompleting"
                                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-green-500 to-blue-500 rounded-lg hover:from-green-600 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                >
                                    <span v-if="isCompleting" class="inline-flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Getting Started...
                                    </span>
                                    <span v-else class="inline-flex items-center">
                                        Get Started
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </span>
                                </button>
                            </div>

                            <!-- Keyboard Hints -->
                            <div class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400 select-none">
                                <span class="inline-block mx-1">← → Navigate</span>
                                <span class="inline-block mx-1">• ESC Skip</span>
                                <span class="inline-block mx-1">• ENTER Next</span>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
/* Use default cursor for all text content */
p, span, h1, h2, h3, h4, h5, h6, div {
  cursor: default;
  user-select: none;
}

/* Pointer cursor for interactive elements */
button {
  cursor: pointer;
}

/* Performance optimizations - GPU acceleration */
.fixed, .absolute, .relative {
  transform: translateZ(0);
}

/* Disable expensive filters */
.rounded-2xl, .rounded-lg {
  will-change: transform;
}

/* Optimize transitions - only animate necessary properties */
button:not(:disabled) {
  transition-property: background-color, border-color, color;
  transition-duration: 150ms;
}

.bg-gradient-to-r {
  transition-property: opacity;
  transition-duration: 150ms;
}

/* Remove smooth scroll lag */
.overflow-y-auto {
  -webkit-overflow-scrolling: touch;
}

/* Reduce paint on hover */
svg {
  pointer-events: none;
}
</style>
