<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController; // Importamos el nuevo controlador

// ---------------------------------------------------------
// RUTAS PÚBLICAS (Cualquiera puede ver productos)
// ---------------------------------------------------------
Route::get('/products', [ProductController::class, 'index']);

// ---------------------------------------------------------
// RUTAS DE ADMIN (CRUD Completo)
// ---------------------------------------------------------
Route::middleware(['jwt.auth:admin'])->group(function () {
    
    // Crear (POST)
    Route::post('/products', [ProductController::class, 'store']);

    // Actualizar (PUT o POST con _method=PUT para soportar imagenes en Laravel)
    // Nota: Laravel tiene un bug conocido con PUT y form-data (imágenes). 
    // Se recomienda usar POST a la URL de update.
    Route::post('/products/{id}', [ProductController::class, 'update']); 
    
    // Borrar (DELETE)
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

});

// Ruta pública para ver un solo producto (opcional pero útil)
Route::get('/products/{id}', [ProductController::class, 'show']);

// ---------------------------------------------------------
// RUTAS DE USUARIO (Carrito de Compras)
// ---------------------------------------------------------
Route::middleware(['jwt.auth:user'])->group(function () {
    Route::post('/cart', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'viewCart']);
    Route::post('/cart/checkout', [CartController::class, 'checkout']);
    
    // --- NUEVAS RUTAS ---
    Route::delete('/cart/items/{itemId}', [CartController::class, 'removeItem']); // Borrar uno
    Route::delete('/cart', [CartController::class, 'clearCart']); // Vaciar todo
});