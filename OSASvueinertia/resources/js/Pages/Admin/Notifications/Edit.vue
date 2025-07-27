<template>
  <div class="max-w-2xl mx-auto p-6 bg-white shadow-md rounded-xl">
    <h1 class="text-2xl font-bold mb-4">Edit Notification</h1>

    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700" for="title">Title</label>
        <input
          v-model="form.title"
          id="title"
          type="text"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
        />
        <p v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</p>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700" for="message">Message</label>
        <textarea
          v-model="form.message"
          id="message"
          rows="4"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
        ></textarea>
        <p v-if="form.errors.message" class="text-red-500 text-sm mt-1">{{ form.errors.message }}</p>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700" for="type">Type</label>
        <select
          v-model="form.type"
          id="type"
          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
        >
          <option value="info">Info</option>
          <option value="success">Success</option>
          <option value="warning">Warning</option>
          <option value="error">Error</option>
        </select>
        <p v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</p>
      </div>

      <div class="flex justify-end">
        <button
          type="button"
          class="mr-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-md"
          @click="cancel"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md"
        >
          Update
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  notification: Object,
})

const form = useForm({
  title: props.notification.title,
  message: props.notification.message,
  type: props.notification.type || 'info',
})

const submit = () => {
  form.put(route('admin.notifications.update', props.notification.id))
}

const cancel = () => {
  router.visit(route('admin.notifications.index'))
}
</script>