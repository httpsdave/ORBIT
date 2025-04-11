

<script setup>
import { defineProps, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({ applications: Array });

const message = ref(null); // Stores success/failure messages

const deleteApplication = (id) => {
    if (confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
        router.delete(`/applications/${id}`, {
            onSuccess: () => {
                message.value = "Application deleted successfully!";
            },
            onError: () => {
                message.value = "Failed to delete application.";
            }
        });
    }
};

const getPdfRoute = (app) => {
    // Check the form type directly
    if (app.form_type === 'LSPU-OSAS-SF-002') {
        return `/applications/${app.id}/export-renewal`;
    } else if (app.form_type === 'LSPU-OSAS-SF-001') {
        return `/applications/${app.id}/pdf`;
    } else if (app.form_type === 'LSPU-OSAS-SF-003') {
        return `/applications/${app.id}/export-commitment`;
    
    } else if (app.form_type === 'LSPU-OSAS-SF-004') {
        return `/applications/${app.id}/export-plan`;
    
    } else if (app.form_type === 'LSPU-OSAS-SF-006') {
        return `/applications/${app.id}/export-certification`;
    
    } else {
        // Default case
        console.warn('Unknown form type:', app.form_type);
        return `/applications/${app.id}/pdf`;
    }
};

</script>

<template>
    <div class="p-6">
        <h1 class="text-xl font-bold">Organization Applications</h1>

        <!-- Display success/failure message -->
        <div v-if="message" class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ message }}
        </div>

        <!-- Create New Application Button -->
        <Link href="/applications/create" class="bg-blue-500 text-white px-4 py-2 rounded">New Application</Link>

        <!-- Show Message if No Applications Exist -->
        <div v-if="applications.length === 0" class="mt-6 text-gray-600">
            No applications found. Click "New Application" to create one.
        </div>

        <!-- Applications Table -->
        <table v-else class="w-full mt-4 border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">Form Type</th>
                    <th class="border px-4 py-2">Organization</th>
                    <th class="border px-4 py-2">President</th>
                    <th class="border px-4 py-2">Date</th>
                    <th class="border px-4 py-2">Status</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="app in applications" :key="app.id">
                    <td class="border px-4 py-2">{{ app.form_type || 'N/A' }}</td>
                    <td class="border px-4 py-2">{{ app.organization_name }}</td>
                    <td class="border px-4 py-2">{{ app.president_name }}</td>
                    <td class="border px-4 py-2">{{ app.application_date }}</td>
                    <td class="border px-4 py-2">{{ app.status }}</td>
                    <td class="border px-4 py-2 flex space-x-2">
                        <Link :href="`/applications/${app.id}/edit`" class="bg-yellow-500 text-white px-4 py-1 rounded">Edit</Link>
                        <a :href="getPdfRoute(app)" class="bg-red-500 text-white px-4 py-1 rounded">
                            Download PDF
                        </a>
                        <button @click="deleteApplication(app.id)" class="bg-gray-500 text-white px-4 py-1 rounded hover:bg-gray-700">
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>