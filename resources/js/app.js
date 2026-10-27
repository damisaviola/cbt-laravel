import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';

createInertiaApp({
    title: title => title ? `${title} - SmartCBT School Edition` : 'SmartCBT School Edition',
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue');
        return pages[`./Pages/${name}.vue`]();
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();
        app.use(plugin);
        app.use(pinia);
        app.mount(el);
    },
    progress: {
        color: '#185338',
        showSpinner: false,
    },
});
