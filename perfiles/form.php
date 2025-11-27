<?php
$row = $row ?? null;
?>
<h2><?= $row ? 'Editar perfil' : 'Nuevo perfil' ?></h2>
<form method="post" action="index.php?a=perfil_save" class="row g-3">
  <?php if ($row): ?><input type="hidden" name="id" value="<?=h($row['perf_id'])?>"><?php endif; ?>
  <div class="col-md-6">
    <label class="form-label">Nombre</label>
    <input type="text" name="perf_nombre" class="form-control" value="<?=h($row['perf_nombre'] ?? '')?>" required>
  </div>
  <div class="col-md-12">
    <label class="form-label">Descripción</label>
    <textarea name="perf_descripcion" class="form-control" rows="3"><?=h($row['perf_descripcion'] ?? '')?></textarea>
  </div>
  <div class="col-md-4">
    <label class="form-label">Estado</label>
    <select name="bestado" class="form-select">
      <?php $b = isset($row['bestado']) ? (int)$row['bestado'] : 1; ?>
      <option value="1" <?=$b===1?'selected':'';?>>Activo</option>
      <option value="0" <?=$b===0?'selected':'';?>>Inactivo</option>
    </select>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="index.php?a=perfiles">Volver</a>
  </div>
</form>
