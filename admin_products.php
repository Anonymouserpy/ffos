<!DOCTYPE html>
<html>

<head>
    <title>Products Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* Simple McDonald's Style */
    :root {
        --mcd-red: #DA291C;
        --mcd-yellow: #FFCC00;
    }

    /* Navbar */
    .navbar.bg-dark {
        background-color: var(--mcd-red) !important;
        border-bottom: 4px solid var(--mcd-yellow);
    }

    .navbar-brand {
        font-weight: bold;
        color: white !important;
    }

    /* Cards */
    .card {
        border: 2px solid var(--mcd-yellow);
        border-radius: 8px;
    }

    .card-header {
        background-color: var(--mcd-red);
        color: white;
        font-weight: bold;
        border-bottom: 2px solid var(--mcd-yellow);
    }

    /* Tables */
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Buttons */
    .btn-danger {
        background-color: var(--mcd-red);
        border-color: var(--mcd-red);
    }

    .btn-primary {
        background-color: #0066CC;
        border-color: #0066CC;
    }

    .btn-warning {
        background-color: var(--mcd-yellow);
        border-color: var(--mcd-yellow);
        color: #333;
    }

    .btn-outline-primary {
        border-color: var(--mcd-red);
        color: var(--mcd-red);
    }

    .btn-outline-primary:hover {
        background-color: var(--mcd-red);
        color: white;
    }

    /* Badges */
    .badge.bg-primary {
        background-color: #0066CC !important;
    }

    .badge.bg-success {
        background-color: #28a745 !important;
    }

    .badge.bg-warning {
        background-color: var(--mcd-yellow) !important;
        color: #333;
    }

    /* Modal */
    .modal-header {
        background-color: var(--mcd-red);
        color: white;
        border-bottom: 2px solid var(--mcd-yellow);
    }

    /* Image hover */
    img[style*="cursor:pointer"]:hover {
        opacity: 0.8;
    }

    /* Form focus */
    .form-control:focus,
    .form-select:focus {
        border-color: var(--mcd-yellow);
        box-shadow: 0 0 0 0.2rem rgba(255, 204, 0, 0.25);
    }
    </style>
</head>
<?php
require_once 'config.php';
if (empty($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

// --- Helpers ---
function handle_image_upload(string $fieldName): ?string
{
    if (empty($_FILES[$fieldName]['name'])) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        return null;
    }

    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        @mkdir($uploadsDir, 0777, true);
    }

    $newName = uniqid('prod_', true) . '.' . $ext;
    $target = $uploadsDir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return null;
    }

    return 'uploads/' . $newName;
}

// --- Handle POST actions (create/edit for category/product/bundle) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Create category
    if ($action === 'create_category') {
        $name = trim($_POST['category_name'] ?? '');
        $imagePath = handle_image_upload('category_image');  // Handle image upload
        if ($name !== '') {
            $stmt = $pdo->prepare("INSERT INTO product_categories (name, img) VALUES (?, ?)");
            $stmt->execute([$name, $imagePath]);
        }
    }

    // Edit category
    if ($action === 'edit_category') {
        $id   = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');
        $imagePath = handle_image_upload('category_image');  // Handle image upload
        if ($id > 0 && $name !== '') {
            if ($imagePath !== null) {
                $stmt = $pdo->prepare("UPDATE product_categories SET name = ?, img = ? WHERE id = ?");
                $stmt->execute([$name, $imagePath, $id]);
            } else {
                // If no new image, update only the name
                $stmt = $pdo->prepare("UPDATE product_categories SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
            }
            echo "<script>alert('Category updated successfully!');</script>";
        } else {
            echo "<script>alert('Error: Invalid category data!');</script>";
        }
    }


    // Create product
    if ($action === 'create_product') {
        $name = trim($_POST['product_name'] ?? '');
        $code = trim($_POST['product_code'] ?? '');
        $price = (float)($_POST['product_price'] ?? 0);
        $categoryId = (int)($_POST['product_category_id'] ?? 0);
        $isActive = isset($_POST['product_is_active']) ? 1 : 0;
        if ($name !== '' && $code !== '' && $price > 0 && $categoryId > 0) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM menu_items WHERE code = ?");
            $check->execute([$code]);
            $exists = $check->fetchColumn();
            if ($exists > 0) {
                echo "<script>alert('Error: Product code already exists!');</script>";
            } else {
                $imagePath = handle_image_upload('product_image');
                $stmt = $pdo->prepare(
                    "INSERT INTO menu_items (code, category_id, is_bundle, name, price, image_path, is_active)
                     VALUES (?, ?, 0, ?, ?, ?, ?)"
                );
                $stmt->execute([$code, $categoryId, $name, $price, $imagePath, $isActive]);
                echo "<script>alert('Product created successfully!');</script>";
            }
        }
    }

    // Edit product
    if ($action === 'edit_product') {
        $id = (int)($_POST['product_id'] ?? 0);
        $name = trim($_POST['product_name'] ?? '');
        $code = trim($_POST['product_code'] ?? '');
        $price = (float)($_POST['product_price'] ?? 0);
        $categoryId = (int)($_POST['product_category_id'] ?? 0);
        $existing = $_POST['existing_product_image'] ?? null;
        $isActive = isset($_POST['product_is_active']) ? 1 : 0;
        if ($id > 0 && $name !== '' && $code !== '' && $price > 0 && $categoryId > 0) {
            $imagePath = handle_image_upload('product_image');
            if ($imagePath === null) {
                $imagePath = $existing;
            }
            $stmt = $pdo->prepare(
                "UPDATE menu_items
                 SET code = ?, category_id = ?, name = ?, price = ?, image_path = ?, is_active = ?
                 WHERE id = ? AND is_bundle = 0"
            );
            $stmt->execute([$code, $categoryId, $name, $price, $imagePath, $isActive, $id]);
        }
    }

    // Create bundle (no category; standalone)
    if ($action === 'create_bundle') {
        $bundleName = trim($_POST['bundle_name'] ?? '');
        $bundleCode = trim($_POST['bundle_code'] ?? '');
        $selectedProd = $_POST['bundle_items'] ?? [];
        $quantities = $_POST['bundle_qty'] ?? [];
        $isActive = isset($_POST['bundle_is_active']) ? 1 : 0;
        if ($bundleName !== '' && $bundleCode !== '' && !empty($selectedProd)) {
            $ids = array_map('intval', $selectedProd);
            $in = implode(',', $ids);
            $stmt = $pdo->query("SELECT id, price FROM menu_items WHERE id IN ($in) AND is_bundle = 0");
            $prices = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $prices[$row['id']] = (float)$row['price'];
            }
            $total = 0;
            $bundleComponents = [];
            foreach ($ids as $pid) {
                $qty = max(1, (int)($quantities[$pid] ?? 1));
                if (!isset($prices[$pid])) continue;
                $total += $prices[$pid] * $qty;
                $bundleComponents[] = ['id' => $pid, 'qty' => $qty];
            }
            if ($total > 0 && !empty($bundleComponents)) {
                $imagePath = handle_image_upload('bundle_image');
                $stmt = $pdo->prepare(
                    "INSERT INTO menu_items (code, category_id, is_bundle, name, price, image_path, is_active)
                     VALUES (?, NULL, 1, ?, ?, ?, ?)"
                );
                $stmt->execute([$bundleCode, $bundleName, $total, $imagePath, $isActive]);
                $bundleMenuId = (int)$pdo->lastInsertId();
                $stmtItem = $pdo->prepare(
                    "INSERT INTO bundle_items (bundle_menu_item_id, menu_item_id, quantity)
                     VALUES (?, ?, ?)"
                );
                foreach ($bundleComponents as $comp) {
                    $stmtItem->execute([$bundleMenuId, $comp['id'], $comp['qty']]);
                }
            }
        }
    }

    // Edit bundle (including composition)
    if ($action === 'edit_bundle') {
        $bundleId = (int)($_POST['bundle_id'] ?? 0);
        $bundleName = trim($_POST['bundle_name'] ?? '');
        $bundleCode = trim($_POST['bundle_code'] ?? '');
        $existingImg = $_POST['existing_bundle_image'] ?? null;
        $selectedProd = $_POST['bundle_items'] ?? [];
        $quantities = $_POST['bundle_qty'] ?? [];
        $isActive = isset($_POST['bundle_is_active']) ? 1 : 0;
        if ($bundleId > 0 && $bundleName !== '' && $bundleCode !== '' && !empty($selectedProd)) {
            $ids = array_map('intval', $selectedProd);
            $in = implode(',', $ids);
            $stmt = $pdo->query("SELECT id, price FROM menu_items WHERE id IN ($in) AND is_bundle = 0");
            $prices = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $prices[$row['id']] = (float)$row['price'];
            }
            $total = 0;
            $bundleComponents = [];
            foreach ($ids as $pid) {
                $qty = max(1, (int)($quantities[$pid] ?? 1));
                if (!isset($prices[$pid])) continue;
                $total += $prices[$pid] * $qty;
                $bundleComponents[] = ['id' => $pid, 'qty' => $qty];
            }
            if ($total > 0 && !empty($bundleComponents)) {
                $imagePath = handle_image_upload('bundle_image');
                if ($imagePath === null) {
                    $imagePath = $existingImg;
                }
                $stmt = $pdo->prepare(
                    "UPDATE menu_items
                     SET code = ?, name = ?, price = ?, image_path = ?, is_active = ?
                     WHERE id = ? AND is_bundle = 1"
                );
                $stmt->execute([$bundleCode, $bundleName, $total, $imagePath, $isActive, $bundleId]);
                $stmtDel = $pdo->prepare("DELETE FROM bundle_items WHERE bundle_menu_item_id = ?");
                $stmtDel->execute([$bundleId]);
                $stmtItem = $pdo->prepare(
                    "INSERT INTO bundle_items (bundle_menu_item_id, menu_item_id, quantity)
                     VALUES (?, ?, ?)"
                );
                foreach ($bundleComponents as $comp) {
                    $stmtItem->execute([$bundleId, $comp['id'], $comp['qty']]);
                }
            }
        }
    }

    // After any POST, redirect to avoid form resubmission
    header('Location: admin_products.php');
    exit;
}

// --- Fetch data for UI ---
// Get categories
$categories = $pdo->query("SELECT id, name FROM product_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// --- JOIN: Get all menu_items (products/bundles) with category name for products ---
$products = $pdo->query(
    "SELECT mi.*, pc.name AS category_name
     FROM menu_items mi
     LEFT JOIN product_categories pc ON mi.category_id = pc.id"
)->fetchAll(PDO::FETCH_ASSOC);

// --- SPLIT: Separate lists ---
$singleProducts = array_values(array_filter($products, fn($p) => (int)$p['is_bundle'] === 0));
$bundles = array_values(array_filter($products, fn($p) => (int)$p['is_bundle'] === 1));

// --- Bundle details for "View" modal and Edit prefill ---
$bundleItemsByBundle = [];
$stmtDetails = $pdo->query(
    "SELECT bi.bundle_menu_item_id, bi.menu_item_id, bi.quantity,
            m.name, m.price
     FROM bundle_items bi
     JOIN menu_items m ON m.id = bi.menu_item_id
     ORDER BY bi.bundle_menu_item_id, m.name"
);
while ($row = $stmtDetails->fetch(PDO::FETCH_ASSOC)) {
    $bid = (int)$row['bundle_menu_item_id'];
    $mid = (int)$row['menu_item_id'];
    $qty = (int)$row['quantity'];
    $price = (float)$row['price'];
    $bundleItemsByBundle[$bid][] = [
        'id'       => $mid,
        'name'     => $row['name'],
        'quantity' => $qty,
        'price'    => $price,
        'subtotal' => $qty * $price
    ];
}

// Stats / insights
$stats = [
    'categories' => count($categories),
    'products'   => count($singleProducts),
    'bundles'    => count($bundles)
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>McDonald’s Product Manager</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font-Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Font: official McDonald’s brand font (replicate with “Chivo” for licence safety) -->
    <link href="https://fonts.googleapis.com/css2?family=Chivo:wght@300;400;600;700;900&display=swap" rel="stylesheet">

    <style>
    /* ---------- OFFICIAL PALETTE ---------- */
    :root {
        --mcd-red: #DA291C;
        --mcd-red-dark: #B22222;
        --mcd-yellow: #FFCC00;
        --mcd-yellow-dark: #B39700;
        --mcd-black: #27251F;
        --mcd-white: #FFFFFF;
        --mcd-grey-light: #F5F5F5;
        --mcd-grey: #E5E5E5;
    }

    /* ---------- GLOBAL ---------- */
    body {
        font-family: 'Chivo', 'Segoe UI', sans-serif;
        background: var(--mcd-grey-light);
        color: var(--mcd-black);
        -webkit-font-smoothing: antialiased;
    }

    a {
        text-decoration: none;
    }

    /* ---------- GOLDEN ARCHES NAV ---------- */
    .navbar-mcd {
        background: linear-gradient(135deg, var(--mcd-red) 0%, var(--mcd-red-dark) 100%);
        border-bottom: 4px solid var(--mcd-yellow);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
    }

    .navbar-mcd .navbar-brand {
        font-weight: 900;
        font-size: 1.4rem;
        letter-spacing: .5px;
        color: var(--mcd-white) !important;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .navbar-mcd .navbar-brand i {
        color: var(--mcd-yellow);
        font-size: 1.6rem;
    }

    .navbar-mcd .btn-outline-light {
        border-width: 2px;
        font-weight: 600;
    }

    .navbar-mcd .btn-outline-light:hover {
        background: var(--mcd-yellow);
        border-color: var(--mcd-yellow);
        color: var(--mcd-black);
    }

    /* ---------- CARDS (FAMILIAR ROUNDED CORNERS) ---------- */
    .card-mcd {
        border-radius: 20px;
        border: none;
        overflow: hidden;
        background: var(--mcd-white);
        box-shadow: 0 6px 18px rgba(0, 0, 0, .08);
        transition: transform .25s, box-shadow .25s;
    }

    .card-mcd:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .12);
    }

    .card-header-mcd {
        background: linear-gradient(135deg, var(--mcd-yellow) 0%, var(--mcd-yellow-dark) 100%);
        color: var(--mcd-black);
        font-weight: 700;
        font-size: 1.1rem;
        padding: .9rem 1.3rem;
        border: none;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .card-header-mcd i {
        font-size: 1.2rem;
    }

    /* ---------- TABLES (DRIVE-THRU CLARITY) ---------- */
    .table-mcd thead th {
        background: var(--mcd-grey);
        color: var(--mcd-black);
        font-weight: 600;
        font-size: .8rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        border-bottom: 2px solid var(--mcd-yellow);
        padding: .75rem .5rem;
        vertical-align: middle;
    }

    .table-mcd tbody tr {
        transition: background .2s;
    }

    .table-mcd tbody tr:hover {
        background: rgba(255, 204, 0, .12);
    }

    .table-mcd img {
        border-radius: 50%;
        border: 3px solid var(--mcd-yellow);
        object-fit: cover;
        cursor: pointer;
        transition: transform .2s;
    }

    .table-mcd img:hover {
        transform: scale(1.1);
    }

    /* ---------- BUTTONS (BIG-MAC-SIZED) ---------- */
    .btn-mcd {
        border-radius: 100px;
        font-weight: 700;
        padding: .55rem 1.4rem;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        transition: all .25s;
    }

    .btn-mcd-primary {
        background: linear-gradient(135deg, var(--mcd-yellow) 0%, var(--mcd-yellow-dark) 100%);
        color: var(--mcd-black);
    }

    .btn-mcd-primary:hover {
        background: linear-gradient(135deg, #FFE133 0%, var(--mcd-yellow) 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 204, 0, .35);
    }

    .btn-mcd-danger {
        background: linear-gradient(135deg, var(--mcd-red) 0%, var(--mcd-red-dark) 100%);
        color: var(--mcd-white);
    }

    .btn-mcd-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(218, 41, 28, .35);
    }

    .btn-mcd-outline {
        border: 2px solid var(--mcd-red);
        color: var(--mcd-red);
        background: transparent;
    }

    .btn-mcd-outline:hover {
        background: var(--mcd-red);
        color: var(--mcd-white);
    }

    /* ---------- STATUS BADGES (FAMILIAR COLORS) ---------- */
    .badge-mcd {
        border-radius: 100px;
        padding: .35rem .7rem;
        font-weight: 600;
        font-size: .7rem;
        letter-spacing: .4px;
    }

    .badge-mcd-available {
        background: #34C759;
        color: var(--mcd-white);
    }

    .badge-mcd-unavailable {
        background: #8E8E93;
        color: var(--mcd-white);
    }

    /* ---------- MODALS (GOLDEN ARCH HEADER) ---------- */
    .modal-header-mcd {
        background: linear-gradient(135deg, var(--mcd-yellow) 0%, var(--mcd-yellow-dark) 100%);
        color: var(--mcd-black);
        font-weight: 700;
        border: none;
    }

    .modal-content-mcd {
        border-radius: 20px;
        border: none;
    }

    /* ---------- KPI CARDS (DRIVE-THRU BOARD) ---------- */
    .kpi-card {
        background: var(--mcd-white);
        border-radius: 20px;
        padding: 1.2rem 1rem;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        transition: transform .25s;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
    }

    .kpi-number {
        font-size: 2.25rem;
        font-weight: 900;
        background: linear-gradient(135deg, var(--mcd-red) 0%, var(--mcd-red-dark) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .kpi-label {
        color: var(--mcd-black);
        font-weight: 600;
        font-size: .9rem;
    }

    /* ---------- RESPONSIVE ---------- */
    @media (max-width: 768px) {
        .table-mcd {
            font-size: .8rem;
        }

        .btn-mcd {
            font-size: .8rem;
            padding: .45rem 1rem;
        }
    }

    /* ---------- SCROLL-BAR (FRIES-Y) ---------- */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--mcd-grey-light);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--mcd-yellow-dark);
        border-radius: 4px;
    }
    </style>
</head>

<body>

    <!-- =========================================================== -->
    <!-- 1.  GOLDEN-ARCHES NAVBAR                                  -->
    <!-- =========================================================== -->
    <nav class="navbar navbar-expand-lg navbar-mcd sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="fa-solid fa-arrows-turn-to-dots"></i> <!-- Golden-arches icon -->
                McDonald’s Product Manager
            </span>
            <div class="d-flex gap-2">
                <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <!-- =========================================================== -->
        <!-- 2.  KPI CARDS (DRIVE-THRU BOARD)                          -->
        <!-- =========================================================== -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="kpi-card">
                    <i class="fa-solid fa-folder-open fa-2x text-warning mb-2"></i>
                    <div class="kpi-number"><?= (int)$stats['categories'] ?></div>
                    <div class="kpi-label">Categories</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card">
                    <i class="fa-solid fa-box fa-2x text-success mb-2"></i>
                    <div class="kpi-number"><?= (int)$stats['products'] ?></div>
                    <div class="kpi-label">Products</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card">
                    <i class="fa-solid fa-gift fa-2x text-info mb-2"></i>
                    <div class="kpi-number"><?= (int)$stats['bundles'] ?></div>
                    <div class="kpi-label">Bundles</div>
                </div>
            </div>
        </div>

        <!-- =========================================================== -->
        <!-- 3.  PRODUCTS TABLE                                         -->
        <!-- =========================================================== -->
        <div class="card-mcd mb-4">
            <div class="card-header-mcd justify-content-between">
                <span><i class="fa-solid fa-box"></i> Products</span>
                <button class="btn-mcd btn-mcd-danger btn-sm" onclick="openProductModal('create')">
                    <i class="fa-solid fa-plus"></i> Add Product
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-mcd mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Category</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($singleProducts as $p): ?>
                            <tr
                                ondblclick="openProductModal('edit',
                        <?= (int)$p['id'] ?>,'<?=htmlspecialchars($p['name'],ENT_QUOTES)?>','<?=htmlspecialchars($p['code'],ENT_QUOTES)?>','<?=$p['price']?>',<?=(int)$p['category_id']?>,'<?=htmlspecialchars($p['image_path']??'',ENT_QUOTES)?>',<?=(int)$p['is_active']?>)">
                                <td class="text-center"><?= (int)$p['id'] ?></td>
                                <td>
                                    <?php if (!empty($p['image_path'])): ?>
                                    <img src="<?= htmlspecialchars($p['image_path']) ?>" alt="" width="44" height="44"
                                        onclick="openImageView('<?=htmlspecialchars($p['image_path'],ENT_QUOTES)?>')">
                                    <?php else: ?>
                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center"
                                        style="width:44px;height:44px"><i class="fa-solid fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                                <td><span class="badge bg-dark"><?= htmlspecialchars($p['code']) ?></span></td>
                                <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
                                <td class="text-end text-success fw-semibold">
                                    ₱<?= number_format((float)$p['price'], 2) ?></td>
                                <td class="text-center">
                                    <?php if ((int)$p['is_active'] === 1): ?>
                                    <span class="badge-mcd badge-mcd-available">Available</span>
                                    <?php else: ?>
                                    <span class="badge-mcd badge-mcd-unavailable">Not Available</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn-mcd btn-mcd-outline btn-sm"
                                        onclick="openProductModal('edit',
                                <?= (int)$p['id'] ?>,'<?=htmlspecialchars($p['name'],ENT_QUOTES)?>','<?=htmlspecialchars($p['code'],ENT_QUOTES)?>','<?=$p['price']?>',<?=(int)$p['category_id']?>,'<?=htmlspecialchars($p['image_path']??'',ENT_QUOTES)?>',<?=(int)$p['is_active']?>)">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (!$singleProducts): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">No products yet – add one to begin.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- =========================================================== -->
        <!-- 4.  CATEGORIES  &  BUNDLES                                -->
        <!-- =========================================================== -->
        <div class="row g-4">
            <!-- --- categories --- -->
            <div class="col-md-4">
                <div class="card-mcd h-100">
                    <div class="card-header-mcd justify-content-between">
                        <span><i class="fa-solid fa-folder-open"></i> Categories</span>
                        <button class="btn-mcd btn-primary btn-sm" onclick="openCategoryModal('create')">
                            <i class="fa-solid fa-plus"></i> Add
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-mcd mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $c): ?>
                                    <tr>
                                        <td><?= (int)$c['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                                        <td class="text-end">
                                            <button class="btn-mcd btn-mcd-outline btn-sm"
                                                onclick="openCategoryModal('edit',<?=(int)$c['id']?>,'<?=htmlspecialchars($c['name'],ENT_QUOTES)?>')">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (!$categories): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No categories.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- --- bundles --- -->
            <div class="col-md-8">
                <div class="card-mcd h-100">
                    <div class="card-header-mcd justify-content-between">
                        <span><i class="fa-solid fa-gift"></i> Bundles</span>
                        <button class="btn-mcd btn-success btn-sm" onclick="openBundleModal('create')">
                            <i class="fa-solid fa-plus"></i> Add Bundle
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-mcd mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bundles as $b): ?>
                                    <tr>
                                        <td><?= (int)$b['id'] ?></td>
                                        <td>
                                            <?php if (!empty($b['image_path'])): ?>
                                            <img src="<?= htmlspecialchars($b['image_path']) ?>" alt="" width="44"
                                                height="44"
                                                onclick="openImageView('<?=htmlspecialchars($b['image_path'],ENT_QUOTES)?>')">
                                            <?php else: ?>
                                            <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center"
                                                style="width:44px;height:44px"><i class="fa-solid fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($b['name']) ?></strong></td>
                                        <td><span class="badge bg-dark"><?= htmlspecialchars($b['code']) ?></span></td>
                                        <td class="text-end text-info fw-semibold">
                                            ₱<?= number_format((float)$b['price'], 2) ?></td>
                                        <td class="text-center">
                                            <?php if ((int)$b['is_active'] === 1): ?>
                                            <span class="badge-mcd badge-mcd-available">Available</span>
                                            <?php else: ?>
                                            <span class="badge-mcd badge-mcd-unavailable">Not Available</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn-mcd btn-outline-primary btn-sm me-1"
                                                onclick="openBundleViewModal(<?=(int)$b['id']?>,'<?=htmlspecialchars($b['name'],ENT_QUOTES)?>','<?=htmlspecialchars($b['code'],ENT_QUOTES)?>',<?=(float)$b['price']?>)">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button class="btn-mcd btn-mcd-outline btn-sm"
                                                onclick="openBundleModal('edit',<?=(int)$b['id']?>,'<?=htmlspecialchars($b['name'],ENT_QUOTES)?>','<?=htmlspecialchars($b['code'],ENT_QUOTES)?>','<?=htmlspecialchars($b['image_path']??'',ENT_QUOTES)?>',<?=(int)$b['is_active']?>)">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (!$bundles): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">No bundles – create one.
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- /container -->

    <!-- ===================================================================
     5.  MODALS  (same IDs / JS – McDonald’s skin)
=================================================================== -->
    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-mcd">
                <form method="post" id="categoryForm" enctype="multipart/form-data">
                    <div class="modal-header modal-header-mcd">
                        <h5 class="modal-title" id="categoryModalTitle">Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="categoryAction" value="create_category">
                        <input type="hidden" name="category_id" id="categoryId">
                        <div class="mb-3">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="category_name" id="categoryName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image (optional)</label>
                            <input type="file" name="category_image" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-mcd btn-mcd-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-mcd">
                <form method="post" enctype="multipart/form-data" id="productForm">
                    <div class="modal-header modal-header-mcd">
                        <h5 class="modal-title" id="productModalTitle">Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="productAction" value="create_product">
                        <input type="hidden" name="product_id" id="productId">
                        <input type="hidden" name="existing_product_image" id="productExistingImage">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" name="product_name" id="productName" class="form-control"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="product_code" id="productCode" class="form-control"
                                        required placeholder="BIGMAC">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="product_category_id" id="productCategoryId" class="form-select"
                                        required>
                                        <option value="">-- Select --</option>
                                        <?php foreach ($categories as $c): ?>
                                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" step="0.01" name="product_price" id="productPrice"
                                            class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="product_is_active"
                                            id="productIsActive" value="1" checked>
                                        <label class="form-check-label" for="productIsActive">Available</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Image (optional)</label>
                                <input type="file" name="product_image" class="form-control mb-3">
                                <div class="border border-dashed rounded p-3 text-center">
                                    <div class="text-muted mb-2">Current Image</div>
                                    <img src="" id="productPreviewImg" alt="" class="img-fluid rounded"
                                        style="max-height:140px;display:none;">
                                    <div id="productNoImg" class="text-muted small">No image</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-mcd btn-mcd-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bundle Modal -->
    <div class="modal fade" id="bundleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-mcd">
                <form method="post" enctype="multipart/form-data" id="bundleForm">
                    <div class="modal-header modal-header-mcd">
                        <h5 class="modal-title" id="bundleModalTitle">Bundle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="bundleAction" value="create_bundle">
                        <input type="hidden" name="bundle_id" id="bundleId">
                        <input type="hidden" name="existing_bundle_image" id="bundleExistingImage">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Bundle Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bundle_name" id="bundleName" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Bundle Code <span class="text-danger">*</span></label>
                                    <input type="text" name="bundle_code" id="bundleCode" class="form-control" required
                                        placeholder="BDL_MEAL1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Select Products & Quantities <span
                                            class="text-danger">*</span></label>
                                    <div class="p-2 rounded"
                                        style="max-height:260px;overflow-y:auto;background:rgba(255,204,0,.08);">
                                        <?php foreach ($singleProducts as $p): ?>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="form-check flex-grow-1">
                                                <input class="form-check-input bundle-prod-checkbox" type="checkbox"
                                                    value="<?= (int)$p['id'] ?>" id="bprod_<?= (int)$p['id'] ?>"
                                                    name="bundle_items[]" onchange="updateBundleTotal()">
                                                <label class="form-check-label"
                                                    for="bprod_<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?>
                                                    (₱<?= number_format((float)$p['price'], 2) ?>)</label>
                                            </div>
                                            <input type="number" name="bundle_qty[<?= (int)$p['id'] ?>]"
                                                class="form-control form-control-sm ms-2 bundle-qty-input" value="1"
                                                min="1" style="width:70px;" onchange="updateBundleTotal()">
                                        </div>
                                        <?php endforeach; ?>
                                        <?php if (!$singleProducts): ?>
                                        <div class="text-muted small">No base products yet.</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text">Bundle price = sum of (product price × quantity). Computed
                                        automatically.</div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="bundle_is_active"
                                            id="bundleIsActive" value="1" checked>
                                        <label class="form-check-label" for="bundleIsActive">Available</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bundle Image (optional)</label>
                                <input type="file" name="bundle_image" class="form-control mb-3">
                                <div class="border border-dashed rounded p-3 text-center">
                                    <div class="text-muted mb-2">Current Image</div>
                                    <img src="" id="bundlePreviewImg" alt="" class="img-fluid rounded"
                                        style="max-height:120px;display:none;">
                                    <div id="bundleNoImg" class="text-muted small">No image</div>
                                </div>
                                <label class="form-label">Computed Total <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" id="bundleTotal" readonly value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-mcd btn-success">Save Bundle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bundle View Modal -->
    <div class="modal fade" id="bundleViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-mcd">
                <div class="modal-header modal-header-mcd">
                    <h5 class="modal-title" id="bundleViewTitle">Bundle Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>Code:</strong> <span id="bundleViewCode"></span> | <strong>Total:</strong>
                        ₱<span id="bundleViewTotal"></span></p>
                    <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                        <table class="table table-dark table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="bundleViewBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center">
                    <img src="" id="imageViewImg" alt="" class="img-fluid rounded"
                        style="max-height:80vh;object-fit:contain;">
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================
     6.  JavaScript  (same IDs / functions – only styling upgraded)
=================================================================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    /* === price map for bundle auto-calc ====================================== */
    const bundlePrices = {
        <?php
    $tmp=[];
    foreach($singleProducts as $p) $tmp[]=(int)$p['id'].':'.(float)$p['price'];
    echo implode(',',$tmp);
?>
    };
    const bundleDetails =
        <?= json_encode($bundleItemsByBundle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}' ?>;

    let categoryModal, productModal, bundleModal, bundleViewModal, imageViewModal;
    document.addEventListener('DOMContentLoaded', () => {
        categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        productModal = new bootstrap.Modal(document.getElementById('productModal'));
        bundleModal = new bootstrap.Modal(document.getElementById('bundleModal'));
        bundleViewModal = new bootstrap.Modal(document.getElementById('bundleViewModal'));
        imageViewModal = new bootstrap.Modal(document.getElementById('imageViewModal'));
    });

    /* === image zoom ========================================================== */
    function openImageView(src) {
        document.getElementById('imageViewImg').src = src;
        imageViewModal.show();
    }

    /* === category ============================================================ */
    function openCategoryModal(mode, id = null, name = '') {
        document.getElementById('categoryModalTitle').textContent = mode === 'create' ? 'Add Category' :
        'Edit Category';
        document.getElementById('categoryAction').value = mode === 'create' ? 'create_category' : 'edit_category';
        document.getElementById('categoryId').value = id || '';
        document.getElementById('categoryName').value = name || '';
        categoryModal.show();
    }

    /* === product ============================================================= */
    function openProductModal(mode, id = null, name = '', code = '', price = '', catId = 0, imgPath = '', active = 0) {
        const title = document.getElementById('productModalTitle');
        const action = document.getElementById('productAction');
        /* ... (same original logic) ... */
        title.textContent = mode === 'create' ? 'Add Product' : 'Edit Product';
        action.value = mode === 'create' ? 'create_product' : 'edit_product';
        document.getElementById('productId').value = id || '';
        document.getElementById('productName').value = name || '';
        document.getElementById('productCode').value = code || '';
        document.getElementById('productPrice').value = price || '';
        document.getElementById('productCategoryId').value = catId || '';
        document.getElementById('productExistingImage').value = imgPath || '';
        document.getElementById('productIsActive').checked = parseInt(active) === 1;

        const preview = document.getElementById('productPreviewImg');
        const noImg = document.getElementById('productNoImg');
        if (imgPath) {
            preview.src = imgPath;
            preview.style.display = 'block';
            noImg.style.display = 'none';
        } else {
            preview.style.display = 'none';
            noImg.style.display = 'block';
        }
        productModal.show();
    }

    /* === bundle ============================================================== */
    function updateBundleTotal() {
        let total = 0;
        document.querySelectorAll('.bundle-prod-checkbox').forEach(cb => {
            if (!cb.checked) return;
            const id = parseInt(cb.value);
            const price = bundlePrices[id] || 0;
            const qty = parseInt(document.querySelector(`input[name="bundle_qty[${id}]"]`)?.value || 1);
            total += price * qty;
        });
        document.getElementById('bundleTotal').value = total.toFixed(2);
    }

    function openBundleModal(mode, id = null, name = '', code = '', imgPath = '', active = 1) {
        document.getElementById('bundleModalTitle').textContent = mode === 'create' ? 'Add Bundle' : 'Edit Bundle';
        document.getElementById('bundleAction').value = mode === 'create' ? 'create_bundle' : 'edit_bundle';
        document.getElementById('bundleId').value = id || '';
        document.getElementById('bundleName').value = name || '';
        document.getElementById('bundleCode').value = code || '';
        document.getElementById('bundleExistingImage').value = imgPath || '';
        document.getElementById('bundleIsActive').checked = parseInt(active) === 1;

        /* reset checks / qty */
        document.querySelectorAll('.bundle-prod-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.bundle-qty-input').forEach(q => q.value = 1);
        if (mode === 'edit' && id) {
            const details = bundleDetails[id] || [];
            details.forEach(it => {
                const cb = document.querySelector(`.bundle-prod-checkbox[value="${it.id}"]`);
                if (cb) {
                    cb.checked = true;
                    document.querySelector(`input[name="bundle_qty[${it.id}]"]`).value = it.quantity;
                }
            });
        }
        updateBundleTotal();
        bundleModal.show();
    }

    /* === bundle view ========================================================= */
    function openBundleViewModal(id, name, code, total) {
        document.getElementById('bundleViewTitle').textContent = name;
        document.getElementById('bundleViewCode').textContent = code;
        document.getElementById('bundleViewTotal').textContent = total.toFixed(2);
        const tbody = document.getElementById('bundleViewBody');
        tbody.innerHTML = '';
        const items = bundleDetails[id] || [];
        if (!items.length) tbody.innerHTML =
            '<tr><td colspan="4" class="text-center text-muted py-3">No items</td></tr>';
        else {
            items.forEach(it => {
                tbody.insertAdjacentHTML('beforeend', `
              <tr>
                <td>${it.name}</td>
                <td class="text-center">${it.quantity}</td>
                <td class="text-end">₱${parseFloat(it.price).toFixed(2)}</td>
                <td class="text-end">₱${parseFloat(it.subtotal).toFixed(2)}</td>
              </tr>`);
            });
        }
        bundleViewModal.show();
    }
    </script>
</body>

</html>