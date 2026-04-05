<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isDoctor()) {
    redirect('../auth/login.php');
}

$doctor_id = $_SESSION['doctor_id'];
$patient_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($patient_id && $action) {
    $new_status = '';
    if ($action == 'start') {
        $new_status = 'in_consultation';
    } elseif ($action == 'complete') {
        $new_status = 'completed';
    }
    
    if ($new_status) {
        $query = "UPDATE waiting_patients SET status = ? WHERE id = ? AND medico_id = ?";
        $stmt = mysqli_prepare($conection, $query);
        mysqli_stmt_bind_param($stmt, "sii", $new_status, $patient_id, $doctor_id);
        mysqli_stmt_execute($stmt);
        
        // Reorder positions
        updatePatientQueuePositions($conection, $doctor_id);
        
        // Update doctor's current patient fields
        if ($action == 'start') {
            // Get patient name
            $patient_query = "SELECT patient_name FROM waiting_patients WHERE id = ?";
            $patient_stmt = mysqli_prepare($conection, $patient_query);
            mysqli_stmt_bind_param($patient_stmt, "i", $patient_id);
            mysqli_stmt_execute($patient_stmt);
            $patient_result = mysqli_stmt_get_result($patient_stmt);
            $patient = mysqli_fetch_assoc($patient_result);
            
            $update_doc = "UPDATE medico SET paciente_actual = ? WHERE idmed = ?";
            $doc_stmt = mysqli_prepare($conection, $update_doc);
            mysqli_stmt_bind_param($doc_stmt, "si", $patient['patient_name'], $doctor_id);
            mysqli_stmt_execute($doc_stmt);
        } elseif ($action == 'complete') {
            $update_doc = "UPDATE medico SET paciente_actual = NULL WHERE idmed = ?";
            $doc_stmt = mysqli_prepare($conection, $update_doc);
            mysqli_stmt_bind_param($doc_stmt, "i", $doctor_id);
            mysqli_stmt_execute($doc_stmt);
        }
    }
}

redirect('dashboard.php');
?>