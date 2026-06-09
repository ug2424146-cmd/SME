<?php
declare(strict_types=1);
require_once __DIR__ . "/../app/helpers/session.php";
require_auth();
$user = current_user();
$isManagerOrAdmin = in_array($user["role"], ["admin", "manager"], true);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reports Center - SME Platform</title>
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
            <div class="sidebar-section mt-4">Management</div>
            <a href="<?= e(url('team_progress.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">👥</span>
                <span>Team Progress</span>
            </a>
            <div class="sidebar-section mt-4">Reports</div>
            <a href="<?= e(url('reports_center.php')) ?>" class="sidebar-link active">
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
                <h1>Reports Center</h1>
                <p><?= $isManagerOrAdmin ? "Generate and download system reports" : "Download your task and performance reports" ?></p>
            </div>
        </div>

        <main class="container py-4">
<div class="card mb-3"><div class="card-body"><h2 class="h6"><?= $isManagerOrAdmin ? "Task Progress Report" : "My Task Report" ?></h2>
<form method="get" action="<?= e(url('reports.php')) ?>" class="row g-2">
<input type="hidden" name="type" value="task_progress">
<div class="col-md-4"><select class="form-select" name="status"><option value="">All statuses</option><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select></div>
<div class="col-md-3"><select class="form-select" name="format"><option value="csv">CSV</option><option value="excel">Excel</option><option value="pdf">PDF/Print</option></select></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Generate</button></div>
</form></div></div>
<div class="card"><div class="card-body"><h2 class="h6"><?= $isManagerOrAdmin ? "Performance Report" : "My Performance Report" ?></h2>
<form method="get" action="<?= e(url('reports.php')) ?>" class="row g-2">
<input type="hidden" name="type" value="performance">
<div class="col-md-3"><select class="form-select" name="format"><option value="csv">CSV</option><option value="excel">Excel</option><option value="pdf">PDF/Print</option></select></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Generate</button></div>
</form></div></div>
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

