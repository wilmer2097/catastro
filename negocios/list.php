<?php $rows = $rows ?? []; $operador = $operador ?? 0; $anio = $anio ?? 0; $mes = $mes ?? 0; $operadores = $operadores ?? []; $anios = $anios ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Negocios</h2>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="index.php?a=reporte_negocios">Reporte acumulado</a>
    <a class="btn btn-primary" href="?a=negocio_new<?= $inmueble_id?('&inmueble_id='.$inmueble_id):''; ?>">Nuevo negocio</a>
  </div>
</div>

<form class="row g-2 mb-3">
  <input type="hidden" name="a" value="negocios">
  <?php if (!$inmueble_id): ?>
  <div class="col-md-3">
    <input class="form-control" name="q" placeholder="Buscar por negocio, producto o calle" value="<?=h($q)?>">
  </div>
  <?php endif; ?>
  <div class="col-6 col-md-3">
    <select class="form-select" name="operador">
      <option value="0">Operador: Todos</option>
      <?php foreach (($operadores ?? []) as $op): $sel = ((int)($operador ?? 0) === (int)$op['ope_id']) ? 'selected' : ''; ?>
        <option value="<?=h($op['ope_id'])?>" <?=$sel?>><?=h($op['ope_nombre'])?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-2">
    <select class="form-select" name="anio">
      <option value="0">Año: Todos</option>
      <?php foreach (($anios ?? []) as $a): $sel = ((int)($anio ?? 0) === (int)$a['anio']) ? 'selected' : ''; ?>
        <option value="<?=h($a['anio'])?>" <?=$sel?>><?=h($a['anio'])?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-2">
    <select class="form-select" name="mes">
      <?php $meses = [0=>'Mes: Todos',1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre']; ?>
      <?php foreach ($meses as $k=>$v): $sel = ((int)($mes ?? 0) === (int)$k) ? 'selected' : ''; ?>
        <option value="<?=h($k)?>" <?=$sel?>><?=h($v)?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <button class="btn btn-outline-secondary w-100">Filtrar</button>
  </div>
</form>

<div class="table-responsive"><table class="table table-striped table-sm align-middle">
  <thead class="table-light"><tr>
    <th>ID</th><th>Negocio</th><th>Tipo</th><th>Inmueble</th><th>Dirección</th><th>Piso</th><th>Interior</th><th>Contacto</th><th>Teléfono</th><th>Acciones</th>
  </tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?=h($r['id'])?></td>
      <td><?=h($r['nombre'])?></td>
      <td><?=h($r['tipo'])?></td>
      <td><?=h($r['inmueble_nombre'])?></td>
      <td><?=h($r['calle'])?> C<?=$r['cdra']?> #<?=h($r['num_predio'])?></td>
      <td><?=h($r['piso'])?></td>
      <td><?=h($r['interior'])?></td>
      <td><?=h($r['contacto'])?></td>
      <td><?=h($r['telefono'])?></td>
      <td>
        <a class="btn btn-sm btn-outline-primary" href="?a=negocio_edit&id=<?=$r['id']?>">Editar</a>
        <a class="btn btn-sm btn-outline-danger" href="?a=negocio_delete&id=<?=$r['id']?>" onclick="return confirm('ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¿Eliminar negocio?');">Eliminar</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>


