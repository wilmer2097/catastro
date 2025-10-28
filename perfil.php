<?php
require_once 'auth.php';

$operador = operador_actual();

if (!$operador['id']) {
    header('Location: logout.php');
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT * FROM operadores WHERE ope_id = :id LIMIT 1');
    $stmt->execute([':id' => $operador['id']]);
    $operadorDb = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error obteniendo operador: ' . $e->getMessage());
    $_SESSION['flash'] = 'No se pudo cargar la información del perfil.';
    header('Location: ?a=home');
    exit();
}

if (!$operadorDb) {
    $_SESSION['flash'] = 'La cuenta ya no está disponible.';
    header('Location: logout.php');
    exit();
}

$formData = [
    'ope_user' => $operadorDb['ope_user'],
    'ope_nombre' => $operadorDb['ope_nombre'],
    'ope_login' => $operadorDb['ope_login'],
    'ope_telefono' => $operadorDb['ope_telefono'],
];

$errores = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['ope_user'] = trim($_POST['ope_user'] ?? '');
    $formData['ope_nombre'] = trim($_POST['ope_nombre'] ?? '');
    $formData['ope_login'] = trim($_POST['ope_login'] ?? '');
    $formData['ope_telefono'] = trim($_POST['ope_telefono'] ?? '');

    $passwordActual = $_POST['password_actual'] ?? '';
    $nuevaPassword = $_POST['nueva_password'] ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';

    if ($formData['ope_user'] === '') {
        $errores[] = 'El usuario es obligatorio.';
    }

    if ($formData['ope_nombre'] === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if ($formData['ope_login'] === '') {
        $errores[] = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($formData['ope_login'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingrese un correo electrónico válido.';
    }

    try {
        $stmt = $pdo->prepare('SELECT ope_id FROM operadores WHERE ope_user = :user AND ope_id <> :id LIMIT 1');
        $stmt->execute([':user' => $formData['ope_user'], ':id' => $operador['id']]);
        if ($stmt->fetch()) {
            $errores[] = 'El usuario ingresado ya se encuentra en uso.';
        }

        $stmt = $pdo->prepare('SELECT ope_id FROM operadores WHERE ope_login = :login AND ope_id <> :id LIMIT 1');
        $stmt->execute([':login' => $formData['ope_login'], ':id' => $operador['id']]);
        if ($stmt->fetch()) {
            $errores[] = 'El correo electrónico ingresado ya se encuentra en uso.';
        }
    } catch (PDOException $e) {
        $errores[] = 'No se pudieron validar los datos ingresados.';
        error_log('Error validando duplicados de operador: ' . $e->getMessage());
    }

    $actualizarPassword = false;
    if ($nuevaPassword !== '' || $confirmarPassword !== '' || $passwordActual !== '') {
        if ($nuevaPassword === '' || $confirmarPassword === '' || $passwordActual === '') {
            $errores[] = 'Para cambiar la contraseña complete todos los campos del bloque de seguridad.';
        } elseif (!password_verify($passwordActual, $operadorDb['ope_pass'])) {
            $errores[] = 'La contraseña actual no es correcta.';
        } elseif ($nuevaPassword !== $confirmarPassword) {
            $errores[] = 'La nueva contraseña y su confirmación no coinciden.';
        } elseif (strlen($nuevaPassword) < 8) {
            $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } else {
            $actualizarPassword = true;
        }
    }

    if (!$errores) {
        try {
            $sql = 'UPDATE operadores SET ope_user = :user, ope_nombre = :nombre, ope_login = :login, ope_telefono = :telefono';
            $params = [
                ':user' => $formData['ope_user'],
                ':nombre' => $formData['ope_nombre'],
                ':login' => $formData['ope_login'],
                ':telefono' => $formData['ope_telefono'],
                ':id' => $operador['id'],
            ];

            if ($actualizarPassword) {
                $sql .= ', ope_pass = :pass';
                $params[':pass'] = password_hash($nuevaPassword, PASSWORD_BCRYPT);
            }

            $sql .= ' WHERE ope_id = :id';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $_SESSION['operador_nombre'] = $formData['ope_nombre'];
            $_SESSION['operador_user'] = $formData['ope_user'];

            $operadorDb = array_merge($operadorDb, [
                'ope_user' => $formData['ope_user'],
                'ope_nombre' => $formData['ope_nombre'],
                'ope_login' => $formData['ope_login'],
                'ope_telefono' => $formData['ope_telefono'],
            ]);

            if ($actualizarPassword) {
                $operadorDb['ope_pass'] = $params[':pass'];
            }

            $successMessage = 'Perfil actualizado correctamente.';
        } catch (PDOException $e) {
            $errores[] = 'Ocurrió un error al guardar los cambios. Intente nuevamente.';
            error_log('Error actualizando perfil: ' . $e->getMessage());
        }
    }
}

include __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-3">Editar perfil</h1>
        <p class="text-muted mb-4">Actualiza tu información personal y credenciales de acceso.</p>

        <?php if ($errores): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errores as $error): ?>
                <li><?=h($error)?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
          <div class="alert alert-success"><?=h($successMessage)?></div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="ope_user" class="form-label">Usuario</label>
              <input type="text" class="form-control" id="ope_user" name="ope_user" required value="<?=h($formData['ope_user'])?>">
            </div>
            <div class="col-md-6">
              <label for="ope_login" class="form-label">Correo electrónico</label>
              <input type="email" class="form-control" id="ope_login" name="ope_login" required value="<?=h($formData['ope_login'])?>">
            </div>
            <div class="col-md-6">
              <label for="ope_nombre" class="form-label">Nombre completo</label>
              <input type="text" class="form-control" id="ope_nombre" name="ope_nombre" required value="<?=h($formData['ope_nombre'])?>">
            </div>
            <div class="col-md-6">
              <label for="ope_telefono" class="form-label">Teléfono</label>
              <input type="text" class="form-control" id="ope_telefono" name="ope_telefono" value="<?=h($formData['ope_telefono'])?>">
            </div>
          </div>

          <hr class="my-4">

          <h2 class="h5 mb-3">Seguridad</h2>
          <p class="text-muted">Para cambiar tu contraseña completa los siguientes campos.</p>

          <div class="row g-3">
            <div class="col-md-4">
              <label for="password_actual" class="form-label">Contraseña actual</label>
              <input type="password" class="form-control" id="password_actual" name="password_actual" autocomplete="current-password">
            </div>
            <div class="col-md-4">
              <label for="nueva_password" class="form-label">Nueva contraseña</label>
              <input type="password" class="form-control" id="nueva_password" name="nueva_password" autocomplete="new-password">
            </div>
            <div class="col-md-4">
              <label for="confirmar_password" class="form-label">Confirmar nueva contraseña</label>
              <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" autocomplete="new-password">
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="?a=home" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
