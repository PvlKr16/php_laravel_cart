@extends('layouts.app')

@section('content')
    <h1>Каталог</h1>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:18px;">
        @foreach($products as $product)
            @include('components.product-card', ['product' => $product])
        @endforeach
    </div>
@endsection
