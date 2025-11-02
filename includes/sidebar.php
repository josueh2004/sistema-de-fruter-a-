<?php
// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<style>
/* Sidebar Mejorado */
.sidebar {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    height: 100vh;
    padding: 25px 20px;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
}

/* Botones del Sidebar */
.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-sidebar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 20px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.btn-sidebar i {
    font-size: 1.3rem;
}

/* Botón Principal (Panel Principal) */
.btn-panel-principal {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.btn-panel-principal:hover {
    background: linear-gradient(135deg, #20c997, #28a745);
    transform: translateX(5px);
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
    color: white;
}

/* Botones Secundarios */
.btn-sidebar-secondary {
    background: white;
    color: #28a745;
    border: 2px solid #28a745;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.btn-sidebar-secondary:hover {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border-color: transparent;
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.btn-sidebar-secondary:hover i {
    transform: scale(1.2);
}

/* Separador */
.sidebar-divider {
    height: 2px;
    background: linear-gradient(90deg, transparent, #28a745, transparent);
    margin: 20px 0;
    border: none;
}

/* Sección de Usuario */
.user-section {
    background: white;
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    margin-bottom: 15px;
}

.user-avatar {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #28a745, #20c997);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 2rem;
    color: white;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

.user-name {
    font-weight: 700;
    color: #333;
    font-size: 1rem;
    margin-bottom: 8px;
}

.user-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-admin {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
}

.badge-user {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    box-shadow: 0 3px 10px rgba(0, 123, 255, 0.3);
}

/* Botón Cerrar Sesión */
.btn-logout {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 15px 20px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
    border: none;
}

.btn-logout:hover {
    background: linear-gradient(135deg, #c82333, #bd2130);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(220, 53, 69, 0.4);
    color: white;
}

.btn-logout i {
    font-size: 1.2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        height: auto;
        padding: 15px;
    }
    
    .btn-sidebar {
        padding: 12px 15px;
        font-size: 0.95rem;
    }
    
    .user-avatar {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <!-- Navegación -->
            <div class="sidebar-nav">
                <a href="index.php" class="btn-sidebar btn-panel-principal">
                    <i class="bi bi-speedometer2"></i>
                    Panel Principal
                </a>
                
                <a href="productos.php" class="btn-sidebar btn-sidebar-secondary">
                    <i class="bi bi-basket3-fill"></i>
                    Productos
                </a>
                
                <a href="alertas.php" class="btn-sidebar btn-sidebar-secondary">
                    <i class="bi bi-bell-fill"></i>
                    Alertas de Stock
                </a>
                
                <a href="proveedores.php" class="btn-sidebar btn-sidebar-secondary">
                    <i class="bi bi-truck"></i>
                    Proveedores
                </a>
                
                <!-- Solo mostrar si es administrador -->
                <?php if (isset($_SESSION['es_admin']) && $_SESSION['es_admin'] === true): ?>
                <a href="usuarios.php" class="btn-sidebar btn-sidebar-secondary">
                    <i class="bi bi-people-fill"></i>
                    Usuarios
                </a>
                
                <a href="admin_perfil.php" class="btn-sidebar btn-sidebar-secondary">
                    <i class="bi bi-person-badge-fill"></i>
                    Mi Perfil Admin
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Separador -->
            <hr class="sidebar-divider">
            
            <!-- Sección de Usuario -->
            <div class="user-section">
                <div class="user-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="user-name">
                    <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
                </div>
                <span class="user-badge <?= isset($_SESSION['es_admin']) && $_SESSION['es_admin'] ? 'badge-admin' : 'badge-user' ?>">
                    <?= isset($_SESSION['es_admin']) && $_SESSION['es_admin'] ? 'Administrador' : 'Usuario' ?>
                </span>
            </div>
            
            <!-- Botón Cerrar Sesión -->
            <a href="cerrar_sesion.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i>
                Cerrar Sesión
            </a>
        </div>
        <div class="col-md-9 col-lg-10 main-content">