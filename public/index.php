<?php

declare(strict_types=1);

require_once __DIR__ . "/../app/helpers/session.php";

if (current_user() !== null) {
    header("Location: " . url("dashboard.php"));
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SME Platform - Home</title>
    <link href="<?php echo url("assets/vendor/bootstrap.min.css"); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="<?php echo url('assets/css/style.css'); ?>" rel="stylesheet">
    <link href="<?php echo url('assets/css/responsive.css'); ?>" rel="stylesheet">
</head>
<body class="homepage-body">
    <nav class="navbar navbar-expand-lg navbar-dark homepage-navbar py-3">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-building me-2"></i>SME Platform
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#homepageNav" aria-controls="homepageNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="homepageNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                    <li class="nav-item"><a class="nav-link nav-feature" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link nav-why" href="#values">Why SME</a></li>
                    <li class="nav-item"><a class="nav-link nav-plans" href="#plans">Plans</a></li>
                    <li class="nav-item"><a class="nav-link nav-testimonial" href="#testimonial">Testimonials</a></li>
                    <li class="nav-item ms-lg-3"><a class="btn btn-outline-light btn-sm" href="login.php">Sign In</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="homepage-hero text-white d-flex align-items-center position-relative">
        <div class="container py-5">
            <div class="row align-items-center gx-5">
                <div class="col-lg-6 text-lg-start text-center">
                    <span class="badge bg-white text-primary fw-bold mb-3">SME Platform</span>
                    <h1 class="display-5 fw-bold mb-4">Empower your team with smarter performance and task management.</h1>
                    <p class="lead mb-4 text-white-75">Track progress, align skills, and deliver stronger results from one intuitive platform built for SMEs.</p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                        <a href="login.php" class="btn btn-primary btn-lg px-4 shadow">Get Started</a>
                        <a href="#features" class="btn btn-light btn-lg px-4 text-dark">View Features</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="hero-card p-4 shadow-lg">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h5 class="text-primary mb-1">Live Employee Overview</h5>
                                <p class="mb-0 text-muted">Team progress, skills and tasks in one glance.</p>
                            </div>
                            <div class="badge bg-success">Live</div>
                        </div>
                        <div class="progress mb-3" style="height: 12px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 68%;" aria-valuenow="68" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="status-box bg-light rounded-3 p-3 text-center">
                                    <i class="fas fa-tasks fa-lg text-primary mb-2"></i>
                                    <div class="fw-semibold">142 Tasks</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="status-box bg-light rounded-3 p-3 text-center">
                                    <i class="fas fa-star fa-lg text-warning mb-2"></i>
                                    <div class="fw-semibold">89 Skills</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="status-box bg-light rounded-3 p-3 text-center">
                                    <i class="fas fa-users fa-lg text-success mb-2"></i>
                                    <div class="fw-semibold">12 Teams</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="status-box bg-light rounded-3 p-3 text-center">
                                    <i class="fas fa-chart-line fa-lg text-info mb-2"></i>
                                    <div class="fw-semibold">98% Alignment</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="features" class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label text-primary">Built for growth</span>
                <h2 class="fw-bold mt-2">All the tools your SME needs in one elegant dashboard.</h2>
                <p class="text-muted mx-auto" style="max-width: 680px;">From team performance tracking to skills development and task coordination, deliver clarity, accountability and faster outcomes.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card p-4 h-100 shadow-sm rounded-4 border">
                        <div class="icon-box mb-3 bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                        <h5 class="fw-bold">Performance Insights</h5>
                        <p class="text-muted mb-0">Measure delivery clearly, identify coaching moments, and celebrate team wins.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card p-4 h-100 shadow-sm rounded-4 border">
                        <div class="icon-box mb-3 bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-tasks fa-lg"></i>
                        </div>
                        <h5 class="fw-bold">Smart Task Management</h5>
                        <p class="text-muted mb-0">Organize priorities, assign ownership and track progress with crystal-clear boards.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card p-4 h-100 shadow-sm rounded-4 border">
                        <div class="icon-box mb-3 bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-user-graduate fa-lg"></i>
                        </div>
                        <h5 class="fw-bold">Skill Development</h5>
                        <p class="text-muted mb-0">Track employee skills and align growth opportunities with business needs.</p>
                    </div>
                </div>
            </div>

            <div id="values" class="row g-4 mt-5">
                <div class="col-lg-4">
                    <div class="stats-card p-4 h-100 rounded-4 shadow-sm bg-primary text-white">
                        <h4 class="fw-bold">Stay on target</h4>
                        <p class="mb-0 text-white-75">Visualize progress, deadlines, and outcomes in one place.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="stats-card p-4 h-100 rounded-4 shadow-sm bg-success text-white">
                        <h4 class="fw-bold">Drive growth</h4>
                        <p class="mb-0 text-white-75">Use skills data to build the right team for every project.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="stats-card p-4 h-100 rounded-4 shadow-sm bg-info text-white">
                        <h4 class="fw-bold">Improve collaboration</h4>
                        <p class="mb-0 text-white-75">Make communication and accountability easy across teams.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonial" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label text-primary">Trusted by teams</span>
                <h2 class="fw-bold mt-2">Simplify your people strategy with one powerful platform.</h2>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6">
                    <div class="testimonial-card p-4 h-100 rounded-4 shadow-sm bg-white">
                        <p class="text-muted mb-4">"The SME Platform gave our managers the visibility they needed to support employees effectively. Task handoffs and performance reviews have never been easier."</p>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="fas fa-user-tie"></i></div>
                            <div>
                                <h6 class="mb-0">Amina Khalid</h6>
                                <small class="text-muted">Operations Lead</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="testimonial-card p-4 h-100 rounded-4 shadow-sm bg-white">
                        <p class="text-muted mb-4">"The platform helped us align team skills to business goals and caused our team satisfaction scores to rise quickly."</p>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;"><i class="fas fa-star"></i></div>
                            <div>
                                <h6 class="mb-0">Jamal Singh</h6>
                                <small class="text-muted">HR Manager</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="plans" class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label text-primary">Pricing made simple</span>
                <h2 class="fw-bold mt-2">Choose a plan that fits your team.</h2>
                <p class="text-muted mx-auto" style="max-width: 680px;">Flexible packages for early-stage teams, growing companies, and enterprise organizations.</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card h-100 rounded-4 shadow-sm border p-4 text-center">
                        <span class="pricing-badge mb-3">Starter</span>
                        <div class="price-value mb-3">$0 <span class="text-muted fs-6">/ month</span></div>
                        <p class="text-muted mb-4">Perfect for small teams learning the ropes.</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Up to 10 users</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Task tracking</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Basic reports</li>
                        </ul>
                        <a href="<?php echo url('login.php'); ?>" class="btn btn-outline-primary btn-sm px-4">Start free</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card pricing-card-featured h-100 rounded-4 shadow-lg p-4 text-center">
                        <span class="pricing-badge pricing-badge-featured mb-3">Business</span>
                        <div class="price-value mb-3">$49 <span class="text-muted fs-6">/ month</span></div>
                        <p class="text-muted mb-4">For teams ready to scale workflows and performance.</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="fas fa-check-circle text-white me-2"></i>Unlimited users</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-white me-2"></i>Advanced analytics</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-white me-2"></i>Skill planning</li>
                        </ul>
                        <a href="<?php echo url('login.php'); ?>" class="btn btn-light btn-sm px-4">Pick Business</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="pricing-card h-100 rounded-4 shadow-sm border p-4 text-center">
                        <span class="pricing-badge mb-3">Enterprise</span>
                        <div class="price-value mb-3">Custom</div>
                        <p class="text-muted mb-4">Tailored support and onboarding for larger organizations.</p>
                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Dedicated onboarding</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Priority support</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Custom integrations</li>
                        </ul>
                        <a href="<?php echo url('login.php'); ?>" class="btn btn-outline-primary btn-sm px-4">Contact sales</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 homepage-cta text-white">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Turn every employee into a confident contributor.</h2>
            <p class="lead mb-4 text-white-75">Fast onboarding, precise reporting, and a dashboard designed to keep your whole organization aligned.</p>
            <a href="<?php echo url('login.php'); ?>" class="btn btn-light btn-lg px-5 shadow">Start your free trial</a>
        </div>
    </section>

    <footer class="homepage-footer py-4 text-center text-white-50">
        <div class="container">
            <p class="mb-2">SME Platform · Empowering small teams with big productivity.</p>
            <p class="mb-0">&copy; <?php echo date('Y'); ?> SME Platform</p>
        </div>
    </footer>

    <script src="<?php echo url("assets/vendor/bootstrap.bundle.min.js"); ?>"></script>
</body>
</html>

