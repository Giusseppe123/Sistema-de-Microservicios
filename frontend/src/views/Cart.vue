<script setup>
import { onMounted, ref, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const store = useAuthStore();
const router = useRouter();
const cart = ref(null);
const loading = ref(true);
const processing = ref(false);

// Calcular Total
const total = computed(() => {
  if (!cart.value || !cart.value.items) return 0;
  return cart.value.items.reduce((acc, item) => {
    return acc + (parseFloat(item.product.price) * item.quantity);
  }, 0);
});

// Cargar Carrito
const loadCart = async () => {
  try {
    const res = await axios.get('http://localhost:8001/api/cart', {
      headers: { Authorization: `Bearer ${store.token}` }
    });
    cart.value = res.data;
  } catch (e) {
    console.error("Error cargando carrito", e);
  } finally {
    loading.value = false;
  }
};

// --- ELIMINAR UN ITEM ---
const removeItem = async (itemId) => {
  if(!confirm("¿Quitar este producto?")) return;
  try {
    await axios.delete(`http://localhost:8001/api/cart/items/${itemId}`, {
      headers: { Authorization: `Bearer ${store.token}` }
    });
    loadCart(); // Recargar lista
  } catch (e) {
    alert("Error eliminando producto");
  }
};

// --- VACIAR CARRITO ---
const clearCart = async () => {
  if(!confirm("¿Estás seguro de vaciar todo el carrito?")) return;
  try {
    await axios.delete('http://localhost:8001/api/cart', {
      headers: { Authorization: `Bearer ${store.token}` }
    });
    loadCart(); // Recargar lista (quedará vacía)
  } catch (e) {
    alert("Error vaciando carrito");
  }
};

// --- PROCESAR PAGO ---
const processPayment = async () => {
  if (!confirm(`¿Pagar $${total.value.toFixed(2)}?`)) return;
  processing.value = true;

  try {
    // 1. Actualizar Rust
    for (const item of cart.value.items) {
      const currentStock = parseInt(item.product.stock);
      const newStock = currentStock - item.quantity;
      await axios.post('http://localhost:8002/api/inventory', {
        product_id: item.product_id,
        stock: newStock >= 0 ? newStock : 0
      }, { headers: { Authorization: `Bearer ${store.token}` } });
    }

    // 2. Checkout Laravel
    await axios.post('http://localhost:8001/api/cart/checkout', {}, {
      headers: { Authorization: `Bearer ${store.token}` }
    });

    alert('¡Compra exitosa!');
    router.push('/');

  } catch (e) {
    alert(`Error en el pago: ${e.response?.data?.error || 'Fallo de conexión'}`);
  } finally {
    processing.value = false;
  }
};

onMounted(loadCart);
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-8 font-sans">
    <div class="max-w-5xl mx-auto">
      
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
          <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
          Mi Carrito
        </h1>
        <div class="flex gap-4">
          <button 
            v-if="cart && cart.items && cart.items.length > 0" 
            @click="clearCart" 
            class="text-red-500 hover:text-red-700 font-bold underline text-sm"
          >
            Vaciar Carrito
          </button>
          <router-link to="/" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
            <span>←</span> Seguir Comprando
          </router-link>
        </div>
      </div>

      <div v-if="loading" class="text-center py-12 text-gray-500 text-lg">Cargando...</div>

      <div v-else-if="!cart || !cart.items || cart.items.length === 0" class="bg-white p-12 rounded-xl shadow-sm text-center border border-gray-100">
        <div class="text-6xl mb-4">🛍️</div>
        <p class="text-gray-500 text-xl mb-6">Tu carrito está vacío</p>
        <router-link to="/" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-md">
          Ir a la Tienda
        </router-link>
      </div>

      <div v-else class="flex flex-col lg:flex-row gap-8">
        
        <div class="flex-1 bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
          <table class="w-full text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
              <tr>
                <th class="p-4">Producto</th>
                <th class="p-4 text-center">Cant.</th>
                <th class="p-4 text-right">Total</th>
                <th class="p-4 text-center">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="item in cart.items" :key="item.id" class="hover:bg-gray-50 transition">
                <td class="p-4 flex items-center gap-4">
                  <img :src="item.product.image_url" v-if="item.product.image_url" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                  <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-xs text-gray-400" v-else>No img</div>
                  <div>
                    <p class="font-bold text-gray-800">{{ item.product.name }}</p>
                    <span class="text-xs text-gray-500">${{ item.product.price }} c/u</span>
                  </div>
                </td>
                <td class="p-4 text-center">
                  <span class="font-bold bg-gray-100 px-2 py-1 rounded text-sm">{{ item.quantity }}</span>
                </td>
                <td class="p-4 text-right font-bold text-indigo-600">
                  ${{ (item.product.price * item.quantity).toFixed(2) }}
                </td>
                <td class="p-4 text-center">
                  <button @click="removeItem(item.id)" class="text-red-400 hover:text-red-600 transition text-xl" title="Eliminar">
                    🗑️
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="lg:w-80">
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 sticky top-24">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Resumen</h3>
            <div class="flex justify-between items-center mb-6">
              <span class="text-gray-600">Total:</span>
              <span class="text-3xl font-bold text-green-600">${{ total.toFixed(2) }}</span>
            </div>
            <button 
              @click="processPayment" 
              :disabled="processing"
              class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 text-white py-3 rounded-lg font-semibold shadow-lg hover:shadow-cyan-500/50 transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ processing ? 'Procesando...' : 'Pagar Ahora' }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>