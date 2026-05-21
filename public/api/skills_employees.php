<?php

declare(strict_types=1);

require_once __DIR__ . "/../../app/helpers/session.php";
require_once __DIR__ . "/../../config/database.php";

require_auth();
header("Content-Type: application/json; charset=utf-8");

$user = current_user();

// Only managers and admins can access this endpoint
if ($user["role"] !== "manager" && $user["role"] !== "admin") {
    http_response_code(403);
    echo json_encode(["ok" => false, "message" => "Forbidden."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["ok" => false, "message" => "Method not allowed."]);
    exit;
}

$skillId = (int) ($_GET["skill_id"] ?? 0);

if ($skillId <= 0) {
    http_response_code(400);
    echo json_encode(["ok" => false, "message" => "Invalid skill ID."]);
    exit;
}

global $mysqli;

// Get employees with the specified skill, ordered by proficiency level (expert first)
$stmt = $mysqli->prepare(
    "SELECT DISTINCT u.id, u.name, es.proficiency_level
     FROM users u
     INNER JOIN roles r ON r.id = u.role_id AND r.role_name = 'employee'
     LEFT JOIN employee_skills es ON es.user_id = u.id AND es.skill_id = ?
     WHERE u.is_active = 1
     ORDER BY 
       CASE es.proficiency_level
         WHEN 'expert' THEN 0
         WHEN 'intermediate' THEN 1
         WHEN 'beginner' THEN 2
         ELSE 3
       END ASC,
       u.name ASC"
);

$stmt->bind_param("i", $skillId);
$stmt->execute();
$result = $stmt->get_result();
$employees = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    "ok" => true,
    "employees" => $employees
]);
