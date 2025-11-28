import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import { createPinia } from 'pinia'
import router from './router' // Importamos el router

const app = createApp(App)

app.use(createPinia()) // Activamos Pinia
app.use(router)        // Activamos el Router

app.mount('#app')