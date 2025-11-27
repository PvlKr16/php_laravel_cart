<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lunar\Base\CartSessionInterface;
use Lunar\Models\ProductVariant;

class CartController extends Controller
{
    protected CartSessionInterface $cartSession;

    public function __construct(CartSessionInterface $cartSession)
    {
        $this->cartSession = $cartSession;
    }

    public function get()
    {
        $cart = $this->cartSession->current();

        if (! $cart) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $items = $cart->lines->map(function ($line) {
            return [
                'id' => $line->id,
                'quantity' => $line->quantity,
                'subtotal' => $line->subTotal?->formatted ?? ($line->subTotal?->value ?? null),
                'total' => $line->total?->formatted ?? ($line->total?->value ?? null),
                'product' => [
                    'name' => $line->purchasable->product->translate('name') ?? $line->purchasable->name,
                    'thumbnail' => $line->purchasable->thumbnail?->getUrl() ?? null,
                ],
            ];
        })->values();

        $total = $cart->total?->formatted ?? ($cart->total?->value ?? 0);

        return response()->json([
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|integer',
            'quantity' => 'sometimes|integer|min:1',
        ]);

        $variant = ProductVariant::find($data['variant_id']);

        if (! $variant) {
            return response()->json(['error' => 'Variant not found'], 404);
        }

        $this->cartSession->manager()->add($variant, $data['quantity'] ?? 1);

        return $this->get();
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'line_id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->cartSession->current();

        $line = $cart->lines->where('id', $data['line_id'])->first();

        if (! $line) {
            return response()->json(['error' => 'Line not found'], 404);
        }

        $this->cartSession->manager()->updateLine($line, $data['quantity']);

        return $this->get();
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'line_id' => 'required'
        ]);

        $cart = $this->cartSession->current();

        $line = $cart->lines->where('id', $data['line_id'])->first();

        if (! $line) {
            return response()->json(['error' => 'Line not found'], 404);
        }

        $this->cartSession->manager()->removeLine($line);

        return $this->get();
    }
}
