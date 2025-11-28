import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Verify from '../views/Verify.vue';
import Home from '../views/Home.vue';
import Cart from '../views/Cart.vue';

const routes = [
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/verify', component: Verify },
  { path: '/', component: Home, meta: { requiresAuth: true } },
  { path: '/cart', component: Cart, meta: { requiresAuth: true } }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const store = useAuthStore();
  store.initialize();
  if (to.meta.requiresAuth && !store.isAuthenticated) {
    next('/login');
  } else {
    next();
  }
});


export default router;