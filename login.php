<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'conexion.php';
    $usuario = $_POST['usuario'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'usuario';

    // Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && $user['contraseña'] === $contraseña) {
        // Verificar si es administrador en la tabla administradores
        $stmtAdmin = $pdo->prepare("SELECT * FROM administradores WHERE usuario_id = ?");
        $stmtAdmin->execute([$user['id']]);
        $admin = $stmtAdmin->fetch();
        $es_administrador = (bool)$admin;

        // VALIDACIÓN ESTRICTA DE ROLES
        if ($tipo_usuario === 'admin') {
            if ($es_administrador) {
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombre_completo'];
                $_SESSION['es_admin'] = true;
                $_SESSION['usuario_id'] = $user['id'];
                
                $stmtUpdate = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
                $stmtUpdate->execute([$user['id']]);
                
                header('Location: admin_perfil.php');
                exit();
            } else {
                $error = 'No tienes permisos de administrador.';
            }
        } else {
            if (!$es_administrador) {
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombre_completo'];
                $_SESSION['es_admin'] = false;
                $_SESSION['usuario_id'] = $user['id'];
                
                $stmtUpdate = $pdo->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
                $stmtUpdate->execute([$user['id']]);
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Eres administrador. Debes seleccionar "Administrador" en el tipo de usuario.';
            }
        }
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistema Frutería</title>
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

        .login-wrapper {
            display: flex;
            max-width: 900px;
            width: 100%;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 10;
            animation: slideIn 0.5s ease-out;
            min-height: 550px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Panel Izquierdo */
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .logo-icon-left {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 3.5rem;
            color: #667eea;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .welcome-text {
            font-size: 1rem;
            opacity: 0.95;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 15px;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
        }

        .feature-item i {
            font-size: 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 10px;
        }

        /* Panel Derecho */
        .login-right {
            flex: 1;
            padding: 50px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #333;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: #fee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 1.1rem;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px 12px 45px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            outline: none;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 5px;
        }

        .password-toggle:hover {
            color: #764ba2;
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
            margin-top: 10px;
            cursor: pointer;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .login-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .login-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-links a:hover {
            color: #764ba2;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }

        .divider span {
            padding: 0 15px;
            color: #999;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .register-prompt {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .register-prompt a {
            color: #667eea;
            font-weight: 700;
            text-decoration: none;
        }

        .register-prompt a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                max-width: 450px;
            }

            .login-left {
                padding: 40px 30px;
                min-height: 300px;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .features {
                display: none;
            }

            .login-right {
                padding: 40px 30px;
            }

            .login-header h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Panel Izquierdo -->
        <div class="login-left">
            <div class="welcome-content">
                <div class="logo-icon-left">
                    <i class="bi bi-apple"></i>
                </div>
                <h1 class="welcome-title">Sistema Frutería</h1>
                <p class="welcome-text">
                    Gestiona tu inventario de forma eficiente y moderna
                </p>
                <div class="features">
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Control de inventario</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Alertas de stock bajo</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Gestión de proveedores</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho -->
        <div class="login-right">
            <div class="login-header">
                <h2>Iniciar Sesión</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-circle input-icon"></i>
                        <input type="text" class="form-control" name="usuario" required 
                               placeholder="Ingresa tu usuario"
                               value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" class="form-control" name="contraseña" 
                               id="password" required placeholder="Ingresa tu contraseña">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipo de usuario</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-badge input-icon"></i>
                        <select class="form-select" name="tipo_usuario" required>
                            <option value="usuario" <?= ($_POST['tipo_usuario'] ?? '') === 'usuario' ? 'selected' : '' ?>>
                                Usuario Normal
                            </option>
                            <option value="admin" <?= ($_POST['tipo_usuario'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                Administrador
                            </option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Iniciar Sesión
                </button>

                <div class="login-links">
                    <a href="recuperar_password.php">¿Olvidaste tu contraseña?</a>
                </div>

                <div class="divider">
                    <span>O</span>
                </div>

                <div class="register-prompt">
                    ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
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
    </script>
</body>
</html>