<?php
declare(strict_types=1);

// Ensure CLI runs detect the environment as local so local DB creds are used
if (php_sapi_name() === 'cli') {
    $_SERVER['SERVER_NAME'] = 'localhost';
}

require_once __DIR__ . "/../config/database.php";

$checks = [
    "users" => "SELECT COUNT(*) c FROM users",
    "tasks" => "SELECT COUNT(*) c FROM tasks",
    "skills" => "SELECT COUNT(*) c FROM skills",
    "notifications" => "SELECT COUNT(*) c FROM notifications",
];

foreach ($checks as $name => $sql) {
    $row = $mysqli->query($sql)->fetch_assoc();
    echo $name . ": " . (int) $row["c"] . PHP_EOL;
}

echo "Smoke test complete." . PHP_EOL;
