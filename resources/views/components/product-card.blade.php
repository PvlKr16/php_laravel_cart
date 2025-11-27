<div class="product-card" style="border:1px solid #eee; padding:14px; border-radius:8px;">
    <div style="height:180px; display:flex; align-items:center; justify-content:center; overflow:hidden;">
        <img src="{{ $product->thumbnail?->getUrl() }}" alt="{{ $product->translate('name') }}" style="max-width:100%; max-height:100%;">
    </div>

    <h3 style="margin:10px 0 4px 0;">{{ $product->translate('name') }}</h3>
    <p style="margin:0 0 10px 0; color:#666;">{{ \Illuminate\Support\Str::limit($product->translate('description') ?? '', 120) }}</p>

    @php
        $variant = $product->variants->first();

        $priceObj = null;
        if ($variant) {
            $priceObj = \Lunar\Models\Price::where('priceable_type', \Lunar\Models\ProductVariant::class)
                ->where('priceable_id', $variant->id)
                ->first();
        }

        $price = $priceObj?->formatted ?? ($priceObj?->price ?? '—');
    @endphp

    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <div><strong>{{ $price }}</strong></div>

        <div>
            <a href="{{ route('products.show', $product) }}" style="margin-right:8px;">Подробнее</a>

            @if ($variant && $variant->id)
                <button onclick="addToCart({{ $variant->id }})"
                        style="padding:6px 10px; cursor:pointer;">
                    В корзину
                </button>
            @else
                <button disabled style="padding:6px 10px; background:#ddd; color:#777;">
                    Нет в наличии
                </button>
            @endif

        </div>
    </div>
</div>
