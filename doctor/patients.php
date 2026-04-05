<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isDoctor()) {
    redirect('../auth/login.php');
}

$doctor_id = $_SESSION['doctor_id'];
$doctor = getDoctorById($conection, $doctor_id);

// Get all patients with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM waiting_patients WHERE medico_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conection, $query);
mysqli_stmt_bind_param($stmt, "iii", $doctor_id, $limit, $offset);
mysqli_stmt_execute($stmt);
$patients_result = mysqli_stmt_get_result($stmt);

$patients = [];
while ($row = mysqli_fetch_assoc($patients_result)) {
    $patients[] = $row;
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM waiting_patients WHERE medico_id = ?";
$count_stmt = mysqli_prepare($conection, $count_query);
mysqli_stmt_bind_param($count_stmt, "i", $doctor_id);
mysqli_stmt_execute($count_stmt);
$total_result = mysqli_stmt_get_result($count_stmt);
$total = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Pacientes - <?php echo htmlspecialchars($doctor['nombrem']); ?></title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .patients-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .patients-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 10px 20px;
            background: var(--onyx);
            color: var(--light-gray);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: #f9a826;
            color: #fff;
        }
        
        .patients-table {
            background: var(--eerie-black2);
            border-radius: 20px;
            padding: 20px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--jet);
            color: var(--light-gray);
        }
        
        th {
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
        
        .status-waiting { background: #f39c12; color: #fff; }
        .status-consultation { background: #3498db; color: #fff; }
        .status-completed { background: #2ecc71; color: #fff; }
        .status-cancelled { background: #e74c3c; color: #fff; }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a {
            padding: 8px 15px;
            background: var(--onyx);
            color: var(--light-gray);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover,
        .pagination a.active {
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
        
        .btn-action {
            padding: 5px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            margin: 0 2px;
        }
        
        .btn-view { background: #3498db; color: #fff; }
        .btn-edit { background: #f39c12; color: #fff; }
        .btn-delete { background: #e74c3c; color: #fff; }
        
        @media (max-width: 768px) {
            th, td {
                padding: 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="patients-container">
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
            <a href="profile.php" class="nav-link">Mi Perfil</a>
            <a href="patients.php" class="nav-link active">Lista de Pacientes</a>
            <a href="../auth/logout.php" class="nav-link">Cerrar Sesión</a>
        </div>
        
        <div class="patients-header">
            <h1>Lista de Pacientes</h1>
            <p>Historial de pacientes atendidos y en espera</p>
        </div>
        
        <div class="filters">
            <button class="filter-btn active" data-filter="all">Todos</button>
            <button class="filter-btn" data-filter="waiting">En espera</button>
            <button class="filter-btn" data-filter="in_consultation">En consulta</button>
            <button class="filter-btn" data-filter="completed">Completados</button>
            <button class="filter-btn" data-filter="cancelled">Cancelados</button>
        </div>
        
        <div class="patients-table">
            <table id="patients-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Cédula</th>
                        <th>Posición</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $patient): ?>
                    <tr data-status="<?php echo $patient['status']; ?>">
                        <td><?php echo $patient['id']; ?></td>
                        <td><?php echo htmlspecialchars($patient['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($patient['patient_id_number']); ?></td>
                        <td><?php echo $patient['position']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $patient['status']; ?>">
                                <?php 
                                    $status_labels = [
                                        'waiting' => 'En espera',
                                        'in_consultation' => 'En consulta',
                                        'completed' => 'Completado',
                                        'cancelled' => 'Cancelado'
                                    ];
                                    echo $status_labels[$patient['status']];
                                ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($patient['created_at'])); ?></td>
                        <td>
                            <button class="btn-action btn-view" onclick="viewPatient(<?php echo $patient['id']; ?>)">Ver</button>
                            <?php if ($patient['status'] == 'waiting'): ?>
                                <button class="btn-action btn-edit" onclick="startConsultation(<?php echo $patient['id']; ?>)">Iniciar</button>
                            <?php endif; ?>
                            <button class="btn-action btn-delete" onclick="deletePatient(<?php echo $patient['id']; ?>)">Eliminar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function startConsultation(id) {
            if (confirm('¿Iniciar consulta con este paciente?')) {
                window.location.href = 'update_patient_queue.php?id=' + id + '&action=start';
            }
        }
        
        function deletePatient(id) {
            if (confirm('¿Eliminar este paciente del historial?')) {
                window.location.href = 'remove_patient.php?id=' + id;
            }
        }
        
        function viewPatient(id) {
            alert('Función de ver detalles del paciente - ID: ' + id);
        }
        
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                const rows = document.querySelectorAll('#patients-table tbody tr');
                
                rows.forEach(row => {
                    if (filter === 'all' || row.dataset.status === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>