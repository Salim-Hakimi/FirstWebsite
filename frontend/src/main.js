import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import { createFanousRouter } from './router';
import './styles/frontend.css';

const apps = {
    spa: defineAsyncComponent(() => import('./App.vue')),
};

function readContext(element) {
    try {
        if (element.dataset.vueContextId) {
            return JSON.parse(document.getElementById(element.dataset.vueContextId)?.textContent || '{}');
        }

        return element.dataset.vueContext ? JSON.parse(element.dataset.vueContext) : {};
    } catch {
        return {};
    }
}

function mountVueApp(element) {
    const name = element.dataset.vueApp || 'spa';
    const component = apps[name];

    if (! component) {
        return;
    }

    const app = createApp(component, {
        context: readContext(element),
        endpoint: element.dataset.endpoint,
        title: element.dataset.title,
    });

    app.use(createPinia());

    if (name === 'spa') {
        app.use(createFanousRouter(element.dataset.vueBase || '/'));
    }

    app.mount(element);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-vue-app]').forEach(mountVueApp);
});
