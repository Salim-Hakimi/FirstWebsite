import { createRouter, createWebHistory } from 'vue-router';

const DashboardPage = () => import('../pages/DashboardPage.vue');
const AdminFinancePage = () => import('../pages/AdminFinancePage.vue');
const AdminUsersPage = () => import('../pages/AdminUsersPage.vue');
const DormRoomsPage = () => import('../pages/DormRoomsPage.vue');
const DormStudentsPage = () => import('../pages/DormStudentsPage.vue');
const LibraryPage = () => import('../pages/LibraryPage.vue');
const NotFoundPage = () => import('../pages/NotFoundPage.vue');
const PurchaserPage = () => import('../pages/PurchaserPage.vue');
const RepresentativePage = () => import('../pages/RepresentativePage.vue');

export function createFanousRouter(base = '/') {
    return createRouter({
        history: createWebHistory(base),
        routes: [
            {
                path: '/',
                name: 'dashboard',
                component: DashboardPage,
                meta: { requiresAuth: true },
            },
            {
                path: '/admin/users',
                name: 'admin-users',
                component: AdminUsersPage,
                meta: { requiresAuth: true },
            },
            {
                path: '/admin/finance',
                name: 'admin-finance',
                component: AdminFinancePage,
                meta: { requiresAuth: true },
            },
            {
                path: '/dorm/rooms',
                name: 'dorm-rooms',
                component: DormRoomsPage,
                meta: { requiresAuth: true },
            },
            {
                path: '/dorm/students',
                name: 'dorm-students',
                component: DormStudentsPage,
                meta: { requiresAuth: true },
            },
            {
                path: '/representative',
                name: 'representative',
                component: RepresentativePage,
                meta: { requiresAuth: true },
            },
            {
                path: '/purchaser',
                name: 'purchaser',
                component: PurchaserPage,
                meta: { requiresAuth: true },
            },
            {
                path: '/library',
                name: 'library',
                component: LibraryPage,
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
