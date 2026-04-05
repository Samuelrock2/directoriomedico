<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isDoctor()) {
    redirect('../auth/login.php');
}

$doctor_id = $_SESSION['doctor_id'];
$patient_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($patient_id) {
    $query = "DELETE FROM waiting_patients WHERE id = ? AND medico_id = ?";
    $stmt = mysqli_prepare($conection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $patient_id, $doctor_id);
    mysqli_stmt_execute($stmt);
    
    // Reorder remaining patients
    updatePatientQueuePositions($conection, $doctor_id);
}

redirect('dashboard.php');
?>