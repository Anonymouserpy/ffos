<?php
require_once 'auth_terminal.php';

if ($_SESSION['terminal_type'] !== 'KITCHEN') {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// Fetch today's kitchen-relevant orders: IN_PROCESS + READY_FOR_CLAIM
$ordersStmt = $pdo->prepare("
    SELECT id, display_number, total_amount, status, created_at, updated_at, paid_at
    FROM orders
    WHERE DATE(created_at) = CURDATE()
      AND status IN ('IN_PROCESS','READY_FOR_CLAIM')
    ORDER BY paid_at ASC, updated_at ASC, id ASC
");
$ordersStmt->execute();
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$orderIds = array_column($orders, 'id');
$itemsByOrder = [];

if (!empty($orderIds)) {
    $inPlaceholders = implode(',', array_fill(0, count($orderIds), '?'));
    $itemsStmt = $pdo->prepare("
        SELECT oi.order_id,
               oi.quantity,
               m.name
        FROM order_items oi
        JOIN menu_items m ON m.id = oi.menu_item_id
        WHERE oi.order_id IN ($inPlaceholders)
        ORDER BY oi.order_id ASC, oi.id ASC
    ");
    $itemsStmt->execute($orderIds);
    while ($row = $itemsStmt->fetch(PDO::FETCH_ASSOC)) {
        $oid = (int)$row['order_id'];
        if (!isset($itemsByOrder[$oid])) {
            $itemsByOrder[$oid] = [];
        }
        $itemsByOrder[$oid][] = [
            'name' => $row['name'],
            'qty'  => (int)$row['quantity'],
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kitchen Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* McDonald's Colors */
    :root {
        --mcd-red: #DA291C;
        --mcd-yellow: #FFCC00;
        --mcd-light-red: #ff5a4d;
        --mcd-light-yellow: #ffde59;
        --mcd-dark-red: #b82217;
        --mcd-light-bg: #fff9e6;
        --mcd-green: #28a745;
    }

    body {
        font-size: 0.9rem;
        background-color: #f8f9fa;
        font-family: 'Roboto', Arial, sans-serif;
    }

    /* Navbar - McDonald's Style */
    .navbar.bg-success {
        background: linear-gradient(135deg, var(--mcd-red) 0%, var(--mcd-dark-red) 100%) !important;
        border-bottom: 4px solid var(--mcd-yellow);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: white !important;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    }

    .navbar-text {
        color: var(--mcd-yellow) !important;
        font-weight: 500;
    }

    .btn-outline-light {
        border-color: var(--mcd-yellow) !important;
        color: var(--mcd-yellow) !important;
    }

    .btn-outline-light:hover {
        background-color: var(--mcd-yellow) !important;
        color: var(--mcd-red) !important;
        border-color: var(--mcd-yellow) !important;
    }

    /* Main Card */
    .card {
        border: 3px solid var(--mcd-yellow);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(218, 41, 28, 0.15);
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(to right, var(--mcd-red), var(--mcd-light-red)) !important;
        color: white !important;
        border-bottom: 3px solid var(--mcd-yellow) !important;
        font-weight: 600;
        padding: 12px 15px !important;
    }

    .card-header .text-muted {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    /* Status Filter Buttons */
    .btn-group .btn-outline-secondary {
        border-color: #ccc;
        color: #666;
        background: white;
        transition: all 0.2s;
    }

    .btn-group .btn-outline-secondary.active {
        background-color: var(--mcd-yellow) !important;
        color: var(--mcd-red) !important;
        border-color: var(--mcd-yellow) !important;
        font-weight: 600;
    }

    .btn-group .btn-outline-secondary:hover:not(.active) {
        background-color: var(--mcd-light-yellow);
        color: var(--mcd-red);
        border-color: var(--mcd-yellow);
    }

    /* Table Styling */
    .table-scroll {
        max-height: 480px;
        overflow-y: auto;
        overflow-x: hidden;
        border: 2px solid #eee;
        border-radius: 8px;
    }
    
    .table-scroll table {
        margin-bottom: 0;
    }
    
    .table-scroll thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background-color: var(--mcd-red) !important;
        color: white !important;
        border: none !important;
        font-weight: 600;
        padding: 12px 8px !important;
    }

    .table-sm td, .table-sm th {
        padding: 10px 8px !important;
    }

    .table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .table tbody tr {
        border-bottom: 1px solid #eee;
    }

    .table tbody tr:hover {
        background-color: var(--mcd-light-bg) !important;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    /* Order Status Colors */
    .table td:nth-child(4) { /* Status column */
        font-weight: 600;
    }

    .table td:nth-child(4):contains('IN_PROCESS') {
        color: #ff9800;
        background-color: rgba(255, 152, 0, 0.1);
        border-radius: 4px;
        padding: 4px 8px !important;
    }

    .table td:nth-child(4):contains('READY_FOR_CLAIM') {
        color: #28a745;
        background-color: rgba(40, 167, 69, 0.1);
        border-radius: 4px;
        padding: 4px 8px !important;
    }

    /* Order Number Styling */
    .table td:first-child {
        font-weight: 700;
        color: var(--mcd-red);
        font-size: 1.1rem;
    }

    /* Items List */
    .items-list div {
        line-height: 1.4;
        margin-bottom: 3px;
        padding: 4px 6px;
        border-left: 3px solid var(--mcd-yellow);
        background-color: rgba(255, 204, 0, 0.05);
        border-radius: 3px;
    }

    .items-list .fw-semibold {
        color: var(--mcd-red);
        font-weight: 700;
    }

    /* Action Buttons */
    .btn-success {
        background: linear-gradient(to bottom, #28a745, #218838) !important;
        border-color: #28a745 !important;
        color: white !important;
        font-weight: 600;
        transition: all 0.2s;
        border-radius: 6px;
        padding: 6px 15px !important;
    }

    .btn-success:hover {
        background: linear-gradient(to bottom, #218838, #1e7e34) !important;
        border-color: #1e7e34 !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .btn-success:active {
        transform: translateY(0);
    }

    /* Empty State */
    .text-center.text-muted {
        color: #666 !important;
        padding: 40px !important;
        font-size: 1rem;
    }

    /* Paid At Column */
    .table td:nth-child(5) {
        color: #555;
        font-weight: 500;
    }

    /* Scrollbar Styling */
    .table-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .table-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-scroll::-webkit-scrollbar-thumb {
        background: var(--mcd-red);
        border-radius: 4px;
    }

    .table-scroll::-webkit-scrollbar-thumb:hover {
        background: var(--mcd-dark-red);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .navbar-brand {
            font-size: 1.2rem;
        }
        
        .table-scroll {
            max-height: 400px;
        }
        
        .btn-group {
            flex-wrap: wrap;
        }
        
        .btn-group .btn {
            margin-bottom: 5px;
        }
        
        .card-header {
            padding: 10px !important;
        }
        
        .table td, .table th {
            padding: 8px 6px !important;
            font-size: 0.85rem;
        }
        
        .btn-success {
            padding: 5px 12px !important;
            font-size: 0.85rem;
        }
    }

    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .card {
            border-width: 2px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
    }
</style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-3">
    <div class="container-fluid">
        <span class="navbar-brand">Kitchen Dashboard</span>
        <div class="ms-auto d-flex align-items-center">
            <span class="navbar-text text-white me-3">
                <?= htmlspecialchars($_SESSION['employee_name']) ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid mb-4">
    <div class="card shadow-sm">
        <div class="card-header py-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div class="mb-2 mb-md-0">
                    <strong>Today's Orders Queue</strong>
                    <span class="text-muted small ms-2">(paid_at ascending)</span>
                </div>
                <div></div>
            </div>
            <div class="mt-2">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary status-filter-btn active"
                            data-status="ALL" onclick="setStatusFilter('ALL')">
                        ALL
                    </button>
                    <button type="button" class="btn btn-outline-secondary status-filter-btn"
                            data-status="IN_PROCESS" onclick="setStatusFilter('IN_PROCESS')">
                        IN_PROCESS
                    </button>
                    <button type="button" class="btn btn-outline-secondary status-filter-btn"
                            data-status="READY_FOR_CLAIM" onclick="setStatusFilter('READY_FOR_CLAIM')">
                        READY_FOR_CLAIM
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-scroll">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width:90px;">Order #</th>
                        <th>Items</th>
                        <th class="text-end" style="width:100px;">Total</th>
                        <th style="width:130px;">Status</th>
                        <th style="width:150px;">Paid At</th>
                        <th style="width:140px;" class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody id="ordersTbody">
                    <?php if ($orders): ?>
                        <?php foreach ($orders as $o):
                            $id = (int)$o['id'];
                            $dispNumber = $o['display_number'] !== null
                                ? str_pad((int)$o['display_number'], 4, '0', STR_PAD_LEFT)
                                : str_pad($id, 4, '0', STR_PAD_LEFT);
                            $status = strtoupper(trim($o['status'] ?? 'IN_PROCESS'));
                            $paidAt = $o['paid_at'] ? date('Y-m-d H:i', strtotime($o['paid_at'])) : '';
                            $items  = $itemsByOrder[$id] ?? [];
                            $showButton = ($status === 'IN_PROCESS');
                        ?>
                        <tr data-order-id="<?= $id ?>"
                            data-status="<?= htmlspecialchars($status) ?>">
                            <td class="fw-bold"><?= htmlspecialchars($dispNumber) ?></td>
                            <td>
                                <div class="items-list">
                                    <?php if ($items): ?>
                                        <?php foreach ($items as $it): ?>
                                            <div>
                                                <span class="fw-semibold"><?= (int)$it['qty'] ?>×</span>
                                                <?= htmlspecialchars($it['name'] ?? '') ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">No items</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end">₱<?= number_format((float)$o['total_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($status) ?></td>
                            <td><?= $paidAt ? htmlspecialchars($paidAt) : '<span class="text-muted">-</span>' ?></td>
                            <td class="text-end">
                                <?php if ($showButton): ?>
                                    <button class="btn btn-sm btn-success"
                                            onclick="markReadyForClaim(<?= $id ?>, '<?= $dispNumber ?>')">
                                        Ready for Claim
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                No orders in queue for today.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let currentStatusFilter = 'ALL';
let ws = null;

document.addEventListener('DOMContentLoaded', () => {
    initWebSocket();
    applyStatusFilter();
});

function setStatusFilter(status) {
    currentStatusFilter = status;
    document.querySelectorAll('.status-filter-btn').forEach(btn => {
        if (btn.dataset.status === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyStatusFilter();
}

function applyStatusFilter() {
    document.querySelectorAll('#ordersTbody tr[data-order-id]').forEach(tr => {
        const status = (tr.dataset.status || '').toUpperCase();
        let visible = true;

        if (currentStatusFilter !== 'ALL' && status !== currentStatusFilter) {
            visible = false;
        }

        if (visible) tr.classList.remove('d-none');
        else tr.classList.add('d-none');
    });
}

// WebSocket: listen for order_created / order_updated and reload
function initWebSocket() {
    try {
        const loc = window.location;
        const wsUrl = (loc.protocol === 'https:' ? 'wss://' : 'ws://') + loc.hostname + ':8080';
        ws = new WebSocket(wsUrl);

        ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                if (data.type === 'order_created' || data.type === 'order_updated') {
                    reloadOrders();
                }
            } catch (e) {
                console.error('Bad WS message', e);
            }
        };
        ws.onclose = () => {
            setTimeout(initWebSocket, 3000);
        };
    } catch (e) {
        console.error('WS init error', e);
    }
}

// Reload kitchen orders via AJAX
function reloadOrders() {
    fetch('api_get_kitchen_orders_today.php')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const tbody = document.getElementById('ordersTbody');
            tbody.innerHTML = '';
            const orders = res.orders || [];

            if (!orders.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            No orders in queue for today.
                        </td>
                    </tr>`;
                return;
            }

            orders.forEach(o => {
                const status = (o.status || 'IN_PROCESS').toUpperCase();
                const dispNumber = o.display_number_str;
                const paidAt = o.paid_at ? o.paid_at : '<span class="text-muted">-</span>';
                const showButton = (status === 'IN_PROCESS');

                const tr = document.createElement('tr');
                tr.dataset.orderId = o.id;
                tr.dataset.status = status;

                let itemsHtml = '';
                if (o.items && o.items.length) {
                    o.items.forEach(it => {
                        itemsHtml += `
                            <div>
                                <span class="fw-semibold">${it.qty}×</span>
                                ${escapeHtml(it.name)}
                            </div>
                        `;
                    });
                } else {
                    itemsHtml = `<span class="text-muted small">No items</span>`;
                }

                tr.innerHTML = `
                    <td class="fw-bold">${dispNumber}</td>
                    <td><div class="items-list">${itemsHtml}</div></td>
                    <td class="text-end">₱${parseFloat(o.total_amount).toFixed(2)}</td>
                    <td>${status}</td>
                    <td>${paidAt}</td>
                    <td class="text-end">
                        ${showButton
                            ? `<button class="btn btn-sm btn-success"
                                        onclick="markReadyForClaim(${o.id}, '${dispNumber}')">
                                    Ready for Claim
                               </button>`
                            : ''}
                    </td>
                `;
                tbody.appendChild(tr);
            });

            applyStatusFilter();
        })
        .catch(err => console.error('reloadOrders error', err));
}

// Mark order as READY_FOR_CLAIM
function markReadyForClaim(orderId, displayNumber) {
    if (!confirm('Mark order #' + displayNumber + ' as READY_FOR_CLAIM?')) return;

    const fd = new FormData();
    fd.append('order_id', orderId);

    fetch('api_kitchen_ready_for_claim.php', {
        method: 'POST',
        body: fd
    })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                alert(res.error || 'Error updating order status.');
                return;
            }
            reloadOrders();
        })
        .catch(() => {
            alert('Network error updating order status.');
        });
}

// Simple HTML escape for injected strings
function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
</body>
</html>
