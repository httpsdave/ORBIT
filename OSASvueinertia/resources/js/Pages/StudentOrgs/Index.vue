<template>
    <AuthenticatedLayout :user="auth.user">
        <Head title="Student Organizations" />

        <!-- Color banner -->
        <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
                    <div class="w-1/4 h-1.5 bg-blue-500 " style="animation-delay: 0.2s;"></div>
                    <div class="w-1/4 h-1.5 bg-green-500 " style="animation-delay: 0.4s;"></div>
                    <div class="w-1/4 h-1.5 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
                    <div class="w-1/4 h-1.5 bg-red-500 " style="animation-delay: 0.8s;"></div>
                </div>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
       
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Student Organizations</h1>
                </div>

                <div v-for="college in colleges" :key="college.id" class="mb-12">
                    <div class="flex items-center mb-6 group">
                        <div class="w-1 h-8 bg-blue-500 mr-3 rounded"></div>
                        <h2 class="text-2xl font-semibold">
                            <Link :href="route('colleges.show', college.id)" class="text-gray-800 hover:text-blue-600 transition duration-300">
                                {{ college.name }} <span v-if="college.acronym" class="text-gray-500">({{ college.acronym }})</span>
                            </Link>
                        </h2>
                    </div>

                    <p v-if="college.student_orgs.length === 0" class="text-gray-500 ml-4 italic">
                        No organizations found for this college.
                    </p>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div
                            v-for="org in college.student_orgs"
                            :key="org.id"
                            class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col"
                        >
                            <div class="h-1.5 w-full" :class="{
                                'bg-green-500': org.status === 'active',
                                'bg-red-500': org.status !== 'active'
                            }"></div>
                            <div class="p-6 flex-grow">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-4" v-if="org.logo_path">
                                        <img
                                            :src="`/storage/${org.logo_path}`"
                                            :alt="`${org.name} logo`"
                                            class="h-16 w-16 object-contain rounded-md bg-gray-50 p-1"
                                        />
                                    </div>
                                    <div class="flex-grow">
                                        <h3 class="text-lg font-semibold mb-2">
                                            <Link :href="route('student-orgs.show', org.id)" class="text-gray-800 hover:text-blue-600 transition duration-300">
                                                {{ org.name }}
                                            </Link>
                                        </h3>
                                        <div v-if="org.acronym" class="text-sm font-medium text-gray-500 mb-2">
                                            {{ org.acronym }}
                                        </div>
                                        <p class="text-gray-600 text-sm line-clamp-3">{{ org.description || 'No description available' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 py-3 bg-gray-50 flex justify-between items-center">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="org.status === 'active'
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800'"
                                >
                                    {{ org.status === 'active' ? 'Active' : 'Inactive' }}
                                </span>
                                <Link 
                                    :href="route('student-orgs.show', org.id)" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View Details
                                </Link>
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
import { defineProps } from 'vue';

defineProps({
    auth: Object,
    colleges: Array,
});
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
  
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>