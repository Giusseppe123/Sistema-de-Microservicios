# 🦀 Microservicio de Inventario (Inventory Service)

Este servicio es el componente de alto rendimiento del sistema. Desarrollado en **Rust** utilizando el framework **Axum**, se encarga de la gestión atómica y segura del stock de productos en tiempo real.

## 🛠️ Stack Tecnológico

* **Lenguaje:** Rust (Edition 2021)
* **Framework Web:** Axum 0.7
* **Runtime Asíncrono:** Tokio
* **Base de Datos:** SQLx (Driver asíncrono para PostgreSQL)
* **Seguridad:** Jsonwebtoken (Validación de JWT)
* **Serialización:** Serde & Serde JSON

## 📋 Funcionalidades Clave

1.  **Gestión de Stock:** Control preciso de la cantidad disponible por producto.
2.  **Operaciones Atómicas:** Uso de consultas SQL optimizadas (`Upsert`) para evitar condiciones de carrera al actualizar el inventario.
3.  **Alto Rendimiento:** Compilado a código binario nativo, ofreciendo latencias mínimas y bajo consumo de memoria.
4.  **Seguridad:** Middleware personalizado que intercepta y valida la firma del Token JWT emitido por el servicio de Auth antes de permitir modificaciones.

## 🔧 Configuración de Entorno

El servicio lee las variables de entorno inyectadas por Docker Compose:

* `DATABASE_URL`: Cadena de conexión a PostgreSQL (`postgres://user:pass@host:port/db`).
* `JWT_SECRET`: Clave secreta compartida para validar la autenticidad de los tokens.

## 🔌 API Endpoints (Puerto 8002)

| Método | Ruta | Descripción | Auth Requerida | Rol |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/inventory/:id` | Consultar stock actual de un producto | No | Público |
| `POST` | `/api/inventory` | Actualizar/Crear stock (Upsert) | Sí | Admin |

### Ejemplo de Payload (POST)
```json
{
  "product_id": 1,
  "stock": 500
}
```

### Ejemplos de Uso

**Consultar Stock:**
```bash
curl http://localhost:8002/api/inventory/1
```

**Actualizar Stock (requiere JWT de Admin):**
```bash
curl -X POST http://localhost:8002/api/inventory \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <tu_token_jwt>" \
  -d '{
    "product_id": 1,
    "stock": 100
  }'
```

## 📁 Estructura del Proyecto

```
inventory-service/
├── src/
│   ├── main.rs           # Punto de entrada y configuración de rutas
│   ├── handlers.rs       # Handlers de las rutas (opcional)
│   └── middleware.rs     # Middleware JWT (opcional)
├── Cargo.toml            # Dependencias de Rust
└── Dockerfile            # Configuración del contenedor
```

## 🚀 Comandos Útiles

### Ver logs del servicio
```bash
docker logs inventory_service -f
```

### Acceder al contenedor
```bash
docker exec -it inventory_service bash
```

### Reiniciar el servicio
```bash
docker-compose restart inventory-service
```

### Compilar localmente (requiere Rust instalado)
```bash
cargo build --release
```

## ⚡ Ventajas de Rust

- **Performance:** Latencias sub-milisegundo en operaciones de inventario
- **Seguridad de Memoria:** Sin garbage collector, sin race conditions
- **Concurrencia:** Manejo seguro de múltiples requests simultáneos
- **Eficiencia:** Bajo consumo de memoria y CPU

## 🔐 Seguridad

- **Middleware JWT:** Valida tokens antes de permitir modificaciones
- **Operaciones Atómicas:** Upsert SQL para evitar inconsistencias
- **Type Safety:** El sistema de tipos de Rust previene errores en tiempo de compilación

## 📝 Notas Importantes

- El servicio utiliza `UPSERT` (INSERT ... ON CONFLICT UPDATE) para operaciones atómicas
- Solo usuarios con rol "admin" pueden modificar el stock
- Las consultas de stock son públicas para permitir que el frontend muestre disponibilidad
- El servicio se comunica con Products Service durante el checkout