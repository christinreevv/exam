<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function add(Request $request, Product $product)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($product->quantity <= 0) {
            return response()->json(['message' => 'Нет в наличии'], 400);
        }

        $cart = $user->cart ?? $user->cart()->create();

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            if ($item->quantity >= $product->quantity) {
                return response()->json(['message' => 'Превышено количество на складе'], 400);
            }

            $item->quantity += 1;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        }

        return response()->json(['message' => 'Товар добавлен в корзину']);
    }


    public function show()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cart = $user->cart()->with('items.product')->first();

        if (!$cart) {
            return response()->json([
                'items' => [],
                'total' => 0,
            ]);
        }

        $total = $cart->items->reduce(function ($carry, $item) {
            return $carry + ($item->quantity * $item->product->price);
        }, 0);

        return response()->json([
            'items' => $cart->items,
            'total' => $total,
        ]);
    }

    public function updateQuantity(Request $request, CartItem $item)
    {
        $user = Auth::user();

        if ($item->cart->user_id !== $user->id) {
            return response()->json(['message' => 'Нет доступа'], 403);
        }

        $action = $request->input('action');

        if ($action === 'increase') {
            if ($item->quantity < $item->product->quantity) {
                $item->quantity += 1;
            } else {
                return response()->json(['message' => 'Превышено количество в наличии'], 400);
            }
        } elseif ($action === 'decrease') {
            if ($item->quantity > 1) {
                $item->quantity -= 1;
            } else {
                return $this->removeItem($item);
            }
        }

        $item->save();

        return response()->json(['message' => 'Количество обновлено']);
    }

    public function removeItem(CartItem $item)
    {
        $user = Auth::user();

        if ($item->cart->user_id !== $user->id) {
            return response()->json(['message' => 'Нет доступа'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Товар удалён из корзины']);
    }

}
