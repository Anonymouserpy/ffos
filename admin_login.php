<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if ($u === ADMIN_USER && $p === ADMIN_PASS) {
        $_SESSION['admin'] = true;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - McDonald's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Simple McDonald's Colors */
        body {
            background: #DA291C; /* McDonald's Red */
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .logo-circle {
            width: 60px;
            height: 60px;
            background: #FFC72C; /* McDonald's Yellow */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-weight: bold;
            font-size: 24px;
            color: #DA291C;
        }
        
        h2 {
            color: #DA291C;
            text-align: center;
            margin-bottom: 25px;
            font-weight: bold;
        }
        
        .form-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
        
        .form-control:focus {
            border-color: #FFC72C;
            box-shadow: 0 0 0 2px rgba(255,199,44,0.3);
        }
        
        .btn-login {
            background: #DA291C;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            width: 100%;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: #A6192E; /* Darker Red */
        }
        
        .error-message {
            background: rgba(218,41,28,0.1);
            color: #DA291C;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #DA291C;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <div class="logo-circle">
                    A
                </div>
                <h2>Admin Login</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <div class="back-link">
                <a href="index.php">&larr; Back to Home</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>