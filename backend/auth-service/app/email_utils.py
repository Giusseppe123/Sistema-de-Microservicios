from fastapi_mail import FastMail, MessageSchema, ConnectionConfig
from pydantic import EmailStr
import os
from dotenv import load_dotenv
import random

load_dotenv()

conf = ConnectionConfig(
    MAIL_USERNAME=os.getenv("MAIL_USERNAME"),
    MAIL_PASSWORD=os.getenv("MAIL_PASSWORD"),
    MAIL_FROM=os.getenv("MAIL_FROM"),
    MAIL_PORT=int(os.getenv("MAIL_PORT")),
    MAIL_SERVER=os.getenv("MAIL_SERVER"),
    MAIL_STARTTLS=True,
    MAIL_SSL_TLS=False,
    USE_CREDENTIALS=True
)

def generate_verification_code():
    # Genera código de 6 dígitos
    return str(random.randint(100000, 999999))

async def send_verification_email(email: EmailStr, code: str):
    message = MessageSchema(
        subject="Verifica tu cuenta - Sistema Distribuido",
        recipients=[email],
        body=f"Tu código de verificación es: {code}",
        subtype="html"
    )
    fm = FastMail(conf)
    await fm.send_message(message)