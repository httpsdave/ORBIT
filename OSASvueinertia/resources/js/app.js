import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';

import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { useTheme } from '@/Composables/useTheme';
import LoadingBar from '@/Components/LoadingBar.vue';


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize theme before creating the Inertia app
const { initializeTheme } = useTheme();
initializeTheme();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ 
            render: () => h('div', [
                h(LoadingBar),
                h(App, props)
            ])
        });
        
        return app
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: false, // Disable Inertia's default progress bar
});
