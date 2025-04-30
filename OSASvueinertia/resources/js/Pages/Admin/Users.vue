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
    roles: Array
});

const showingCreateModal = ref(false);
const showingEditModal = ref(false);
const showingDeleteModal = ref(false);
const userToDelete = ref(null);
const userToEdit = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: '',
});

const editForm = useForm({
    name: '',
    email: '',
    role_id: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({});

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
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role_id = user.role.id;
    editForm.password = '';
    editForm.password_confirmation = '';
    showingEditModal.value = true;
};

const updateUser = () => {
    editForm.put(route('admin.users.update', userToEdit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showingEditModal.value = false;
            userToEdit.value = null;
        },
    });
};

const confirmUserDeletion = (user) => {
    userToDelete.value = user;
    showingDeleteModal.value = true;
};

const deleteUser = () => {
    deleteForm.delete(route('admin.users.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showingDeleteModal.value = false;
            userToDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between mb-6">
                            <h3 class="text-lg font-medium">All Users</h3>
                            <PrimaryButton @click="showingCreateModal = true">Add New User</PrimaryButton>
                        </div>
                        
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users" :key="user.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                              :class="user.role.slug === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                                            {{ user.role.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex space-x-3">
                                            <button 
                                                @click="confirmUserEdit(user)" 
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Edit
                                            </button>
                                            <button 
                                                @click="confirmUserDeletion(user)" 
                                                class="text-red-600 hover:text-red-900"
                                                v-if="user.id !== $page.props.auth.user.id"
                                            >
                                                Delete
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

        <!-- Create User Modal -->
        <Modal :show="showingCreateModal" @close="showingCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Create New User
                </h2>

                <form @submit.prevent="createUser" class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autofocus
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
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div>
                        <InputLabel for="role" value="Role" />
                        <select
                            id="role"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            v-model="form.role_id"
                            required
                        >
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.role_id" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <SecondaryButton @click="showingCreateModal = false" class="mr-2">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Create User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit User Modal -->
        <Modal :show="showingEditModal" @close="showingEditModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Edit User
                </h2>

                <form @submit.prevent="updateUser" class="mt-6 space-y-6">
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
                            required
                        >
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                        <InputError class="mt-2" :message="editForm.errors.role_id" />
                    </div>

                    <div>
                        <InputLabel for="edit_password" value="Password (leave blank to keep current)" />
                        <TextInput
                            id="edit_password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="editForm.password"
                        />
                        <InputError class="mt-2" :message="editForm.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="edit_password_confirmation" value="Confirm Password" />
                        <TextInput
                            id="edit_password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="editForm.password_confirmation"
                        />
                        <InputError class="mt-2" :message="editForm.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <SecondaryButton @click="showingEditModal = false" class="mr-2">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing">
                            Update User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        
        <!-- Delete User Confirmation Modal -->
        <Modal :show="showingDeleteModal" @close="showingDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete User
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>

                <div v-if="userToDelete" class="mt-4 p-4 bg-gray-100 rounded-md">
                    <p><strong>Name:</strong> {{ userToDelete.name }}</p>
                    <p><strong>Email:</strong> {{ userToDelete.email }}</p>
                    <p v-if="userToDelete.role"><strong>Role:</strong> {{ userToDelete.role.name }}</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showingDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton
                        class="ml-3"
                        :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing"
                        @click="deleteUser"
                    >
                        Delete User
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>