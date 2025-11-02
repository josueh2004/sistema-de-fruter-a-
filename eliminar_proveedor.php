<?php
require_once 'conexion.php';

if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        $consulta = $pdo->prepare("UPDATE proveedores SET activo = 0 WHERE id = ?");
        $consulta->execute([$id]);
        header("Location: proveedores.php?exito=1");
    } catch (PDOException $e) {
        error_log("Error en eliminación: " . $e->getMessage());
        header("Location: proveedores.php?error=bd");
    }
    exit();
}

header("Location: proveedores.php");