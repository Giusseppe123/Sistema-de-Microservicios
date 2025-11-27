<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem; // Importante: Necesario para limpiar el carrito al borrar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // ------------------------------------------------
    // 1. READ (Leer todos) - Público
    // ------------------------------------------------
    public function index()
    {
        return response()->json(Product::all());
    }

    // ------------------------------------------------
    // 2. READ ONE (Leer uno solo por ID) - Público
    // ------------------------------------------------
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        return response()->json($product);
    }

    // ------------------------------------------------
    // 3. CREATE (Crear) - Solo Admin
    // ------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = url('storage/' . $path);
        }

        // Decodificar features si viene como string (desde Postman form-data)
        $features = $request->features;
        if (is_string($features)) {
            $features = json_decode($features, true);
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_url' => $imageUrl,
            'features' => $features
        ]);

        return response()->json($product, 201);
    }

    // ------------------------------------------------
    // 4. UPDATE (Actualizar) - Solo Admin
    // ------------------------------------------------
    public function update(Request $request, $id)
    {
        // Buscar producto
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        // Validar (todo es opcional 'nullable' porque quizás solo quieres cambiar el precio)
        $request->validate([
            'name' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Manejo de Imagen Nueva
        if ($request->hasFile('image')) {
            // 1. Borrar imagen vieja del disco si existe para no llenar el servidor
            if ($product->image_url) {
                // Convertimos la URL completa a ruta relativa para borrarla
                $oldPath = str_replace(url('storage/'), '', $product->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            // 2. Guardar imagen nueva
            $path = $request->file('image')->store('products', 'public');
            $product->image_url = url('storage/' . $path);
        }

        // Actualizar campos simples si vienen en la petición
        if ($request->has('name')) $product->name = $request->name;
        if ($request->has('description')) $product->description = $request->description;
        if ($request->has('price')) $product->price = $request->price;
        if ($request->has('stock')) $product->stock = $request->stock;
        
        // Actualizar JSON
        if ($request->has('features')) {
            $features = $request->features;
            if (is_string($features)) {
                $features = json_decode($features, true);
            }
            $product->features = $features;
        }

        $product->save();

        return response()->json(['message' => 'Producto actualizado', 'product' => $product]);
    }

    // ------------------------------------------------
    // 5. DELETE (Borrar) - Solo Admin
    // ------------------------------------------------
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        // --- CORRECCIÓN ERROR 500 ---
        // Primero sacamos el producto de los carritos de compra de los usuarios
        CartItem::where('product_id', $id)->delete();

        // Borrar imagen asociada del disco
        if ($product->image_url) {
            $oldPath = str_replace(url('storage/'), '', $product->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        // Ahora sí borramos el producto
        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}