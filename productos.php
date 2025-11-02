<?php
require_once 'conexion.php';
include 'includes/header.php';

$productos = $pdo->query("
    SELECT p.*, c.nombre AS categoria, pr.nombre AS proveedor 
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
");

$categorias = $pdo->query("SELECT * FROM categorias");
$proveedores = $pdo->query("SELECT * FROM proveedores WHERE activo = 1");

include 'includes/sidebar.php';
?>

<style>
/* Productos - Diseño Mejorado */
.productos-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 20px;
    padding: 30px;
    color: white;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.productos-header h2 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.btn-nuevo-producto {
    background: white;
    color: #667eea;
    border: none;
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.btn-nuevo-producto:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    color: #667eea;
    background: #f8f9fa;
}

/* Tabla de Productos Mejorada */
.productos-table-container {
    background: white;
    border-radius: 20px;
    padding: 0;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.table-productos {
    margin: 0;
}

.table-productos thead {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.table-productos thead th {
    border: none;
    padding: 20px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table-productos tbody tr {
    transition: all 0.3s ease;
    border-bottom: 1px solid #f0f0f0;
}

.table-productos tbody tr:hover {
    background: linear-gradient(135deg, #f8f9fa, #fff);
    transform: scale(1.01);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

.table-productos tbody td {
    padding: 20px;
    vertical-align: middle;
    border: none;
}

.producto-nombre {
    font-weight: 600;
    color: #333;
    font-size: 1.05rem;
}

.producto-categoria,
.producto-proveedor {
    color: #666;
    font-size: 0.95rem;
}

.stock-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.stock-normal {
    background: #e8f5e9;
    color: #2e7d32;
}

.stock-bajo {
    background: #ffebee;
    color: #c62828;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.precio-venta {
    font-size: 1.2rem;
    font-weight: 700;
    color: #4caf50;
}

.btn-accion {
    padding: 8px 12px;
    border-radius: 8px;
    border: none;
    transition: all 0.3s ease;
    margin: 0 3px;
}

.btn-editar {
    background: #2196f3;
    color: white;
}

.btn-editar:hover {
    background: #1976d2;
    transform: translateY(-2px);
}

.btn-eliminar {
    background: #f44336;
    color: white;
}

.btn-eliminar:hover {
    background: #d32f2f;
    transform: translateY(-2px);
}

/* Alertas */
.alert-custom {
    border-radius: 15px;
    border: none;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.alert-success-custom {
    background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
    color: #2e7d32;
}

.alert-danger-custom {
    background: linear-gradient(135deg, #ffebee, #ffcdd2);
    color: #c62828;
}

/* Modal Mejorado */
.modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 20px 20px 0 0;
    padding: 25px;
    border: none;
}

.modal-title {
    font-weight: 700;
    font-size: 1.5rem;
}

.modal-body {
    padding: 30px;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-control,
.form-select {
    border-radius: 10px;
    border: 2px solid #e0e0e0;
    padding: 12px;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.modal-footer {
    padding: 20px 30px;
    border-top: 2px solid #f0f0f0;
}

.btn-guardar {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 12px 30px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-guardar:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

/* Responsive */
@media (max-width: 768px) {
    .productos-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .table-productos {
        font-size: 0.85rem;
    }
    
    .table-productos tbody td {
        padding: 15px 10px;
    }
}
</style>

<!-- Header de Productos -->
<div class="productos-header">
    <h2>
        <i class="bi bi-basket3-fill"></i>
        Gestión de Productos
    </h2>
    <button class="btn btn-nuevo-producto" data-bs-toggle="modal" data-bs-target="#modalProducto">
        <i class="bi bi-plus-circle-fill"></i> Nuevo Producto
    </button>
</div>

<!-- Alertas -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success-custom alert-dismissible fade show">
    <i class="bi bi-check-circle-fill"></i> Operación realizada correctamente
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger-custom alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill"></i> Error al realizar la operación
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Tabla de Productos -->
<div class="productos-table-container">
    <table class="table table-productos table-hover">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Proveedor</th>
                <th>Stock</th>
                <th>Precio Venta</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($producto = $productos->fetch()): ?>
            <tr>
                <td class="producto-nombre">
                    <i class="bi bi-apple text-success"></i>
                    <?= htmlspecialchars($producto['nombre']) ?>
                </td>
                <td class="producto-categoria">
                    <i class="bi bi-tag"></i>
                    <?= htmlspecialchars($producto['categoria']) ?>
                </td>
                <td class="producto-proveedor">
                    <i class="bi bi-truck"></i>
                    <?= htmlspecialchars($producto['proveedor']) ?>
                </td>
                <td>
                    <span class="stock-badge <?= $producto['stock_actual'] <= $producto['stock_minimo'] ? 'stock-bajo' : 'stock-normal' ?>">
                        <?php if($producto['stock_actual'] <= $producto['stock_minimo']): ?>
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        <?php else: ?>
                            <i class="bi bi-check-circle-fill"></i>
                        <?php endif; ?>
                        <?= $producto['stock_actual'] ?> <?= $producto['unidad_medida'] ?>
                    </span>
                </td>
                <td class="precio-venta">
                    $<?= number_format($producto['precio_venta'], 2) ?>
                </td>
                <td>
                    <a href="editar_producto.php?id=<?= $producto['id'] ?>" class="btn btn-accion btn-editar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <button onclick="confirmarEliminacion(<?= $producto['id'] ?>)" class="btn btn-accion btn-eliminar">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function confirmarEliminacion(id) {
    if (confirm("¿Estás seguro de eliminar este producto?")) {
        window.location.href = `eliminar_producto.php?id=${id}`;
    }
}
</script>

<!-- Modal Nuevo Producto -->
<div class="modal fade" id="modalProducto">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="guardar_producto.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle-fill"></i> Nuevo Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control" name="nombre" required placeholder="Ej: Manzana Roja">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" name="categoria_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Proveedor</label>
                            <select class="form-select" name="proveedor_id" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach($proveedores as $prov): ?>
                                <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unidad de Medida</label>
                            <select class="form-select" name="unidad_medida" required>
                                <option value="">Seleccionar...</option>
                                <option value="kg">Kilogramos (kg)</option>
                                <option value="pieza">Pieza</option>
                                <option value="caja">Caja</option>
                                <option value="racimo">Racimo</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Precio de Compra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" name="precio_compra" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Precio de Venta</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" name="precio_venta" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Actual</label>
                            <input type="number" step="0.01" class="form-control" name="stock_actual" required placeholder="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stock Mínimo (Alerta)</label>
                            <input type="number" step="0.01" class="form-control" name="stock_minimo" required placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-guardar">
                        <i class="bi bi-save-fill"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>
