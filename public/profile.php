<?php
declare(strict_types=1);
require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/helpers/app.php";
require_auth();
$user = current_user();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        flash("error", "Invalid CSRF token.");
        header("Location: " . url("profile.php"));
        exit;
    }
    $action = (string) ($_POST["action"] ?? "");
    if ($action === "update_profile") {
        $name = trim((string) ($_POST["name"] ?? ""));
        if ($name === "") {
            flash("error", "Name is required.");
        } else {
            $stmt = $mysqli->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $user["id"]);
            $stmt->execute();
            $stmt->close();
            $_SESSION["user"]["name"] = $name;
            log_activity((int) $user["id"], "profile_update", "Updated own profile");
            flash("success", "Profile updated.");
        }
    } elseif ($action === "upload_profile_picture") {
        if (!isset($_FILES["profile_picture"])) {
            flash("error", "No file uploaded.");
        } else {
            $file = $_FILES["profile_picture"];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];

            if ($file["size"] > $maxSize) {
                flash("error", "File size exceeds 5MB limit.");
            } elseif (!in_array($file["type"], $allowedTypes, true)) {
                flash("error", "Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.");
            } elseif ($file["error"] !== UPLOAD_ERR_OK) {
                flash("error", "File upload failed.");
            } else {
                $uploadDir = __DIR__ . "/uploads/profiles/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
                $filename = "profile_" . $user["id"] . "_" . time() . "." . $ext;
                $filePath = $uploadDir . $filename;

                if (move_uploaded_file($file["tmp_name"], $filePath)) {
                    // Delete old profile picture if exists
                    $stmt = $mysqli->prepare("SELECT profile_picture FROM users WHERE id = ? LIMIT 1");
                    $stmt->bind_param("i", $user["id"]);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if ($row && $row["profile_picture"]) {
                        $oldPath = __DIR__ . "/uploads/profiles/" . $row["profile_picture"];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $stmt = $mysqli->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                    $stmt->bind_param("si", $filename, $user["id"]);
                    $stmt->execute();
                    $stmt->close();

                    $_SESSION["user"]["profile_picture"] = $filename;
                    log_activity((int) $user["id"], "profile_picture_upload", "Uploaded profile picture");
                    flash("success", "Profile picture updated successfully.");
                } else {
                    flash("error", "Failed to save file.");
                }
            }
        }
    } elseif ($action === "change_password") {
        $current = (string) ($_POST["current_password"] ?? "");
        $new = (string) ($_POST["new_password"] ?? "");
        if (strlen($new) < 8) {
            flash("error", "New password must be at least 8 characters.");
        } else {
            $stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $user["id"]);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row || !password_verify($current, (string) $row["password"])) {
                flash("error", "Current password is incorrect.");
            } else {
                $hash = password_hash($new, PASSWORD_BCRYPT);
                $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hash, $user["id"]);
                $stmt->execute();
                $stmt->close();
                log_activity((int) $user["id"], "password_change", "Changed own password");
                flash("success", "Password changed.");
            }
        }
    }
    header("Location: " . url("profile.php"));
    exit;
}

$successMessage = get_flash("success");
$errorMessage = get_flash("error");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile - SME Platform</title>
    <link href="<?php echo url("assets/vendor/bootstrap.min.css"); ?>" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>?v=<?= time() ?>" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%) !important; }
        .card { border-radius: 12px !important; box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important; border: none !important; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; color: white !important; font-weight: 600 !important; border-radius: 12px 12px 0 0 !important; padding: 1rem 1.5rem !important; }
        .btn { border-radius: 8px !important; font-weight: 600 !important; padding: 0.75rem 1.5rem !important; transition: all 0.3s ease !important; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; border: none !important; }
        .btn-success { background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important; border: none !important; }
        .btn-info { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%) !important; border: none !important; }
        .btn-warning { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%) !important; border: none !important; }
        .btn:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important; }
        .sidebar { background: linear-gradient(180deg, #667eea 0%, #764ba2 100%) !important; box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important; }
        .sidebar-brand { color: white !important; font-weight: 700 !important; }
        .sidebar-link { color: rgba(255,255,255,0.85) !important; }
        .sidebar-link:hover { background: rgba(255,255,255,0.15) !important; color: white !important; }
        .sidebar-link.active { background: rgba(255,255,255,0.25) !important; color: white !important; }
        .main-content { 
            margin-left: 280px !important; 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%) !important; 
            min-height: 100vh !important; 
            padding-top: 0 !important;
        }
        .main-content > main {
            padding-top: 2rem !important;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            padding: 2.5rem 0 !important;
            border-radius: 12px !important;
            margin-bottom: 2rem !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .page-header h1 { font-weight: 800 !important; font-size: 2.25rem !important; margin: 0 !important; }
        .table thead th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; color: white !important; }
    </style>
</head>
<body class="bg-light">
<div class="sidebar-wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?= e(url('dashboard.php')) ?>" class="sidebar-brand">
                🏢 SME Platform
            </a>
        </div>
        <div class="sidebar-user">
            <div class="sidebar-user-name"><?= e($user["name"]) ?></div>
            <div class="sidebar-user-role"><?= e($user["role"]) ?></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section d-flex align-items-center">
                <span class="me-2">☰</span>
                <span>Main Menu</span>
            </div>
            <div class="main-menu-row">
                <a href="<?= e(url('dashboard.php')) ?>" class="main-menu-item">
                    <span class="icon">📊</span>
                    <span class="label">Dashboard</span>
                </a>
                <a href="<?= e(url('tasks.php')) ?>" class="main-menu-item">
                    <span class="icon">📋</span>
                    <span class="label">Tasks</span>
                </a>
                <a href="<?= e(url('skills.php')) ?>" class="main-menu-item">
                    <span class="icon">🎯</span>
                    <span class="label">Skills</span>
                </a>
                <a href="<?= e(url('performance.php')) ?>" class="main-menu-item">
                    <span class="icon">⭐</span>
                    <span class="label">Performance</span>
                </a>
            </div>
            <?php if ($user["role"] !== "employee"): ?>
                <div class="sidebar-section mt-4">Management</div>
                <a href="<?= e(url('team_progress.php')) ?>" class="sidebar-link">
                    <span class="sidebar-link-icon">👥</span>
                    <span>Team Progress</span>
                </a>
            <?php endif; ?>
            <div class="sidebar-section mt-4">Reports</div>
            <a href="<?= e(url('reports_center.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">📈</span>
                <span>Reports</span>
            </a>
            <?php if ($user["role"] === "admin"): ?>
                <div class="sidebar-section mt-4">Administration</div>
                <a href="<?= e(url('users.php')) ?>" class="sidebar-link">
                    <span class="sidebar-link-icon">👤</span>
                    <span>Users</span>
                </a>
                <a href="<?= e(url('departments.php')) ?>" class="sidebar-link">
                    <span class="sidebar-link-icon">🏢</span>
                    <span>Departments</span>
                </a>
                <a href="<?= e(url('settings.php')) ?>" class="sidebar-link">
                    <span class="sidebar-link-icon">⚙️</span>
                    <span>Settings</span>
                </a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= e(url('notifications_view.php')) ?>" class="sidebar-footer-link">
                <span>🔔</span>
                <span>Notifications</span>
            </a>
            <a href="<?= e(url('profile.php')) ?>" class="sidebar-footer-link">
                <span>👤</span>
                <span>My Profile</span>
            </a>
            <a href="<?= e(url('logout.php')) ?>" class="sidebar-footer-link">
                <span>🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">☰ Menu</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1>My Profile</h1>
                <p>Manage your account settings</p>
            </div>
        </div>

        <main class="container py-4">
            <?php if ($successMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>✓ Success!</strong> <?= e($successMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>✕ Error!</strong> <?= e($errorMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Profile Picture Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📷 Profile Picture</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-4">
                            <div style="width: 150px; height: 150px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto; overflow: hidden; border: 4px solid #e2e8f0; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);">
                                <?php 
                                    $profilePic = $user["profile_picture"] ?? null;
                                    if ($profilePic && file_exists(__DIR__ . "/uploads/profiles/" . $profilePic)):
                                ?>
                                    <img src="<?= e(url('uploads/profiles/' . $profilePic)) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="font-size: 3.5rem;">👤</div>
                                <?php endif; ?>
                            </div>
                            <p style="margin-top: 1rem; color: #64748b; font-size: 0.9rem;">
                                <?= e((string) $user["name"]) ?><br>
                                <span style="text-transform: capitalize; font-weight: 600;">{{ role }}</span>
                            </p>
                        </div>
                        <div class="col-md-9">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="upload_profile_picture">
                                <div class="mb-3">
                                    <label class="form-label" for="profile_picture" style="font-weight: 600;">Upload New Picture</label>
                                    <input class="form-control form-control-lg" id="profile_picture" type="file" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp" required>
                                    <small class="text-muted d-block mt-2">
                                        Supported formats: JPEG, PNG, GIF, WebP<br>
                                        Maximum file size: 5MB<br>
                                        Recommended: Square image (1:1 aspect ratio)
                                    </small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    ⬆️ Upload Picture
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📝 Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label" for="name" style="font-weight: 600;">Name</label>
                                <input class="form-control form-control-lg" id="name" type="text" name="name" value="<?= e((string) $user["name"]) ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="email" style="font-weight: 600;">Email</label>
                                <input class="form-control form-control-lg" id="email" type="email" value="<?= e((string) $user["email"]) ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight: 600;">Role</label>
                                <div class="alert alert-info mb-0" role="alert">
                                    <strong style="text-transform: capitalize;"><?= e((string) $user["role"]) ?></strong>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            ✓ Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">🔒 Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="change_password">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="current_password" style="font-weight: 600;">Current Password</label>
                                <input class="form-control form-control-lg" id="current_password" type="password" name="current_password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="new_password" style="font-weight: 600;">New Password (min. 8 characters)</label>
                                <input class="form-control form-control-lg" id="new_password" type="password" name="new_password" minlength="8" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            🔄 Change Password
                        </button>
                    </form>
                </div>
            </div>
        </main></div></div>

<script src="<?php echo url("assets/vendor/bootstrap.bundle.min.js"); ?>"></script>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar && overlay) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
}

// Auto-hide alerts after 5 seconds
document.querySelectorAll('.alert').forEach(function(alert) {
    setTimeout(function() {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
});
</script>
</body>
</html>

