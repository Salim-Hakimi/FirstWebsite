import { createApp, defineAsyncComponent } from 'vue';
import { createPinia } from 'pinia';
import { createFanousRouter } from './router';
import './styles/frontend.css';

const apps = {
    spa: defineAsyncComponent(() => import('./App.vue')),
    'dashboard-card': defineAsyncComponent(() => import('./components/common/AsyncDataCard.vue')),
    'dashboard-summary': defineAsyncComponent(() => import('./components/dashboard/DashboardSummary.vue')),
    'library-members-table': defineAsyncComponent(() => import('./components/library/LibraryMembersTable.vue')),
    'library-books-table': defineAsyncComponent(() => import('./components/library/LibraryBooksTable.vue')),
    'library-inventory-copies-table': defineAsyncComponent(() => import('./components/library/LibraryInventoryCopiesTable.vue')),
    'library-loans-table': defineAsyncComponent(() => import('./components/library/LibraryLoansTable.vue')),
    'dorm-students-table': defineAsyncComponent(() => import('./components/dorm/DormStudentsTable.vue')),
    'dorm-rooms-table': defineAsyncComponent(() => import('./components/dorm/DormRoomsTable.vue')),
    'admin-finance-transactions': defineAsyncComponent(() => import('./components/finance/AdminFinanceTransactionsTable.vue')),
    'admin-users-table': defineAsyncComponent(() => import('./components/admin/AdminUsersTable.vue')),
    'purchaser-records-table': defineAsyncComponent(() => import('./components/purchaser/PurchaserRecordsTable.vue')),
    'representative-collections-table': defineAsyncComponent(() => import('./components/representative/RepresentativeCollectionsTable.vue')),
};

function readContext(element) {
    try {
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
        app.use(createFanousRouter());
    }

    app.mount(element);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-vue-app]').forEach(mountVueApp);
});
