<?php
declare(strict_types=1);
require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/controllers/TeamProgressController.php";

require_role(["manager", "admin"]);
$user = current_user();
$controller = new TeamProgressController();

$departmentId = (int) ($_GET["department_id"] ?? 0);
$redirectQs = $departmentId > 0 ? "?department_id=" . $departmentId : "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        flash("error", "Invalid CSRF token.");
    } else {
        $action = (string) ($_POST["action"] ?? "");
        if ($action === "assign_department") {
            $controller->assignToDepartment($_POST, (int) $user["id"]);
        } elseif ($action === "remove_department") {
            $controller->removeFromDepartment($_POST, (int) $user["id"]);
        } else {
            flash("error", "Unsupported action.");
        }
    }
    header("Location: " . url("team_progress.php") . $redirectQs);
    exit;
}

$departments = $mysqli->query("SELECT id, department_name FROM departments ORDER BY department_name ASC")->fetch_all(MYSQLI_ASSOC);

$employees = [];
$empRes = $mysqli->query(
    "SELECT u.id, u.name
     FROM users u
     LEFT JOIN roles r ON r.id = u.role_id
     WHERE COALESCE(r.role_name, u.role) = 'employee'
     ORDER BY u.name ASC"
);
while ($row = $empRes->fetch_assoc()) {
    $employees[] = $row;
}

$sqlInner = "SELECT u.id,
               u.name AS employee,
               GROUP_CONCAT(DISTINCT d.department_name ORDER BY d.department_name SEPARATOR ', ') AS department_names,
               COUNT(t.id) AS total_tasks,
               SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks,
               ROUND(
                   (SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) / NULLIF(COUNT(t.id), 0)) * 100,
                   2
               ) AS completion_rate
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        LEFT JOIN user_departments ud ON ud.user_id = u.id
        LEFT JOIN departments d ON d.id = ud.department_id
        LEFT JOIN tasks t ON t.assigned_to = u.id
        WHERE COALESCE(r.role_name, u.role) = 'employee'";
if ($departmentId > 0) {
    $sqlInner .= " AND EXISTS (
        SELECT 1 FROM user_departments udf
        WHERE udf.user_id = u.id AND udf.department_id = ?
    )";
}
$sqlInner .= " GROUP BY u.id, u.name";
$sql = "SELECT tp.id, tp.employee, tp.department_names, tp.total_tasks, tp.completed_tasks, tp.completion_rate
        FROM (" . $sqlInner . ") AS tp
        ORDER BY (tp.completion_rate IS NULL) ASC, tp.completion_rate DESC, tp.employee ASC";

if ($departmentId > 0) {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $departmentId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $rows = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$successMessage = get_flash("success");
$errorMessage = get_flash("error");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Team Progress - SME Platform</title>
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
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?= e($successMessage) ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?= e($errorMessage) ?></div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <strong>Team &amp; departments</strong>
                    <span class="text-white-50 small ms-2">Put employees on a department so filters and reporting stay accurate.</span>
                </div>
                <div class="card-body">
                    <?php if (empty($departments)): ?>
                        <p class="text-muted mb-0">Create departments first (Admin → Departments), then assign employees here.</p>
                    <?php elseif (empty($employees)): ?>
                        <p class="text-muted mb-0">No employee accounts yet. Add users with the Employee role.</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <h3 class="h6 text-muted text-uppercase">Add to department</h3>
                                <form method="post" class="row g-2 align-items-end">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="assign_department">
                                    <div class="col-md-6">
                                        <label class="form-label small mb-0">Employee</label>
                                        <select class="form-select" name="user_id" required>
                                            <?php foreach ($employees as $e): ?>
                                                <option value="<?= (int) $e["id"] ?>"><?= e((string) $e["name"]) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-0">Department</label>
                                        <select class="form-select" name="department_id" required>
                                            <?php foreach ($departments as $d): ?>
                                                <option value="<?= (int) $d["id"] ?>"><?= e((string) $d["department_name"]) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-success w-100">Add</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-6">
                                <h3 class="h6 text-muted text-uppercase">Remove from department</h3>
                                <form method="post" class="row g-2 align-items-end">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="remove_department">
                                    <div class="col-md-6">
                                        <label class="form-label small mb-0">Employee</label>
                                        <select class="form-select" name="user_id" required>
                                            <?php foreach ($employees as $e): ?>
                                                <option value="<?= (int) $e["id"] ?>"><?= e((string) $e["name"]) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small mb-0">Department</label>
                                        <select class="form-select" name="department_id" required>
                                            <?php foreach ($departments as $d): ?>
                                                <option value="<?= (int) $d["id"] ?>"><?= e((string) $d["department_name"]) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-outline-danger w-100">Remove</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <form method="get" class="row g-2 mb-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-0">Filter by department</label>
                    <select class="form-select" name="department_id">
                        <option value="0">All departments</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= (int) $d["id"] ?>" <?= $departmentId === (int) $d["id"] ? "selected" : "" ?>>
                                <?= e((string) $d["department_name"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <div class="table-responsive shadow-sm rounded overflow-hidden">
                <table class="table table-sm table-striped bg-white mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department(s)</th>
                            <th class="text-end">Total tasks</th>
                            <th class="text-end">Completed</th>
                            <th class="text-end">Completion %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $deptLabel = trim((string) ($r["department_names"] ?? ""));
                            if ($deptLabel === "") {
                                $deptLabel = "—";
                            }
                            $total = (int) $r["total_tasks"];
                            $completed = (int) $r["completed_tasks"];
                            $rate = $r["completion_rate"];
                            $rateLabel = $total === 0 ? "—" : ($rate !== null ? (string) $rate : "—");
                            ?>
                            <tr>
                                <td><?= e((string) $r["employee"]) ?></td>
                                <td><span class="text-muted"><?= e($deptLabel) ?></span></td>
                                <td class="text-end"><?= $total ?></td>
                                <td class="text-end"><?= $completed ?></td>
                                <td class="text-end"><?= e($rateLabel) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>

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

