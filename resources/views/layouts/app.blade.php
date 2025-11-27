<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Demo Shop</title>

    {{-- CSRF token for JS --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- main styles --}}
    <link rel="stylesheet" href="/css/cart.css">

    <style>
        body { font-family: Arial, sans-serif; margin: 0; color:#222; }
        .navbar { display:flex; align-items:center; justify-content:space-between; padding:12px 20px; border-bottom:1px solid #eee; }
        .navbar .brand { font-weight:700; }
        .container { max-width:1100px; margin: 0 auto; padding:20px; }
        a { color: inherit; text-decoration: none; }
    </style>
</head>
<body>

<header class="navbar">
    <div class="brand"><a href="{{ route('products.index') }}">Demo Shop</a></div>

    <div>
        <button onclick="openCart()" style="padding:8px 12px; cursor:pointer;">Корзина</button>
    </div>
</header>

<main class="container">
    @yield('content')
</main>

{{-- HTML корзины (вставляем один раз) --}}
<div id="cart-overlay"></div>

<div id="cart-panel" aria-hidden="true" role="dialog" aria-label="Корзина">
    <div id="cart-header">
        <h3>Корзина</h3>
        <button id="cart-close" aria-label="Закрыть">&times;</button>
    </div>

    <div id="cart-items" aria-live="polite"></div>

    <div id="cart-footer">
        <div id="cart-total"></div>
        <button id="checkout-btn" onclick="location.href='/checkout'">Оформить заказ</button>
    </div>
</div>

{{-- Подключаем JS после HTML --}}
<script src="/js/cart.js"></script>
</body>
</html>
