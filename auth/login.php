<?php
require_once '../conexion.php';
require_once '../includes/functions.php';

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } elseif (isDoctor()) {
        redirect('../doctor/dashboard.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor ingrese usuario y contraseña';
    } else {
        // Hash the password with MD5 (matching your existing system)
        $hashed_password = md5($password);
        
        $query = "SELECT u.*, r.rol as role_name 
                  FROM usuario u 
                  JOIN rol r ON u.rol = r.idrol 
                  WHERE u.usuario = ? AND u.clave = ? AND u.estatus = 1";
        
        $stmt = mysqli_prepare($conection, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            $_SESSION['user_id'] = $user['idusuario'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_role'] = $user['rol'];
            $_SESSION['user_role_name'] = $user['role_name'];
            
            // If user is a doctor (role 3), get their doctor info
            if ($user['rol'] == 3) {
                $doc_query = "SELECT idmed FROM medico WHERE usuario_id = ? AND estado = 1";
                $doc_stmt = mysqli_prepare($conection, $doc_query);
                mysqli_stmt_bind_param($doc_stmt, "i", $user['idusuario']);
                mysqli_stmt_execute($doc_stmt);
                $doc_result = mysqli_stmt_get_result($doc_stmt);
                
                if ($doctor = mysqli_fetch_assoc($doc_result)) {
                    $_SESSION['doctor_id'] = $doctor['idmed'];
                }
            }
            
            if ($user['rol'] == 1) {
                redirect('../admin/dashboard.php');
            } else {
                redirect('../doctor/dashboard.php');
            }
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Médico</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
        }
        
        .logo h2 {
            color: #fff;
            margin-top: 15px;
            font-size: 24px;
        }
        
        .logo p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #f9a826 0%, #f97316 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .error-message {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid rgba(255, 0, 0, 0.5);
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
            color: #ff6b6b;
            text-align: center;
            font-size: 14px;
        }
        
        .info-text {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            margin-top: 20px;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../img/img_paciente.png" alt="Logo">
            <h2>PIDIM</h2>
            <p>Pantalla Integral De Información Médica</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" placeholder="Ingrese su usuario" required>
            </div>
            
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="Ingrese su contraseña" required>
            </div>
            
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>
        
        <div class="info-text">
            <p>Sistema de Gestión de Consultorios Médicos</p>
        </div>
    </div>
</body>
</html>