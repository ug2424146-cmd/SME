<?php
declare(strict_types=1);
require_once __DIR__ . "/../../app/helpers/session.php";
require_once __DIR__ . "/../../config/database.php";
require_role(["admin"]);
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $rows = $mysqli->query(
        "SELECT u.id, u.name, u.email, u.is_active, COALESCE(r.role_name,u.role) role
         FROM users u LEFT JOIN roles r ON r.id = u.role_id ORDER BY u.created_at DESC"
    )->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["users" => $rows]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
