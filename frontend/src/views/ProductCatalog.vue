<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import ProductDetail from '../components/ProductDetail.vue';

const store = useAuthStore();
const router = useRouter();
const products = ref([]);
const searchQuery = ref('');
const sortBy = ref('name');
const filterStock = ref('all');
const loading = ref(false);

const selectedProduct = ref(null);
const showDetailModal = ref(false);

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
  let result = products.value;

  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(product =>
      product.name.toLowerCase().includes(query) ||
      (product.description && product.description.toLowerCase().includes(query))
    );
  }

  if (filterStock.value === 'low') {
    result = result.filter(p => p.stock > 0 && p.stock < 10);
  } else if (filterStock.value === 'out') {
    result = result.filter(p => p.stock === 0);
  } else if (filterStock.value === 'available') {
    result = result.filter(p => p.stock >= 10);
  }

  result = [...result].sort((a, b) => {
    if (sortBy.value === 'name') {
      return a.name.localeCompare(b.name);
    } else if (sortBy.value === 'price-asc') {
      return parseFloat(a.price) - parseFloat(b.price);
    } else if (sortBy.value === 'price-desc') {
      return parseFloat(b.price) - parseFloat(a.price);
    } else if (sortBy.value === 'stock-asc') {
      return a.stock - b.stock;
    } else if (sortBy.value === 'stock-desc') {
      return b.stock - a.stock;
    }
    return 0;
  });

  return result;
});

const openProductDetail = (product) => {
  selectedProduct.value = product;
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  showDetailModal.value = false;
  selectedProduct.value = null;
};

const handleProductUpdated = () => {
  loadProducts();
};

const handleProductDeleted = () => {
  loadProducts();
};

onMounted(loadProducts);
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50">
    <nav class="bg-gradient-to-r from-slate-800 to-blue-800 text-white p-4 shadow-lg sticky top-0 z-40 backdrop-blur-sm bg-opacity-95">
      <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center gap-3">
          <router-link to="/admin" class="flex items-center gap-2 hover:opacity-80 transition">
            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
          </router-link>
          <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h1 class="text-xl font-bold">Catálogo de Productos</h1>
        </div>
        <div class="flex items-center gap-4">
          <span class="bg-cyan-600 text-white px-3 py-1 rounded-lg text-xs font-semibold uppercase">{{ store.role }}</span>
          <button @click="store.logout(); router.push('/login')" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition font-semibold">Salir</button>
        </div>
      </div>
    </nav>

    <div class="container mx-auto p-6">
      <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Catálogo Completo 📚</h2>
        <p class="text-gray-600">Visualiza, edita y gestiona todos tus productos</p>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-md mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-bold text-gray-700 mb-2">Buscar Productos</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar por nombre o descripción..."
                class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition"
              >
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Ordenar Por</label>
            <select v-model="sortBy" class="w-full p-3 rounded-lg border-2 border-gray-200 focus:border-blue-500 focus:outline-none transition">
              <option value="name">Nombre (A-Z)</option>
              <option value="price-asc">Precio (Menor a Mayor)</option>
              <option value="price-desc">Precio (Mayor a Menor)</option>
              <option value="stock-asc">Stock (Menor a Mayor)</option>
              <option value="stock-desc">Stock (Mayor a Menor)</option>
            </select>
          </div>
        </div>

        <div class="mt-4">
          <label class="block text-sm font-bold text-gray-700 mb-2">Filtrar por Stock</label>
          <div class="flex gap-2 flex-wrap">
            <button
              @click="filterStock = 'all'"
              :class="filterStock === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
              class="px-4 py-2 rounded-lg font-semibold transition hover:opacity-80"
            >
              Todos
            </button>
            <button
              @click="filterStock = 'available'"
              :class="filterStock === 'available' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'"
              class="px-4 py-2 rounded-lg font-semibold transition hover:opacity-80"
            >
              Disponible (≥10)
            </button>
            <button
              @click="filterStock = 'low'"
              :class="filterStock === 'low' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700'"
              class="px-4 py-2 rounded-lg font-semibold transition hover:opacity-80"
            >
              Stock Bajo (&lt;10)
            </button>
            <button
              @click="filterStock = 'out'"
              :class="filterStock === 'out' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'"
              class="px-4 py-2 rounded-lg font-semibold transition hover:opacity-80"
            >
              Agotado (0)
            </button>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <p class="text-gray-600">
          Mostrando <span class="font-bold text-blue-600">{{ filteredProducts.length }}</span> 
          {{ filteredProducts.length === 1 ? 'producto' : 'productos' }}
        </p>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
        <p class="text-gray-600 mt-4">Cargando productos...</p>
      </div>

      <div v-else-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="prod in filteredProducts"
          :key="prod.id"
          @click="openProductDetail(prod)"
          class="bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden cursor-pointer transform hover:-translate-y-2 border-2 border-transparent hover:border-blue-500"
        >
          <div class="h-48 bg-gray-100 relative">
            <img v-if="prod.image_url" :src="prod.image_url" class="w-full h-full object-cover" />
            <div v-else class="flex flex-col items-center justify-center h-full text-gray-400">
              <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              <span class="text-xs mt-1">Sin Imagen</span>
            </div>

            <div class="absolute top-2 right-2">
              <span
                :class="prod.stock === 0 ? 'bg-red-500' : prod.stock < 10 ? 'bg-yellow-500' : 'bg-green-500'"
                class="px-3 py-1 rounded-full text-white text-xs font-bold shadow-lg"
              >
                {{ prod.stock }} unidades
              </span>
            </div>
          </div>

          <div class="p-5">
            <h3 class="font-bold text-lg text-gray-800 mb-1 line-clamp-1">{{ prod.name }}</h3>
            <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ prod.description || 'Sin descripción' }}</p>

            <div class="flex justify-between items-center">
              <span class="text-2xl font-bold text-green-600">${{ prod.price }}</span>
              <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                Ver Detalles
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12 bg-white rounded-lg border-2 border-dashed border-gray-300">
        <div class="text-6xl mb-4">🔍</div>
        <p class="text-gray-600 text-xl font-semibold mb-2">No se encontraron productos</p>
        <p class="text-gray-400">Intenta ajustar los filtros de búsqueda</p>
      </div>
    </div>

    <ProductDetail
      :product="selectedProduct"
      :show="showDetailModal"
      @close="closeDetailModal"
      @updated="handleProductUpdated"
      @deleted="handleProductDeleted"
    />
  </div>
</template>
