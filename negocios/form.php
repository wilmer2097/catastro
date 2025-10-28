<?php $row = $row ?? ['inmueble_id'=>0]; ?>
<h2><?= !empty($row['id']) ? 'Editar negocio' : 'Nuevo negocio' ?></h2>
<form method="post" action="?a=negocio_save" class="row g-3">
  <?php if (!empty($row['id'])): ?><input type="hidden" name="id" value="<?=h($row['id'])?>"><?php endif; ?>
  <div class="col-md-4">
    <label class="form-label">Inmueble</label>
    <select name="inmueble_id" class="form-select" required>
      <option value="">-- Selecciona --</option>
      <?php
        $opts = $pdo->query("SELECT id, calle, cdra, num, nombre FROM inmuebles ORDER BY calle, cdra, num")->fetchAll();
        foreach ($opts as $o) {
          $sel = ($o['id']==($row['inmueble_id'] ?? 0)) ? 'selected' : '';
          $label = ($o['nombre'] ? $o['nombre'].' - ' : '') . $o['calle'].' C'.$o['cdra'].' #'.$o['num'];
          echo '<option value="'.h($o['id']).'" '.$sel.'>'.h($label).'</option>';
        }
      ?>
    </select>
  </div>
  <div class="col-md-2"><label class="form-label">Piso</label><input name="piso" class="form-control" value="<?=h($row['piso'] ?? '')?>" placeholder="Z, A, 1, 2"></div>
  <div class="col-md-2"><label class="form-label">Interior</label><input name="interior" class="form-control" value="<?=h($row['interior'] ?? '')?>"></div>
  <div class="col-md-2"><label class="form-label">Número</label><input name="num" class="form-control" value="<?=h($row['num'] ?? '')?>"></div>
  <div class="col-md-2"><label class="form-label">Tipo</label><input name="tipo" class="form-control" value="<?=h($row['tipo'] ?? '')?>" placeholder="Imprenta, tienda..."></div>
  <div class="col-md-6"><label class="form-label">Nombre del negocio</label><input name="nombre" class="form-control" value="<?=h($row['nombre'] ?? '')?>" required></div>
  <div class="col-md-6"><label class="form-label">Productos (R1..R6, una por línea)</label><textarea name="productos" class="form-control" rows="4"><?=h($row['productos'] ?? '')?></textarea></div>
  <div class="col-md-4"><label class="form-label">Contacto</label><input name="contacto" class="form-control" value="<?=h($row['contacto'] ?? '')?>"></div>
  <div class="col-md-4"><label class="form-label">Cargo</label><input name="cargo" class="form-control" value="<?=h($row['cargo'] ?? '')?>"></div>
  <div class="col-md-4"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" value="<?=h($row['telefono'] ?? '')?>"></div>
  <div class="col-md-4"><label class="form-label">RUC</label><input name="ruc" class="form-control" value="<?=h($row['ruc'] ?? '')?>"></div>
  <div class="col-md-4"><label class="form-label">Tamaño (m²)</label><input type="number" step="0.01" name="tam_m2" class="form-control" value="<?=h($row['tam_m2'] ?? '')?>"></div>
  <div class="col-md-4 d-flex align-items-end">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="imprenta" <?= !empty($row['imprenta'])?'checked':''; ?>>
      <label class="form-check-label">Imprenta (emite comprobantes)</label>
    </div>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="?a=negocios<?= !empty($row['inmueble_id'])?('&inmueble_id='.$row['inmueble_id']):''; ?>">Volver</a>
  </div>
</form>
