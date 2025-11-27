// cart.js - vanilla JS for slide-in cart
(function () {
    const cartPanel = document.getElementById('cart-panel');
    const cartOverlay = document.getElementById('cart-overlay');
    const cartClose = document.getElementById('cart-close');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function setActive(state) {
        if (state) {
            cartPanel.classList.add('active');
            cartOverlay.classList.add('active');
            cartPanel.setAttribute('aria-hidden', 'false');
        } else {
            cartPanel.classList.remove('active');
            cartOverlay.classList.remove('active');
            cartPanel.setAttribute('aria-hidden', 'true');
        }
    }

    window.openCart = function () {
        setActive(true);
        loadCart();
    };

    function closeCart() {
        setActive(false);
    }

    cartOverlay.addEventListener('click', closeCart);
    cartClose.addEventListener('click', closeCart);

    async function loadCart() {
        try {
            const res = await fetch('/cart', { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Network error');
            const data = await res.json();
            renderCart(data);
        } catch (e) {
            console.error(e);
        }
    }

    function renderCart(data) {
        cartItems.innerHTML = '';

        if (!data.items || data.items.length === 0) {
            cartItems.innerHTML = '<p style="padding:10px;color:#666;">Корзина пуста</p>';
            cartTotal.textContent = 'Итого: 0';
            return;
        }

        data.items.forEach(item => {
            const thumb = item.product.thumbnail || 'data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="70" height="70"><rect width="100%" height="100%" fill="%23eee"/></svg>';

            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <img src="${thumb}" alt="${escapeHtml(item.product.name)}" />
                <div class="meta">
                    <div class="title">${escapeHtml(item.product.name)}</div>
                    <div class="qty">
                        <button class="small" data-action="dec" data-id="${item.id}">-</button>
                        <div style="min-width:28px; text-align:center;">${item.quantity}</div>
                        <button class="small" data-action="inc" data-id="${item.id}">+</button>
                        <button class="small" data-action="remove" style="margin-left:8px; color:#b00;" data-id="${item.id}">Удалить</button>
                    </div>
                    <div style="margin-top:6px; color:#444;">${item.total ?? item.subtotal ?? ''}</div>
                </div>
            `;
            cartItems.appendChild(div);
        });

        cartTotal.textContent = 'Итого: ' + (data.total ?? '0');
    }

    // delegation for + - remove
    cartItems.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-action');
        const id = btn.getAttribute('data-id');
        if (action === 'dec') {
            updateItem(id, -1);
        } else if (action === 'inc') {
            updateItem(id, +1);
        } else if (action === 'remove') {
            removeItem(id);
        }
    });

    async function updateItem(lineId, delta) {
        // need to get current quantity from DOM
        const qtyNode = cartItems.querySelector(`button[data-id="${lineId}"]`)?.parentElement?.querySelector('div:nth-child(2)');
        // fallback: just attempt server-side decrement/increment by reading current in UI is complex; we'll request new qty by fetching cart, find line
        try {
            const resCart = await fetch('/cart', { credentials: 'same-origin' });
            const data = await resCart.json();
            const line = data.items.find(i => String(i.id) === String(lineId));
            if (!line) return;
            const newQty = Math.max(1, (line.quantity || 1) + delta);

            await fetch('/cart/update', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ line_id: lineId, quantity: newQty })
            });

            loadCart();
        } catch (e) {
            console.error(e);
        }
    }

    async function removeItem(lineId) {
        try {
            await fetch('/cart/remove', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ line_id: lineId })
            });
            loadCart();
        } catch (e) {
            console.error(e);
        }
    }

    window.addToCart = async function (variantId) {
        try {
            await fetch('/cart/add', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ variant_id: variantId, quantity: 1 })
            });

            openCart();
        } catch (e) {
            console.error(e);
        }
    };

    // small helper
    function escapeHtml(s) {
        if (!s) return '';
        return s.replace(/[&<>"']/g, function (m) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[m];
        });
    }

    // Load cart count / preview optionally on page load (not required)
})();
