@extends('layouts.app')

@section('content')

    <div style="max-width: 900px; margin: 0 auto; padding: 30px;">

        <div style="display: flex; gap: 30px; flex-wrap: wrap; align-items:flex-start;">

            <div style="flex: 1 1 300px;">
                <img src="{{ $product->thumbnail?->getUrl() }}" alt="{{ $product->translate('name') }}" style="width: 100%; max-width: 480px; border-radius: 8px;">
            </div>

            <div style="flex: 1 1 300px;">
                <h1 style="margin-bottom: 10px;">{{ $product->translate('name') }}</h1>

                <p style="font-size: 16px; margin-bottom: 20px; color:#444;">
                    {!! nl2br(e($product->translate('description'))) !!}
                </p>

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

                <div style="font-size: 20px; margin-bottom: 20px;">
                    Цена: <strong>{{ $price }}</strong>
                </div>

                <button onclick="addToCart({{ $variant->id }})"
                        style="padding:12px 20px; background:#111; color:#fff; border:none; cursor:pointer; border-radius:4px;">
                    В корзину
                </button>
            </div>

        </div>

    </div>

@endsection
