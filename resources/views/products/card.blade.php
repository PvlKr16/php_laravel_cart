@php
    $variant = $product->variants->first();
    $price = $variant?->prices->first();
@endphp

<div style="border:1px solid #ddd; padding:15px; text-align:center;">
    <h3>{{ $product->translateAttribute('name') }}</h3>

    <p>
        @if($price)
            ${{ number_format($price->price->decimal, 2) }}
        @else
            <span style="color:#777;">Price is missing</span>
        @endif
    </p>

    @if($variant)
        <button onclick="addToCart({{ $variant->id }})"
                style="padding:8px 12px; cursor:pointer;">
            Add to cart
        </button>
    @else
        <p style="color:#999;">No items</p>
    @endif
</div>
