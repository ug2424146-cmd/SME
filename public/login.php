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
    <link href="<?php echo url("assets/vendor/bootstrap.min.css"); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="<?php echo url('assets/css/style.css'); ?>" rel="stylesheet">
    <link href="<?php echo url('assets/css/responsive.css'); ?>" rel="stylesheet">
</head>
<body>
    <div class="d-flex align-items-center justify-content-center min-vh-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card p-4 shadow-lg" style="max-width: 420px; width: 100%; border-radius: 24px;">
            <div class="text-center mb-4">
                <i class="fas fa-building fa-3x text-primary"></i>
                <h1 class="h4 mt-3 mb-1">SME Platform</h1>
                <p class="text-muted mb-0">Sign in to your account</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo e($error); ?>
                </div>
            <?php endif; ?>
            <form method="post" action="login.php">
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
                <a href="index.php" class="text-decoration-none">Back to homepage</a>
            </div>
        </div>
    </div>
    <script src="<?php echo url("assets/vendor/bootstrap.bundle.min.js"); ?>"></script>
</body>
</html>
