<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Get all doctors
$doctors = getAllDoctors($conection);

// Get users that don't have a doctor assigned yet
$user_query = "SELECT u.*, r.rol as role_name 
               FROM usuario u 
               JOIN rol r ON u.rol = r.idrol 
               WHERE u.rol = 3 AND u.idusuario NOT IN (SELECT usuario_id FROM medico WHERE estado = 1)
               AND u.estatus = 1";
$user_result = mysqli_query($conection, $user_query);
$users = [];
while ($row = mysqli_fetch_assoc($user_result)) {
    $users[] = $row;
}

// Get consultories for dropdown
$consultories_query = "SELECT * FROM consultory WHERE status = 1 ORDER BY consultory_number";
$consultories_result = mysqli_query($conection, $consultories_query);
$consultories = [];
while ($c = mysqli_fetch_assoc($consultories_result)) {
    $consultories[] = $c;
}

// Handle add/edit operations
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $nombrem = sanitizeInput($_POST['nombrem']);
        $especialidad = sanitizeInput($_POST['especialidad']);
        $consultorio_num = sanitizeInput($_POST['consultorio']);
        $consultory_id = !empty($_POST['consultory_id']) ? (int)$_POST['consultory_id'] : null;
        $mpps = sanitizeInput($_POST['mpps']);
        $comezu = sanitizeInput($_POST['comezu']);
        $usuario_id = (int)$_POST['usuario_id'];
        $fechas_consul = sanitizeInput($_POST['fechas_consul']);
        $horarios = sanitizeInput($_POST['horarios']);
        
        $query = "INSERT INTO medico (nombrem, especialidad, consultorio, consultory_id, mpps, comezu, 
                  usuario_id, fechas_consul, horarios, estado) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = mysqli_prepare($conection, $query);
        mysqli_stmt_bind_param($stmt, "sssisssss", $nombrem, $especialidad, $consultorio_num, 
                              $consultory_id, $mpps, $comezu, $usuario_id, $fechas_consul, $horarios);
        
        if (mysqli_stmt_execute($stmt)) {
            $doctor_id = mysqli_insert_id($conection);
            
            // Handle photo upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $upload = uploadDoctorPhoto($_FILES['foto'], $doctor_id);
                if ($upload['success']) {
                    $update_photo = "UPDATE medico SET foto = ? WHERE idmed = ?";
                    $photo_stmt = mysqli_prepare($conection, $update_photo);
                    mysqli_stmt_bind_param($photo_stmt, "si", $upload['filename'], $doctor_id);
                    mysqli_stmt_execute($photo_stmt);
                }
            }
            
            $success = "Médico agregado correctamente";
            // Refresh the page to show updated list
            header("Refresh:0");
            exit();
        } else {
            $error = "Error al agregar el médico: " . mysqli_error($conection);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Médicos - Admin</title>
        <link rel="shortcut icon" href="../img/img_paciente.png" type="image/x-icon">
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
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: #16213e;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        
        .sidebar-header h3 {
            font-size: 18px;
        }
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.7;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-menu li {
            margin-bottom: 10px;
        }
        
        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .nav-menu a:hover,
        .nav-menu a.active {
            background: linear-gradient(135deg, #f9a826 0%, #f97316 100%);
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #16213e;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .btn-back {
            padding: 10px 20px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
        }
        
        .section {
            background: #16213e;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .section-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #f9a826;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
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
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        
        .doctors-table {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        th {
            background: #0f3460;
            font-weight: 600;
        }
        
        .doctor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .btn-edit, .btn-delete, .btn-view {
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            margin: 0 3px;
            display: inline-block;
        }
        
        .btn-edit {
            background: #f39c12;
            color: #fff;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: #fff;
        }
        
        .btn-view {
            background: #3498db;
            color: #fff;
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
        
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-header h3,
            .sidebar-header p,
            .nav-menu span {
                display: none;
            }
            
            .main-content {
                margin-left: 80px;
            }
            
            .nav-menu a {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../img/img_paciente.png" alt="Logo">
                <h3>PIDIM</h3>
                <p>Administrador</p>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="manage_doctors.php" class="active">👨‍⚕️ Gestionar Médicos</a></li>
                <li><a href="consultories.php">🏥 Gestionar Consultorios</a></li>
                <li><a href="../auth/logout.php">🚪 Cerrar Sesión</a></li>
            </ul>
        </aside>
        
        <div class="main-content">
            <div class="header">
                <h1>Gestionar Médicos</h1>
                <a href="dashboard.php" class="btn-back">← Volver al Dashboard</a>
            </div>
            
            <?php if ($success): ?>
                <div class="alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="section">
                <div class="section-header">
                    <h2>Agregar Nuevo Médico</h2>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre completo *</label>
                            <input type="text" name="nombrem" required>
                        </div>
                        <div class="form-group">
                            <label>Especialidad *</label>
                            <input type="text" name="especialidad" required>
                        </div>
                        <div class="form-group">
                            <label>Número de Consultorio</label>
                            <input type="text" name="consultorio" placeholder="Ej: 1, 2, 3">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Consultorio Asignado</label>
                            <select name="consultory_id">
                                <option value="">Seleccionar consultorio</option>
                                <?php foreach ($consultories as $c): ?>
                                    <option value="<?php echo $c['id']; ?>">
                                        Consultorio <?php echo htmlspecialchars($c['consultory_number']); ?> - <?php echo htmlspecialchars($c['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>CMV (MPPS)</label>
                            <input type="text" name="mpps" placeholder="Número de registro MPPS">
                        </div>
                        <div class="form-group">
                            <label>CMVZUL</label>
                            <input type="text" name="comezu" placeholder="Número de registro CMVZUL">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Usuario asociado *</label>
                            <select name="usuario_id" required>
                                <option value="">Seleccionar usuario</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['idusuario']; ?>">
                                        <?php echo htmlspecialchars($user['nombre']); ?> (<?php echo htmlspecialchars($user['usuario']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Días de consulta</label>
                            <input type="text" name="fechas_consul" placeholder="Ej: Lunes a Viernes">
                        </div>
                        <div class="form-group">
                            <label>Horario</label>
                            <input type="text" name="horarios" placeholder="Ej: 8:00 AM - 4:00 PM">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Foto de perfil</label>
                            <input type="file" name="foto" accept="image/*">
                            <small style="display: block; margin-top: 5px; opacity: 0.7;">Formatos: JPG, PNG, GIF (Max 5MB)</small>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Agregar Médico</button>
                </form>
            </div>
            
            <div class="section">
                <div class="section-header">
                    <h2>Lista de Médicos</h2>
                </div>
                <div class="doctors-table">
                    <?php if (count($doctors) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nombre</th>
                                <th>Especialidad</th>
                                <th>Consultorio</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo getDisplayDoctorPhoto($doctor['foto']); ?>" alt="Doctor" class="doctor-avatar">
                                </td>
                                <td><?php echo htmlspecialchars($doctor['nombrem']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['especialidad']); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($doctor['consultory_number'])) {
                                        echo "N° " . htmlspecialchars($doctor['consultory_number']);
                                    } elseif (!empty($doctor['consultorio'])) {
                                        echo "N° " . htmlspecialchars($doctor['consultorio']);
                                    } else {
                                        echo "No asignado";
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($doctor['user_name']); ?></td>
                                <td>
                                    <a href="edit_doctor.php?id=<?php echo $doctor['idmed']; ?>" class="btn-edit">Editar</a>
                                    <a href="delete_doctor.php?id=<?php echo $doctor['idmed']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar este médico?')">Eliminar</a>
                                    <a href="../display.php?doctor=<?php echo $doctor['idmed']; ?>&mode=single" class="btn-view" target="_blank">Ver Pantalla</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p style="text-align: center; padding: 40px;">No hay médicos registrados</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>