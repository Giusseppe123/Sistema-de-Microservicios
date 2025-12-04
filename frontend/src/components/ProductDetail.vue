<script setup>
import { defineProps, defineEmits, ref, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const props = defineProps({
  product: Object,
  show: Boolean
});

const emit = defineEmits(['close', 'updated', 'deleted']);
const store = useAuthStore();

const isEditing = ref(false);
const editForm = ref({
  name: '',
  price: '',
  stock: '',
  description: '',
  image: null
});
const stockInput = ref('');

watch(() => props.product, (newProduct) => {
  if (newProduct) {
    editForm.value = {
      name: newProduct.name,
      price: newProduct.price,
      stock: newProduct.stock,
      description: newProduct.description || '',
      image: null
    };
  }
}, { immediate: true });

const closeModal = () => {
  isEditing.value = false;
  emit('close');
};

const toggleEdit = () => {
  isEditing.value = !isEditing.value;
  if (!isEditing.value) {
    editForm.value = {
      name: props.product.name,
      price: props.product.price,
      stock: props.product.stock,
      description: props.product.description || '',
      image: null
    };
  }
};

const handleFile = (e) => {
  editForm.value.image = e.target.files[0];
};

const saveChanges = async () => {
  const formData = new FormData();
  formData.append('name', editForm.value.name);
  formData.append('price', editForm.value.price);
  formData.append('stock', editForm.value.stock);
  formData.append('description', editForm.value.description);
  if (editForm.value.image) formData.append('image', editForm.value.image);
  formData.append('features', JSON.stringify({ updated_by: store.user, date: new Date() }));

  try {
    await axios.post(`http://localhost:8001/api/products/${props.product.id}`, formData, {
      headers: { Authorization: `Bearer ${store.token}`, 'Content-Type': 'multipart/form-data' }
    });
    alert('✅ Producto actualizado exitosamente');
    isEditing.value = false;
    emit('updated');
  } catch (e) {
    alert(`❌ Error: ${e.response?.data?.message || 'Fallo al actualizar'}`);
  }
};

const deleteProduct = async () => {
  if (!confirm(`¿Estás seguro de eliminar "${props.product.name}"?`)) return;
  
  try {
    await axios.delete(`http://localhost:8001/api/products/${props.product.id}`, {
      headers: { Authorization: `Bearer ${store.token}` }
    });
    alert('✅ Producto eliminado');
    emit('deleted');
    closeModal();
  } catch (e) {
    alert('❌ Error al eliminar producto');
  }
};

const updateStock = async () => {
  if (!stockInput.value || stockInput.value < 0) {
    return alert('❌ Ingresa un valor de stock válido (no negativo)');
  }

  try {
    await axios.post('http://localhost:8002/api/inventory', {
      product_id: props.product.id,
      stock: parseInt(stockInput.value)
    }, {
      headers: { Authorization: `Bearer ${store.token}` }
    });
    alert(`✅ Stock actualizado a ${stockInput.value}`);
    stockInput.value = '';
    emit('updated');
  } catch (e) {
    alert('❌ Error al actualizar stock');
  }
};
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show && product" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeModal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
          <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-cyan-600 text-white p-6 rounded-t-2xl flex justify-between items-center z-10">
            <h2 class="text-2xl font-bold">{{ isEditing ? 'Editar Producto' : 'Detalles del Producto' }}</h2>
            <button @click="closeModal" class="text-white hover:bg-white/20 p-2 rounded-full transition">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 shadow-lg">
                  <img v-if="product.image_url" :src="product.image_url" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="mt-2">Sin imagen</span>
                  </div>
                </div>

                <div v-if="isEditing" class="mt-4">
                  <label class="block text-sm font-bold text-gray-700 mb-2">Cambiar Imagen</label>
                  <input type="file" @change="handleFile" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>
              </div>

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Nombre del Producto</label>
                  <input
                    v-if="isEditing"
                    v-model="editForm.name"
                    class="w-full border-2 border-gray-300 p-3 rounded-lg focus:border-blue-500 outline-none"
                  />
                  <p v-else class="text-2xl font-bold text-gray-800">{{ product.name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Precio</label>
                  <input
                    v-if="isEditing"
                    v-model="editForm.price"
                    type="number"
                    min="0"
                    step="0.01"
                    class="w-full border-2 border-gray-300 p-3 rounded-lg focus:border-blue-500 outline-none"
                  />
                  <p v-else class="text-3xl font-bold text-green-600">${{ product.price }}</p>
                </div>

                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Stock Actual</label>
                  <input
                    v-if="isEditing"
                    v-model="editForm.stock"
                    type="number"
                    min="0"
                    class="w-full border-2 border-gray-300 p-3 rounded-lg focus:border-blue-500 outline-none"
                  />
                  <div v-else class="flex items-center gap-3">
                    <span class="text-2xl font-bold text-gray-800">{{ product.stock }}</span>
                    <span class="text-gray-500">unidades</span>
                    <span
                      :class="product.stock === 0 ? 'bg-red-100 text-red-600' : product.stock < 10 ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600'"
                      class="px-3 py-1 rounded-full text-xs font-bold uppercase"
                    >
                      {{ product.stock === 0 ? 'Agotado' : product.stock < 10 ? 'Stock Bajo' : 'Disponible' }}
                    </span>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Descripción</label>
                  <textarea
                    v-if="isEditing"
                    v-model="editForm.description"
                    rows="4"
                    class="w-full border-2 border-gray-300 p-3 rounded-lg focus:border-blue-500 outline-none resize-none"
                  ></textarea>
                  <p v-else class="text-gray-600">{{ product.description || 'Sin descripción' }}</p>
                </div>

                <div v-if="!isEditing" class="bg-blue-50 p-4 rounded-lg border-2 border-blue-200">
                  <label class="block text-sm font-bold text-blue-800 mb-2">Actualización Rápida de Stock</label>
                  <div class="flex gap-2">
                    <input
                      v-model.number="stockInput"
                      type="number"
                      min="0"
                      placeholder="Nuevo stock"
                      class="flex-1 border-2 border-blue-300 p-2 rounded-lg text-center font-bold focus:border-blue-500 outline-none"
                    />
                    <button
                      @click="updateStock"
                      class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition"
                    >
                      Actualizar
                    </button>
                  </div>
                  <p class="text-xs text-blue-600 mt-2">Sincroniza con el servicio Rust</p>
                </div>
              </div>
            </div>

            <div class="mt-8 flex gap-3 justify-end border-t pt-6">
              <button
                v-if="!isEditing"
                @click="deleteProduct"
                class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition flex items-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Eliminar
              </button>

              <button
                v-if="isEditing"
                @click="toggleEdit"
                class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition"
              >
                Cancelar
              </button>

              <button
                v-if="isEditing"
                @click="saveChanges"
                class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Guardar Cambios
              </button>

              <button
                v-if="!isEditing"
                @click="toggleEdit"
                class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .bg-white,
.modal-leave-active .bg-white {
  transition: transform 0.3s ease;
}

.modal-enter-from .bg-white,
.modal-leave-to .bg-white {
  transform: scale(0.9);
}
</style>
