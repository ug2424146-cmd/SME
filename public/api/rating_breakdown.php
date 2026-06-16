<?php
declare(strict_types=1);
require_once __DIR__ . "/../../app/helpers/session.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../app/helpers/app.php";

require_auth();
header('Content-Type: application/json; charset=utf-8');

$current = current_user();
$userRole = $current['role'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

// Managers and admins can request any user's breakdown; employees only their own
if ($userRole !== 'manager' && $userRole !== 'admin') {
    // if employee, force their own id
    $userId = $current['id'];
}

global $mysqli;

$rows = [];

if ($userId > 0) {
    $d = compute_user_rating($userId, true);
    $rows[] = [
        'id' => $userId,
        'name' => get_user_name_by_id($userId),
        'detail' => $d,
    ];
} else {
    // return all employees
    $res = $mysqli->query("SELECT u.id, u.name FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.role_name = 'employee' AND u.is_active = 1 ORDER BY u.name ASC");
    while ($r = $res->fetch_assoc()) {
        $uid = (int) $r['id'];
        $d = compute_user_rating($uid, true);
        $rows[] = ['id' => $uid, 'name' => $r['name'], 'detail' => $d];
    }
}

echo json_encode(['ok' => true, 'rows' => $rows]);

function get_user_name_by_id(int $id): string
{
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT name FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (string) $row['name'] : 'Unknown';
}
