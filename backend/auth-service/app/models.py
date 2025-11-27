from sqlalchemy import Column, Integer, String, Boolean, Enum
from .database import Base
import enum

class Role(str, enum.Enum):
    admin = "admin"
    user = "user"

class User(Base):
    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    username = Column(String, unique=True, index=True, nullable=False)
    name = Column(String, nullable=False)
    email = Column(String, unique=True, index=True, nullable=False)
    hashed_password = Column(String, nullable=False)
    
    # Roles
    role = Column(String, default="user") # "admin" o "user"
    
    # Verificación de correo
    is_verified = Column(Boolean, default=False)
    verification_code = Column(String, nullable=True)