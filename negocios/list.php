<?php
$rows = $rows ?? [];
$operador = $operador ?? 0;
$anio = $anio ?? 0;
$mes = $mes ?? 0;
$inmueble_id = $inmueble_id ?? 0;
$operadores = $operadores ?? [];
$anios = $anios ?? [];
$inmuebles = $inmuebles ?? [];

$formatInmueble = function(array $r): string {
  $base = trim($r['nombre'] ?? '');
  $dir = trim(($r['calle'] ?? '').' C'.($r['cdra'] ?? '').' #'.($r['num'] ?? ''));
  return $base !== '' ? ($base.' — '.$dir) : $dir;
};
$selectedInmuebleLabel = '';
if (!empty($inmueble_id)) {
  foreach ($inmuebles as $im) {
    if ((int)$im['id'] === (int)$inmueble_id) {
      $selectedInmuebleLabel = $formatInmueble($im);
      break;
    }
  }
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Negocios</h2>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="index.php?a=reporte_negocios">Reporte acumulado</a>
    <a class="btn btn-primary" href="?a=negocio_new<?= $inmueble_id?('&inmueble_id='.$inmueble_id):''; ?>">Nuevo negocio</a>
  </div>
</div>

<form class="row g-2 mb-3">
  <input type="hidden" name="a" value="negocios">
  <div class="col-md-3">
    <input class="form-control" name="q" placeholder="Buscar por negocio, producto o calle" value="<?=h($q)?>">
  </div>
  <div class="col-12 col-md-4">
    <div class="dropdown w-100" data-search-dropdown>
      <button class="form-select text-start d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="inmuebleDropdownBtn">
        <span class="text-truncate" id="inmuebleDropdownLabel"><?=h($selectedInmuebleLabel ?: 'Todos los inmuebles')?></span>
        <span class="ms-2 text-muted flex-shrink-0">▾</span>
      </button>
      <div class="dropdown-menu p-2 w-100 shadow-sm">
        <input type="text" class="form-control form-control-sm mb-2" placeholder="Buscar inmueble..." autocomplete="off" data-search-input>
        <div class="list-group list-group-flush dropdown-search-list" data-search-list>
          <button type="button" class="list-group-item list-group-item-action small<?= $inmueble_id ? '' : ' active'; ?>" data-id="" data-label="Todos los inmuebles">Todos los inmuebles</button>
          <?php foreach ($inmuebles as $im): ?>
            <?php $label = $formatInmueble($im); ?>
            <?php $active = ((int)$im['id'] === (int)$inmueble_id) ? ' active' : ''; ?>
            <button type="button" class="list-group-item list-group-item-action small<?=$active?>" data-id="<?=h($im['id'])?>" data-label="<?=h($label)?>"><?=h($label)?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <input type="hidden" name="inmueble_id" id="inmuebleIdInput" value="<?=h($inmueble_id)?>">
    </div>
  </div>
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
        <a class="btn btn-sm btn-outline-danger" href="?a=negocio_delete&id=<?=$r['id']?>">Eliminar</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<script src="assets/js/negocios_list.js"></script>
