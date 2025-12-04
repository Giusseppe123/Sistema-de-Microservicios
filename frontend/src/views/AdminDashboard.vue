<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const store = useAuthStore();
const router = useRouter();
const products = ref([]);


const loadProducts = async () => {
  try {
    const res = await axios.get('http://localhost:8001/api/products');
    products.value = res.data;
  } catch (e) {
    console.error("Error loading products", e);
  }
};


const totalProducts = computed(() => products.value.length);
const lowStockProducts = computed(() => products.value.filter(p => p.stock < 10).length);
const totalInventoryValue = computed(() => {
  return products.value.reduce((sum, p) => sum + (p.price * p.stock), 0).toFixed(2);
});
const outOfStock = computed(() => products.value.filter(p => p.stock === 0).length);

onMounted(loadProducts);
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50">
    
    <nav class="bg-gradient-to-r from-slate-800 to-blue-800 text-white p-4 shadow-lg sticky top-0 z-50 backdrop-blur-sm bg-opacity-95">
      <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center gap-3">
          <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h1 class="text-xl font-bold">Panel de Administración</h1>
        </div>
        <div class="flex items-center gap-4">
          <router-link to="/" class="flex items-center gap-2 bg-cyan-600 hover:bg-cyan-700 px-4 py-2 rounded-lg transition font-semibold shadow-md text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Ir a Tienda
          </router-link>
          <div class="text-right hidden sm:block">
            <span class="block text-xs text-slate-300">Usuario</span>
            <span class="font-semibold text-sm">{{ store.user }}</span>
          </div>
          <span class="bg-cyan-600 text-white px-3 py-1 rounded-lg text-xs font-semibold uppercase">{{ store.role }}</span>
          <button @click="store.logout(); router.push('/login')" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition font-semibold">Salir</button>
        </div>
      </div>
    </nav>

    <div class="container mx-auto p-6">
      
      <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">¡Bienvenido, {{ store.user }}! 👋</h2>
        <p class="text-gray-600">Gestiona tu inventario y productos desde este panel de control</p>
      </div>

      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Total Productos</p>
              <p class="text-4xl font-bold mt-2">{{ totalProducts }}</p>
            </div>
            <div class="bg-white/20 p-4 rounded-full">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
          </div>
        </div>

        
        <div class="bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-yellow-100 text-sm font-semibold uppercase tracking-wide">Stock Bajo</p>
              <p class="text-4xl font-bold mt-2">{{ lowStockProducts }}</p>
              <p class="text-xs text-yellow-100 mt-1">Menos de 10 unidades</p>
            </div>
            <div class="bg-white/20 p-4 rounded-full">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
            </div>
          </div>
        </div>

        
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Valor Total</p>
              <p class="text-4xl font-bold mt-2">${{ totalInventoryValue }}</p>
              <p class="text-xs text-green-100 mt-1">Inventario completo</p>
            </div>
            <div class="bg-white/20 p-4 rounded-full">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        
        <div class="bg-gradient-to-br from-red-500 to-pink-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition duration-300">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-red-100 text-sm font-semibold uppercase tracking-wide">Sin Stock</p>
              <p class="text-4xl font-bold mt-2">{{ outOfStock }}</p>
              <p class="text-xs text-red-100 mt-1">Productos agotados</p>
            </div>
            <div class="bg-white/20 p-4 rounded-full">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      
      <h3 class="text-2xl font-bold text-gray-800 mb-6">Acciones Rápidas</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <router-link to="/admin/inventory" class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-blue-500 transform hover:-translate-y-2">
          <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-6 text-white">
            <div class="flex items-center justify-between">
              <h4 class="text-xl font-bold">Gestión de Inventario</h4>
              <svg class="w-8 h-8 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
              </svg>
            </div>
          </div>
          <div class="p-6">
            <p class="text-gray-600 mb-4">Actualiza el stock de tus productos mediante el servicio de inventario Rust</p>
            <div class="flex items-center text-blue-600 font-semibold group-hover:gap-3 gap-2 transition-all">
              <span>Gestionar Stock</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
              </svg>
            </div>
          </div>
        </router-link>

        
        <router-link to="/admin/catalog" class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-purple-500 transform hover:-translate-y-2">
          <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-6 text-white">
            <div class="flex items-center justify-between">
              <h4 class="text-xl font-bold">Catálogo de Productos</h4>
              <svg class="w-8 h-8 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
            </div>
          </div>
          <div class="p-6">
            <p class="text-gray-600 mb-4">Visualiza, edita y elimina productos del catálogo completo</p>
            <div class="flex items-center text-purple-600 font-semibold group-hover:gap-3 gap-2 transition-all">
              <span>Ver Catálogo</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
              </svg>
            </div>
          </div>
        </router-link>

        
        <router-link to="/admin/create" class="group bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border-2 border-transparent hover:border-green-500 transform hover:-translate-y-2">
          <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6 text-white">
            <div class="flex items-center justify-between">
              <h4 class="text-xl font-bold">Crear Producto</h4>
              <svg class="w-8 h-8 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
            </div>
          </div>
          <div class="p-6">
            <p class="text-gray-600 mb-4">Agrega nuevos productos al catálogo con vista previa de imagen</p>
            <div class="flex items-center text-green-600 font-semibold group-hover:gap-3 gap-2 transition-all">
              <span>Crear Nuevo</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
              </svg>
            </div>
          </div>
        </router-link>
      </div>

      
      <div class="mt-10 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-xl shadow-lg p-1">
        <div class="bg-white rounded-lg p-6">
          <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Consejos Rápidos
          </h3>
          <ul class="space-y-2 text-gray-700">
            <li class="flex items-start gap-2">
              <span class="text-purple-600 font-bold">•</span>
              <span>Usa la <strong>Gestión de Inventario</strong> para actualizar el stock mediante el servicio Rust de alto rendimiento</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-purple-600 font-bold">•</span>
              <span>El <strong>Catálogo de Productos</strong> te permite ver detalles completos y editar productos existentes</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-purple-600 font-bold">•</span>
              <span>Al <strong>Crear Productos</strong> podrás ver una vista previa de la imagen antes de guardar</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-purple-600 font-bold">•</span>
              <span>Los valores de stock <strong>no pueden ser negativos</strong> - validación automática activada</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
