<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import Modal from '@/Components/Modal.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
console.log('User data:', user);
console.log('User social_links:', user.social_links);
console.log('Type of social_links:', typeof user.social_links);

// Add for name change restriction
const lastNameChangeAt = user.last_name_change_at ? new Date(user.last_name_change_at) : null;

const canChangeName = computed(() => {
    if (!lastNameChangeAt) return true;
    const now = new Date();
    const msLeft = (lastNameChangeAt.getTime() + 14 * 24 * 60 * 60 * 1000) - now.getTime();
    return msLeft <= 0;
});

const nextAllowedDate = computed(() => {
    if (!lastNameChangeAt) return null;
    return new Date(lastNameChangeAt.getTime() + 14 * 24 * 60 * 60 * 1000);
});

const daysLeft = computed(() => {
    if (!lastNameChangeAt) return 0;
    const now = new Date();
    const msLeft = (lastNameChangeAt.getTime() + 14 * 24 * 60 * 60 * 1000) - now.getTime();
    return msLeft > 0 ? Math.floor(msLeft / (1000 * 60 * 60 * 24)) : 0;
});

const hoursLeft = computed(() => {
    if (!lastNameChangeAt) return 0;
    const now = new Date();
    const msLeft = (lastNameChangeAt.getTime() + 14 * 24 * 60 * 60 * 1000) - now.getTime();
    return msLeft > 0 ? Math.floor((msLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)) : 0;
});

// Check for admin role based on role property structure
const isAdmin = user.role && 
    (user.role === 'admin' || 
     user.role.name === 'admin' || 
     (typeof user.role === 'object' && user.role.id === 1));

console.log('Is admin:', isAdmin);

const form = useForm({
    name: user.name,
    email: user.email,
    profile_photo: null,
    description: user.description || '',
    social_links: user.social_links && Array.isArray(user.social_links) && user.social_links.length > 0 
        ? [...user.social_links] 
        : [{ platform: '', url: '' }],
});

const photoPreview = ref(user.profile_photo_url);
const removeProfilePhoto = ref(false);

// Helper function to get URL for a specific platform
const getSocialUrl = (platform) => {
    if (!platform || !form.social_links || !Array.isArray(form.social_links)) {
        return '';
    }
    const link = form.social_links.find(link => 
        link && 
        link.platform && 
        typeof link.platform === 'string' && 
        link.platform.toLowerCase() === platform.toLowerCase()
    );
    return link && link.url ? link.url : '';
};

// Helper function to set URL for a specific platform
const setSocialUrl = (platform, url) => {
    if (!platform || !form.social_links || !Array.isArray(form.social_links)) {
        return;
    }
    
    const existingIndex = form.social_links.findIndex(link => 
        link && 
        link.platform && 
        typeof link.platform === 'string' && 
        link.platform.toLowerCase() === platform.toLowerCase()
    );
    
    if (existingIndex !== -1) {
        if (url && url.trim() !== '') {
            form.social_links[existingIndex].url = url;
        } else {
            // Remove the link if URL is empty
            form.social_links.splice(existingIndex, 1);
        }
    } else if (url && url.trim() !== '') {
        // Add new link if URL is provided
        form.social_links.push({ platform: platform, url: url });
    }
    
    // Ensure we always have at least one empty link for the original UI compatibility
    if (form.social_links.length === 0) {
        form.social_links.push({ platform: '', url: '' });
    }
};

// Social links modal management
const showSocialModal = ref(false);
const currentPlatform = ref('');
const currentUrl = ref('');

function openSocialModal(platform) {
    currentPlatform.value = platform;
    currentUrl.value = getSocialUrl(platform);
    showSocialModal.value = true;
}

function saveSocialLink() {
    setSocialUrl(currentPlatform.value, currentUrl.value);
    showSocialModal.value = false;
}

function removeSocialLink() {
    setSocialUrl(currentPlatform.value, '');
    showSocialModal.value = false;
}

function cancelSocialModal() {
    showSocialModal.value = false;
    currentUrl.value = '';
}

function handlePhotoChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.profile_photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
}

function handleRemovePhoto() {
    removeProfilePhoto.value = true;
    form.profile_photo = '__REMOVE__';
    photoPreview.value = null;
}

function submit() {
    const data = { ...form.data() };
    if (removeProfilePhoto.value) {
        data.remove_profile_photo = true;
        data.profile_photo = null;
    }
    
    // Filter out empty social links before submission
    data.social_links = form.social_links.filter(link => 
        link.platform && link.platform.trim() !== '' && 
        link.url && link.url.trim() !== ''
    );
    
    console.log('Submitting profile data:', data);
    
    form.post(route('profile.update'), {
        data,
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            console.log('Profile update successful');
            removeProfilePhoto.value = false;
            photoPreview.value = usePage().props.auth.user.profile_photo_url;
            router.reload({ only: ['auth'] });
            isEditingProfile.value = false; // Return to disabled state after save
        },
        onError: (errors) => {
            console.log('Profile update errors:', errors);
        },
        _method: 'patch',
    });
}

const isEditingDescription = ref(false);
const originalDescription = ref(user.description || '');

// Sync form.description and originalDescription with user.description on mount and when user.description changes
onMounted(() => {
    form.description = user.description || '';
    originalDescription.value = user.description || '';
    // Ensure social_links are properly initialized
    if (!user.social_links || !Array.isArray(user.social_links) || user.social_links.length === 0) {
        form.social_links = [{ platform: '', url: '' }];
    } else {
        // Deep copy to avoid reactivity issues
        form.social_links = user.social_links.map(link => ({ ...link }));
    }
});

watch(
  () => user.description,
  (newVal) => {
    form.description = newVal || '';
    originalDescription.value = newVal || '';
  }
);

watch(
  () => user.social_links,
  (newVal) => {
    if (!newVal || !Array.isArray(newVal) || newVal.length === 0) {
        form.social_links = [{ platform: '', url: '' }];
    } else {
        // Deep copy to avoid reactivity issues
        form.social_links = newVal.map(link => ({ ...link }));
    }
  }
);

function startEditDescription() {
    isEditingDescription.value = true;
}

function cancelEditDescription() {
    form.description = originalDescription.value;
    isEditingDescription.value = false;
}

function saveDescription() {
    // Only submit the description field
    form.post(route('profile.update'), {
        data: { description: form.description },
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            originalDescription.value = form.description;
            isEditingDescription.value = false;
            router.reload({ only: ['auth'] }); // Reload user data after save
        },
        _method: 'patch',
    });
}

// Remove description logic for admins
const showDescription = !isAdmin;

const isEditingProfile = ref(false);

function startEditProfile() {
    isEditingProfile.value = true;
}

function cancelEditProfile() {
    isEditingProfile.value = false;
    // Reset form fields to original user data
    form.name = user.name;
    form.email = user.email;
    form.description = user.description || '';
    // Reset social_links to original user data or default empty link
    if (user.social_links && Array.isArray(user.social_links) && user.social_links.length > 0) {
        form.social_links = user.social_links.map(link => ({ ...link }));
    } else {
        form.social_links = [{ platform: '', url: '' }];
    }
    photoPreview.value = user.profile_photo_url;
    removeProfilePhoto.value = false;
}

const showConfirmModal = ref(false);

function handleSaveClick() {
    showConfirmModal.value = true;
}

function confirmSave() {
    showConfirmModal.value = false;
    isEditingProfile.value = false; // Immediately exit edit mode
    submit();
}

function cancelSave() {
    showConfirmModal.value = false;
}
</script>

<template>
    <section class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-100 dark:border-gray-700">
        <!-- Specific section description with colored accent -->
        <div class="border-l-4 border-blue-500 pl-3 mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Manage your personal details, profile photo, and organization description. <span v-if="isAdmin">As an admin, you can also update your name and email address.</span>
            </p>
        </div>
        <!-- Status bar for verification status -->
        <div 
            v-if="mustVerifyEmail && user.email_verified_at === null"
            class="mb-6 bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-500 p-4 rounded-md"
        >
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        Your email address is unverified.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="font-medium text-blue-500 dark:text-blue-400 underline hover:text-blue-700 dark:hover:text-blue-300 focus:outline-none"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>
                    <p
                        v-show="status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-500 dark:text-green-400"
                    >
                        A new verification link has been sent to your email address.
                    </p>
                </div>
            </div>
        </div>

                <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                <div>
                    <InputLabel for="name" value="Name" class="text-gray-700 dark:text-gray-300 font-medium" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300"
                        v-model="form.name"
                        :disabled="isAdmin ? !isEditingProfile : (!isEditingProfile || !canChangeName)"
                        :class="(isAdmin ? !isEditingProfile : (!isEditingProfile || !canChangeName)) ? 'bg-gray-100 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed select-none pointer-events-none' : ''"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                    <div v-if="!isAdmin && !canChangeName" class="text-xs text-red-500 dark:text-red-400 mt-1">
                        Name can only be changed once every 14 days. {{ daysLeft > 0 ? `${daysLeft} days and ${hoursLeft} hours remaining.` : 'You can change your name now.' }}
                    </div>
                </div>

                <div>
                    <InputLabel for="email" value="Email" class="text-gray-700 dark:text-gray-300 font-medium" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300"
                        v-model="form.email"
                        :disabled="!isEditingProfile"
                        :class="!isEditingProfile ? 'bg-gray-100 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed select-none pointer-events-none' : ''"
                        required
                        autocomplete="email"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
            </div>

            <div v-if="showDescription">
                <InputLabel for="description" value="Organization Description" class="text-gray-700 dark:text-gray-300 font-medium" />
                <div v-if="!isEditingDescription && (!isAdmin && !isEditingProfile)" class="flex items-center justify-between group">
                    <div class="text-gray-800 dark:text-gray-200 min-h-[2.5rem]">
                        <span v-if="form.description">{{ form.description }}</span>
                        <span v-else class="italic text-gray-400 dark:text-gray-500">No description available</span>
                    </div>
                </div>
                <div v-else-if="!isEditingDescription && (isAdmin || isEditingProfile)" class="flex items-center justify-between group">
                    <div class="text-gray-800 dark:text-gray-200 min-h-[2.5rem]">
                        <span v-if="form.description">{{ form.description }}</span>
                        <span v-else class="italic text-gray-400 dark:text-gray-500">No description available</span>
                    </div>
                    <button type="button" @click="startEditDescription" class="ml-2 text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 opacity-70 group-hover:opacity-100 transition p-1 rounded-full" title="Edit Description">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" />
                        </svg>
                    </button>
                </div>
                <div v-else>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        :disabled="!isAdmin && !isEditingProfile"
                        :class="(!isAdmin && !isEditingProfile) ? 'bg-gray-100 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed select-none pointer-events-none' : ''"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm resize-none dark:bg-gray-700 dark:text-gray-300"
                        placeholder="Write a short description about your organization..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                    <div class="flex gap-2 mt-2" v-if="isAdmin || isEditingProfile">
                        <button type="button" @click="saveDescription" class="px-4 py-1 bg-blue-500 dark:bg-blue-600 text-white rounded hover:bg-blue-600 dark:hover:bg-blue-700 text-sm font-medium">Save</button>
                        <button type="button" @click="cancelEditDescription" class="px-4 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 text-sm font-medium">Cancel</button>
                    </div>
                </div>
            </div>

            <div>
                <InputLabel value="Profile Photo" class="text-gray-700 dark:text-gray-300 font-medium" />
                <div class="mt-2 p-4 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-6">
                        <!-- Profile Photo Preview -->
                        <div class="flex-shrink-0">
                            <div class="relative group">
                                <img 
                                    :src="photoPreview || '/images/lspu_logo_better.png'" 
                                    class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-4 border-blue-200 dark:border-blue-600 shadow-md transition-all duration-200 group-hover:border-blue-300 dark:group-hover:border-blue-500" 
                                />
                                <div 
                                    v-if="photoPreview"
                                    class="absolute inset-0 bg-black bg-opacity-40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center"
                                >
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <!-- Photo Controls -->
                        <div class="flex-1 w-full sm:w-auto space-y-3">
                            <!-- Choose Photo Button -->
                            <div class="relative w-full">
                                <input 
                                    type="file" 
                                    accept="image/*" 
                                    @change="handlePhotoChange" 
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    id="profile-photo-input"
                                    :disabled="!isEditingProfile"
                                />
                                <label 
                                    for="profile-photo-input"
                                    :class="['inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group cursor-pointer', !isEditingProfile ? 'opacity-50 pointer-events-none cursor-not-allowed' : '']"
                                >
                                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    Choose Photo
                                </label>
                            </div>
                            <!-- Remove Photo Button -->
                            <button
                                v-if="(user.profile_photo_url || photoPreview) && isEditingProfile"
                                type="button"
                                @click="handleRemovePhoto"
                                class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-500 hover:border-red-300 hover:text-red-600 dark:hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove Photo
                            </button>
                            <!-- Photo Guidelines -->
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center sm:text-left">
                                Recommended: Square image, at least 200x200 pixels. Maximum file size: 5MB.
                            </p>
                        </div>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.profile_photo" />
            </div>

            <!-- Social Links Section -->
            <div>
                <InputLabel value="Social Links" class="text-gray-700 dark:text-gray-300 font-medium" />
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Click on the icons to add or edit your social media links.</p>
                
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 p-4 sm:p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                    <!-- Facebook -->
                    <button
                        type="button"
                        @click="openSocialModal('facebook')"
                        :disabled="!isEditingProfile"
                        :class="[
                            'relative group p-2 sm:p-3 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex-shrink-0',
                            getSocialUrl('facebook') ? 'bg-blue-600 text-white shadow-lg' : 'bg-white dark:bg-gray-600 text-gray-400 dark:text-gray-300 shadow-md hover:shadow-lg',
                            !isEditingProfile ? 'opacity-50 cursor-not-allowed' : 'hover:scale-110'
                        ]"
                        title="Facebook"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <div v-if="getSocialUrl('facebook')" class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 w-2.5 h-2.5 sm:w-3 sm:h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-700"></div>
                    </button>

                    <!-- X (Twitter) -->
                    <button
                        type="button"
                        @click="openSocialModal('twitter')"
                        :disabled="!isEditingProfile"
                        :class="[
                            'relative group p-2 sm:p-3 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex-shrink-0',
                            getSocialUrl('twitter') ? 'bg-black text-white shadow-lg' : 'bg-white dark:bg-gray-600 text-gray-400 dark:text-gray-300 shadow-md hover:shadow-lg',
                            !isEditingProfile ? 'opacity-50 cursor-not-allowed' : 'hover:scale-110'
                        ]"
                        title="X (Twitter)"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                        <div v-if="getSocialUrl('twitter')" class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 w-2.5 h-2.5 sm:w-3 sm:h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-700"></div>
                    </button>

                    <!-- Instagram -->
                    <button
                        type="button"
                        @click="openSocialModal('instagram')"
                        :disabled="!isEditingProfile"
                        :class="[
                            'relative group p-2 sm:p-3 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex-shrink-0',
                            getSocialUrl('instagram') ? 'bg-gradient-to-br from-purple-600 to-pink-500 text-white shadow-lg' : 'bg-white dark:bg-gray-600 text-gray-400 dark:text-gray-300 shadow-md hover:shadow-lg',
                            !isEditingProfile ? 'opacity-50 cursor-not-allowed' : 'hover:scale-110'
                        ]"
                        title="Instagram"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        <div v-if="getSocialUrl('instagram')" class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 w-2.5 h-2.5 sm:w-3 sm:h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-700"></div>
                    </button>

                    <!-- LinkedIn -->
                    <button
                        type="button"
                        @click="openSocialModal('linkedin')"
                        :disabled="!isEditingProfile"
                        :class="[
                            'relative group p-2 sm:p-3 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex-shrink-0',
                            getSocialUrl('linkedin') ? 'bg-blue-700 text-white shadow-lg' : 'bg-white dark:bg-gray-600 text-gray-400 dark:text-gray-300 shadow-md hover:shadow-lg',
                            !isEditingProfile ? 'opacity-50 cursor-not-allowed' : 'hover:scale-110'
                        ]"
                        title="LinkedIn"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                        <div v-if="getSocialUrl('linkedin')" class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 w-2.5 h-2.5 sm:w-3 sm:h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-700"></div>
                    </button>

                    <!-- Other/External Link -->
                    <button
                        type="button"
                        @click="openSocialModal('other')"
                        :disabled="!isEditingProfile"
                        :class="[
                            'relative group p-2 sm:p-3 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex-shrink-0',
                            getSocialUrl('other') ? 'bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 shadow-lg' : 'bg-white dark:bg-gray-600 text-gray-400 dark:text-gray-300 shadow-md hover:shadow-lg',
                            !isEditingProfile ? 'opacity-50 cursor-not-allowed' : 'hover:scale-110'
                        ]"
                        title="Other Link"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <div v-if="getSocialUrl('other')" class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 w-2.5 h-2.5 sm:w-3 sm:h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-700"></div>
                    </button>
                </div>
                
                <!-- Display active links when not editing -->
                <div v-if="!isEditingProfile && (getSocialUrl('facebook') || getSocialUrl('twitter') || getSocialUrl('instagram') || getSocialUrl('linkedin') || getSocialUrl('other'))" class="mt-4 space-y-2">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Links:</p>
                    <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <div v-if="getSocialUrl('facebook')" class="flex items-start space-x-2">
                            <span class="w-16 sm:w-20 flex-shrink-0 font-medium">Facebook:</span>
                            <a :href="getSocialUrl('facebook')" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline break-all">{{ getSocialUrl('facebook') }}</a>
                        </div>
                        <div v-if="getSocialUrl('twitter')" class="flex items-start space-x-2">
                            <span class="w-16 sm:w-20 flex-shrink-0 font-medium">X (Twitter):</span>
                            <a :href="getSocialUrl('twitter')" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline break-all">{{ getSocialUrl('twitter') }}</a>
                        </div>
                        <div v-if="getSocialUrl('instagram')" class="flex items-start space-x-2">
                            <span class="w-16 sm:w-20 flex-shrink-0 font-medium">Instagram:</span>
                            <a :href="getSocialUrl('instagram')" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline break-all">{{ getSocialUrl('instagram') }}</a>
                        </div>
                        <div v-if="getSocialUrl('linkedin')" class="flex items-start space-x-2">
                            <span class="w-16 sm:w-20 flex-shrink-0 font-medium">LinkedIn:</span>
                            <a :href="getSocialUrl('linkedin')" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline break-all">{{ getSocialUrl('linkedin') }}</a>
                        </div>
                        <div v-if="getSocialUrl('other')" class="flex items-start space-x-2">
                            <span class="w-16 sm:w-20 flex-shrink-0 font-medium">Other:</span>
                            <a :href="getSocialUrl('other')" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline break-all">{{ getSocialUrl('other') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <Modal :show="showConfirmModal" @close="cancelSave">
                <div class="p-4 sm:p-6 flex flex-col items-center justify-center min-h-[180px] bg-white dark:bg-gray-800 max-w-md mx-auto">
                    <h2 class="text-lg font-semibold mb-4 text-center text-gray-900 dark:text-gray-100">Confirm Changes</h2>
                    <p class="mb-6 text-center text-gray-700 dark:text-gray-300 text-sm sm:text-base px-2">Are you sure you want to save these changes to your profile?</p>
                    <p v-if="!isAdmin" class="mb-4 text-sm text-blue-500 dark:text-blue-400 text-center px-2">
                        Note: You can only change your name once every 14 days.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-2 sm:gap-3 w-full">
                        <button @click="cancelSave" type="button"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-xl shadow-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group">
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            Cancel
                        </button>
                        <button @click="confirmSave" type="button"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-500 dark:bg-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group">
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                            Yes, Save
                        </button>
                    </div>
                </div>
            </Modal>

            <!-- Social Links Modal -->
                        <!-- Social Links Modal -->
            <Modal :show="showSocialModal" @close="cancelSocialModal">
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 max-w-md mx-auto">
                    <h2 class="text-lg font-semibold mb-4 text-center text-gray-900 dark:text-gray-100 capitalize">
                        Edit {{ currentPlatform }} Link
                    </h2>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            URL
                        </label>
                        <input
                            v-model="currentUrl"
                            type="url"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100 text-sm"
                            :placeholder="`Enter your ${currentPlatform} URL`"
                        />
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between gap-2 sm:gap-3">
                        <button
                            type="button"
                            @click="saveSocialLink"
                            class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                        >
                            Save
                        </button>
                        <button
                            v-if="getSocialUrl(currentPlatform)"
                            type="button"
                            @click="removeSocialLink"
                            class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                        >
                            Remove
                        </button>
                        <button
                            type="button"
                            @click="cancelSocialModal"
                            class="w-full sm:w-auto px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </Modal>
            <div class="flex flex-col sm:flex-row items-center pt-4 border-t border-gray-100 dark:border-gray-700 gap-2 sm:gap-0">
                <PrimaryButton 
                    v-if="isEditingProfile"
                    :disabled="form.processing"
                    class="w-full sm:w-auto bg-blue-500 hover:bg-blue-600 focus:bg-blue-600"
                    @click.prevent="handleSaveClick"
                >
                    Save Changes
                </PrimaryButton>
                <button
                    v-if="isEditingProfile"
                    type="button"
                    @click="cancelEditProfile"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-sm font-medium text-gray-700 rounded-xl shadow-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group sm:ml-2"
                >
                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                    Cancel
                </button>
                <PrimaryButton
                    v-if="!isEditingProfile"
                    @click="startEditProfile"
                    type="button"
                    class="w-full sm:w-auto"
                >
                    Edit Profile
                </PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="ml-0 sm:ml-4 mt-2 sm:mt-0 text-sm text-green-500 flex items-center"
                    >
                        <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Saved successfully
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>