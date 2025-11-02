<?php
require_once 'conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Marcar la alerta como atendida y registrar la fecha de atención
    $stmt = $pdo->prepare("UPDATE alertas_stock SET atendida = 1, fecha_atencion = NOW() WHERE id = ?");
    
    if ($stmt->execute([$id])) {
        // Éxito - Redirigir con mensaje de éxito
        header("Location: alertas.php?success=1&manual=1");
    } else {
        // Error - Redirigir con mensaje de error
        header("Location: alertas.php?error=1");
    }
    exit();
} else {
    // Sin ID - Redirigir a alertas
    header("Location: alertas.php");
    exit();
}