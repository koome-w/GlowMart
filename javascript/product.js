const params = new URLSearchParams(window.location.search);
const productId = parseInt(params.get("id"));

const product = products.find(p => p.id === productId);

document.getElementById("productDetails").innerHTML = `
  <h2>${product.name}</h2>
  <img src="${product.image}">
  <p>KES ${product.price}</p>
  <button onclick="addToCart(${product.id})">Add to Cart</button>
`;

const reviews = [
  { product_id: 1, user: "Amina", rating: 5, comment: "Perfect glow!" },
  { product_id: 1, user: "Joy", rating: 4, comment: "Very smooth" }
];

document.getElementById("reviewsSection").innerHTML = `
  <h3>Customer Reviews</h3>
  ${reviews
    .filter(r => r.product_id === productId)
    .map(r => `
      <p>⭐ ${r.rating} – ${r.comment} <strong>${r.user}</strong></p>
    `).join("")}
`;