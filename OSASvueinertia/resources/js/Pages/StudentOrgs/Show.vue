<template>
    <AuthenticatedLayout :user="auth.user">
        <Head :title="studentOrg.name" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6">
                            <Link :href="route('student-orgs.index')" class="text-blue-600 hover:text-blue-800">
                                &larr; Back to Student Organizations
                            </Link>
                        </div>

                        <h1 class="text-3xl font-semibold mb-2">{{ studentOrg.name }}</h1>

                        <div v-if="studentOrg.acronym" class="text-xl text-gray-600 mb-4">
                            ({{ studentOrg.acronym }})
                        </div>

                        <div class="flex items-center mb-6">
                            <span
                                class="px-3 py-1 rounded"
                                :class="studentOrg.status === 'active'
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-red-100 text-red-800'"
                            >
                                {{ studentOrg.status === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="mx-3">•</span>
                            <Link :href="route('colleges.show', studentOrg.college.id)" class="text-blue-600 hover:text-blue-800">
                                {{ studentOrg.college.name }}
                            </Link>
                        </div>

                        <div v-if="studentOrg.logo_path" class="mb-6">
                            <img
                                :src="`/storage/${studentOrg.logo_path}`"
                                :alt="`${studentOrg.name} logo`"
                                class="h-32 w-auto object-contain"
                            />
                        </div>

                        <div v-if="studentOrg.description" class="text-gray-700 mb-8">
                            <h2 class="text-xl font-semibold mb-2">Description</h2>
                            <p>{{ studentOrg.description }}</p>
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
    studentOrg: Object,
});
</script>
