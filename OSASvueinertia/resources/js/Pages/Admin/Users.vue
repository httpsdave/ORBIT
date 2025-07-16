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
                <div class="w-1/4 h-1 bg-blue-500 " style="animation-delay: 0.2s;"></div>
                <div class="w-1/4 h-1 bg-green-500 " style="animation-delay: 0.4s;"></div>
                <div class="w-1/4 h-1 bg-yellow-500 " style="animation-delay: 0.6s;"></div>
                <div class="w-1/4 h-1 bg-red-500 " style="animation-delay: 0.8s;"></div>
            </div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Management
            </h2>
        </template>

        <div class="py-6 md:py-12">
           
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 pb-4 border-b border-gray-100">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 sm:mb-0">
                                
                                <span class="ml-2">All Users</span>
                            </h3>
                            <PrimaryButton 
                                @click="showingCreateModal = true"
                                class="flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200 shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add New User
                            </PrimaryButton>
                        </div>
                        
                        <div class="overflow-x-auto bg-white rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <!-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Organization</th> -->
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                  :class="user.role.slug === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                                                {{ user.role.name }}
                                            </span>
                                        </td>
                                        <!-- Student Organization column removed -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex space-x-3">
                                                <button 
                                                    @click="confirmUserEdit(user)" 
                                                    class="flex items-center text-blue-500 hover:text-blue-700 transition-colors duration-150"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                                <button 
                                                    @click="confirmUserDeletion(user)" 
                                                    class="flex items-center text-red-500 hover:text-red-700 transition-colors duration-150"
                                                    v-if="user.id !== $page.props.auth.user.id"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="users.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            No users found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
          
        </div>

        <!-- Create User Modal -->
        <Modal :show="showingCreateModal" @close="cancelCreate">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-500 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-medium text-gray-900">
                        Create New User
                    </h2>
                </div>

                <form @submit.prevent="createUser" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Enter user's name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            placeholder="Enter user's email"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            required
                            placeholder="Enter password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password_confirmation"
                            required
                            placeholder="Confirm password"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div>
                        <InputLabel for="role" value="Role" />
                        <select
                            id="role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.role_id"
                            @change="watchRoleChange"
                            required
                        >
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.role_id" />
                    </div>

                    <!-- Student Organization select removed -->

                    <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                        <SecondaryButton @click="cancelCreate" class="mr-2">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton 
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                            class="bg-blue-500 hover:bg-blue-600"
                        >
                            Create User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit User Modal -->
        <Modal :show="showingEditModal" @close="cancelEdit">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-500 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-medium text-gray-900">
                        Edit User
                    </h2>
                </div>

                <form @submit.prevent="updateUser" class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="edit_name" value="Name" />
                        <TextInput
                            id="edit_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="editForm.name"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="editForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="edit_email" value="Email" />
                        <TextInput
                            id="edit_email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="editForm.email"
                            required
                        />
                        <InputError class="mt-2" :message="editForm.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="edit_role" value="Role" />
                        <select
                            id="edit_role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="editForm.role_id"
                            @change="watchRoleChange"
                            required
                        >
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="editForm.errors.role_id" />
                    </div>

                    <!-- Password fields removed for privacy -->

                    <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                        <SecondaryButton @click="cancelEdit" class="mr-2">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton 
                            :class="{ 'opacity-25': editForm.processing }" 
                            :disabled="editForm.processing"
                            class="bg-blue-500 hover:bg-blue-600"
                        >
                            Update User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        
        <!-- Delete User Confirmation Modal -->
        <Modal :show="showingDeleteModal" @close="() => { showingDeleteModal = false; deleteConfirmation = ''; }">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-red-500 p-2 rounded-lg mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-medium text-gray-900">
                        Delete User
                    </h2>
                </div>

                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>

                <div v-if="userToDelete" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="mb-1"><span class="font-medium text-gray-700">Name:</span> {{ userToDelete.name }}</p>
                    <p class="mb-1"><span class="font-medium text-gray-700">Email:</span> {{ userToDelete.email }}</p>
                    <p v-if="userToDelete.role"><span class="font-medium text-gray-700">Role:</span> {{ userToDelete.role.name }}</p>
                </div>

                <!-- Confirmation input -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmation</label>
                    <input
                        v-model="deleteConfirmation"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        :placeholder="userToDelete ? `Type '${userToDelete.email}' to confirm` : ''"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                        Type <strong>{{ userToDelete ? userToDelete.email : '' }}</strong> to confirm this action
                    </p>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="() => { showingDeleteModal = false; deleteConfirmation = ''; }" class="mr-2">
                        Cancel
                    </SecondaryButton>
                    <DangerButton
                        class="bg-red-500 hover:bg-red-600"
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