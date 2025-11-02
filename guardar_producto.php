<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre' => $_POST['nombre'] ?? '',
        'categoria_id' => $_POST['categoria_id'] ?? 0,
        'proveedor_id' => $_POST['proveedor_id'] ?? 0,
        'unidad_medida' => $_POST['unidad_medida'] ?? '',
        'precio_compra' => $_POST['precio_compra'] ?? 0,
        'precio_venta' => $_POST['precio_venta'] ?? 0,
        'stock_actual' => $_POST['stock_actual'] ?? 0,
        'stock_minimo' => $_POST['stock_minimo'] ?? 0
    ];

    if (
        empty($datos['nombre']) || !is_numeric($datos['categoria_id']) || !is_numeric($datos['proveedor_id']) ||
        empty($datos['unidad_medida']) || !is_numeric($datos['precio_compra']) || !is_numeric($datos['precio_venta']) ||
        !is_numeric($datos['stock_actual']) || !is_numeric($datos['stock_minimo'])
    ) {
        header("Location: productos.php?error=invalid_input");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO productos (nombre, categoria_id, proveedor_id, unidad_medida, precio_compra, precio_venta, stock_actual, stock_minimo, activo) VALUES (:nombre, :categoria_id, :proveedor_id, :unidad_medida, :precio_compra, :precio_venta, :stock_actual, :stock_minimo, 1)");
        if ($stmt->execute($datos)) {
            $producto_id = $pdo->lastInsertId();

            // --- ALERTA DE STOCK BAJO AL CREAR ---
            if ($datos['stock_actual'] <= $datos['stock_minimo']) {
                $mensaje = "¡Alerta! Stock bajo de {$datos['nombre']}: {$datos['stock_actual']} {$datos['unidad_medida']} (Mínimo requerido: {$datos['stock_minimo']} {$datos['unidad_medida']})";
                $stmtInsert = $pdo->prepare("INSERT INTO alertas_stock (producto_id, mensaje, cantidad_actual, atendida) VALUES (?, ?, ?, 0)");
                $stmtInsert->execute([$producto_id, $mensaje, $datos['stock_actual']]);
            } else {
                // Si el stock está por encima del mínimo, asegurarse de que no haya alertas pendientes
                $stmtAtender = $pdo->prepare("UPDATE alertas_stock SET atendida = 1 WHERE producto_id = ? AND atendida = 0");
                $stmtAtender->execute([$producto_id]);
            }
            // --- FIN ALERTA DE STOCK BAJO ---

            header("Location: productos.php?success=1");
        } else {
            error_log("Failed to insert product: " . implode(", ", $stmt->errorInfo()));
            header("Location: productos.php?error=1");
        }
    } catch (PDOException $e) {
        error_log("PDO Exception in guardar_producto.php: " . $e->getMessage());
        header("Location: productos.php?error=1");
    }
    exit();
}
?>