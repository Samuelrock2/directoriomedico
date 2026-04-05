<?php 
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'pantalla';

$conection = @mysqli_connect($host, $user, $password, $db);

if (!$conection) {
    die("Error en la conexión: " . mysqli_connect_error());
}

// Set charset for proper encoding
mysqli_set_charset($conection, "utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>