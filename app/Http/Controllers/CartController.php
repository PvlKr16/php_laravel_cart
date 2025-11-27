<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lunar\Models\ProductVariant;
use Lunar\Models\Price;

class CartController extends Controller
{
    public function show()
    {
        $cart = session()->get('cart', []);

        $items = [];
        $total = 0;

        foreach ($cart as $lineId => $line) {

            $variant = ProductVariant::with('product', 'prices')->find($line['variant_id']);
            if (!$variant) continue;

            $price = $variant->prices->first();
            $lineTotal = $price->price->decimal * $line['quantity'];

            $items[] = [
                'id'       => $lineId,
                'variant_id' => $variant->id,
                'quantity' => $line['quantity'],
                'product'  => [
                    'name' => $variant->product->translateAttribute('name'),
                    'thumbnail' => null,
                ],
                'total'    => '$' . number_format($lineTotal, 2),
            ];

            $total += $lineTotal;
        }

        return response()->json([
            'items' => $items,
            'total' => '$' . number_format($total, 2),
        ]);
    }

    public function add(Request $request)
    {
        $variantId = $request->variant_id;

        if (!$variantId) {
            return response()->json(['error' => 'variant_id missing'], 400);
        }

        $cart = session()->get('cart', []);

        $lineId = uniqid();

        $cart[$lineId] = [
            'variant_id' => $variantId,
            'quantity'   => 1,
        ];

        session()->put('cart', $cart);

        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $lineId = $request->line_id;
        $qty    = (int)$request->quantity;

        $cart = session()->get('cart', []);

        if (!isset($cart[$lineId])) {
            return response()->json(['error' => 'line not found'], 404);
        }

        $cart[$lineId]['quantity'] = max(1, $qty);

        session()->put('cart', $cart);

        return response()->json(['success' => true]);
    }

    public function remove(Request $request)
    {
        $lineId = $request->line_id;

        $cart = session()->get('cart', []);

        unset($cart[$lineId]);

        session()->put('cart', $cart);

        return response()->json(['success' => true]);
    }
}
