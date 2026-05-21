<?php

declare(strict_types=1);

require_once __DIR__ . "/../../app/helpers/session.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/controllers/SkillController.php";

require_auth();
header("Content-Type: application/json; charset=utf-8");

$user = current_user();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "message" => "Method not allowed."]);
    exit;
}

$input = $_POST;
$contentType = $_SERVER["CONTENT_TYPE"] ?? "";
if (str_contains($contentType, "application/json")) {
    $raw = file_get_contents("php://input");
    $decoded = json_decode($raw ?: "{}", true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

if (!verify_csrf_token($input["csrf_token"] ?? null)) {
    http_response_code(403);
    echo json_encode(["ok" => false, "message" => "Invalid CSRF token."]);
    exit;
}

$controller = new SkillController();
$result = $controller->upsertEmployeeSkill($input, (int) $user["id"], false);

if (!$result["ok"]) {
    http_response_code(400);
}

echo json_encode($result);
