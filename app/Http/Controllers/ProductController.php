<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lunar\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        // Подгружаем продукты — адаптируй, если у тебя другой способ
        $products = Product::with('variants.prices')->get();
        return view('products.index', compact('products'));
    }

    public function show(Product $product)
    {
        $product->load('variants.prices');
        return view('products.show', compact('product'));
    }
}
