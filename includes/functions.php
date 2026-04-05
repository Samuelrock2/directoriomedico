<?php
/**
 * Helper functions for the medical dashboard
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
}

function isDoctor() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3 && isset($_SESSION['doctor_id']);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function getDoctorById($conn, $doctor_id) {
    $query = "SELECT m.*, c.consultory_number, c.name as consultory_name, u.nombre as user_name 
              FROM medico m 
              LEFT JOIN consultory c ON m.consultory_id = c.id 
              LEFT JOIN usuario u ON m.usuario_id = u.idusuario
              WHERE m.idmed = ? AND m.estado = 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

function getAllDoctors($conn, $limit = null, $offset = 0) {
    $query = "SELECT m.*, c.consultory_number, c.name as consultory_name, u.nombre as user_name 
              FROM medico m 
              LEFT JOIN consultory c ON m.consultory_id = c.id 
              LEFT JOIN usuario u ON m.usuario_id = u.idusuario 
              WHERE m.estado = 1 
              ORDER BY c.consultory_number ASC, m.nombrem ASC";
    
    if ($limit) {
        $query .= " LIMIT $offset, $limit";
    }
    
    $result = mysqli_query($conn, $query);
    $doctors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $doctors[] = $row;
    }
    return $doctors;
}

function getWaitingPatients($conn, $doctor_id, $status = null) {
    $query = "SELECT * FROM waiting_patients WHERE medico_id = ?";
    if ($status) {
        $query .= " AND status = '$status'";
    }
    $query .= " ORDER BY position ASC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $patients = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $patients[] = $row;
    }
    return $patients;
}

function updatePatientQueuePositions($conn, $doctor_id) {
    // Renumber positions after deletions/updates
    $query = "SELECT id FROM waiting_patients 
              WHERE medico_id = ? AND status IN ('waiting', 'in_consultation') 
              ORDER BY position ASC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $position = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $update = "UPDATE waiting_patients SET position = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($update_stmt, "ii", $position, $row['id']);
        mysqli_stmt_execute($update_stmt);
        $position++;
    }
}

function getDoctorStats($conn, $doctor_id) {
    $stats = [];
    
    // Total patients today
    $query = "SELECT COUNT(*) as total FROM waiting_patients 
              WHERE medico_id = ? AND DATE(created_at) = CURDATE()";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['today_total'] = mysqli_fetch_assoc($result)['total'];
    
    // Waiting patients
    $stats['waiting'] = count(getWaitingPatients($conn, $doctor_id, 'waiting'));
    
    // In consultation
    $stats['in_consultation'] = count(getWaitingPatients($conn, $doctor_id, 'in_consultation'));
    
    // Completed today
    $query = "SELECT COUNT(*) as completed FROM waiting_patients 
              WHERE medico_id = ? AND status = 'completed' AND DATE(updated_at) = CURDATE()";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['completed'] = mysqli_fetch_assoc($result)['completed'];
    
    return $stats;
}

function uploadDoctorPhoto($file, $doctor_id) {
    $target_dir = "../assets/uploads/doctors/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = "doctor_" . $doctor_id . "_" . time() . "." . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is valid
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ["success" => false, "error" => "El archivo no es una imagen válida."];
    }
    
    // Check file size (max 5MB)
    if ($file["size"] > 5000000) {
        return ["success" => false, "error" => "El archivo es demasiado grande (máximo 5MB)."];
    }
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        return ["success" => false, "error" => "Solo se permiten archivos JPG, JPEG, PNG y GIF."];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filename" => $new_filename];
    } else {
        return ["success" => false, "error" => "Error al subir el archivo."];
    }
}

function getDisplayDoctorPhoto($photo) {
    if ($photo && $photo != 'img_paciente.jpg' && $photo != 'img_paciente.png') {
        if (file_exists("../assets/uploads/doctors/" . $photo)) {
            return "../assets/uploads/doctors/" . $photo;
        } elseif (file_exists("../assets/uploads/doctors/" . $photo)) {
            return "../assets/uploads/doctors/" . $photo;
        } elseif (file_exists("img/uploads/" . $photo)) {
            return "img/uploads/" . $photo;
        }
    }
    return "img/img_paciente.jpg";
}
?>