<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const store = useAuthStore();
const router = useRouter();

const productForm = ref({
  name: '',
  price: '',
  stock: '',
  description: '',
  image: null
});

const imagePreview = ref(null);
const loading = ref(false);

const handleFile = (e) => {
  const file = e.target.files[0];
  if (file) {
    productForm.value.image = file;
    
    const reader = new FileReader();
    reader.onload = (event) => {
      imagePreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeImage = () => {
  productForm.value.image = null;
  imagePreview.value = null;
  const fileInput = document.getElementById('fileInput');
  if (fileInput) fileInput.value = '';
};

const createProduct = async () => {
  if (!productForm.value.name || !productForm.value.price || !productForm.value.stock) {
    return alert(' Por favor completa todos los campos obligatorios');
  }

  if (parseFloat(productForm.value.price) < 0) {
    return alert(' El precio no puede ser negativo');
  }

  if (parseInt(productForm.value.stock) < 0) {
    return alert(' El stock no puede ser negativo');
  }

  loading.value = true;

  const formData = new FormData();
  formData.append('name', productForm.value.name);
  formData.append('price', productForm.value.price);
  formData.append('stock', productForm.value.stock);
  formData.append('description', productForm.value.description || '');
  if (productForm.value.image) formData.append('image', productForm.value.image);
  formData.append('features', JSON.stringify({ created_by: store.user, date: new Date() }));

  try {
    const config = {
      headers: { Authorization: `Bearer ${store.token}`, 'Content-Type': 'multipart/form-data' }
    };

    await axios.post('http://localhost:8001/api/products', formData, config);
    alert('✅ ¡Producto creado exitosamente!');
    resetForm();
    
    const goToCatalog = confirm('¿Deseas ver el catálogo de productos?');
    if (goToCatalog) {
      router.push('/admin/catalog');
    }
  } catch (e) {
    alert(`❌ Error: ${e.response?.data?.message || 'Fallo al crear producto'}`);
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  productForm.value = {
    name: '',
    price: '',
    stock: '',
    description: '',
    image: null
  };
  imagePreview.value = null;
  const fileInput = document.getElementById('fileInput');
  if (fileInput) fileInput.value = '';
};
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <h1 class="text-xl font-bold">Crear Nuevo Producto</h1>
        </div>
        <div class="flex items-center gap-4">
          <span class="bg-cyan-600 text-white px-3 py-1 rounded-lg text-xs font-semibold uppercase">{{ store.role }}</span>
          <button @click="store.logout(); router.push('/login')" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm transition font-semibold">Salir</button>
        </div>
      </div>
    </nav>

    <div class="container mx-auto p-6 max-w-4xl">
      <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Agregar Producto ➕</h2>
        <p class="text-gray-600">Completa la información del nuevo producto</p>
      </div>

      <div class="bg-white rounded-xl shadow-lg p-8">
        <form @submit.prevent="createProduct">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-6">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                  Nombre del Producto <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="productForm.name"
                  type="text"
                  required
                  placeholder="Ej: Laptop Gamer"
                  class="w-full border-2 border-gray-300 p-3 rounded-lg focus:border-blue-500 outline-none transition"
                />
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                  Precio <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-3 text-gray-500 font-bold">$</span>
                  <input
                    v-model="productForm.price"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                    placeholder="0.00"
                    class="w-full border-2 border-gray-300 p-3 pl-8 rounded-lg focus:border-blue-500 outline-none transition"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                  Stock Inicial <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="productForm.stock"
                  type="number"
                  min="0"
                  required
                  placeholder="0"
                  class="w-full border-2 border-gray-300 p-3 rounded-lg focus:border-blue-500 outline-none transition"
                />
                <p class="text-xs text-gray-500 mt-1">El stock no puede ser negativo</p>
              </div>

              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                  Descripción
                </label>
                <textarea
                  v-model="productForm.description"
                  rows="4"
                  placeholder="Describe las características del producto..."
                  class="w-full border-2 border-gray-300 p-3 rounded-lg focus:border-blue-500 outline-none transition resize-none"
                ></textarea>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">
                  Imagen del Producto
                </label>
                
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition">
                  <input
                    id="fileInput"
                    type="file"
                    @change="handleFile"
                    accept="image/*"
                    class="hidden"
                  />
                  <label for="fileInput" class="cursor-pointer">
                    <div class="flex flex-col items-center">
                      <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <span class="text-sm text-gray-600 font-semibold">Haz clic para subir imagen</span>
                      <span class="text-xs text-gray-400 mt-1">PNG, JPG, GIF hasta 10MB</span>
                    </div>
                  </label>
                </div>

                <div v-if="imagePreview" class="mt-4">
                  <div class="relative">
                    <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 shadow-lg border-4 border-green-500">
                      <img :src="imagePreview" class="w-full h-full object-cover" />
                    </div>
                    <button
                      type="button"
                      @click="removeImage"
                      class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full shadow-lg hover:bg-red-600 transition"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </div>
                  <div class="mt-2 bg-green-50 border-l-4 border-green-500 p-3 rounded">
                    <p class="text-sm text-green-700 font-semibold flex items-center gap-2">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                      Vista previa de la imagen
                    </p>
                    <p class="text-xs text-green-600 mt-1">Esta imagen se subirá con el producto</p>
                  </div>
                </div>

                <div v-else class="mt-4 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                  <svg class="w-16 h-16 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <p class="text-sm text-gray-500">Sin imagen seleccionada</p>
                  <p class="text-xs text-gray-400 mt-1">La vista previa aparecerá aquí</p>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-8 flex gap-4 justify-end border-t pt-6">
            <button
              type="button"
              @click="resetForm"
              class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition"
            >
              Limpiar Formulario
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-green-700 hover:to-emerald-700 transition shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              <div v-else class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              {{ loading ? 'Creando...' : 'Crear Producto' }}
            </button>
          </div>
        </form>
      </div>

      <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex items-start gap-3">
          <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <h4 class="font-bold text-blue-800 mb-1">Consejos</h4>
            <ul class="text-sm text-blue-700 space-y-1">
              <li>• Los campos marcados con <span class="text-red-500">*</span> son obligatorios</li>
              <li>• Puedes ver la imagen antes de crear el producto</li>
              <li>• El precio y stock no pueden ser negativos</li>
              <li>• Usa descripciones claras para ayudar a los clientes</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
