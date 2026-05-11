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
