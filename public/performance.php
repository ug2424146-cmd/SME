<?php
declare(strict_types=1);
require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/controllers/PerformanceController.php";
require_auth();
$user = current_user();
$controller = new PerformanceController();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null) || !in_array($user["role"], ["manager", "admin"], true)) {
        flash("error", "Invalid CSRF token.");
    } else {
        $controller->createReview($_POST, (int) $user["id"]);
    }
    header("Location: " . url("performance.php"));
    exit;
}

$employees = $mysqli->query("SELECT u.id, u.name FROM users u INNER JOIN roles r ON r.id=u.role_id WHERE r.role_name='employee' ORDER BY u.name")->fetch_all(MYSQLI_ASSOC);
if ($user["role"] === "employee") {
    $stmt = $mysqli->prepare(
        "SELECT p.rating,p.feedback,p.review_date,u.name employee,rv.name reviewer
         FROM performance p
         INNER JOIN users u ON u.id=p.user_id
         INNER JOIN users rv ON rv.id=p.reviewer_id
         WHERE p.user_id = ?
         ORDER BY p.created_at DESC LIMIT 50"
    );
    $stmt->bind_param("i", $user["id"]);
    $stmt->execute();
    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $reviews = $mysqli->query("SELECT p.rating,p.feedback,p.review_date,u.name employee,rv.name reviewer FROM performance p INNER JOIN users u ON u.id=p.user_id INNER JOIN users rv ON rv.id=p.reviewer_id ORDER BY p.created_at DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);
}
$successMessage = get_flash("success");
$errorMessage = get_flash("error");
$avgRating = 0;
if (count($reviews) > 0) {
    $sum = 0;
    foreach ($reviews as $review) {
        $sum += (int) $review["rating"];
    }
    $avgRating = round($sum / count($reviews), 2);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Performance - SME Platform</title>
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
            <div class="sidebar-section">Main Menu</div>
            <a href="<?= e(url('dashboard.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="<?= e(url('tasks.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">📋</span>
                <span>Tasks</span>
            </a>
            <a href="<?= e(url('skills.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">🎯</span>
                <span>Skills</span>
            </a>
            <a href="<?= e(url('performance.php')) ?>" class="sidebar-link active">
                <span class="sidebar-link-icon">⭐</span>
                <span>Performance</span>
            </a>
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
                <h1>Performance Reviews</h1>
                <p>Track and manage employee performance</p>
            </div>
        </div>

        <main class="container py-4">
<?php if ($successMessage): ?><div class="alert alert-success"><?= e($successMessage) ?></div><?php endif; ?>
<?php if ($errorMessage): ?><div class="alert alert-danger"><?= e($errorMessage) ?></div><?php endif; ?>
<?php if (in_array($user["role"], ["manager", "admin"], true)): ?>
<form method="post" class="row g-2 mb-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="col-md-4"><select class="form-select" name="user_id" required><option value="">Select employee</option><?php foreach($employees as $e): ?><option value="<?= (int)$e["id"] ?>"><?= e($e["name"]) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><input class="form-control" type="number" min="1" max="5" name="rating" placeholder="Rating"></div><div class="col-md-4"><input class="form-control" name="feedback" placeholder="Feedback"></div><div class="col-md-2"><button class="btn btn-primary w-100">Submit</button></div></form>
<?php endif; ?>
<div class="alert alert-info">Average Rating: <?= e((string) $avgRating) ?></div>
<table class="table table-sm bg-white"><thead><tr><th>Employee</th><th>Reviewer</th><th>Rating</th><th>Feedback</th><th>Date</th></tr></thead><tbody><?php foreach($reviews as $r): ?><tr><td><?= e($r["employee"]) ?></td><td><?= e($r["reviewer"]) ?></td><td><?= e((string)$r["rating"]) ?></td><td><?= e((string)$r["feedback"]) ?></td><td><?= e($r["review_date"]) ?></td></tr><?php endforeach; ?></tbody></table>
</main></div></div>

<script src="<?php echo url("assets/vendor/bootstrap.bundle.min.js"); ?>"></script>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}
</script>
</body>
</html>

