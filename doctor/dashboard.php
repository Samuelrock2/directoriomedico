<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

// Check authentication
if (!isLoggedIn() || !isDoctor()) {
    redirect('../auth/login.php');
}

$doctor_id = $_SESSION['doctor_id'];
$doctor = getDoctorById($conection, $doctor_id);
$stats = getDoctorStats($conection, $doctor_id);
$waiting_patients = getWaitingPatients($conection, $doctor_id, 'waiting');
$in_consultation = getWaitingPatients($conection, $doctor_id, 'in_consultation');

$photo_url = getDisplayDoctorPhoto($doctor['foto']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Doctor - <?php echo htmlspecialchars($doctor['nombrem']); ?></title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
        }
        
        .doctor-info {
            display: flex;
            align-items: center;
            gap: 25px;
            flex-wrap: wrap;
        }
        
        .doctor-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
        
        .doctor-details h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .doctor-details p {
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .queue-section {
            background: var(--eerie-black2);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .section-title {
            color: var(--white2);
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .patients-table {
            width: 100%;
            overflow-x: auto;
        }
        
        .patients-table table {
            width: 100%;
            border-collapse: collapse;
            color: var(--light-gray);
        }
        
        .patients-table th,
        .patients-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--jet);
        }
        
        .patients-table th {
            background: var(--onyx);
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-waiting {
            background: #f39c12;
            color: #fff;
        }
        
        .status-consultation {
            background: #3498db;
            color: #fff;
        }
        
        .status-completed {
            background: #2ecc71;
            color: #fff;
        }
        
        .btn-action {
            padding: 6px 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            margin: 0 3px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #f9a826;
            color: #fff;
        }
        
        .btn-success {
            background: #2ecc71;
            color: #fff;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: #fff;
        }
        
        .btn-info {
            background: #3498db;
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
        
        .add-patient-form {
            background: var(--eerie-black1);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--jet);
            border-radius: 8px;
            background: var(--eerie-black2);
            color: var(--white2);
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="doctor-info">
                <img src="<?php echo $photo_url; ?>" alt="Doctor" class="doctor-avatar">
                <div class="doctor-details">
                    <h1>Dr/a. <?php echo htmlspecialchars($doctor['nombrem']); ?></h1>
                    <p><?php echo htmlspecialchars($doctor['especialidad']); ?> | Consultorio N° <?php echo htmlspecialchars($doctor['consultorio']); ?></p>
                    <p>CMV: <?php echo htmlspecialchars($doctor['mpps']); ?> | CMVZUL: <?php echo htmlspecialchars($doctor['comezu']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link active">Dashboard</a>
            <a href="profile.php" class="nav-link">Mi Perfil</a>
            <a href="patients.php" class="nav-link">Lista de Pacientes</a>
            <a href="../auth/logout.php" class="nav-link">Cerrar Sesión</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $stats['today_total']; ?></h3>
                <p>Pacientes Hoy</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['waiting']; ?></h3>
                <p>En Espera</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['in_consultation']; ?></h3>
                <p>En Consulta</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['completed']; ?></h3>
                <p>Atendidos Hoy</p>
            </div>
        </div>
        
        <div class="queue-section">
            <h2 class="section-title">Pacientes en Consulta</h2>
            <?php if (count($in_consultation) > 0): ?>
                <div class="patients-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Posición</th>
                                <th>Paciente</th>
                                <th>Cédula</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($in_consultation as $patient): ?>
                            <tr>
                                <td><?php echo $patient['position']; ?></td>
                                <td><?php echo htmlspecialchars($patient['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['patient_id_number']); ?></td>
                                <td><span class="status-badge status-consultation">En consulta</span></td>
                                <td>
                                    <button class="btn-action btn-success" onclick="completePatient(<?php echo $patient['id']; ?>)">Completar</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No hay pacientes en consulta actualmente.</p>
            <?php endif; ?>
        </div>
        
        <div class="queue-section">
            <h2 class="section-title">Pacientes en Espera</h2>
            
            <div class="add-patient-form">
                <h3>Agregar Paciente a la Cola</h3>
                <form method="POST" action="add_patient.php" class="form-row">
                    <div class="form-group">
                        <input type="text" name="patient_name" placeholder="Nombre del paciente" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="patient_id" placeholder="Cédula (opcional)">
                    </div>
                    <div class="form-group">
                        <input type="text" name="estimated_time" placeholder="Tiempo estimado (ej: 15 min)">
                    </div>
                    <button type="submit" class="btn-action btn-primary">Agregar a la cola</button>
                </form>
            </div>
            
            <?php if (count($waiting_patients) > 0): ?>
                <div class="patients-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Posición</th>
                                <th>Paciente</th>
                                <th>Cédula</th>
                                <th>Tiempo estimado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($waiting_patients as $patient): ?>
                            <tr>
                                <td><?php echo $patient['position']; ?></td>
                                <td><?php echo htmlspecialchars($patient['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['patient_id_number']); ?></td>
                                <td><?php echo htmlspecialchars($patient['estimated_time']); ?></td>
                                <td><span class="status-badge status-waiting">En espera</span></td>
                                <td>
                                    <button class="btn-action btn-primary" onclick="startConsultation(<?php echo $patient['id']; ?>)">Iniciar consulta</button>
                                    <button class="btn-action btn-danger" onclick="removePatient(<?php echo $patient['id']; ?>)">Eliminar</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No hay pacientes en espera.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function startConsultation(patientId) {
            if (confirm('¿Desea iniciar la consulta con este paciente?')) {
                window.location.href = 'update_patient_queue.php?id=' + patientId + '&action=start';
            }
        }
        
        function completePatient(patientId) {
            if (confirm('¿Marcar este paciente como completado?')) {
                window.location.href = 'update_patient_queue.php?id=' + patientId + '&action=complete';
            }
        }
        
        function removePatient(patientId) {
            if (confirm('¿Eliminar este paciente de la cola?')) {
                window.location.href = 'remove_patient.php?id=' + patientId;
            }
        }
    </script>
</body>
</html>