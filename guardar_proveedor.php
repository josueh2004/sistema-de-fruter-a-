<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
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
        $consulta = $pdo->prepare("INSERT INTO proveedores 
            (nombre, contacto, telefono, direccion, activo) 
            VALUES (:nombre, :contacto, :telefono, :direccion, 1)");
            
        if ($consulta->execute($datos)) {
            header("Location: proveedores.php?exito=1");
        } else {
            header("Location: proveedores.php?error=insercion");
        }
    } catch (PDOException $e) {
        error_log("Error en guardar_proveedor: " . $e->getMessage());
        header("Location: proveedores.php?error=bd");
    }
    exit();
}

header("Location: proveedores.php");