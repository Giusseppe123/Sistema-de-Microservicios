<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id', 
        'product_id', 
        'quantity'
    ];

    // Relación: Este item corresponde a un Producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación: Este item pertenece a un Carrito
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}