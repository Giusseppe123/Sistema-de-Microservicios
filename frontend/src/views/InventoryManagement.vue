<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const store = useAuthStore();
const router = useRouter();
const products = ref([]);
const searchQuery = ref('');
const stockInputs = ref({});
const loading = ref(false);

const loadProducts = async () => {
  loading.value = true;
  try {
    const res = await axios.get('http://localhost:8001/api/products');
    products.value = res.data.map(p => ({
      ...p,
      stock: parseInt(p.stock)
    }));
  } catch (e) {
    console.error("Error loading products", e);
    alert('Error al cargar productos');
  } finally {
    loading.value = false;
  }
};

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

const updateStockRust = async (productId) => {
  const newStockVal = stockInputs.value[productId];

  if (newStockVal === undefined || newStockVal === "") {
    return alert("Por favor ingresa una cantidad válida");
  }

  const stockToSend = parseInt(newStockVal);

  if (stockToSend < 0) {
    return alert("❌ El stock no puede ser negativo");
  }

  try {
    await axios.post('http://localhost:8002/api/inventory', {
      product_id: productId,
      stock: stockToSend
    }, {
      headers: { Authorization: `Bearer ${store.token}` }
    });

    const productIndex = products.value.findIndex(p => p.id === productId);
    if (productIndex !== -1) {
      products.value[productIndex].stock = stockToSend;
    }

    alert(`✅ Stock actualizado a ${stockToSend} unidades`);
    stockInputs.value[productId] = '';

  } catch (e) {
    console.error(e);
    if (e.response?.status === 400) {
      alert('❌ Error: El stock no puede ser negativo');
    } else {
      alert('❌ Error: No se pudo conectar con el servicio de inventario');
    }
  }
};

const getStockStatusColor = (stock) => {
  if (stock === 0) return 'text-red-600 bg-red-100';
  if (stock < 10) return 'text-yellow-600 bg-yellow-100';
  return 'text-green-600 bg-green-100';
};

const getStockStatusText = (stock) => {
  if (stock === 0) return 'Agotado';
  if (stock < 10) return 'Stock Bajo';
  return 'Disponible';
};

onMounted(loadProducts);
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50">
    <nav class="bg-gradient-to-r from-slate-800 to-blue-800 text-white p-4 shadow-lg sticky top-0 z-50 backdrop-blur-sm bg-opacity-95">
      <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center gap-3">
          <router-link to="/admin" class="flex items-center gap-2 hover:opacity-80 transition">
            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
          </router-link>
          <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
          </svg>
          <h1 class="text-xl font-bold">Gestión de Inventario</h1>
        </div>
        <div class="flex items-center gap-4">
          <span class="bg-cyan-600 text-white px-3 py-1 rounded-lg text-xs font-semibold uppercase">{{ store.role }}</span>
          <button @click="store.logout(); router.push('/login')" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition font-semibold">Salir</button>
        </div>
      </div>
    </nav>

    <div class="container mx-auto p-6">
      <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Control de Stock 📦</h2>
        <p class="text-gray-600">Actualiza el inventario mediante el servicio Rust de alto rendimiento</p>
      </div>

      <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
        <div class="flex items-start gap-3">
          <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <h4 class="font-bold text-blue-800 mb-1">Información Importante</h4>
            <ul class="text-sm text-blue-700 space-y-1">
              <li>• Los valores de stock <strong>no pueden ser negativos</strong></li>
              <li>• Los cambios se sincronizan con el servicio de inventario Rust (Puerto 8002)</li>
              <li>• El stock se actualiza en tiempo real en toda la aplicación</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl shadow-md mb-6">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="🔍 Buscar productos..."
            class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition"
          >
        </div>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
        <p class="text-gray-600 mt-4">Cargando productos...</p>
      </div>

      <div v-else-if="filteredProducts.length > 0" class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Producto</th>
                <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Precio</th>
                <th class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider">Stock Actual</th>
                <th class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider">Actualizar Stock</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="prod in filteredProducts" :key="prod.id" class="hover:bg-blue-50 transition">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                      <img v-if="prod.image_url" :src="prod.image_url" class="w-full h-full object-cover" />
                      <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                        📷
                      </div>
                    </div>
                    <div>
                      <p class="font-bold text-gray-800">{{ prod.name }}</p>
                      <p class="text-xs text-gray-500 line-clamp-1">{{ prod.description || 'Sin descripción' }}</p>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4">
                  <span class="text-lg font-bold text-green-600">${{ prod.price }}</span>
                </td>

                <td class="px-6 py-4 text-center">
                  <span class="text-2xl font-bold text-gray-800">{{ prod.stock }}</span>
                  <span class="text-sm text-gray-500 ml-1">unidades</span>
                </td>

                <td class="px-6 py-4 text-center">
                  <span :class="getStockStatusColor(prod.stock)" class="px-3 py-1 rounded-full text-xs font-bold uppercase">
                    {{ getStockStatusText(prod.stock) }}
                  </span>
                </td>

                <td class="px-6 py-4">
                  <div class="flex items-center gap-2 justify-center">
                    <input
                      v-model.number="stockInputs[prod.id]"
                      type="number"
                      min="0"
                      placeholder="Nuevo stock"
                      class="w-32 border-2 border-gray-300 p-2 rounded-lg text-center font-bold focus:border-blue-500 outline-none"
                    >
                    <button
                      @click="updateStockRust(prod.id)"
                      class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition shadow-md flex items-center gap-2"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                      </svg>
                      Actualizar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-else class="text-center py-12 bg-white rounded-lg border-2 border-dashed border-gray-300">
        <div class="text-6xl mb-4">🔍</div>
        <p class="text-gray-600 text-xl font-semibold mb-2">No se encontraron productos</p>
        <p class="text-gray-400">Intenta con otro término de búsqueda</p>
      </div>
    </div>
  </div>
</template>
