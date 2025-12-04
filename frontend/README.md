#  Frontend - Sistema de Microservicios

## Descripción

Frontend desarrollado con **Vue.js 3** y **Tailwind CSS** que proporciona una interfaz moderna y profesional para el sistema de e-commerce distribuido.

##  Tecnologías

- **Vue.js 3** - Framework progresivo de JavaScript
- **Vue Router** - Enrutamiento SPA
- **Pinia** - Gestión de estado
- **Axios** - Cliente HTTP
- **Tailwind CSS** - Framework de CSS utility-first
- **Vite** - Build tool y dev server

---

## 🛣️ Rutas de la Aplicación

### Rutas Públicas (sin autenticación)

| Ruta | Componente | Descripción |
|------|-----------|-------------|
| `/login` | Login.vue | Iniciar sesión |
| `/register` | Register.vue | Registrar nueva cuenta |
| `/verify` | Verify.vue | Verificar cuenta con código |

### Rutas de Usuario (requiere autenticación)

| Ruta | Componente | Descripción |
|------|-----------|-------------|
| `/` | Home.vue | Catálogo de productos (tienda) |
| `/cart` | Cart.vue | Carrito de compras |

### Rutas de Administrador (requiere rol admin)

| Ruta | Componente | Descripción |
|------|-----------|-------------|
| `/admin` | AdminDashboard.vue | Dashboard con estadísticas |
| `/admin/inventory` | InventoryManagement.vue | Gestión de inventario |
| `/admin/catalog` | ProductCatalog.vue | Catálogo completo |
| `/admin/create` | CreateProduct.vue | Crear nuevo producto |

---

##  Funcionalidades Principales

###  Vista Previa de Imágenes
Al crear un producto, puedes ver la imagen antes de subirla usando FileReader API.

###  Validación de Stock Negativo
- Frontend: Input HTML5 con `min="0"`
- Frontend: Validación JavaScript
- Backend: Validación en servicio Rust

###  Panel de Administrador
- Dashboard con estadísticas en tiempo real
- Gestión de inventario con tabla profesional
- Catálogo con búsqueda, filtros y ordenamiento
- Modal de detalles para ver/editar productos

###  Interfaz Moderna
- Gradientes y animaciones suaves
- Diseño responsivo
- Iconos SVG personalizados
- Indicadores de estado de stock

---

##  Desarrollo Local

```bash
cd frontend
npm install
npm run dev
```

La aplicación estará disponible en: http://localhost:5173

---

## 📝 Notas Importantes

- ⚠️ Para ser administrador, el **username debe contener "admin"** al registrarse
- 🔒 Todas las rutas admin están protegidas con guardias de ruta
- 🖼️ Las imágenes se suben como `multipart/form-data`
- 💾 El token JWT se guarda en `localStorage`

---

Para más detalles, consulta el [README principal](../README.md) y el [walkthrough.md](../.gemini/antigravity/brain/076d9d45-e168-4539-8be9-5819abc94191/walkthrough.md).