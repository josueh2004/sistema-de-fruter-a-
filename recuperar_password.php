<?php
/**
 * Sistema de Restablecimiento de Contraseña
 * Nombre: restablecer_password.php
 * Descripción: Permite al usuario crear una nueva contraseña usando el token enviado por email
 */

require_once 'conexion.php';

$mensaje = '';
$tipo_mensaje = '';
$token_valido = false;
$token = '';

// ==================== VERIFICAR TOKEN ====================
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        // Buscar el token en la base de datos
        $stmt = $pdo->prepare("
            SELECT t.*, u.nombre_completo, u.usuario 
            FROM tokens_recuperacion t
            INNER JOIN usuarios u ON t.usuario_id = u.id
            WHERE t.token = ? 
              AND t.usado = 0 
              AND t.expiracion > NOW()
        ");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch();
        
        if ($token_data) {
            $token_valido = true;
        } else {
            // Verificar por qué falló
            $stmt = $pdo->prepare("SELECT * FROM tokens_recuperacion WHERE token = ?");
            $stmt->execute([$token]);
            $token_check = $stmt->fetch();
            
            if (!$token_check) {
                $mensaje = 'El enlace de recuperación no es válido.';
            } elseif ($token_check['usado'] == 1) {
                $mensaje = 'Este enlace ya ha sido utilizado. Solicita uno nuevo.';
            } elseif (strtotime($token_check['expiracion']) < time()) {
                $mensaje = 'Este enlace ha expirado. Solicita uno nuevo.';
            }
            $tipo_mensaje = 'danger';
        }
    } catch (PDOException $e) {
        $mensaje = 'Error al verificar el token. Intenta nuevamente.';
        $tipo_mensaje = 'danger';
        error_log("Error al verificar token: " . $e->getMessage());
    }
} else {
    $mensaje = 'No se proporcionó un token de recuperación.';
    $tipo_mensaje = 'danger';
}

// ==================== PROCESAR CAMBIO DE CONTRASEÑA ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $nueva_contraseña = $_POST['nueva_contraseña'] ?? '';
    $confirmar_contraseña = $_POST['confirmar_contraseña'] ?? '';
    $token_post = $_POST['token'] ?? '';
    
    // Validaciones
    if (empty($nueva_contraseña) || empty($confirmar_contraseña)) {
        $mensaje = 'Todos los campos son obligatorios.';
        $tipo_mensaje = 'danger';
    } elseif ($nueva_contraseña !== $confirmar_contraseña) {
        $mensaje = 'Las contraseñas no coinciden.';
        $tipo_mensaje = 'danger';
    } elseif (strlen($nueva_contraseña) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres.';
        $tipo_mensaje = 'danger';
    } else {
        try {
            // Iniciar transacción
            $pdo->beginTransaction();
            
            // Actualizar la contraseña del usuario
            $stmt = $pdo->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
            $stmt->execute([$nueva_contraseña, $token_data['usuario_id']]);
            
            // Marcar el token como usado
            $stmt = $pdo->prepare("UPDATE tokens_recuperacion SET usado = 1 WHERE token = ?");
            $stmt->execute([$token_post]);
            
            // Confirmar transacción
            $pdo->commit();
            
            // Éxito
            $mensaje = '¡Contraseña actualizada exitosamente! Ya puedes iniciar sesión con tu nueva contraseña.';
            $tipo_mensaje = 'success';
            $token_valido = false; // Ocultar formulario
            
        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            $pdo->rollBack();
            $mensaje = 'Error al actualizar la contraseña. Intenta nuevamente.';
            $tipo_mensaje = 'danger';
            error_log("Error al actualizar contraseña: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña | Sistema Frutería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
            padding: 20px;
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

        .reset-container {
            max-width: 550px;
            width: 100%;
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

        .reset-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .reset-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .reset-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .reset-header p {
            color: #666;
            font-size: 1rem;
            margin: 0;
        }

        .user-info {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #2196f3;
        }

        .user-info strong {
            color: #1565c0;
            display: block;
            margin-bottom: 5px;
        }

        .user-info span {
            color: #1976d2;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert i {
            font-size: 1.5rem;
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
            color: #667eea;
            font-size: 1.1rem;
        }

        .password-wrapper {
            position: relative;
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #e0e0e0;
            padding: 14px 50px 14px 18px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.3rem rgba(102, 126, 234, 0.15);
        }

        .password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 5px;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #764ba2;
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

        .btn-reset {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
            margin-top: 10px;
        }

        .btn-reset:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #764ba2;
            transform: translateX(-3px);
        }

        .info-box {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
            border-left: 5px solid #ffc107;
        }

        .info-box h6 {
            color: #856404;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #856404;
        }

        .info-box li {
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .reset-container {
                padding: 35px 25px;
            }

            .reset-header h1 {
                font-size: 1.6rem;
            }

            .reset-icon {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Header -->
        <div class="reset-header">
            <div class="reset-icon">
                <i class="bi bi-key-fill"></i>
            </div>
            <h1>Restablecer Contraseña</h1>
            <p>Crea una nueva contraseña segura</p>
        </div>

        <!-- Información del usuario (solo si el token es válido) -->
        <?php if ($token_valido): ?>
        <div class="user-info">
            <strong><i class="bi bi-person-fill"></i> Restableciendo contraseña para:</strong>
            <span><?= htmlspecialchars($token_data['nombre_completo']) ?> (<?= htmlspecialchars($token_data['usuario']) ?>)</span>
        </div>
        <?php endif; ?>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <i class="bi bi-<?= $tipo_mensaje === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
                <div>
                    <?= $mensaje ?>
                    <?php if ($tipo_mensaje === 'success'): ?>
                        <br><strong><a href="login.php" style="color: #155724; text-decoration: underline;">Ir al inicio de sesión →</a></strong>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulario (solo si el token es válido) -->
        <?php if ($token_valido): ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-lock-fill"></i>
                    Nueva Contraseña
                </label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" name="nueva_contraseña" 
                           id="password" required placeholder="Mínimo 6 caracteres"
                           onkeyup="checkPasswordStrength()">
                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                        <i class="bi bi-eye" id="toggleIcon1"></i>
                    </button>
                </div>
                <div class="password-strength" id="strengthIndicator"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-lock-fill"></i>
                    Confirmar Nueva Contraseña
                </label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" name="confirmar_contraseña" 
                           id="confirm_password" required placeholder="Repite tu nueva contraseña"
                           onkeyup="checkPasswordMatch()">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                        <i class="bi bi-eye" id="toggleIcon2"></i>
                    </button>
                </div>
                <div class="password-strength" id="matchIndicator"></div>
            </div>

            <button type="submit" class="btn btn-reset">
                <i class="bi bi-check-circle-fill"></i>
                Restablecer Contraseña
            </button>
        </form>

        <!-- Información -->
        <div class="info-box">
            <h6>
                <i class="bi bi-shield-fill-check"></i>
                Consejos de seguridad:
            </h6>
            <ul>
                <li>Usa al menos 6 caracteres</li>
                <li>Combina letras, números y símbolos</li>
                <li>No reutilices contraseñas de otras cuentas</li>
                <li>Evita información personal obvia</li>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Link de regreso -->
        <div class="login-link">
            <a href="login.php">
                <i class="bi bi-arrow-left-circle"></i>
                Volver al inicio de sesión
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
            
            checkPasswordMatch();
        }

        // Check if passwords match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchIndicator = document.getElementById('matchIndicator');
            
            if (confirmPassword.length === 0) {
                matchIndicator.textContent = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchIndicator.textContent = '✓ Las contraseñas coinciden';
                matchIndicator.className = 'password-strength strength-strong';
            } else {
                matchIndicator.textContent = '✗ Las contraseñas no coinciden';
                matchIndicator.className = 'password-strength strength-weak';
            }
        }
    </script>
</body>
</html>
