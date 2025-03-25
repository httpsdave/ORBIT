<script setup>
import { defineProps } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ application: Object });

const form = useForm({
    organization_name: props.application.organization_name,
    president_name: props.application.president_name,
    application_date: props.application.application_date,
});

const submit = () => form.put(`/applications/${props.application.id}`);
</script>

<template>
    <div class="p-6">
        <h1 class="text-xl font-bold">Edit Application</h1>
        <form @submit.prevent="submit" class="mt-4 space-y-4">
            <div>
                <label class="block">Organization Name</label>
                <input v-model="form.organization_name" class="border p-2 w-full" required>
            </div>
            <div>
                <label class="block">President Name</label>
                <input v-model="form.president_name" class="border p-2 w-full" required>
            </div>
            <div>
                <label class="block">Application Date</label>
                <input type="date" v-model="form.application_date" class="border p-2 w-full" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
        </form>
    </div>
</template>
