<?php
require_once 'config.php';
if (empty($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

function random_pin() {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Initialize terminals array to avoid undefined variable error
$terminals = [];

// Handle create/update/regenerate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $name = $_POST['name'] ?? '';
        $type = $_POST['type'] ?? '';
        $employee = $_POST['employee'] ?? '';
        $pin = random_pin();

        $stmt = $pdo->prepare("INSERT INTO terminals (name, type, employee_name, pin_code) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $type, $employee, $pin]);
    } elseif (isset($_POST['regen'])) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $pin = random_pin();
            $stmt = $pdo->prepare("UPDATE terminals SET pin_code = ? WHERE id = ?");
            $stmt->execute([$pin, $id]);
        }
    }
}

// Try to fetch terminals with error handling
try {
    $stmt = $pdo->query("SELECT * FROM terminals ORDER BY id");
    $terminals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Log error but don't crash
    error_log("Error fetching terminals: " . $e->getMessage());
    $terminals = []; // Ensure it's an array
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Terminal Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* McDonald's Colors */
        :root {
            --mcd-red: #DA291C;
            --mcd-yellow: #FFCC00;
            --mcd-light-yellow: #ffde59;
            --mcd-dark-red: #b82217;
            --mcd-light-bg: #fff9e6;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Roboto', Arial, sans-serif;
        }

        /* Navbar - McDonald's Style */
        .navbar.bg-dark {
            background: linear-gradient(135deg, var(--mcd-red) 0%, var(--mcd-dark-red) 100%) !important;
            border-bottom: 4px solid var(--mcd-yellow);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            padding: 12px 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
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

        /* Container */
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        /* Cards */
        .card {
            border: 4px solid var(--mcd-yellow);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(218, 41, 28, 0.15);
            overflow: hidden;
            height: 100%;
        }

        .card-header {
            color: white !important;
            border-bottom: 3px solid var(--mcd-yellow);
            padding: 1rem 1.5rem !important;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .card-header.bg-danger {
            background: linear-gradient(to right, var(--mcd-red), var(--mcd-dark-red)) !important;
        }

        .card-header.bg-warning {
            background: linear-gradient(to right, var(--mcd-yellow), var(--mcd-light-yellow)) !important;
            color: var(--mcd-red) !important;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Form Styling */
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--mcd-yellow);
            box-shadow: 0 0 0 0.25rem rgba(255, 204, 0, 0.25);
        }

        /* Create Button */
        .btn-danger {
            background: linear-gradient(to bottom, var(--mcd-red), var(--mcd-dark-red)) !important;
            border-color: var(--mcd-red) !important;
            color: white !important;
            font-weight: 700;
            padding: 0.75rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: linear-gradient(to bottom, var(--mcd-dark-red), var(--mcd-red)) !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(218, 41, 28, 0.3);
        }

        /* Table Styling */
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #eee;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: linear-gradient(to right, var(--mcd-red), var(--mcd-dark-red));
            color: white !important;
            border: none;
            font-weight: 600;
            padding: 1rem 1rem !important;
            border-bottom: 2px solid var(--mcd-yellow);
        }

        .table tbody tr {
            border-bottom: 1px solid #eee;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table tbody tr:hover {
            background-color: var(--mcd-light-bg);
        }

        .table td {
            padding: 1rem 1rem !important;
            vertical-align: middle;
            color: #333;
        }

        /* Badge for PIN */
        .badge.bg-dark {
            background: linear-gradient(135deg, #333, #555) !important;
            color: white;
            font-weight: 600;
            font-family: monospace;
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #444;
            min-width: 100px;
            display: inline-block;
            text-align: center;
        }

        /* Regenerate Button */
        .btn-outline-primary {
            border-color: var(--mcd-red) !important;
            color: var(--mcd-red) !important;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--mcd-red) !important;
            color: white !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(218, 41, 28, 0.2);
        }

        /* Empty State */
        .text-center.text-muted {
            color: #666 !important;
            padding: 2rem !important;
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .row.g-3 {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }
            
            .col-md-4, .col-md-8 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .table td, .table th {
                padding: 0.75rem !important;
            }
            
            .badge.bg-dark {
                font-size: 0.9rem;
                padding: 0.4rem 0.8rem;
                min-width: 80px;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
            
            .card-header {
                padding: 0.75rem 1rem !important;
                font-size: 1.1rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .btn-outline-primary {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
            
            .btn-danger {
                padding: 0.6rem;
            }
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
    <div class="container-fluid">
        <span class="navbar-brand">Terminal Management</span>
        <a href="admin_dashboard.php" class="btn btn-outline-light btn-sm ms-auto">Back to Dashboard</a>
    </div>
</nav>

<div class="container">
    <?php if (isset($e)): ?>
        <div class="alert alert-danger mb-3">
            <strong>Database Error:</strong> Could not fetch terminals. Please check your database connection.
        </div>
    <?php endif; ?>
    
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <strong>Create Terminal</strong>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="create" value="1">
                        <div class="mb-3">
                            <label class="form-label">Terminal Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Kiosk 1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="CUSTOMER">CUSTOMER</option>
                                <option value="TELLER">TELLER</option>
                                <option value="KITCHEN">KITCHEN</option>
                                <option value="CLAIM">CLAIM</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Employee Name</label>
                            <input type="text" name="employee" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Create</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <strong>Existing Terminals</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Employee</th>
                                <th>PIN</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (is_array($terminals) && count($terminals) > 0): ?>
                                <?php foreach ($terminals as $t): ?>
                                    <tr>
                                        <td><?= (int)$t['id'] ?></td>
                                        <td><?= htmlspecialchars($t['name']) ?></td>
                                        <td><?= htmlspecialchars($t['type']) ?></td>
                                        <td><?= htmlspecialchars($t['employee_name']) ?></td>
                                        <td><span class="badge bg-dark"><?= htmlspecialchars($t['pin_code']) ?></span></td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                                <button type="submit" name="regen" value="1"
                                                        class="btn btn-sm btn-outline-primary">
                                                    Regenerate PIN
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">No terminals yet. Create your first terminal using the form on the left.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>