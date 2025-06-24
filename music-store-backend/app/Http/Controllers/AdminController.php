<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;


class AdminController extends Controller
{

    public function confirmOrder(Order $order)
    {
        $order->status = 'Подтвержден';
        $order->save();

        return response()->json(['message' => 'Заказ подтвержден']);
    }

    public function cancelOrder(Request $request, Order $order)
    {
        $order->status = 'Отменен';
        $order->cancel_reason = $request->input('reason');
        $order->save();

        return response()->json(['message' => 'Заказ отменен']);
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'year' => 'nullable|integer',
            'model' => 'nullable|string',
            'manufacturer_country' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }



    public function getAllProducts()
    {
        $products = Product::all();
        return response()->json(['products' => $products]);
    }


    public function updateProduct(Request $request, Product $product)
    {
        $product->update($request->only(['name', 'description', 'price', 'quantity', 'category_id']));

        return response()->json(['message' => 'Товар обновлен']);
    }

    public function deleteProduct(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Товар удален']);
    }

    public function createCategory(Request $request)
    {
        $category = Category::create(['name' => $request->input('name')]);

        return response()->json($category);
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);

        // Удалим все товары в этой категории
        $category->products()->delete();

        // Затем удалим саму категорию
        $category->delete();

        return response()->json(['message' => 'Категория и все связанные товары удалены']);
    }


}
