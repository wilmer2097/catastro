<?php
session_start();
require_once 'config.php';

$mensaje = '';
$mensajeTipo = '';
$usuarioValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $usuarioValue = $usuario;

    if ($usuario === '') {
        $mensaje = 'Por favor ingrese su usuario o correo registrado.';
        $mensajeTipo = 'error';
    } else {
        try {
            $sql = "SELECT ope_id, ope_nombre, ope_login FROM operadores WHERE (ope_user = :usuario OR ope_login = :usuario) LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':usuario' => $usuario]);
            $operador = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($operador) {
                $mensaje = 'Hemos registrado su solicitud. Un administrador se pondrá en contacto para restablecer la contraseña.';
                $mensajeTipo = 'success';
            } else {
                $mensaje = 'No encontramos una cuenta asociada a esos datos.';
                $mensajeTipo = 'error';
            }
        } catch (PDOException $e) {
            $mensaje = 'Ocurrió un error al procesar la solicitud. Intente nuevamente.';
            $mensajeTipo = 'error';
            error_log('Error en recuperación de contraseña: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Sistema de Catastro</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-header">
            <h2>Recuperar contraseña</h2>
            <p>Ingrese su usuario o correo registrado</p>
        </div>
        <div class="recovery-content">
            <?php if ($mensaje): ?>
                <div class="alert alert-<?= $mensajeTipo === 'success' ? 'success' : 'error' ?>"><?=h($mensaje)?></div>
            <?php endif; ?>

            <p>Ingrese su usuario o correo electrónico y un miembro del equipo validará la cuenta para restablecer su acceso.</p>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="usuario">Usuario o Correo</label>
                    <input type="text" id="usuario" name="usuario" value="<?=h($usuarioValue)?>" placeholder="Ingrese su usuario o correo" required>
                </div>

                <button type="submit" class="btn-submit">Enviar solicitud</button>
            </form>

            <div class="back-link">
                <a href="login.php">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
