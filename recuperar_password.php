<?php
require_once 'conexion.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $usuario = $_POST['usuario'] ?? '';

    // Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ? AND usuario = ?");
    $stmt->execute([$correo, $usuario]);
    $user = $stmt->fetch();

    if ($user) {
        // Usuario encontrado - mostrar contraseña
        $mensaje = "Tu contraseña es: <strong>" . htmlspecialchars($user['contraseña']) . "</strong>";
        $tipo_mensaje = 'success';
    } else {
        $mensaje = 'No se encontró ninguna cuenta con ese correo y usuario';
        $tipo_mensaje = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña | Sistema Frutería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 800px;
            height: 800px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-50px) rotate(5deg); }
        }

        .recovery-container {
            max-width: 500px;
            width: 90%;
            background: white;
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 45px 40px;
            position: relative;
            z-index: 10;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .recovery-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .recovery-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .recovery-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .recovery-header p {
            color: #666;
            font-size: 1rem;
            margin: 0;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 5px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 5px solid #dc3545;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #4facfe;
            font-size: 1.1rem;
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #e0e0e0;
            padding: 14px 18px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 0.3rem rgba(79, 172, 254, 0.15);
        }

        .btn-recovery {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(79, 172, 254, 0.3);
            margin-top: 10px;
        }

        .btn-recovery:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.4);
            color: white;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #4facfe;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: #00f2fe;
            transform: translateX(-3px);
        }

        .info-box {
            background: linear-gradient(135deg, #fff3cd, #ffecb5);
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
            border-left: 5px solid #ffc107;
        }

        .info-box p {
            margin: 0;
            color: #856404;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .info-box i {
            font-size: 1.3rem;
            margin-top: 2px;
        }

        @media (max-width: 576px) {
            .recovery-container {
                padding: 35px 25px;
            }

            .recovery-header h1 {
                font-size: 1.6rem;
            }

            .recovery-icon {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <!-- Header -->
        <div class="recovery-header">
            <div class="recovery-icon">
                <i class="bi bi-key-fill"></i>
            </div>
            <h1>Recuperar Contraseña</h1>
            <p>Ingresa tu correo y usuario para recuperar tu contraseña</p>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <i class="bi bi-<?= $tipo_mensaje === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-envelope-fill"></i>
                    Correo Electrónico
                </label>
                <input type="email" class="form-control" name="correo" required 
                       placeholder="tu@correo.com"
                       value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-person-circle"></i>
                    Usuario
                </label>
                <input type="text" class="form-control" name="usuario" required 
                       placeholder="Tu nombre de usuario"
                       value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-recovery">
                <i class="bi bi-key"></i>
                Recuperar Contraseña
            </button>
        </form>

        <!-- Información -->
        <div class="info-box">
            <p>
                <i class="bi bi-lightbulb-fill"></i>
                <span>Necesitas proporcionar tanto tu correo electrónico como tu nombre de usuario para recuperar tu contraseña.</span>
            </p>
        </div>

        <!-- Link de regreso -->
        <div class="back-link">
            <a href="login.php">
                <i class="bi bi-arrow-left-circle"></i>
                Volver al inicio de sesión
            </a>
        </div>
    </div>
</body>
</html>