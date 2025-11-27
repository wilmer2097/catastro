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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Ubuntu, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;
            background: #f9fafb;
            color: #111827;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
        }

        .recovery-container {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .recovery-header {
            background: transparent;
            color: #111827;
            padding: 24px;
            text-align: center;
            border-bottom: 1px solid #f3f4f6;
        }

        .recovery-header h2 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .recovery-content {
            padding: 24px;
        }

        .recovery-content p {
            margin-bottom: 20px;
            color: #555;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #111827;
            box-shadow: 0 0 0 3px rgba(17,24,39,0.08);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #111827;
            color: #ffffff;
            border: 1px solid #111827;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s, transform 0.15s;
        }

        .btn-submit:hover {
            background: #0b1220;
            transform: translateY(-1px);
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background-color: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }

        .back-link a {
            color: #374151;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
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
