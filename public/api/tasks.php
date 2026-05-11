<?php
declare(strict_types=1);
require_once __DIR__ . "/../../app/helpers/session.php";
require_once __DIR__ . "/../../config/database.php";
require_auth();
header("Content-Type: application/json");
$user = current_user();

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if ($user["role"] === "employee") {
        $stmt = $mysqli->prepare("SELECT id,title,status,priority,deadline FROM tasks WHERE assigned_to=? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user["id"]);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $rows = $mysqli->query("SELECT id,title,status,priority,deadline FROM tasks ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
    }
    echo json_encode(["tasks" => $rows]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
