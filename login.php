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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Ubuntu, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif; background: #f9fafb; color: #111827; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 24px; }
        .login-container { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; max-width: 400px; width: 100%; }
        .login-header { background: transparent; color: #111827; padding: 24px; text-align: center; border-bottom: 1px solid #f3f4f6; }
        .login-header h2 { font-size: 22px; font-weight: 600; margin-bottom: 6px; }
        .login-header p { font-size: 14px; color: #6b7280; }
        .login-form { padding: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px; }
        .form-group input { width: 100%; padding: 11px 12px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; font-size: 14px; transition: border-color 0.2s, box-shadow 0.2s; }
        .form-group input:focus { outline: none; border-color: #111827; box-shadow: 0 0 0 3px rgba(17,24,39,0.08); }
        .form-group input.error { border-color: #e74c3c; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        .alert-error { background-color: #fee; color: #c33; border: 1px solid #fcc; }
        .alert-success { background-color: #efe; color: #3c3; border: 1px solid #cfc; }
        .btn-login { width: 100%; padding: 12px; background: #111827; color: #ffffff; border: 1px solid #111827; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background-color 0.15s, transform 0.15s; }
        .btn-login:hover { background: #0b1220; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }
        .forgot-password { text-align: center; margin-top: 15px; }
        .forgot-password a { color: #374151; text-decoration: none; font-size: 14px; }
        .forgot-password a:hover { text-decoration: underline; }
    </style>
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

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let isValid = true;
            const usuario = document.getElementById('usuario');
            const password = document.getElementById('password');
            if (usuario.value.trim() === '') { usuario.classList.add('error'); isValid = false; } else { usuario.classList.remove('error'); }
            if (password.value.trim() === '') { password.classList.add('error'); isValid = false; } else { password.classList.remove('error'); }
            if (!isValid) { e.preventDefault(); }
        });
        document.getElementById('usuario').addEventListener('input', function() { this.classList.remove('error'); });
        document.getElementById('password').addEventListener('input', function() { this.classList.remove('error'); });
    </script>
</body>
</html>

