<?php
// =========================================================
// Konfigurasi Database
// =========================================================

$host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'pemilihan_formatur';

// Create connection
$conn = new mysqli($host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Define constants
define('SITE_URL', 'http://localhost/pemilihan_formatur/');
define('SITE_NAME', 'E-Voting Pemilihan Formatur');

?>
