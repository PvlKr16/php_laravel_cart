(function () {
    const panel = document.getElementById('cart-panel');
    const overlay = document.getElementById('cart-overlay');
    const closeBtn = document.getElementById('cart-close');
    const itemsBox = document.getElementById('cart-items');
    const totalBox = document.getElementById('cart-total');

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function setOpen(open) {
        if (open) {
            panel.classList.add('active');
            overlay.classList.add('active');
            loadCart();
        } else {
            panel.classList.remove('active');
            overlay.classList.remove('active');
        }
    }

    window.openCart = () => setOpen(true);
    closeBtn.onclick = () => setOpen(false);
    overlay.onclick = () => setOpen(false);

    async function loadCart() {
        const res = await fetch('/cart');
        const data = await res.json();
        render(data);
    }

    function render(data) {
        itemsBox.innerHTML = '';

        if (!data.items.length) {
            itemsBox.innerHTML = '<p>Cart is empty</p>';
            totalBox.textContent = 'Total: 0';
            return;
        }

        data.items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <div class="meta">
                    <div class="title">${item.product.name}</div>
                    <div>Quantity: ${item.quantity}</div>
                    <div>${item.total}</div>
                </div>

                <button data-action="remove" data-id="${item.id}" class="small" style="margin-left:auto;">
                    Remove
                </button>
            `;
            itemsBox.appendChild(div);
        });

        totalBox.textContent = 'Total: ' + data.total;
    }

    window.addToCart = async function (variantId) {
        await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ variant_id: variantId })
        });
        openCart();
    };

    window.removeFromCart = async function (lineId) {
        await fetch('/cart/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ line_id: lineId })
        });
        loadCart();
    };

    itemsBox.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;

        if (btn.dataset.action === 'remove') {
            removeFromCart(btn.dataset.id);
        }
    });
})();
