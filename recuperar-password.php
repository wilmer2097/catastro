<?php
session_start();
require_once 'config.php';

$mensaje = '';
$mensajeTipo = '';
$usuarioValue = '';
$passwordGenerado = '';

function generarPasswordTemporal(int $longitud = 12): string
{
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*?';
    $caracteresLength = strlen($caracteres);
    $password = '';

    for ($i = 0; $i < $longitud; $i++) {
        $password .= $caracteres[random_int(0, $caracteresLength - 1)];
    }

    return $password;
}

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
                $passwordGenerado = generarPasswordTemporal();
                $hash = password_hash($passwordGenerado, PASSWORD_DEFAULT);

                $updateSql = "UPDATE operadores
                              SET ope_pass = :password, intentos_fallidos = 0, ultimo_acceso = NULL
                              WHERE ope_id = :id";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([
                    ':password' => $hash,
                    ':id' => $operador['ope_id'],
                ]);

                $mensaje = 'Se generó una nueva contraseña temporal. Úsela para iniciar sesión y cámbiela desde su perfil.';
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .recovery-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 420px;
            width: 100%;
        }

        .recovery-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .recovery-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .recovery-content {
            padding: 30px;
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
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
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

        .new-password {
            background-color: #f5f7ff;
            border: 1px dashed #667eea;
            color: #333;
            font-weight: 600;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            word-break: break-all;
            text-align: center;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }

        .back-link a {
            color: #667eea;
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

            <p>Ingrese su usuario o correo electrónico y el sistema generará una contraseña temporal para restablecer el acceso.</p>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="usuario">Usuario o Correo</label>
                    <input type="text" id="usuario" name="usuario" value="<?=h($usuarioValue)?>" placeholder="Ingrese su usuario o correo" required>
                </div>

                <button type="submit" class="btn-submit">Enviar solicitud</button>
            </form>

            <?php if ($passwordGenerado): ?>
                <div class="new-password">
                    Nueva contraseña: <span><?= h($passwordGenerado) ?></span>
                </div>
            <?php endif; ?>

            <div class="back-link">
                <a href="login.php">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</body>
</html>
