<template>
    <AuthenticatedLayout :user="auth.user">
        <Head title="Colleges" />

        <div class="py-8 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Animated colored banner -->
                <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
                    <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
                    <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
                    <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
                    <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
                </div>

                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 flex items-center">
                        <span class="mr-2">Colleges</span>
                        <span class="text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 py-1 px-2 rounded-full">
                            {{ colleges.length }} total
                        </span>
                    </h1>
                </div>

                <div v-if="colleges.length === 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-8 text-center">
                    <div class="text-gray-500 dark:text-gray-400">No colleges found</div>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="college in colleges"
                        :key="college.id"
                        class="relative bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg dark:hover:shadow-gray-900/50 transition-all duration-300 flex flex-col"
                    >
                        <!-- Colored top border, random color per college -->
                        <div class="h-1 w-full rounded-t-xl" :class="{
                            'bg-blue-500': college.id % 4 === 0,
                            'bg-green-500': college.id % 4 === 1,
                            'bg-yellow-500': college.id % 4 === 2,
                            'bg-red-500': college.id % 4 === 3,
                        }"></div>
                        
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-start justify-between">
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 leading-tight flex-1 min-w-0 mr-2">
                                    <Link
                                        :href="route('colleges.show', college.id)"
                                        class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors break-words"
                                    >
                                        {{ college.name }}
                                    </Link>
                                </h2>
                                <span v-if="college.acronym" 
                                      class="flex-shrink-0 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm px-2 py-1 rounded-md">
                                    {{ college.acronym }}
                                </span>
                            </div>
                            
                            <p v-if="college.description" class="text-gray-600 dark:text-gray-400 mt-3 text-sm line-clamp-3">
                                {{ college.description }}
                            </p>
                            <div v-else class="text-gray-400 dark:text-gray-500 italic text-sm mt-3">No description available</div>
                            
                            <div class="mt-auto pt-4 flex items-center text-sm">
                                <span class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ college.users_count }} {{ college.users_count === 1 ? 'Organization' : 'Organizations' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-100 dark:border-gray-600">
                            <Link
                                :href="route('colleges.show', college.id)"
                                class="inline-flex items-center justify-center w-4/5 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
                            >
                                <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                                View Details
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                        
                        <!-- College Logo/Avatar - Positioned to overflow -->
                        <div class="absolute -bottom-4 -right-4 z-10">
                            <img 
                                :src="getCollegeLogo(college.acronym, college.logo_path)" 
                                :alt="`${college.name} logo`"
                                class="college-logo"
                                @error="handleImageError"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to top floating button -->
        <button
            v-if="showBackToTop"
            @click="scrollToTop"
            aria-label="Back to top"
            class="fixed z-50 right-6 bottom-8 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 shadow-lg hover:shadow-2xl rounded-full p-3 transition transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
            title="Back to top"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 5a1 1 0 01.707.293l5 5a1 1 0 01-1.414 1.414L10 7.414 5.707 11.707A1 1 0 014.293 10.293l5-5A1 1 0 0110 5z" clip-rule="evenodd" />
            </svg>
        </button>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    auth: Object,
    colleges: Array,
});

// Back-to-top button state
const showBackToTop = ref(false);

const onScroll = () => {
    try {
        const y = window.scrollY || window.pageYOffset;
        showBackToTop.value = y > 300;
    } catch (e) {
        // ignore for SSR
    }
};

const scrollToTop = (e) => {
    e?.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
});

// Methods for logo handling
const getCollegeLogo = (acronym, customLogoPath = null) => {
    // If college has a custom uploaded logo, use it
    if (customLogoPath) {
        // Add /storage/ prefix to access files through the storage link
        return `/storage/${customLogoPath}`;
    }
    
    if (!acronym) {
        return '/images/lspu_logo_better.png';
    }
    
    const logoMap = {
        'CAS': '/images/cas-logo.jpg',
        'CCS': '/images/ccs-logo.jpg',
        'CCJE': '/images/ccje-logo.jpg',
        'COE': '/images/coe-logo.jpg',
        'CIT': '/images/cit-logo.jpg',
        'CTE': '/images/cte-logo.jpg',
        'CIHTM': '/images/chmt-logo.jpg',
        'CBAA': '/images/cbaa-logo.jpg'
    };
    
    return logoMap[acronym.toUpperCase()] || '/images/lspu_logo_better.png'; // Default fallback
};

const handleImageError = (event) => {
    // Fallback to default logo if image fails to load
    if (event && event.target) {
        event.target.src = '/images/lspu_logo_better.png';
    }
};
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Ensure consistent logo sizing and positioning */
.college-logo {
    width: 5rem;
    height: 5rem;
    border-radius: 50%;
    object-fit: cover;
    object-position: center;
    border: 4px solid white;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    background-color: white;
    flex-shrink: 0;
}

:global(.dark) .college-logo {
    border-color: #374151; /* gray-700 */
    background-color: #374151; /* gray-700 */
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
}
</style>