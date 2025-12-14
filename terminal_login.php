<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pin = $_POST['pin'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM terminals WHERE pin_code = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$pin]);
    $terminal = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($terminal) {
        $_SESSION['terminal_id'] = $terminal['id'];
        $_SESSION['terminal_type'] = $terminal['type'];
        $_SESSION['employee_name'] = $terminal['employee_name'];

        switch ($terminal['type']) {
            case 'CUSTOMER':
                header('Location: customer_kiosk.php');
                break;
            case 'TELLER':
                header('Location: teller_dashboard.php');
                break;
            case 'KITCHEN':
                header('Location: kitchen_dashboard.php');
                break;
            case 'CLAIM':
                header('Location: claim_display.php');
                break;
        }
        exit;
    } else {
        $error = 'Invalid PIN or inactive terminal.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Terminal Login - McDonald's Ordering</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* McDonald's Color Palette */
        :root {
            --mcd-red: #DA291C;
            --mcd-yellow: #FFC72C;
            --mcd-dark-red: #A6192E;
            --mcd-light-yellow: #FFF1D0;
            --mcd-white: #FFFFFF;
            --mcd-gray: #F5F5F5;
            --mcd-dark: #27251F;
        }
        
        body {
            background: linear-gradient(135deg, var(--mcd-red) 0%, var(--mcd-dark-red) 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, -apple-system, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: var(--mcd-white);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--mcd-red), var(--mcd-dark-red));
            color: var(--mcd-white);
            text-align: center;
            padding: 30px 20px;
            border-bottom: none;
            position: relative;
            overflow: hidden;
        }
        
        .card-body {
            padding: 30px;
            background: var(--mcd-gray);
        }
        
        /* McDonald's Logo */
        .mcd-logo {
            width: 120px;
            height: 60px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .mcd-logo-text {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--mcd-yellow);
            text-shadow: 3px 3px 0 var(--mcd-red),
                         -3px -3px 0 var(--mcd-red),
                         -3px 3px 0 var(--mcd-red),
                         3px -3px 0 var(--mcd-red),
                         0 0 10px rgba(0,0,0,0.3);
            letter-spacing: -1px;
        }
        
        .login-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--mcd-white);
        }
        
        .login-subtitle {
            font-size: 1rem;
            color: var(--mcd-light-yellow);
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--mcd-dark);
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: var(--mcd-white);
        }
        
        .form-control:focus {
            border-color: var(--mcd-yellow);
            box-shadow: 0 0 0 3px rgba(255, 199, 44, 0.3);
        }
        
        .form-control-lg {
            font-size: 1.5rem !important;
            font-weight: 600;
            letter-spacing: 4px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--mcd-yellow), #FFB612);
            border: none;
            color: var(--mcd-dark);
            font-weight: 700;
            padding: 15px;
            border-radius: 12px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #FFB612, #FFA000);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 199, 44, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert-danger {
            background: rgba(218, 41, 28, 0.1);
            border: 2px solid var(--mcd-red);
            color: var(--mcd-dark-red);
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
        
        .back-link {
            color: var(--mcd-red);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-link:hover {
            color: var(--mcd-dark-red);
            text-decoration: underline;
        }
        
        .pin-hint {
            font-size: 0.85rem;
            color: #666;
            text-align: center;
            margin-top: 10px;
        }
        
        @media (max-width: 576px) {
            .login-card {
                margin: 0 15px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
            
            .mcd-logo-text {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card login-card">
                        <div class="card-header">
                            <div class="mcd-logo">
                                <div class="mcd-logo-text">McDonald's</div>
                            </div>
                            <h1 class="login-title">Terminal Login</h1>
                            <p class="login-subtitle">Ordering System</p>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            
                            <form method="post">
                                <div class="mb-4">
                                    <label for="pin" class="form-label">Enter Terminal PIN</label>
                                    <input type="password" 
                                           name="pin" 
                                           id="pin" 
                                           maxlength="6"
                                           class="form-control form-control-lg text-center"
                                           placeholder="••••••"
                                           autofocus 
                                           required
                                           pattern="[0-9]*"
                                           inputmode="numeric">
                                    <div class="pin-hint">Enter your 6-digit terminal PIN</div>
                                </div>
                                
                                <button type="submit" class="btn btn-login">
                                    LOGIN TO TERMINAL
                                </button>
                            </form>
                            
                            <div class="text-center mt-4 pt-3 border-top">
                                <a href="index.php" class="back-link">
                                    ← Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pinInput = document.getElementById('pin');
            
            pinInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
            
            pinInput.addEventListener('focus', function() {
                this.style.backgroundColor = 'var(--mcd-light-yellow)';
            });
            
            pinInput.addEventListener('blur', function() {
                this.style.backgroundColor = 'var(--mcd-white)';
            });
        });
    </script>
</body>
</html>