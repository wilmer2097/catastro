<?php
session_start();
require_once 'config.php';

if (!empty($_SESSION['loggedin'])) {
    header('Location: dashboard.php');
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
            $sql = "SELECT * FROM operadores
                    WHERE (ope_user = :usuario OR ope_login = :usuario)
                    AND bestado = 1
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':usuario' => $usuario]);
            $operador = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($operador) {
                if ((int)$operador['intentos_fallidos'] >= 5) {
                    $errorMessage = 'Cuenta bloqueada por múltiples intentos fallidos. Contacte al administrador.';
                } elseif (password_verify($password, $operador['ope_pass'])) {
                    $_SESSION['operador_id'] = (int)$operador['ope_id'];
                    $_SESSION['operador_nombre'] = $operador['ope_nombre'];
                    $_SESSION['operador_user'] = $operador['ope_user'];
                    $_SESSION['operador_rol'] = $operador['ope_rol'];
                    $_SESSION['operador_img'] = $operador['ope_img'];
                    $_SESSION['loggedin'] = true;

                    $updateSql = "UPDATE operadores
                                   SET ultimo_acceso = NOW(), intentos_fallidos = 0
                                   WHERE ope_id = :id";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([':id' => $operador['ope_id']]);

                    $redirect = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                    unset($_SESSION['redirect_after_login']);

                    header('Location: ' . $redirect);
                    exit();
                } else {
                    $updateSql = "UPDATE operadores
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

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .login-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-form {
            padding: 30px;
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

        .form-group input.error {
            border-color: #e74c3c;
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

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
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
                <label for="usuario">Usuario o Correo</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario o correo" required value="<?=h($usuarioValue)?>">
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

            if (usuario.value.trim() === '') {
                usuario.classList.add('error');
                isValid = false;
            } else {
                usuario.classList.remove('error');
            }

            if (password.value.trim() === '') {
                password.classList.add('error');
                isValid = false;
            } else {
                password.classList.remove('error');
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        document.getElementById('usuario').addEventListener('input', function() {
            this.classList.remove('error');
        });

        document.getElementById('password').addEventListener('input', function() {
            this.classList.remove('error');
        });
    </script>
</body>
</html>
