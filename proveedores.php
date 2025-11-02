<?php
require_once 'conexion.php';
include 'includes/header.php';

$proveedores = $pdo->query("SELECT * FROM proveedores WHERE activo = 1");

include 'includes/sidebar.php';
?>

<style>
/* Proveedores - Diseño Mejorado */
.proveedores-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 30px;
    color: white;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
}

.proveedores-header h2 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.btn-nuevo-proveedor {
    background: white;
    color:  #b54ffeff;
    border: none;
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.btn-nuevo-proveedor:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    color:  #4facfe;
    background: #f8f9fa;
}

/* Grid de Proveedores */
.proveedores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.proveedor-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.proveedor-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
}

.proveedor-card-header {
    background: linear-gradient(135deg, #fe604fff, #00f2fe);
    padding: 25px;
    color: white;
    position: relative;
}

.proveedor-icon {
    width: 70px;
    height: 70px;
    background: white;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #4facfe;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.proveedor-nombre {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
}

.proveedor-card-body {
    padding: 25px;
}

.proveedor-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.info-icon {
    width: 40px;
    height: 40px;
    min-width: 40px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #4facfe;
}

.info-content {
    flex: 1;
}

.info-label {
    font-size: 0.8rem;
    color: #999;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.info-value {
    font-size: 1rem;
    color: #333;
    font-weight: 500;
    word-break: break-word;
}

.info-value a {
    color: #4facfe;
    text-decoration: none;
    transition: all 0.3s ease;
}

.info-value a:hover {
    color: #00f2fe;
    text-decoration: underline;
}

.proveedor-card-footer {
    padding: 20px 25px;
    background: #f8f9fa;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-accion-prov {
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-editar-prov {
    background: linear-gradient(135deg, #2196f3, #1976d2);
    color: white;
}

.btn-editar-prov:hover {
    background: linear-gradient(135deg, #1976d2, #1565c0);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4);
}

.btn-eliminar-prov {
    background: linear-gradient(135deg, #f44336, #d32f2f);
    color: white;
}

.btn-eliminar-prov:hover {
    background: linear-gradient(135deg, #d32f2f, #c62828);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(244, 67, 54, 0.4);
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
    background: linear-gradient(135deg, #4facfe, #00f2fe);
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

.form-control:focus {
    border-color: #4facfe;
    box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.modal-footer {
    padding: 20px 30px;
    border-top: 2px solid #f0f0f0;
}

.btn-guardar-prov {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
    padding: 12px 30px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-guardar-prov:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
    color: white;
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
    color: #4facfe;
    margin-bottom: 25px;
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
}

/* Responsive */
@media (max-width: 768px) {
    .proveedores-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .proveedores-grid {
        grid-template-columns: 1fr;
    }
    
    .proveedor-card-footer {
        flex-direction: column;
    }
    
    .btn-accion-prov {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Header de Proveedores -->
<div class="proveedores-header">
    <h2>
        <i class="bi bi-truck"></i>
        Gestión de Proveedores
    </h2>
    <button class="btn btn-nuevo-proveedor" data-bs-toggle="modal" data-bs-target="#modalProveedor">
        <i class="bi bi-plus-circle-fill"></i> Nuevo Proveedor
    </button>
</div>

<!-- Alertas -->
<?php if (isset($_GET['exito'])): ?>
<div class="alert alert-success-custom alert-dismissible fade show">
    <i class="bi bi-check-circle-fill"></i> Operación realizada correctamente
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger-custom alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <?php
    switch ($_GET['error']) {
        case 'campos_vacios': echo 'Todos los campos son obligatorios'; break;
        case 'bd': echo 'Error en la base de datos'; break;
        default: echo 'Error al realizar la operación';
    }
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Grid de Proveedores -->
<?php 
$proveedores_array = $proveedores->fetchAll();
if(count($proveedores_array) > 0): 
?>
    <div class="proveedores-grid">
        <?php foreach($proveedores_array as $proveedor): ?>
        <div class="proveedor-card">
            <div class="proveedor-card-header">
                <div class="proveedor-icon">
                    <i class="bi bi-building"></i>
                </div>
                <h3 class="proveedor-nombre"><?= htmlspecialchars($proveedor['nombre']) ?></h3>
            </div>
            <div class="proveedor-card-body">
                <div class="proveedor-info">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Contacto</div>
                            <div class="info-value"><?= htmlspecialchars($proveedor['contacto']) ?></div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Teléfono</div>
                            <div class="info-value">
                                <a href="tel:<?= htmlspecialchars($proveedor['telefono']) ?>">
                                    <?= htmlspecialchars($proveedor['telefono']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Dirección</div>
                            <div class="info-value"><?= htmlspecialchars($proveedor['direccion']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="proveedor-card-footer">
                <a href="editar_proveedor.php?id=<?= $proveedor['id'] ?>" class="btn-accion-prov btn-editar-prov">
                    <i class="bi bi-pencil-fill"></i> Editar
                </a>
                <button onclick="confirmarEliminacion(<?= $proveedor['id'] ?>)" class="btn-accion-prov btn-eliminar-prov">
                    <i class="bi bi-trash-fill"></i> Eliminar
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Estado Vacío -->
    <div class="empty-state-container">
        <div class="empty-state-icon">
            <i class="bi bi-truck"></i>
        </div>
        <h2 class="empty-state-title">No hay proveedores registrados</h2>
        <p class="empty-state-text">Agrega tu primer proveedor para comenzar a gestionar tu inventario</p>
    </div>
<?php endif; ?>

<script>
function confirmarEliminacion(id) {
    if (confirm("¿Estás seguro de eliminar este proveedor?")) {
        window.location.href = `eliminar_proveedor.php?id=${id}`;
    }
}
</script>

<!-- Modal Nuevo Proveedor -->
<div class="modal fade" id="modalProveedor">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle-fill"></i> Nuevo Proveedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="guardar_proveedor.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre de la Empresa</label>
                            <input type="text" class="form-control" name="nombre" required placeholder="Ej: Frutas del Valle S.A.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre del Contacto</label>
                            <input type="text" class="form-control" name="contacto" required placeholder="Ej: Juan Pérez">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" required placeholder="Ej: 555-1234">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <textarea class="form-control" name="direccion" rows="3" required placeholder="Calle, número, colonia, ciudad..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-guardar-prov">
                        <i class="bi bi-save-fill"></i> Guardar Proveedor
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
