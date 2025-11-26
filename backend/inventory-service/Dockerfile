FROM rust:latest

# Instalamos la herramienta de recarga automática
RUN cargo install cargo-watch

WORKDIR /app

# Copiamos todo (cuando existan archivos)
COPY . .

# Exponemos puerto
EXPOSE 8002

# Comando de arranque
CMD ["cargo", "watch", "-x", "run"]