<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { watch, ref } from 'vue';

const user = usePage().props.auth.user;

const form = useForm({
    coordinator_name: user.coordinator_name || '',
    director_name: user.director_name || '',
    allow_image_uploads: user.allow_image_uploads !== undefined ? user.allow_image_uploads : true,
    allow_link_submissions: user.allow_link_submissions !== undefined ? user.allow_link_submissions : true,
});

const isEditing = ref(false);
const originalDefaults = ref({
    coordinator_name: user.coordinator_name || '',
    director_name: user.director_name || '',
    allow_image_uploads: user.allow_image_uploads !== undefined ? user.allow_image_uploads : true,
    allow_link_submissions: user.allow_link_submissions !== undefined ? user.allow_link_submissions : true,
});

// Watch for changes in user data and update form values
// Note: These are now global system defaults, not user-specific
watch(() => user, (newUser) => {
    if (newUser) {
        form.coordinator_name = newUser.coordinator_name || '';
        form.director_name = newUser.director_name || '';
        form.allow_image_uploads = newUser.allow_image_uploads !== undefined ? newUser.allow_image_uploads : true;
        form.allow_link_submissions = newUser.allow_link_submissions !== undefined ? newUser.allow_link_submissions : true;
        originalDefaults.value = {
            coordinator_name: newUser.coordinator_name || '',
            director_name: newUser.director_name || '',
            allow_image_uploads: newUser.allow_image_uploads !== undefined ? newUser.allow_image_uploads : true,
            allow_link_submissions: newUser.allow_link_submissions !== undefined ? newUser.allow_link_submissions : true,
        };
        isEditing.value = false;
    }
}, { deep: true });

function startEdit() {
    isEditing.value = true;
}

function cancelEdit() {
    form.coordinator_name = originalDefaults.value.coordinator_name;
    form.director_name = originalDefaults.value.director_name;
    form.allow_image_uploads = originalDefaults.value.allow_image_uploads;
    form.allow_link_submissions = originalDefaults.value.allow_link_submissions;
    isEditing.value = false;
    form.clearErrors();
}

function submit() {
    form.patch(route('profile.form-defaults.update'), {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['auth'] });
            isEditing.value = false;
        },
    });
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <InputLabel for="coordinator_name" value="Coordinator Name" class="text-gray-700 dark:text-gray-300 font-medium" />
                <TextInput
                    id="coordinator_name"
                    type="text"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300"
                    v-model="form.coordinator_name"
                    :disabled="!isEditing"
                    :class="!isEditing ? 'bg-gray-100 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed select-none pointer-events-none' : ''"
                    placeholder="Enter coordinator name"
                />
                <InputError class="mt-2" :message="form.errors.coordinator_name" />
            </div>

            <div>
                <InputLabel for="director_name" value="Director Name" class="text-gray-700 dark:text-gray-300 font-medium" />
                <TextInput
                    id="director_name"
                    type="text"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-amber-500 focus:ring focus:ring-amber-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300"
                    v-model="form.director_name"
                    :disabled="!isEditing"
                    :class="!isEditing ? 'bg-gray-100 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed select-none pointer-events-none' : ''"
                    placeholder="Enter director/chairperson name"
                />
                <InputError class="mt-2" :message="form.errors.director_name" />
            </div>
        </div>

        <!-- Image Upload Settings -->
        <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Form Settings</h4>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <InputLabel for="allow_image_uploads" value="Allow Image Uploads" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Enable or disable image upload functionality in List of Members and List of Officers forms
                        </p>
                    </div>
                    <div class="ml-4">
                        <div class="relative inline-flex items-center">
                            <input 
                                id="allow_image_uploads"
                                type="checkbox" 
                                v-model="form.allow_image_uploads"
                                :disabled="!isEditing"
                                class="sr-only"
                            />
                            <label 
                                for="allow_image_uploads" 
                                :class="[
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2',
                                    form.allow_image_uploads ? 'bg-amber-500' : 'bg-gray-200',
                                    !isEditing ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <span 
                                    :class="[
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                        form.allow_image_uploads ? 'translate-x-5' : 'translate-x-0'
                                    ]"
                                ></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <InputLabel for="allow_link_submissions" value="Allow Link Submissions" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Enable or disable link submission option in Upload Signed Document modal
                        </p>
                    </div>
                    <div class="ml-4">
                        <div class="relative inline-flex items-center">
                            <input 
                                id="allow_link_submissions"
                                type="checkbox" 
                                v-model="form.allow_link_submissions"
                                :disabled="!isEditing"
                                class="sr-only"
                            />
                            <label 
                                for="allow_link_submissions" 
                                :class="[
                                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2',
                                    form.allow_link_submissions ? 'bg-amber-500' : 'bg-gray-200',
                                    !isEditing ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                            >
                                <span 
                                    :class="[
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                        form.allow_link_submissions ? 'translate-x-5' : 'translate-x-0'
                                    ]"
                                ></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton v-if="isEditing" :disabled="form.processing" class="bg-amber-500 hover:bg-amber-600 focus:bg-amber-600">
                <span v-if="form.processing">Saving...</span>
                <span v-else>Save Form Defaults</span>
            </PrimaryButton>
            <button
                v-if="isEditing"
                type="button"
                @click="cancelEdit"
                class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-sm font-medium text-gray-700 rounded-xl shadow-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-300 relative overflow-hidden group"
            >
                Cancel
            </button>
            <PrimaryButton v-if="!isEditing" type="button" @click="startEdit" class="bg-amber-500 hover:bg-amber-600 focus:bg-amber-600 inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Defaults
            </PrimaryButton>

            <Transition
                enter-active-class="transition ease-in-out duration-300"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out duration-300"
                leave-to-class="opacity-0"
            >
                <p
                    v-if="form.recentlySuccessful"
                    class="text-sm text-green-500 flex items-center"
                >
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Form defaults saved successfully
                </p>
            </Transition>
        </div>
    </form>
</template>