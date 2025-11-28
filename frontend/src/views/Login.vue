<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const store = useAuthStore();
const router = useRouter();
const email = ref('');
const password = ref('');
const errorMsg = ref('');
const loading = ref(false);

const login = async () => {
  errorMsg.value = '';
  loading.value = true;
  
  try {
    await store.login(email.value, password.value);
    router.push('/');
  } catch (e) {
    errorMsg.value = e.response?.data?.detail || "Credenciales incorrectas. Verifica tu email y contraseña.";
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-blue-900 to-slate-800 relative overflow-hidden">
    
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-2000"></div>
    <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-4000"></div>

    
    <div class="relative z-10 w-full max-w-md px-6">
      
      <div class="text-center mb-8 animate-fade-in-down">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl shadow-2xl mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">
          Iniciar Sesión
        </h1>
        <p class="text-slate-300 text-sm">Accede a tu cuenta</p>
      </div>

      <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-2xl p-8 border border-white/20 animate-fade-in-up">
        
        <div v-if="errorMsg" class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-100 text-sm backdrop-blur-sm animate-shake">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ errorMsg }}</span>
          </div>
        </div>

        <form @submit.prevent="login" class="space-y-5">
          
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-200">
              Correo Electrónico
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                </svg>
              </div>
              <input 
                v-model="email" 
                type="email" 
                placeholder="correo@ejemplo.com"
                class="w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-blue-400 focus:bg-white/15 transition duration-200"
                required 
              />
            </div>
          </div>

          
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-200">
              Contraseña
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
              </div>
              <input 
                v-model="password" 
                type="password" 
                placeholder="••••••••"
                class="w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-blue-400 focus:bg-white/15 transition duration-200"
                required 
              />
            </div>
          </div>

          
          <button 
            type="submit"
            :disabled="loading"
            class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 text-white py-3 rounded-lg font-semibold shadow-lg hover:shadow-blue-500/50 transform hover:scale-[1.02] active:scale-[0.98] transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none relative overflow-hidden group mt-6"
          >
            <span v-if="!loading" class="relative z-10">Iniciar Sesión</span>
            <span v-else class="relative z-10 flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Iniciando...
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-cyan-600 to-blue-600 opacity-0 group-hover:opacity-100 transition duration-200"></div>
          </button>
        </form>

        
        <div class="mt-6 pt-6 border-t border-white/10">
          <div class="flex flex-col sm:flex-row justify-between items-center gap-3 text-sm">
            <router-link 
              to="/register" 
              class="text-blue-300 hover:text-white font-medium transition duration-200"
            >
              Crear Cuenta Nueva
            </router-link>
            <router-link 
              to="/verify" 
              class="text-slate-400 hover:text-slate-200 transition duration-200"
            >
              Verificar Código
            </router-link>
          </div>
        </div>
      </div>

      
      <div class="text-center mt-6 text-slate-400 text-xs animate-fade-in">
        <p>Sistema de Microservicios Distribuidos</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes blob {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
}

@keyframes fade-in-down {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

.animate-fade-in-down {
  animation: fade-in-down 0.6s ease-out;
}

.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out 0.2s both;
}

.animate-fade-in {
  animation: fade-in 0.8s ease-out 0.4s both;
}

.animate-shake {
  animation: shake 0.5s ease-in-out;
}
</style>