### 2. README FRONTEND (Vue.js)
**Ubicación:** `frontend/README.md`

```markdown
# 🎨 Frontend - Vue.js 3

Cliente web desarrollado con **Vue.js 3 (Composition API)**, **Vite** y **Tailwind CSS**. Consume los tres microservicios del sistema.

## 🛠 Estructura del Proyecto

* `src/stores/auth.js`: **Pinia Store**. Maneja el estado global de autenticación (Token JWT, Rol de Usuario).
* `src/views/`:
    * `Login.vue` / `Register.vue` / `Verify.vue`: Interfaz con el microservicio de Python.
    * `Home.vue`: Dashboard principal. Consume Laravel (Productos) y Rust (Inventario).
    * `Cart.vue`: Carrito de compras. Consume Laravel.
* `src/router/`: Configuración de rutas y **Guardias de Navegación** (protección de rutas privadas).

## ✨ Características Implementadas

1. **Gestión de Roles:** La interfaz se adapta dinámicamente.
    * **Admin:** Ve paneles de edición, creación de productos y gestión de stock (Rust).
    * **User:** Ve botones de compra y acceso al carrito.
2. **Validación de Formularios:** Regex para contraseñas seguras y confirmación de doble contraseña.
3. **Feedback Visual:** Actualización optimista del stock en la UI al interactuar con Rust.
4. **Diseño Moderno:** Esquema de colores profesional azul/gris, glassmorphism y animaciones suaves.

## 🎨 Diseño

- **Colores:** Paleta profesional azul/gris (slate-800, blue-800, cyan-600)
- **Componentes:** Iconos SVG, glassmorphism, animaciones CSS
- **Responsive:** Diseño adaptable a diferentes tamaños de pantalla

## 📦 Comandos Docker (Desarrollo)

El servicio se levanta automáticamente con el `docker-compose` principal, pero para reconstruir solo el frontend:

```bash
docker-compose up -d --build frontend
```

## 🚀 Desarrollo Local

```bash
# Instalar dependencias
npm install

# Servidor de desarrollo
npm run dev

# Build para producción
npm run build
```

## 🔌 Conexión con Backend

El frontend se conecta a los siguientes servicios:

- **Auth Service (Python):** `http://localhost:8000`
- **Products Service (PHP):** `http://localhost:8001`
- **Inventory Service (Rust):** `http://localhost:8002`

## 📝 Rutas Principales

| Ruta | Componente | Descripción | Protegida |
| :--- | :--- | :--- | :--- |
| `/login` | Login.vue | Inicio de sesión | No |
| `/register` | Register.vue | Registro de usuario | No |
| `/verify` | Verify.vue | Verificación de cuenta | No |
| `/` | Home.vue | Dashboard principal | Sí |
| `/cart` | Cart.vue | Carrito de compras | Sí (User) |

## 🔐 Autenticación

El frontend utiliza Pinia para gestionar el estado de autenticación:
- Token JWT almacenado en `localStorage`
- Decodificación del token para extraer rol y usuario
- Guards de navegación para proteger rutas privadas
```