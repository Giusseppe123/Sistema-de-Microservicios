<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Campos que permitimos llenar masivamente
    protected $fillable = [
        'name', 
        'description', 
        'price', 
        'stock', 
        'image_url', 
        'features'
    ];

    // TRUCO NoSQL: Esto convierte automáticamente el JSON de Postgres a un Array de PHP
    protected $casts = [
        'features' => 'array',
    ];
}