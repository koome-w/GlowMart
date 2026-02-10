const categories = [
  { id: 1, name: "Skin Care" },
  { id: 2, name: "Body Care" },
  { id: 3, name: "Hair Care" }
];

const products = [
  {
    id: 1,
    name: "Glow Face Serum",
    price: 1500,
    image: "assets/serum.jpg",
    rating: 4.6,
    category_id: 1
  },
  {
    id: 2,
    name: "Luxury Body Oil",
    price: 1200,
    image: "assets/oil.jpg",
    rating: 4.4,
    category_id: 2
  }
];

const categoryList = document.getElementById("categoryList");

categories.forEach(cat => {
  const li = document.createElement("li");
  li.textContent = cat.name;

  li.onclick = () => {
    document.querySelectorAll(".category-list li")
      .forEach(el => el.classList.remove("active"));
    li.classList.add("active");
    renderProducts(cat.id);
  };

  categoryList.appendChild(li);
});

const grid = document.getElementById("productsGrid");

function renderProducts(categoryId) {
  grid.innerHTML = "";

  products
    .filter(p => p.category_id === categoryId)
    .forEach(p => {
      grid.innerHTML += `
        <div class="product-card">
          <div class="product-image">
            <img src="${p.image}" alt="${p.name}">
          </div>

          <div class="product-info">
            <h4 class="product-name">${p.name}</h4>
            <p class="product-price">KES ${p.price}</p>
            <p class="product-rating">⭐ ${p.rating}</p>

            <div class="product-actions">
              <button class="add-cart-btn" onclick="addToCart(${p.id})">
                Add to Cart
              </button>
              <a class="view-btn" href="product.html?id=${p.id}">
                View
              </a>
            </div>
          </div>
        </div>
      `;
    });
}

function addToCart(id) {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  const item = cart.find(i => i.id === id);
  item ? item.qty++ : cart.push({ id, qty: 1 });

  localStorage.setItem("cart", JSON.stringify(cart));
  alert("Added to cart");
}

