<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';

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

// Add for name change restriction
const lastNameChangeAt = user.last_name_change_at ? new Date(user.last_name_change_at) : null;
const now = new Date();
let canChangeName = true;
let nextAllowedDate = null;
let daysLeft = 0;
let hoursLeft = 0;
if (lastNameChangeAt) {
    const msLeft = (lastNameChangeAt.getTime() + 14 * 24 * 60 * 60 * 1000) - now.getTime();
    if (msLeft > 0) {
        canChangeName = false;
        nextAllowedDate = new Date(lastNameChangeAt.getTime() + 14 * 24 * 60 * 60 * 1000);
        daysLeft = Math.floor(msLeft / (1000 * 60 * 60 * 24));
        hoursLeft = Math.floor((msLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    }
}

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
});

const photoPreview = ref(user.profile_photo_url);
const removeProfilePhoto = ref(false);

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
    form.post(route('profile.update'), {
        data,
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            removeProfilePhoto.value = false;
            photoPreview.value = usePage().props.auth.user.profile_photo_url;
            router.reload({ only: ['auth'] });
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
});

watch(
  () => user.description,
  (newVal) => {
    form.description = newVal || '';
    originalDescription.value = newVal || '';
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
    photoPreview.value = user.profile_photo_url;
    removeProfilePhoto.value = false;
}
</script>

<template>
    <section class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
        <!-- Specific section description with colored accent -->
        <div class="border-l-4 border-blue-500 pl-3 mb-6">
            <p class="text-sm text-gray-600">
                Manage your personal details, profile photo, and organization description. <span v-if="isAdmin">As an admin, you can also update your name and email address.</span>
            </p>
        </div>
        <!-- Status bar for verification status -->
        <div 
            v-if="mustVerifyEmail && user.email_verified_at === null"
            class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-md"
        >
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Your email address is unverified.
                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="font-medium text-blue-500 underline hover:text-blue-700 focus:outline-none"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>
                    <p
                        v-show="status === 'verification-link-sent'"
                        class="mt-2 text-sm font-medium text-green-500"
                    >
                        A new verification link has been sent to your email address.
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="name" value="Name" class="text-gray-700 font-medium" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        v-model="form.name"
                        :disabled="(!isAdmin && !isEditingProfile) || (!isAdmin && !canChangeName)"
                        :class="((!isAdmin && !isEditingProfile) || (!isAdmin && !canChangeName)) ? 'bg-gray-100 text-gray-400 cursor-not-allowed select-none pointer-events-none' : ''"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                    <div v-if="!isAdmin && !canChangeName" class="text-xs text-red-500 mt-1">
                        <span v-if="daysLeft > 0 || hoursLeft > 0">
                            You can change your name in <span class="font-semibold">{{ daysLeft }}</span> day<span v-if="daysLeft !== 1">s</span>
                            <span v-if="hoursLeft > 0"> and <span class="font-semibold">{{ hoursLeft }}</span> hour<span v-if="hoursLeft !== 1">s</span></span>.
                        </span>
                        <br/>
                        Next allowed change: <span class="font-semibold">{{ nextAllowedDate ? nextAllowedDate.toLocaleDateString() : '' }}</span>
                    </div>
                </div>

                <div>
                    <InputLabel for="email" value="Email" class="text-gray-700 font-medium" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        v-model="form.email"
                        :disabled="!isAdmin"
                        :class="!isAdmin ? 'bg-gray-100 text-gray-400 cursor-not-allowed select-none pointer-events-none' : ''"
                        required
                        autocomplete="email"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
            </div>

            <div v-if="showDescription">
                <InputLabel for="description" value="Organization Description" class="text-gray-700 font-medium" />
                <div v-if="!isEditingDescription && (!isAdmin && !isEditingProfile)" class="flex items-center justify-between group">
                    <div class="text-gray-800 min-h-[2.5rem]">
                        <span v-if="form.description">{{ form.description }}</span>
                        <span v-else class="italic text-gray-400">No description available</span>
                    </div>
                </div>
                <div v-else-if="!isEditingDescription && (isAdmin || isEditingProfile)" class="flex items-center justify-between group">
                    <div class="text-gray-800 min-h-[2.5rem]">
                        <span v-if="form.description">{{ form.description }}</span>
                        <span v-else class="italic text-gray-400">No description available</span>
                    </div>
                    <button type="button" @click="startEditDescription" class="ml-2 text-blue-500 hover:text-blue-700 opacity-70 group-hover:opacity-100 transition p-1 rounded-full" title="Edit Description">
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
                        :class="(!isAdmin && !isEditingProfile) ? 'bg-gray-100 text-gray-400 cursor-not-allowed select-none pointer-events-none' : ''"
                        class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm resize-none"
                        placeholder="Write a short description about your organization..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                    <div class="flex gap-2 mt-2" v-if="isAdmin || isEditingProfile">
                        <button type="button" @click="saveDescription" class="px-4 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm font-medium">Save</button>
                        <button type="button" @click="cancelEditDescription" class="px-4 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm font-medium">Cancel</button>
                    </div>
                </div>
            </div>

            <div>
                <InputLabel value="Profile Photo" class="text-gray-700 font-medium" />
                <div class="mt-2 p-4 border border-gray-300 rounded-md bg-gray-50">
                    <div class="flex items-center gap-6">
                        <!-- Profile Photo Preview -->
                        <div class="flex-shrink-0">
                            <div class="relative group">
                                <img 
                                    :src="photoPreview || '/images/lspu_logo_better.png'" 
                                    class="w-24 h-24 rounded-full object-cover border-4 border-blue-200 shadow-md transition-all duration-200 group-hover:border-blue-300" 
                                />
                                <div 
                                    v-if="photoPreview"
                                    class="absolute inset-0 bg-black bg-opacity-40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center"
                                >
                                    <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <!-- Photo Controls -->
                        <div class="flex-1 space-y-3">
                            <!-- Choose Photo Button -->
                            <div class="relative">
                                <input 
                                    type="file" 
                                    accept="image/*" 
                                    @change="handlePhotoChange" 
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    id="profile-photo-input"
                                    :disabled="!isAdmin && !isEditingProfile"
                                />
                                <label 
                                    for="profile-photo-input"
                                    :class="['inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-sm font-medium text-white rounded-xl shadow-md hover:shadow-blue-300/30 hover:from-blue-400 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:from-blue-600 active:to-blue-700 transition-all duration-300 relative overflow-hidden group cursor-pointer', (!isAdmin && !isEditingProfile) ? 'opacity-50 cursor-not-allowed pointer-events-none' : '']"
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
                                v-if="(user.profile_photo_url || photoPreview) && (isAdmin || isEditingProfile)"
                                type="button"
                                @click="handleRemovePhoto"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 hover:border-red-300 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove Photo
                            </button>
                            <!-- Photo Guidelines -->
                            <p class="text-xs text-gray-500 mt-2">
                                Recommended: Square image, at least 200x200 pixels. Maximum file size: 2MB.
                            </p>
                        </div>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.profile_photo" />
            </div>
            <div class="flex items-center pt-4 border-t border-gray-100">
                <PrimaryButton 
                    v-if="isAdmin || isEditingProfile"
                    :disabled="form.processing"
                    class="bg-blue-500 hover:bg-blue-600 focus:bg-blue-600"
                >
                    Save Changes
                </PrimaryButton>
                <button
                    v-if="!isAdmin && isEditingProfile"
                    type="button"
                    @click="cancelEditProfile"
                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-sm font-medium text-gray-700 rounded-xl shadow-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group ml-2"
                >
                    <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-96 group-hover:h-96 opacity-10"></span>
                    Cancel
                </button>
                <PrimaryButton
                    v-if="!isAdmin && !isEditingProfile"
                    @click="startEditProfile"
                    type="button"
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
                        class="ml-4 text-sm text-green-500 flex items-center"
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