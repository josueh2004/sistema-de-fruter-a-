<?php
require_once 'conexion.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre_completo' => $_POST['nombre_completo'] ?? '',
        'usuario' => $_POST['usuario'] ?? '',
        'contraseña' => $_POST['contraseña'] ?? '',
        'confirmar_contraseña' => $_POST['confirmar_contraseña'] ?? '',
        'correo' => $_POST['correo'] ?? '',
        'celular' => $_POST['celular'] ?? ''
    ];

    // Validaciones
    if (empty($datos['nombre_completo']) || empty($datos['usuario']) || 
        empty($datos['contraseña']) || empty($datos['correo']) || empty($datos['celular'])) {
        $mensaje = 'Todos los campos son obligatorios';
        $tipo_mensaje = 'danger';
    } elseif ($datos['contraseña'] !== $datos['confirmar_contraseña']) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipo_mensaje = 'danger';
    } elseif (strlen($datos['contraseña']) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
        $tipo_mensaje = 'danger';
    } else {
        // Verificar si el usuario ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? OR correo = ?");
        $stmt->execute([$datos['usuario'], $datos['correo']]);
        
        if ($stmt->fetch()) {
            $mensaje = 'El usuario o correo ya están registrados';
            $tipo_mensaje = 'danger';
        } else {
            // Registrar usuario
            try {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, usuario, contraseña, correo, celular) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([
                    $datos['nombre_completo'],
                    $datos['usuario'],
                    $datos['contraseña'],
                    $datos['correo'],
                    $datos['celular']
                ])) {
                    $mensaje = '¡Registro exitoso! Ya puedes iniciar sesión';
                    $tipo_mensaje = 'success';
                    // Limpiar campos
                    $datos = array_fill_keys(array_keys($datos), '');
                } else {
                    $mensaje = 'Error al registrar el usuario';
                    $tipo_mensaje = 'danger';
                }
            } catch (PDOException $e) {
                $mensaje = 'Error en el registro: ' . $e->getMessage();
                $tipo_mensaje = 'danger';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | Sistema Frutería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 30px 0;
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

        .register-container {
            max-width: 600px;
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

        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .register-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(67, 233, 123, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .register-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .register-header p {
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
            color: #43e97b;
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
            border-color: #43e97b;
            box-shadow: 0 0 0 0.3rem rgba(67, 233, 123, 0.15);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #43e97b;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 5px;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #38f9d7;
            transform: translateY(-50%) scale(1.1);
        }

        .password-strength {
            margin-top: 8px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }

        .btn-register {
            background: linear-gradient(135deg, #43e97b, #38f9d7);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(67, 233, 123, 0.3);
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(67, 233, 123, 0.4);
            color: white;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
        }

        .login-link a {
            color: #43e97b;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #38f9d7;
            transform: translateX(-3px);
        }

        .requirements-box {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
            border-left: 5px solid #2196f3;
        }

        .requirements-box h6 {
            color: #1565c0;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .requirements-box ul {
            margin: 0;
            padding-left: 20px;
            color: #1976d2;
        }

        .requirements-box li {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .register-container {
                padding: 35px 25px;
            }

            .register-header h1 {
                font-size: 1.6rem;
            }

            .register-icon {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <div class="register-icon">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <h1>Crear Cuenta</h1>
            <p>Completa el formulario para registrarte</p>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <i class="bi bi-<?= $tipo_mensaje === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
                <?= $mensaje ?>
                <?php if ($tipo_mensaje === 'success'): ?>
                    <br><a href="login.php" style="color: #155724; font-weight: 700; text-decoration: underline;">Ir al inicio de sesión</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">
                        <i class="bi bi-person-fill"></i>
                        Nombre Completo
                    </label>
                    <input type="text" class="form-control" name="nombre_completo" required 
                           placeholder="Juan Pérez López"
                           value="<?= htmlspecialchars($datos['nombre_completo'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-person-circle"></i>
                        Usuario
                    </label>
                    <input type="text" class="form-control" name="usuario" required 
                           placeholder="usuario123"
                           value="<?= htmlspecialchars($datos['usuario'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-envelope-fill"></i>
                        Correo Electrónico
                    </label>
                    <input type="email" class="form-control" name="correo" required 
                           placeholder="correo@ejemplo.com"
                           value="<?= htmlspecialchars($datos['correo'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">
                        <i class="bi bi-phone-fill"></i>
                        Celular
                    </label>
                    <input type="tel" class="form-control" name="celular" required 
                           placeholder="555-1234-5678"
                           value="<?= htmlspecialchars($datos['celular'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-lock-fill"></i>
                        Contraseña
                    </label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" name="contraseña" 
                               id="password" required placeholder="Mínimo 6 caracteres"
                               onkeyup="checkPasswordStrength()">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                            <i class="bi bi-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="strengthIndicator"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-lock-fill"></i>
                        Confirmar Contraseña
                    </label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" name="confirmar_contraseña" 
                               id="confirm_password" required placeholder="Repite tu contraseña">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                            <i class="bi bi-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-register">
                <i class="bi bi-check-circle-fill"></i>
                Registrarse
            </button>
        </form>

        <!-- Requisitos -->
        <div class="requirements-box">
            <h6>
                <i class="bi bi-info-circle-fill"></i>
                Requisitos de la contraseña:
            </h6>
            <ul>
                <li>Mínimo 6 caracteres</li>
                <li>Se recomienda usar letras, números y símbolos</li>
                <li>Evita usar información personal obvia</li>
            </ul>
        </div>

        <!-- Link de login -->
        <div class="login-link">
            <a href="login.php">
                <i class="bi bi-arrow-left-circle"></i>
                ¿Ya tienes cuenta? Inicia sesión aquí
            </a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Check password strength
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const indicator = document.getElementById('strengthIndicator');
            
            if (password.length === 0) {
                indicator.textContent = '';
                return;
            }
            
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength <= 2) {
                indicator.textContent = '⚠️ Contraseña débil';
                indicator.className = 'password-strength strength-weak';
            } else if (strength <= 4) {
                indicator.textContent = '✓ Contraseña media';
                indicator.className = 'password-strength strength-medium';
            } else {
                indicator.textContent = '✓✓ Contraseña fuerte';
                indicator.className = 'password-strength strength-strong';
            }
        }
    </script>
</body>
</html>