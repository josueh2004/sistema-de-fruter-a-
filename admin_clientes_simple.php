<?php

session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
    header('Location: login.php');
    exit();
}

require_once 'conexion.php';

$stmt = $pdo->query("
    SELECT u.*, 
           CASE WHEN a.usuario_id IS NOT NULL THEN 'Administrador' ELSE 'Cliente' END as tipo_usuario
    FROM usuarios u 
    LEFT JOIN administradores a ON u.id = a.usuario_id 
    ORDER BY u.fecha_registro DESC
");
$usuarios = $stmt->fetchAll();

$clientes_normales = 0;
$administradores = 0;
foreach($usuarios as $usuario) {
    if($usuario['tipo_usuario'] === 'Administrador') {
        $administradores++;
    } else {
        $clientes_normales++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Clientes</title>
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

        /* Header Mejorado */
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

        /* Navegación */
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

        /* Tarjetas de Estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stats-card {
            background: white;
            border-radius: 25px;
            padding: 0;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            height: 200px;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            opacity: 0.1;
            transition: all 0.3s ease;
        }

        .stats-card.total::before {
            background: #667eea;
        }

        .stats-card.clientes::before {
            background: #4caf50;
        }

        .stats-card.admins::before {
            background: #f44336;
        }

        .stats-card:hover::before {
            transform: scale(1.5);
            opacity: 0.15;
        }

        .stats-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .stats-card .card-body {
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

        .stats-card i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .stats-card.total i { color: #667eea; }
        .stats-card.clientes i { color: #4caf50; }
        .stats-card.admins i { color: #f44336; }

        .stats-card h3 {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 8px;
            line-height: 1;
        }

        .stats-card.total h3 { color: #667eea; }
        .stats-card.clientes h3 { color: #4caf50; }
        .stats-card.admins h3 { color: #f44336; }

        .stats-card p {
            font-size: 1.05rem;
            color: #666;
            font-weight: 600;
            margin: 0;
        }

        /* Tabla de Clientes */
        .clientes-table-container {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            color: white;
        }

        .table-header h5 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table-clientes {
            margin: 0;
            width: 100%;
        }

        .table-clientes thead {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .table-clientes thead th {
            padding: 20px 15px;
            font-weight: 700;
            color: #667eea;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            border: none;
        }

        .table-clientes tbody tr {
            transition: all 0.3s ease;
            border-bottom: 2px solid #f0f0f0;
        }

        .table-clientes tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa, #fff);
            transform: scale(1.01);
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.05);
        }

        .table-clientes tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            border: none;
            font-size: 0.95rem;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .badge.bg-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268) !important;
            box-shadow: 0 3px 10px rgba(108, 117, 125, 0.3);
        }

        .badge.bg-danger {
            background: linear-gradient(135deg, #f44336, #d32f2f) !important;
            box-shadow: 0 3px 10px rgba(244, 67, 54, 0.3);
        }

        .badge.bg-primary {
            background: linear-gradient(135deg, #667eea, #764ba2) !important;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }

        .password-field {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px solid #dee2e6;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
        }

        .table-clientes a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .table-clientes a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .table-clientes code {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 6px 12px;
            border-radius: 8px;
            color: #1976d2;
            font-weight: 600;
            border: 2px solid #90caf9;
        }

        /* Footer */
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

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stats-card h3 {
                font-size: 2.5rem;
            }

            .table-clientes {
                font-size: 0.85rem;
            }

            .table-clientes thead th,
            .table-clientes tbody td {
                padding: 12px 8px;
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
      
        <div class="admin-nav">
            <div class="row">
                <div class="col">
                    <a href="admin_perfil.php" class="btn btn-outline-primary me-3">
                        <i class="bi bi-person-badge"></i>
                        Mi Perfil
                    </a>
                    <a href="admin_clientes_simple.php" class="btn btn-primary">
                        <i class="bi bi-people-fill"></i>
                        Gestión de Clientes
                    </a>
                </div>
            </div>
        </div>

      
        <div class="stats-grid">
            <div class="stats-card total">
                <div class="card-body">
                    <i class="bi bi-people"></i>
                    <h3><?= count($usuarios) ?></h3>
                    <p>Total de Usuarios</p>
                </div>
            </div>

            <div class="stats-card clientes">
                <div class="card-body">
                    <i class="bi bi-person"></i>
                    <h3><?= $clientes_normales ?></h3>
                    <p>Clientes</p>
                </div>
            </div>

            <div class="stats-card admins">
                <div class="card-body">
                    <i class="bi bi-shield-check"></i>
                    <h3><?= $administradores ?></h3>
                    <p>Administradores</p>
                </div>
            </div>
        </div>

       
        <div class="clientes-table-container">
            <div class="table-header">
                <h5>
                    <i class="bi bi-table"></i>
                    Lista de Clientes Registrados
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-clientes table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Usuario</th>
                            <th>Contraseña</th>
                            <th>Correo</th>
                            <th>Celular</th>
                            <th>Tipo</th>
                            <th>Fecha Registro</th>
                            <th>Último Login</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($usuarios as $usuario): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary"><?= $usuario['id'] ?></span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($usuario['nombre_completo']) ?></strong>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($usuario['usuario']) ?></code>
                            </td>
                            <td>
                                <span class="password-field">
                                    <?= htmlspecialchars($usuario['contraseña']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($usuario['correo']) ?>">
                                    <i class="bi bi-envelope"></i>
                                    <?= htmlspecialchars($usuario['correo']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="tel:<?= htmlspecialchars($usuario['celular']) ?>">
                                    <i class="bi bi-phone"></i>
                                    <?= htmlspecialchars($usuario['celular']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if($usuario['tipo_usuario'] === 'Administrador'): ?>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-shield-check"></i> Admin
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary">
                                        <i class="bi bi-person"></i> Cliente
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?>
                                </small>
                            </td>
                            <td>
                                <?php if($usuario['ultimo_login']): ?>
                                    <small class="text-muted">
                                        <i class="bi bi-clock-history"></i>
                                        <?= date('d/m/Y', strtotime($usuario['ultimo_login'])) ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-warning">
                                        <i class="bi bi-exclamation-circle"></i> Nunca
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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