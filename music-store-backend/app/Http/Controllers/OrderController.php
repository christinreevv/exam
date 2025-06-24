<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status');

        $query = Order::with('user', 'items.product')
            ->when($status, fn($q) => $q->where('status', $status));

        if ($user->role === 1) {
            $orders = $query->orderBy('created_at', 'desc')->get();
        } else {
            $orders = $query->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Неверный пароль'], 422);
        }

        $cart = $user->cart->items()->with('product')->get();

        if ($cart->isEmpty()) {
            return response()->json(['message' => 'Корзина пуста'], 400);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'Новый' // русское значение "Новый"
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ]);

            $item->product->decrement('quantity', $item->quantity);
        }

        $user->cartItems()->delete();

        return response()->json(['message' => 'Заказ успешно оформлен']);
    }

    public function confirm($orderId)
    {
        $this->authorizeAdmin();

        $order = Order::findOrFail($orderId);
        $order->status = 'В обработке'; // вместо 'confirmed' ставим 'В обработке'
        // $order->cancel_reason = null;
        $order->save();

        return response()->json(['message' => 'Заказ подтверждён']);
    }

    public function cancel(Request $request, $orderId)
    {
        $this->authorizeAdmin();

        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        $order = Order::findOrFail($orderId);
        $order->status = 'Отменён'; // вместо 'cancelled' ставим 'Отменён'
        $order->cancel_reason = $request->cancel_reason;
        $order->save();

        return response()->json(['message' => 'Заказ отменён']);
    }

    public function destroy($orderId)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('status', 'Новый') // вместо 'new' пишем 'Новый'
            ->findOrFail($orderId);

        $order->delete();

        return response()->json(['message' => 'Заказ удалён']);
    }

    protected function authorizeAdmin()
    {
        if (auth()->user()->role !== 1) {
            abort(403, 'Доступ запрещён');
        }
    }
}
