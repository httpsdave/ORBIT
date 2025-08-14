<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    archivedApplications: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
    academicYears: {
        type: Array,
        default: () => [],
    },
    currentUserFilter: {
        type: String,
        default: '',
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

const showRestoreModal = ref(false);
const applicationToRestore = ref(null);

const filterForm = ref({
    user_filter: props.currentUserFilter,
    academic_year_filter: props.currentAcademicYearFilter,
});

const applyFilters = () => {
    router.get(route('admin.archive.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.value = {
        user_filter: '',
        academic_year_filter: '',
    };
    applyFilters();
};

const confirmRestore = (application) => {
    applicationToRestore.value = application;
    showRestoreModal.value = true;
};

const restoreApplication = () => {
    if (applicationToRestore.value) {
        router.patch(route('admin.archive.restore', applicationToRestore.value.id), {}, {
            onSuccess: () => {
                showRestoreModal.value = false;
                applicationToRestore.value = null;
            },
        });
    }
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
            return 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300';
        case 'disapproved':
            return 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300';
        case 'pending':
            return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300';
        default:
            return 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300';
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
    <Head title="Archive Management" />

    <AuthenticatedLayout>
        <template #header>
            <!-- Segmented Animated Color Banner for Consistency -->
            <div class="flex w-full overflow-hidden shadow-md rounded-t-lg mb-2">
              <div class="w-1/4 h-1.5 bg-blue-600 animate-pulse" style="animation-delay: 0.2s;"></div>
              <div class="w-1/4 h-1.5 bg-green-500 animate-pulse" style="animation-delay: 0.4s;"></div>
              <div class="w-1/4 h-1.5 bg-amber-500 animate-pulse" style="animation-delay: 0.6s;"></div>
              <div class="w-1/4 h-1.5 bg-red-500 animate-pulse" style="animation-delay: 0.8s;"></div>
            </div>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 w-full">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Archive Management
                </h2>
                <Link
                    :href="route('applications.index')"
                    class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-medium rounded-xl shadow-md hover:from-purple-500 hover:to-indigo-500 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-300 relative overflow-hidden group w-full sm:w-auto"
                >
                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Applications
                </Link>
            </div>
        </template>

        <!-- Success/Error Messages -->
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
            {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded">
            {{ errorMessage }}
        </div>

        <!-- Filters -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 mb-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4">Filters</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Organization</label>
                            <SelectInput
                                v-model="filterForm.user_filter"
                                :options="[
                                    { value: '', label: 'All Organizations' },
                                    ...users.map(user => ({ value: user.id.toString(), label: user.name }))
                                ]"
                                class="w-full"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Academic Year</label>
                            <SelectInput
                                v-model="filterForm.academic_year_filter"
                                :options="[
                                    { value: '', label: 'All Years' },
                                    ...academicYears.map(year => ({ value: year, label: year }))
                                ]"
                                class="w-full"
                            />
                        </div>
                        <div class="flex flex-col justify-end sm:col-span-2 lg:col-span-2">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <PrimaryButton @click="applyFilters" class="flex-1 justify-center">
                                    Apply Filters
                                </PrimaryButton>
                                <SecondaryButton @click="clearFilters" class="flex-1 justify-center">
                                    Clear
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Archived Applications Table -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-2">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">
                            Archived Applications ({{ archivedApplications.length }})
                        </h3>
                    </div>

                    <div v-if="archivedApplications.length === 0" class="text-center py-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="mt-4 text-gray-600 dark:text-gray-400">No archived applications found.</p>
                    </div>

                    <!-- Mobile Card Layout -->
                    <div v-else class="block lg:hidden space-y-4">
                        <div v-for="application in archivedApplications" :key="application.id" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex flex-col space-y-3">
                                <!-- Header with Organization and Status -->
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ application.organization_name }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ getFormTypeLabel(application.form_type) }}
                                        </p>
                                    </div>
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2 flex-shrink-0',
                                            getStatusColor(application.status)
                                        ]"
                                    >
                                        {{ application.status }}
                                    </span>
                                </div>

                                <!-- Details Grid -->
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Organization:</span>
                                        <p class="text-gray-900 dark:text-gray-100 font-medium">{{ application.user?.name || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Academic Year:</span>
                                        <p class="text-gray-900 dark:text-gray-100 font-medium">{{ application.academic_year_archived || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Archived By:</span>
                                        <p class="text-gray-900 dark:text-gray-100 font-medium">{{ application.archived_by_user?.name || 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Archived At:</span>
                                        <p class="text-gray-900 dark:text-gray-100 font-medium">{{ formatDate(application.archived_at) }}</p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-col sm:flex-row gap-2 pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <a
                                        :href="route('applications.pdf', application.id) + '?action=view'"
                                        class="inline-flex items-center justify-center px-3 py-2 bg-green-500 hover:bg-green-400 text-white text-sm font-medium rounded-lg transition duration-300 relative overflow-hidden group shadow-sm flex-1"
                                        target="_blank"
                                    >
                                        <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-16 group-hover:h-16 opacity-10"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                        View PDF
                                    </a>
                                    <button
                                        @click="confirmRestore(application)"
                                        class="inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg transition duration-300 relative overflow-hidden group shadow-sm flex-1"
                                    >
                                        <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-16 group-hover:h-16 opacity-10"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l-4-4m0 0l4-4m-4 4h11a4 4 0 110 8h-1" />
                                        </svg>
                                        Restore
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table Layout -->
                    <div v-else class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Application
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Organization
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Academic Year
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Archived By
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Archived At
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="application in archivedApplications" :key="application.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ application.organization_name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ getFormTypeLabel(application.form_type) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ application.user?.name || 'N/A' }}
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ application.academic_year_archived || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ application.archived_by_user?.name || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ formatDate(application.archived_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
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
                                            <button
                                                @click="confirmRestore(application)"
                                                class="bg-blue-600 hover:bg-blue-500 text-white p-2.5 rounded-lg transition duration-300 relative overflow-hidden group shadow-sm"
                                                title="Restore"
                                            >
                                                <span class="absolute w-0 h-0 transition-all duration-300 ease-out bg-white rounded-full group-hover:w-16 group-hover:h-16 opacity-10"></span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l-4-4m0 0l4-4m-4 4h11a4 4 0 110 8h-1" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Confirmation Modal -->
        <Modal :show="showRestoreModal" @close="showRestoreModal = false">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Restore Application
                        </h3>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Are you sure you want to restore this application? It will become active again and users will be able to edit it.
                    </p>
                </div>
                <div class="mt-4 flex justify-end space-x-3">
                    <SecondaryButton @click="showRestoreModal = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="restoreApplication" class="bg-green-600 hover:bg-green-700">
                        Restore
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template> 