<?php

declare(strict_types=1);

require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../app/controllers/AuthController.php";

if (current_user() !== null) {
    header("Location: " . url("dashboard.php"));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $controller = new AuthController();
    $controller->login();
}

$error = $_SESSION["error"] ?? null;
unset($_SESSION["error"]);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SME Platform - Login</title>
    <link rel="icon" type="image/png" href="<?php echo url('assets/images/logo.png'); ?>">
    <link href="<?php echo url("assets/vendor/bootstrap.min.css"); ?>" rel="stylesheet">
    <link href="<?php echo url('assets/vendor/all.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo url('assets/css/style.css'); ?>?v=<?php echo time(); ?>" rel="stylesheet">
    <script>
        (function() {
            var saved = localStorage.getItem("sme_theme");
            var prefers = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
            var theme = saved || (prefers ? "dark" : "light");
            if (theme === "dark") {
                document.documentElement.classList.add("dark");
                document.documentElement.setAttribute("data-theme", "dark");
                document.documentElement.setAttribute("data-bs-theme", "dark");
            }
        })();
    </script>
    <style>
        .login-page-wrapper {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-page-wrapper::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background-image: url('<?php echo url('assets/images/logo.png'); ?>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
        }
        
        .login-card {
            position: relative;
            z-index: 1;
            background-color: var(--bs-body-bg);
            border-radius: 16px;
            max-width: 420px;
            width: 100%;
        }
        
        .dark .login-page-wrapper {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }
    </style>
</head>
<body class="login-page">
    <div class="login-page-wrapper d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-card p-4 shadow-lg">
            <div class="login-card-brand text-center mb-4">
                <div class="login-brand-icon mb-3">
                    <img src="<?php echo url('assets/images/logo.png'); ?>" alt="SME Platform" width="90" height="90" style="border-radius:22px;object-fit:cover;">
                </div>
                <h1 class="h4 fw-bold mb-1">SME Platform</h1>
                <p class="text-muted mb-0">Secure access to your team dashboard</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo e($error); ?>
                </div>
            <?php endif; ?>
            <form method="post" action="<?php echo url('login.php'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input id="email" type="email" class="form-control" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input id="password" type="password" class="form-control" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
            </form>
            <div class="text-center mt-4">
                <a href="<?php echo url('index.php'); ?>" class="text-decoration-none text-muted">Back to homepage</a>
            </div>
        </div>
    </div>
    <script src="<?php echo url("assets/vendor/bootstrap.bundle.min.js"); ?>"></script>
    <script src="<?php echo url('assets/js/app.js'); ?>?v=<?php echo time(); ?>"></script>
</body>
</html>
