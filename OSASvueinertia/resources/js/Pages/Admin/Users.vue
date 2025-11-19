<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import { auth } from '@/firebase/config';
import { createUserWithEmailAndPassword, sendEmailVerification } from 'firebase/auth';

const props = defineProps({
    users: Array,
    roles: Array,
    // studentOrgs: Array, // Removed
});

const showingCreateModal = ref(false);
const showingEditModal = ref(false);
const showingDeleteModal = ref(false);
const showingRoleChangeConfirmModal = ref(false);
const userToDelete = ref(null);
const userToEdit = ref(null);
const deleteConfirmation = ref(''); // Add this line
const searchQuery = ref(''); // Add search functionality
const originalRoleId = ref(null); // Track original role for confirmation

// Password visibility toggles
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Notification state
const notification = ref({
    show: false,
    type: '', // 'success', 'error', 'info'
    title: '',
    message: ''
});

// Show notification function
const showNotification = (type, title, message) => {
    notification.value = {
        show: true,
        type,
        title,
        message
    };
    // Auto-hide after 5 seconds
    setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

// Close notification function
const closeNotification = () => {
    notification.value.show = false;
};

// Computed property for filtered and sorted users
const filteredUsers = computed(() => {
    let filtered = props.users;
    
    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(user => 
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            user.role.name.toLowerCase().includes(query)
        );
    }
    
    // Sort alphabetically by name
    return filtered.slice().sort((a, b) => a.name.localeCompare(b.name));
});

// Clear search function
const clearSearch = () => {
    searchQuery.value = '';
};

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: '',
    // student_org_id: '', // Removed
});

// Client-side validation errors
const validationErrors = ref({
    password: '',
    password_confirmation: '',
});

const editForm = useForm({
    name: '',
    email: '',
    role_id: '',
    // password and password_confirmation removed for privacy
});

const deleteForm = useForm({});

// Dropdown state management (similar to ApplicationsTable)
const activeDropdownUser = ref(null);
const dropdownPosition = ref({ top: 0, left: 0 });
const dropdownButtonEl = ref(null);
const dropdownRef = ref(null);
const dropdownDirection = ref('down');

// Mobile modal state
const showMobileActionsModal = ref(false);
const selectedMobileUser = ref(null);

// Back-to-top button state and handler
const showBackToTop = ref(false);
const onScroll = () => {
    try {
        const y = window.scrollY || window.pageYOffset;
        showBackToTop.value = y > 300;
    } catch (e) {
        // ignore for SSR
    }
};

const scrollToTop = (e) => {
    e?.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    document.removeEventListener('click', closeDropdowns);
    removeDropdownListeners();
});

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

// Validate password on the client side
const validatePassword = () => {
    validationErrors.value.password = '';
    validationErrors.value.password_confirmation = '';

    // Check password length
    if (form.password.length < 8) {
        validationErrors.value.password = 'Password must be at least 8 characters';
        return false;
    }

    // Check if passwords match
    if (form.password !== form.password_confirmation) {
        validationErrors.value.password_confirmation = 'Passwords do not match';
        return false;
    }

    return true;
};

const createUser = async () => {
    // Clear previous validation errors
    validationErrors.value.password = '';
    validationErrors.value.password_confirmation = '';

    // Validate password before submission
    if (!validatePassword()) {
        return;
    }

    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: async () => {
            // User created successfully in Laravel
            const userEmail = form.email;
            const userName = form.name;
            const userPassword = form.password;
            
            try {
                // Create Firebase account and send verification email
                console.log('Creating Firebase account for new user:', userEmail);
                
                // Step 1: Create Firebase user account
                const userCredential = await createUserWithEmailAndPassword(auth, userEmail, userPassword);
                console.log('Firebase account created successfully');
                
                // Step 2: Send email verification (uses Firebase's Email Verification template)
                await sendEmailVerification(userCredential.user, {
                    url: window.location.origin + '/login',
                    handleCodeInApp: false
                });
                
                console.log('Verification email sent to:', userEmail);
                
                // Step 3: Sign out the newly created user (important!)
                await auth.signOut();
                
                // Show success notification
                showNotification(
                    'success',
                    'User Created Successfully!',
                    `User "${userName}" has been created. A verification email has been sent to ${userEmail}. The user can log in immediately, but should verify their email for full access.`
                );
                
            } catch (firebaseError) {
                console.error('Firebase error:', firebaseError);
                
                // User was created in Laravel but Firebase failed
                if (firebaseError.code === 'auth/email-already-in-use') {
                    // User already exists in Firebase - that's okay
                    console.log('User already exists in Firebase');
                    showNotification(
                        'success',
                        'User Created Successfully!',
                        `User "${userName}" has been created. Note: Firebase account already exists. User can log in with their credentials.`
                    );
                } else if (firebaseError.code === 'auth/weak-password') {
                    showNotification(
                        'success',
                        'User Created Successfully!',
                        `User "${userName}" has been created. Note: Password should be at least 6 characters for Firebase. User can still log in.`
                    );
                } else {
                    // Other errors - not critical
                    showNotification(
                        'success',
                        'User Created Successfully!',
                        `User "${userName}" has been created. Note: Verification email could not be sent, but the account is active.`
                    );
                }
            }
            
            form.reset();
            showingCreateModal.value = false;
            // Reset password visibility toggles
            showPassword.value = false;
            showConfirmPassword.value = false;
            // Clear validation errors
            validationErrors.value.password = '';
            validationErrors.value.password_confirmation = '';
        },
        onError: () => {
            // Keep the modal open on server-side validation errors
        }
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
    // Store original role for comparison
    originalRoleId.value = user.role.id;
    // password fields removed
    showingEditModal.value = true;
};

const updateUser = () => {
    // Check if role has changed
    if (editForm.role_id !== originalRoleId.value) {
        // Hide edit modal and show confirmation modal for role change
        showingEditModal.value = false;
        showingRoleChangeConfirmModal.value = true;
    } else {
        // No role change, proceed with update
        proceedWithUpdate();
    }
};

const proceedWithUpdate = () => {
    editForm.put(route('admin.users.update', userToEdit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showingEditModal.value = false;
            showingRoleChangeConfirmModal.value = false;
            userToEdit.value = null;
            originalRoleId.value = null;
            editForm.reset(); // Reset form after successful update
            showNotification('success', 'User Updated', 'User information has been successfully updated.');
        },
    });
};

const cancelRoleChange = () => {
    showingRoleChangeConfirmModal.value = false;
    // Reopen the edit modal so user can make changes
    showingEditModal.value = true;
};

// Add a function to handle modal cancellation
const cancelEdit = () => {
    showingEditModal.value = false;
    showingRoleChangeConfirmModal.value = false;
    userToEdit.value = null;
    originalRoleId.value = null;
    editForm.reset(); // Reset form when canceling
    editForm.clearErrors(); // Clear any validation errors
};

// Add a function to handle create modal cancellation
const cancelCreate = () => {
    showingCreateModal.value = false;
    form.reset();
    form.clearErrors();
    // Reset password visibility toggles
    showPassword.value = false;
    showConfirmPassword.value = false;
    // Clear client-side validation errors
    validationErrors.value.password = '';
    validationErrors.value.password_confirmation = '';
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

// Dropdown functionality (similar to ApplicationsTable)
const toggleDropdown = (user, event) => {
    if (window.innerWidth < 640) { // Mobile: show modal popup
        selectedMobileUser.value = user;
        showMobileActionsModal.value = true;
        return;
    }
    // Desktop: floating dropdown
    if (activeDropdownUser.value && activeDropdownUser.value.id === user.id) {
        activeDropdownUser.value = null;
        dropdownButtonEl.value = null;
        removeDropdownListeners();
    } else {
        activeDropdownUser.value = user;
        dropdownButtonEl.value = event.currentTarget;
        updateDropdownPosition();
        addDropdownListeners();
    }
};

async function updateDropdownPosition() {
    if (!dropdownButtonEl.value) return;
    const rect = dropdownButtonEl.value.getBoundingClientRect();
    let dropdownWidth = 160; // Fixed width for w-40
    let left = rect.right - dropdownWidth + 8;
    if (left + dropdownWidth > window.innerWidth) left = window.innerWidth - dropdownWidth - 16;
    if (left < 16) left = 16;

    await nextTick();
    let dropdownHeight = dropdownRef.value ? dropdownRef.value.offsetHeight : 120;

    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;

    let top;
    if (spaceBelow >= dropdownHeight + 16) {
        top = rect.bottom + 2;
        dropdownDirection.value = 'down';
    } else if (spaceAbove >= dropdownHeight + 16) {
        top = rect.top - dropdownHeight - 2;
        dropdownDirection.value = 'up';
    } else if (spaceBelow >= spaceAbove) {
        top = rect.bottom + 2;
        dropdownDirection.value = 'down';
    } else {
        top = Math.max(8, rect.top - dropdownHeight - 2);
        dropdownDirection.value = 'up';
    }

    dropdownPosition.value = { top, left };
}

function addDropdownListeners() {
    window.addEventListener('scroll', updateDropdownPosition, true);
    window.addEventListener('resize', updateDropdownPosition);
}

function removeDropdownListeners() {
    window.removeEventListener('scroll', updateDropdownPosition, true);
    window.removeEventListener('resize', updateDropdownPosition);
}

const closeDropdowns = (event) => {
    if (!event.target.closest('.dropdown-container')) {
        activeDropdownUser.value = null;
    }
};

const handleDropdownAction = (user, action) => {
    // Close dropdown
    activeDropdownUser.value = null;
    
    // Handle specific actions
    switch(action) {
        case 'edit':
            confirmUserEdit(user);
            break;
        case 'delete':
            confirmUserDeletion(user);
            break;
    }
};

// Mobile modal action handlers
const handleMobileAction = (action) => {
    const user = selectedMobileUser.value;
    if (!user) return;
    
    // Close mobile modal
    showMobileActionsModal.value = false;
    selectedMobileUser.value = null;
    
    // Handle specific actions
    switch(action) {
        case 'edit':
            confirmUserEdit(user);
            break;
        case 'delete':
            confirmUserDeletion(user);
            break;
    }
};

const closeMobileActionsModal = () => {
    showMobileActionsModal.value = false;
    selectedMobileUser.value = null;
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <!-- Custom Notification -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-[-20px]"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-[-20px]"
        >
            <div
                v-if="notification.show"
                class="fixed top-4 left-4 right-4 sm:left-auto sm:right-4 z-50 max-w-md w-auto sm:w-96 shadow-lg rounded-lg overflow-hidden"
                :class="{
                    'bg-green-50 border-l-4 border-green-500': notification.type === 'success',
                    'bg-red-50 border-l-4 border-red-500': notification.type === 'error',
                    'bg-blue-50 border-l-4 border-blue-500': notification.type === 'info'
                }"
            >
                <div class="p-3 sm:p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <!-- Success Icon -->
                            <svg v-if="notification.type === 'success'" class="h-5 w-5 sm:h-6 sm:w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Error Icon -->
                            <svg v-else-if="notification.type === 'error'" class="h-5 w-5 sm:h-6 sm:w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Info Icon -->
                            <svg v-else class="h-5 w-5 sm:h-6 sm:w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-2 sm:ml-3 flex-1 min-w-0">
                            <h3 class="text-xs sm:text-sm font-semibold"
                                :class="{
                                    'text-green-800': notification.type === 'success',
                                    'text-red-800': notification.type === 'error',
                                    'text-blue-800': notification.type === 'info'
                                }"
                            >
                                {{ notification.title }}
                            </h3>
                            <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm break-words"
                                :class="{
                                    'text-green-700': notification.type === 'success',
                                    'text-red-700': notification.type === 'error',
                                    'text-blue-700': notification.type === 'info'
                                }"
                            >
                                {{ notification.message }}
                            </p>
                        </div>
                        <div class="ml-2 sm:ml-4 flex-shrink-0">
                            <button
                                @click="closeNotification"
                                class="inline-flex rounded-md p-1 focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="{
                                    'text-green-500 hover:text-green-600 focus:ring-green-500': notification.type === 'success',
                                    'text-red-500 hover:text-red-600 focus:ring-red-500': notification.type === 'error',
                                    'text-blue-500 hover:text-blue-600 focus:ring-blue-500': notification.type === 'info'
                                }"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Colored Banner -->
        <div class="flex w-full mb-6 overflow-hidden rounded-lg shadow-md">
            <div class="w-1/4 h-1.5 bg-blue-500" style="animation-delay: 0.2s;"></div>
            <div class="w-1/4 h-1.5 bg-green-500" style="animation-delay: 0.4s;"></div>
            <div class="w-1/4 h-1.5 bg-yellow-500" style="animation-delay: 0.6s;"></div>
            <div class="w-1/4 h-1.5 bg-red-500" style="animation-delay: 0.8s;"></div>
        </div>

        <div class="py-3 sm:py-6 md:py-8 w-full">
            <div class="max-w-7xl mx-auto px-1 sm:px-6 lg:px-8 w-full">
                <!-- Header Section -->
                <div class="mb-8">
                    <div class="flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between mb-2">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                User Management
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">
                                Manage user accounts and permissions
                            </p>
                        </div>
                        <PrimaryButton 
                            @click="showingCreateModal = true"
                            class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 shadow-sm text-sm w-full sm:w-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden xs:inline">Add New User</span>
                            <span class="xs:hidden">Add User</span>
                        </PrimaryButton>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg min-w-0 w-full">
                    <div class="p-2 sm:p-4 lg:p-6 w-full">

                        <!-- Search Bar -->
                        <div class="mb-3 sm:mb-6">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    type="text"
                                    v-model="searchQuery"
                                    class="block w-full pl-12 pr-12 py-3 border border-gray-300 dark:border-gray-600 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:border-blue-400 transition duration-150 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400"
                                    placeholder="Search users by name, email, or role..."
                                />
                                <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button @click="clearSearch" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mobile Card View (hidden on desktop) -->
                        <div class="block md:hidden space-y-2">
                            <div v-for="user in filteredUsers" :key="`mobile-${user.id}`" class="bg-white dark:bg-gray-700 rounded-lg p-2 border border-gray-200 dark:border-gray-600 shadow-sm">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center space-x-2 flex-1 min-w-0">
                                        <template v-if="user.profile_photo_url">
                                            <img
                                                :src="user.profile_photo_url"
                                                alt="Avatar"
                                                class="w-10 h-10 rounded-full object-cover border-2 border-blue-200 shadow-sm flex-shrink-0"
                                            />
                                        </template>
                                        <template v-else>
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner flex-shrink-0 text-sm"
                                            >
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </div>
                                        </template>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100 text-sm truncate">{{ user.name }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 break-words">{{ user.email }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full flex-shrink-0"
                                          :class="user.role.slug === 'admin' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'">
                                        {{ user.role.name }}
                                    </span>
                                </div>
                                <div class="flex justify-end space-x-2 pt-2 border-t border-gray-100 dark:border-gray-600">
                                    <button
                                        @click.stop="toggleDropdown(user, $event)"
                                        :aria-label="'Actions for ' + user.name"
                                        class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-400 transition group"
                                        :data-dropdown-trigger="user.id"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <circle cx="10" cy="4" r="2.2"/>
                                            <circle cx="10" cy="10" r="2.2"/>
                                            <circle cx="10" cy="16" r="2.2"/>
                                        </svg>
                                        <span class="absolute left-1/2 -bottom-8 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
                                            Actions
                                        </span>
                                    </button>
                                </div>

                            </div>
                            <div v-if="filteredUsers.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm">
                                {{ searchQuery ? 'No users found matching your search.' : 'No users found.' }}
                            </div>
                        </div>
                        
                        <!-- Desktop Table View (hidden on mobile) -->
                        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
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
                                            <div class="flex justify-center">
                                                <button
                                                    @click.stop="toggleDropdown(user, $event)"
                                                    :aria-label="'Actions for ' + user.name"
                                                    class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-400 transition group"
                                                    :data-dropdown-trigger="user.id"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <circle cx="10" cy="4" r="2.2"/>
                                                        <circle cx="10" cy="10" r="2.2"/>
                                                        <circle cx="10" cy="16" r="2.2"/>
                                                    </svg>
                                                    <span class="absolute left-1/2 -bottom-8 transform -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white dark:text-gray-200 text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity duration-300 whitespace-nowrap z-50">
                                                        Actions
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredUsers.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            {{ searchQuery ? 'No users found matching your search.' : 'No users found.' }}
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
        <Modal :show="showingCreateModal" @close="cancelCreate" max-width="lg">
            <div class="p-3 sm:p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center mb-3 sm:mb-4">
                    <div class="bg-blue-500 p-2 rounded-lg mr-2 sm:mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">
                        Create New User
                    </h2>
                </div>

                <form @submit.prevent="createUser" class="mt-3 sm:mt-6 space-y-3 sm:space-y-4">
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
                            autocomplete="email"
                            placeholder="Enter user's email"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" class="text-sm" />
                        <div class="relative">
                            <TextInput
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="mt-1 block w-full text-sm pr-10"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                placeholder="Enter password (minimum 8 characters)"
                                @input="() => { validationErrors.password = ''; validationErrors.password_confirmation = ''; }"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="validationErrors.password || form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-sm" />
                        <div class="relative">
                            <TextInput
                                id="password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                class="mt-1 block w-full text-sm pr-10"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Re-enter password"
                                @input="validationErrors.password_confirmation = ''"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="validationErrors.password_confirmation || form.errors.password_confirmation" />
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

                    <div class="flex flex-col sm:flex-row items-center justify-end pt-3 sm:pt-4 border-t border-gray-100 dark:border-gray-700 gap-2">
                        <SecondaryButton type="button" @click="cancelCreate" class="w-full sm:w-auto order-2 sm:order-1">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="w-full sm:w-auto order-1 sm:order-2">
                            Create User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit User Modal -->
        <Modal :show="showingEditModal" @close="cancelEdit" max-width="lg">
            <div class="p-3 sm:p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center mb-3 sm:mb-4">
                    <div class="bg-blue-500 p-2 rounded-lg mr-2 sm:mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">
                        Edit User
                    </h2>
                </div>

                <form @submit.prevent="updateUser" class="mt-3 sm:mt-6 space-y-3 sm:space-y-4">
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

                    <div class="flex flex-col sm:flex-row items-center justify-end pt-3 sm:pt-4 border-t border-gray-100 dark:border-gray-700 gap-2">
                        <SecondaryButton type="button" @click="cancelEdit" class="w-full sm:w-auto order-2 sm:order-1">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing" class="w-full sm:w-auto order-1 sm:order-2">
                            Update User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        
        <!-- Role Change Confirmation Modal -->
        <Modal :show="showingRoleChangeConfirmModal" @close="cancelRoleChange" max-width="lg">
            <div class="p-3 sm:p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center mb-3 sm:mb-4">
                    <div class="bg-amber-500 p-2 rounded-lg mr-2 sm:mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-100">
                        Confirm Role Change
                    </h2>
                </div>

                <div v-if="userToEdit" class="space-y-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Are you sure you want to change <strong class="text-gray-900 dark:text-gray-100">{{ userToEdit.name }}'s</strong> role?
                    </p>

                    <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 p-3 rounded-md">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-amber-500 mt-0.5 mr-2 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-amber-800 dark:text-amber-200">
                                <p class="font-medium mb-1">Role Change Details:</p>
                                <div class="space-y-1">
                                    <p><span class="font-medium">Current Role:</span> <span class="px-2 py-0.5 rounded text-xs bg-amber-100 dark:bg-amber-800">{{ roles.find(r => r.id === originalRoleId)?.name }}</span></p>
                                    <p><span class="font-medium">New Role:</span> <span class="px-2 py-0.5 rounded text-xs bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200">{{ roles.find(r => r.id === editForm.role_id)?.name }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-md border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            <strong>Note:</strong> Changing a user's role will affect their permissions and access to system features. This change takes effect immediately.
                        </p>
                    </div>
                </div>

                <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-end gap-2">
                    <SecondaryButton 
                        type="button" 
                        @click="cancelRoleChange"
                        class="w-full sm:w-auto order-2 sm:order-1"
                    >
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        type="button"
                        @click="proceedWithUpdate"
                        :class="{ 'opacity-25': editForm.processing }" 
                        :disabled="editForm.processing"
                        class="w-full sm:w-auto order-1 sm:order-2 bg-amber-600 hover:bg-amber-700 focus:bg-amber-700"
                    >
                        Confirm Role Change
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
        
        <!-- Delete User Confirmation Modal -->
        <Modal :show="showingDeleteModal" @close="() => { showingDeleteModal = false; deleteConfirmation = ''; }" max-width="lg">
            <div class="p-3 sm:p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center mb-3 sm:mb-4">
                    <div class="bg-red-500 p-2 rounded-lg mr-2 sm:mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-medium text-gray-900 dark:text-gray-200">
                        Delete User
                    </h2>
                </div>

                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>

                <div v-if="userToDelete" class="mt-4 p-3 sm:p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <p class="mb-1 text-sm"><span class="font-medium text-gray-800 dark:text-gray-100">Name:</span> <span class="text-gray-700 dark:text-gray-200">{{ userToDelete.name }}</span></p>
                    <p class="mb-1 text-sm"><span class="font-medium text-gray-800 dark:text-gray-100">Email:</span> <span class="break-all text-gray-700 dark:text-gray-200">{{ userToDelete.email }}</span></p>
                    <p v-if="userToDelete.role" class="text-sm"><span class="font-medium text-gray-800 dark:text-gray-100">Role:</span> <span class="text-gray-700 dark:text-gray-200">{{ userToDelete.role.name }}</span></p>
                </div>

                <!-- Confirmation input -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-800 dark:text-gray-100 mb-2">Confirmation</label>
                    <input
                        v-model="deleteConfirmation"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                        :placeholder="userToDelete ? `Type '${userToDelete.email}' to confirm` : ''"
                    />
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                        Type <strong class="break-all">{{ userToDelete ? userToDelete.email : '' }}</strong> to confirm this action
                    </p>
                </div>

                <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row justify-end gap-2">
                    <button
                        type="button"
                        @click="() => { showingDeleteModal = false; deleteConfirmation = ''; }"
                        class="w-full sm:w-auto order-2 sm:order-1 inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600 shadow-sm"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="deleteUser"
                        :disabled="deleteForm.processing || deleteConfirmation !== (userToDelete ? userToDelete.email : '')"
                        :class="['w-full sm:w-auto order-1 sm:order-2 inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 bg-red-600 text-white hover:bg-red-700 border border-red-600 shadow-sm', { 'opacity-25 cursor-not-allowed': deleteForm.processing || deleteConfirmation !== (userToDelete ? userToDelete.email : '') }]"
                    >
                        Delete User
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>

    <!-- Floating Desktop Dropdown (similar to ApplicationsTable) -->
    <Teleport to="body">
        <div 
            ref="dropdownRef"
            v-if="activeDropdownUser"
            class="dropdown-container fixed z-50 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-40"
            :style="{ top: `${dropdownPosition.top}px`, left: `${dropdownPosition.left}px`, visibility: activeDropdownUser ? 'visible' : 'hidden' }"
            @click.stop
        >
            <button 
                @click="handleDropdownAction(activeDropdownUser, 'edit')"
                class="w-full text-left px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-center gap-2 transition duration-200"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </button>
            <button 
                v-if="activeDropdownUser && activeDropdownUser.id !== $page.props.auth.user.id"
                @click="handleDropdownAction(activeDropdownUser, 'delete')" 
                class="w-full text-left px-3 py-1.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 flex items-center gap-2 transition duration-200 border-t border-gray-100 dark:border-gray-600 mt-1 pt-1"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
            </button>
        </div>
    </Teleport>

    <!-- Mobile Actions Modal -->
    <Teleport to="body">
        <transition
            enter-active-class="transition-opacity ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showMobileActionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-end justify-center z-50" @click="closeMobileActionsModal">
                <transition
                    enter-active-class="transition-transform ease-out duration-250"
                    enter-from-class="translate-y-full"
                    enter-to-class="translate-y-0"
                    leave-active-class="transition-transform ease-in duration-200"
                    leave-from-class="translate-y-0"
                    leave-to-class="translate-y-full"
                >
                    <div v-if="showMobileActionsModal" class="bg-white dark:bg-gray-800 w-full max-w-sm rounded-t-lg shadow-xl" @click.stop>
                <!-- Modal Header -->
                <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-2">
                        <template v-if="selectedMobileUser && selectedMobileUser.profile_photo_url">
                            <img
                                :src="selectedMobileUser.profile_photo_url"
                                alt="Avatar"
                                class="w-8 h-8 rounded-full object-cover border border-blue-200 shadow-sm"
                            />
                        </template>
                        <template v-else-if="selectedMobileUser">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-green-400 flex items-center justify-center text-white font-medium shadow-inner text-xs">
                                {{ selectedMobileUser.name.charAt(0).toUpperCase() }}
                            </div>
                        </template>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ selectedMobileUser ? selectedMobileUser.name : '' }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ selectedMobileUser ? selectedMobileUser.email : '' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Actions -->
                <div class="py-1">
                    <button 
                        @click="handleMobileAction('edit')"
                        class="w-full flex items-center px-3 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-150 active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="text-sm text-gray-900 dark:text-gray-100">Edit User</span>
                    </button>
                    
                    <button 
                        v-if="selectedMobileUser && selectedMobileUser.id !== $page.props.auth.user.id"
                        @click="handleMobileAction('delete')"
                        class="w-full flex items-center px-3 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-150 active:scale-[0.98]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600 dark:text-red-400 mr-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span class="text-sm text-red-600 dark:text-red-400">Delete User</span>
                    </button>
                </div>
                
                <!-- Cancel Button -->
                <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-700">
                    <button 
                        @click="closeMobileActionsModal"
                        class="w-full py-1.5 text-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors duration-200"
                    >
                        Cancel
                    </button>
                </div>
            </div>
                </transition>
            </div>
        </transition>
    </Teleport>

    <!-- Back to top floating button -->
    <button
        v-if="showBackToTop"
        @click="scrollToTop"
        aria-label="Back to top"
        class="fixed z-50 right-6 bottom-8 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 shadow-lg hover:shadow-2xl rounded-full p-3 transition transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-500"
        title="Back to top"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 5a1 1 0 01.707.293l5 5a1 1 0 01-1.414 1.414L10 7.414 5.707 11.707A1 1 0 014.293 10.293l5-5A1 1 0 0110 5z" clip-rule="evenodd" />
        </svg>
    </button>
</template>