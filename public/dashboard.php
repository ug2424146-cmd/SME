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
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - SME Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(url('../assets/css/style.css')) ?>?v=<?= time() ?>" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%) !important; }
        .stat-card { 
            background: white !important; 
            border-radius: 12px !important; 
            padding: 1.75rem !important; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
            border-left: 4px solid #4F46E5 !important;
            transition: all 0.3s ease !important;
        }
        .stat-card:hover { 
            transform: translateY(-5px) !important; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
        }
        .stat-card.success { border-left-color: #10B981 !important; }
        .stat-card.warning { border-left-color: #F59E0B !important; }
        .stat-card.info { border-left-color: #3B82F6 !important; }
        .stat-card h3 { 
            font-size: 2.5rem !important; 
            font-weight: 800 !important; 
            color: #4F46E5 !important;
            margin-bottom: 0.5rem !important;
        }
        .stat-card.success h3 { color: #10B981 !important; }
        .stat-card.warning h3 { color: #F59E0B !important; }
        .stat-card.info h3 { color: #3B82F6 !important; }
        .stat-card p { 
            font-weight: 600 !important; 
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #6B7280 !important;
            margin: 0 !important;
        }
        .card { 
            border-radius: 12px !important; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
            border: none !important;
        }
        .card-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            font-weight: 600 !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 1rem 1.5rem !important;
        }
        .btn {
            border-radius: 8px !important;
            font-weight: 600 !important;
            padding: 0.75rem 1.5rem !important;
            transition: all 0.3s ease !important;
        }
        .main-content { 
            margin-left: 280px !important; 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%) !important; 
            min-height: 100vh !important; 
            padding-top: 0 !important;
        }
        .main-content > main {
            padding-top: 2rem !important;
        }
        .btn-success { background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important; border: none !important; }
        .btn-info { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%) !important; border: none !important; }
        .btn-warning { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%) !important; border: none !important; }
        .btn:hover { transform: translateY(-2px) !important; box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important; }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
            padding: 2.5rem 0 !important;
            border-radius: 12px !important;
            margin-bottom: 2rem !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .page-header h1 { font-weight: 800 !important; font-size: 2.25rem !important; }
        .sidebar {
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
        }
        .sidebar-brand { color: white !important; font-weight: 700 !important; }
        .sidebar-link { color: rgba(255,255,255,0.85) !important; }
        .sidebar-link:hover { background: rgba(255,255,255,0.15) !important; color: white !important; }
        .sidebar-link.active { background: rgba(255,255,255,0.25) !important; color: white !important; }
        .table thead th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; color: white !important; }
    </style>
</head>
<body class="bg-light">
<div class="sidebar-wrapper">
    <!-- Sidebar -->
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
            <a href="<?= e(url('dashboard.php')) ?>" class="sidebar-link active">
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
    
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        ☰ Menu
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1><?= ucfirst($user["role"]) ?> Dashboard</h1>
                <p>Welcome back, <?= e($user["name"]) ?>! Here's your overview.</p>
            </div>
        </div>

        <main class="container py-4">
    <?php if ($user["role"] === "admin"): ?>
        <!-- ADMIN DASHBOARD -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card animate-slide-up">
                    <h3><?= $roleStats["total_users"] ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success animate-slide-up-delay-1">
                    <h3><?= $roleStats["active_users"] ?></h3>
                    <p>Active Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning animate-slide-up-delay-2">
                    <h3><?= $roleStats["total_departments"] ?></h3>
                    <p>Departments</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info animate-slide-up-delay-3">
                    <h3><?= $roleStats["total_skills"] ?></h3>
                    <p>Total Skills</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card animate-slide-up-delay-1">
                    <div class="card-header">
                        Task Overview
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h4 text-warning"><?= $stats["pending"] ?></div>
                                <small class="text-muted">Pending</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-info"><?= $stats["in_progress"] ?></div>
                                <small class="text-muted">In Progress</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-success"><?= $stats["completed"] ?></div>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card animate-slide-up-delay-2">
                    <div class="card-header">
                        Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <a href="<?= e(url('users.php')) ?>" class="btn btn-primary w-100 pulse-on-hover">
                                    👥 Manage Users
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?= e(url('departments.php')) ?>" class="btn btn-success w-100 pulse-on-hover">
                                    🏢 Departments
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?= e(url('settings.php')) ?>" class="btn btn-warning w-100 pulse-on-hover">
                                    ⚙️ Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Recent System Activities
            </div>
            <div class="card-body">
                <?php if (empty($roleStats["recent_activities"])): ?>
                    <p class="text-muted">No recent activities.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roleStats["recent_activities"] as $activity): ?>
                                    <tr>
                                        <td><span class="badge badge-primary"><?= e($activity["action"]) ?></span></td>
                                        <td><?= e($activity["description"] ?? '-') ?></td>
                                        <td><?= e($activity["created_at"]) ?></td>
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
            <div class="col-md-3">
                <div class="stat-card animate-slide-up">
                    <h3><?= $roleStats["team_size"] ?></h3>
                    <p>Team Members</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning animate-slide-up-delay-1">
                    <h3><?= $roleStats["team_pending"] ?></h3>
                    <p>Pending Tasks</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success animate-slide-up-delay-2">
                    <h3><?= $roleStats["team_completed"] ?></h3>
                    <p>Completed Tasks</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info animate-slide-up-delay-3">
                    <h3><?= $roleStats["avg_completion"] ?>%</h3>
                    <p>Avg Completion</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card animate-slide-up-delay-1">
                    <div class="card-header">
                        Task Distribution
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h4 text-warning"><?= $stats["pending"] ?></div>
                                <small class="text-muted">Pending</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-info"><?= $stats["in_progress"] ?></div>
                                <small class="text-muted">In Progress</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-success"><?= $stats["completed"] ?></div>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card animate-slide-up-delay-2">
                    <div class="card-header">
                        Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <a href="<?= e(url('tasks.php')) ?>" class="btn btn-primary w-100 pulse-on-hover">
                                    📋 Assign Tasks
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?= e(url('team_progress.php')) ?>" class="btn btn-success w-100 pulse-on-hover">
                                    📊 Team Progress
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?= e(url('performance.php')) ?>" class="btn btn-info w-100 pulse-on-hover">
                                    ⭐ Reviews
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?= e(url('reports_center.php')) ?>" class="btn btn-warning w-100 pulse-on-hover">
                                    📈 Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Recent Team Tasks
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
                                            <?php
                                            $statusClass = match($task["status"]) {
                                                'pending' => 'status-pending',
                                                'in_progress' => 'status-in-progress',
                                                'completed' => 'status-completed',
                                                default => ''
                                            };
                                            ?>
                                            <span class="<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $task["status"])) ?></span>
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
            <div class="col-md-3">
                <div class="stat-card animate-slide-up">
                    <h3><?= $roleStats["total_tasks"] ?></h3>
                    <p>Total Tasks</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card warning animate-slide-up-delay-1">
                    <h3><?= $stats["pending"] ?></h3>
                    <p>Pending</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card success animate-slide-up-delay-2">
                    <h3><?= $roleStats["my_skills"] ?></h3>
                    <p>My Skills</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card info animate-slide-up-delay-3">
                    <h3><?= $roleStats["avg_rating"] ?> ⭐</h3>
                    <p>Avg Rating</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="card animate-slide-up-delay-1">
                    <div class="card-header">
                        My Tasks Overview
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h4 text-warning"><?= $stats["pending"] ?></div>
                                <small class="text-muted">Pending</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-info"><?= $stats["in_progress"] ?></div>
                                <small class="text-muted">In Progress</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-success"><?= $stats["completed"] ?></div>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card animate-slide-up-delay-2">
                    <div class="card-header">
                        Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?= e(url('tasks.php')) ?>" class="btn btn-primary pulse-on-hover">
                                📋 My Tasks
                            </a>
                            <a href="<?= e(url('skills.php')) ?>" class="btn btn-success pulse-on-hover">
                                🎯 My Skills
                            </a>
                            <a href="<?= e(url('performance.php')) ?>" class="btn btn-info pulse-on-hover">
                                ⭐ My Performance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                My Recent Tasks
            </div>
            <div class="card-body">
                <?php if (empty($roleStats["recent_tasks"])): ?>
                    <p class="text-muted">No tasks assigned yet.</p>
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
                                            <?php
                                            $statusClass = match($task["status"]) {
                                                'pending' => 'status-pending',
                                                'in_progress' => 'status-in-progress',
                                                'completed' => 'status-completed',
                                                default => ''
                                            };
                                            ?>
                                            <span class="<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $task["status"])) ?></span>
                                        </td>
                                        <td><?= e($task["deadline"] ?? 'No deadline') ?></td>
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
        </main>

        <!-- Chart Section -->
        <div class="container pb-4">
            <div class="card">
                <div class="card-header">
                    Task Statistics Chart
                </div>
                <div class="card-body">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}

const ctx = document.getElementById('taskChart').getContext('2d');
new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ['Pending', 'In Progress', 'Completed'],
    datasets: [{
      label: 'Tasks',
      data: [<?= (int) $stats["pending"] ?>, <?= (int) $stats["in_progress"] ?>, <?= (int) $stats["completed"] ?>],
      backgroundColor: [
        '#F59E0B',
        '#3B82F6',
        '#10B981'
      ],
      borderColor: [
        '#D97706',
        '#2563EB',
        '#059669'
      ],
      borderWidth: 2
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          padding: 20,
          font: {
            size: 14
          }
        }
      },
      title: {
        display: true,
        text: '<?= ucfirst($user["role"]) ?> Task Distribution',
        font: {
          size: 18,
          weight: 'bold'
        },
        padding: 20
      }
    },
    animation: {
      animateScale: true,
      animateRotate: true
    }
  }
});
</script>
</body>
</html>
