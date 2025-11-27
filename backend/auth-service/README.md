# 🐍 Microservicio de Autenticación (Auth Service)

Desarrollado en **Python** utilizando el framework **FastAPI**. Este servicio es la autoridad central de identidad del sistema.

## ⚙️ Tecnologías

* **Framework:** FastAPI
* **ORM:** SQLAlchemy
* **Seguridad:** Passlib (Bcrypt), Python-Jose (JWT)
* **Email:** FastAPI-Mail
* **Base de Datos:** PostgreSQL 15

## 📋 Funcionalidades

1. **Registro de Usuarios:** Creación de cuentas con validación de email
2. **Verificación por Código:** Sistema de códigos de 6 dígitos enviados por email
3. **Autenticación:** Login con generación de tokens JWT
4. **Sistema de Roles:** Asignación automática de roles (Admin/User)
5. **Seguridad:** Hashing de contraseñas con Bcrypt

## 🔧 Configuración (.env)

Crear un archivo `.env` en esta carpeta con:

```ini
DATABASE_URL=postgresql://user_db:admin123@db:5432/microservices_db
SECRET_KEY=una_clave_muy_secreta_y_larga_para_jwt
ALGORITHM=HS256
MAIL_USERNAME=tu_correo@gmail.com
MAIL_PASSWORD=tu_clave_aplicacion
MAIL_FROM=tu_correo@gmail.com
MAIL_PORT=587
MAIL_SERVER=smtp.gmail.com
```

## 🔌 API Endpoints (Puerto 8000)

| Método | Ruta | Descripción | Auth Requerida |
| :--- | :--- | :--- | :--- |
| `POST` | `/register` | Crea un nuevo usuario y envía código | No |
| `POST` | `/verify` | Valida el código de correo | No |
| `POST` | `/login` | Valida credenciales y retorna JWT | No |

### Ejemplos de Uso

**Registro:**
```bash
curl -X POST http://localhost:8000/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "usuario1",
    "email": "usuario@example.com",
    "password": "Password123!",
    "role": "user"
  }'
```

**Verificación:**
```bash
curl -X POST http://localhost:8000/verify \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@example.com",
    "code": "123456"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8000/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "usuario1",
    "password": "Password123!"
  }'
```

## 📁 Estructura del Proyecto

```
auth-service/
├── app/
│   ├── main.py           # Punto de entrada y rutas
│   ├── database.py       # Configuración de BD
│   ├── models.py         # Modelos SQLAlchemy
│   └── schemas.py        # Esquemas Pydantic
├── Dockerfile            # Configuración del contenedor
├── requirements.txt      # Dependencias Python
└── .env                  # Variables de entorno
```

## 🚀 Comandos Útiles

### Ver logs del servicio
```bash
docker logs auth_service -f
```

### Acceder al contenedor
```bash
docker exec -it auth_service bash
```

### Reiniciar el servicio
```bash
docker-compose restart auth-service
```

## 🔐 Seguridad

- **Hashing de Contraseñas:** Bcrypt con salt automático
- **Tokens JWT:** Firmados con HS256
- **Validación de Email:** Códigos de 6 dígitos con expiración
- **CORS:** Configurado para permitir requests del frontend

## 📝 Notas Importantes

- El primer usuario registrado con rol "admin" tendrá privilegios de administrador
- Los códigos de verificación expiran después de cierto tiempo
- Los tokens JWT contienen el `user_id`, `username` y `role` del usuario
- La contraseña debe cumplir con requisitos mínimos de seguridad