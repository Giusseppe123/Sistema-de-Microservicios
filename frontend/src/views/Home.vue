<script setup>
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const store = useAuthStore();
const products = ref([]);
const searchQuery = ref('');

// Estado para inputs del carrito
const cartInputs = ref({});

// Productos filtrados por búsqueda
const filteredProducts = computed(() => {
  if (!searchQuery.value.trim()) {
    return products.value;
  }
  
  const query = searchQuery.value.toLowerCase();
  return products.value.filter(product => 
    product.name.toLowerCase().includes(query) ||
    (product.description && product.description.toLowerCase().includes(query))
  );
});

// Cargar productos
const loadProducts = async () => {
  try {
    const res = await axios.get('http://localhost:8001/api/products');
    products.value = res.data.map(p => ({
      ...p,
      stock: parseInt(p.stock) 
    }));
  } catch (e) {
    console.error("Error cargando productos", e);
  }
};

// Agregar al carrito
const addToCart = async (productId) => {
  const qty = cartInputs.value[productId] || 1;
  try {
    await axios.post('http://localhost:8001/api/cart', {
      product_id: productId,
      quantity: parseInt(qty)
    }, {
      headers: { Authorization: `Bearer ${store.token}` }
    });
    alert('✅ Agregado al carrito.');
    cartInputs.value[productId] = 1;
  } catch (e) {
    alert('❌ Error: No se pudo agregar al carrito.');
  }
};

// Limpiar búsqueda
const clearSearch = () => {
  searchQuery.value = '';
};

onMounted(loadProducts);
</script>

<template>
  <div class="min-h-screen pb-10 bg-gray-50 font-sans">
    <nav class="bg-gradient-to-r from-slate-800 to-blue-800 text-white p-4 shadow-lg sticky top-0 z-50 backdrop-blur-sm bg-opacity-95">
      <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center gap-3">
          <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h1 class="text-xl font-bold">Sistema de Microservicios</h1>
        </div>
        <div class="flex items-center gap-4">
          <router-link v-if="store.isAdmin" to="/admin" class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded-lg transition font-semibold shadow-md text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Panel Admin
          </router-link>
          <router-link v-if="!store.isAdmin" to="/cart" class="flex items-center gap-2 bg-cyan-600 hover:bg-cyan-700 px-4 py-2 rounded-lg transition font-semibold shadow-md text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Ver Carrito
          </router-link>
          <div class="text-right hidden sm:block">
            <span class="block text-xs text-slate-300">Usuario</span>
            <span class="font-semibold text-sm">{{ store.user }}</span>
          </div>
          <span class="bg-cyan-600 text-white px-3 py-1 rounded-lg text-xs font-semibold uppercase">{{ store.role }}</span>
          <button @click="store.logout(); $router.push('/login')" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition font-semibold">Salir</button>
        </div>
      </div>
    </nav>

    <div class="container mx-auto p-6">
      
      <div class="bg-gradient-to-r from-blue-600 to-cyan-600 p-6 rounded-xl shadow-lg mb-6">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="🔍 Buscar productos por nombre o descripción..."
            class="w-full pl-12 pr-12 py-3 rounded-lg bg-white/90 backdrop-blur-sm text-gray-800 placeholder-gray-500 border-2 border-white/50 focus:border-white focus:outline-none focus:ring-2 focus:ring-white/50 transition-all font-medium"
          >
          <button 
            v-if="searchQuery" 
            @click="clearSearch" 
            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-red-600 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <span>Catálogo de Productos</span>
        <span class="text-sm font-normal text-gray-500 bg-gray-200 px-3 py-1 rounded-full">
          {{ filteredProducts.length }} {{ searchQuery ? 'encontrados' : 'items' }}
        </span>
      </h2>
      
      <div v-if="filteredProducts.length === 0 && searchQuery" class="text-center py-12 bg-white rounded-lg border-2 border-dashed border-gray-300">
        <div class="text-6xl mb-4">🔍</div>
        <p class="text-gray-600 text-xl font-semibold mb-2">No se encontraron productos</p>
        <p class="text-gray-400 mb-4">Intenta con otro término de búsqueda</p>
        <button @click="clearSearch" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
          Limpiar búsqueda
        </button>
      </div>

      <div v-else-if="products.length === 0" class="text-center py-12 bg-white rounded-lg border border-dashed border-gray-300">
        <p class="text-gray-400 text-lg">No hay productos disponibles.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="prod in filteredProducts" :key="prod.id" class="bg-white rounded-xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden flex flex-col border border-gray-100">
          
          <div class="h-48 bg-gray-100 relative group">
            <img v-if="prod.image_url" :src="prod.image_url" class="w-full h-full object-cover transition duration-500 group-hover:scale-105" />
            <div v-else class="flex flex-col items-center justify-center h-full text-gray-400">
              <span class="text-3xl">📷</span>
              <span class="text-xs mt-1">Sin Imagen</span>
            </div>
          </div>

          <div class="p-5 flex-1 flex flex-col">
            <h3 class="font-bold text-lg text-gray-800 mb-1 leading-tight">{{ prod.name }}</h3>
            <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ prod.description || 'Sin descripción' }}</p>
            
            <div class="flex justify-between items-center mb-4 bg-gray-50 p-2 rounded">
              <span class="text-xl font-bold text-green-600">${{ prod.price }}</span>
              <div class="text-right">
                <span class="block text-xs text-gray-400 uppercase font-bold">Stock</span>
                <span class="text-sm font-bold text-gray-700">{{ prod.stock }} u.</span>
              </div>
            </div>

            <div class="mt-auto">
              <div class="flex gap-2">
                <input 
                  v-model="cartInputs[prod.id]" 
                  type="number" 
                  min="1" 
                  placeholder="1" 
                  class="w-16 border p-2 rounded text-center font-bold text-gray-700"
                >
                <button 
                  @click="addToCart(prod.id)" 
                  class="flex-1 bg-cyan-600 text-white py-2 rounded-lg font-semibold hover:bg-cyan-700 active:scale-95 transition shadow-md flex items-center justify-center gap-2"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  Agregar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>