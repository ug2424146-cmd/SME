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
// Compute system-driven average rating across employees
if (count($employees) > 0) {
    $sum = 0.0;
    foreach ($employees as $e) {
        $sum += compute_user_rating((int) $e["id"]);
    }
    $avgRating = round($sum / count($employees), 2);
}

$pageTitle = 'Performance Reviews';
$pageSubtitle = 'Track and manage employee performance';
$currentPage = 'performance';

ob_start();
?>
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="fas fa-check-circle me-2"></i><?= e($successMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="fas fa-exclamation-circle me-2"></i><?= e($errorMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <div class="card border-0 shadow-sm rounded-2xl flex-1">
        <div class="card-body p-4 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 rounded-lg p-3">
                <i class="fas fa-star text-primary" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <span class="text-muted small fw-semibold uppercase">Average Rating</span>
                <div class="fw-bold" style="font-size: 1.75rem; line-height: 1.2;"><?= e((string) $avgRating) ?> <span class="text-muted small fw-normal">/ 5</span></div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm rounded-2xl flex-1">
        <div class="card-body p-4 d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 rounded-lg p-3">
                <i class="fas fa-file-alt text-success" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <span class="text-muted small fw-semibold uppercase">Total Reviews</span>
                <div class="fw-bold" style="font-size: 1.75rem; line-height: 1.2;"><?= count($reviews) ?></div>
            </div>
        </div>
    </div>
</div>

<?php if (in_array($user["role"], ["manager", "admin"], true)): ?>
    <div class="card border-0 shadow-sm rounded-2xl mb-4">
        <div class="card-header bg-white border-bottom-0 px-4 pt-4 pb-0">
            <h5 class="fw-bold mb-0"><i class="fas fa-robot text-primary me-2"></i>System-calculated Ratings</h5>
        </div>
        <div class="card-body px-4 pb-4 pt-3">
            <p class="small text-muted mb-3">Ratings are automatically computed by the system based on task outcomes and skill proficiency. Manual rating submission has been disabled to ensure consistent, objective measures.</p>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold px-4">Employee</th>
                            <th class="fw-semibold">Computed Rating</th>
                            <th class="fw-semibold">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ratings = [];
                        foreach ($employees as $e) {
                            $r = compute_user_rating((int)$e['id']);
                            $ratings[] = ['id' => $e['id'], 'name' => $e['name'], 'rating' => $r];
                        }
                        usort($ratings, function($a, $b) { return $b['rating'] <=> $a['rating']; });
                        foreach ($ratings as $row):
                        ?>
                            <tr>
                                <td class="px-4 fw-medium"><?= e($row['name']) ?></td>
                                <td>
                                    <?php $rating = (float)$row['rating']; $full = floor($rating); ?>
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $full ? ' text-warning' : ' text-muted' ?>" style="font-size: 0.75rem;"></i>
                                    <?php endfor; ?>
                                    <span class="ms-1 fw-bold small"><?= e((string)$rating) ?></span>
                                </td>
                                <td class="text-muted small">Computed from tasks and skills</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($reviews)): ?>
    <div class="card border-0 shadow-sm rounded-2xl">
        <div class="card-body text-center py-5">
            <i class="fas fa-star text-muted" style="font-size: 2.5rem; opacity: 0.4;"></i>
            <h6 class="fw-bold mt-3 mb-2">No Reviews Yet</h6>
            <p class="text-muted small mb-0">Performance reviews will appear here.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-2xl">
        <div class="card-header bg-white border-bottom-0 px-4 pt-4 pb-0">
            <h5 class="fw-bold mb-0"><i class="fas fa-list text-primary me-2"></i>Review History</h5>
        </div>
        <div class="card-body px-4 pb-4 pt-3 p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold px-4">Employee</th>
                            <th class="fw-semibold">Reviewer</th>
                            <th class="fw-semibold">Rating</th>
                            <th class="fw-semibold">Feedback</th>
                            <th class="fw-semibold pe-4">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reviews as $r): ?>
                            <tr>
                                <td class="px-4 fw-medium"><?= e($r["employee"]) ?></td>
                                <td class="text-muted"><?= e($r["reviewer"]) ?></td>
                                <td>
                                    <?php $rating = (int)$r["rating"]; ?>
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $rating ? ' text-warning' : ' text-muted' ?>" style="font-size: 0.75rem;"></i>
                                    <?php endfor; ?>
                                    <span class="ms-1 fw-bold small"><?= e((string)$r["rating"]) ?></span>
                                </td>
                                <td class="text-muted small"><?= e((string)$r["feedback"]) ?: '<em class="text-muted">No feedback</em>' ?></td>
                                <td class="text-muted small pe-4"><?= e($r["review_date"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
$scripts = '';
require_once __DIR__ . "/../app/views/layouts/main.php";
