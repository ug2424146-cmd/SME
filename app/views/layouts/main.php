<?php
/**
 * Main Layout Template
 * Includes sidebar, topbar, responsive shell, footer
 */
$loadChart = $loadChart ?? false;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle ?? 'SME Platform', ENT_QUOTES, 'UTF-8'); ?> - SME Platform</title>
    <link href="<?php echo url("assets/vendor/bootstrap.min.css"); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="<?php echo url('assets/css/style.css'); ?>?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="<?php echo url('assets/css/responsive.css'); ?>?v=<?php echo time(); ?>" rel="stylesheet">
    <?php if ($loadChart): ?>
    <script src="<?php echo url("assets/vendor/chart.umd.min.js"); ?>"></script>
    <?php endif; ?>
</head>
<body class="bg-light app-layout">
    <button type="button" class="sidebar-toggle-btn d-lg-none" id="sidebarToggle" aria-label="Open menu">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar-wrapper">
        <aside class="sidebar" id="sidebar" aria-label="Main navigation">
            <div class="sidebar-header">
                <a href="<?php echo url('dashboard.php'); ?>" class="sidebar-brand">
                    <i class="fas fa-building"></i> SME Platform
                </a>
            </div>

            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="sidebar-user-name"><?php echo e($user["name"]); ?></div>
                <div class="sidebar-user-role"><?php echo e(ucfirst($user["role"])); ?></div>
            </div>

            <nav class="sidebar-nav">
                <div class="sidebar-section">Main Menu</div>
                <a href="<?php echo url('dashboard.php'); ?>" class="sidebar-link <?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo url('tasks.php'); ?>" class="sidebar-link <?php echo ($currentPage ?? '') === 'tasks' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i>
                    <span>Tasks</span>
                </a>
                <a href="<?php echo url('skills.php'); ?>" class="sidebar-link <?php echo ($currentPage ?? '') === 'skills' ? 'active' : ''; ?>">
                    <i class="fas fa-bullseye"></i>
                    <span>Skills</span>
                </a>
                <a href="<?php echo url('performance.php'); ?>" class="sidebar-link <?php echo ($currentPage ?? '') === 'performance' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i>
                    <span>Performance</span>
                </a>

                <?php if ($user["role"] !== "employee"): ?>
                    <div class="sidebar-section mt-4">Management</div>
                    <a href="<?php echo url('team_progress.php'); ?>" class="sidebar-link <?php echo ($currentPage ?? '') === 'team_progress' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span>Team Progress</span>
                    </a>
                <?php endif; ?>

                <div class="sidebar-section mt-4">Reports</div>
                <a href="<?php echo url('reports_center.php'); ?>" class="sidebar-link <?php echo ($currentPage ?? '') === 'reports' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>

                <?php if ($user["role"] === "admin"): ?>
                    <div class="sidebar-section mt-4">Administration</div>
                    <a href="<?php echo url('users.php'); ?>" class="sidebar-link <?php echo ($currentPage ?? '') === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users-cog"></i>
                        <span>Users</span>
                    </a>
                    <a href="<?php echo url('departments.php'); ?>" class="sidebar-link <?php echo ($currentPage ?? '') === 'departments' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i>
                        <span>Departments</span>
                    </a>
                    <a href="<?php echo url('settings.php'); ?>" class="sidebar-link <?php echo ($currentPage ?? '') === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="<?php echo url('notifications_view.php'); ?>" class="sidebar-footer-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
                <a href="<?php echo url('profile.php'); ?>" class="sidebar-footer-link">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
                <a href="<?php echo url('logout.php'); ?>" class="sidebar-footer-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

        <div class="main-content">
            <?php $cp = $currentPage ?? ''; ?>
            <nav class="app-topbar" aria-label="Primary navigation">
                <div class="app-topbar-inner">
                    <div class="app-topbar-scroll">
                        <a href="<?php echo url('dashboard.php'); ?>" class="app-topbar-link <?php echo $cp === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt" aria-hidden="true"></i><span>Dashboard</span>
                        </a>
                        <a href="<?php echo url('tasks.php'); ?>" class="app-topbar-link <?php echo $cp === 'tasks' ? 'active' : ''; ?>">
                            <i class="fas fa-tasks" aria-hidden="true"></i><span>Tasks</span>
                        </a>
                        <a href="<?php echo url('skills.php'); ?>" class="app-topbar-link <?php echo $cp === 'skills' ? 'active' : ''; ?>">
                            <i class="fas fa-bullseye" aria-hidden="true"></i><span>Skills</span>
                        </a>
                        <a href="<?php echo url('performance.php'); ?>" class="app-topbar-link <?php echo $cp === 'performance' ? 'active' : ''; ?>">
                            <i class="fas fa-star" aria-hidden="true"></i><span>Performance</span>
                        </a>
                        <?php if ($user["role"] !== "employee"): ?>
                            <a href="<?php echo url('team_progress.php'); ?>" class="app-topbar-link <?php echo $cp === 'team_progress' ? 'active' : ''; ?>">
                                <i class="fas fa-users" aria-hidden="true"></i><span>Team</span>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo url('reports_center.php'); ?>" class="app-topbar-link <?php echo $cp === 'reports' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line" aria-hidden="true"></i><span>Reports</span>
                        </a>
                        <?php if ($user["role"] === "admin"): ?>
                            <a href="<?php echo url('users.php'); ?>" class="app-topbar-link <?php echo $cp === 'users' ? 'active' : ''; ?>">
                                <i class="fas fa-users-cog" aria-hidden="true"></i><span>Users</span>
                            </a>
                            <a href="<?php echo url('departments.php'); ?>" class="app-topbar-link <?php echo $cp === 'departments' ? 'active' : ''; ?>">
                                <i class="fas fa-building" aria-hidden="true"></i><span>Departments</span>
                            </a>
                            <a href="<?php echo url('settings.php'); ?>" class="app-topbar-link <?php echo $cp === 'settings' ? 'active' : ''; ?>">
                                <i class="fas fa-cog" aria-hidden="true"></i><span>Settings</span>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="app-topbar-actions">
                        <button type="button" class="theme-toggle-btn" aria-label="Toggle dark mode" title="Theme">
                            <i class="fas fa-moon"></i>
                        </button>
                        <a href="<?php echo url('notifications_view.php'); ?>" class="app-topbar-icon <?php echo $cp === 'notifications' ? 'active' : ''; ?>" title="Notifications" aria-label="Notifications">
                            <i class="fas fa-bell" aria-hidden="true"></i>
                        </a>
                        <a href="<?php echo url('profile.php'); ?>" class="app-topbar-icon <?php echo $cp === 'profile' ? 'active' : ''; ?>" title="My profile" aria-label="My profile">
                            <i class="fas fa-user" aria-hidden="true"></i>
                        </a>
                        <a href="<?php echo url('logout.php'); ?>" class="app-topbar-logout" title="Log out"><span>Logout</span></a>
                    </div>
                </div>
            </nav>

            <header class="page-header">
                <div class="container-fluid">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h1 class="page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                            <p class="page-subtitle"><?php echo $pageSubtitle ?? 'Welcome back, ' . e($user["name"]); ?></p>
                        </div>
                        <div class="col-auto">
                            <div class="header-actions">
                                <?php echo $headerActions ?? ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="container-fluid py-4 app-shell-main">
                <?php echo $content ?? ''; ?>
            </main>

            <footer class="app-footer">
                <div class="container-fluid app-footer-inner">
                    <span>&copy; <?php echo date('Y'); ?> SME Platform</span>
                    <span class="text-muted-small">Responsive HR &amp; task management</span>
                </div>
            </footer>
        </div>
    </div>

    <button type="button" id="scrollTopBtn" class="scroll-top-btn" aria-label="Scroll to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="<?php echo url('assets/vendor/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo url('assets/js/app.js'); ?>?v=<?php echo time(); ?>"></script>
    <?php echo $scripts ?? ''; ?>
</body>
</html>
