<?php
require_once 'auth_terminal.php';

if ($_SESSION['terminal_type'] !== 'CUSTOMER') {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// Fetch categories (now including img)
$catStmt = $pdo->query("
    SELECT id, name, img
    FROM product_categories
    ORDER BY name
");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all active menu items (with category and is_bundle)
$itemStmt = $pdo->query("
    SELECT m.id, m.name, m.price, m.image_path, m.is_bundle, c.name AS category_name, c.id AS category_id
    FROM menu_items m
    LEFT JOIN product_categories c ON c.id = m.category_id
    WHERE m.is_active = 1
    ORDER BY c.name, m.name
");
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drink Ordering Kiosk</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Roboto', Arial, sans-serif;
    background: #f5f5f5;
    color: #333;
    overflow: hidden;
}

.kiosk {
    width: 100vw;
    height: 100vh;
    display: flex;
    flex-direction: column;
    background: #ffffff;
}

/* McDonald's-style Banner */
.banner {
    background: linear-gradient(135deg, #DA291C 0%, #FFCC00 100%);
    padding: 20px 30px;
    text-align: center;
    color: #fff;
    position: relative;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
    border-bottom: 4px solid #FFCC00;
}

.banner h2 {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    color: #fff;
}

.banner p {
    margin: 5px 0 0;
    font-size: 1rem;
    font-weight: 300;
    color: #fff;
}

.logout-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid #fff;
    color: #fff;
    font-size: 1.5rem;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease, transform 0.2s ease;
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.4);
    transform: scale(1.1);
}

.main {
    display: flex;
    flex: 1;
    overflow: hidden;
}

/* McDonald's Sidebar */
.sidebar {
    width: 280px;
    background: #DA291C;
    overflow-y: auto;
    flex-shrink: 0;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar li {
    padding: 18px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    font-weight: 500;
    color: #fff;
}

.sidebar li:hover {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar .active {
    background: #FFCC00;
    color: #DA291C;
    font-weight: 700;
    border-left: 5px solid #fff;
}

.category-img {
    width: 24px;
    height: 24px;
    object-fit: cover;
    border-radius: 4px;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

/* Product Grid */
.products {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    padding: 25px;
    gap: 25px;
    overflow-y: auto;
    background: #f8f8f8;
}

.item {
    background: #ffffff;
    border: 3px solid #FFCC00;
    padding: 20px;
    text-align: center;
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(218, 41, 28, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 220px;
    position: relative;
}

.item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(218, 41, 28, 0.2);
    border-color: #DA291C;
}

.item img {
    width: 130px;
    height: 130px;
    object-fit: cover;
    border-radius: 10px;
    background: #f0f0f0;
    margin-bottom: 15px;
    border: 2px solid #FFCC00;
}

.item .name {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #333;
    line-height: 1.2;
}

.item .price {
    font-size: 1.3rem;
    font-weight: 700;
    color: #DA291C;
    background: #FFCC00;
    padding: 4px 12px;
    border-radius: 20px;
    margin-top: auto;
}

/* McDonald's Footer */
.footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #DA291C;
    padding: 20px 30px;
    flex-shrink: 0;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    border-top: 4px solid #FFCC00;
}

.cancel {
    background: #666;
    border: none;
    padding: 14px 28px;
    color: white;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.cancel:hover {
    background: #555;
    transform: scale(1.05);
}

.pay {
    background: #FFCC00;
    border: none;
    padding: 14px 28px;
    color: #DA291C;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.pay:hover {
    background: #ffd633;
    transform: scale(1.05);
}

.total {
    font-size: 1.6rem;
    font-weight: 700;
    color: #FFCC00;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
}

/* McDonald's Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(218, 41, 28, 0.9);
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    max-height: 80%;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    border: 5px solid #FFCC00;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 3px solid #DA291C;
    padding-bottom: 15px;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.6rem;
    font-weight: 700;
    color: #DA291C;
}

.close {
    cursor: pointer;
    font-size: 28px;
    color: #DA291C;
    transition: color 0.3s ease;
    font-weight: bold;
}

.close:hover {
    color: #FFCC00;
}

.modal-body table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.modal-body th {
    background: #DA291C;
    color: white;
    padding: 15px;
    text-align: left;
    border: none;
}

.modal-body td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.modal-body input[type="number"] {
    width: 60px;
    padding: 8px;
    border: 2px solid #FFCC00;
    border-radius: 6px;
    text-align: center;
}

.modal-body button {
    background: #DA291C;
    border: none;
    padding: 8px 16px;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.3s ease;
}

.modal-body button:hover {
    background: #b82217;
}

.modal-total {
    text-align: right;
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #DA291C;
    padding-top: 15px;
    border-top: 2px solid #FFCC00;
}

.submit-btn {
    width: 100%;
    padding: 16px;
    background: #FFCC00;
    border: none;
    color: #DA291C;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.3s ease;
    font-size: 1.1rem;
}

.submit-btn:hover {
    background: #ffd633;
}

/* Loading state */
.loading {
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.2rem;
    color: #DA291C;
}

.success-message {
    display: none;
    background: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
    border: 2px solid #c3e6cb;
}

/* Accessibility */
.sidebar li:focus,
.item:focus,
.cancel:focus,
.pay:focus,
.logout-btn:focus,
.close:focus,
.submit-btn:focus {
    outline: 3px solid #FFCC00;
    outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .sidebar {
        width: 220px;
    }
    
    .products {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        padding: 15px;
        gap: 15px;
    }
    
    .banner h2 {
        font-size: 1.5rem;
    }
    
    .footer {
        padding: 15px 20px;
        flex-direction: column;
        gap: 15px;
    }
    
    .total {
        order: -1;
    }
    
    .item {
        height: 200px;
        padding: 15px;
    }
    
    .item img {
        width: 100px;
        height: 100px;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 180px;
    }
    
    .products {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        padding: 10px;
        gap: 10px;
    }
    
    .item {
        height: 180px;
        padding: 10px;
    }
    
    .item img {
        width: 90px;
        height: 90px;
    }
    
    .banner {
        padding: 15px 20px;
    }
    
    .footer {
        padding: 12px 15px;
    }
}
</style>
</head>

<body>
    <div class="kiosk">
        <!-- Top Banner -->
        <div class="banner">
            <button class="logout-btn" onclick="logout()" title="Logout" aria-label="Logout">×</button>
            <h2>Self Service Ordering </h2>
            <p>Select your favorite drinks and proceed to checkout</p>
        </div>

        <!-- Menu Section -->
        <div class="main">
            <!-- Left Sidebar Menu -->
            <div class="sidebar">
                <ul>
                    <li class="active" data-category="BUNDLES" onclick="filterCategory('BUNDLES')" tabindex="0">Bundles
                    </li>
                    <?php foreach ($categories as $c): ?>
                    <li data-category="<?= (int)$c['id'] ?>" onclick="filterCategory('<?= (int)$c['id'] ?>')"
                        tabindex="0">
                        <?php if (!empty($c['img'])): ?>
                        <img src="<?= htmlspecialchars($c['img']) ?>" class="category-img"
                            alt="<?= htmlspecialchars($c['name'] ?? '') ?>">
                        <?php endif; ?>
                        <?= htmlspecialchars($c['name'] ?? '') ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Product Grid -->
            <div class="products" id="productsContainer">
                <?php foreach ($items as $p): ?>
                <div class="item" data-category-id="<?= (int)($p['category_id'] ?? 0) ?>"
                    data-is-bundle="<?= (int)$p['is_bundle'] ?>"
                    onclick="addToCart(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['name'] ?? '', ENT_QUOTES) ?>', <?= (float)$p['price'] ?>, '<?= htmlspecialchars($p['category_name'] ?? '', ENT_QUOTES) ?>')"
                    tabindex="0">
                    <?php if (!empty($p['image_path']) && file_exists($p['image_path'])): ?>
                    <img src="<?= htmlspecialchars($p['image_path']) ?>"
                        alt="<?= htmlspecialchars($p['name'] ?? '') ?>">
                    <?php else: ?>
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4="
                        alt="No Image">
                    <?php endif; ?>
                    <div class="name"><?= htmlspecialchars($p['name'] ?? '') ?></div>
                    <div class="price">₱<?= number_format((float)$p['price'], 2) ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (!$items): ?>
                <div class="item" style="grid-column: span 2;">
                    <p>No drinks available at the moment.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Order Bar -->
        <div class="footer">
            <button class="cancel" onclick="clearCart()" aria-label="Cancel Order">Cancel Order</button>
            <button class="pay" onclick="openCartModal()" aria-label="Review and Pay">Review & Pay</button>
            <div class="total">Total: ₱<span id="cartTotalAmount">0.00</span></div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div id="cartModal" class="modal" role="dialog" aria-labelledby="cartModalTitle" aria-hidden="true">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="cartModalTitle">Your Order</h3>
                <span class="close" onclick="closeCartModal()" aria-label="Close Modal">&times;</span>
            </div>
            <div class="modal-body">
                <table>
                    <thead>
                        <tr>
                            <th>Drink</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="cartModalBody">
                        <!-- Filled by JS -->
                    </tbody>
                </table>
                <div class="modal-total">
                    <strong>Total: ₱<span id="cartModalTotal">0.00</span></strong>
                </div>
            </div>
            <button class="submit-btn" id="submitBtn" onclick="submitOrder()" aria-label="Submit Order">Submit
                Order</button>
            <div class="success-message" id="successMessage"></div>
            <div class="loading" id="loadingIndicator">Processing...</div>
        </div>
    </div>

    <script>
    let currentCategoryFilter = 'BUNDLES';
    let cart = {}; // id -> {id, name, price, qty, category}

    function filterCategory(catId) {
        currentCategoryFilter = catId;
        document.querySelectorAll('.sidebar li').forEach(li => {
            li.classList.toggle('active', li.dataset.category === catId);
        });

        document.querySelectorAll('#productsContainer .item').forEach(item => {
            const itemCat = item.dataset.categoryId || '0';
            const isBundle = item.dataset.isBundle === '1';
            if (catId === 'BUNDLES') {
                item.style.display = isBundle ? 'block' : 'none';
            } else {
                item.style.display = (catId === itemCat) ? 'block' : 'none';
            }
        });
    }

    function addToCart(id, name, price, category) {
        id = String(id);
        if (!cart[id]) {
            cart[id] = {
                id,
                name,
                price: parseFloat(price),
                qty: 0,
                category
            };
        }
        cart[id].qty++;
        updateCartUI();
    }

    function updateCartUI() {
        let totalAmount = 0;
        Object.values(cart).forEach(item => {
            totalAmount += item.qty * item.price;
        });
        document.getElementById('cartTotalAmount').textContent = totalAmount.toFixed(2);
    }

    function clearCart() {
        if (confirm('Are you sure you want to clear the cart?')) {
            cart = {};
            updateCartUI();
            closeCartModal();
        }
    }

    function openCartModal() {
        renderCartModal();
        document.getElementById('cartModal').style.display = 'flex';
        document.getElementById('successMessage').style.display = 'none'; // Hide success message when opening
        document.getElementById('submitBtn').disabled = false;
        document.getElementById('submitBtn').textContent = 'Submit Order';
    }

    function closeCartModal() {
        document.getElementById('cartModal').style.display = 'none';
    }

    function renderCartModal() {
        const tbody = document.getElementById('cartModalBody');
        tbody.innerHTML = '';
        let totalAmount = 0;

        const items = Object.values(cart).filter(i => i.qty > 0);
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="5">Your cart is empty.</td></tr>';
        } else {
            items.forEach(item => {
                const sub = item.qty * item.price;
                totalAmount += sub;
                tbody.innerHTML += `
                    <tr>
                        <td>${escapeHtml(item.name)}</td>
                        <td><input type="number" min="1" value="${item.qty}" onchange="changeCartQty('${item.id}', this.value)"></td>
                        <td>₱${item.price.toFixed(2)}</td>
                        <td>₱${sub.toFixed(2)}</td>
                        <td><button onclick="removeFromCart('${item.id}')">Remove</button></td>
                    </tr>
                `;
            });
        }
        document.getElementById('cartModalTotal').textContent = totalAmount.toFixed(2);
    }

    function changeCartQty(id, value) {
        id = String(id);
        let qty = parseInt(value, 10);
        if (isNaN(qty) || qty <= 0) {
            delete cart[id];
        } else {
            cart[id].qty = qty;
        }
        updateCartUI();
        renderCartModal();
    }

    function removeFromCart(id) {
        delete cart[id];
        updateCartUI();
        renderCartModal();
    }

    function submitOrder() {
        const items = Object.values(cart).filter(i => i.qty > 0);
        if (!items.length) return;

        document.getElementById('loadingIndicator').style.display = 'block';
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').textContent = 'Submitted';

        const payload = items.map(i => ({
            id: i.id,
            qty: i.qty,
            price: i.price
        }));
        const fd = new FormData();
        fd.append('items', JSON.stringify(payload));

        fetch('api_create_order.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                document.getElementById('loadingIndicator').style.display = 'none';
                if (!res.success) {
                    alert(res.error || 'Error creating order.');
                    document.getElementById('submitBtn').disabled = false;
                    document.getElementById('submitBtn').textContent = 'Submit Order';
                    return;
                }
                const displayNum = res.order_number ?? res.order_id;
                document.getElementById('successMessage').innerHTML =
                    `<p>Order submitted successfully! Your order number is: <strong>${displayNum}</strong></p>`;
                document.getElementById('successMessage').style.display = 'block';
                cart = {};
                updateCartUI();
                // Keep modal open to show the order number
            })
            .catch(() => {
                document.getElementById('loadingIndicator').style.display = 'none';
                alert('Network error.');
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('submitBtn').textContent = 'Submit Order';
            });
    }

    function logout() {
        // Assuming logout.php handles session destruction and redirect
        window.location.href = 'logout.php';
    }

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.addEventListener('DOMContentLoaded', () => {
        filterCategory('BUNDLES'); // Start with bundles
    });
    </script>
</body>

</html>