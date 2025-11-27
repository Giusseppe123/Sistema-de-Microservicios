from fastapi import FastAPI, Depends, HTTPException, status
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session
from . import models, schemas, database, auth, email_utils

# Crear tablas al iniciar
models.Base.metadata.create_all(bind=database.engine)

app = FastAPI(title="Auth Service Microservice")

# Configuración de CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.post("/register", response_model=schemas.UserResponse)
async def register(user: schemas.UserCreate, db: Session = Depends(database.get_db)):
    # 1. Validar dominio de email
    valid_domains = ['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com', 'live.com', 'icloud.com', 'protonmail.com', 'zoho.com']
    email_domain = user.email.split('@')[1].lower() if '@' in user.email else None
    
    if not email_domain or email_domain not in valid_domains:
        raise HTTPException(
            status_code=400, 
            detail="Por favor usa un correo válido (@gmail.com, @hotmail.com, @outlook.com, etc.)"
        )
    
    # 2. Verificar si existe correo
    db_user = db.query(models.User).filter(models.User.email == user.email).first()
    if db_user:
        raise HTTPException(status_code=400, detail="El correo ya está registrado")
    
    # 3. Generar código y Hash
    code = email_utils.generate_verification_code()
    hashed_pwd = auth.get_password_hash(user.password)
    
    # 4. Asignar rol
    role = "admin" if "admin" in user.username else "user"

    # 5. Crear objeto Usuario
    new_user = models.User(
        username=user.username,
        name=user.name,
        email=user.email,
        hashed_password=hashed_pwd,
        verification_code=code,
        role=role,
        is_verified=False
    )

    # 6. Guardar en DB
    try:
        db.add(new_user)
        db.commit()
        db.refresh(new_user)
    except Exception as e:
        db.rollback()
        raise HTTPException(status_code=500, detail="Error al crear usuario")

    # 7. Enviar correo
    try:
        await email_utils.send_verification_email(user.email, code)
    except Exception as e:
        print(f"⚠️ Error enviando email: {e}")
        print(f"📧 CÓDIGO DE VERIFICACIÓN PARA {user.email}: {code}")

    return new_user

@app.post("/verify")
def verify_code(verification: schemas.UserVerify, db: Session = Depends(database.get_db)):
    user = db.query(models.User).filter(models.User.email == verification.email).first()
    if not user:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    
    if user.verification_code != verification.code:
        raise HTTPException(status_code=400, detail="Código incorrecto")
    
    user.is_verified = True
    user.verification_code = None
    db.commit()
    return {"message": "Cuenta verificada exitosamente"}

@app.post("/login", response_model=schemas.Token)
def login(user_credentials: schemas.UserLogin, db: Session = Depends(database.get_db)):
    user = db.query(models.User).filter(models.User.email == user_credentials.email).first()
    
    if not user:
        raise HTTPException(status_code=403, detail="Credenciales inválidas")
    
    if not auth.verify_password(user_credentials.password, user.hashed_password):
        raise HTTPException(status_code=403, detail="Credenciales inválidas")
        
    if not user.is_verified:
        raise HTTPException(status_code=401, detail="Cuenta no verificada")

    access_token = auth.create_access_token(
        data={"sub": user.email, "role": user.role, "user_id": user.id}
    )
    
    return {"access_token": access_token, "token_type": "bearer"}