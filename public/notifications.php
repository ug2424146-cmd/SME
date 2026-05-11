<?php

declare(strict_types=1);

require_once __DIR__ . "/../app/helpers/session.php";
require_once __DIR__ . "/../config/database.php";

require_auth();
header("Content-Type: application/json");
$user = current_user();
$action = $_GET["action"] ?? "list";

if (($action === "mark_read" || $action === "mark_unread") && $_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token($_POST["csrf_token"] ?? null)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid CSRF token"]);
        exit;
    }
    $notificationId = (int) ($_POST["id"] ?? 0);
    $isRead = $action === "mark_read" ? 1 : 0;
    $stmt = $mysqli->prepare("UPDATE notifications SET is_read = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $isRead, $notificationId, $user["id"]);
    $stmt->execute();
    $stmt->close();
    echo json_encode(["ok" => true]);
    exit;
}

$stmt = $mysqli->prepare(
    "SELECT id, title, message, is_read, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 10"
);
$stmt->bind_param("i", $user["id"]);
$stmt->execute();
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

echo json_encode(["notifications" => $rows]);
