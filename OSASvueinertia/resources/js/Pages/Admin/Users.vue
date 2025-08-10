<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    users: Array,
    roles: Array,
    // studentOrgs: Array, // Removed
});

const showingCreateModal = ref(false);
const showingEditModal = ref(false);
const showingDeleteModal = ref(false);
const userToDelete = ref(null);
const userToEdit = ref(null);
const deleteConfirmation = ref(''); // Add this line

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: '',
    // student_org_id: '', // Removed
});

const editForm = useForm({
    name: '',
    email: '',
    role_id: '',
    // password and password_confirmation removed for privacy
});

const deleteForm = useForm({});

// Helper function to check if a role is admin
const isAdminRole = (roleId) => {
    if (!roleId) return false;
    const role = props.roles.find(r => r.id == roleId);
    return role && role.slug === 'admin';
};

// Watch for role changes and clear student_org_id if admin is selected
const watchRoleChange = () => {
    // No longer needed since student_org_id is removed
};

const createUser = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showingCreateModal.value = false;
        },
    });
};

const confirmUserEdit = (user) => {
    userToEdit.value = user;
    // Reset the form first to clear any previous state
    editForm.reset();
    // Then populate with user data
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role_id = user.role.id;
    // password fields removed
    showingEditModal.value = true;
};

const updateUser = () => {
    editForm.put(route('admin.users.update', userToEdit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showingEditModal.value = false;
            userToEdit.value = null;
            editForm.reset(); // Reset form after successful update
        },
    });
};

// Add a function to handle modal cancellation
const cancelEdit = () => {
    showingEditModal.value = false;
    userToEdit.value = null;
    editForm.reset(); // Reset form when canceling
    editForm.clearErrors(); // Clear any validation errors
};

// Add a function to handle create modal cancellation
const cancelCreate = () => {
    showingCreateModal.value = false;
    form.reset();
    form.clearErrors();
};

const confirmUserDeletion = (user) => {
    userToDelete.value = user;
    deleteConfirmation.value = ''; // Reset confirmation input
    showingDeleteModal.value = true;
};

const deleteUser = () => {
    deleteForm.delete(route('admin.users.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showingDeleteModal.value = false;
            userToDelete.value = null;
            deleteConfirmation.value = '';
        },
    });
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <!-- Colored banner -->
            <div class="flex w-full overflow-hidden rounded-lg mb-2">
                <div class="w-1/4 h-1 bg-blue-500" style="animation-delay: 0.2s;"></div>
                <div class="w-1/4 h-1 bg-green-500" style="animation-delay: 0.4s;"></div>
                <div class="w-1/4 h-1 bg-yellow-500" style="animation-delay: 0.6s;"></div>
                <div class="w-1/4 h-1 bg-red-500" style="animation-delay: 0.8s;"></div>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                User Management
            </h2>
        </template>

        <div class="py-3 sm:py-6 md:py-12">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-3 sm:p-4 lg:p-6">
                        <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 pb-3 sm:pb-4 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">
                                    All Users
                                </h3>
                                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">Manage user accounts and permissions</p>
                            </div>
                            <PrimaryButton 
                                @click="showingCreateModal = true"
                                class="flex items-center justify-center px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 shadow-sm text-sm w-full sm:w-auto"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="hidden xs:inline">Add New User</span>
                                <span class="xs:hidden">Add User</span>
                            </PrimaryButton>
                        </div>
                        
                        <!-- Mobile Card View (hidden on desktop) -->
                        <div class="block md:hidden space-y-2 sm:space-y-3">
                            <div v-for="user in users" :key="`mobile-${user.id}`" class="bg-white dark:bg-gray-700 rounded-lg p-3 border border-gray-200 dark:border-gray-600 shadow-sm hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center space-x-2.5 flex-1 min-w-0">
                                        <template v-if="user.profile_photo_url">
                                            <img
                                                :src="user.profile_photo_url"
                                                alt="Avatar"
                                                class="w-9 h-9 xs:w-10 xs:h-10 sm:w-12 sm:h-12 rounded-full object-cover border-2 border-blue-200 shadow-sm flex-shrink-0"
                                            />
                                        </template>
                                        <template v-else>
                                            <div
                                                class="w-9 h-9 xs:w-10 xs:h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner flex-shrink-0 text-xs xs:text-sm"
                                            >
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100 text-sm xs:text-base truncate">{{ user.name }}</h4>
                                            <p class="text-xs xs:text-sm text-gray-500 dark:text-gray-400 truncate">{{ user.email }}</p>
                                        </div>
                                    </div>
                                    <span class="px-1.5 xs:px-2 py-1 inline-flex text-xs leading-4 font-semibold rounded-full flex-shrink-0 ml-2"
                                          :class="user.role.slug === 'admin' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'">
                                        <span class="xs:hidden">{{ user.role.name.charAt(0) }}</span>
                                        <span class="hidden xs:inline">{{ user.role.name }}</span>
                                    </span>
                                </div>
                                <div class="flex justify-end space-x-1.5 xs:space-x-2 pt-2 border-t border-gray-100 dark:border-gray-600">
                                    <button 
                                        @click="confirmUserEdit(user)" 
                                        class="flex items-center px-2 xs:px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors duration-150"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 xs:mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span class="hidden xs:inline">Edit</span>
                                    </button>
                                    <button 
                                        @click="confirmUserDeletion(user)" 
                                        class="flex items-center px-2 xs:px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-150"
                                        v-if="user.id !== $page.props.auth.user.id"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 xs:mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span class="hidden xs:inline">Delete</span>
                                    </button>
                                </div>
                            </div>
                            <div v-if="users.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm">
                                No users found.
                            </div>
                        </div>
                        
                        <!-- Desktop Table View (hidden on mobile) -->
                        <div class="hidden md:block overflow-x-auto bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                                        <th scope="col" class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            <div class="flex items-center gap-2">
                                                <template v-if="user.profile_photo_url">
                                                    <img
                                                        :src="user.profile_photo_url"
                                                        alt="Avatar"
                                                        class="w-8 h-8 lg:w-10 lg:h-10 rounded-full object-cover border-2 border-blue-200 shadow-sm"
                                                    />
                                                </template>
                                                <template v-else>
                                                    <div
                                                        class="w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner text-xs lg:text-sm"
                                                    >
                                                        {{ user.name.charAt(0).toUpperCase() }}
                                                    </div>
                                                </template>
                                                <span
                                                    class="ml-2 truncate block cursor-pointer max-w-[80px] lg:max-w-[120px] xl:max-w-[200px] text-xs lg:text-sm"
                                                    :title="user.name"
                                                >
                                                    {{ user.name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-xs lg:text-sm text-gray-500 dark:text-gray-400">
                                            <div class="truncate max-w-[120px] lg:max-w-[200px] xl:max-w-none" :title="user.email">
                                                {{ user.email }}
                                            </div>
                                        </td>
                                        <td class="px-3 lg:px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 lg:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                  :class="user.role.slug === 'admin' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'">
                                                {{ user.role.name }}
                                            </span>
                                        </td>
                                        <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-xs lg:text-sm">
                                            <div class="flex space-x-2 lg:space-x-3">
                                                <button 
                                                    @click="confirmUserEdit(user)" 
                                                    class="flex items-center text-blue-500 hover:text-blue-700 transition-colors duration-150"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 lg:h-4 lg:w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    <span class="hidden lg:inline">Edit</span>
                                                </button>
                                                <button 
                                                    @click="confirmUserDeletion(user)" 
                                                    class="flex items-center text-red-500 hover:text-red-700 transition-colors duration-150"
                                                    v-if="user.id !== $page.props.auth.user.id"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 lg:h-4 lg:w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span class="hidden lg:inline">Delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="users.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No users found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <Modal :show="showingCreateModal" @close="cancelCreate">
            <div class="p-4 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-500 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">
                        Create New User
                    </h2>
                </div>

                <form @submit.prevent="createUser" class="mt-4 sm:mt-6 space-y-3 sm:space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" class="text-sm" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full text-sm"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Enter user's name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" class="text-sm" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full text-sm"
                            v-model="form.email"
                            required
                            placeholder="Enter user's email"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" class="text-sm" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full text-sm"
                            v-model="form.password"
                            required
                            placeholder="Enter password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-sm" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full text-sm"
                            v-model="form.password_confirmation"
                            required
                            placeholder="Confirm password"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div>
                        <InputLabel for="role" value="Role" class="text-sm" />
                        <select
                            id="role"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            v-model="form.role_id"
                            @change="watchRoleChange"
                            required
                        >
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.role_id" />
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-end pt-3 sm:pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2 sm:space-y-0 sm:space-x-2">
                        <SecondaryButton @click="cancelCreate" class="w-full sm:w-auto order-2 sm:order-1">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton 
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                            class="bg-blue-500 hover:bg-blue-600 w-full sm:w-auto order-1 sm:order-2"
                        >
                            Create User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit User Modal -->
        <Modal :show="showingEditModal" @close="cancelEdit">
            <div class="p-4 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-500 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">
                        Edit User
                    </h2>
                </div>

                <form @submit.prevent="updateUser" class="mt-4 sm:mt-6 space-y-3 sm:space-y-4">
                    <div>
                        <InputLabel for="edit_name" value="Name" class="text-sm" />
                        <TextInput
                            id="edit_name"
                            type="text"
                            class="mt-1 block w-full text-sm"
                            v-model="editForm.name"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="editForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="edit_email" value="Email" class="text-sm" />
                        <TextInput
                            id="edit_email"
                            type="email"
                            class="mt-1 block w-full text-sm"
                            v-model="editForm.email"
                            required
                        />
                        <InputError class="mt-2" :message="editForm.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="edit_role" value="Role" class="text-sm" />
                        <select
                            id="edit_role"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            v-model="editForm.role_id"
                            @change="watchRoleChange"
                            required
                        >
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="editForm.errors.role_id" />
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-end pt-3 sm:pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2 sm:space-y-0 sm:space-x-2">
                        <SecondaryButton @click="cancelEdit" class="w-full sm:w-auto order-2 sm:order-1">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton 
                            :class="{ 'opacity-25': editForm.processing }" 
                            :disabled="editForm.processing"
                            class="bg-blue-500 hover:bg-blue-600 w-full sm:w-auto order-1 sm:order-2"
                        >
                            Update User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        
        <!-- Delete User Confirmation Modal -->
        <Modal :show="showingDeleteModal" @close="() => { showingDeleteModal = false; deleteConfirmation = ''; }">
            <div class="p-4 sm:p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-red-500 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">
                        Delete User
                    </h2>
                </div>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>

                <div v-if="userToDelete" class="mt-4 p-3 sm:p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <p class="mb-1 text-sm"><span class="font-medium text-gray-700 dark:text-gray-300">Name:</span> {{ userToDelete.name }}</p>
                    <p class="mb-1 text-sm"><span class="font-medium text-gray-700 dark:text-gray-300">Email:</span> <span class="break-all">{{ userToDelete.email }}</span></p>
                    <p v-if="userToDelete.role" class="text-sm"><span class="font-medium text-gray-700 dark:text-gray-300">Role:</span> {{ userToDelete.role.name }}</p>
                </div>

                <!-- Confirmation input -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirmation</label>
                    <input
                        v-model="deleteConfirmation"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                        :placeholder="userToDelete ? `Type '${userToDelete.email}' to confirm` : ''"
                    />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Type <strong class="break-all">{{ userToDelete ? userToDelete.email : '' }}</strong> to confirm this action
                    </p>
                </div>

                <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-2">
                    <SecondaryButton @click="() => { showingDeleteModal = false; deleteConfirmation = ''; }" class="w-full sm:w-auto order-2 sm:order-1">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        class="bg-red-500 hover:bg-red-600 w-full sm:w-auto order-1 sm:order-2"
                        :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing || deleteConfirmation !== (userToDelete ? userToDelete.email : '')"
                        @click="deleteUser"
                    >
                        Delete User
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>