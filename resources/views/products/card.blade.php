@php
    $variant = $product->variants->first();
    $price   = $variant->prices->first();
    $amount  = $price?->price->value / 100;
    $stock   = $variant->stock;
@endphp

<div style="border:1px solid #ddd; padding:15px; text-align:center;">
    <h3>{{ $product->translateAttribute('name') }}</h3>

    <p><strong>Price:</strong> {{ $amount }} USD</p>
    <p><strong>In stock:</strong> {{ $stock }} pcs</p>

    <label>
        Quantity:
        <input type="number" min="1" max="{{ $stock }}" value="1" id="qty-{{ $variant->id }}">
    </label>

    <button onclick="addToCartWithQty({{ $variant->id }}, {{ $stock }})">
        Add to cart
    </button>

    <p id="msg-{{ $variant->id }}" style="color:red;"></p>

</div>
