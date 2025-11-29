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

        // Cart recalculating
        $cart->calculate();

        $cart->load('lines.purchasable.product');

        return response()->json([
            'id' => $cart->id,
            'items' => $cart->lines->map(function ($line) {

                $name = $line->purchasable
                    ->product
                    ->translateAttribute('name');

                return [
                    'id'       => $line->id,
                    'quantity' => $line->quantity,
                    'total'    => $line->total?->formatted ?? '',
                    'product'  => [
                        'name' => $name,
                    ],
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
            'variant_id' => 'required|integer|exists:lunar_product_variants,id'
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);

        $cart = $this->getCart();

        // line adding to cart
        $cart->lines()->create([
            'quantity'         => 1,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id'   => $variant->id,
        ]);

        $cart->calculate();

        return response()->json(['success' => true]);
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

        $cart->lines()
            ->where('id', $request->line_id)
            ->delete();

        $cart->calculate();

        return response()->json(['success' => true]);
    }
}
