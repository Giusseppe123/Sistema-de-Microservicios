<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // ID único del producto
            
            $table->string('name'); // Nombre
            $table->text('description')->nullable(); // Descripción
            $table->decimal('price', 10, 2); // Precio con 2 decimales
            $table->integer('stock'); // Cantidad disponible
            
            // AQUÍ CUMPLIMOS TU REQUISITO DE IMAGENES
            $table->string('image_url')->nullable(); 
            
            // AQUÍ CUMPLIMOS TU REQUISITO DE NoSQL (JSON en PostgreSQL)
            // Guardaremos características variables (color, talla, peso)
            $table->jsonb('features')->nullable(); 
            
            $table->timestamps(); // Crea columnas created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};