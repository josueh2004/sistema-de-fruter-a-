<?php

session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
    header('Location: login.php');
    exit();
}

require_once 'conexion.php';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$user = $stmt->fetch();

$total_clientes = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Mi Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        /* Header Mejorado con Animación */
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .admin-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .admin-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }

        .admin-header .container {
            position: relative;
            z-index: 2;
        }

        .admin-header h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .admin-header h2 i {
            font-size: 3rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .admin-header p {
            font-size: 1.3rem;
            opacity: 0.95;
            font-weight: 300;
        }

        .btn-logout-header {
            background: white;
            color: #667eea;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-logout-header:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            color: #764ba2;
            background: #f8f9fa;
        }

        /* Navegación Mejorada */
        .admin-nav {
            background: white;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 35px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        .admin-nav .btn {
            padding: 15px 30px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .admin-nav .btn i {
            font-size: 1.3rem;
        }

        .admin-nav .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .admin-nav .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .admin-nav .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            background: white;
        }

        .admin-nav .btn-outline-primary:hover {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-color: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        /* Tarjeta de Estadísticas Mejorada */
        .stat-card {
            background: white;
            border-radius: 25px;
            padding: 0;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            height: 220px;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover::before {
            opacity: 0.05;
        }

        .stat-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 50px rgba(102, 126, 234, 0.3);
        }

        .stat-card .card-body {
            padding: 35px;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            height: 100%;
        }

        .stat-card i {
            font-size: 4rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(2px 2px 4px rgba(102, 126, 234, 0.3));
            animation: iconBounce 3s ease-in-out infinite;
        }

        @keyframes iconBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .stat-card h3 {
            font-size: 3.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            line-height: 1;
        }

        .stat-card p {
            font-size: 1.1rem;
            color: #666;
            font-weight: 600;
            margin: 0;
            letter-spacing: 0.5px;
        }

        /* Tarjeta de Perfil Mejorada */
        .profile-card {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .profile-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 35px;
            position: relative;
            overflow: hidden;
        }

        .profile-card .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .profile-card .card-header h5 {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            z-index: 2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .profile-card .card-header i {
            font-size: 2.2rem;
        }

        .profile-card .card-body {
            padding: 40px;
        }

        /* Avatar del Usuario */
        .user-avatar-large {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 4rem;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: avatarFloat 3s ease-in-out infinite;
        }

        @keyframes avatarFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Tabla de Información */
        .info-table {
            width: 100%;
            margin-top: 20px;
        }

        .info-table tr {
            border-bottom: 2px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table tr:hover {
            background: linear-gradient(135deg, #f8f9fa, #fff);
            transform: translateX(5px);
        }

        .info-table th {
            padding: 20px 15px;
            font-weight: 700;
            color: #667eea;
            font-size: 1rem;
            text-align: left;
            width: 250px;
        }

        .info-table th i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .info-table td {
            padding: 20px 15px;
            color: #333;
            font-weight: 500;
            font-size: 1.05rem;
        }

        .info-table .badge {
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 700;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }

        .info-table code {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 8px 16px;
            border-radius: 10px;
            color: #667eea;
            font-weight: 600;
            border: 2px solid #e0e0e0;
        }

        .info-table a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .info-table a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Tarjeta de Panel Administrativo */
        .admin-info-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            margin-top: 25px;
        }

        .admin-info-card .card-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 25px;
        }

        .admin-info-card .card-header h6 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-info-card .card-body {
            padding: 30px;
        }

        .admin-feature {
            background: linear-gradient(135deg, #f8f9fa, #fff);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid #e9ecef;
        }

        .admin-feature:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .admin-feature i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 15px;
        }

        .admin-feature strong {
            display: block;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 8px;
        }

        .admin-feature p {
            margin: 0;
            color: #666;
            font-size: 0.95rem;
        }

        /* Footer Mejorado */
        footer {
            background: white;
            border-top: 3px solid #667eea;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
        }

        footer small {
            font-size: 0.95rem;
            color: #666;
        }

        footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-header h2 {
                font-size: 2rem;
            }

            .admin-header h2 i {
                font-size: 2rem;
            }

            .stat-card h3 {
                font-size: 2.5rem;
            }

            .user-avatar-large {
                width: 120px;
                height: 120px;
                font-size: 3rem;
            }

            .info-table th {
                width: 150px;
                font-size: 0.9rem;
            }

            .info-table td {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
  
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <h2>
                        <i class="bi bi-shield-fill-check"></i>
                        Panel de Administrador
                    </h2>
                    <p>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?> 👑</p>
                </div>
                <div class="col-auto">
                    <a href="cerrar_sesion.php" class="btn-logout-header">
                        <i class="bi bi-box-arrow-right"></i>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Navegación -->
        <div class="admin-nav">
            <div class="row">
                <div class="col">
                    <a href="admin_perfil.php" class="btn btn-primary me-3">
                        <i class="bi bi-person-badge"></i>
                        Mi Perfil
                    </a>
                    <a href="admin_clientes_simple.php" class="btn btn-outline-primary">
                        <i class="bi bi-people-fill"></i>
                        Gestión de Clientes
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadística -->
        <div class="row mb-5">
            <div class="col-md-4 mx-auto">
                <div class="stat-card">
                    <div class="card-body">
                        <i class="bi bi-people"></i>
                        <h3><?= $total_clientes ?></h3>
                        <p>Total de Clientes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Perfil del Administrador -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="profile-card">
                    <div class="card-header">
                        <h5>
                            <i class="bi bi-person-badge"></i>
                            Mi Perfil de Administrador
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Avatar -->
                        <div class="user-avatar-large">
                            <i class="bi bi-person-circle"></i>
                        </div>

                        <!-- Información del Usuario -->
                        <table class="info-table">
                            <tr>
                                <th><i class="bi bi-hash"></i> ID de Usuario:</th>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($user['id']) ?></span></td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-person-fill"></i> Nombre Completo:</th>
                                <td><strong><?= htmlspecialchars($user['nombre_completo']) ?></strong></td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-at"></i> Usuario:</th>
                                <td><code><?= htmlspecialchars($user['usuario']) ?></code></td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-envelope-fill"></i> Correo Electrónico:</th>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($user['correo']) ?>">
                                        <?= htmlspecialchars($user['correo']) ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-phone-fill"></i> Celular:</th>
                                <td>
                                    <a href="tel:<?= htmlspecialchars($user['celular']) ?>">
                                        <?= htmlspecialchars($user['celular']) ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-calendar-check"></i> Fecha de Registro:</th>
                                <td><?= date('d/m/Y H:i:s', strtotime($user['fecha_registro'])) ?></td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-clock-history"></i> Último Login:</th>
                                <td>
                                    <?php if($user['ultimo_login']): ?>
                                        <?= date('d/m/Y H:i:s', strtotime($user['ultimo_login'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Nunca</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="bi bi-shield-check"></i> Rol:</th>
                                <td><span class="badge bg-danger">Administrador</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Información del Panel -->
                <div class="admin-info-card">
                    <div class="card-header">
                        <h6>
                            <i class="bi bi-info-circle"></i>
                            Capacidades del Panel Administrativo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center g-4">
                            <div class="col-md-6">
                                <div class="admin-feature">
                                    <i class="bi bi-gear-fill"></i>
                                    <strong>Gestión Completa</strong>
                                    <p>Administra todos los usuarios y clientes del sistema</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-feature">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    <strong>Acceso Exclusivo</strong>
                                    <p>Solo administradores pueden acceder a este panel</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <footer class="mt-5 py-4">
        <div class="container text-center">
            <small>
                <i class="bi bi-shield-fill-check"></i>
                Panel de Administración - <?= htmlspecialchars($_SESSION['usuario']) ?> | 
                <a href="cerrar_sesion.php">Cerrar Sesión</a>
            </small>
        </div>
    </footer>
</body>
</html>