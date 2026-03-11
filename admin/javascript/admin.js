// ========== 1. DASHBOARD ==========

document.addEventListener('DOMContentLoaded', function () {
    fetch('../php/dashboard_data.php')
        .then(res => res.json())
        .then(data => {
            document.getElementById('sales-count').textContent = data.sales;
            document.getElementById('users-count').textContent = data.users;
            document.getElementById('orders-count').textContent = data.orders;
            document.getElementById('pending-count').textContent = data.pending;
            document.getElementById('stocks-count').textContent = data.stocks;

            const ctx = document.getElementById('categoryPieChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.categories,
                    datasets: [{
                        data: data.quantities,
                        backgroundColor: [
                            '#ff9800', '#4caf50', '#2196f3', '#e91e63',
                            '#9c27b0', '#00bcd4', '#ffc107', '#8bc34a'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        })
        .catch(() => {
            // fallback or error display
        });
});


// ========== 2. PRODUCT MANAGEMENT ==========

// In-memory store for products — avoids serializing data into onclick attributes
const productMap = {};

function loadCategories(selectId, includeAll = true, callback = null) {
    fetch('../php/products_api.php?action=categories')
        .then(res => res.json())
        .then(categories => {
            const select = document.getElementById(selectId);
            select.innerHTML = '';
            if (includeAll) select.innerHTML += '<option value="0">All Categories</option>';
            categories.forEach(cat => {
                select.innerHTML += `<option value="${cat.category_id}">${cat.category_name}</option>`;
            });
            if (callback) callback();
        })
        .catch(err => console.error('Failed to load categories:', err));
}

function loadProducts() {
    const cat = document.getElementById('categoryFilter').value;
    fetch('../php/products_api.php?action=list&category=' + cat)
        .then(res => res.json())
        .then(products => {
            const tbody = document.getElementById('productTableBody');
            tbody.innerHTML = '';

            // Reset the product map on every load to stay in sync
            Object.keys(productMap).forEach(k => delete productMap[k]);

            if (!Array.isArray(products) || products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No products found.</td></tr>';
                return;
            }

            products.forEach(prod => {
                // Store full product object by ID — safe, no HTML serialization
                productMap[prod.product_id] = prod;

                tbody.innerHTML += `
                    <tr>
                        <td><img src="../../assets/${prod.image || 'no-image.png'}" alt="Product" style="max-width:60px;border-radius:8px;" /></td>
                        <td>${prod.name}</td>
                        <td>${prod.category_name || ''}</td>
                        <td>KES ${parseFloat(prod.price).toFixed(2)}</td>
                        <td>${prod.quantity}</td>
                        <td>
                            <button class="action-btn" onclick="editProduct(${prod.product_id})" title="Edit"><i class="fa fa-edit"></i></button>
                            <button class="action-btn" onclick="deleteProduct(${prod.product_id})" title="Delete"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => console.error('Failed to load products:', err));
}

function openProductModal(edit = false, prod = null) {
    document.getElementById('productModal').classList.add('show');
    document.getElementById('modalTitle').textContent = edit ? 'Edit Product' : 'Add Product';
    document.getElementById('productForm').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('productImagePreview').style.display = 'none';

    // Load categories first, then prefill fields if editing
    loadCategories('product_category', false, () => {
        if (edit && prod) {
            document.getElementById('product_id').value      = prod.product_id;
            document.getElementById('product_name').value    = prod.name;
            document.getElementById('product_category').value = prod.category_id;
            document.getElementById('product_price').value   = prod.price;
            document.getElementById('product_stock').value   = prod.quantity;

            if (prod.image) {
                const preview = document.getElementById('productImagePreview');
                // prod.image already contains 'assets/filename.jpg' — no duplication
                preview.src = '../../assets/' + prod.image;
                preview.style.display = 'block';
            }
        }
    });
}

// Receives only a plain integer ID — looks up full data from productMap
function editProduct(productId) {
    const prod = productMap[productId];
    if (!prod) {
        alert('Product data not found. Please refresh and try again.');
        return;
    }
    openProductModal(true, prod);
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('show');
}

function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('product_id', id);
    fetch('../php/products_api.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadProducts();
            } else {
                alert(data.error || 'Failed to delete product.');
            }
        })
        .catch(err => console.error('Delete failed:', err));
}

// ── Event Listeners ──────────────────────────────────────────────────────────

document.getElementById('openAddProductModal').addEventListener('click', () => {
    openProductModal(false);
});

document.getElementById('closeProductModal').addEventListener('click', closeProductModal);

// Close modal when clicking outside the modal content
document.getElementById('productModal').addEventListener('click', function (e) {
    if (e.target === this) closeProductModal();
});

// Image preview on file select
document.getElementById('product_image').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('productImagePreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Form submit — handles both Add and Edit
document.getElementById('productForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    const isEdit = document.getElementById('product_id').value !== '';
    fd.append('action', isEdit ? 'edit' : 'add');

    fetch('../php/products_api.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeProductModal();
                loadProducts();
            } else {
                alert(data.error || 'Something went wrong. Please try again.');
            }
        })
        .catch(err => console.error('Save failed:', err));
});

// Category filter change
document.getElementById('categoryFilter').addEventListener('change', loadProducts);

// ── Page Init — loads categories first, then triggers loadProducts via callback ──
loadCategories('categoryFilter', true, loadProducts);


// ========== 3. CATEGORY MANAGEMENT ==========

// In-memory store — avoids serializing data into onclick attributes
const categoryMap = {};

function loadCategoryTable() {
    fetch('../php/categories_api.php?action=list')
        .then(res => res.json())
        .then(categories => {
            const tbody = document.getElementById('categoryTableBody');
            tbody.innerHTML = '';

            // Reset map on every load
            Object.keys(categoryMap).forEach(k => delete categoryMap[k]);

            if (!Array.isArray(categories) || categories.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;color:#888;">No categories found.</td></tr>';
                return;
            }

            categories.forEach(cat => {
                // Store full object by ID
                categoryMap[cat.category_id] = cat;

                tbody.innerHTML += `
                    <tr>
                        <td>${cat.category_name}</td>
                        <td>
                            <button class="action-btn" onclick="editCategory(${cat.category_id})" title="Edit"><i class="fa fa-edit"></i></button>
                            <button class="action-btn" onclick="deleteCategory(${cat.category_id})" title="Delete"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => console.error('Failed to load categories:', err));
}

function openCategoryModal(edit = false, cat = null) {
    document.getElementById('categoryModal').classList.add('show');
    document.getElementById('categoryModalTitle').textContent = edit ? 'Edit Category' : 'Add Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';

    if (edit && cat) {
        document.getElementById('category_id').value = cat.category_id;
        document.getElementById('category_name').value = cat.category_name;
    }
}

function closeCategoryModal() {
    document.getElementById('categoryModal').classList.remove('show');
}

// Receives plain integer ID — looks up full data from categoryMap
function editCategory(categoryId) {
    const cat = categoryMap[categoryId];
    if (!cat) {
        alert('Category data not found. Please refresh and try again.');
        return;
    }
    openCategoryModal(true, cat);
}

function deleteCategory(id) {
    if (!confirm('Delete this category?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('category_id', id);
    fetch('../php/categories_api.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadCategoryTable();
            } else {
                alert(data.error || 'Failed to delete category.');
            }
        })
        .catch(err => console.error('Delete failed:', err));
}

// ── Event Listeners ──────────────────────────────────────────────────────────

document.getElementById('openAddCategoryModal').addEventListener('click', () => {
    openCategoryModal(false);
});

document.getElementById('closeCategoryModal').addEventListener('click', closeCategoryModal);

// Close modal when clicking outside
document.getElementById('categoryModal').addEventListener('click', function (e) {
    if (e.target === this) closeCategoryModal();
});

// Form submit — handles both Add and Edit
document.getElementById('categoryForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    const isEdit = document.getElementById('category_id').value !== '';
    fd.append('action', isEdit ? 'edit' : 'add');

    fetch('../php/categories_api.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeCategoryModal();
                loadCategoryTable();
            } else {
                alert(data.error || 'Something went wrong. Please try again.');
            }
        })
        .catch(err => console.error('Save failed:', err));
});

// ── Page Init ──────────────────────────────────────────────────────────────
loadCategoryTable();


// ========== 4. ORDER MANAGEMENT ==========

// In-memory store — avoids serializing data into onclick attributes
const orderMap = {};

function loadOrderTable() {
    fetch('../php/orders_api.php?action=list')
        .then(res => res.json())
        .then(orders => {
            const tbody = document.getElementById('orderTableBody');
            tbody.innerHTML = '';

            // Reset map on every load
            Object.keys(orderMap).forEach(k => delete orderMap[k]);

            if (!Array.isArray(orders) || orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#888;">No orders found.</td></tr>';
                return;
            }

            orders.forEach(order => {
                // Store full object by ID
                orderMap[order.order_id] = order;

                tbody.innerHTML += `
                    <tr>
                        <td>${order.order_id}</td>
                        <td>${order.fullname || ''}</td>
                        <td>${order.product_name || ''}</td>
                        <td>${order.quantity}</td>
                        <td>KES ${parseFloat(order.total_amount).toFixed(2)}</td>
                        <td>${order.status}</td>
                        <td>${order.created_at ? order.created_at.split(' ')[0] : ''}</td>
                        <td>
                            <button class="action-btn" onclick="openOrderStatusModal(${order.order_id})" title="Update Status"><i class="fa fa-edit"></i></button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(err => console.error('Failed to load orders:', err));
}

// Receives plain integer ID — looks up full data from orderMap
function openOrderStatusModal(orderId) {
    const order = orderMap[orderId];
    if (!order) {
        alert('Order data not found. Please refresh and try again.');
        return;
    }
    document.getElementById('orderStatusModal').classList.add('show');
    document.getElementById('order_id').value = order.order_id;
    document.getElementById('order_status').value = order.status;
    // document.getElementById('delivery_date').value = order.delivery_date ? order.delivery_date.split(' ')[0] : '';
}

function closeOrderStatusModal() {
    document.getElementById('orderStatusModal').classList.remove('show');
}

// ── Event Listeners ──────────────────────────────────────────────────────────

document.getElementById('closeOrderStatusModal').addEventListener('click', closeOrderStatusModal);

// Close modal when clicking outside
document.getElementById('orderStatusModal').addEventListener('click', function (e) {
    if (e.target === this) closeOrderStatusModal();
});

// Form submit — update order status
document.getElementById('orderStatusForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action', 'update');

    fetch('../php/orders_api.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeOrderStatusModal();
                loadOrderTable();
            } else {
                alert(data.error || 'Failed to update order.');
            }
        })
        .catch(err => console.error('Update failed:', err));
});

// ── Page Init ────────────────────────────────────────────────────────────────
loadOrderTable();


// ========== 5. PAYMENT MANAGEMENT ==========

function loadPaymentTable() {
    fetch('payments_api.php?action=list')
        .then(res => res.json())
        .then(payments => {
            const tbody = document.getElementById('paymentTableBody');
            tbody.innerHTML = '';
            if (payments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No payments found.</td></tr>';
                return;
            }
            payments.forEach(pay => {
                tbody.innerHTML += `
                    <tr>
                        <td>${pay.receipt_id}</td>
                        <td>${pay.order_id}</td>
                        <td>${pay.payment_method}</td>
                        <td>KES ${parseFloat(pay.amount).toFixed(2)}</td>
                        <td>${pay.status}</td>
                        <td>${pay.payment_date ? pay.payment_date.split(' ')[0] : ''}</td>
                    </tr>
                `;
            });
        });
}


// ========== 6. FEEDBACK MANAGEMENT ==========

function loadFeedbackTable() {
    fetch('feedback_api.php?action=list')
        .then(res => res.json())
        .then(feedbacks => {
            const tbody = document.getElementById('feedbackTableBody');
            tbody.innerHTML = '';
            if (feedbacks.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#888;">No feedback found.</td></tr>';
                return;
            }
            feedbacks.forEach(fb => {
                tbody.innerHTML += `
                    <tr>
                        <td>${fb.feedback_id}</td>
                        <td>${fb.username || ''}</td>
                        <td>${fb.message}</td>
                        <td>${fb.rating ? fb.rating + ' / 5' : ''}</td>
                        <td>${fb.created_at ? fb.created_at.split(' ')[0] : ''}</td>
                    </tr>
                `;
            });
        });
}


// ========== 7. REPORTS ==========

function loadReportsTable() {
    fetch('reports_api.php?action=sales')
        .then(res => res.json())
        .then(orders => {
            const tbody = document.getElementById('reportsTableBody');
            tbody.innerHTML = '';
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#888;">No sales data found.</td></tr>';
                return;
            }
            orders.forEach(order => {
                tbody.innerHTML += `
                    <tr>
                        <td>${order.order_id}</td>
                        <td>${order.username || ''}</td>
                        <td>${order.product_name || ''}</td>
                        <td>${order.quantity}</td>
                        <td>KES ${parseFloat(order.total).toFixed(2)}</td>
                        <td>${order.status}</td>
                        <td>${order.order_date ? order.order_date.split(' ')[0] : ''}</td>
                    </tr>
                `;
            });
        });
}

function downloadSalesCSV() {
    window.open('reports_api.php?action=sales_csv', '_blank');
}

function downloadSalesPDF() {
    window.open('reports_api.php?action=sales_pdf', '_blank');
}


// ========== 8. ADMIN PROFILE ==========

function loadAdminProfile() {
    fetch('profile_api.php?action=get')
        .then(res => res.json())
        .then(data => {
            document.getElementById('admin_full_name').value = data.full_name || '';
            document.getElementById('admin_email').value = data.email || '';
            document.getElementById('admin_phone').value = data.phone || '';
            if (data.profile_pic) {
                document.getElementById('adminProfilePicPreview').src = '../' + data.profile_pic;
                document.getElementById('adminProfilePicPreview').style.display = 'block';
            } else {
                document.getElementById('adminProfilePicPreview').style.display = 'none';
            }
        });
}


// ========== 9. DOMContentLoaded — ALL EVENT BINDINGS ==========

document.addEventListener('DOMContentLoaded', function () {

    // --- Products ---
    loadCategories('categoryFilter');
    loadProducts();
    document.getElementById('categoryFilter').addEventListener('change', loadProducts);
    document.getElementById('openAddProductModal').addEventListener('click', () => openProductModal(false));
    document.getElementById('closeProductModal').addEventListener('click', closeProductModal);
    document.getElementById('product_image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                document.getElementById('productImagePreview').src = ev.target.result;
                document.getElementById('productImagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    document.getElementById('productForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', document.getElementById('product_id').value ? 'edit' : 'add');
        fetch('products_api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(() => {
                closeProductModal();
                loadProducts();
            });
    });

    // --- Categories ---
    loadCategoryTable();
    document.getElementById('openAddCategoryModal').addEventListener('click', () => openCategoryModal(false));
    document.getElementById('closeCategoryModal').addEventListener('click', closeCategoryModal);
    document.getElementById('categoryForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', document.getElementById('category_id').value ? 'edit' : 'add');
        fetch('categories_api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(() => {
                closeCategoryModal();
                loadCategoryTable();
                loadCategories('categoryFilter');
                loadCategories('product_category', false);
            });
    });

    // --- Orders ---
    loadOrderTable();
    document.getElementById('closeOrderStatusModal').addEventListener('click', closeOrderStatusModal);
    document.getElementById('orderStatusForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', 'update');
        fetch('orders_api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(() => {
                closeOrderStatusModal();
                loadOrderTable();
            });
    });

    // --- Payments ---
    loadPaymentTable();

    // --- Feedback ---
    loadFeedbackTable();

    // --- Reports ---
    loadReportsTable();
    document.getElementById('downloadSalesCSV').addEventListener('click', downloadSalesCSV);
    document.getElementById('downloadSalesPDF').addEventListener('click', downloadSalesPDF);

    // --- Admin Profile ---
    loadAdminProfile();
    document.getElementById('admin_profile_pic').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                document.getElementById('adminProfilePicPreview').src = ev.target.result;
                document.getElementById('adminProfilePicPreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    document.getElementById('adminProfileForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', 'update');
        fetch('profile_api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(() => loadAdminProfile());
    });
    document.getElementById('adminPasswordForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', 'change_password');
        fetch('profile_api.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(resp => {
                if (resp.error) {
                    alert(resp.error);
                } else {
                    alert('Password changed successfully!');
                    document.getElementById('adminPasswordForm').reset();
                }
            });
    });

});