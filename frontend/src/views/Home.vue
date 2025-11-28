<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const store = useAuthStore();
const products = ref([]);

// Estados para el Formulario (Crear / Editar)
const isEditing = ref(false);
const editingId = ref(null);
const productForm = ref({ name: '', price: '', stock: '', description: '', image: null });

// Estado para inputs
const stockInputs = ref({});
const cartInputs = ref({});

// 1. Cargar los productos desde Laravel
const loadProducts = async () => {
  try {
    const res = await axios.get('http://localhost:8001/api/products');
    // Guardamos los productos en la variable reactiva
    products.value = res.data.map(p => ({
      ...p,
      // Aseguramos que stock sea un número para evitar errores visuales
      stock: parseInt(p.stock) 
    }));
  } catch (e) {
    console.error("Error cargando productos", e);
  }
};

//2. Guardar productos del laravel
const saveProduct = async () => {
  const formData = new FormData();
  formData.append('name', productForm.value.name);
  formData.append('price', productForm.value.price);
  formData.append('stock', productForm.value.stock);
  formData.append('description', productForm.value.description || '');
  if (productForm.value.image) formData.append('image', productForm.value.image);
  formData.append('features', JSON.stringify({ updated_by: store.user, date: new Date() }));

  try {
    const config = {
      headers: { Authorization: `Bearer ${store.token}`, 'Content-Type': 'multipart/form-data' }
    };

    if (isEditing.value) {
      await axios.post(`http://localhost:8001/api/products/${editingId.value}`, formData, config);
      alert('¡Producto actualizado!');
    } else {
      await axios.post('http://localhost:8001/api/products', formData, config);
      alert('¡Producto creado!');
    }
    resetForm();
    loadProducts();
  } catch (e) {
    alert(`Error: ${e.response?.data?.message || 'Fallo al guardar'}`);
  }
};

// 3. Eliminar productos del laravel
const deleteProduct = async (id) => {
  if (!confirm("¿Eliminar este producto?")) return;
  try {
    await axios.delete(`http://localhost:8001/api/products/${id}`, {
      headers: { Authorization: `Bearer ${store.token}` }
    });
    // Lo quitamos de la lista visualmente para que sea rápido
    products.value = products.value.filter(p => p.id !== id);
    alert('Producto eliminado.');
  } catch (e) {
    alert('Error al eliminar.');
  }
};

// 4. Gestionar inventario (Rust + actualización visual)

const updateStockRust = async (productId) => {
  // Obtenemos el valor del input específico de este producto
  const newStockVal = stockInputs.value[productId];

  // Validación simple
  if (newStockVal === undefined || newStockVal === "") {
    return alert("Por favor ingresa una cantidad válida");
  }

  const stockToSend = parseInt(newStockVal);

  try {
    // A. Enviamos el dato a Rust (Puerto 8002)
    await axios.post('http://localhost:8002/api/inventory', {
      product_id: productId,
      stock: stockToSend
    }, {
      headers: { Authorization: `Bearer ${store.token}` }
    });


    const productIndex = products.value.findIndex(p => p.id === productId);
    if (productIndex !== -1) {
      // Forzamos la actualización del valor visual
      products.value[productIndex].stock = stockToSend;
    }

    alert(`¡Stock sincronizado en Rust a ${stockToSend}!`);
    
    // Limpiamos el input
    stockInputs.value[productId] = ''; 

  } catch (e) {
    console.error(e);
    alert('Error: No se pudo conectar con Rust. Verifica si el contenedor está encendido y eres Admin.');
  }
};

// 5. Carrito (Laravel)

const addToCart = async (productId) => {
  const qty = cartInputs.value[productId] || 1;
  try {
    await axios.post('http://localhost:8001/api/cart', {
      product_id: productId,
      quantity: parseInt(qty)
    }, {
      headers: { Authorization: `Bearer ${store.token}` }
    });
    alert('Agregado al carrito.');
    cartInputs.value[productId] = 1;
  } catch (e) {
    alert('Error: Inicia sesión como usuario para comprar.');
  }
};

// Utiles
const handleFile = (e) => productForm.value.image = e.target.files[0];
const startEdit = (prod) => {
  isEditing.value = true;
  editingId.value = prod.id;
  productForm.value = { 
    name: prod.name, price: prod.price, stock: prod.stock, 
    description: prod.description, image: null 
  };
  window.scrollTo({ top: 0, behavior: 'smooth' });
};
const resetForm = () => {
  isEditing.value = false;
  editingId.value = null;
  productForm.value = { name: '', price: '', stock: '', description: '', image: null };
  const fileInput = document.getElementById('fileInput');
  if(fileInput) fileInput.value = '';
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
      
      <div v-if="store.isAdmin" class="bg-white p-6 rounded-xl shadow-lg mb-10 border-l-4 border-blue-600">
        <div class="flex justify-between items-center mb-6 border-b pb-2">
          <h3 class="font-bold text-xl text-gray-800">
            {{ isEditing ? 'Editar Producto' : 'Crear Nuevo Producto' }}
          </h3>
          <button v-if="isEditing" @click="resetForm" class="text-sm text-red-600 font-semibold hover:underline">Cancelar</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
          <div class="lg:col-span-2 space-y-1">
            <label class="text-xs font-bold text-gray-600 uppercase">Nombre</label>
            <input v-model="productForm.name" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Ej: Laptop Gamer">
          </div>
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-600 uppercase">Precio</label>
            <input v-model="productForm.price" type="number" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0.00">
          </div>
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-600 uppercase">Stock Inicial</label>
            <input v-model="productForm.stock" type="number" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0">
          </div>
          <div class="space-y-1">
            <label class="text-xs font-bold text-gray-600 uppercase">Imagen</label>
            <input id="fileInput" type="file" @change="handleFile" class="w-full text-xs text-gray-500 file:mr-2 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button @click="saveProduct" class="bg-blue-600 text-white px-8 py-2 rounded-lg font-semibold shadow-md hover:bg-blue-700 transition">
            {{ isEditing ? 'Guardar Cambios' : 'Crear Producto' }}
          </button>
        </div>
      </div>

      <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <span>Catálogo de Productos</span>
        <span class="text-sm font-normal text-gray-500 bg-gray-200 px-3 py-1 rounded-full">{{ products.length }} items</span>
      </h2>
      
      <div v-if="products.length === 0" class="text-center py-12 bg-white rounded-lg border border-dashed border-gray-300">
        <p class="text-gray-400 text-lg">No hay productos disponibles.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="prod in products" :key="prod.id" class="bg-white rounded-xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden flex flex-col border border-gray-100">
          
          <div class="h-48 bg-gray-100 relative group">
            <img v-if="prod.image_url" :src="prod.image_url" class="w-full h-full object-cover transition duration-500 group-hover:scale-105" />
            <div v-else class="flex flex-col items-center justify-center h-full text-gray-400">
              <span class="text-3xl">📷</span>
              <span class="text-xs mt-1">Sin Imagen</span>
            </div>
            <div v-if="store.isAdmin" class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition duration-300">
              <button @click="startEdit(prod)" class="bg-yellow-400 text-white p-2 rounded-full shadow hover:bg-yellow-500" title="Editar">✏️</button>
              <button @click="deleteProduct(prod.id)" class="bg-red-500 text-white p-2 rounded-full shadow hover:bg-red-600" title="Eliminar">🗑️</button>
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
              <div v-if="store.isAdmin" class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                <p class="text-[10px] font-bold text-blue-700 uppercase mb-1 tracking-wide">
                  Gestión de Inventario (Rust)
                </p>
                <div class="flex gap-2">
                  <input 
                    v-model="stockInputs[prod.id]" 
                    type="number" 
                    placeholder="Nuevo" 
                    class="w-full border border-blue-300 p-1 rounded-lg text-sm text-center focus:border-blue-500 outline-none"
                  >
                  <button 
                    @click="updateStockRust(prod.id)" 
                    class="bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-lg hover:bg-blue-700 transition shadow-sm"
                  >
                    Actualizar
                  </button>
                </div>
              </div>

              <div v-if="!store.isAdmin" class="flex gap-2">
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