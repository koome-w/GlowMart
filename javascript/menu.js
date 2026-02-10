// Open/Close side nav
function openNav() {
    document.getElementById("sideNav").style.width = "250px";
}
function closeNav() {
    document.getElementById("sideNav").style.width = "0";
}

// Fetch products dynamically
fetch("../php/fetch_products.php")
.then(res => res.json())
.then(data => {
    const container = document.getElementById("products");
    const categoryScroll = document.getElementById("categoryScroll");

    // Extract unique categories
    const categories = ["All", ...new Set(data.map(p => p.category_name))];

    // Build horizontal category buttons
    categoryScroll.innerHTML = categories.map(cat => `
        <button class="category-btn" onclick="filterCategory('${cat}')">${cat}</button>
    `).join('');

    // Display all products initially
    displayProducts(data);

    // Filter function
    window.filterCategory = function(category) {
        const filtered = category === "All" ? data : data.filter(p => p.category_name === category);
        displayProducts(filtered);

        // Active button styling
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.toggle('active', btn.textContent === category);
        });
    }

    function displayProducts(products) {
        container.innerHTML = products.map(p => `
            <div class="product-card">
                <div class="product-container">
                <img src="../assets/${p.image}" alt="${p.name}">
                </div>
                <h3 class="product-name">${p.name}</h3>
                <p class="product-price">KES ${p.price}</p>
                <p class="stock">Stock: ${p.quantity}</p>
                <div class="quantity-container">
                    <button onclick="decreaseQty(${p.product_id})">-</button>
                    <input type="number" min="1" max="${p.quantity}" value="1" id="qty-${p.product_id}">
                    <button onclick="increaseQty(${p.product_id})">+</button>
                </div>
                <button class="add-to-cart" onclick="addToCart(${p.product_id})">Add to Cart</button>
            </div>
        `).join('');
    }
});

// Quantity helper functions
function decreaseQty(id) {
    const input = document.getElementById(`qty-${id}`);
    if(input.value > 1) input.value--;
}
function increaseQty(id) {
    const input = document.getElementById(`qty-${id}`);
    if(input.value < parseInt(input.max)) input.value++;
}

// Add to cart function
async function addToCart(productId) {
    const qty = parseInt(document.getElementById(`qty-${productId}`).value);

    try {
        const res = await fetch('../php/add_to_cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ product_id: productId, quantity: qty })
        });
        const data = await res.json();

        if(data.status === 'success') {
            alert('Added to cart!');
            updateCartCount();
        } else {
            alert(data.message);
        }
    } catch(err) {
        console.error(err);
    }
}

// Update cart count in header
async function updateCartCount() {
    try {
        const res = await fetch('../php/cart_count.php');
        const data = await res.json();
        document.getElementById('cart-count').textContent = data.count || 0;
    } catch(err) {
        console.error(err);
    }
}

// Call cart count on page load
updateCartCount();

