<?php
// config.php
$db_host = 'localhost';
$db_user = 'mail';
$db_pass = 'activate';
$db_name = 'givehub';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


