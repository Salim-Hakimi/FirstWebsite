import { createRouter, createWebHistory } from 'vue-router';

const DashboardPage = () => import('../pages/DashboardPage.vue');
const NotFoundPage = () => import('../pages/NotFoundPage.vue');

export function createFanousRouter() {
    return createRouter({
        history: createWebHistory(),
        routes: [
            {
                path: '/',
                name: 'dashboard',
                component: DashboardPage,
                meta: { requiresAuth: true },
            },
            {
                path: '/:pathMatch(.*)*',
                name: 'not-found',
                component: NotFoundPage,
            },
        ],
        scrollBehavior() {
            return { top: 0 };
        },
    });
}
