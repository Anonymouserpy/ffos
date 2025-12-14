<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
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

        /* Main Card */
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .card {
            border: 4px solid var(--mcd-yellow);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(218, 41, 28, 0.15);
            overflow: hidden;
        }

        .card-body {
            padding: 2rem;
        }

        h1.h4 {
            color: var(--mcd-red);
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--mcd-yellow);
        }

        /* List Group */
        .list-group {
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #eee;
        }

        .list-group-item {
            padding: 1.25rem 1.5rem;
            border-left: 5px solid var(--mcd-yellow);
            border-right: none;
            border-top: none;
            background-color: white;
            transition: all 0.3s ease;
        }

        .list-group-item:first-child {
            border-top: none;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .list-group-item:hover {
            background-color: var(--mcd-light-bg);
            border-left-color: var(--mcd-red);
            transform: translateX(5px);
        }

        .list-group-item a {
            color: var(--mcd-red);
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: block;
            transition: color 0.3s ease;
        }

        .list-group-item a:hover {
            color: var(--mcd-dark-red);
        }

        .list-group-item a::after {
            content: '→';
            float: right;
            color: var(--mcd-yellow);
            font-weight: bold;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .list-group-item:hover a::after {
            transform: translateX(5px);
            color: var(--mcd-red);
        }

        /* Logout Button */
        .btn-outline-secondary {
            border-color: var(--mcd-red) !important;
            color: var(--mcd-red) !important;
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
        }

        .btn-outline-secondary:hover {
            background-color: var(--mcd-red) !important;
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(218, 41, 28, 0.3);
        }

        .text-muted {
            color: #666 !important;
        }

        /* Footer Links */
        p.mt-3 {
            margin-top: 2rem !important;
            padding-top: 1.5rem;
            border-top: 2px solid #eee;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            .list-group-item {
                padding: 1rem 1.25rem;
            }
            
            .list-group-item a {
                font-size: 1rem;
            }
            
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 1.25rem;
            }
            
            h1.h4 {
                font-size: 1.3rem;
            }
            
            .list-group-item {
                padding: 0.75rem 1rem;
            }
            
            .btn-outline-secondary,
            .btn-outline-light {
                padding: 0.4rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
    <div class="container-fluid">
        <span class="navbar-brand">Admin Dashboard</span>
        <a href="admin_login.php" class="btn btn-outline-light btn-sm ms-auto">Admin Home</a>
    </div>
</nav>

<div class="container">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h4">Admin Dashboard</h1>
            <ul class="list-group mt-3">
                <li class="list-group-item">
                    <a href="admin_terminals.php" class="text-decoration-none">Terminal Management</a>
                </li>
                <li class="list-group-item">
                    <a href="admin_products.php" class="text-decoration-none">Products Management</a>
                </li>
            </ul>

            <p class="mt-3 mb-0">
                <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout Terminal Session</a>
                <span class="text-muted ms-2">(Admin session uses separate page; add admin logout if needed.)</span>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>