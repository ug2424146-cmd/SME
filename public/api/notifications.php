<?php
declare(strict_types=1);
require_once __DIR__ . "/../../app/helpers/session.php";
require_once __DIR__ . "/../../config/database.php";
require_auth();
header("Content-Type: application/json");
$user = current_user();

$stmt = $mysqli->prepare("SELECT id,title,message,is_read,created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
$stmt->bind_param("i", $user["id"]);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
echo json_encode(["notifications" => $rows]);
