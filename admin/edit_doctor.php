<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = '';
$error = '';

// Get consultories for dropdown
$consultories_query = "SELECT * FROM consultory WHERE status = 1 ORDER BY consultory_number";
$consultories_result = mysqli_query($conection, $consultories_query);
$consultories = [];
while ($c = mysqli_fetch_assoc($consultories_result)) {
    $consultories[] = $c;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? 'edit';
    
    if ($action == 'edit') {
        $nombrem = sanitizeInput($_POST['nombrem']);
        $especialidad = sanitizeInput($_POST['especialidad']);
        $consultorio_num = sanitizeInput($_POST['consultorio']);
        $consultory_id = !empty($_POST['consultory_id']) ? (int)$_POST['consultory_id'] : null;
        $mpps = sanitizeInput($_POST['mpps']);
        $comezu = sanitizeInput($_POST['comezu']);
        $fechas_consul = sanitizeInput($_POST['fechas_consul']);
        $horarios = sanitizeInput($_POST['horarios']);
        
        $query = "UPDATE medico SET nombrem = ?, especialidad = ?, consultorio = ?, 
                  consultory_id = ?, mpps = ?, comezu = ?, fechas_consul = ?, horarios = ? 
                  WHERE idmed = ?";
        $stmt = mysqli_prepare($conection, $query);
        mysqli_stmt_bind_param($stmt, "sssissssi", $nombrem, $especialidad, $consultorio_num, 
                              $consultory_id, $mpps, $comezu, $fechas_consul, $horarios, $doctor_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Handle photo upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $upload = uploadDoctorPhoto($_FILES['foto'], $doctor_id);
                if ($upload['success']) {
                    $update_photo = "UPDATE medico SET foto = ? WHERE idmed = ?";
                    $photo_stmt = mysqli_prepare($conection, $update_photo);
                    mysqli_stmt_bind_param($photo_stmt, "si", $upload['filename'], $doctor_id);
                    mysqli_stmt_execute($photo_stmt);
                    $success = "Médico actualizado correctamente. Foto actualizada.";
                } else {
                    $error = $upload['error'];
                }
            } else {
                $success = "Médico actualizado correctamente";
            }
        } else {
            $error = "Error al actualizar el médico: " . mysqli_error($conection);
        }
        
        // Refresh doctor data
        $doctor = getDoctorById($conection, $doctor_id);
    }
}

// Get doctor data
$doctor = getDoctorById($conection, $doctor_id);
if (!$doctor && $doctor_id > 0) {
    $error = "Médico no encontrado";
}

$photo_url = $doctor ? getDisplayDoctorPhoto($doctor['foto']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Médico - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #1a1a2e;
            color: #fff;
            padding: 40px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #16213e;
            border-radius: 20px;
            padding: 30px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-back {
            padding: 10px 20px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: #2980b9;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            opacity: 0.8;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            background: #0f3460;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #f9a826;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .current-photo {
            text-align: center;
            margin-bottom: 25px;
            padding: 20px;
            background: #0f3460;
            border-radius: 15px;
        }
        
        .current-photo img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f9a826;
        }
        
        .current-photo p {
            margin-top: 10px;
            font-size: 12px;
            opacity: 0.7;
        }
        
        .btn-submit {
            padding: 12px 30px;
            background: linear-gradient(135deg, #f9a826 0%, #f97316 100%);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid #2ecc71;
            color: #2ecc71;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid #e74c3c;
            color: #e74c3c;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .info-note {
            background: rgba(52, 152, 219, 0.1);
            border-left: 3px solid #3498db;
            padding: 12px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 13px;
            opacity: 0.8;
        }
        
        @media (max-width: 600px) {
            body {
                padding: 20px;
            }
            
            .container {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✏️ Editar Médico</h1>
            <a href="manage_doctors.php" class="btn-back">← Volver</a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert-success">✓ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($doctor): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                
                <?php if ($doctor['foto'] && $doctor['foto'] != 'img_paciente.jpg' && $doctor['foto'] != 'img_paciente.png'): ?>
                    <div class="current-photo">
                        <img src="<?php echo $photo_url; ?>" alt="Doctor">
                        <p>Foto actual del médico</p>
                    </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre completo *</label>
                        <input type="text" name="nombrem" value="<?php echo htmlspecialchars($doctor['nombrem']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Especialidad *</label>
                        <input type="text" name="especialidad" value="<?php echo htmlspecialchars($doctor['especialidad']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Número de Consultorio</label>
                        <input type="text" name="consultorio" value="<?php echo htmlspecialchars($doctor['consultorio']); ?>" placeholder="Ej: 1, 2, 3">
                    </div>
                    <div class="form-group">
                        <label>Consultorio Asignado (Sistema)</label>
                        <select name="consultory_id">
                            <option value="">Seleccionar consultorio</option>
                            <?php foreach ($consultories as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($doctor['consultory_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    Consultorio <?php echo htmlspecialchars($c['consultory_number']); ?> - <?php echo htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>CMV (MPPS)</label>
                        <input type="text" name="mpps" value="<?php echo htmlspecialchars($doctor['mpps']); ?>" placeholder="Número de registro MPPS">
                    </div>
                    <div class="form-group">
                        <label>CMVZUL</label>
                        <input type="text" name="comezu" value="<?php echo htmlspecialchars($doctor['comezu']); ?>" placeholder="Número de registro CMVZUL">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Días de consulta</label>
                        <input type="text" name="fechas_consul" value="<?php echo htmlspecialchars($doctor['fechas_consul']); ?>" placeholder="Ej: Lunes a Viernes">
                    </div>
                    <div class="form-group">
                        <label>Horario</label>
                        <input type="text" name="horarios" value="<?php echo htmlspecialchars($doctor['horarios']); ?>" placeholder="Ej: 8:00 AM - 4:00 PM">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Nueva foto de perfil (opcional)</label>
                    <input type="file" name="foto" accept="image/*">
                    <small style="display: block; margin-top: 5px; opacity: 0.7;">
                        Formatos permitidos: JPG, JPEG, PNG, GIF (Max 5MB). Dejar en blanco para mantener la foto actual.
                    </small>
                </div>
                
                <button type="submit" class="btn-submit">💾 Guardar Cambios</button>
                
                <div class="info-note">
                    <strong>ℹ️ Información:</strong>
                    <ul style="margin-left: 20px; margin-top: 5px;">
                        <li>El número de consultorio es para referencia visual</li>
                        <li>El consultorio asignado se usa para filtrar médicos por ubicación</li>
                        <li>Los cambios se verán reflejados inmediatamente en la pantalla de visualización</li>
                    </ul>
                </div>
            </form>
        <?php else: ?>
            <div class="alert-error">⚠️ Médico no encontrado. Por favor, verifica el ID.</div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="manage_doctors.php" class="btn-back">← Volver a la lista de médicos</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>