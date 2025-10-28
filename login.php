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
        
        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        
        .error-message.show {
            display: block;
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
            <?php
            session_start();
            
            // Mostrar mensajes de error o éxito
            if(isset($_SESSION['error_login'])) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error_login']) . '</div>';
                unset($_SESSION['error_login']);
            }
            
            if(isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']);
            }
            ?>
            
            <div class="form-group">
                <label for="usuario">Usuario o Correo</label>
                <input type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario o correo" required>
                <span class="error-message" id="error-usuario">Este campo es requerido</span>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
                <span class="error-message" id="error-password">Este campo es requerido</span>
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
            
            // Validar usuario
            const usuario = document.getElementById('usuario');
            const errorUsuario = document.getElementById('error-usuario');
            if(usuario.value.trim() === '') {
                usuario.classList.add('error');
                errorUsuario.classList.add('show');
                isValid = false;
            } else {
                usuario.classList.remove('error');
                errorUsuario.classList.remove('show');
            }
            
            // Validar contraseña
            const password = document.getElementById('password');
            const errorPassword = document.getElementById('error-password');
            if(password.value.trim() === '') {
                password.classList.add('error');
                errorPassword.classList.add('show');
                isValid = false;
            } else {
                password.classList.remove('error');
                errorPassword.classList.remove('show');
            }
            
            if(!isValid) {
                e.preventDefault();
            }
        });
        
        // Limpiar errores al escribir
        document.getElementById('usuario').addEventListener('input', function() {
            this.classList.remove('error');
            document.getElementById('error-usuario').classList.remove('show');
        });
        
        document.getElementById('password').addEventListener('input', function() {
            this.classList.remove('error');
            document.getElementById('error-password').classList.remove('show');
        });
    </script>
</body>
</html>

<?php
// Procesar el login
if(isset($_POST['login'])) {
    require_once 'config.php'; // Archivo de conexión a la BD
    
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    
    // Validaciones del servidor
    if(empty($usuario) || empty($password)) {
        $_SESSION['error_login'] = 'Por favor complete todos los campos';
        header('Location: login.php');
        exit();
    }
    
    try {
        // Buscar usuario por nombre de usuario o correo
        $sql = "SELECT * FROM operadores 
                WHERE (ope_user = :usuario OR ope_login = :usuario) 
                AND bestado = 1 
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->execute();
        
        $operador = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($operador) {
            // Verificar la contraseña
            if(password_verify($password, $operador['ope_pass'])) {
                // Verificar intentos fallidos
                if($operador['intentos_fallidos'] >= 5) {
                    $_SESSION['error_login'] = 'Cuenta bloqueada por múltiples intentos fallidos. Contacte al administrador.';
                    header('Location: login.php');
                    exit();
                }
                
                // Login exitoso
                $_SESSION['operador_id'] = $operador['ope_id'];
                $_SESSION['operador_nombre'] = $operador['ope_nombre'];
                $_SESSION['operador_user'] = $operador['ope_user'];
                $_SESSION['operador_rol'] = $operador['ope_rol'];
                $_SESSION['operador_img'] = $operador['ope_img'];
                $_SESSION['loggedin'] = true;
                
                // Actualizar último acceso y resetear intentos fallidos
                $updateSql = "UPDATE operadores 
                             SET ultimo_acceso = NOW(), intentos_fallidos = 0 
                             WHERE ope_id = :id";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindParam(':id', $operador['ope_id'], PDO::PARAM_INT);
                $updateStmt->execute();
                
                // Redirigir al dashboard
                header('Location: dashboard.php');
                exit();
                
            } else {
                // Contraseña incorrecta - incrementar intentos fallidos
                $updateSql = "UPDATE operadores 
                             SET intentos_fallidos = intentos_fallidos + 1 
                             WHERE ope_id = :id";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindParam(':id', $operador['ope_id'], PDO::PARAM_INT);
                $updateStmt->execute();
                
                $_SESSION['error_login'] = 'Usuario o contraseña incorrectos';
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['error_login'] = 'Usuario o contraseña incorrectos';
            header('Location: login.php');
            exit();
        }
        
    } catch(PDOException $e) {
        $_SESSION['error_login'] = 'Error en el sistema. Intente nuevamente.';
        error_log("Error en login: " . $e->getMessage());
        header('Location: login.php');
        exit();
    }
}
?>