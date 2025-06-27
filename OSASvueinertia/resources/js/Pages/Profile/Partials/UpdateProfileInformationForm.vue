<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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
    form.profile_photo = null;
    photoPreview.value = null;
}

function submit() {
    const data = { ...form.data() };
    if (removeProfilePhoto.value) {
        data.remove_profile_photo = true;
    }
    form.post(route('profile.update'), {
        data,
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            removeProfilePhoto.value = false;
        },
        _method: 'patch',
    });
}
</script>

<template>
    <section class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
        <!-- Header with colored accent -->
        <div class="border-l-4 border-blue-500 pl-3 mb-6">
            <h2 class="text-xl font-semibold text-gray-800">
                Profile Information
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile photo. <span v-if="isAdmin">You can also update your name and email address.</span>
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
                        :disabled="!isAdmin"
                        :class="!isAdmin ? 'bg-gray-100 text-gray-400 cursor-not-allowed select-none pointer-events-none' : ''"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
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
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
            </div>

            <div>
                <InputLabel value="Profile Photo" />
                <div class="flex items-center gap-6 mt-2">
                    <img :src="photoPreview" class="w-24 h-24 rounded-full object-cover border-2 border-blue-200 shadow" />
                    <div class="flex flex-col gap-2">
                        <label class="inline-block">
                            <input type="file" accept="image/*" @change="handlePhotoChange" class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition" />
                        </label>
                        <button
                            v-if="user.profile_photo_url || photoPreview"
                            type="button"
                            @click="handleRemovePhoto"
                            class="px-3 py-1 bg-gray-100 text-gray-500 border border-gray-200 rounded hover:bg-red-100 hover:text-red-600 transition text-xs font-semibold shadow-sm mt-1"
                        >
                            Remove Photo
                        </button>
                    </div>
                </div>
                <InputError :message="form.errors.profile_photo" />
            </div>

            <div class="flex items-center pt-4 border-t border-gray-100">
                <PrimaryButton 
                    :disabled="form.processing"
                    class="bg-blue-500 hover:bg-blue-600 focus:bg-blue-600"
                >
                    Save Changes
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