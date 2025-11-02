<?php
require_once 'conexion.php';

if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: productos.php?success=1");
    } catch (PDOException $e) {
        error_log("Error al eliminar: " . $e->getMessage());
        header("Location: productos.php?error=1");
    }
    exit();
}

header("Location: productos.php");