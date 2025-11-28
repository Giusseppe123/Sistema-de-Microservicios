<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
   
    public function addToCart(Request $request)
    {
        
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        
        $userData = $request->user; 
        $userId = $userData['user_id']; 

        
        $cart = Cart::firstOrCreate(
            ['user_id' => $userId, 'status' => 'active']
        );

        
        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $request->product_id)
                            ->first();

        
                            if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        
        } else {
        
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json([
            'message' => 'Producto agregado al carrito exitosamente',
            'cart_id' => $cart->id
        ], 200);
    }

    
    public function viewCart(Request $request)
    
    {
    
        $userData = $request->user;
        $userId = $userData['user_id'];

    
        $cart = Cart::where('user_id', $userId)
                    ->where('status', 'active')
                    ->with('items.product') 
                    ->first();

        if (!$cart) {
            return response()->json(['message' => 'Tu carrito está vacío'], 200);
        }

        return response()->json($cart);
    }
    
    public function checkout(Request $request)
    {
        $userData = $request->user;
        $userId = $userData['user_id'];

    
        $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();

        if (!$cart) {
            return response()->json(['error' => 'No hay carrito activo'], 400);
        }

    
        $items = CartItem::where('cart_id', $cart->id)->get();

        if ($items->isEmpty()) {
            return response()->json(['error' => 'El carrito está vacío'], 400);
        }

    
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
    
                if ($product->stock >= $item->quantity) {
                    $product->decrement('stock', $item->quantity);
                } else {
                    return response()->json(['error' => "Stock insuficiente para {$product->name}"], 400);
                }
            }
        }

    
        CartItem::where('cart_id', $cart->id)->delete();

        return response()->json(['message' => 'Compra realizada con éxito. Stock descontado.']);
    }

    public function removeItem(Request $request, $itemId)
    {
        $userData = $request->user;
        $userId = $userData['user_id'];

    
        $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();

        if (!$cart) {
            return response()->json(['error' => 'Carrito no encontrado'], 404);
        }

    
        $item = CartItem::where('id', $itemId)->where('cart_id', $cart->id)->first();

        if (!$item) {
            return response()->json(['error' => 'Item no encontrado en tu carrito'], 404);
        }

    
        $item->delete();

        return response()->json(['message' => 'Producto eliminado del carrito']);
    }

    
    public function clearCart(Request $request)
    {
        $userData = $request->user;
        $userId = $userData['user_id'];

        $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();

        if ($cart) { 
            CartItem::where('cart_id', $cart->id)->delete();
        }

        return response()->json(['message' => 'Carrito vaciado correctamente']);
    }
}

