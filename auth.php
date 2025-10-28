<?php
/**
 * Middleware de autenticación
 * Incluir este archivo al inicio de cada página protegida
 * Guardar como: auth.php (en la raíz del proyecto)
 * 
 * Uso: require_once 'auth.php';
 */

// Incluir la configuración de base de datos
require_once 'config.php';

session_start();

// Verificar si el usuario está autenticado
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Guardar la URL a la que intentaba acceder
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirigir al login
    header("Location: login.php");
    exit();
}

// Verificar tiempo de inactividad (opcional - 30 minutos)
$inactive_time = 1800; // 30 minutos en segundos

if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_time)) {
    // Sesión expirada por inactividad
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['error_login'] = 'Su sesión ha expirado por inactividad';
    header("Location: login.php");
    exit();
}

// Actualizar tiempo de última actividad
$_SESSION['last_activity'] = time();

/**
 * Función para verificar roles
 * @param array|string $roles_permitidos - Rol o array de roles permitidos
 * @return bool
 */
function verificar_rol($roles_permitidos) {
    if(!isset($_SESSION['operador_rol'])) {
        return false;
    }
    
    if(is_array($roles_permitidos)) {
        return in_array($_SESSION['operador_rol'], $roles_permitidos);
    }
    
    return $_SESSION['operador_rol'] === $roles_permitidos;
}

/**
 * Función para verificar si es administrador
 * @return bool
 */
function es_admin() {
    return isset($_SESSION['operador_rol']) && $_SESSION['operador_rol'] === 'admin';
}

/**
 * Función para obtener datos del operador actual
 * @return array
 */
function operador_actual() {
    return [
        'id' => $_SESSION['operador_id'] ?? null,
        'nombre' => $_SESSION['operador_nombre'] ?? '',
        'usuario' => $_SESSION['operador_user'] ?? '',
        'rol' => $_SESSION['operador_rol'] ?? '',
        'imagen' => $_SESSION['operador_img'] ?? 'default-avatar.png'
    ];
}
?>