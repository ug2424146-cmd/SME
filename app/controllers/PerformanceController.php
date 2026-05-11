<?php

declare(strict_types=1);

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../helpers/session.php";
require_once __DIR__ . "/../helpers/app.php";

class PerformanceController
{
    public function createReview(array $data, int $reviewerId): void
    {
        $userId = (int) ($data["user_id"] ?? 0);
        $rating = (int) ($data["rating"] ?? 0);
        $feedback = trim((string) ($data["feedback"] ?? ""));
        if ($userId <= 0 || $rating < 1 || $rating > 5) {
            flash("error", "Invalid review values.");
            return;
        }
        global $mysqli;
        $stmt = $mysqli->prepare(
            "INSERT INTO performance (user_id, reviewer_id, rating, feedback, review_date) VALUES (?, ?, ?, ?, CURDATE())"
        );
        $stmt->bind_param("iiis", $userId, $reviewerId, $rating, $feedback);
        $stmt->execute();
        $stmt->close();
        create_notification($userId, "Performance Review", "A new performance review has been submitted.");
        log_activity($reviewerId, "performance_review_create", "Created review for user #" . $userId);
        flash("success", "Review submitted.");
    }
}
