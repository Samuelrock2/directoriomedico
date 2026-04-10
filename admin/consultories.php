<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Handle add/edit/delete operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $consultory_number = sanitizeInput($_POST['consultory_number']);
            $name = sanitizeInput($_POST['name']);
            $location = sanitizeInput($_POST['location']);
            
            $query = "INSERT INTO consultory (consultory_number, name, location) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conection, $query);
            mysqli_stmt_bind_param($stmt, "sss", $consultory_number, $name, $location);
            mysqli_stmt_execute($stmt);
        } elseif ($_POST['action'] == 'edit') {
            $id = (int)$_POST['id'];
            $consultory_number = sanitizeInput($_POST['consultory_number']);
            $name = sanitizeInput($_POST['name']);
            $location = sanitizeInput($_POST['location']);
            
            $query = "UPDATE consultory SET consultory_number = ?, name = ?, location = ? WHERE id = ?";
            $stmt = mysqli_prepare($conection, $query);
            mysqli_stmt_bind_param($stmt, "sssi", $consultory_number, $name, $location, $id);
            mysqli_stmt_execute($stmt);
        } elseif ($_POST['action'] == 'delete') {
            $id = (int)$_POST['id'];
            $query = "UPDATE consultory SET status = 0 WHERE id = ?";
            $stmt = mysqli_prepare($conection, $query);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
        }
        redirect('consultories.php');
    }
}

$consultories_query = "SELECT c.*, COUNT(m.idmed) as doctors_count 
                       FROM consultory c 
                       LEFT JOIN medico m ON c.id = m.consultory_id AND m.estado = 1
                       WHERE c.status = 1 
                       GROUP BY c.id 
                       ORDER BY c.consultory_number";
$consultories_result = mysqli_query($conection, $consultories_query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Consultorios - Admin</title>
    <link rel="shortcut icon" href="../img/img_paciente.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #1a1a2e; color: #fff; padding: 40px; }
        .container { max-width: 1200px; margin: 0 auto; background: #16213e; border-radius: 20px; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-back { padding: 10px 20px; background: #3498db; color: #fff; text-decoration: none; border-radius: 10px; }
        .btn-add { padding: 10px 20px; background: #2ecc71; color: #fff; border: none; border-radius: 10px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        th { background: #0f3460; }
        .btn-edit, .btn-delete { padding: 5px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; margin: 0 3px; display: inline-block; cursor: pointer; }
        .btn-edit { background: #f39c12; color: #fff; }
        .btn-delete { background: #e74c3c; color: #fff; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: #16213e; padding: 30px; border-radius: 20px; min-width: 400px; }
        .modal-content input { width: 100%; padding: 10px; margin: 10px 0; background: #0f3460; border: none; border-radius: 8px; color: #fff; }
        .modal-buttons { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gestionar Consultorios</h1>
            <div>
                <button class="btn-add" onclick="openModal('add')">+ Nuevo Consultorio</button>
                <a href="dashboard.php" class="btn-back">← Volver</a>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>N° Consultorio</th>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Médicos Asignados</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($c = mysqli_fetch_assoc($consultories_result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['consultory_number']); ?></td>
                    <td><?php echo htmlspecialchars($c['name']); ?></td>
                    <td><?php echo htmlspecialchars($c['location']); ?></td>
                    <td><?php echo $c['doctors_count']; ?> médicos</td>
                    <td>
                        <button class="btn-edit" onclick="openModal('edit', <?php echo $c['id']; ?>, '<?php echo htmlspecialchars($c['consultory_number']); ?>', '<?php echo htmlspecialchars($c['name']); ?>', '<?php echo htmlspecialchars($c['location']); ?>')">Editar</button>
                        <button class="btn-delete" onclick="deleteConsultory(<?php echo $c['id']; ?>)">Eliminar</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div id="modal" class="modal">
        <div class="modal-content">
            <h2 id="modal-title">Agregar Consultorio</h2>
            <form method="POST" id="modal-form">
                <input type="hidden" name="action" id="form-action">
                <input type="hidden" name="id" id="consultory-id">
                <div class="form-group">
                    <label>Número de Consultorio</label>
                    <input type="text" name="consultory_number" id="consultory-number" required>
                </div>
                <div class="form-group">
                    <label>Nombre del Consultorio</label>
                    <input type="text" name="name" id="consultory-name">
                </div>
                <div class="form-group">
                    <label>Ubicación</label>
                    <input type="text" name="location" id="consultory-location" placeholder="Ej: Primer piso, ala norte">
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-back" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-add">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(action, id = '', number = '', name = '', location = '') {
            const modal = document.getElementById('modal');
            const formAction = document.getElementById('form-action');
            const consultoryId = document.getElementById('consultory-id');
            const consultoryNumber = document.getElementById('consultory-number');
            const consultoryName = document.getElementById('consultory-name');
            const consultoryLocation = document.getElementById('consultory-location');
            const modalTitle = document.getElementById('modal-title');
            
            if (action === 'add') {
                formAction.value = 'add';
                consultoryId.value = '';
                consultoryNumber.value = '';
                consultoryName.value = '';
                consultoryLocation.value = '';
                modalTitle.textContent = 'Agregar Consultorio';
            } else {
                formAction.value = 'edit';
                consultoryId.value = id;
                consultoryNumber.value = number;
                consultoryName.value = name;
                consultoryLocation.value = location;
                modalTitle.textContent = 'Editar Consultorio';
            }
            
            modal.style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }
        
        function deleteConsultory(id) {
            if (confirm('¿Eliminar este consultorio? Los médicos asignados quedarán sin consultorio asignado.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>