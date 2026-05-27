import { createRouter, createWebHistory } from 'vue-router';
import Login from './pages/Login.vue';
import ForgotPassword from './pages/ForgotPassword.vue';

const routes = [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'login', component: Login, meta: { title: 'Login | LSPU ORBIT' } },
    { path: '/forgot-password', name: 'forgot-password', component: ForgotPassword, meta: { title: 'Forgot Password | LSPU ORBIT' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.afterEach((to) => {
    document.title = to.meta.title || 'LSPU ORBIT Auth';
});

export default router;
