<?php

declare(strict_types=1);

$host = "127.0.0.1";
$dbName = "sme_platform";
$username = "root";
$password = "";

$mysqli = new mysqli($host, $username, $password, $dbName);

if ($mysqli->connect_error) {
    http_response_code(500);
    exit("Database connection failed.");
}

$mysqli->set_charset("utf8mb4");
