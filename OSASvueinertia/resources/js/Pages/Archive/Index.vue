<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    archivedApplications: {
        type: Array,
        default: () => [],
    },
    academicYears: {
        type: Array,
        default: () => [],
    },
    currentAcademicYearFilter: {
        type: String,
        default: '',
    },
    successMessage: {
        type: String,
        default: '',
    },
    errorMessage: {
        type: String,
        default: '',
    },
});

const filterForm = ref({
    academic_year_filter: props.currentAcademicYearFilter,
});

const applyFilters = () => {
    router.get(route('archive.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        academic_year_filter: '',
    };
    applyFilters();
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status) => {
    switch (status?.toLowerCase()) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'disapproved':
            return 'bg-red-100 text-red-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getFormTypeLabel = (formType) => {
    const formTypes = {
        'LSPU-OSAS-SF-001': 'Student Organization Application',
        'LSPU-OSAS-SF-002': 'Student Organization Renewal',
        'LSPU-OSAS-SF-003': 'Commitment Form',
        'LSPU-OSAS-SF-004': 'Plan of Activities',
        'LSPU-OSAS-SF-005': 'List of Members',
        'LSPU-OSAS-SF-006': 'Student Certification',
        'LSPU-OSAS-SF-007': 'List of Officers',
        'LSPU-OSAS-SF-009': 'Activity Attendance Sheet',
    };
    return formTypes[formType] || formType;
};
</script>

<template>
    <Head title="My Archived Applications" />

    <AuthenticatedLayout>
        <template #header>
            <!-- Segmented Animated Color Banner for Consistency -->
            <div class="flex w-full overflow-hidden shadow-md rounded-t-lg mb-2">
              <div class="w-1/4 h-1.5 bg-blue-600 animate-pulse" style="animation-delay: 0.2s;"></div>
              <div class="w-1/4 h-1.5 bg-green-500 animate-pulse" style="animation-delay: 0.4s;"></div>
              <div class="w-1/4 h-1.5 bg-amber-500 animate-pulse" style="animation-delay: 0.6s;"></div>
              <div class="w-1/4 h-1.5 bg-red-500 animate-pulse" style="animation-delay: 0.8s;"></div>
            </div>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    My Archived Applications
                </h2>
            </div>
        </template>

        <!-- Success/Error Messages -->
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ errorMessage }}
        </div>

        <!-- Info Banner -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Archived Applications
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>
                            These applications have been archived and can no longer be edited. You can view them for reference purposes.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-800 mb-4">Filters</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                        <SelectInput
                            v-model="filterForm.academic_year_filter"
                            :options="[
                                { value: '', label: 'All Years' },
                                ...academicYears.map(year => ({ value: year, label: year }))
                            ]"
                            class="w-full"
                        />
                    </div>
                    <div class="flex items-end space-x-2">
                        <PrimaryButton @click="applyFilters" class="flex-1">
                            Apply Filters
                        </PrimaryButton>
                        <SecondaryButton @click="clearFilters">
                            Clear
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Archived Applications Table -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-800">
                        Archived Applications ({{ archivedApplications.length }})
                    </h3>
                </div>

                <div v-if="archivedApplications.length === 0" class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-4 text-gray-600">No archived applications found.</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Application
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Academic Year
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Archived At
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="application in archivedApplications" :key="application.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ application.organization_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ getFormTypeLabel(application.form_type) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            getStatusColor(application.status)
                                        ]"
                                    >
                                        {{ application.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ application.academic_year_archived || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(application.archived_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a
                                        :href="route('applications.pdf', application.id) + '?action=view'"
                                        class="bg-green-500 hover:bg-green-400 text-white p-2.5 rounded-lg transition duration-300 relative overflow-hidden group shadow-sm"
                                        target="_blank"
                                        title="View PDF"
                                    >
                                        <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-16 group-hover:h-16 opacity-10"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add a subtle, center-aligned archive link at the bottom -->
        <div class="flex justify-center mt-10 mb-6">
          <a
            :href="route('applications.index')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg shadow-sm transition duration-200 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
            aria-label="Back to Applications"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <span>Back to Applications</span>
          </a>
        </div>
    </AuthenticatedLayout>
</template> 