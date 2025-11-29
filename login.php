<?php
session_start();
require_once 'config.php';

if (!empty($_SESSION['loggedin'])) {
    header('Location: index.php?a=home');
    exit();
}

$errorMessage = '';
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
$usuarioValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $usuarioValue = $usuario;

    if ($usuario === '' || $password === '') {
        $errorMessage = 'Por favor complete todos los campos.';
    } else {
        try {
            $sql = "SELECT o.*, p.perf_nombre, p.perf_descripcion
                    FROM operador o
                    LEFT JOIN perfil p ON p.perf_id = o.perf_id
                    WHERE o.ope_user = :usuario
                      AND o.bestado = 1
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':usuario' => $usuario]);
            $operador = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($operador) {
                if ((int)$operador['intentos_fallidos'] >= 5) {
                    $errorMessage = 'Cuenta bloqueada por múltiples intentos fallidos. Contacte al administrador.';
                } elseif ($password === $operador['ope_pass']) {
                    $_SESSION['operador'] = [
                        'ope_id' => (int)$operador['ope_id'],
                        'ope_user' => $operador['ope_user'],
                        'ope_nombre' => $operador['ope_nombre'],
                        'perf_id' => (int)$operador['perf_id'],
                        'perf_nombre' => $operador['perf_nombre'] ?? '',
                        'perf_descripcion' => $operador['perf_descripcion'] ?? '',
                    ];
                    $_SESSION['operador_nombre'] = $operador['ope_nombre'];
                    $_SESSION['operador_img'] = $operador['ope_img'] ?? null;
                    $_SESSION['loggedin'] = true;

                    $updateSql = "UPDATE operador
                                   SET ultimo_acceso = NOW(), intentos_fallidos = 0
                                   WHERE ope_id = :id";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([':id' => $operador['ope_id']]);

                    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php?a=home';
                    unset($_SESSION['redirect_after_login']);

                    header('Location: ' . $redirect);
                    exit();
                } else {
                    $updateSql = "UPDATE operador
                                   SET intentos_fallidos = intentos_fallidos + 1
                                   WHERE ope_id = :id";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([':id' => $operador['ope_id']]);

                    $errorMessage = 'Usuario o contraseña incorrectos.';
                }
            } else {
                $errorMessage = 'Usuario o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Error en el sistema. Intente nuevamente más tarde.';
            error_log('Error en login: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Catastro</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Sistema de Catastro</h2>
            <p>Ingrese sus credenciales</p>
        </div>

        <form class="login-form" method="POST" action="" id="loginForm" novalidate>
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?=h($errorMessage)?></div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?=h($successMessage)?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario" required value="<?=h($usuarioValue)?>">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
            </div>

            <button type="submit" class="btn-login" name="login">Iniciar Sesión</button>

            <div class="forgot-password">
                <a href="recuperar-password.php">¿Olvidó su contraseña?</a>
            </div>
        </form>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>

