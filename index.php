<?php 
require_once 'conexion.php';
include 'includes/header.php';

// Estadísticas
$estadisticas = [
    'productos' => $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn(),
    'alertas' => $pdo->query("SELECT COUNT(*) FROM alertas_stock WHERE atendida = 0")->fetchColumn(),
    'proveedores' => $pdo->query("SELECT COUNT(*) FROM proveedores WHERE activo = 1")->fetchColumn(),
    'movimientos' => $pdo->query("SELECT COUNT(*) FROM movimientos")->fetchColumn()
];

// Productos con stock bajo
$productos_bajo_stock = $pdo->query("
    SELECT id, nombre, stock_actual, stock_minimo, unidad_medida 
    FROM productos 
    WHERE stock_actual <= stock_minimo 
    ORDER BY stock_actual ASC 
    LIMIT 5
")->fetchAll();

include 'includes/sidebar.php';
?>

<style>
.dashboard-welcome {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 40px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.dashboard-welcome h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.dashboard-welcome p {
    font-size: 1.2rem;
    opacity: 0.9;
}

/* Tarjetas de Estadísticas */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    position: relative;
    background: white;
    border-radius: 20px;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-decoration: none;
    color: inherit;
    height: 180px;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

.stat-card-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.4;
}

.stat-card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.75));
}

.stat-card-content {
    position: relative;
    z-index: 2;
    padding: 30px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 10px;
    filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.5));
}

.stat-number {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1;
    margin: 10px 0;
    text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.8);
}

.stat-label {
    font-size: 1.1rem;
    opacity: 1;
    font-weight: 600;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
}

/* Colores específicos */
.stat-card.productos { background: linear-gradient(135deg, #667eea, #764ba2); }
.stat-card.alertas { background: linear-gradient(135deg, #f093fb, #f5576c); }
.stat-card.proveedores { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.stat-card.movimientos { background: linear-gradient(135deg, #43e97b, #38f9d7); }

/* Sección de Stock Bajo */
.stock-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 3px solid #f0f0f0;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #333;
    display: flex;
    align-items: center;
    gap: 12px;
}

.badge-count {
    background: #ff6b6b;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.95rem;
    font-weight: 600;
}

.stock-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.stock-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #fff);
    border-radius: 15px;
    border-left: 5px solid #ff6b6b;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.stock-item:hover {
    background: linear-gradient(135deg, #fff, #f8f9fa);
    transform: translateX(10px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.stock-item-icon {
    font-size: 3rem;
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 15px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.stock-item-info {
    flex: 1;
}

.stock-item-name {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.stock-progress-container {
    display: flex;
    align-items: center;
    gap: 15px;
}

.progress-bar-custom {
    flex: 1;
    height: 10px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill-custom {
    height: 100%;
    background: linear-gradient(90deg, #ff6b6b, #ffa502);
    border-radius: 10px;
    transition: width 0.5s ease;
}

.stock-text {
    font-size: 0.95rem;
    color: #666;
    font-weight: 500;
    white-space: nowrap;
}

.empty-state {
    text-align: center;
    padding: 60px 30px;
    color: #999;
}

.empty-state i {
    font-size: 4rem;
    color: #4caf50;
    margin-bottom: 20px;
}

.empty-state p {
    font-size: 1.1rem;
    color: #666;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-welcome {
        padding: 30px 20px;
    }
    
    .dashboard-welcome h2 {
        font-size: 1.8rem;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
    }
    
    .stock-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<!-- Bienvenida -->
<div class="dashboard-welcome">
    <h2><i class="bi bi-speedometer2"></i> Panel Principal</h2>
    <p>Bienvenido de vuelta, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?> 👋</p>
</div>

<!-- Tarjetas de Estadísticas -->
<div class="stats-container">
    <a href="productos.php" class="stat-card productos">
        <img src="https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=500&q=80" alt="Productos" class="stat-card-image">
        <div class="stat-card-overlay"></div>
        <div class="stat-card-content">
            <div class="stat-icon"><i class="bi bi-basket3-fill"></i></div>
            <div>
                <div class="stat-number"><?= $estadisticas['productos'] ?></div>
                <div class="stat-label">Productos Totales</div>
            </div>
        </div>
    </a>

    <a href="alertas.php" class="stat-card alertas">
        <img src="https://images.unsplash.com/photo-1586528116493-a029325540fa?w=500&q=80" alt="Alertas" class="stat-card-image">
        <div class="stat-card-overlay"></div>
        <div class="stat-card-content">
            <div class="stat-icon"><i class="bi bi-bell-fill"></i></div>
            <div>
                <div class="stat-number"><?= $estadisticas['alertas'] ?></div>
                <div class="stat-label">Alertas Activas</div>
            </div>
        </div>
    </a>

    <a href="proveedores.php" class="stat-card proveedores">
        <img src="https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?w=500&q=80" alt="Proveedores" class="stat-card-image">
        <div class="stat-card-overlay"></div>
        <div class="stat-card-content">
            <div class="stat-icon"><i class="bi bi-truck"></i></div>
            <div>
                <div class="stat-number"><?= $estadisticas['proveedores'] ?></div>
                <div class="stat-label">Proveedores Activos</div>
            </div>
        </div>
    </a>

    <div class="stat-card movimientos">
        <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=500&q=80" alt="Movimientos" class="stat-card-image">
        <div class="stat-card-overlay"></div>
        <div class="stat-card-content">
            <div class="stat-icon"><i class="bi bi-arrow-left-right"></i></div>
            <div>
                <div class="stat-number"><?= $estadisticas['movimientos'] ?></div>
                <div class="stat-label">Movimientos Totales</div>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Stock Bajo -->
<div class="stock-section">
    <div class="section-header">
        <h3 class="section-title">
            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
            Productos con Stock Bajo
        </h3>
        <span class="badge-count"><?= count($productos_bajo_stock) ?></span>
    </div>

    <div class="stock-list">
        <?php if(count($productos_bajo_stock) > 0): ?>
            <?php foreach($productos_bajo_stock as $producto): ?>
            <a href="editar_producto.php?id=<?= $producto['id'] ?>" class="stock-item">
                <div class="stock-item-icon">🍎</div>
                <div class="stock-item-info">
                    <div class="stock-item-name"><?= htmlspecialchars($producto['nombre']) ?></div>
                    <div class="stock-progress-container">
                        <?php 
                        $porcentaje = ($producto['stock_actual'] / $producto['stock_minimo']) * 100;
                        $porcentaje = min($porcentaje, 100);
                        ?>
                        <div class="progress-bar-custom">
                            <div class="progress-fill-custom" style="width: <?= $porcentaje ?>%"></div>
                        </div>
                        <span class="stock-text">
                            <?= $producto['stock_actual'] ?> / <?= $producto['stock_minimo'] ?> <?= $producto['unidad_medida'] ?>
                        </span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-check-circle-fill"></i>
                <p>¡Excelente! Todo el inventario está en niveles óptimos</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
