<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isDoctor()) {
    redirect('../auth/login.php');
}

$doctor_id = $_SESSION['doctor_id'];
$doctor = getDoctorById($conection, $doctor_id);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $nombrem = sanitizeInput($_POST['nombrem']);
        $especialidad = sanitizeInput($_POST['especialidad']);
        $consultorio = sanitizeInput($_POST['consultorio']);
        $mpps = sanitizeInput($_POST['mpps']);
        $comezu = sanitizeInput($_POST['comezu']);
        $horarios = sanitizeInput($_POST['horarios']);
        $fechas_consul = sanitizeInput($_POST['fechas_consul']);
        
        $query = "UPDATE medico SET nombrem = ?, especialidad = ?, consultorio = ?, 
                  mpps = ?, comezu = ?, horarios = ?, fechas_consul = ? 
                  WHERE idmed = ?";
        $stmt = mysqli_prepare($conection, $query);
        mysqli_stmt_bind_param($stmt, "sssssssi", $nombrem, $especialidad, $consultorio, 
                              $mpps, $comezu, $horarios, $fechas_consul, $doctor_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Perfil actualizado correctamente";
            $doctor = getDoctorById($conection, $doctor_id);
        } else {
            $error = "Error al actualizar el perfil";
        }
    }
    
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $upload = uploadDoctorPhoto($_FILES['profile_photo'], $doctor_id);
        if ($upload['success']) {
            $query = "UPDATE medico SET foto = ? WHERE idmed = ?";
            $stmt = mysqli_prepare($conection, $query);
            mysqli_stmt_bind_param($stmt, "si", $upload['filename'], $doctor_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Foto de perfil actualizada correctamente";
                $doctor = getDoctorById($conection, $doctor_id);
            }
        } else {
            $error = $upload['error'];
        }
    }
}

$photo_url = getDisplayDoctorPhoto($doctor['foto']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?php echo htmlspecialchars($doctor['nombrem']); ?></title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            text-align: center;
        }
        
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            margin-bottom: 20px;
        }
        
        .profile-form {
            background: var(--eerie-black2);
            border-radius: 20px;
            padding: 30px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            color: var(--white2);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--jet);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            color: var(--light-gray);
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--jet);
            border-radius: 10px;
            background: var(--eerie-black1);
            color: var(--white2);
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #f9a826;
        }
        
        .btn-save {
            background: #f9a826;
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-save:hover {
            background: #f97316;
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
        
        .photo-upload {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .photo-upload input {
            display: none;
        }
        
        .photo-upload label {
            display: inline-block;
            padding: 10px 20px;
            background: var(--onyx);
            color: var(--light-gray);
            border-radius: 10px;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        
        .photo-upload label:hover {
            background: #f9a826;
            color: #fff;
        }
        
        .nav-links {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .nav-link {
            padding: 10px 20px;
            background: var(--onyx);
            color: var(--light-gray);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: #f9a826;
            color: #fff;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="profile.php" class="nav-link active">Mi Perfil</a>
            <a href="patients.php" class="nav-link">Lista de Pacientes</a>
            <a href="../auth/logout.php" class="nav-link">Cerrar Sesión</a>
        </div>
        
        <div class="profile-header">
            <img src="<?php echo $photo_url; ?>" alt="Doctor" class="profile-photo" id="profile-photo">
            <h1>Dr/a. <?php echo htmlspecialchars($doctor['nombrem']); ?></h1>
            <p><?php echo htmlspecialchars($doctor['especialidad']); ?></p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="profile-form">
            <div class="photo-upload">
                <form method="POST" enctype="multipart/form-data" id="photo-form">
                    <input type="file" name="profile_photo" id="profile_photo" accept="image/*">
                    <label for="profile_photo">Cambiar foto de perfil</label>
                </form>
            </div>
            
            <form method="POST">
                <div class="form-section">
                    <h3>Información Personal</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nombre completo</label>
                            <input type="text" name="nombrem" value="<?php echo htmlspecialchars($doctor['nombrem']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Especialidad</label>
                            <input type="text" name="especialidad" value="<?php echo htmlspecialchars($doctor['especialidad']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Consultorio N°</label>
                            <input type="text" name="consultorio" value="<?php echo htmlspecialchars($doctor['consultorio']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Información Profesional</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>CMV (MPPS)</label>
                            <input type="text" name="mpps" value="<?php echo htmlspecialchars($doctor['mpps']); ?>">
                        </div>
                        <div class="form-group">
                            <label>CMVZUL</label>
                            <input type="text" name="comezu" value="<?php echo htmlspecialchars($doctor['comezu']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Horario de Consulta</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Días de consulta</label>
                            <input type="text" name="fechas_consul" value="<?php echo htmlspecialchars($doctor['fechas_consul']); ?>" placeholder="Ej: Lunes a Viernes">
                        </div>
                        <div class="form-group">
                            <label>Horario</label>
                            <input type="text" name="horarios" value="<?php echo htmlspecialchars($doctor['horarios']); ?>" placeholder="Ej: 8:00 AM - 4:00 PM">
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="update_profile" class="btn-save">Guardar Cambios</button>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('profile_photo').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                var formData = new FormData();
                formData.append('profile_photo', this.files[0]);
                
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        location.reload();
                    }
                };
                xhr.send(formData);
            }
        });
    </script>
</body>
</html>