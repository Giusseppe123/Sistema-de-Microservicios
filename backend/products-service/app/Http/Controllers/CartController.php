<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
    // ---------------------------------------------------
    // AGREGAR AL CARRITO (Solo para Rol: User)
    // ---------------------------------------------------
    public function addToCart(Request $request)
    {
        // 1. Validar que envíen un producto existente y una cantidad válida
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // 2. Obtener el ID del usuario desde el Token JWT
        // El Middleware ya decodificó el token y lo guardó en $request->user
        $userData = $request->user; 
        $userId = $userData['user_id']; // Obtenemos el ID del usuario de Python

        // 3. Buscar si el usuario ya tiene un carrito "activo" (si no, se crea uno)
        $cart = Cart::firstOrCreate(
            ['user_id' => $userId, 'status' => 'active']
        );

        // 4. Verificar si el producto ya estaba en el carrito
        $cartItem = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $request->product_id)
                            ->first();

        if ($cartItem) {
            // Si ya existe, solo sumamos la cantidad nueva
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // Si no existe, creamos el item nuevo en el carrito
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

    // ---------------------------------------------------
    // VER MI CARRITO (Solo para Rol: User)
    // ---------------------------------------------------
    public function viewCart(Request $request)
    {
        // 1. Obtener usuario del token
        $userData = $request->user;
        $userId = $userData['user_id'];

        // 2. Buscar el carrito activo de este usuario
        // Usamos 'with' para traer automáticamente los datos de los productos y sus imagenes
        $cart = Cart::where('user_id', $userId)
                    ->where('status', 'active')
                    ->with('items.product') 
                    ->first();

        if (!$cart) {
            return response()->json(['message' => 'Tu carrito está vacío'], 200);
        }

        return response()->json($cart);
    }
    // ---------------------------------------------------
    // PROCESAR PAGO (Checkout)
    // ---------------------------------------------------
    public function checkout(Request $request)
    {
        $userData = $request->user;
        $userId = $userData['user_id'];

        // 1. Buscar el carrito activo
        $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();

        if (!$cart) {
            return response()->json(['error' => 'No hay carrito activo'], 400);
        }

        // 2. Obtener los items
        $items = CartItem::where('cart_id', $cart->id)->get();

        if ($items->isEmpty()) {
            return response()->json(['error' => 'El carrito está vacío'], 400);
        }

        // 3. Restar stock en Laravel y Vaciar Carrito
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                // Restamos el stock en la BD de productos
                if ($product->stock >= $item->quantity) {
                    $product->decrement('stock', $item->quantity);
                } else {
                    return response()->json(['error' => "Stock insuficiente para {$product->name}"], 400);
                }
            }
        }

        // 4. Eliminar los items (Vaciar carrito)
        CartItem::where('cart_id', $cart->id)->delete();

        return response()->json(['message' => 'Compra realizada con éxito. Stock descontado.']);
    }
    // ---------------------------------------------------
    // ELIMINAR UN ÍTEM ESPECÍFICO
    // ---------------------------------------------------
    public function removeItem(Request $request, $itemId)
    {
        $userData = $request->user;
        $userId = $userData['user_id'];

        // 1. Buscamos el carrito del usuario
        $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();

        if (!$cart) {
            return response()->json(['error' => 'Carrito no encontrado'], 404);
        }

        // 2. Buscamos el item y verificamos que pertenezca a este carrito (Seguridad)
        $item = CartItem::where('id', $itemId)->where('cart_id', $cart->id)->first();

        if (!$item) {
            return response()->json(['error' => 'Item no encontrado en tu carrito'], 404);
        }

        // 3. Eliminar
        $item->delete();

        return response()->json(['message' => 'Producto eliminado del carrito']);
    }

    // ---------------------------------------------------
    // VACIAR CARRITO COMPLETO
    // ---------------------------------------------------
    public function clearCart(Request $request)
    {
        $userData = $request->user;
        $userId = $userData['user_id'];

        $cart = Cart::where('user_id', $userId)->where('status', 'active')->first();

        if ($cart) {
            // Borrar todos los items de este carrito
            CartItem::where('cart_id', $cart->id)->delete();
        }

        return response()->json(['message' => 'Carrito vaciado correctamente']);
    }
}

