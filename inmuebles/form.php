<?php
$row = $row ?? null;
// Cargar calles disponibles
try {
  $calles = $pdo->query("SELECT id, nombre, cdra_ini, cdra_fin FROM calles ORDER BY nombre")->fetchAll();
} catch (Throwable $e) {
  $calles = [];
}
try {
  $tipos = $pdo->query("SELECT nombre FROM tipos_inmueble ORDER BY nombre ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
  $tipos = [];
}
$selId = isset($row['calle_id']) ? (int)$row['calle_id'] : 0;
$selNombre = $row['calle'] ?? '';
$selectedCalle = null;
foreach ($calles as $c) {
  if (($selId && (int)$c['id'] === $selId) || (!$selId && $selNombre !== '' && $c['nombre'] === $selNombre)) {
    $selectedCalle = $c;
    break;
  }
}
$selectedCdra = isset($row['cdra']) ? (int)$row['cdra'] : '';
$dir1Value = $selectedCalle['nombre'] ?? ($row['dir1'] ?? ($row['calle'] ?? ''));
?>
<h2><?= $row ? 'Editar inmueble' : 'Nuevo inmueble' ?></h2>
<?php if (!empty($row['nro'])): ?>
  <p class="text-muted">Ficha: <strong><?=h($row['nro'])?></strong></p>
<?php endif; ?>
<form method="post" action="?a=inmueble_save" class="row g-3">
  <?php if ($row): ?><input type="hidden" name="id" value="<?=h($row['id'])?>"><?php endif; ?>
  <div class="col-md-4">
    <label class="form-label">Calle</label>
    <select name="calle_id" id="calle_id" class="form-select" required>
      <option value="">-- Selecciona calle --</option>
      <?php
        foreach ($calles as $c) {
          $selected = ($selectedCalle && (int)$c['id'] === (int)$selectedCalle['id']) ? 'selected' : '';
          echo '<option value="'.h($c['id']).'" data-cdra_ini="'.h($c['cdra_ini']).'" data-cdra_fin="'.h($c['cdra_fin']).'" data-nombre="'.h($c['nombre']).'" '.$selected.'>'.h($c['nombre']).' (C'.h($c['cdra_ini']).'-C'.h($c['cdra_fin']).')</option>';
        }
      ?>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label" for="cdraSelect">Cdra</label>
    <select name="cdra" id="cdraSelect" class="form-select" required data-selected="<?=h($selectedCdra)?>">
      <option value="">-- Selecciona cuadra --</option>
      <?php
        if ($selectedCalle) {
          for ($i = (int)$selectedCalle['cdra_ini']; $i <= (int)$selectedCalle['cdra_fin']; $i++) {
            $sel = ((string)$i === (string)$selectedCdra) ? 'selected' : '';
            echo '<option value="'.h($i).'" '.$sel.'>C'.h($i).'</option>';
          }
        }
      ?>
    </select>
  </div>
  <div class="col-md-2"><label class="form-label">Número</label><input name="num" class="form-control" value="<?=h($row['num'] ?? '')?>" required></div>
  <div class="col-md-4">
    <label class="form-label">Tipo</label>
    <select name="tipo" class="form-select" required>
      <?php
        $sel = $row['tipo'] ?? '';
        foreach ($tipos as $t) {
          $s = ($t === $sel) ? 'selected' : '';
          echo '<option value="'.h($t).'" '.$s.'>'.h($t).'</option>';
        }
      ?>
    </select>
  </div>
  <div class="col-md-6"><label class="form-label">Nombre del edificio</label><input name="nombre" class="form-control" value="<?=h($row['nombre'] ?? '')?>"></div>
  <div class="col-md-6 d-flex align-items-end gap-3">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="uso_mul" <?= !empty($row['uso_mul'])?'checked':''; ?>>
      <label class="form-check-label">Uso múltiple</label>
    </div>
  </div>
  <?php
    if (!isset($row['nro_sotanos'])) {
      $row['nro_sotanos'] = $row['nro_sotanos'] ?? ($row['nro_zocalos'] ?? ($row['nzocalos'] ?? ($row['zocalo'] ?? '')));
    }
    if (!isset($row['nro_pisos'])) {
      $row['nro_pisos'] = $row['nro_pisos'] ?? ($row['pisos'] ?? ($row['npisos'] ?? ($row['n_pisos'] ?? ($row['nro_pisos'] ?? ''))));
    }
  ?>
  <div class="col-md-3"><label class="form-label">Nro. Locales</label><input type="number" min="0" name="nro_locales" class="form-control" value="<?=h($row['nro_locales'] ?? '')?>"></div>
  <div class="col-md-3"><label class="form-label">Nro. Sótanos</label><input type="number" min="0" name="nro_sotanos" class="form-control" value="<?=h($row['nro_sotanos'] ?? '')?>"></div>
  <div class="col-md-3"><label class="form-label">Nro. Pisos</label><input type="number" min="0" name="nro_pisos" class="form-control" value="<?=h($row['nro_pisos'] ?? '')?>"></div>
  <div class="col-md-3"><label class="form-label">Dir1 (calle inventario)</label><input name="dir1" id="dir1Input" class="form-control" value="<?=h($dir1Value)?>" readonly></div>
  <?php
    $dir2 = $row['dir2'] ?? '';
    $dir3 = $row['dir3'] ?? '';
    $dir4 = $row['dir4'] ?? '';
    $renderCalleOptions = function($selected) use ($calles) {
      $options = '<option value="">-- Selecciona calle --</option>';
      foreach ($calles as $c) {
        $sel = ($selected !== '' && $c['nombre'] === $selected) ? 'selected' : '';
        $options .= '<option value="'.h($c['nombre']).'" '.$sel.'>'.h($c['nombre']).'</option>';
      }
      return $options;
    };
  ?>
  <div class="col-md-3">
    <label class="form-label">Dir2</label>
    <select name="dir2" id="dir2" class="form-select">
      <?=$renderCalleOptions($dir2)?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Dir3</label>
    <select name="dir3" id="dir3" class="form-select">
      <?=$renderCalleOptions($dir3)?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Dir4</label>
    <select name="dir4" id="dir4" class="form-select">
      <?=$renderCalleOptions($dir4)?>
    </select>
  </div>
  <div class="col-12">
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="?a=inmuebles">Volver</a>
  </div>
</form>

<script src="assets/js/inmuebles.js?v=1"></script>

