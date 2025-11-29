<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

class CartController extends Controller
{
    /**
     * Cart getting
     */
    private function getCart()
    {
        $cart = CartSession::current();

        if (!$cart) {
            $currency = \Lunar\Models\Currency::where('default', true)->first();
            $channel  = \Lunar\Models\Channel::where('default', true)->first();

            $cart = \Lunar\Models\Cart::create([
                'currency_id' => $currency->id,
                'channel_id'  => $channel->id,
            ]);

            CartSession::use($cart);
        }

        return $cart;
    }

    /**
     * Cart showing
     */
    public function show()
    {
        $cart = $this->getCart();

        $cart->calculate();
        $cart->load('lines.purchasable.product');

        return response()->json([
            'id' => $cart->id,

            'items' => $cart->lines->map(function ($line) {

                $variant = $line->purchasable;

                $priceModel = $variant->prices()->first();
                $unitCents  = $priceModel?->getRawOriginal('price') ?? 0;

                $totalCents = $unitCents * $line->quantity;
                $formattedLineTotal = sprintf("$%.2f", $totalCents / 100);

                return [
                    'id' => $line->id,
                    'quantity' => $line->quantity,
                    'line_total' => $formattedLineTotal,
                    'product' => [
                        'name' => $variant->product?->translate('name'),
                    ]
                ];
            }),

            'total' => $cart->total?->formatted ?? '0',
        ]);
    }

    /**
     * Item adding
     */
    public function add(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|integer|exists:lunar_product_variants,id',
            'quantity'   => 'nullable|integer|min:1'
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);
        $qty = $request->quantity ?? 1;

        if ($variant->stock < $qty) {
            return response()->json([
                'success' => false,
                'error' => "Insufficient qty in stock. Available: {$variant->stock}"
            ], 422);
        }

        $cart = $this->getCart();
        $cart = $cart->add($variant, $qty);

        $variant->stock -= $qty;
        $variant->save();

        $cart->calculate();

        return response()->json([
            'success' => true,
            'new_stock' => $variant->stock
        ]);
    }

    /**
     * Line deleting
     */
    public function remove(Request $request)
    {
        $request->validate([
            'line_id' => 'required|integer'
        ]);

        $cart = $this->getCart();

        $line = $cart->lines()->find($request->line_id);

        if (!$line) {
            return response()->json(['success' => false], 404);
        }

        $variant = $line->purchasable;

        $variant->stock += $line->quantity;
        $variant->save();

        $line->delete();

        $cart->calculate();

        return response()->json([
            'success' => true,
            'variant_id' => $variant->id,
            'new_stock' => $variant->stock
        ]);
    }
}
