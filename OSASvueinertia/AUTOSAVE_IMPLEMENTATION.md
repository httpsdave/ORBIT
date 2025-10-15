# Autosave Feature Implementation

## 🎯 Overview
Autosave feature that preserves form data when users accidentally exit, their device dies, or they don't finish filling out forms. Works seamlessly with pre-filled/initialized data.

## 🔧 How It Works

### Smart Data Priority System
The autosave system uses **timestamp comparison** to intelligently handle data conflicts:

```
1. User opens form → Fetch autosaved data
2. Compare timestamps:
   - Autosaved timestamp > Initialized data timestamp → Show restore prompt
   - Autosaved timestamp ≤ Initialized data timestamp → Use initialized data, enable autosave
   - No autosaved data → Enable autosave immediately
```

### Key Features
✅ **Disabled by default** - Autosave starts ONLY after determining what data to use
✅ **Timestamp-based comparison** - Compares `autosaved.updated_at` vs `initialFormData.updated_at/created_at`
✅ **User choice** - Restore prompt lets users decide: restore autosaved or start fresh
✅ **Clean slate** - Dismissing the prompt clears old autosaved data
✅ **Automatic cleanup** - Successful submission removes autosaved data
✅ **Edit mode excluded** - Autosave disabled when editing submitted forms

## 📁 Files Created/Modified

### Backend
- **Migration**: `database/migrations/2025_10_15_224605_create_autosaved_forms_table.php`
- **Model**: `app/Models/AutosavedForm.php`
- **Controller**: `app/Http/Controllers/FormAutosaveController.php`
- **Routes**: Added to `routes/web.php`
  - `POST /auto-save-form-data`
  - `GET /get-autosaved-form-data`
  - `DELETE /delete-autosaved-form-data`

### Frontend
- **Composable**: `resources/js/Composables/useFormAutoSave.js` (enhanced with enable/disable)
- **Example Form**: `resources/js/Components/forms/StudentOrganizationForm.vue`

## 🔄 Data Flow

```
┌─────────────────────────────────────────────────────────────┐
│ User opens form (with/without initialized data)            │
└──────────────────────┬──────────────────────────────────────┘
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ Fetch autosaved data from backend                          │
└──────────────────────┬──────────────────────────────────────┘
                       ▼
                ┌──────────────┐
                │ Autosaved?   │
                └──────┬───────┘
                       │
        ┌──────────────┴───────────────┐
        │                              │
        ▼ YES                          ▼ NO
┌──────────────────┐          ┌────────────────┐
│ Compare          │          │ Enable         │
│ timestamps       │          │ autosave       │
└────┬─────────────┘          └────────────────┘
     │
     ├─► Autosaved NEWER → Show restore prompt
     │                      ├─► Restore → Apply data + enable autosave
     │                      └─► Dismiss → Clear old + enable autosave
     │
     └─► Autosaved OLDER → Use initialized + enable autosave
```

## 🚀 Implementation for Other Forms

To add autosave to other forms (e.g., `RenewalForm.vue`, `PlanOfActivitiesForm.vue`):

### 1. Add imports
```javascript
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useFormAutoSave } from '@/Composables/useFormAutoSave';
```

### 2. Initialize composable (DISABLED by default)
```javascript
const showRestorePrompt = ref(false);
const autosavedData = ref(null);

const formDataForAutosave = computed(() => form.data());
const { isAutoSaving, enable, stop } = useFormAutoSave(
  formDataForAutosave, 
  'renewal_form', // Change this for each form type
  { enabled: false }
);
```

### 3. Add timestamp comparison helper
```javascript
const isAutosavedDataNewer = (autosavedTimestamp) => {
  if (!autosavedTimestamp) return false;
  
  if (props.initialFormData?.updated_at || props.initialFormData?.created_at) {
    const initialTimestamp = new Date(props.initialFormData.updated_at || props.initialFormData.created_at);
    const autosaveTimestamp = new Date(autosavedTimestamp);
    return autosaveTimestamp > initialTimestamp;
  }
  
  return true;
};
```

### 4. Fetch and compare on mount
```javascript
onMounted(async () => {
  if (!props.isEdit) {
    try {
      const response = await fetch('/get-autosaved-form-data?form_type=renewal_form', {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        },
      });
      
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.form_data) {
          if (isAutosavedDataNewer(data.updated_at)) {
            autosavedData.value = data.form_data;
            showRestorePrompt.value = true;
          } else {
            enable(); // Autosaved is older
          }
        } else {
          enable(); // No autosaved data
        }
      } else {
        enable(); // 404 - no autosaved data
      }
    } catch (error) {
      console.error('Failed to fetch autosaved data:', error);
      enable(); // On error, enable anyway
    }
  }
});

onUnmounted(() => {
  stop();
});
```

### 5. Add restore/dismiss functions
```javascript
const restoreAutosavedData = () => {
  if (autosavedData.value) {
    Object.keys(autosavedData.value).forEach(key => {
      if (key in form) {
        form[key] = autosavedData.value[key];
      }
    });
  }
  showRestorePrompt.value = false;
  enable();
};

const dismissRestorePrompt = async () => {
  showRestorePrompt.value = false;
  autosavedData.value = null;
  
  try {
    await fetch('/delete-autosaved-form-data?form_type=renewal_form', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
      },
      body: JSON.stringify({ form_type: 'renewal_form' }),
    });
  } catch (error) {
    console.error('Failed to clear autosaved data:', error);
  }
  
  enable();
};
```

### 6. Clear on successful submission
```javascript
form.post('/applications', {
  onSuccess: async () => {
    try {
      await fetch('/delete-autosaved-form-data?form_type=renewal_form', {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        },
        body: JSON.stringify({ form_type: 'renewal_form' }),
      });
    } catch (error) {
      console.error('Failed to clear autosaved data:', error);
    }
    emit('submitted', form.data());
  },
  // ...
});
```

### 7. Add UI elements

**Restore Prompt Modal** (at top of template):
```vue
<div v-if="showRestorePrompt" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
  <div class="bg-white rounded-lg shadow-xl p-6 max-w-md mx-4">
    <div class="flex items-start mb-4">
      <svg class="w-6 h-6 text-blue-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <h3 class="text-lg font-semibold text-gray-900">Restore Unsaved Changes?</h3>
        <p class="mt-2 text-sm text-gray-600">
          We found unsaved changes from your previous session. Would you like to restore them?
        </p>
      </div>
    </div>
    <div class="flex justify-end gap-3 mt-6">
      <button
        @click="dismissRestorePrompt"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
      >
        Start Fresh
      </button>
      <button
        @click="restoreAutosavedData"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
      >
        Restore Changes
      </button>
    </div>
  </div>
</div>
```

**Autosave Indicator** (before submit button):
```vue
<div v-if="isAutoSaving" class="mb-3 text-sm text-gray-500 flex items-center justify-center gap-2">
  <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
  </svg>
  <span>Saving...</span>
</div>
<div v-else-if="!isEdit" class="mb-3 text-sm text-green-600 flex items-center justify-center gap-2">
  <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
  </svg>
  <span>Draft saved</span>
</div>
```

## 🔑 Key Points

### Form Type Naming
Each form needs a unique `form_type` identifier:
- `student_organization` - StudentOrganizationForm
- `renewal_form` - RenewalForm
- `plan_of_activities` - PlanOfActivitiesForm
- `commitment_form` - CommitmentForm
- `list_of_members` - ListOfMembersForm
- `list_of_officers` - ListOfOfficersForm
- `evaluation_form` - EvaluationForm
- `attendance_form` - ActivityAttendanceForm
- `certification_form` - StudentCertificationForm

### Database Constraint
The `unique(['user_id', 'form_type'])` constraint ensures one autosave per user per form type. This prevents duplicate autosaves.

### Edit Mode Exclusion
Always check `!props.isEdit` before enabling autosave. Edit mode should work with submitted data, not autosave.

### Cleanup Strategy
Always clear autosaved data after successful submission to prevent showing old data on next form creation.

## 🐛 Troubleshooting

**Problem**: Autosave not working
- Check: Is `enable()` being called?
- Check: Is form not in edit mode?
- Check: Browser console for errors

**Problem**: Restore prompt not showing
- Check: Is autosaved timestamp actually newer?
- Check: Console log the timestamp comparison

**Problem**: Data not persisting after refresh
- Check: Database has autosaved record
- Check: Network tab shows successful POST to `/auto-save-form-data`
- Check: CSRF token is valid

## 📊 Database Schema

```sql
CREATE TABLE autosaved_forms (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  form_type VARCHAR(255) NOT NULL,
  form_data JSON NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY (user_id, form_type),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  KEY (user_id, form_type, updated_at)
);
```

## ⚡ Performance Considerations

- **Debounced saves**: 1 second delay prevents excessive API calls
- **Duplicate prevention**: Compares JSON to avoid saving identical data
- **Indexed queries**: Composite index on (user_id, form_type, updated_at)
- **JSON storage**: Efficient for complex nested form structures
- **Automatic cleanup**: Prevents database bloat from abandoned drafts

---

**Status**: ✅ Implemented and tested for `StudentOrganizationForm.vue`
**Next Steps**: Apply pattern to remaining 7 forms
