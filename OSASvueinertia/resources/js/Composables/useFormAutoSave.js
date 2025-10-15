import { ref, watch } from 'vue';

export function useFormAutoSave(form, formType, options = {}) {
    const isAutoSaving = ref(false);
    const lastSavedData = ref({});
    const autoSaveTimeout = ref(null);
    const isEnabled = ref(options.enabled !== false); // Default to true

    let unwatch = null;

    // Function to start watching
    function start() {
        if (unwatch) return; // Already watching
        
        unwatch = watch(form, (newFormData) => {
            if (!isEnabled.value) return; // Skip if not enabled
            
            clearTimeout(autoSaveTimeout.value);
            autoSaveTimeout.value = setTimeout(() => {
                autoSaveFormData(newFormData);
            }, 1000);
        }, { deep: true });
    }

    const autoSaveFormData = async (formData) => {
        if (!isEnabled.value) return; // Skip if not enabled
        
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

    // Function to enable autosave
    function enable() {
        isEnabled.value = true;
        start(); // Start watching if not already
    }

    // Function to disable autosave
    function disable() {
        isEnabled.value = false;
    }

    // Return a stop function to clean up
    function stop() {
        if (unwatch) unwatch();
        if (autoSaveTimeout.value) clearTimeout(autoSaveTimeout.value);
        unwatch = null;
    }

    // Auto-start if enabled by default
    if (isEnabled.value) {
        start();
    }

    return {
        isAutoSaving,
        autoSaveFormData,
        enable,
        disable,
        start,
        stop
    };
} 