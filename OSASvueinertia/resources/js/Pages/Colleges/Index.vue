<template>
    <AuthenticatedLayout :user="auth.user">
        <Head title="Colleges" />

        <div class="py-8 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Animated colored banner -->
                <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
                    <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
                    <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
                    <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
                    <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
                </div>

                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <span class="mr-2">Colleges</span>
                        <span class="text-sm bg-blue-100 text-blue-800 py-1 px-2 rounded-full">
                            {{ colleges.length }} total
                        </span>
                    </h1>
                </div>

                <div v-if="colleges.length === 0" class="bg-white rounded-xl shadow-md p-8 text-center">
                    <div class="text-gray-500">No colleges found</div>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="college in colleges"
                        :key="college.id"
                        class="relative bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 flex flex-col"
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
                                <h2 class="text-xl font-semibold text-gray-800 leading-tight flex-1 min-w-0 mr-2">
                                    <Link
                                        :href="route('colleges.show', college.id)"
                                        class="hover:text-blue-600 transition-colors break-words"
                                    >
                                        {{ college.name }}
                                    </Link>
                                </h2>
                                <span v-if="college.acronym" 
                                      class="flex-shrink-0 bg-gray-100 text-gray-600 text-sm px-2 py-1 rounded-md">
                                    {{ college.acronym }}
                                </span>
                            </div>
                            
                            <p v-if="college.description" class="text-gray-600 mt-3 text-sm line-clamp-3">
                                {{ college.description }}
                            </p>
                            <div v-else class="text-gray-400 italic text-sm mt-3">No description available</div>
                            
                            <div class="mt-auto pt-4 flex items-center text-sm">
                                <span class="flex items-center text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ college.users_count }} {{ college.users_count === 1 ? 'Organization' : 'Organizations' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
                            <Link
                                :href="route('colleges.show', college.id)"
                                class="inline-flex items-center justify-center w-4/5 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group"
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
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    auth: Object,
    colleges: Array,
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
        'CHMT': '/images/chmt-logo.jpg',
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
</style>