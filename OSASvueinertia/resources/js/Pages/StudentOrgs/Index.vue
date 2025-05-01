<template>
    <AuthenticatedLayout :user="auth.user">
        <Head title="Student Organizations" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h1 class="text-2xl font-semibold mb-6">Student Organizations</h1>

                        <div v-for="college in colleges" :key="college.id" class="mb-10">
                            <h2 class="text-xl font-semibold mb-4 px-4 py-2 bg-gray-100 rounded">
                                <Link :href="route('colleges.show', college.id)" class="text-blue-600 hover:text-blue-800">
                                    {{ college.name }} <span v-if="college.acronym">({{ college.acronym }})</span>
                                </Link>
                            </h2>

                            <p v-if="college.student_orgs.length === 0" class="text-gray-500 ml-4">
                                No organizations found for this college.
                            </p>

                            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div
                                    v-for="org in college.student_orgs"
                                    :key="org.id"
                                    class="bg-gray-50 p-6 rounded-lg shadow hover:shadow-md transition"
                                >
                                    <h3 class="text-lg font-semibold">
                                        <Link :href="route('student-orgs.show', org.id)" class="text-blue-600 hover:text-blue-800">
                                            {{ org.name }} <span v-if="org.acronym">({{ org.acronym }})</span>
                                        </Link>
                                    </h3>
                                    <img
                                        v-if="org.logo_path"
                                        :src="`/storage/${org.logo_path}`"
                                        :alt="`${org.name} logo`"
                                        class="h-16 w-auto object-contain mt-3 mb-3"
                                    />
                                    <p class="text-gray-600 mt-2">{{ org.description }}</p>
                                    <div class="mt-3 text-sm">
                                        <span
                                            class="px-2 py-1 rounded"
                                            :class="org.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-red-100 text-red-800'"
                                        >
                                            {{ org.status === 'active' ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
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
