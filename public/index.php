<?php

declare(strict_types=1);

require_once __DIR__ . "/../app/helpers/session.php";

if (current_user() !== null) {
    header("Location: " . url("dashboard.php"));
    exit;
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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            opacity: 0.9;
            margin: 0;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .input-group-text {
            background: #f8fafc;
            border: 2px solid #e5e7eb;
            border-right: none;
            color: #6b7280;
        }
        .form-control:focus + .input-group-text,
        .input-group-text:focus {
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-card animate-fade-in">
        <div class="login-header">
            <i class="fas fa-building mb-3" style="font-size: 3rem;"></i>
            <h1>SME Platform</h1>
            <p>Sign in to your account</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger border-0" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo url('login.php'); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">
                        <i class="fas fa-envelope me-2"></i>Email Address
                    </label>
                    <div class="input-group">
                        <input id="email" type="email" class="form-control" name="email"
                               placeholder="Enter your email" required>
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <div class="input-group">
                        <input id="password" type="password" class="form-control" name="password"
                               placeholder="Enter your password" required>
                        <span class="input-group-text">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Secure login powered by SME Platform
                </small>
            </div>
        </div>
    </div>

    <script src="<?php echo url("assets/vendor/bootstrap.bundle.min.js"); ?>"></script>
</body>
</html>

