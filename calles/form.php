<?php $row = $row ?? null; ?>
<h2><?= $row ? 'Editar calle' : 'Nueva calle' ?></h2>
<form method="post" action="?a=calle_save" class="row g-3">
  <?php if ($row): ?><input type="hidden" name="id" value="<?=h($row['id'])?>"><?php endif; ?>
  <div class="col-md-6">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" value="<?=h($row['nombre'] ?? '')?>" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Cdra inicial</label>
    <input type="number" name="cdra_ini" class="form-control" value="<?=h($row['cdra_ini'] ?? '')?>" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Cdra final</label>
    <input type="number" name="cdra_fin" class="form-control" value="<?=h($row['cdra_fin'] ?? '')?>" required>
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="?a=calles">Volver</a>
  </div>
</form>

