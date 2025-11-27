<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            
            // Guardaremos el ID del usuario que viene del Token JWT
            // No usamos 'constrained' porque el usuario está en OTRA base de datos (la de Python)
            $table->integer('user_id'); 
            
            // Estado del carrito: 'active' (comprando) o 'completed' (pagado)
            $table->string('status')->default('active');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};