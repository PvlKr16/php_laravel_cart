@php
    $variant = $product->variants->first();
    $price   = $variant->prices->first();
    $amount  = $price?->price->value / 100;
    $stock   = $variant->stock;
@endphp

<div style="border:1px solid #ddd; padding:15px; text-align:center;">
    <h3>{{ $product->translateAttribute('name') }}</h3>

    <p><strong>Price:</strong> {{ $amount }} USD</p>
    <div class="stock" id="stock-{{ $variant->id }}">
        На складе: {{ $variant->stock }}
    </div>

    <label>
        Quantity:
        <input type="number" id="qty-{{ $variant->id }}" value="1" min="1" max="{{ $variant->stock }}">
        <div id="msg-{{ $variant->id }}" style="color:red; font-size:14px;"></div>
    </label>

    <button onclick="addToCartWithQty({{ $variant->id }})">
        В корзину
    </button>

    <p id="msg-{{ $variant->id }}" style="color:red;"></p>

</div>
