<?php
declare(strict_types=1);
require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_role(["manager", "admin"]);
$user = current_user();

$departmentId = (int) ($_GET["department_id"] ?? 0);
$departments = $mysqli->query("SELECT id, department_name FROM departments ORDER BY department_name ASC")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT u.name AS employee,
               d.department_name,
               COUNT(t.id) AS total_tasks,
               SUM(CASE WHEN t.status='completed' THEN 1 ELSE 0 END) AS completed_tasks,
               ROUND((SUM(CASE WHEN t.status='completed' THEN 1 ELSE 0 END) / NULLIF(COUNT(t.id),0)) * 100, 2) AS completion_rate
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id AND r.role_name='employee'
        LEFT JOIN user_departments ud ON ud.user_id = u.id
        LEFT JOIN departments d ON d.id = ud.department_id
        LEFT JOIN tasks t ON t.assigned_to = u.id
        WHERE 1=1";
if ($departmentId > 0) {
    $sql .= " AND d.id = " . $departmentId;
}
$sql .= " GROUP BY u.id, u.name, d.department_name ORDER BY completion_rate DESC, u.name ASC";
$rows = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Team Progress - SME Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(url('../assets/css/style.css')) ?>?v=<?= time() ?>" rel="stylesheet">
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
            <a href="<?= e(url('performance.php')) ?>" class="sidebar-link">
                <span class="sidebar-link-icon">⭐</span>
                <span>Performance</span>
            </a>
            <div class="sidebar-section mt-4">Management</div>
            <a href="<?= e(url('team_progress.php')) ?>" class="sidebar-link active">
                <span class="sidebar-link-icon">👥</span>
                <span>Team Progress</span>
            </a>
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
                <h1>Team Progress</h1>
                <p>Monitor team performance and completion rates</p>
            </div>
        </div>

        <main class="container py-4">
<form method="get" class="row g-2 mb-3"><div class="col-md-6"><select class="form-select" name="department_id"><option value="0">All departments</option><?php foreach($departments as $d): ?><option value="<?= (int)$d["id"] ?>" <?= $departmentId === (int)$d["id"] ? "selected" : "" ?>><?= e($d["department_name"]) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div></form>
<table class="table table-sm bg-white"><thead><tr><th>Employee</th><th>Department</th><th>Total</th><th>Completed</th><th>Completion %</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= e($r["employee"]) ?></td><td><?= e((string)$r["department_name"]) ?></td><td><?= e((string)$r["total_tasks"]) ?></td><td><?= e((string)$r["completed_tasks"]) ?></td><td><?= e((string)$r["completion_rate"]) ?></td></tr><?php endforeach; ?></tbody></table>
</main></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
