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
});

const isEditing = ref(false);
const originalDefaults = ref({
    coordinator_name: user.coordinator_name || '',
    director_name: user.director_name || '',
});

// Watch for changes in user data and update form values
// Note: These are now global system defaults, not user-specific
watch(() => user, (newUser) => {
    if (newUser) {
        form.coordinator_name = newUser.coordinator_name || '';
        form.director_name = newUser.director_name || '';
        originalDefaults.value = {
            coordinator_name: newUser.coordinator_name || '',
            director_name: newUser.director_name || '',
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
            <PrimaryButton v-if="!isEditing" type="button" @click="startEdit" class="bg-amber-500 hover:bg-amber-600 focus:bg-amber-600">
                Edit Form Defaults
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