<?php
require_once 'conexion.php';

// Get doctor ID from URL parameter or use default
$doctor_id = isset($_GET['doctor']) ? (int)$_GET['doctor'] : 1;

$query = mysqli_query($conection, "SELECT * FROM medico WHERE idmed = $doctor_id AND estado = 1");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    // If doctor not found, try to get first available
    $query = mysqli_query($conection, "SELECT * FROM medico WHERE estado = 1 LIMIT 1");
    $data = mysqli_fetch_assoc($query);
    if (!$data) {
        die("No hay médicos registrados");
    }
}

if ($data["foto"] != 'img_paciente.jpg' && $data["foto"] != 'img_paciente.png') {
    $foto = 'assets/uploads/doctors' . $data['foto'];
    if (!file_exists($foto)) {
        $foto = 'assets/uploads/doctors/' . $data['foto'];
        if (!file_exists($foto)) {
            $foto = 'img/img_paciente.jpg';
        }
    }
} else {
    $foto = 'img/img_paciente.jpg';
}

// Get waiting patients for this doctor
$waiting_patients = [];
$patients_query = "SELECT * FROM waiting_patients WHERE medico_id = {$data['idmed']} AND status IN ('waiting', 'in_consultation') ORDER BY position ASC";
$patients_result = mysqli_query($conection, $patients_query);
while ($row = mysqli_fetch_assoc($patients_result)) {
    $waiting_patients[] = $row;
}

$current_patient = '';
$next_patient = '';
foreach ($waiting_patients as $index => $patient) {
    if ($patient['status'] == 'in_consultation') {
        $current_patient = $patient['patient_name'];
    } elseif ($patient['status'] == 'waiting' && !$next_patient) {
        $next_patient = $patient['patient_name'];
    }
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
        /* Add styles for patient queue display */
        .patient-queue {
            margin-top: 20px;
        }
        .patient-card {
            background: var(--eerie-black1);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #f9a826;
        }
        .patient-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--white2);
        }
        .patient-position {
            font-size: 12px;
            color: var(--light-gray70);
        }
        .current-patient {
            border-left-color: #2ecc71;
            background: rgba(46, 204, 113, 0.1);
        }
        .waiting-patient {
            border-left-color: #f39c12;
        }
    </style>
</head>
<body>
    <main>
        <aside class="sidebar" data-sidebar>
            <div class="sidebar-info">
                <figure class="avatar-box">
                    <img src="<?php echo $foto; ?>" alt="avatar" width="80">
                </figure>
                <div class="info-content">
                    <h1 class="name" title="nombre"><?php echo htmlspecialchars($data["nombrem"]); ?></h1>
                    <p class="title"><?php echo htmlspecialchars($data["especialidad"]); ?></p>
                    <p class="title1">CMVZUL: <?php echo htmlspecialchars($data["comezu"]); ?></p>
                    <p class="title1">CMV: <?php echo htmlspecialchars($data["mpps"]); ?></p>
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
                            <time><?php echo htmlspecialchars($data["fechas_consul"]); ?></time>
                            <p class="contact-title">Horarios</p>
                            <time><?php echo htmlspecialchars($data["horarios"]); ?></time>
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
                    <h2 class="h2 article-title">Consultorio N° <?php echo htmlspecialchars($data["consultorio"]); ?></h2>
                </header>
                <section class="service">
                    <ul class="service-list">
                        <li class="service-item">
                            <div class="service-content-box">
                                <h4 class="h4 service-item-title">CMV:</h4>
                                <h4 class="h4 service-item-title"><?php echo htmlspecialchars($data["mpps"]); ?></h4>
                            </div>
                        </li>
                        <li class="service-item">
                            <div class="service-content-box">
                                <h4 class="h4 service-item-title">CMVZUL:</h4>
                                <h4 class="h4 service-item-title"><?php echo htmlspecialchars($data["comezu"]); ?></h4>
                            </div>
                        </li>
                        <li class="service-item">
                            <div class="service-content-box">
                                <h4 class="h4 service-item-title">Especialidades:</h4>
                                <h4 class="h4 service-item-title"><?php echo htmlspecialchars($data["pacientes"]); ?></h4>
                            </div>
                        </li>
                        <li class="service-item">
                            <div class="service-content-box">
                                <h4 class="h4 service-item-title">Paciente Actual</h4>
                                <h4 class="h4 service-item-title"><?php echo $current_patient ? htmlspecialchars($current_patient) : '---'; ?></h4>
                                <h4 class="h4 service-item-title">Paciente Siguiente</h4>
                                <h5 class="h4 service-item-title"><?php echo $next_patient ? htmlspecialchars($next_patient) : '---'; ?></h5>
                            </div>
                        </li>
                    </ul>
                </section>
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
                <?php if (count($waiting_patients) > 0): ?>
                <section class="patient-queue">
                    <h3 class="h3 clients-title">Lista de Espera</h3>
                    <?php foreach ($waiting_patients as $patient): ?>
                        <div class="patient-card <?php echo $patient['status'] == 'in_consultation' ? 'current-patient' : 'waiting-patient'; ?>">
                            <div class="patient-name">
                                <?php echo htmlspecialchars($patient['patient_name']); ?>
                                <?php if ($patient['status'] == 'in_consultation'): ?>
                                    <span style="font-size: 12px; color: #2ecc71;">(En consulta)</span>
                                <?php endif; ?>
                            </div>
                            <div class="patient-position">Posición: <?php echo $patient['position']; ?></div>
                            <?php if ($patient['estimated_time']): ?>
                                <div class="patient-position">Tiempo estimado: <?php echo htmlspecialchars($patient['estimated_time']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </section>
                <?php endif; ?>
            </article>
        </div>
    </main>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script src="script.js"></script>
    <script src="clock.js"></script>
</body>
</html>