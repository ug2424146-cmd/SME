<?php

declare(strict_types=1);

require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";

require_auth();
$user = current_user();

$stats = [
    "pending" => 0,
    "in_progress" => 0,
    "completed" => 0,
];

global $mysqli;

if ($user["role"] === "employee") {
    $stmt = $mysqli->prepare("SELECT status, COUNT(*) AS total FROM tasks WHERE assigned_to = ? GROUP BY status");
    $stmt->bind_param("i", $user["id"]);
} else {
    $stmt = $mysqli->prepare("SELECT status, COUNT(*) AS total FROM tasks GROUP BY status");
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $status = strtolower((string) $row["status"]);
    if (array_key_exists($status, $stats)) {
        $stats[$status] = (int) $row["total"];
    }
}
$stmt->close();

// Role-specific statistics
$roleStats = [];

if ($user["role"] === "admin") {
    // Admin stats
    $roleStats["total_users"] = $mysqli->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()["count"];
    $roleStats["active_users"] = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1")->fetch_assoc()["count"];
    $roleStats["total_departments"] = $mysqli->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()["count"];
    $roleStats["total_skills"] = $mysqli->query("SELECT COUNT(*) as count FROM skills")->fetch_assoc()["count"];
    $roleStats["recent_activities"] = $mysqli->query("SELECT action, description, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
    
} elseif ($user["role"] === "manager") {
    // Manager stats
    $roleStats["team_size"] = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE role = 'employee'")->fetch_assoc()["count"];
    $roleStats["team_pending"] = $mysqli->query("SELECT COUNT(*) as count FROM tasks t JOIN users u ON t.assigned_to = u.id WHERE u.role = 'employee' AND t.status = 'pending'")->fetch_assoc()["count"];
    $roleStats["team_completed"] = $mysqli->query("SELECT COUNT(*) as count FROM tasks t JOIN users u ON t.assigned_to = u.id WHERE u.role = 'employee' AND t.status = 'completed'")->fetch_assoc()["count"];
    $roleStats["avg_completion"] = $mysqli->query("SELECT ROUND(AVG(completion_rate), 2) as avg FROM (SELECT u.id, (COUNT(CASE WHEN t.status = 'completed' THEN 1 END) * 100.0 / COUNT(t.id)) as completion_rate FROM users u LEFT JOIN tasks t ON t.assigned_to = u.id WHERE u.role = 'employee' GROUP BY u.id) as rates")->fetch_assoc()["avg"] ?? 0;
    $roleStats["recent_tasks"] = $mysqli->query("SELECT t.title, t.status, u.name as assignee, t.created_at FROM tasks t JOIN users u ON t.assigned_to = u.id WHERE u.role = 'employee' ORDER BY t.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
    
} else {
    // Employee stats
    $roleStats["total_tasks"] = $mysqli->prepare("SELECT COUNT(*) as count FROM tasks WHERE assigned_to = ?");
    $roleStats["total_tasks"]->bind_param("i", $user["id"]);
    $roleStats["total_tasks"]->execute();
    $roleStats["total_tasks"] = $roleStats["total_tasks"]->get_result()->fetch_assoc()["count"];
    
    $roleStats["my_skills"] = $mysqli->prepare("SELECT COUNT(*) as count FROM employee_skills WHERE user_id = ?");
    $roleStats["my_skills"]->bind_param("i", $user["id"]);
    $roleStats["my_skills"]->execute();
    $roleStats["my_skills"] = $roleStats["my_skills"]->get_result()->fetch_assoc()["count"];
    
    $roleStats["avg_rating"] = $mysqli->prepare("SELECT ROUND(AVG(rating), 2) as avg FROM performance WHERE user_id = ?");
    $roleStats["avg_rating"]->bind_param("i", $user["id"]);
    $roleStats["avg_rating"]->execute();
    $roleStats["avg_rating"] = $roleStats["avg_rating"]->get_result()->fetch_assoc()["avg"] ?? 0;
    
    $roleStats["recent_tasks"] = $mysqli->prepare("SELECT title, status, deadline, created_at FROM tasks WHERE assigned_to = ? ORDER BY created_at DESC LIMIT 5");
    $roleStats["recent_tasks"]->bind_param("i", $user["id"]);
    $roleStats["recent_tasks"]->execute();
    $roleStats["recent_tasks"] = $roleStats["recent_tasks"]->get_result()->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = ucfirst($user["role"]) . ' Dashboard';
$pageSubtitle = 'Welcome back, ' . e($user["name"]) . '! Here\'s your overview.';
$currentPage = 'dashboard';

ob_start();
?>
<?php if ($user["role"] === "admin"): ?>
    <!-- ADMIN DASHBOARD -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card animate-slide-up h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-primary"><?= $roleStats["total_users"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Total Users</p>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card success animate-slide-up-delay-1 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-success"><?= $roleStats["active_users"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Active Users</p>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle p-3">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card warning animate-slide-up-delay-2 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-warning"><?= $roleStats["total_departments"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Departments</p>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card info animate-slide-up-delay-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-info"><?= $roleStats["total_skills"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Total Skills</p>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle p-3">
                        <i class="fas fa-cogs"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6 col-lg-12">
            <div class="card animate-slide-up-delay-1 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-chart-pie me-2"></i>Task Distribution
                    </div>
                    <div>
                        <span class="badge bg-white text-primary">
                            <i class="fas fa-tasks me-1"></i>
                            <?php echo (int)$stats["pending"] + (int)$stats["in_progress"] + (int)$stats["completed"]; ?>
                            Total
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <div class="chart-wrap"><canvas id="taskChart"></canvas></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-4 text-center">
                            <div class="stat-number text-warning"><?= $stats["pending"] ?></div>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="stat-number text-info"><?= $stats["in_progress"] ?></div>
                            <small class="text-muted">In Progress</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="stat-number text-success"><?= $stats["completed"] ?></div>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-12">
            <div class="card animate-slide-up-delay-2 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-rocket me-2"></i>Quick Actions
                    </div>
                    <div>
                        <small class="text-white-50">Navigate to key features</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?= e(url('users.php')) ?>" class="btn btn-primary w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-users me-2"></i>
                                <span>Manage Users</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= e(url('departments.php')) ?>" class="btn btn-success w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-building me-2"></i>
                                <span>Departments</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= e(url('settings.php')) ?>" class="btn btn-warning w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-cog me-2"></i>
                                <span>Settings</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= e(url('reports_center.php')) ?>" class="btn btn-info w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-chart-line me-2"></i>
                                <span>Reports</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-history me-2"></i>Recent System Activities
            </div>
            <div>
                <span class="badge bg-white text-primary px-3 py-2">
                    <i class="fas fa-list me-1"></i>
                    <?php echo count($roleStats["recent_activities"]); ?>
                    Activities
                </span>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($roleStats["recent_activities"])): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No recent activities found</p>
                    <small class="text-muted">System activities will appear here</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="border-0"><i class="fas fa-tag me-2"></i>Action</th>
                                <th class="border-0"><i class="fas fa-file-alt me-2"></i>Description</th>
                                <th class="border-0"><i class="fas fa-clock me-2"></i>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roleStats["recent_activities"] as $activity): ?>
                                <tr class="hover-lift">
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-bolt me-1"></i>
                                            <?= e($activity["action"]) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?= e($activity["description"] ?? '<em class="text-muted">No description</em>') ?></div>
                                    </td>
                                    <td>
                                        <div class="text-muted small">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= e($activity["created_at"]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($user["role"] === "manager"): ?>
    <!-- MANAGER DASHBOARD -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card animate-slide-up h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-primary"><?= $roleStats["team_size"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Team Members</p>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card warning animate-slide-up-delay-1 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-warning"><?= $roleStats["team_pending"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Pending Tasks</p>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card success animate-slide-up-delay-2 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-success"><?= $roleStats["team_completed"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Completed Tasks</p>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle p-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card info animate-slide-up-delay-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-info"><?= $roleStats["avg_completion"] ?>%</h3>
                        <p class="mb-0 text-muted small text-uppercase">Avg Completion</p>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle p-3">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6 col-lg-12">
            <div class="card animate-slide-up-delay-1 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-chart-pie me-2"></i>Team Task Distribution
                    </div>
                    <div>
                        <span class="badge bg-white text-primary">
                            <i class="fas fa-tasks me-1"></i>
                            <?php echo (int)$stats["pending"] + (int)$stats["in_progress"] + (int)$stats["completed"]; ?>
                            Total
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <div class="chart-wrap"><canvas id="taskChart"></canvas></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-4 text-center">
                            <div class="stat-number text-warning"><?= $stats["pending"] ?></div>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="stat-number text-info"><?= $stats["in_progress"] ?></div>
                            <small class="text-muted">In Progress</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="stat-number text-success"><?= $stats["completed"] ?></div>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-12">
            <div class="card animate-slide-up-delay-2 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-rocket me-2"></i>Quick Actions
                    </div>
                    <div>
                        <small class="text-white-50">Manage team efficiently</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?= e(url('tasks.php')) ?>" class="btn btn-primary w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-tasks me-2"></i>
                                <span>Assign Tasks</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= e(url('team_progress.php')) ?>" class="btn btn-success w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-chart-line me-2"></i>
                                <span>Team Progress</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= e(url('performance.php')) ?>" class="btn btn-info w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-star me-2"></i>
                                <span>Reviews</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= e(url('reports_center.php')) ?>" class="btn btn-warning w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-chart-bar me-2"></i>
                                <span>Reports</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-clock me-2"></i>Recent Team Tasks
        </div>
        <div class="card-body">
            <?php if (empty($roleStats["recent_tasks"])): ?>
                <p class="text-muted">No recent tasks.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roleStats["recent_tasks"] as $task): ?>
                                <tr>
                                    <td><?= e($task["title"]) ?></td>
                                    <td><?= e($task["assignee"]) ?></td>
                                    <td>
                                        <span class="badge <?php echo match($task["status"]) {
                                            'pending' => 'badge-warning',
                                            'in_progress' => 'badge-info',
                                            'completed' => 'badge-success',
                                            default => 'badge-secondary'
                                        }; ?>"><?= ucfirst(str_replace('_', ' ', $task["status"])) ?></span>
                                    </td>
                                    <td><?= e($task["created_at"]) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- EMPLOYEE DASHBOARD -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card animate-slide-up h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-primary"><?= $roleStats["total_tasks"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Total Tasks</p>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card success animate-slide-up-delay-1 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-success"><?= $roleStats["my_skills"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">My Skills</p>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle p-3">
                        <i class="fas fa-bullseye"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card warning animate-slide-up-delay-2 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-warning"><?= $roleStats["avg_rating"] ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Avg Rating</p>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card info animate-slide-up-delay-3 h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-1 fw-bold text-info"><?php echo $stats["completed"]; ?></h3>
                        <p class="mb-0 text-muted small text-uppercase">Completed</p>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle p-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-6 col-lg-12">
            <div class="card animate-slide-up-delay-1 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-chart-pie me-2"></i>My Task Status
                    </div>
                    <div>
                        <span class="badge bg-white text-primary">
                            <i class="fas fa-tasks me-1"></i>
                            <?php echo (int)$stats["pending"] + (int)$stats["in_progress"] + (int)$stats["completed"]; ?>
                            Total
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <div class="chart-wrap"><canvas id="taskChart"></canvas></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-4 text-center">
                            <div class="stat-number text-warning"><?= $stats["pending"] ?></div>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="stat-number text-info"><?= $stats["in_progress"] ?></div>
                            <small class="text-muted">In Progress</small>
                        </div>
                        <div class="col-4 text-center">
                            <div class="stat-number text-success"><?= $stats["completed"] ?></div>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-lg-12">
            <div class="card animate-slide-up-delay-2 h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-rocket me-2"></i>Quick Actions
                    </div>
                    <div>
                        <small class="text-white-50">Manage your work</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?= e(url('tasks.php')) ?>" class="btn btn-primary w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-tasks me-2"></i>
                                <span>View Tasks</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= e(url('skills.php')) ?>" class="btn btn-success w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-bullseye me-2"></i>
                                <span>My Skills</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= e(url('performance.php')) ?>" class="btn btn-info w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-star me-2"></i>
                                <span>Performance</span>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= e(url('profile.php')) ?>" class="btn btn-warning w-100 d-flex align-items-center justify-content-center p-3">
                                <i class="fas fa-user me-2"></i>
                                <span>Profile</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-clock me-2"></i>My Recent Tasks
        </div>
        <div class="card-body">
            <?php if (empty($roleStats["recent_tasks"])): ?>
                <p class="text-muted">No recent tasks.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Status</th>
                                <th>Deadline</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roleStats["recent_tasks"] as $task): ?>
                                <tr>
                                    <td><?= e($task["title"]) ?></td>
                                    <td>
                                        <span class="badge <?php echo match($task["status"]) {
                                            'pending' => 'badge-warning',
                                            'in_progress' => 'badge-info',
                                            'completed' => 'badge-success',
                                            default => 'badge-secondary'
                                        }; ?>"><?= ucfirst(str_replace('_', ' ', $task["status"])) ?></span>
                                    </td>
                                    <td><?= e($task["deadline"] ?? '-') ?></td>
                                    <td><?= e($task["created_at"]) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();

$loadChart = true;
$scripts = <<<SCRIPT
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('taskChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Completed'],
                datasets: [{
                    label: 'Tasks',
                    data: [{$stats["pending"]}, {$stats["in_progress"]}, {$stats["completed"]}],
                    backgroundColor: ['#F59E0B', '#3B82F6', '#10B981'],
                    borderColor: ['#D97706', '#2563EB', '#059669'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16, font: { size: 13 } }
                    }
                }
            }
        });
    }
});
</script>
SCRIPT;

require_once __DIR__ . "/../app/views/layouts/main.php";
