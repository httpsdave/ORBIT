<template>
    <AuthenticatedLayout :user="auth.user">
        <Head title="Colleges" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h1 class="text-2xl font-semibold mb-6">Colleges</h1>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="college in colleges"
                                :key="college.id"
                                class="bg-gray-50 p-6 rounded-lg shadow hover:shadow-md transition"
                            >
                                <h2 class="text-xl font-semibold">
                                    <Link
                                        :href="route('colleges.show', college.id)"
                                        class="text-blue-600 hover:text-blue-800"
                                    >
                                        {{ college.name }} <span v-if="college.acronym">({{ college.acronym }})</span>
                                    </Link>
                                </h2>
                                <p class="text-gray-600 mt-2">{{ college.description }}</p>
                                <div class="mt-4 text-sm text-gray-500">
                                    {{ college.student_orgs_count }} Student Organizations
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
