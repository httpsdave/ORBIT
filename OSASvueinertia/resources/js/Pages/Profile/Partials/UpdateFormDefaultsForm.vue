<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

// Form defaults for admin
const form = useForm({
    coordinator_name: user.coordinator_name || '',
    director_name: user.director_name || '',
});

function submit() {
    form.patch(route('profile.form-defaults.update'), {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ['auth'] });
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
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300"
                    v-model="form.coordinator_name"
                    placeholder="Enter coordinator name"
                />
                <InputError class="mt-2" :message="form.errors.coordinator_name" />
            </div>

            <div>
                <InputLabel for="director_name" value="Director Name" class="text-gray-700 dark:text-gray-300 font-medium" />
                <TextInput
                    id="director_name"
                    type="text"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-gray-700 dark:text-gray-300"
                    v-model="form.director_name"
                    placeholder="Enter director/chairperson name"
                />
                <InputError class="mt-2" :message="form.errors.director_name" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="form.processing" class="bg-purple-500 hover:bg-purple-600 focus:bg-purple-600">
                <span v-if="form.processing">Saving...</span>
                <span v-else>Save Form Defaults</span>
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