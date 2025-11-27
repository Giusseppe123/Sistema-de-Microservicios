# 🐘 Microservicio de Productos (Products Service)

Este servicio es el núcleo de la lógica de negocio del E-Commerce. Desarrollado en **PHP** con el framework **Laravel**, se encarga de la gestión del catálogo, almacenamiento de imágenes, persistencia híbrida (SQL/NoSQL) y la gestión del carrito de compras.

## 🛠️ Stack Tecnológico

* **Lenguaje:** PHP 8.2
* **Framework:** Laravel 11
* **Base de Datos:** PostgreSQL 15 (Tablas `products`, `carts`, `cart_items`)
* **Seguridad:** Validación de JWT manual (librería `firebase/php-jwt`)
* **Almacenamiento:** Sistema de archivos local (Docker Volume) para imágenes

## 📋 Funcionalidades Clave

1. **CRUD de Productos:** Gestión completa (Crear, Leer, Actualizar, Eliminar) protegida por roles.
2. **Manejo de Imágenes:** Subida de archivos `multipart/form-data`, almacenamiento en disco público y generación de URLs accesibles.
3. **NoSQL en PostgreSQL:** Uso del tipo de dato `JSONB` para almacenar características dinámicas del producto (color, talla, especificaciones) sin alterar el esquema de la tabla.
4. **Lógica de Carrito:** Gestión de estados de carrito, adición/remoción de items y cálculo de totales.
5. **Checkout:** Proceso de compra que valida stock lógico y limpia el carrito.

## 🔧 Configuración de Entorno (.env)

El contenedor Docker inyecta la mayoría de las variables, pero asegúrate de que el archivo `.env` contenga:

```ini
APP_NAME=ProductsService
APP_ENV=local
APP_KEY=base64:...(generada por laravel)...
APP_DEBUG=true
APP_URL=http://localhost:8001

# Conexión a Base de Datos Compartida
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=microservices_db
DB_USERNAME=user_db
DB_PASSWORD=admin123

# Seguridad (Debe coincidir con Auth Service)
JWT_SECRET=una_clave_muy_secreta_y_larga_para_jwt
JWT_ALGO=HS256
```

## 🔌 API Endpoints (Puerto 8001)

### Productos (Catálogo)

| Método | Ruta | Descripción | Auth | Rol |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/products` | Listar todos los productos | No | Público |
| `GET` | `/api/products/{id}` | Ver detalle de un producto | No | Público |
| `POST` | `/api/products` | Crear producto (requiere imagen) | Sí | Admin |
| `POST` | `/api/products/{id}` | Actualizar producto (Soporta img) | Sí | Admin |
| `DELETE` | `/api/products/{id}` | Eliminar producto | Sí | Admin |

### Carrito de Compras

| Método | Ruta | Descripción | Auth | Rol |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/cart` | Ver carrito actual del usuario | Sí | User |
| `POST` | `/api/cart` | Agregar ítem al carrito | Sí | User |
| `DELETE` | `/api/cart/items/{id}` | Eliminar un ítem específico | Sí | User |
| `DELETE` | `/api/cart` | Vaciar todo el carrito | Sí | User |
| `POST` | `/api/cart/checkout` | Procesar compra (resta stock lógico) | Sí | User |

## 📦 Estructura del Proyecto

```
products-service/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ProductController.php  # CRUD de productos
│   │   │   └── CartController.php     # Lógica del carrito
│   │   └── Middleware/
│   │       └── JwtMiddleware.php      # Validación JWT
│   └── Models/
│       ├── Product.php                # Modelo de producto
│       ├── Cart.php                   # Modelo de carrito
│       └── CartItem.php               # Modelo de items del carrito
├── database/
│   └── migrations/                    # Migraciones de BD
├── public/
│   └── storage/
│       └── images/                    # Imágenes de productos
└── routes/
    └── api.php                        # Definición de rutas
```

## 🚀 Comandos Útiles

### Generar clave de aplicación
```bash
docker exec -it products_service php artisan key:generate
```

### Ejecutar migraciones
```bash
docker exec -it products_service php artisan migrate
```

### Limpiar caché
```bash
docker exec -it products_service php artisan cache:clear
docker exec -it products_service php artisan config:clear
```

### Ver logs
```bash
docker logs products_service -f
```

## 🔍 Características Técnicas

### Middleware JWT
El servicio valida tokens JWT en cada petición protegida. El middleware extrae el token del header `Authorization: Bearer <token>`, verifica la firma usando la clave secreta compartida y extrae el rol del usuario.

### Almacenamiento de Imágenes
Las imágenes se almacenan en `public/storage/images/` y se sirven a través de URLs públicas. El sistema genera nombres únicos para evitar colisiones.

### Campo JSONB
El campo `features` en la tabla `products` utiliza el tipo `JSONB` de PostgreSQL para almacenar datos dinámicos como:
```json
{
  "color": "Azul",
  "talla": "M",
  "peso": "500g",
  "updated_by": "admin",
  "date": "2024-01-15"
}
```

## 📝 Notas Importantes

- El servicio se comunica con el **Inventory Service** (Rust) durante el checkout para validar y actualizar el stock.
- Los productos eliminados también se eliminan automáticamente de todos los carritos.
- El carrito es persistente y se mantiene entre sesiones del usuario.
