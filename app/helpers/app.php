<?php

declare(strict_types=1);

require_once __DIR__ . "/../../config/database.php";

function log_activity(?int $userId, string $action, ?string $description = null): void
{
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $action, $description);
    $stmt->execute();
    $stmt->close();
}

function create_notification(int $userId, string $title, string $message): void
{
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO notifications (user_id, title, message, is_read) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("iss", $userId, $title, $message);
    $stmt->execute();
    $stmt->close();
}

function role_id_by_name(string $roleName): ?int
{
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id FROM roles WHERE role_name = ? LIMIT 1");
    $stmt->bind_param("s", $roleName);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int) $row["id"] : null;
}

/**
 * Compute an automated performance rating for a user (1-5) based on recent task outcomes and skill proficiency.
 */
function compute_user_rating(int $userId, bool $detailed = false)
{
    global $mysqli;

    // Tunable weights via environment variables (values between 0..1, sum ideally to 1)
    $wCompletion = (float) (getenv('RATING_WEIGHT_COMPLETION') ?: 0.6);
    $wTimeliness = (float) (getenv('RATING_WEIGHT_TIMELINESS') ?: 0.3);
    $wProficiency = (float) (getenv('RATING_WEIGHT_PROFICIENCY') ?: 0.1);

    // Assigned tasks and completed tasks
    $stmt = $mysqli->prepare("SELECT COUNT(*) c FROM tasks WHERE assigned_to = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $assigned = (int) ($stmt->get_result()->fetch_assoc()["c"] ?? 0);
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT COUNT(*) c FROM tasks WHERE assigned_to = ? AND status = 'completed'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $completed = (int) ($stmt->get_result()->fetch_assoc()["c"] ?? 0);
    $stmt->close();

    // Late completions: status_updated -> completed event after deadline
    $stmt = $mysqli->prepare(
        "SELECT COUNT(DISTINCT t.id) c
         FROM tasks t
         INNER JOIN task_history th ON th.task_id = t.id AND th.action = 'status_updated' AND th.details LIKE '%completed%'
         WHERE t.assigned_to = ? AND t.deadline IS NOT NULL AND DATE(th.created_at) > t.deadline"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $late = (int) ($stmt->get_result()->fetch_assoc()["c"] ?? 0);
    $stmt->close();

    // Average proficiency across employee skills (map to 1..5)
    $stmt = $mysqli->prepare(
        "SELECT AVG(CASE es.proficiency_level WHEN 'expert' THEN 5 WHEN 'intermediate' THEN 3 ELSE 1 END) avgp
         FROM employee_skills es WHERE es.user_id = ?"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $avgProf = (float) ($stmt->get_result()->fetch_assoc()["avgp"] ?? 0.0);
    $stmt->close();

    // Normalize components to 0..1
    $completionScore = $assigned > 0 ? min(1.0, $completed / max(1, $assigned)) : 1.0;
    $timelinessScore = $completed > 0 ? max(0.0, 1.0 - ($late / max(1, $completed))) : 1.0;
    $proficiencyScore = $avgProf > 0 ? ($avgProf - 1.0) / 4.0 : 0.5;

    // Weighted aggregate
    $combined = ($completionScore * $wCompletion) + ($timelinessScore * $wTimeliness) + ($proficiencyScore * $wProficiency);

    // Map to 1..5
    $rating = round(1 + 4 * $combined, 2);
    if ($rating < 1) $rating = 1;
    if ($rating > 5) $rating = 5;

    if ($detailed) {
        return [
            'assigned' => $assigned,
            'completed' => $completed,
            'late' => $late,
            'avg_proficiency' => round($avgProf, 2),
            'completion_score' => round($completionScore, 3),
            'timeliness_score' => round($timelinessScore, 3),
            'proficiency_score' => round($proficiencyScore, 3),
            'weights' => ['completion' => $wCompletion, 'timeliness' => $wTimeliness, 'proficiency' => $wProficiency],
            'rating' => $rating,
        ];
    }

    return $rating;
}
