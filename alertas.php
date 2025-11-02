<?php
require_once 'conexion.php';
include 'includes/header.php';

$alertas = $pdo->query("
    SELECT a.*, p.nombre AS producto 
    FROM alertas_stock a
    JOIN productos p ON a.producto_id = p.id
    WHERE a.atendida = 0
    ORDER BY a.fecha DESC
");

include 'includes/sidebar.php';
?>

<style>
/* Alertas - Diseño Mejorado */
.alertas-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 20px;
    padding: 40px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(245, 87, 108, 0.3);
}

.alertas-header h2 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.alertas-header .subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

/* Alertas personalizadas */
.alert-success-dual {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    border: none;
    border-left: 5px solid #28a745;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
}

.alert-success-dual i {
    font-size: 1.5rem;
    color: #155724;
}

.alert-success-dual strong {
    color: #155724;
}

.alert-error-dual {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    border: none;
    border-left: 5px solid #dc3545;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
}

.alert-error-dual i {
    font-size: 1.5rem;
    color: #721c24;
}

.alertas-stats {
    background: white;
    border-radius: 15px;
    padding: 20px 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #333;
}

.stat-content p {
    margin: 0;
    color: #666;
    font-size: 0.95rem;
}

/* Grid de Alertas */
.alertas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.alerta-card {
    background: white;
    border-radius: 20px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border-left: 6px solid #ff6b6b;
}

.alerta-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

.alerta-card-header {
    background: linear-gradient(135deg, #fff5f5, #ffe5e5);
    padding: 25px;
    border-bottom: 2px solid #ffcccb;
}

.alerta-producto {
    font-size: 1.3rem;
    font-weight: 700;
    color: #c62828;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alerta-fecha {
    font-size: 0.85rem;
    color: #666;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alerta-card-body {
    padding: 25px;
}

.alerta-mensaje {
    font-size: 1rem;
    color: #555;
    line-height: 1.6;
    margin: 0 0 20px 0;
}

.alerta-card-footer {
    padding: 20px 25px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-atender {
    background: linear-gradient(135deg, #4caf50, #45a049);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-atender:hover {
    background: linear-gradient(135deg, #45a049, #388e3c);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
    color: white;
}

.btn-editar-producto {
    background: linear-gradient(135deg, #2196f3, #1976d2);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-editar-producto:hover {
    background: linear-gradient(135deg, #1976d2, #1565c0);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4);
    color: white;
}

.alerta-info-box {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    border-radius: 10px;
    padding: 12px;
    margin-top: 10px;
    font-size: 0.9rem;
    color: #1565c0;
}

/* Estado Vacío */
.empty-state-container {
    background: white;
    border-radius: 20px;
    padding: 80px 40px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
}

.empty-state-icon {
    font-size: 6rem;
    color: #4caf50;
    margin-bottom: 25px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.empty-state-title {
    font-size: 2rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 15px;
}

.empty-state-text {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 30px;
}

.btn-ver-productos {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 15px 35px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-ver-productos:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .alertas-header {
        padding: 30px 20px;
    }
    
    .alertas-header h2 {
        font-size: 1.8rem;
    }
    
    .alertas-grid {
        grid-template-columns: 1fr;
    }
    
    .alertas-stats {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
}
</style>

<!-- Header de Alertas -->
<div class="alertas-header">
    <h2>
        <i class="bi bi-bell-fill"></i>
        Alertas de Stock
    </h2>
    <p class="subtitle">Sistema dual: Actualización automática y manual</p>
</div>

<!-- Mensajes de éxito/error -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success-dual alert-dismissible fade show">
    <i class="bi bi-check-circle-fill"></i>
    <strong>¡Éxito!</strong>
    <?php if (isset($_GET['manual'])): ?>
        La alerta ha sido marcada como atendida <strong>manualmente</strong>.
    <?php else: ?>
        La alerta se ha actualizado correctamente.
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-error-dual alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>Error:</strong> No se pudo completar la operación. Intenta nuevamente.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Estadísticas -->
<?php 
$total_alertas = 0;
$alertas_temp = $pdo->query("SELECT COUNT(*) FROM alertas_stock WHERE atendida = 0")->fetchColumn();
$total_alertas = $alertas_temp;
?>

<div class="alertas-stats">
    <div class="stat-item">
        <div class="stat-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="stat-content">
            <h3><?= $total_alertas ?></h3>
            <p>Alertas Pendientes</p>
        </div>
    </div>
</div>

<!-- Grid de Alertas -->
<?php 
$alertas_array = $alertas->fetchAll();
if(count($alertas_array) > 0): 
?>
    <div class="alertas-grid">
        <?php foreach($alertas_array as $alerta): ?>
        <div class="alerta-card">
            <div class="alerta-card-header">
                <h3 class="alerta-producto">
                    <i class="bi bi-basket3-fill"></i>
                    <?= htmlspecialchars($alerta['producto']) ?>
                </h3>
                <div class="alerta-fecha">
                    <i class="bi bi-clock"></i>
                    <?= date('d/m/Y H:i', strtotime($alerta['fecha'])) ?>
                </div>
            </div>
            <div class="alerta-card-body">
                <p class="alerta-mensaje">
                    <i class="bi bi-info-circle-fill text-danger"></i>
                    <?= htmlspecialchars($alerta['mensaje']) ?>
                </p>
                <div class="alerta-info-box">
                    <i class="bi bi-lightbulb"></i>
                    <strong>Dos formas de resolver:</strong>
                    <br>1. Actualiza el stock del producto (se resuelve automáticamente)
                    <br>2. Marca manualmente como atendida
                </div>
            </div>
            <div class="alerta-card-footer">
                <a href="editar_producto.php?id=<?= $alerta['producto_id'] ?>" class="btn-editar-producto">
                    <i class="bi bi-pencil-fill"></i>
                    Editar Stock (Auto)
                </a>
                <a href="marcar_alerta.php?id=<?= $alerta['id'] ?>" class="btn-atender">
                    <i class="bi bi-check-circle-fill"></i>
                    Marcar Manual
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Estado Vacío -->
    <div class="empty-state-container">
        <div class="empty-state-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h2 class="empty-state-title">¡Excelente trabajo!</h2>
        <p class="empty-state-text">
            No hay alertas pendientes. Todos los productos están en niveles óptimos de stock.
        </p>
        <a href="productos.php" class="btn-ver-productos">
            <i class="bi bi-basket3-fill"></i>
            Ver Todos los Productos
        </a>
    </div>
<?php endif; ?>

</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
