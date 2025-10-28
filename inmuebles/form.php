<?php $row = $row ?? null; ?>
<h2><?= $row ? 'Editar inmueble' : 'Nuevo inmueble' ?></h2>
<form method="post" action="?a=inmueble_save" class="row g-3">
  <?php if ($row): ?><input type="hidden" name="id" value="<?=h($row['id'])?>"><?php endif; ?>
  <div class="col-md-4"><label class="form-label">Calle</label><input name="calle" class="form-control" value="<?=h($row['calle'] ?? '')?>" required></div>
  <div class="col-md-2"><label class="form-label">Cdra</label><input type="number" name="cdra" class="form-control" value="<?=h($row['cdra'] ?? '')?>" required></div>
  <div class="col-md-2"><label class="form-label">Número</label><input name="num" class="form-control" value="<?=h($row['num'] ?? '')?>" required></div>
  <div class="col-md-4">
    <label class="form-label">Tipo</label>
    <select name="tipo" class="form-select" required>
      <?php
        $tipos = ['Casa','Galería','Feria','Parqueo','Almacén','Fábrica','Restaurante','Construcción','Otro'];
        $sel = $row['tipo'] ?? '';
        foreach($tipos as $t){
          $s = ($t===$sel)?'selected':'';
          echo "<option $s>".h($t)."</option>";
        }
      ?>
    </select>
  </div>
  <div class="col-md-6"><label class="form-label">Nombre</label><input name="nombre" class="form-control" value="<?=h($row['nombre'] ?? '')?>"></div>
  <div class="col-md-6 d-flex align-items-end gap-3">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="uso_mul" <?= !empty($row['uso_mul'])?'checked':''; ?>>
      <label class="form-check-label">Uso múltiple</label>
    </div>
    <div class="form-check ms-3">
      <input class="form-check-input" type="checkbox" name="nivel_z" <?= !empty($row['nivel_z'])?'checked':''; ?>>
      <label class="form-check-label">Zócalo (Z)</label>
    </div>
    <div class="form-check ms-3">
      <input class="form-check-input" type="checkbox" name="nivel_a" <?= !empty($row['nivel_a'])?'checked':''; ?>>
      <label class="form-check-label">Altos (A)</label>
    </div>
  </div>
  <div class="col-md-3"><label class="form-label">Dir1 (calle inventario)</label><input name="dir1" class="form-control" value="<?=h($row['dir1'] ?? '')?>"></div>
  <div class="col-md-3"><label class="form-label">Dir2</label><input name="dir2" class="form-control" value="<?=h($row['dir2'] ?? '')?>"></div>
  <div class="col-md-3"><label class="form-label">Dir3</label><input name="dir3" class="form-control" value="<?=h($row['dir3'] ?? '')?>"></div>
  <div class="col-md-3"><label class="form-label">Dir4</label><input name="dir4" class="form-control" value="<?=h($row['dir4'] ?? '')?>"></div>
  <div class="col-md-3"><label class="form-label">Nro (ficha)</label><input name="nro" class="form-control" value="<?=h($row['nro'] ?? '')?>"></div>
  <div class="col-md-3"><label class="form-label">Operador</label><input name="ope" class="form-control" value="<?=h($row['ope'] ?? '')?>"></div>
  <div class="col-12">
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="?a=inmuebles">Volver</a>
  </div>
</form>
