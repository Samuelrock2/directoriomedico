<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isDoctor()) {
    redirect('../auth/login.php');
}

$doctor_id = $_SESSION['doctor_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_name = sanitizeInput($_POST['patient_name']);
    $patient_id_number = sanitizeInput($_POST['patient_id']);
    $estimated_time = sanitizeInput($_POST['estimated_time']);
    
    // Get next position
    $query = "SELECT MAX(position) as max_pos FROM waiting_patients 
              WHERE medico_id = ? AND status IN ('waiting', 'in_consultation')";
    $stmt = mysqli_prepare($conection, $query);
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $next_position = ($row['max_pos'] ?? 0) + 1;
    
    $insert = "INSERT INTO waiting_patients (medico_id, patient_name, patient_id_number, position, estimated_time, status) 
               VALUES (?, ?, ?, ?, ?, 'waiting')";
    $insert_stmt = mysqli_prepare($conection, $insert);
    mysqli_stmt_bind_param($insert_stmt, "issis", $doctor_id, $patient_name, $patient_id_number, $next_position, $estimated_time);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        // Update doctor's waiting list display fields
        $update_doc = "UPDATE medico SET pacientes = 'VIENDO PACIENTES' WHERE idmed = ?";
        $doc_stmt = mysqli_prepare($conection, $update_doc);
        mysqli_stmt_bind_param($doc_stmt, "i", $doctor_id);
        mysqli_stmt_execute($doc_stmt);
    }
}

redirect('dashboard.php');
?>