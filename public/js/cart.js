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
                    <div class="qty">
                        <div style="min-width:28px; text-align:center;">${item.quantity}</div>
                    </div>
                    <div style="margin-top:6px; color:#444;">${item.total ?? ''}</div>
                </div>

                <button class="small" data-action="remove" data-id="${item.id}" style="margin-left:auto;">
                    Delete
                </button>
            `;
            itemsBox.appendChild(div);
        });

        totalBox.textContent = 'Total: ' + data.total;
    }

    // all buttons main processor (+, -, Delete)
    itemsBox.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;

        const action = btn.dataset.action;
        const id = btn.dataset.id;

        if (action === 'remove') {
            removeItem(id);
        }
    });

    async function removeItem(lineId) {
        await fetch('/cart/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ line_id: lineId })
        });

        loadCart();
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
})();
