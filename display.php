<?php
require_once 'conexion.php';
require_once 'includes/functions.php';

// Get doctor ID from URL parameter or show all doctors
$doctor_id = isset($_GET['doctor']) ? (int)$_GET['doctor'] : 0;
$consultory_id = isset($_GET['consultory']) ? (int)$_GET['consultory'] : 0;
$view_mode = isset($_GET['mode']) ? $_GET['mode'] : 'single'; // single, grid, consultory

// Auto-refresh every 30 seconds
$page = $_SERVER['PHP_SELF'];
$sec = "30";
header("Refresh: $sec; url=$page" . ($doctor_id ? "?doctor=$doctor_id" : ($consultory_id ? "?consultory=$consultory_id" : "")));

// Get all active doctors
$all_doctors_query = "SELECT m.*, c.consultory_number, c.name as consultory_name 
                      FROM medico m 
                      LEFT JOIN consultory c ON m.consultory_id = c.id 
                      WHERE m.estado = 1 
                      ORDER BY c.consultory_number ASC, m.nombrem ASC";
$all_doctors_result = mysqli_query($conection, $all_doctors_query);
$all_doctors = [];
while ($row = mysqli_fetch_assoc($all_doctors_result)) {
    $all_doctors[] = $row;
}

// Get specific doctor if requested
$doctor = null;
if ($doctor_id > 0) {
    $doctor_query = "SELECT m.*, c.consultory_number, c.name as consultory_name 
                     FROM medico m 
                     LEFT JOIN consultory c ON m.consultory_id = c.id 
                     WHERE m.idmed = $doctor_id AND m.estado = 1";
    $doctor_result = mysqli_query($conection, $doctor_query);
    $doctor = mysqli_fetch_assoc($doctor_result);
}

// Get consultory if requested
$consultory = null;
if ($consultory_id > 0) {
    $consultory_query = "SELECT * FROM consultory WHERE id = $consultory_id AND status = 1";
    $consultory_result = mysqli_query($conection, $consultory_query);
    $consultory = mysqli_fetch_assoc($consultory_result);
}

// Function to get waiting patients for a doctor
function getDoctorPatients($conn, $doctor_id) {
    $patients = [];
    $query = "SELECT * FROM waiting_patients 
              WHERE medico_id = $doctor_id 
              AND status IN ('waiting', 'in_consultation') 
              ORDER BY position ASC";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $patients[] = $row;
    }
    return $patients;
}

// Function to get doctor photo
function getDoctorPhoto($photo) {
    if ($photo && $photo != 'img_paciente.jpg' && $photo != 'img_paciente.png') {
        if (file_exists("assets/uploads/doctors/" . $photo)) {
            return "assets/uploads/doctors/" . $photo;
        } elseif (file_exists("img/uploads/" . $photo)) {
            return "img/uploads/" . $photo;
        }
    }
    return "img/img_paciente.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>*PIDIM* Pantalla Integral De Informacion Medica</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="./img/img_paciente.png" type="image/x-icon">
    <style>
        /* Multi-doctor display styles */
        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .doctor-card {
            background: var(--eerie-black2);
            border-radius: 20px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .doctor-card:hover {
            transform: translateY(-5px);
        }
        
        .doctor-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            text-align: center;
            color: white;
        }
        
        .doctor-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            margin-bottom: 10px;
        }
        
        .consultory-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .doctor-info {
            padding: 20px;
        }
        
        .current-patient {
            background: rgba(46, 204, 113, 0.1);
            border-left: 4px solid #2ecc71;
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
        }
        
        .waiting-list {
            margin-top: 15px;
        }
        
        .waiting-item {
            padding: 8px;
            border-bottom: 1px solid var(--jet);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .waiting-position {
            background: var(--onyx);
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 500;
        }
        
        .status-consultation {
            background: #3498db;
            color: white;
        }
        
        .status-waiting {
            background: #f39c12;
            color: white;
        }
        
        .nav-controls {
            background: var(--eerie-black2);
            padding: 15px 20px;
            border-radius: 20px;
            margin: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .view-buttons {
            display: flex;
            gap: 10px;
        }
        
        .view-btn {
            padding: 8px 20px;
            background: var(--onyx);
            color: var(--light-gray);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .view-btn.active,
        .view-btn:hover {
            background: #f9a826;
            color: white;
        }
        
        .consultory-filter {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .consultory-btn {
            padding: 8px 15px;
            background: var(--onyx);
            color: var(--light-gray);
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .consultory-btn:hover,
        .consultory-btn.active {
            background: #f9a826;
            color: white;
        }
        
        .doctor-detail-view {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: var(--onyx);
            color: var(--light-gray);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background: #f9a826;
            color: white;
        }
        
        .refresh-time {
            font-size: 12px;
            color: var(--light-gray70);
        }
        
        @media (max-width: 768px) {
            .doctors-grid {
                grid-template-columns: 1fr;
                padding: 10px;
            }
            
            .nav-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .view-buttons,
            .consultory-filter {
                justify-content: center;
            }
        }
        
        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .consulting-animation {
            animation: pulse 2s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <?php if ($view_mode == 'single' && $doctor): ?>
        <!-- Single Doctor View -->
        <div class="doctor-detail-view">
            <a href="display.php?mode=grid" class="back-button">← Ver todos los médicos</a>
            
            <?php
            $patients = getDoctorPatients($conection, $doctor['idmed']);
            $current_patient = '';
            $waiting_patients = [];
            foreach ($patients as $patient) {
                if ($patient['status'] == 'in_consultation') {
                    $current_patient = $patient;
                } elseif ($patient['status'] == 'waiting') {
                    $waiting_patients[] = $patient;
                }
            }
            $photo_url = getDoctorPhoto($doctor['foto']);
            ?>
            
            <main>
                <aside class="sidebar" data-sidebar>
                    <div class="sidebar-info">
                        <figure class="avatar-box">
                            <img src="<?php echo $photo_url; ?>" alt="avatar" width="80">
                        </figure>
                        <div class="info-content">
                            <h1 class="name" title="nombre"><?php echo htmlspecialchars($doctor["nombrem"]); ?></h1>
                            <p class="title"><?php echo htmlspecialchars($doctor["especialidad"]); ?></p>
                            <p class="title1">CMVZUL: <?php echo htmlspecialchars($doctor["comezu"]); ?></p>
                            <p class="title1">CMV: <?php echo htmlspecialchars($doctor["mpps"]); ?></p>
                            <p class="title1">Consultorio: <?php echo htmlspecialchars($doctor["consultory_number"] ?: $doctor["consultorio"]); ?></p>
                        </div>
                        <button class="info-more-btn" data-sidebar-btn>
                            <span>Show</span>
                            <ion-icon name="chevron-down"></ion-icon>
                        </button>
                    </div>
                    <div class="sidebar-info-more">
                        <div class="separator"></div>
                        <ul class="contacts-list">
                            <li class="contact-item">
                                <div class="icon-box">
                                    <ion-icon name="calendar-outline"></ion-icon>
                                </div>
                                <div class="contact-info">
                                    <p class="contact-title">Fecha de consulta</p>
                                    <time><?php echo htmlspecialchars($doctor["fechas_consul"]); ?></time>
                                    <p class="contact-title">Horarios</p>
                                    <time><?php echo htmlspecialchars($doctor["horarios"]); ?></time>
                                </div>
                            </li>
                            <p class="time">
                                <?php
                                setlocale(LC_TIME, 'es_VE.UTF-8', 'esp');
                                date_default_timezone_set('America/Caracas');
                                echo strftime("%A, %d %B %Y");
                                ?>
                            </p>
                            <p id="clock" class="clock">.</p>
                        </ul>
                        <div class="separator"></div>
                    </div>
                </aside>
                <div class="main-content">
                    <article class="about active" data-page="about">
                        <header>
                            <h2 class="h2 article-title">Consultorio <?php echo htmlspecialchars($doctor["consultory_number"] ?: $doctor["consultorio"]); ?></h2>
                        </header>
                        <section class="service">
                            <ul class="service-list">
                                <li class="service-item">
                                    <div class="service-content-box">
                                        <h4 class="h4 service-item-title">CMV:</h4>
                                        <h4 class="h4 service-item-title"><?php echo htmlspecialchars($doctor["mpps"]); ?></h4>
                                    </div>
                                </li>
                                <li class="service-item">
                                    <div class="service-content-box">
                                        <h4 class="h4 service-item-title">CMVZUL:</h4>
                                        <h4 class="h4 service-item-title"><?php echo htmlspecialchars($doctor["comezu"]); ?></h4>
                                    </div>
                                </li>
                                <li class="service-item">
                                    <div class="service-content-box">
                                        <h4 class="h4 service-item-title">Especialidades:</h4>
                                        <h4 class="h4 service-item-title"><?php echo htmlspecialchars($doctor["pacientes"]); ?></h4>
                                    </div>
                                </li>
                                <li class="service-item">
                                    <div class="service-content-box">
                                        <h4 class="h4 service-item-title">Paciente Actual</h4>
                                        <h4 class="h4 service-item-title <?php echo $current_patient ? 'consulting-animation' : ''; ?>">
                                            <?php echo $current_patient ? htmlspecialchars($current_patient['patient_name']) : '---'; ?>
                                        </h4>
                                        <h4 class="h4 service-item-title">Paciente Siguiente</h4>
                                        <h5 class="h4 service-item-title">
                                            <?php 
                                            if (!empty($waiting_patients)) {
                                                echo htmlspecialchars($waiting_patients[0]['patient_name']);
                                            } else {
                                                echo '---';
                                            }
                                            ?>
                                        </h5>
                                    </div>
                                </li>
                            </ul>
                        </section>
                        
                        <?php if (!empty($waiting_patients) || $current_patient): ?>
                        <section class="patient-queue" style="margin-top: 20px;">
                            <h3 class="h3 clients-title">Lista de Espera</h3>
                            <?php if ($current_patient): ?>
                                <div class="current-patient" style="background: rgba(46, 204, 113, 0.1); border-left: 4px solid #2ecc71; padding: 15px; margin: 10px 0; border-radius: 10px;">
                                    <strong>🔴 EN CONSULTA:</strong> <?php echo htmlspecialchars($current_patient['patient_name']); ?>
                                    <span style="font-size: 12px; margin-left: 10px;">Posición: <?php echo $current_patient['position']; ?></span>
                                </div>
                            <?php endif; ?>
                            <?php foreach ($waiting_patients as $index => $patient): ?>
                                <div style="padding: 10px; border-bottom: 1px solid var(--jet); display: flex; justify-content: space-between;">
                                    <span><strong><?php echo $index + 1; ?>.</strong> <?php echo htmlspecialchars($patient['patient_name']); ?></span>
                                    <?php if ($patient['estimated_time']): ?>
                                        <span style="font-size: 12px; color: var(--light-gray70);">⏱️ <?php echo htmlspecialchars($patient['estimated_time']); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </section>
                        <?php endif; ?>
                        
                        <section class="clients">
                            <h3 class="h3 clients-title">Otros Servicios Disponibles</h3>
                            <ul class="clients-list has-scrollbar">
                                <li class="clients-item"><a href="#"><img src="img/servicios/ECO.png" alt="logo"></a></li>
                                <li class="clients-item"><a href="#"><img src="img/servicios/LAB.png" alt="logo"></a></li>
                                <li class="clients-item"><a href="#"><img src="img/servicios/RX.png" alt="logo"></a></li>
                            </ul>
                            <ul class="clients-list has-scrollbar">
                                <li class="clients-item"><a href="#"><img src="img/servicios/TOMO.png" alt="logo"></a></li>
                                <li class="clients-item"><a href="#"><img src="img/servicios/PANO.png" alt="logo"></a></li>
                                <li class="clients-item"><a href="#"><img src="img/servicios/MAMO.png" alt="logo"></a></li>
                            </ul>
                        </section>
                    </article>
                </div>
            </main>
        </div>
        
    <?php elseif ($view_mode == 'consultory' && $consultory): ?>
        <!-- Consultory View - Show all doctors in a specific consultory -->
        <div class="doctor-detail-view">
            <div class="nav-controls">
                <a href="display.php?mode=grid" class="view-btn">← Ver todos</a>
                <div class="refresh-time">
                    Actualizando automáticamente cada 30 segundos
                </div>
            </div>
            
            <h2 style="text-align: center; margin: 20px 0;">Consultorio <?php echo htmlspecialchars($consultory['consultory_number']); ?> - <?php echo htmlspecialchars($consultory['name']); ?></h2>
            
            <div class="doctors-grid">
                <?php 
                $consultory_doctors = array_filter($all_doctors, function($d) use ($consultory) {
                    return $d['consultory_id'] == $consultory['id'];
                });
                
                if (count($consultory_doctors) > 0):
                    foreach ($consultory_doctors as $doc):
                        $doc_patients = getDoctorPatients($conection, $doc['idmed']);
                        $doc_current = '';
                        $doc_waiting = [];
                        foreach ($doc_patients as $p) {
                            if ($p['status'] == 'in_consultation') $doc_current = $p;
                            elseif ($p['status'] == 'waiting') $doc_waiting[] = $p;
                        }
                        $doc_photo = getDoctorPhoto($doc['foto']);
                ?>
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <img src="<?php echo $doc_photo; ?>" alt="Doctor" class="doctor-avatar">
                            <h3><?php echo htmlspecialchars($doc['nombrem']); ?></h3>
                            <p><?php echo htmlspecialchars($doc['especialidad']); ?></p>
                            <span class="consultory-badge">Consultorio <?php echo htmlspecialchars($doc['consultory_number']); ?></span>
                        </div>
                        <div class="doctor-info">
                            <?php if ($doc_current): ?>
                                <div class="current-patient">
                                    <strong>🟢 En consulta:</strong><br>
                                    <?php echo htmlspecialchars($doc_current['patient_name']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($doc_waiting)): ?>
                                <div class="waiting-list">
                                    <strong>⏳ En espera (<?php echo count($doc_waiting); ?>):</strong>
                                    <?php foreach (array_slice($doc_waiting, 0, 3) as $idx => $p): ?>
                                        <div class="waiting-item">
                                            <span><?php echo $idx + 1; ?>. <?php echo htmlspecialchars($p['patient_name']); ?></span>
                                            <span class="waiting-position">Pos <?php echo $p['position']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($doc_waiting) > 3): ?>
                                        <div style="text-align: center; margin-top: 5px;">
                                            <small>+<?php echo count($doc_waiting) - 3; ?> más</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <p style="text-align: center; color: var(--light-gray70);">No hay pacientes en espera</p>
                            <?php endif; ?>
                            
                            <div style="text-align: center; margin-top: 15px;">
                                <a href="display.php?doctor=<?php echo $doc['idmed']; ?>&mode=single" class="view-btn" style="display: inline-block;">Ver detalle</a>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <p style="text-align: center; grid-column: 1/-1;">No hay médicos asignados a este consultorio</p>
                <?php endif; ?>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Grid View - Show all doctors -->
        <div class="nav-controls">
            <div class="view-buttons">
                <a href="display.php?mode=grid" class="view-btn active">📋 Vista General</a>
            </div>
            
            <div class="consultory-filter">
                <a href="display.php?mode=grid" class="consultory-btn <?php echo !$consultory_id ? 'active' : ''; ?>">Todos</a>
                <?php 
                $consultories_query = "SELECT * FROM consultory WHERE status = 1 ORDER BY consultory_number";
                $consultories_result = mysqli_query($conection, $consultories_query);
                while ($c = mysqli_fetch_assoc($consultories_result)):
                ?>
                    <a href="display.php?mode=consultory&consultory=<?php echo $c['id']; ?>" class="consultory-btn">
                        Cons. <?php echo htmlspecialchars($c['consultory_number']); ?>
                    </a>
                <?php endwhile; ?>
            </div>
            
            <div class="refresh-time">
                🔄 Actualizando automáticamente cada 30 segundos
            </div>
        </div>
        
        <div class="doctors-grid">
            <?php if (count($all_doctors) > 0): ?>
                <?php foreach ($all_doctors as $doc): 
                    $patients = getDoctorPatients($conection, $doc['idmed']);
                    $current_patient = '';
                    $waiting_patients = [];
                    foreach ($patients as $p) {
                        if ($p['status'] == 'in_consultation') {
                            $current_patient = $p;
                        } elseif ($p['status'] == 'waiting') {
                            $waiting_patients[] = $p;
                        }
                    }
                    $photo_url = getDoctorPhoto($doc['foto']);
                ?>
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <img src="<?php echo $photo_url; ?>" alt="Doctor" class="doctor-avatar">
                            <h3><?php echo htmlspecialchars($doc['nombrem']); ?></h3>
                            <p><?php echo htmlspecialchars($doc['especialidad']); ?></p>
                            <span class="consultory-badge">
                                Consultorio <?php echo htmlspecialchars($doc['consultory_number'] ?: $doc['consultorio']); ?>
                            </span>
                        </div>
                        <div class="doctor-info">
                            <div style="margin-bottom: 10px;">
                                <strong>📋 CMV:</strong> <?php echo htmlspecialchars($doc['mpps']); ?><br>
                                <strong>📋 CMVZUL:</strong> <?php echo htmlspecialchars($doc['comezu']); ?>
                            </div>
                            
                            <?php if ($current_patient): ?>
                                <div class="current-patient">
                                    <strong>🟢 En consulta:</strong><br>
                                    <?php echo htmlspecialchars($current_patient['patient_name']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($waiting_patients)): ?>
                                <div class="waiting-list">
                                    <strong>⏳ Lista de espera (<?php echo count($waiting_patients); ?>):</strong>
                                    <?php foreach (array_slice($waiting_patients, 0, 3) as $idx => $p): ?>
                                        <div class="waiting-item">
                                            <span><?php echo $idx + 1; ?>. <?php echo htmlspecialchars($p['patient_name']); ?></span>
                                            <span class="waiting-position">Pos <?php echo $p['position']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($waiting_patients) > 3): ?>
                                        <div style="text-align: center; margin-top: 5px;">
                                            <small>+<?php echo count($waiting_patients) - 3; ?> pacientes en espera</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <p style="text-align: center; color: var(--light-gray70); padding: 10px;">
                                    No hay pacientes en espera
                                </p>
                            <?php endif; ?>
                            
                            <div style="margin-top: 15px;">
                                <div><strong>🕐 Horario:</strong> <?php echo htmlspecialchars($doc['horarios'] ?: 'No especificado'); ?></div>
                                <div><strong>📅 Días:</strong> <?php echo htmlspecialchars($doc['fechas_consul'] ?: 'No especificado'); ?></div>
                            </div>
                            
                            <div style="text-align: center; margin-top: 15px;">
                                <a href="display.php?doctor=<?php echo $doc['idmed']; ?>&mode=single" class="view-btn" style="display: inline-block;">Ver detalle completo →</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: 1/-1;">No hay médicos registrados</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script src="script.js"></script>
    <script src="clock.js"></script>
</body>
</html>