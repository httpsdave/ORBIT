<template>
    <AuthenticatedLayout :user="auth.user">
        <Head title="Student Organizations" />

        <div class="py-8 bg-gray-50 min-h-screen">
            <!-- Animated colored banner -->
            <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
                <div class="w-1/4 h-1.5 bg-blue-500" style="animation-delay: 0.2s;"></div>
                <div class="w-1/4 h-1.5 bg-green-500" style="animation-delay: 0.4s;"></div>
                <div class="w-1/4 h-1.5 bg-yellow-500" style="animation-delay: 0.6s;"></div>
                <div class="w-1/4 h-1.5 bg-red-500" style="animation-delay: 0.8s;"></div>
            </div>

            <div class="pl-4 pr-4 sm:pl-8 sm:pr-6 lg:pl-16 lg:pr-12">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Student Organizations</h1>

                <div class="mb-6 max-w-md">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search organizations or colleges..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm bg-white"
                    />
                </div>

                <div v-if="filteredOrganizations.length === 0" class="text-gray-500 italic text-center py-12">
                    No organizations found.
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div
                        v-for="org in filteredOrganizations"
                        :key="org.id"
                        class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col"
                    >
                        <!-- Colored top border, cycling colors -->
                        <div class="h-1 w-full"
                            :class="{
                                'bg-blue-500': org.id % 4 === 0,
                                'bg-green-500': org.id % 4 === 1,
                                'bg-yellow-500': org.id % 4 === 2,
                                'bg-red-500': org.id % 4 === 3,
                            }"
                        ></div>
                        <div class="p-6 flex-1 flex flex-row items-center justify-between">
                            <div class="flex flex-col items-start flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-800 mb-1 truncate w-full">{{ org.name }}</h3>
                                <span v-if="org.college" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full mb-2">{{ org.college.acronym || org.college.name }}</span>
                                <p class="text-gray-600 text-xs line-clamp-2 mb-2 w-full">{{ org.description || 'No description available' }}</p>
                            </div>
                            <div class="flex-shrink-0 ml-4 flex items-center justify-center">
                                <img
                                    v-if="org.profile_photo_url"
                                    :src="org.profile_photo_url"
                                    :alt="`${org.name} logo`"
                                    class="h-20 w-20 object-cover rounded-full border border-gray-200 shadow-inner bg-gray-50"
                                />
                                <div
                                    v-else
                                    class="h-20 w-20 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white text-2xl font-medium shadow-inner select-none"
                                >
                                    {{ org.name ? org.name.charAt(0).toUpperCase() : '?' }}
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center rounded-b-lg">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            <Link :href="route('student-orgs.show', org.id)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Details</Link>
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
import { defineProps, ref, computed } from 'vue';

const props = defineProps({
    auth: Object,
    organizations: Array,
});

const searchQuery = ref('');

const filteredOrganizations = computed(() => {
    if (!searchQuery.value) return props.organizations;
    const q = searchQuery.value.toLowerCase();
    return props.organizations.filter(org => {
        const name = org.name ? org.name.toLowerCase() : '';
        const college = org.college ? (org.college.name + ' ' + (org.college.acronym || '')).toLowerCase() : '';
        return name.includes(q) || college.includes(q);
    });
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>