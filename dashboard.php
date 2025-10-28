<?php
/**
 * Ejemplo de página protegida - Dashboard
 * Guardar como: dashboard.php
 */
require_once 'auth.php'; // Proteger la página
require_once 'config.php'; // Ya está incluido en auth.php pero por claridad

// Obtener datos del operador actual
$operador = operador_actual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Catastro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-size: 20px;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #667eea;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
        }
        
        .user-role {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .welcome-card h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome-card p {
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        
        .info-box {
            background: #e8f4fd;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .info-box strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">Sistema de Catastro</div>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($operador['nombre'], 0, 1)); ?>
            </div>
            <div class="user-details">
                <span class="user-name"><?php echo h($operador['nombre']); ?></span>
                <span class="user-role">
                    <?php 
                    $roles = [
                        'admin' => 'Administrador',
                        'supervisor' => 'Supervisor',
                        'operador' => 'Operador'
                    ];
                    echo $roles[$operador['rol']] ?? 'Usuario';
                    ?>
                </span>
            </div>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="welcome-card">
            <h1>¡Bienvenido, <?php echo h($operador['nombre']); ?>!</h1>
            <p>Sistema de Gestión Catastral - Panel de Control</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Propiedades Registradas</h3>
                <div class="number">1,234</div>
            </div>
            
            <div class="stat-card">
                <h3>Predios Actualizados</h3>
                <div class="number">856</div>
            </div>
            
            <div class="stat-card">
                <h3>Consultas del Mes</h3>
                <div class="number">342</div>
            </div>
            
            <div class="stat-card">
                <h3>Usuarios Activos</h3>
                <div class="number">
                    <?php
                    // Ejemplo de consulta a la base de datos
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM operadores WHERE bestado = 1");
                        $result = $stmt->fetch();
                        echo $result['total'];
                    } catch(PDOException $e) {
                        echo "0";
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="info-box">
            <strong>Información de tu sesión:</strong><br>
            Usuario: <?php echo h($operador['usuario']); ?><br>
            ID: <?php echo h($operador['id']); ?><br>
            Rol: <?php echo h($operador['rol']); ?>
            
            <?php if(es_admin()): ?>
                <br><br><strong>Tienes permisos de administrador</strong>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>