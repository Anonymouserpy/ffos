<?php
require_once 'auth_terminal.php';

if ($_SESSION['terminal_type'] !== 'CLAIM') {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// Initial load: today's IN_PROCESS and READY_FOR_CLAIM
$stmt = $pdo->prepare("
    SELECT id, display_number, status, updated_at
    FROM orders
    WHERE DATE(created_at) = CURDATE()
      AND status IN ('IN_PROCESS', 'READY_FOR_CLAIM')
    ORDER BY updated_at ASC, id ASC
");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$inProcess = [];
$readyForClaim = [];

foreach ($rows as $r) {
    $id    = (int)$r['id'];
    $disp  = $r['display_number'] !== null ? (int)$r['display_number'] : $id;
    $dispStr = str_pad($disp, 4, '0', STR_PAD_LEFT);
    $status = strtoupper(trim($r['status'] ?? ''));

    $entry = [
        'id'      => $id,
        'display' => $dispStr,
        'status'  => $status,
    ];

    if ($status === 'READY_FOR_CLAIM') {
        $readyForClaim[] = $entry;
    } else {
        $inProcess[] = $entry;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Claim Display</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* McDonald's Colors */
    :root {
        --mcd-red: #DA291C;
        --mcd-yellow: #FFCC00;
        --mcd-light-yellow: #ffde59;
        --mcd-dark-red: #b82217;
        --mcd-light-bg: #fff9e6;
        --mcd-green: #22c55e;
        --mcd-dark-green: #065f46;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Roboto', system-ui, -apple-system, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        overflow: hidden;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    /* Navbar - McDonald's Style */
    .navbar {
        background: linear-gradient(135deg, var(--mcd-red) 0%, var(--mcd-dark-red) 100%);
        border-bottom: 4px solid var(--mcd-yellow);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        padding: 12px 0;
    }

    .screen-title {
        font-size: 1.8rem;
        font-weight: 800;
        color: white !important;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        letter-spacing: 0.05em;
    }

    .navbar-text {
        color: var(--mcd-yellow) !important;
        font-weight: 500;
        font-size: 1.1rem;
    }

    .btn-outline-light {
        border-color: var(--mcd-yellow) !important;
        color: var(--mcd-yellow) !important;
        font-weight: 600;
    }

    .btn-outline-light:hover {
        background-color: var(--mcd-yellow) !important;
        color: var(--mcd-red) !important;
        border-color: var(--mcd-yellow) !important;
    }

    /* Main Wrapper */
    .main-wrapper {
        padding: 1.5rem 1.5rem 2rem;
        height: calc(100vh - 80px);
        overflow: hidden;
    }

    /* Panels */
    .panel {
        background: white;
        border-radius: 15px;
        border: 4px solid var(--mcd-yellow);
        box-shadow: 0 10px 25px rgba(218, 41, 28, 0.15);
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .panel-header {
        padding: 1rem 1.5rem;
        background: linear-gradient(to right, var(--mcd-red), var(--mcd-dark-red));
        color: white !important;
        border-bottom: 3px solid var(--mcd-yellow);
    }

    .panel-title {
        font-size: 1.4rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: white;
        margin: 0;
    }

    .panel-sub {
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.9) !important;
        margin-top: 0.25rem;
        font-weight: 400;
    }

    .text-warning {
        color: var(--mcd-yellow) !important;
        font-weight: 500;
    }

    .panel-body {
        flex: 1;
        padding: 1.5rem;
        overflow-y: auto;
        background: #f8f9fa;
    }

    /* Scrollbar Styling */
    .panel-body::-webkit-scrollbar {
        width: 10px;
    }

    .panel-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 5px;
    }

    .panel-body::-webkit-scrollbar-thumb {
        background: var(--mcd-red);
        border-radius: 5px;
        border: 2px solid #f1f1f1;
    }

    .panel-body::-webkit-scrollbar-thumb:hover {
        background: var(--mcd-dark-red);
    }

    /* Claim Tiles (Left - READY) */
    .claim-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        justify-content: center;
        align-items: center;
        min-height: 100%;
    }

    .claim-tile-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.3s ease;
    }

    .claim-tile-wrapper:hover {
        transform: translateY(-5px);
    }

    .claim-tile {
        width: 200px;
        height: 160px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        letter-spacing: 0.1em;
        background: var(--mcd-yellow);
        color: var(--mcd-red);
        box-shadow: 0 15px 35px rgba(218, 41, 28, 0.25);
        border: 5px solid white;
        font-size: 3.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .claim-tile::before {
        content: '';
        position: absolute;
        top: -10px;
        left: -10px;
        right: -10px;
        bottom: -10px;
        background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.3) 50%, transparent 70%);
        animation: shine 3s infinite linear;
    }

    @keyframes shine {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .claim-btn {
        margin-top: 0.75rem;
        font-size: 0.9rem;
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        font-weight: 700;
        background: var(--mcd-red) !important;
        border-color: var(--mcd-red) !important;
        color: white !important;
        transition: all 0.2s;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .claim-btn:hover {
        background: var(--mcd-dark-red) !important;
        border-color: var(--mcd-dark-red) !important;
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(218, 41, 28, 0.3);
    }

    /* In-Process Tiles (Right) */
    .process-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        justify-content: center;
        align-items: center;
        min-height: 100%;
    }

    .process-tile {
        width: 140px;
        height: 110px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        letter-spacing: 0.08em;
        background: linear-gradient(135deg, var(--mcd-green), var(--mcd-dark-green));
        color: white;
        box-shadow: 0 10px 25px rgba(34, 197, 94, 0.3);
        border: 3px solid white;
        font-size: 2.2rem;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .process-tile:hover {
        transform: scale(1.05);
        box-shadow: 0 15px 30px rgba(34, 197, 94, 0.4);
    }

    /* Empty Messages */
    .empty-message {
        text-align: center;
        margin-top: 3rem;
        color: #666;
        font-size: 1.1rem;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        border: 2px dashed #ddd;
    }

    /* Responsive Design */
    @media (min-width: 1200px) {
        .claim-tile {
            width: 220px;
            height: 180px;
            font-size: 4rem;
        }
        
        .process-tile {
            width: 160px;
            height: 130px;
            font-size: 2.5rem;
        }
    }

    @media (max-width: 991.98px) {
        .panel {
            height: 500px;
            margin-bottom: 1.5rem;
        }
        
        .main-wrapper {
            height: auto;
            overflow-y: auto;
        }
        
        .claim-tile {
            width: 180px;
            height: 140px;
            font-size: 3rem;
        }
        
        .process-tile {
            width: 120px;
            height: 100px;
            font-size: 1.8rem;
        }
    }

    @media (max-width: 768px) {
        .screen-title {
            font-size: 1.4rem;
        }
        
        .panel-title {
            font-size: 1.2rem;
        }
        
        .claim-tile {
            width: 150px;
            height: 120px;
            font-size: 2.5rem;
        }
        
        .process-tile {
            width: 100px;
            height: 80px;
            font-size: 1.5rem;
        }
        
        .claim-grid,
        .process-grid {
            gap: 1rem;
        }
        
        .claim-btn {
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .main-wrapper {
            padding: 1rem;
        }
        
        .panel-header {
            padding: 0.75rem 1rem;
        }
        
        .panel-body {
            padding: 1rem;
        }
        
        .claim-grid,
        .process-grid {
            gap: 0.75rem;
        }
        
        .claim-tile {
            width: 130px;
            height: 100px;
            font-size: 2rem;
        }
        
        .process-tile {
            width: 85px;
            height: 70px;
            font-size: 1.3rem;
        }
    }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark mb-0">
    <div class="container-fluid">
        <span class="navbar-brand screen-title">ORDER CLAIM DISPLAY</span>
        <div class="ms-auto d-flex align-items-center">
            <span class="navbar-text text-light me-3">
                <?= htmlspecialchars($_SESSION['employee_name']) ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="main-wrapper">
    <div class="row g-3">
        

        <!-- IN PROCESS (right, smaller) -->
        <div class="col-lg-5 col-md-12">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">IN PROCESS</div>
                    <div class="panel-sub">These orders are currently being prepared.</div>
                </div>
                <div class="panel-body" id="inProcessContainer">
                    <?php if ($inProcess): ?>
                        <div class="process-grid">
                            <?php foreach ($inProcess as $o): ?>
                                <div class="process-tile"
                                     data-order-id="<?= (int)$o['id'] ?>">
                                    <?= htmlspecialchars($o['display']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-message">
                            No orders in process.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
		<!-- CLAIM NOW (left, bigger) -->
        <div class="col-lg-7 col-md-12">
            <div class="panel">
                <div class="panel-header">
                    <div class="panel-title">CLAIM NOW</div>
                    <div class="panel-sub text-warning">When your number appears here, proceed to the counter.</div>
                </div>
                <div class="panel-body" id="readyContainer">
                    <?php if ($readyForClaim): ?>
                        <div class="claim-grid">
                            <?php foreach ($readyForClaim as $o): ?>
                                <div class="claim-tile-wrapper">
                                    <div class="claim-tile"
                                         data-order-id="<?= (int)$o['id'] ?>"
                                         data-display="<?= htmlspecialchars($o['display']) ?>">
                                        <?= htmlspecialchars($o['display']) ?>
                                    </div>
                                    <button class="btn btn-success claim-btn"
                                            onclick="markClaimed(<?= (int)$o['id'] ?>, '<?= htmlspecialchars($o['display']) ?>')">
                                        MARK AS CLAIMED
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-message">
                            No orders ready for claim.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let ws = null;

document.addEventListener('DOMContentLoaded', () => {
    initWebSocket();
});

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

function reloadOrders() {
    fetch('api_get_claim_orders_today.php')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const inProc = res.in_process || [];
            const ready  = res.ready_for_claim || [];

            const inProcContainer = document.getElementById('inProcessContainer');
            const readyContainer  = document.getElementById('readyContainer');

            // READY FOR CLAIM
            if (!ready.length) {
                readyContainer.innerHTML =
                    '<div class="empty-message">No orders ready for claim.</div>';
            } else {
                let html = '<div class="claim-grid">';
                ready.forEach(o => {
                    const disp = escapeHtml(o.display_number_str);
                    html += `
                        <div class="claim-tile-wrapper">
                            <div class="claim-tile"
                                 data-order-id="${o.id}"
                                 data-display="${disp}">
                                ${disp}
                            </div>
                            <button class="btn btn-dark claim-btn"
                                    onclick="markClaimed(${o.id}, '${disp}')">
                                Claimed
                            </button>
                        </div>
                    `;
                });
                html += '</div>';
                readyContainer.innerHTML = html;
            }

            // IN PROCESS
            if (!inProc.length) {
                inProcContainer.innerHTML =
                    '<div class="empty-message">No orders in process.</div>';
            } else {
                let html = '<div class="process-grid">';
                inProc.forEach(o => {
                    const disp = escapeHtml(o.display_number_str);
                    html += `
                        <div class="process-tile" data-order-id="${o.id}">
                            ${disp}
                        </div>
                    `;
                });
                html += '</div>';
                inProcContainer.innerHTML = html;
            }
        })
        .catch(err => console.error('reloadOrders error', err));
}

function markClaimed(orderId, displayNumber) {
    if (!confirm('Mark order #' + displayNumber + ' as CLAIMED?')) return;

    const fd = new FormData();
    fd.append('order_id', orderId);

    fetch('api_claim_mark_claimed.php', {
        method: 'POST',
        body: fd
    })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                alert(res.error || 'Error marking as claimed.');
                return;
            }
            reloadOrders();
        })
        .catch(() => {
            alert('Network error marking as claimed.');
        });
}

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
