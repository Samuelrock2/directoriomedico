<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($doctor_id) {
    // Soft delete - just mark as inactive
    $query = "UPDATE medico SET estado = 0 WHERE idmed = ?";
    $stmt = mysqli_prepare($conection, $query);
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
}

redirect('manage_doctors.php');
?>