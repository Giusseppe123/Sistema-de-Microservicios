# Documento Técnico - Sistema Distribuido de Microservicios

## Información del Proyecto

**Nombre del Proyecto:** Sistema de E-Commerce Distribuido con Microservicios  


**Integrantes del Equipo:**
- [Luis Chirivella] - Backend FastAPI
- [Alvaro Lugo] - Backend Laravel
- [Jose Mendez] - Backend Rust
- [Giusseppe Marinelly] - Frontend Vue.js Integración y Documentación

---

## 1. Descripción General del Sistema

Este proyecto implementa una **arquitectura de microservicios políglota** para un sistema de comercio electrónico, donde cada servicio está desarrollado con un lenguaje y framework diferente, demostrando la interoperabilidad y comunicación entre tecnologías heterogéneas.

### Objetivos del Sistema

1. **Demostrar arquitectura de microservicios** con servicios independientes y desacoplados
2. **Implementar autenticación distribuida** usando JWT (JSON Web Tokens)
3. **Gestionar datos de forma híbrida** combinando SQL relacional y NoSQL (JSONB)
4. **Orquestar servicios** mediante Docker Compose
5. **Crear una interfaz moderna** que consuma los microservicios de forma transparente

---

## 2. Arquitectura del Sistema





### 2.2 Tecnologías Utilizadas

| Componente | Tecnología | Versión | Justificación |
|------------|------------|---------|---------------|
| **Auth Service** | Python + FastAPI | 3.9 / 0.109.0 | Rápido desarrollo de APIs, excelente para autenticación |
| **Products Service** | PHP + Laravel | 8.2 / 11.x | Framework maduro con ORM potente (Eloquent) |
| **Inventory Service** | Rust + Axum | Latest | Alto rendimiento para operaciones críticas de stock |
| **Frontend** | Vue.js 3 | 3.x | Framework reactivo moderno con Composition API |
| **Base de Datos** | PostgreSQL | 15 | Soporte SQL + NoSQL (JSONB) en un solo motor |
| **Orquestación** | Docker Compose | 3.8 | Gestión simplificada de contenedores |
| **Estilos** | Tailwind CSS | 3.x | Utility-first CSS para desarrollo rápido |

---

## 3. Descripción Detallada de Microservicios

### 3.1 Auth Service (Python + FastAPI)

**Responsabilidad:** Gestión de identidad, autenticación y autorización.

#### Endpoints

| Método | Ruta | Descripción | Autenticación |
|--------|------|-------------|---------------|
| POST | `/register` | Registro de nuevos usuarios | No |
| POST | `/verify` | Verificación de cuenta con código | No |
| POST | `/login` | Inicio de sesión y generación de JWT | No |



#### Características Técnicas

- **Hashing de contraseñas:** bcrypt con salt automático
- **Generación de códigos:** 6 dígitos aleatorios
- **Roles:** Sistema de roles (admin/user) basado en username
- **Persistencia:** SQLAlchemy ORM con PostgreSQL
- **CORS:** Configurado para permitir peticiones del frontend

#### Modelo de Datos

```python
class User:
    id: Integer (PK)
    username: String(50)
    name: String(100)
    email: String(100) UNIQUE
    hashed_password: String(255)
    verification_code: String(6) NULLABLE
    is_verified: Boolean DEFAULT False
    role: String(20) DEFAULT 'user'
    created_at: DateTime
```

---

### 3.2 Products Service (PHP + Laravel)

**Responsabilidad:** Gestión del catálogo de productos y carrito de compras.

#### Endpoints Principales

**Productos:**

| Método | Ruta | Descripción | Autenticación | Rol |
|--------|------|-------------|---------------|-----|
| GET | `/api/products` | Listar todos los productos | No | - |
| GET | `/api/products/{id}` | Obtener un producto | No | - |
| POST | `/api/products` | Crear producto | Sí | Admin |
| POST | `/api/products/{id}` | Actualizar producto | Sí | Admin |
| DELETE | `/api/products/{id}` | Eliminar producto | Sí | Admin |

**Carrito:**

| Método | Ruta | Descripción | Autenticación | Rol |
|--------|------|-------------|---------------|-----|
| POST | `/api/cart` | Agregar al carrito | Sí | User |
| GET | `/api/cart` | Ver mi carrito | Sí | User |
| DELETE | `/api/cart/items/{id}` | Eliminar item | Sí | User |
| DELETE | `/api/cart` | Vaciar carrito | Sí | User |
| POST | `/api/cart/checkout` | Procesar pago | Sí | User |

#### Características Técnicas

- **ORM:** Eloquent para interacción con base de datos
- **Validación JWT:** Middleware personalizado `JwtMiddleware`
- **Upload de imágenes:** Storage en `public/products`
- **JSONB:** Campo `features` para datos no estructurados
- **Relaciones:** Cart hasMany CartItems, CartItem belongsTo Product

#### Modelos de Datos

```php
// Product
id: bigint (PK)
name: varchar(255)
description: text NULLABLE
price: decimal(10,2)
stock: integer
image_url: varchar(500) NULLABLE
features: jsonb NULLABLE
created_at: timestamp
updated_at: timestamp

// Cart
id: bigint (PK)
user_id: integer (FK a Auth Service)
status: varchar(20) DEFAULT 'active'
created_at: timestamp
updated_at: timestamp

// CartItem
id: bigint (PK)
cart_id: bigint (FK)
product_id: bigint (FK)
quantity: integer
created_at: timestamp
updated_at: timestamp
```



---

### 3.3 Inventory Service (Rust + Axum)

**Responsabilidad:** Gestión de alto rendimiento del inventario y stock.

#### Endpoints

| Método | Ruta | Descripción | Autenticación |
|--------|------|-------------|---------------|
| GET | `/api/inventory/{id}` | Consultar stock de producto | Sí |
| POST | `/api/inventory` | Actualizar stock | Sí |

#### Características Técnicas

- **Framework:** Axum (async web framework de Rust)
- **Base de datos:** SQLx con soporte async para PostgreSQL
- **Validación JWT:** Middleware usando `jsonwebtoken` crate
- **CORS:** Tower-HTTP middleware
- **Concurrencia:** Tokio runtime para operaciones asíncronas

#### Modelo de Datos

```rust
struct InventoryItem {
    product_id: i32 (PK)
    stock: i32
}
```

#### Operación UPSERT

```sql
INSERT INTO inventory (product_id, stock) 
VALUES ($1, $2)
ON CONFLICT (product_id) 
DO UPDATE SET stock = EXCLUDED.stock
RETURNING product_id, stock
```

Esta operación garantiza que:
- Si el producto no existe en inventory, se crea
- Si ya existe, se actualiza el stock
- Todo en una sola operación atómica

---

### 3.4 Frontend (Vue.js 3)

**Responsabilidad:** Interfaz de usuario y orquestación de microservicios.

#### Estructura de Componentes

```
src/
├── views/
│   ├── Login.vue          # Autenticación
│   ├── Register.vue       # Registro de usuarios
│   ├── Verify.vue         # Verificación de cuenta
│   ├── Home.vue           # Catálogo y gestión
│   └── Cart.vue           # Carrito de compras
├── stores/
│   └── auth.js            # Pinia store (JWT, roles)
├── router/
│   └── index.js           # Vue Router con guards
└── main.js
```

#### Gestión de Estado (Pinia)

```javascript
// auth.js
state: {
  token: localStorage.getItem('token'),
  user: null,
  role: null
}

actions: {
  login(email, password)    // Auth Service
  logout()                  // Limpiar estado
  initialize()              // Decodificar JWT al cargar
}

getters: {
  isAuthenticated           // !!token
  isAdmin                   // role === 'admin'
}
```

#### Protección de Rutas

```javascript
router.beforeEach((to, from, next) => {
  const store = useAuthStore();
  store.initialize();
  
  if (to.meta.requiresAuth && !store.isAuthenticated) {
    next('/login');
  } else {
    next();
  }
});
```

---

## 4. Autenticación con JWT (JSON Web Tokens)

### 4.1 ¿Qué es JWT?

JWT es un estándar abierto (RFC 7519) que define una forma compacta y autónoma de transmitir información de forma segura entre partes como un objeto JSON. Esta información puede ser verificada y confiable porque está firmada digitalmente.

### 4.2 Estructura de un JWT

Un JWT consta de tres partes separadas por puntos (`.`):

```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJ1c2VyQGV4YW1wbGUuY29tIiwicm9sZSI6InVzZXIiLCJleHAiOjE3MDA1MDAwMDB9.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c

[Header].[Payload].[Signature]
```

**Header (Encabezado):**
```json
{
  "alg": "HS256",
  "typ": "JWT"
}
```

**Payload (Datos):**
```json
{
  "sub": "user@example.com",
  "role": "user",
  "user_id": 123,
  "exp": 1700500000
}
```

**Signature (Firma):**
```
HMACSHA256(
  base64UrlEncode(header) + "." + base64UrlEncode(payload),
  SECRET_KEY
)
```



### 4.4 Implementación en cada Servicio

#### Python (FastAPI) - Generación

```python
from jose import jwt
from datetime import datetime, timedelta

def create_access_token(data: dict):
    to_encode = data.copy()
    expire = datetime.utcnow() + timedelta(minutes=60)
    to_encode.update({"exp": expire})
    
    encoded_jwt = jwt.encode(
        to_encode, 
        SECRET_KEY, 
        algorithm="HS256"
    )
    return encoded_jwt
```

#### PHP (Laravel) - Validación

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$token = $request->bearerToken();
$decoded = JWT::decode($token, new Key($secret, 'HS256'));
$role = $decoded->role;
```

#### Rust (Axum) - Validación

```rust
use jsonwebtoken::{decode, DecodingKey, Validation, Algorithm};

let token_data = decode::<Claims>(
    token,
    &DecodingKey::from_secret(jwt_secret.as_bytes()),
    &Validation::new(Algorithm::HS256)
)?;
```

#### JavaScript (Vue.js) - Decodificación

```javascript
import { jwtDecode } from 'jwt-decode';

const decoded = jwtDecode(token);
console.log(decoded.role);  // "admin" o "user"
```

### 4.5 Ventajas de JWT en Microservicios

1. **Stateless:** No requiere sesiones en servidor
2. **Escalable:** Cada servicio valida independientemente
3. **Descentralizado:** No hay punto único de fallo
4. **Información embebida:** El token contiene el rol y user_id
5. **Interoperable:** Funciona entre diferentes lenguajes

---

## 5. Base de Datos PostgreSQL

### 5.1 Esquema de Base de Datos

```mermaid
erDiagram
    USERS ||--o{ CARTS : "tiene"
    CARTS ||--|{ CART_ITEMS : "contiene"
    PRODUCTS ||--o{ CART_ITEMS : "referenciado en"
    PRODUCTS ||--|| INVENTORY : "tiene stock en"
    
    USERS {
        int id PK
        varchar username
        varchar name
        varchar email UK
        varchar hashed_password
        varchar verification_code
        boolean is_verified
        varchar role
        timestamp created_at
    }
    
    PRODUCTS {
        bigint id PK
        varchar name
        text description
        decimal price
        int stock
        varchar image_url
        jsonb features
        timestamp created_at
        timestamp updated_at
    }
    
    CARTS {
        bigint id PK
        int user_id FK
        varchar status
        timestamp created_at
        timestamp updated_at
    }
    
    CART_ITEMS {
        bigint id PK
        bigint cart_id FK
        bigint product_id FK
        int quantity
        timestamp created_at
        timestamp updated_at
    }
    
    INVENTORY {
        int product_id PK
        int stock
    }
```

### 5.2 Uso de SQL y NoSQL (JSONB)

PostgreSQL permite combinar lo mejor de ambos mundos:

**SQL Relacional:**
```sql
-- Consulta con JOIN
SELECT p.name, c.quantity, p.price * c.quantity as total
FROM cart_items c
JOIN products p ON c.product_id = p.id
WHERE c.cart_id = 1;
```

**NoSQL con JSONB:**
```sql
-- Almacenar datos no estructurados
INSERT INTO products (name, price, features) VALUES (
  'Laptop Gamer',
  1500.00,
  '{"brand": "ASUS", "ram": "16GB", "gpu": "RTX 3060"}'::jsonb
);

-- Consultar dentro del JSON
SELECT name, features->>'brand' as marca
FROM products
WHERE features->>'gpu' = 'RTX 3060';
```

**Ventajas:**
- Flexibilidad para datos variables (especificaciones de productos)
- No requiere migraciones para agregar atributos
- Indexable y consultable como JSON nativo

---

## 6. Orquestación con Docker

### 6.2 Configuración de Servicios

Cada servicio tiene:
- **Dockerfile:** Define cómo construir la imagen
- **Variables de entorno:** Configuración sensible
- **Volúmenes:** Para desarrollo con hot-reload
- **Dependencias:** `depends_on` para orden de inicio

---

## 7. Instrucciones de Instalación y Ejecución

### 7.1 Prerrequisitos

- **Docker Desktop** instalado y corriendo
- **Git** para clonar el repositorio
- **Puertos disponibles:** 8000, 8001, 8002, 5173, 5438

### 7.2 Instalación Paso a Paso

#### Paso 1: Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/sistema-distribuido-microservicios.git
cd sistema-distribuido-microservicios
```

#### Paso 2: Configurar variables de entorno

**Auth Service:**
```bash
cd backend/auth-service
cp .env.example .env
# Editar .env con tus credenciales de email
```

**Products Service:**
```bash
cd backend/products-service
cp .env.example .env
# Configurar DATABASE_URL y JWT_SECRET
```

#### Paso 3: Levantar los servicios

```bash
# Desde la raíz del proyecto
docker-compose up -d --build
```

Este comando:
- Construye las imágenes de cada servicio
- Crea la red de Docker
- Levanta los 5 contenedores
- Inicia PostgreSQL y crea las tablas automáticamente

#### Paso 4: Verificar que todo esté corriendo

```bash
docker-compose ps
```

Deberías ver:
```
NAME                  STATUS
auth_service          Up
products_service      Up
inventory_service     Up
frontend_client       Up
postgres_main         Up
```

#### Paso 5: Acceder a la aplicación

Abre tu navegador en: **http://localhost:5173**

### 7.3 Comandos Útiles

```bash
# Ver logs de un servicio específico
docker-compose logs -f auth-service

# Reiniciar un servicio
docker-compose restart products-service

# Detener todo
docker-compose down

# Detener y eliminar volúmenes (reset completo)
docker-compose down -v

# Ejecutar comando dentro de un contenedor
docker exec -it postgres_main psql -U user_db -d microservices_db
```

### 7.4 Acceso a la Base de Datos

**Con DBeaver o cualquier cliente PostgreSQL:**

- **Host:** localhost
- **Puerto:** 5438
- **Database:** microservices_db
- **Usuario:** user_db
- **Contraseña:** admin123

---

## 8. Pruebas y Validación

### 8.1 Flujo de Prueba Completo

1. **Registro de usuario:**
   - Ir a http://localhost:5173/register
   - Crear cuenta con email válido
   - Copiar código de verificación de los logs: `docker-compose logs auth-service`
   - Verificar cuenta

2. **Login:**
   - Iniciar sesión con las credenciales
   - Verificar que se guarda el token en localStorage (DevTools → Application)

3. **CRUD de Productos (como Admin):**
   - Crear usuario con "admin" en el username
   - Crear un producto con imagen
   - Editar el producto
   - Eliminar el producto

4. **Gestión de Inventario:**
   - Como admin, actualizar el stock desde Rust
   - Verificar que el cambio se refleja inmediatamente

5. **Carrito y Compra (como User):**
   - Crear usuario normal
   - Agregar productos al carrito
   - Ver carrito
   - Procesar pago
   - Verificar que el stock se reduce

### 8.2 Endpoints de Prueba con curl

**Registro:**
```bash
curl -X POST http://localhost:8000/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

**Crear Producto (requiere token):**
```bash
curl -X POST http://localhost:8001/api/products \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -F "name=Laptop" \
  -F "price=1500" \
  -F "stock=10"
```

---

## 9. Desafíos Técnicos y Soluciones

### 9.1 Problema: CORS entre servicios

**Desafío:** El frontend no podía hacer peticiones a los backends.

**Solución:** Configurar CORS en cada microservicio:
- FastAPI: `CORSMiddleware`
- Laravel: Headers en respuestas
- Rust: `tower-http::cors::CorsLayer`

### 9.2 Problema: Validación de JWT en diferentes lenguajes

**Desafío:** Cada lenguaje tiene su propia librería JWT.

**Solución:** 
- Usar el mismo algoritmo (HS256)
- Compartir el mismo SECRET_KEY
- Validar la estructura del payload

### 9.3 Problema: Sincronización de stock

**Desafío:** Mantener consistencia entre Products (Laravel) e Inventory (Rust).

**Solución:**
- Inventory es la fuente de verdad
- Products actualiza Inventory en cada compra
- Frontend consulta ambos servicios

---



## Referencias

- [FastAPI Documentation](https://fastapi.tiangolo.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [Axum Documentation](https://docs.rs/axum/)
- [Vue.js 3 Documentation](https://vuejs.org/)
- [JWT.io](https://jwt.io/)
- [PostgreSQL JSONB](https://www.postgresql.org/docs/current/datatype-json.html)
- [Docker Compose](https://docs.docker.com/compose/)
