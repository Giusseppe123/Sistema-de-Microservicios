import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Verify from '../views/Verify.vue';
import Home from '../views/Home.vue';
import Cart from '../views/Cart.vue';
import AdminDashboard from '../views/AdminDashboard.vue';
import InventoryManagement from '../views/InventoryManagement.vue';
import ProductCatalog from '../views/ProductCatalog.vue';
import CreateProduct from '../views/CreateProduct.vue';

const routes = [
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/verify', component: Verify },
  { path: '/', component: Home, meta: { requiresAuth: true } },
  { path: '/cart', component: Cart, meta: { requiresAuth: true } },
  { path: '/admin', component: AdminDashboard, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/inventory', component: InventoryManagement, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/catalog', component: ProductCatalog, meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/create', component: CreateProduct, meta: { requiresAuth: true, requiresAdmin: true } }
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
  } else if (to.meta.requiresAdmin && !store.isAdmin) {
    alert('❌ Acceso denegado: Se requieren permisos de administrador');
    next('/');
  } else {
    next();
  }
});


export default router;