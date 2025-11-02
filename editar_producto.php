<?php
require_once 'conexion.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        header("Location: productos.php?error=1");
        exit();
    }

    $categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();
    $proveedores = $pdo->query("SELECT * FROM proveedores WHERE activo = 1")->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'id' => $_POST['id'],
        'nombre' => $_POST['nombre'],
        'categoria_id' => $_POST['categoria_id'],
        'proveedor_id' => $_POST['proveedor_id'],
        'precio_compra' => $_POST['precio_compra'],
        'precio_venta' => $_POST['precio_venta'],
        'stock_actual' => $_POST['stock_actual'],
        'stock_minimo' => $_POST['stock_minimo'],
        'unidad_medida' => $_POST['unidad_medida'],
        'activo' => $_POST['activo']
    ];

    $stmt = $pdo->prepare("UPDATE productos SET 
        nombre = :nombre,
        categoria_id = :categoria_id,
        proveedor_id = :proveedor_id,
        precio_compra = :precio_compra,
        precio_venta = :precio_venta,
        stock_actual = :stock_actual,
        stock_minimo = :stock_minimo,
        unidad_medida = :unidad_medida,
        activo = :activo
        WHERE id = :id
    ");

    if ($stmt->execute($datos)) {
        // --- SISTEMA DUAL DE ALERTAS ---
        
        // OPCIÓN 1: Si el stock está BAJO o IGUAL al mínimo -> CREAR O MANTENER alerta
        if ($datos['stock_actual'] <= $datos['stock_minimo']) {
            // Verificar si ya existe una alerta pendiente
            $stmtAlerta = $pdo->prepare("SELECT id FROM alertas_stock WHERE producto_id = ? AND atendida = 0");
            $stmtAlerta->execute([$datos['id']]);
            
            if (!$stmtAlerta->fetch()) {
                // No existe alerta pendiente, crear una nueva
                $mensaje = "¡Alerta! Stock bajo de {$datos['nombre']}: {$datos['stock_actual']} {$datos['unidad_medida']} (Mínimo requerido: {$datos['stock_minimo']} {$datos['unidad_medida']})";
                $stmtInsert = $pdo->prepare("INSERT INTO alertas_stock (producto_id, mensaje, cantidad_actual, atendida) VALUES (?, ?, ?, 0)");
                $stmtInsert->execute([$datos['id'], $mensaje, $datos['stock_actual']]);
            } else {
                // Ya existe alerta, actualizar la cantidad actual
                $stmtUpdate = $pdo->prepare("UPDATE alertas_stock SET cantidad_actual = ?, fecha = NOW() WHERE producto_id = ? AND atendida = 0");
                $stmtUpdate->execute([$datos['stock_actual'], $datos['id']]);
            }
        } 
        // OPCIÓN 2: Si el stock SUPERA el mínimo -> MARCAR AUTOMÁTICAMENTE COMO ATENDIDA
        else {
            // Stock suficiente, marcar automáticamente todas las alertas pendientes como atendidas
            $stmtAtender = $pdo->prepare("UPDATE alertas_stock SET atendida = 1, fecha_atencion = NOW() WHERE producto_id = ? AND atendida = 0");
            $resultado = $stmtAtender->execute([$datos['id']]);
            
            // Agregar parámetro para mostrar que se actualizó automáticamente
            if ($stmtAtender->rowCount() > 0) {
                header("Location: productos.php?success=1&alerta_resuelta=1");
                exit();
            }
        }
        // --- FIN SISTEMA DUAL ---

        header("Location: productos.php?success=1");
    } else {
        header("Location: productos.php?error=1");
    }
    exit();
}

include 'includes/sidebar.php';
?>

<style>
/* Estilos del formulario mejorado */
.form-container {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
}

.form-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 15px;
    padding: 25px;
    color: white;
    margin-bottom: 30px;
}

.form-header h2 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-info-custom {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border: none;
    border-left: 5px solid #2196f3;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.alert-info-custom i {
    font-size: 1.5rem;
    color: #1976d2;
}

.alert-info-custom strong {
    color: #1565c0;
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

.input-group-text {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    font-weight: 600;
    border-radius: 10px 0 0 10px;
}

.stock-info {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 15px;
    margin-top: 10px;
    border-left: 4px solid #ffc107;
}

.stock-info small {
    color: #666;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-guardar-cambios {
    background: linear-gradient(135deg, #4caf50, #45a049);
    color: white;
    padding: 15px 40px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.btn-guardar-cambios:hover {
    background: linear-gradient(135deg, #45a049, #388e3c);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
    color: white;
}

.btn-cancelar {
    background: #6c757d;
    color: white;
    padding: 15px 40px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.btn-cancelar:hover {
    background: #5a6268;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(108, 117, 125, 0.4);
    color: white;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 2px solid #f0f0f0;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .btn-guardar-cambios,
    .btn-cancelar {
        width: 100%;
    }
}
</style>

<div class="form-container">
    <div class="form-header">
        <h2>
            <i class="bi bi-pencil-square"></i>
            Editar Producto
        </h2>
    </div>

    <!-- Alerta informativa sobre el sistema dual -->
    <div class="alert alert-info-custom">
        <i class="bi bi-lightbulb-fill"></i>
        <strong>Sistema de Alertas Inteligente:</strong>
        <ul class="mb-0 mt-2">
            <li>Si el stock es <strong>menor o igual</strong> al mínimo, se creará o actualizará una alerta automáticamente.</li>
            <li>Si el stock <strong>supera</strong> el mínimo, la alerta se resolverá automáticamente.</li>
            <li>También puedes marcar alertas manualmente desde la sección de Alertas.</li>
        </ul>
    </div>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $producto['id'] ?>">
        
        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-basket3"></i> Nombre del Producto
                </label>
                <input type="text" class="form-control" name="nombre" required 
                       value="<?= htmlspecialchars($producto['nombre']) ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-tag"></i> Categoría
                </label>
                <select class="form-select" name="categoria_id" required>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $producto['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-truck"></i> Proveedor
                </label>
                <select class="form-select" name="proveedor_id" required>
                    <?php foreach($proveedores as $prov): ?>
                        <option value="<?= $prov['id'] ?>" <?= $producto['proveedor_id'] == $prov['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prov['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-rulers"></i> Unidad de Medida
                </label>
                <select class="form-select" name="unidad_medida" required>
                    <option value="kg" <?= $producto['unidad_medida'] == 'kg' ? 'selected' : '' ?>>Kilogramos (kg)</option>
                    <option value="pieza" <?= $producto['unidad_medida'] == 'pieza' ? 'selected' : '' ?>>Pieza</option>
                    <option value="caja" <?= $producto['unidad_medida'] == 'caja' ? 'selected' : '' ?>>Caja</option>
                    <option value="racimo" <?= $producto['unidad_medida'] == 'racimo' ? 'selected' : '' ?>>Racimo</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-currency-dollar"></i> Precio de Compra
                </label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" name="precio_compra" 
                           required value="<?= $producto['precio_compra'] ?>">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-cash-stack"></i> Precio de Venta
                </label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" name="precio_venta" 
                           required value="<?= $producto['precio_venta'] ?>">
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-box-seam"></i> Stock Actual
                </label>
                <input type="number" step="0.01" class="form-control" name="stock_actual" 
                       required value="<?= $producto['stock_actual'] ?>" id="stock_actual">
                <div class="stock-info">
                    <small>
                        <i class="bi bi-info-circle"></i>
                        Actual: <strong><?= $producto['stock_actual'] ?> <?= $producto['unidad_medida'] ?></strong>
                    </small>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-exclamation-triangle"></i> Stock Mínimo (Alerta)
                </label>
                <input type="number" step="0.01" class="form-control" name="stock_minimo" 
                       required value="<?= $producto['stock_minimo'] ?>" id="stock_minimo">
                <div class="stock-info">
                    <small>
                        <i class="bi bi-bell"></i>
                        Mínimo: <strong><?= $producto['stock_minimo'] ?> <?= $producto['unidad_medida'] ?></strong>
                    </small>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    <i class="bi bi-toggle-on"></i> Estado del Producto
                </label>
                <select class="form-select" name="activo">
                    <option value="1" <?= $producto['activo'] ? 'selected' : '' ?>>✅ Activo</option>
                    <option value="0" <?= !$producto['activo'] ? 'selected' : '' ?>>❌ Inactivo</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="productos.php" class="btn btn-cancelar">
                <i class="bi bi-x-circle"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-guardar-cambios">
                <i class="bi bi-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>

<script>
// Validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const stockActual = document.getElementById('stock_actual');
    const stockMinimo = document.getElementById('stock_minimo');
    
    function validarStock() {
        const actual = parseFloat(stockActual.value) || 0;
        const minimo = parseFloat(stockMinimo.value) || 0;
        
        if (actual <= minimo) {
            stockActual.style.borderColor = '#f44336';
            stockActual.style.backgroundColor = '#ffebee';
        } else {
            stockActual.style.borderColor = '#4caf50';
            stockActual.style.backgroundColor = '#e8f5e9';
        }
    }
    
    stockActual.addEventListener('input', validarStock);
    stockMinimo.addEventListener('input', validarStock);
    validarStock(); // Ejecutar al cargar
});
</script>

</div>
</div>
</div>

<?php include 'includes/footer.php'; ?>