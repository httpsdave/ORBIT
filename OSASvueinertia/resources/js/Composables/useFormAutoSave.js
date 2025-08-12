import { ref, watch } from 'vue';

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
            // Use fetch instead of Inertia router to avoid iframe overlays
            const response = await fetch('/auto-save-form-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    form_type: formType,
                    form_data: formData
                })
            });
            
            if (response.ok) {
                lastSavedData.value = { ...formData };
            } else {
                console.error('Auto-save failed:', response.statusText);
            }
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