<?php
// Repoblar datos enviados si hubo error
if (!empty($_SESSION['form_data'])) {
    $row = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}

$row = $row ?? null;
$perfiles = $perfiles ?? [];
?>

<h2><?= $row ? 'Editar operador' : 'Nuevo operador' ?></h2>

<!-- Mostrar error dentro del formulario -->
<?php if (!empty($_SESSION['form_error'])): ?>
  <div class="alert alert-danger">
    <?= h($_SESSION['form_error']) ?>
  </div>
  <?php unset($_SESSION['form_error']); ?>
<?php endif; ?>

<form method="post" action="index.php?a=operador_save" class="row g-3">

  <?php if ($row && !empty($row['ope_id'])): ?>
    <input type="hidden" name="id" value="<?= h($row['ope_id']) ?>">
  <?php endif; ?>

  <div class="col-md-4">
    <label class="form-label">Usuario</label>
    <input type="text" name="ope_user" class="form-control"
           value="<?= h($row['ope_user'] ?? '') ?>" required>
  </div>

  <div class="col-md-4">
    <label class="form-label">Nombre completo</label>
    <input type="text" name="ope_nombre" class="form-control"
           value="<?= h($row['ope_nombre'] ?? '') ?>" required>
  </div>

  <div class="col-md-4">
    <label class="form-label">Correo</label>
    <input type="email" name="ope_login" class="form-control"
           value="<?= h($row['ope_login'] ?? '') ?>" required>
  </div>

  <div class="col-md-4">
    <label class="form-label">Teléfono</label>
    <input type="text" name="ope_telefono" class="form-control"
           value="<?= h($row['ope_telefono'] ?? '') ?>">
  </div>

  <div class="col-md-4">
    <label class="form-label">Perfil</label>
    <select name="perf_id" class="form-select" required>
      <option value="">-- Selecciona perfil --</option>
      <?php foreach ($perfiles as $p): ?>
        <?php
          $sel = '';
          if ($row && isset($row['perf_id']) && (int)$row['perf_id'] === (int)$p['perf_id']) {
              $sel = 'selected';
          }
        ?>
        <option value="<?= h($p['perf_id']) ?>" <?= $sel ?>>
          <?= h($p['perf_nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label">
      Contraseña <?= $row ? '(dejar vacío para mantener)' : '' ?>
    </label>
    <input type="password" name="ope_pass" class="form-control">
  </div>

  <div class="col-md-4">
    <label class="form-label">Estado</label>
    <select name="bestado" class="form-select">
      <?php $b = isset($row['bestado']) ? (int)$row['bestado'] : 1; ?>
      <option value="1" <?= $b === 1 ? 'selected' : '' ?>>Activo</option>
      <option value="0" <?= $b === 0 ? 'selected' : '' ?>>Inactivo</option>
    </select>
  </div>

  <div class="col-12">
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="index.php?a=operadores">Volver</a>
  </div>

</form>
