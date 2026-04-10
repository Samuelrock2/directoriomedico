<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Get statistics
$total_doctors_query = "SELECT COUNT(*) as total FROM medico WHERE estado = 1";
$total_doctors = mysqli_fetch_assoc(mysqli_query($conection, $total_doctors_query))['total'];

$total_patients_query = "SELECT COUNT(*) as total FROM waiting_patients";
$total_patients = mysqli_fetch_assoc(mysqli_query($conection, $total_patients_query))['total'];

$today_patients_query = "SELECT COUNT(*) as total FROM waiting_patients WHERE DATE(created_at) = CURDATE()";
$today_patients = mysqli_fetch_assoc(mysqli_query($conection, $today_patients_query))['total'];

$doctors = getAllDoctors($conection, 10);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PIDIM</title>
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
        
        .header h1 {
            font-size: 24px;
        }
        
        .logout-btn {
            padding: 10px 20px;
            background: #e74c3c;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #16213e;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 36px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #f9a826 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-card p {
            opacity: 0.8;
            font-size: 14px;
        }
        
        .section {
            background: #16213e;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .section-header h2 {
            font-size: 20px;
        }
        
        .btn-add {
            padding: 8px 20px;
            background: #2ecc71;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-add:hover {
            background: #27ae60;
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
        
        .btn-edit, .btn-delete {
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
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
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
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="manage_doctors.php">👨‍⚕️ Gestionar Médicos</a></li>
                <li><a href="consultories.php">🏥 Gestionar Consultorios</a></li>
                <li><a href="../auth/logout.php">🚪 Cerrar Sesión</a></li>

            </ul>
        </aside>
        
        <div class="main-content">
            <div class="header">
                <h1>Dashboard de Administración</h1>
                <a href="../auth/logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_doctors; ?></h3>
                    <p>Médicos Registrados</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_patients; ?></h3>
                    <p>Pacientes Totales</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $today_patients; ?></h3>
                    <p>Pacientes Hoy</p>
                </div>
            </div>
            
            <div class="section">
                <div class="section-header">
                    <h2>Médicos Recientes</h2>
                    <a href="manage_doctors.php" class="btn-add">+ Agregar Médico</a>
                </div>
                <div class="doctors-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nombre</th>
                                <th>Especialidad</th>
                                <th>Consultorio</th>
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
                                <td>N° <?php echo htmlspecialchars($doctor['consultorio']); ?></td>
                                <td>
                                    <a href="edit_doctor.php?id=<?php echo $doctor['idmed']; ?>" class="btn-edit">Editar</a>
                                    <a href="delete_doctor.php?id=<?php echo $doctor['idmed']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar este médico?')">Eliminar</a>
                                    <a href="../display.php?doctor=<?php echo $doctor['idmed']; ?>" class="btn-view" target="_blank">Ver Pantalla</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>