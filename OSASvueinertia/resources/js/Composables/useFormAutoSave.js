import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export function useFormAutoSave(form, formType) {
    const isAutoSaving = ref(false);
    const lastSavedData = ref({});
    const autoSaveTimeout = ref(null);

    // Save the watcher so we can stop it
    let unwatch = watch(form, (newFormData) => {
        clearTimeout(autoSaveTimeout.value);
        autoSaveTimeout.value = setTimeout(() => {
            autoSaveFormData(newFormData);
        }, 1000);
    }, { deep: true });

    const autoSaveFormData = async (formData) => {
        if (!formData || Object.keys(formData).length === 0) {
            return;
        }
        const currentData = JSON.stringify(formData);
        if (currentData === JSON.stringify(lastSavedData.value)) {
            return;
        }
        isAutoSaving.value = true;
        try {
            await router.post('/auto-save-form-data', {
                form_type: formType,
                form_data: formData
            }, {
                preserveState: true,
                preserveScroll: true
            });
            lastSavedData.value = { ...formData };
        } catch (error) {
            console.error('Auto-save failed:', error);
        } finally {
            isAutoSaving.value = false;
        }
    };

    // Return a stop function to clean up
    function stop() {
        if (unwatch) unwatch();
        if (autoSaveTimeout.value) clearTimeout(autoSaveTimeout.value);
    }

    return {
        isAutoSaving,
        autoSaveFormData,
        stop
    };
} 