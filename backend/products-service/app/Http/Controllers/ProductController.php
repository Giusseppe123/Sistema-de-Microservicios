<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CartItem; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    public function index()
    {
        return response()->json(Product::all());
    }

    
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        return response()->json($product);
    }

 
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

    
    public function update(Request $request, $id)
    {
      
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

       
        $request->validate([
            'name' => 'nullable|string',
            'price' => 'nullable|numeric',
            'stock' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

       
        if ($request->hasFile('image')) {

            if ($product->image_url) {

                $oldPath = str_replace(url('storage/'), '', $product->image_url);
                Storage::disk('public')->delete($oldPath);
            }

          
            $path = $request->file('image')->store('products', 'public');
            $product->image_url = url('storage/' . $path);
        }

       
        if ($request->has('name')) $product->name = $request->name;
        if ($request->has('description')) $product->description = $request->description;
        if ($request->has('price')) $product->price = $request->price;
        if ($request->has('stock')) $product->stock = $request->stock;
        
    
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

   
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

      
        CartItem::where('product_id', $id)->delete();

       
        if ($product->image_url) {
            $oldPath = str_replace(url('storage/'), '', $product->image_url);
            Storage::disk('public')->delete($oldPath);
        }

       
        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}