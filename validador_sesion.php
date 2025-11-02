<?php
/**
 * Clase: validador_sesion.php
 * Propósito: Validar sesiones y permisos de usuario
 * Nombre en español: Validador de Sesión
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ValidadorSesion {
    
    /**
     * Verificar si el usuario está logueado
     * @return bool
     */
    public static function usuarioLogueado() {
        return isset($_SESSION['usuario']) && 
               isset($_SESSION['usuario_id']) && 
               isset($_SESSION['es_admin']);
    }
    
    /**
     * Verificar si el usuario es administrador
     * @return bool
     */
    public static function esAdministrador() {
        return self::usuarioLogueado() && $_SESSION['es_admin'] === true;
    }
    
    /**
     * Verificar si el usuario es usuario normal (no admin)
     * @return bool
     */
    public static function esUsuarioNormal() {
        return self::usuarioLogueado() && $_SESSION['es_admin'] === false;
    }
    
    /**
     * Redirigir si no está logueado
     */
    public static function requiereLogin() {
        if (!self::usuarioLogueado()) {
            header('Location: login.php');
            exit();
        }
    }
    
    /**
     * Redirigir si no es administrador
     */
    public static function requiereAdmin() {
        self::requiereLogin();
        if (!self::esAdministrador()) {
            header('Location: index.php?error=sin_permisos');
            exit();
        }
    }
    
    /**
     * Redirigir si no es usuario normal
     */
    public static function requiereUsuario() {
        self::requiereLogin();
        if (!self::esUsuarioNormal()) {
            header('Location: admin_perfil.php?error=acceso_denegado');
            exit();
        }
    }
    
    /**
     * Obtener información del usuario actual
     * @return array
     */
    public static function obtenerUsuario() {
        if (!self::usuarioLogueado()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['usuario_id'],
            'usuario' => $_SESSION['usuario'],
            'nombre' => $_SESSION['nombre'],
            'es_admin' => $_SESSION['es_admin']
        ];
    }
    
    /**
     * Limpiar sesión completamente
     */
    public static function cerrarSesion() {
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
}

// Función global para facilitar el uso
function requiere_login() {
    ValidadorSesion::requiereLogin();
}

function requiere_admin() {
    ValidadorSesion::requiereAdmin();
}

function requiere_usuario() {
    ValidadorSesion::requiereUsuario();
}

function es_admin() {
    return ValidadorSesion::esAdministrador();
}

function usuario_actual() {
    return ValidadorSesion::obtenerUsuario();
}
?>