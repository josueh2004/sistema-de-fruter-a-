<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $consulta = $pdo->prepare("SELECT * FROM proveedores WHERE id = ?");
        $consulta->execute([$id]);
        $proveedor = $consulta->fetch();
        
        if (!$proveedor) {
            header("Location: proveedores.php?error=no_encontrado");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error en consulta: " . $e->getMessage());
        header("Location: proveedores.php?error=bd");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'id' => $_POST['id'],
        'nombre' => $_POST['nombre'] ?? '',
        'contacto' => $_POST['contacto'] ?? '',
        'telefono' => $_POST['telefono'] ?? '',
        'direccion' => $_POST['direccion'] ?? ''
    ];

   
    if (empty($datos['nombre']) || empty($datos['contacto']) || 
       empty($datos['telefono']) || empty($datos['direccion'])) {
        header("Location: proveedores.php?error=campos_vacios");
        exit();
    }

    try {
        $actualizar = $pdo->prepare("UPDATE proveedores SET 
            nombre = :nombre,
            contacto = :contacto,
            telefono = :telefono,
            direccion = :direccion
            WHERE id = :id");
        
        if ($actualizar->execute($datos)) {
            header("Location: proveedores.php?exito=1");
        } else {
            header("Location: proveedores.php?error=actualizacion");
        }
    } catch (PDOException $e) {
        error_log("Error en actualización: " . $e->getMessage());
        header("Location: proveedores.php?error=bd");
    }
    exit();
}

include 'includes/sidebar.php';
?>

<div class="container mt-4">
    <h2>Editar Proveedor</h2>
    
    <form method="POST">
        <input type="hidden" name="id" value="<?= $proveedor['id'] ?>">
        
        <div class="row g-3 mb-4">
            <div class="col-12">
                <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" required 
                    value="<?= htmlspecialchars($proveedor['nombre']) ?>">
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Contacto</label>
                <input type="text" class="form-control" name="contacto" required
                    value="<?= htmlspecialchars($proveedor['contacto']) ?>">
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input type="tel" class="form-control" name="telefono" required
                    value="<?= htmlspecialchars($proveedor['telefono']) ?>">
            </div>
            
            <div class="col-12">
                <label class="form-label">Dirección</label>
                <textarea class="form-control" name="direccion" rows="3" required><?= 
                    htmlspecialchars($proveedor['direccion']) ?></textarea>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="proveedores.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include 'includes/footer.php';?>

