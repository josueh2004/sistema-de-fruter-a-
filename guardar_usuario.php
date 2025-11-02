<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre_completo' => $_POST['nombre_completo'] ?? '',
        'usuario' => $_POST['usuario'] ?? '',
        'contraseña' => $_POST['contraseña'] ?? '',
        'correo' => $_POST['correo'] ?? '',
        'celular' => $_POST['celular'] ?? ''
    ];

   
    if (empty($datos['nombre_completo']) || empty($datos['usuario']) || empty($datos['contraseña']) || 
        empty($datos['correo']) || empty($datos['celular'])) {
        header("Location: usuarios.php?error=invalid_input");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, usuario, contraseña, correo, celular) VALUES (:nombre_completo, :usuario, :contraseña, :correo, :celular)");
        if ($stmt->execute($datos)) {
            header("Location: usuarios.php?success=1");
        } else {
            error_log("Failed to insert user: " . implode(", ", $stmt->errorInfo()));
            header("Location: usuarios.php?error=1");
        }
    } catch (PDOException $e) {
        error_log("PDO Exception in guardar_usuario.php: " . $e->getMessage());
        header("Location: usuarios.php?error=1");
    }
    exit();
}
?>