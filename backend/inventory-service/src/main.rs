use axum::{
    extract::{Path, State, Request},
    http::{StatusCode, header, Method}, 
    middleware::{self, Next},
    response::{Json, Response},
    routing::{get, post},
    Router,
};
use serde::{Deserialize, Serialize};
use sqlx::{postgres::PgPoolOptions, Pool, Postgres};
use std::net::SocketAddr;
use std::env;
use jsonwebtoken::{decode, DecodingKey, Validation, Algorithm};
use tower_http::cors::{Any, CorsLayer}; 


#[derive(Deserialize)]
struct UpdateStockRequest { product_id: i32, stock: i32 }
#[derive(Serialize)]
struct InventoryItem { product_id: i32, stock: i32 }
#[derive(Clone)]
struct AppState { db: Pool<Postgres>, jwt_secret: String }
#[derive(Debug, Serialize, Deserialize)]
struct Claims { sub: String, role: String, exp: usize }

#[tokio::main]
async fn main() {
    let database_url = env::var("DATABASE_URL").expect("DATABASE_URL fail");
    let jwt_secret = env::var("JWT_SECRET").expect("JWT_SECRET fail");

    println!("Conectando a Postgres...");
    let pool = PgPoolOptions::new().max_connections(5).connect(&database_url).await.expect("Error DB");

    let state = AppState { db: pool, jwt_secret };

    let cors = CorsLayer::new()
        .allow_methods([Method::GET, Method::POST])
        .allow_origin(Any)
        .allow_headers([header::AUTHORIZATION, header::CONTENT_TYPE]);

    let app = Router::new()
        .route("/api/inventory/:id", get(get_stock))
        .route("/api/inventory", post(update_stock))
        .layer(middleware::from_fn_with_state(state.clone(), auth_middleware))
        .layer(cors)
        .with_state(state);

    let addr = SocketAddr::from(([0, 0, 0, 0], 8002));
    println!("Inventory Service corriendo en {}", addr);
    let listener = tokio::net::TcpListener::bind(addr).await.unwrap();
    axum::serve(listener, app).await.unwrap();
}


async fn auth_middleware(State(state): State<AppState>, req: Request, next: Next) -> Result<Response, StatusCode> {
    let auth_header = req.headers().get(header::AUTHORIZATION).and_then(|h| h.to_str().ok());
    let auth_header = if let Some(h) = auth_header { h } else { return Err(StatusCode::UNAUTHORIZED); };
    if !auth_header.starts_with("Bearer ") { return Err(StatusCode::UNAUTHORIZED); }
    let token = &auth_header[7..];
    
    let token_data = decode::<Claims>(token, &DecodingKey::from_secret(state.jwt_secret.as_bytes()), &Validation::new(Algorithm::HS256));
    
    match token_data {
        Ok(_) => Ok(next.run(req).await),
        Err(_) => Err(StatusCode::UNAUTHORIZED),
    }
}

async fn get_stock(State(state): State<AppState>, Path(product_id): Path<i32>) -> Result<Json<InventoryItem>, StatusCode> {
    let result = sqlx::query_as!(InventoryItem, "SELECT product_id, stock FROM inventory WHERE product_id = $1", product_id)
        .fetch_optional(&state.db).await;
    match result {
        Ok(Some(item)) => Ok(Json(item)),
        Ok(None) => Err(StatusCode::NOT_FOUND),
        Err(_) => Err(StatusCode::INTERNAL_SERVER_ERROR),
    }
}

async fn update_stock(State(state): State<AppState>, Json(payload): Json<UpdateStockRequest>) -> Result<Json<InventoryItem>, StatusCode> {
    if payload.stock < 0 {
        eprintln!("Error: Attempted to set negative stock: {}", payload.stock);
        return Err(StatusCode::BAD_REQUEST);
    }
    
    let result = sqlx::query_as!(InventoryItem, r#"
        INSERT INTO inventory (product_id, stock) VALUES ($1, $2)
        ON CONFLICT (product_id) DO UPDATE SET stock = EXCLUDED.stock
        RETURNING product_id, stock
        "#, payload.product_id, payload.stock).fetch_one(&state.db).await;
    match result { Ok(item) => Ok(Json(item)), Err(_) => Err(StatusCode::INTERNAL_SERVER_ERROR) }
}