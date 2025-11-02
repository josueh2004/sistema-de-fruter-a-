<?php
/**
 * Sistema de Recuperación de Contraseña por Email
 * Nombre: recuperar_password_email.php
 * Descripción: Envía enlaces de recuperación por correo electrónico usando PHPMailer
 */

require_once 'conexion.php';
require_once 'vendor/autoload.php'; // Cargar PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Variables para mensajes
$mensaje = '';
$tipo_mensaje = '';
$mostrar_formulario = true;

// ==================== PROCESAR FORMULARIO ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    // Validar email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Por favor, ingresa un correo electrónico válido.';
        $tipo_mensaje = 'danger';
    } else {
        try {
            // Buscar usuario por correo
            $stmt = $pdo->prepare("SELECT id, nombre_completo, usuario FROM usuarios WHERE correo = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Usuario encontrado - Generar token de recuperación
                $token = bin2hex(random_bytes(32)); // Token aleatorio de 64 caracteres
                $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour')); // Expira en 1 hora
                
                // Guardar token en la base de datos
                $stmt = $pdo->prepare("
                    INSERT INTO tokens_recuperacion (usuario_id, token, expiracion, usado) 
                    VALUES (?, ?, ?, 0)
                ");
                $stmt->execute([$usuario['id'], $token, $expiracion]);
                
                // ==================== CONFIGURAR PHPMAILER ====================
                $mail = new PHPMailer(true);
                
                try {
                    // Configuración del servidor SMTP de Gmail
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'josuehernandezdelangel9@gmail.com'; // ⚠️ CAMBIA ESTO
                    $mail->Password = 'otpzjoceckqzzrxp'; // ⚠️ CAMBIA ESTO
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';
                    
                    // Configuración del correo
                    $mail->setFrom('josuehernandezdelangel9@gmail.com', 'Sistema Frutería'); // ⚠️ CAMBIA ESTO
                    $mail->addAddress($email, $usuario['nombre_completo']); // Nombre opcional
                    $mail->isHTML(true);
                    
                    // Construir el enlace de recuperación
                    $enlace_recuperacion = "http://localhost/tu_proyecto/restablecer_password.php?token=" . $token;
                    
                    // Asunto y cuerpo del correo
                    $mail->Subject = 'Recuperación de Contraseña - Sistema Frutería';
                    $mail->Body = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                                .button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
                                .footer { text-align: center; color: #999; font-size: 12px; margin-top: 30px; }
                                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>🔐 Recuperación de Contraseña</h1>
                                </div>
                                <div class='content'>
                                    <p>Hola <strong>{$usuario['nombre_completo']}</strong>,</p>
                                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.</p>
                                    <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                                    <center>
                                        <a href='{$enlace_recuperacion}' class='button'>Restablecer Contraseña</a>
                                    </center>
                                    <p>O copia y pega este enlace en tu navegador:</p>
                                    <p style='background: #f0f0f0; padding: 10px; border-radius: 5px; word-break: break-all;'>
                                        {$enlace_recuperacion}
                                    </p>
                                    <div class='warning'>
                                        ⚠️ <strong>Importante:</strong>
                                        <ul>
                                            <li>Este enlace expirará en <strong>1 hora</strong></li>
                                            <li>Si no solicitaste este cambio, ignora este correo</li>
                                            <li>Tu contraseña actual sigue siendo válida hasta que la cambies</li>
                                        </ul>
                                    </div>
                                    <p>Si tienes problemas con el enlace, contacta al administrador del sistema.</p>
                                </div>
                                <div class='footer'>
                                    <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                                    <p>&copy; " . date('Y') . " Sistema Frutería - Todos los derechos reservados</p>
                                </div>
                            </div>
                        </body>
                        </html>
                    ";
                    
                    // Versión de texto plano (fallback)
                    $mail->AltBody = "
                        Hola {$usuario['nombre_completo']},
                        
                        Hemos recibido una solicitud para restablecer tu contraseña.
                        
                        Copia y pega este enlace en tu navegador:
                        {$enlace_recuperacion}
                        
                        Este enlace expirará en 1 hora.
                        
                        Si no solicitaste este cambio, ignora este correo.
                        
                        Sistema Frutería
                    ";
                    
                    // Enviar correo
                    $mail->send();
                    
                    // Éxito
                    $mensaje = '¡Correo enviado exitosamente! Revisa tu bandeja de entrada (y spam) para continuar.';
                    $tipo_mensaje = 'success';
                    $mostrar_formulario = false;
                    
                } catch (Exception $e) {
                    // Error al enviar correo
                    $mensaje = "Error al enviar el correo: {$mail->ErrorInfo}";
                    $tipo_mensaje = 'danger';
                }
                
            } else {
                // Usuario no encontrado - Por seguridad, mostramos el mismo mensaje
                $mensaje = 'Si el correo existe en nuestro sistema, recibirás un enlace de recuperación en breve.';
                $tipo_mensaje = 'info';
                $mostrar_formulario = false;
            }
            
        } catch (PDOException $e) {
            $mensaje = 'Error en el sistema. Por favor, intenta nuevamente.';
            $tipo_mensaje = 'danger';
            error_log("Error en recuperación de contraseña: " . $e->getMessage());
        }
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

        .recovery-container {
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

        .recovery-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .recovery-icon {
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

        .recovery-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
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

        .alert-info {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            color: #0c5460;
            border-left: 5px solid #17a2b8;
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

        .form-control {
            border-radius: 15px;
            border: 2px solid #e0e0e0;
            padding: 14px 18px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.3rem rgba(102, 126, 234, 0.15);
        }

        .btn-recovery {
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

        .btn-recovery:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-link a:hover {
            color: #764ba2;
            transform: translateX(-3px);
        }

        .info-box {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            border-radius: 15px;
            padding: 20px;
            margin-top: 25px;
            border-left: 5px solid #2196f3;
        }

        .info-box h6 {
            color: #1565c0;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #1976d2;
        }

        .info-box li {
            margin-bottom: 8px;
            font-size: 0.9rem;
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
                <i class="bi bi-envelope-fill"></i>
            </div>
            <h1>Recuperar Contraseña</h1>
            <p>Te enviaremos un enlace de recuperación por email</p>
        </div>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <i class="bi bi-<?= $tipo_mensaje === 'success' ? 'check-circle-fill' : ($tipo_mensaje === 'info' ? 'info-circle-fill' : 'exclamation-triangle-fill') ?>"></i>
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <?php if ($mostrar_formulario): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-envelope-fill"></i>
                    Correo Electrónico
                </label>
                <input type="email" class="form-control" name="email" required 
                       placeholder="tu@correo.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-recovery">
                <i class="bi bi-send-fill"></i>
                Enviar Enlace de Recuperación
            </button>
        </form>

        <!-- Información -->
        <div class="info-box">
            <h6>
                <i class="bi bi-lightbulb-fill"></i>
                ¿Cómo funciona?
            </h6>
            <ul>
                <li>Ingresa el correo con el que te registraste</li>
                <li>Recibirás un email con un enlace seguro</li>
                <li>El enlace expira en 1 hora</li>
                <li>Podrás crear una nueva contraseña</li>
            </ul>
        </div>
        <?php endif; ?>

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
