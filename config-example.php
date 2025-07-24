<?php
// config.php
$db_host = 'DB_HOST';
$db_user = 'DB_USER';
$db_pass = 'DB_PASSWORD';
$db_name = 'DB_NAME';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


