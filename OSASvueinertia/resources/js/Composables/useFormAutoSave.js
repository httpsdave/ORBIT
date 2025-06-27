import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

export function useFormAutoSave(form, formType) {
    const isAutoSaving = ref(false);
    const lastSavedData = ref({});

    // Auto-save form data when form changes
    watch(form, (newFormData) => {
        // Debounce auto-save to avoid too many requests
        clearTimeout(autoSaveTimeout.value);
        autoSaveTimeout.value = setTimeout(() => {
            autoSaveFormData(newFormData);
        }, 1000); // Save after 1 second of inactivity
    }, { deep: true });

    const autoSaveTimeout = ref(null);

    const autoSaveFormData = async (formData) => {
        if (!formData || Object.keys(formData).length === 0) {
            return;
        }

        // Don't save if data hasn't changed
        const currentData = JSON.stringify(formData);
        if (currentData === JSON.stringify(lastSavedData.value)) {
            return;
        }

        isAutoSaving.value = true;

        try {
            // Send auto-save request
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

    return {
        isAutoSaving,
        autoSaveFormData
    };
} 