<?php
// Middleware de autenticación
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['loggedin'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
    header('Location: login.php');
    exit();
}

// Verificar tiempo de inactividad (30 minutos)
$inactive_time = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive_time)) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['error_login'] = 'Su sesión ha expirado por inactividad';
    header('Location: login.php');
    exit();
}
$_SESSION['last_activity'] = time();

function operador_actual(): array {
    $op = $_SESSION['operador'] ?? [];
    return [
        'id' => $op['ope_id'] ?? null,
        'nombre' => $op['ope_nombre'] ?? '',
        'usuario' => $op['ope_user'] ?? '',
        'perf_id' => $op['perf_id'] ?? null,
        'perfil_nombre' => $op['perf_nombre'] ?? '',
        'perfil_descripcion' => $op['perf_descripcion'] ?? '',
        'imagen' => $_SESSION['operador_img'] ?? 'default-avatar.png',
    ];
}
?>

