# 🛒 Sistema de E-Commerce Distribuido con Microservicios

[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker)](https://www.docker.com/)
[![FastAPI](https://img.shields.io/badge/FastAPI-0.109.0-009688?logo=fastapi)](https://fastapi.tiangolo.com/)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel)](https://laravel.com/)
[![Rust](https://img.shields.io/badge/Rust-Axum-000000?logo=rust)](https://www.rust-lang.org/)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?logo=vue.js)](https://vuejs.org/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15-336791?logo=postgresql)](https://www.postgresql.org/)

> **Proyecto Académico:** Sistema distribuido de microservicios políglota implementando arquitectura moderna de backend con autenticación JWT, base de datos híbrida SQL/NoSQL y orquestación con Docker.

---

## 📋 Descripción General

Este proyecto implementa una **arquitectura de microservicios** completa para un sistema de e-commerce, donde cada servicio backend está desarrollado con un **lenguaje y framework diferente**, demostrando la interoperabilidad entre tecnologías heterogéneas.

### 🎯 Características Principales

✅ **3 Microservicios Independientes** (Python, PHP, Rust)  
✅ **Autenticación JWT** distribuida y stateless  
✅ **CRUD Completo** de productos con imágenes  
✅ **Sistema de Carrito** de compras  
✅ **Gestión de Inventario** de alto rendimiento  
✅ **Base de Datos Híbrida** SQL + NoSQL (JSONB)  
✅ **Frontend Moderno** con Vue.js 3  
✅ **Orquestación Docker** con Compose  

---

## 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    🌐 Frontend (Vue.js)                     │
│                    Puerto: 5173                             │
└────────────┬──────────────┬──────────────┬─────────────────┘
             │              │              │
             ▼              ▼              ▼
┌────────────────┐ ┌────────────────┐ ┌────────────────┐
│  🔐 Auth       │ │  📦 Products   │ │  📊 Inventory  │
│  FastAPI       │ │  Laravel       │ │  Rust/Axum     │
│  Puerto: 8000  │ │  Puerto: 8001  │ │  Puerto: 8002  │
└────────┬───────┘ └────────┬───────┘ └────────┬───────┘
         │                  │                  │
         └──────────────────┼──────────────────┘
                            ▼
                   ┌─────────────────┐
                   │  🗄️ PostgreSQL  │
                   │  Puerto: 5438   │
                   └─────────────────┘
```

### Tecnologías por Servicio

| Servicio | Lenguaje | Framework | Responsabilidad |
|----------|----------|-----------|-----------------|
| **Auth** | Python 3.9 | FastAPI 0.109 | Registro, Login, JWT, Verificación Email |
| **Products** | PHP 8.2 | Laravel 11.x | CRUD Productos, Carrito, Imágenes |
| **Inventory** | Rust | Axum | Gestión Stock, Consultas Rápidas |
| **Frontend** | JavaScript | Vue.js 3 + Tailwind | Interfaz Usuario, Orquestación |
| **Database** | - | PostgreSQL 15 | Persistencia SQL + JSONB |

---

## 🚀 Inicio Rápido

### Prerrequisitos

- ✅ **Docker Desktop** instalado y corriendo ([Descargar](https://www.docker.com/products/docker-desktop))
- ✅ **Git** para clonar el repositorio
- ✅ **Puertos disponibles:** 8000, 8001, 8002, 5173, 5438

### Instalación en 3 Pasos

#### 1️⃣ Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/sistema-distribuido-microservicios.git
cd sistema-distribuido-microservicios
```

#### 2️⃣ Configurar Variables de Entorno

**Auth Service** (opcional - para envío de emails):
```bash
cd backend/auth-service
# El .env ya está configurado, pero puedes personalizar las credenciales de email
```

**Products Service** (crear archivo .env):
```bash
cd backend/products-service
cp .env.example .env
# Editar .env y configurar:
# - DB_CONNECTION=pgsql
# - DB_HOST=db
# - DB_PORT=5432
# - DB_DATABASE=microservices_db
# - DB_USERNAME=user_db
# - DB_PASSWORD=admin123
# - JWT_SECRET=una_clave_muy_secreta_y_larga_para_jwt
```

#### 3️⃣ Levantar Todo el Sistema

```bash
# Desde la raíz del proyecto
docker-compose up -d --build
```

Este comando:
- 🔨 Construye las imágenes de cada servicio
- 🚀 Levanta los 5 contenedores
- 🗄️ Crea la base de datos y tablas automáticamente
- ⚡ Habilita hot-reload para desarrollo

### Verificar que Todo Funciona

```bash
docker-compose ps
```

Deberías ver todos los servicios en estado `Up`:
```
NAME                  STATUS
auth_service          Up
products_service      Up
inventory_service     Up
frontend_client       Up
postgres_main         Up
```

### Acceder a la Aplicación

🌐 **Frontend:** [http://localhost:5173](http://localhost:5173)

---

## 📡 API Endpoints

### 🔐 Auth Service (Puerto 8000)

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| POST | `/register` | Registrar nuevo usuario | No |
| POST | `/verify` | Verificar cuenta con código | No |
| POST | `/login` | Iniciar sesión y obtener JWT | No |

**Ejemplo de Registro:**
```bash
curl -X POST http://localhost:8000/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin_user",
    "name": "Admin Test",
    "email": "admin@test.com",
    "password": "password123"
  }'
```

**Ejemplo de Login:**
```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@test.com",
    "password": "password123"
  }'
```

---

### 📦 Products Service (Puerto 8001)

| Método | Endpoint | Descripción | Auth | Rol |
|--------|----------|-------------|------|-----|
| GET | `/api/products` | Listar productos | No | - |
| GET | `/api/products/{id}` | Ver un producto | No | - |
| POST | `/api/products` | Crear producto | Sí | Admin |
| POST | `/api/products/{id}` | Actualizar producto | Sí | Admin |
| DELETE | `/api/products/{id}` | Eliminar producto | Sí | Admin |
| POST | `/api/cart` | Agregar al carrito | Sí | User |
| GET | `/api/cart` | Ver mi carrito | Sí | User |
| POST | `/api/cart/checkout` | Procesar pago | Sí | User |
| DELETE | `/api/cart` | Vaciar carrito | Sí | User |

**Ejemplo de Crear Producto:**
```bash
curl -X POST http://localhost:8001/api/products \
  -H "Authorization: Bearer TU_TOKEN_JWT" \
  -F "name=Laptop Gamer" \
  -F "price=1500" \
  -F "stock=10" \
  -F "description=Laptop de alto rendimiento" \
  -F "image=@/ruta/a/imagen.jpg"
```

---

### 📊 Inventory Service (Puerto 8002)

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/inventory/{id}` | Consultar stock | Sí |
| POST | `/api/inventory` | Actualizar stock | Sí |

**Ejemplo de Actualizar Stock:**
```bash
curl -X POST http://localhost:8002/api/inventory \
  -H "Authorization: Bearer TU_TOKEN_JWT" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "stock": 50
  }'
```

---

## 🔐 Autenticación JWT

### Flujo de Autenticación

1. **Usuario se registra** → Auth Service crea usuario y envía código de verificación
2. **Usuario verifica cuenta** → Activa la cuenta con el código
3. **Usuario hace login** → Auth Service genera y firma un JWT
4. **Frontend guarda el token** → LocalStorage + Pinia store
5. **Peticiones protegidas** → Frontend envía `Authorization: Bearer {token}`
6. **Servicios validan** → Laravel y Rust verifican la firma del token

### Estructura del Token JWT

```json
{
  "sub": "user@example.com",
  "role": "admin",
  "user_id": 123,
  "exp": 1700500000
}
```

### Sistema de Roles

- **Admin:** Puede crear, editar y eliminar productos. Gestionar inventario.
- **User:** Puede agregar productos al carrito y comprar.

> 💡 **Tip:** Para crear un admin, incluye "admin" en el username al registrarte (ej: "admin_juan")

---

## 🗄️ Base de Datos

### Conexión a PostgreSQL

**Con DBeaver, pgAdmin o cualquier cliente:**

```
Host:     localhost
Puerto:   5438
Database: microservices_db
Usuario:  user_db
Password: admin123
```

### Tablas Principales

| Tabla | Descripción | Servicio |
|-------|-------------|----------|
| `users` | Usuarios del sistema | Auth |
| `products` | Catálogo de productos (con JSONB) | Products |
| `carts` | Carritos de compra | Products |
| `cart_items` | Items en carritos | Products |
| `inventory` | Stock de productos | Inventory |

### Uso de JSONB (NoSQL)

La tabla `products` tiene un campo `features` de tipo JSONB para almacenar atributos variables:

```sql
-- Ejemplo de producto con features
{
  "brand": "ASUS",
  "ram": "16GB",
  "gpu": "RTX 3060",
  "color": "Negro"
}
```

---

## 🛠️ Comandos Útiles

### Docker Compose

```bash
# Ver logs de todos los servicios
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f auth-service

# Reiniciar un servicio
docker-compose restart products-service

# Detener todo
docker-compose down

# Detener y eliminar volúmenes (reset completo)
docker-compose down -v

# Reconstruir un servicio específico
docker-compose up -d --build auth-service
```

### Acceso a Contenedores

```bash
# Entrar a la base de datos
docker exec -it postgres_main psql -U user_db -d microservices_db

# Ver tablas
\dt

# Consultar usuarios
SELECT * FROM users;

# Entrar al contenedor de Laravel
docker exec -it products_service bash

# Ejecutar migraciones de Laravel manualmente
php artisan migrate
```

---

## 🧪 Pruebas del Sistema

### Flujo de Prueba Completo

1. **Registro y Verificación**
   - Ir a http://localhost:5173/register
   - Crear cuenta (usa "admin" en username para ser admin)
   - Ver logs: `docker-compose logs auth-service | grep "CODIGO"`
   - Copiar código y verificar cuenta

2. **Login**
   - Iniciar sesión con las credenciales
   - Abrir DevTools → Application → LocalStorage
   - Verificar que existe el token

3. **CRUD de Productos (Admin)**
   - Crear un producto con imagen
   - Editar el producto
   - Actualizar stock desde Rust
   - Eliminar el producto

4. **Compra (User)**
   - Crear usuario normal (sin "admin" en username)
   - Agregar productos al carrito
   - Ver carrito
   - Procesar pago
   - Verificar que el stock se redujo

---

## 📁 Estructura del Proyecto

```
sistema-distribuido-microservicios/
├── backend/
│   ├── auth-service/           # Python + FastAPI
│   │   ├── app/
│   │   │   ├── main.py         # Endpoints
│   │   │   ├── auth.py         # JWT
│   │   │   ├── models.py       # SQLAlchemy
│   │   │   └── schemas.py      # Pydantic
│   │   ├── Dockerfile
│   │   ├── requirements.txt
│   │   └── .env
│   │
│   ├── products-service/       # PHP + Laravel
│   │   ├── app/
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── ProductController.php
│   │   │   │   │   └── CartController.php
│   │   │   │   └── Middleware/
│   │   │   │       └── JwtMiddleware.php
│   │   │   └── Models/
│   │   ├── Dockerfile
│   │   └── .env
│   │
│   └── inventory-service/      # Rust + Axum
│       ├── src/
│       │   └── main.rs
│       ├── Cargo.toml
│       └── Dockerfile
│
├── frontend/                   # Vue.js 3
│   ├── src/
│   │   ├── views/
│   │   │   ├── Login.vue
│   │   │   ├── Register.vue
│   │   │   ├── Home.vue
│   │   │   └── Cart.vue
│   │   ├── stores/
│   │   │   └── auth.js
│   │   └── router/
│   │       └── index.js
│   ├── Dockerfile
│   └── package.json
│
├── docker-compose.yml
├── README.md
└── DOCUMENTO_TECNICO.md        # Documentación completa
```

---

## 🐛 Troubleshooting

### Problema: Servicios no inician

```bash
# Ver logs para identificar el error
docker-compose logs

# Verificar que los puertos no estén ocupados
netstat -ano | findstr "8000"  # Windows
lsof -i :8000                  # Mac/Linux

# Reiniciar Docker Desktop
```

### Problema: No se conecta a la base de datos

```bash
# Verificar que PostgreSQL esté corriendo
docker-compose ps postgres_main

# Ver logs de la base de datos
docker-compose logs db

# Reiniciar solo la base de datos
docker-compose restart db
```

### Problema: CORS errors en el navegador

- Verificar que el frontend esté en `http://localhost:5173`
- Limpiar caché del navegador
- Verificar que los servicios backend tengan CORS configurado

### Problema: Token inválido

```bash
# Verificar que JWT_SECRET sea el mismo en todos los servicios
# Auth: .env → SECRET_KEY
# Products: .env → JWT_SECRET
# Inventory: docker-compose.yml → JWT_SECRET
```

---

## � Documentación Adicional

- 📖 **[DOCUMENTO_TECNICO.md](./DOCUMENTO_TECNICO.md)** - Arquitectura detallada, diagramas y explicación de JWT
- 📖 **[backend/auth-service/README.md](./backend/auth-service/README.md)** - Documentación del servicio de autenticación
- 📖 **[backend/products-service/README.md](./backend/products-service/README.md)** - Documentación del servicio de productos
- 📖 **[backend/inventory-service/README.md](./backend/inventory-service/README.md)** - Documentación del servicio de inventario

---





## 👥 Autores

**Equipo de Desarrollo:**
- [Luis Chirivella] - Backend FastAPI
- [Alvaro Lugo] - Backend Laravel
- [Jose Mendez] - Backend Rust
- [Giusseppe Marinelly] - Frontend Vue.js Integración y Documentación

---

