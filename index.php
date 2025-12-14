<?php
// index.php
require_once 'config.php';

// If already logged in as admin, go straight to admin dashboard
if (!empty($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

// If already logged in as a terminal, go straight to the proper screen
if (isset($_SESSION['terminal_id'], $_SESSION['terminal_type'])) {
    switch ($_SESSION['terminal_type']) {
        case 'CUSTOMER':
            header('Location: customer_kiosk.php');
            exit;
        case 'TELLER':
            header('Location: teller_dashboard.php');
            exit;
        case 'KITCHEN':
            header('Location: kitchen_dashboard.php');
            exit;
        case 'CLAIM':
            header('Location: claim_display.php');
            exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>McDo-Style Ordering System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('welcome.png');
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .overlay {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            border-radius: 15px;
            border: dotted 2px rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(5px);
            max-width: 500px;
            max-height: 600px;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f3efefff, #f85701ff);
            color: white;
            text-align: center;
            padding: 5px 5px;
            border-bottom: none;
        }
        
        .card-body {
            padding: 10px;
        }
        
        .welcome-title {
            font-size: 2.2rem;
            font-weight: 500;
            margin-bottom: 5px;
            background: linear-gradient(135deg, #ff6b6b, #ff8e53);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .system-name {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }
        
        .login-btn {
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-terminal {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: #333;
        }
        
        .btn-terminal:hover {
            background: linear-gradient(135deg, #ffb300, #f57c00);
            color: #000;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        
        .btn-admin:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
            color: white;
        }
        
        .info-text {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.5;
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }
        
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff6b6b, #ff8e53);
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="overlay">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card login-card">
                        <div class="card-header">
                            <div class="logo-placeholder">
                                <span>McD</span>
                            </div>
                            <h1 class="welcome-title">Welcome to</h1>
                            <h2 class="system-name">McDonalds Ordering</h2>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-muted mb-4">Select your login method to continue:</p>

                            <a class="btn btn-terminal login-btn w-100 mb-3" href="terminal_login.php">
                                <i class="bi bi-display"></i> Terminal Login
                                <br>
                                <small>(Customer / Teller / Kitchen / Claim)</small>
                            </a>

                            <a class="btn btn-admin login-btn w-100 mb-4" href="admin_login.php">
                                <i class="bi bi-shield-lock"></i> Admin Login
                            </a>

                            <div class="info-text">
                                <strong>Usage Guide:</strong><br>
                                • Use <strong>Terminal PIN</strong> for kiosks, teller, kitchen, or claim stations<br>
                                • Use <strong>admin credentials</strong> for system setup and management
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Icons for the icons used -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>