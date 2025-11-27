<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            
            // Relación con el carrito: Si se borra el carrito, se borran los items (cascade)
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            
            // Relación con el producto: Para saber qué producto es
            $table->foreignId('product_id')->constrained();
            
            // Cuántos productos de este tipo lleva
            $table->integer('quantity');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};