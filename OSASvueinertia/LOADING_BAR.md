# Global Loading Bar System

## Overview
A custom top loading bar that matches your app's design and color scheme using the blue-to-green gradient from your branding.

## Features
- **Automatic**: Shows on all Inertia.js page navigation
- **Manual Control**: Can be triggered for forms, AJAX requests, etc.
- **Realistic Progress**: Simulates realistic loading progress
- **Beautiful Effects**: Shimmer animation and glow effects
- **Theme Aware**: Works with light/dark mode
- **Performance Optimized**: Uses requestAnimationFrame for smooth animations

## Usage

### Automatic (Page Navigation)
The loading bar automatically shows during:
- Link clicks
- Form submissions with Inertia
- Browser back/forward navigation

### Manual Control (Forms, AJAX, etc.)
```javascript
import { useGlobalLoading } from '@/Composables/useGlobalLoading';

const { startLoading, stopLoading } = useGlobalLoading();

// Start loading
startLoading('my-task');

// Stop loading
stopLoading('my-task');
```

### Example Usage in Forms
```javascript
const submit = () => {
    startLoading('login-form');
    
    form.post('/login', {
        onFinish: () => {
            stopLoading('login-form');
        }
    });
};
```

### Example Usage with Fetch/Axios
```javascript
const fetchData = async () => {
    startLoading('data-fetch');
    
    try {
        const response = await fetch('/api/data');
        const data = await response.json();
        // Handle data
    } finally {
        stopLoading('data-fetch');
    }
};
```

## Customization

### Colors
Edit `resources/js/Components/LoadingBar.vue` to change colors:
```vue
<!-- Change the gradient colors -->
<div class="bg-gradient-to-r from-blue-500 via-green-500 to-blue-500">
```

### Height
```vue
<!-- Change the height class -->
<div class="fixed top-0 left-0 right-0 z-[9999] h-2"> <!-- Change h-1 to h-2 -->
```

### Speed
```javascript
// In LoadingBar.vue, adjust the progress simulation speed
if (progress.value < 30) {
    progress.value += Math.random() * 15 + 5; // Faster/slower
}
```

## Technical Details
- Uses Vue 3 Composition API
- Listens to Inertia.js router events
- Custom event system for manual control
- Hardware-accelerated animations
- Properly cleans up resources on unmount
