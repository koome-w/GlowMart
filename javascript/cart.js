// Update the cart count in the header
async function updateCartCount() {
    try {
        const res = await fetch('../php/cart_count.php');
        const data = await res.json();
        const cartCountElem = document.getElementById('cart-count');
        if (cartCountElem) cartCountElem.textContent = data.count || 0;
    } catch (err) {
        console.error("Error updating cart count:", err);
    }
}

// Load the cart items and render them
async function loadCart() {
    try {
        const res = await fetch('../php/fetch_cart.php');
        const items = await res.json();

        const container = document.getElementById('cart-items');
        const emptyMsg = document.getElementById('empty-cart');
        const cartTotalElem = document.getElementById('cart-total');
        let total = 0;

        if (!container || !emptyMsg || !cartTotalElem) return;

        if (items.length === 0) {
            container.style.display = 'none';
            emptyMsg.style.display = 'block';
            cartTotalElem.textContent = 'Total: KES 0';
            return;
        } else {
            container.style.display = 'flex';
            emptyMsg.style.display = 'none';
        }

        // Render items dynamically
        container.innerHTML = items.map(item => {
            total += item.price * item.quantity;
            return `
                <div class="cart-item" data-id="${item.product_id}">
                    <img src="../assets/${item.image}" alt="${item.name}">
                    <div class="cart-item-details">
                        <h3>${item.name}</h3>
                        <p>Price: KES ${item.price}</p>
                        <div class="quantity-container">
                            <button class="qty-minus">-</button>
                            <input type="number" class="qty-input" id="qty-${item.product_id}" value="${item.quantity}" min="1">
                            <button class="qty-plus">+</button>
                        </div>
                    </div>
                    <button class="remove-btn">Remove</button>
                </div>
            `;
        }).join('');

        cartTotalElem.textContent = 'Total: KES ' + total;

    } catch (err) {
        console.error("Error loading cart:", err);
    }
}

// Event delegation for cart item actions
// Using a delegated click listener on document to handle dynamically created elements
document.addEventListener('click', async (e) => {
    // Check if the clicked element is a quantity button or remove button
    const qtyPlusBtn = e.target.closest('.qty-plus');
    const qtyMinusBtn = e.target.closest('.qty-minus');
    const removeBtn = e.target.closest('.remove-btn');
    
    // Only proceed if one of our cart action buttons was clicked
    if (!qtyPlusBtn && !qtyMinusBtn && !removeBtn) return;

    // Get the parent cart item
    const cartItem = e.target.closest('.cart-item');
    if (!cartItem) return;

    const productId = cartItem.dataset.id;
    if (!productId) {
        console.error("Product ID not found on cart item");
        return;
    }

    // Handle quantity increase
    if (qtyPlusBtn) {
        const input = cartItem.querySelector('.qty-input');
        if (input) {
            input.value = parseInt(input.value) + 1;
            await changeQty(productId, input);
        }
    }
    // Handle quantity decrease
    else if (qtyMinusBtn) {
        const input = cartItem.querySelector('.qty-input');
        if (input) {
            input.value = Math.max(1, parseInt(input.value) - 1);
            await changeQty(productId, input);
        }
    }
    // Handle item removal
    else if (removeBtn) {
        await removeItem(productId);
    }
});

// Update the quantity of a product in the cart
async function changeQty(productId, input) {
    let newQty = parseInt(input.value);
    if (newQty < 1) newQty = 1;
    input.value = newQty;

    try {
        console.log(`Updating product ${productId} to quantity ${newQty}`);
        
        const response = await fetch('../php/update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, quantity: newQty })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        console.log('Quantity updated successfully');
        
        // Reload cart items and cart count
        await loadCart();
        await updateCartCount();
    } catch (err) {
        console.error("Error updating cart:", err);
        alert("Failed to update quantity. Please try again.");
    }
}

// Remove a product from the cart
async function removeItem(productId) {
    try {
        console.log(`Removing product ${productId} from cart`);
        
        const response = await fetch('../php/remove_cart_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        console.log('Item removed successfully');
        
        // Reload cart items and cart count after removal
        await loadCart();
        await updateCartCount();
    } catch (err) {
        console.error("Error removing item:", err);
        alert("Failed to remove item. Please try again.");
    }
}

// Checkout button (placeholder)
const checkoutBtn = document.getElementById('checkout-btn');
if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
        alert('Checkout functionality will be added later!');
    });
}

// Initialize
console.log('Cart script initializing...');
updateCartCount();
loadCart();
