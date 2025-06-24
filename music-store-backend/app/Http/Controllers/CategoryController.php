<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::all();
            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            // Логируем ошибку
            \Log::error('Error fetching categories: '.$e->getMessage());
            return response()->json(['message' => 'Ошибка сервера'], 500);
        }
    }

}
