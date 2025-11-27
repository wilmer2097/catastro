<?php $rows = $rows ?? []; $q = $q ?? ''; $uso = $uso ?? ''; $operador = $operador ?? 0; $anio = $anio ?? 0; $mes = $mes ?? 0; $operadores = $operadores ?? []; $anios = $anios ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Inmuebles</h2>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="reportes/inmuebles_acumulado.php" target="_blank">Reporte acumulado</a>
    <a class="btn btn-primary" href="?a=inmueble_new">Nuevo inmueble</a>
  </div>
  </div>

<form class="row g-2 mb-3">
  <input type="hidden" name="a" value="inmuebles">
  <div class="col-12 col-md-5">
    <input class="form-control" name="q" placeholder="Buscar por calle/nombre/tipo/num" value="<?=h($q)?>">
  </div>
  <div class="col-6 col-md-3">
    <select class="form-select" name="uso">
      <option value="">Uso múltiple: Todos</option>
      <option value="1" <?= $uso==='1'?'selected':''; ?>>Sí</option>
      <option value="0" <?= $uso==='0'?'selected':''; ?>>No</option>
    </select>
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
  <div class="col-6 col-md-2">
    <button class="btn btn-outline-secondary w-100" type="submit">Filtrar</button>
  </div>
</form>

<div class="table-responsive">
<table class="table table-striped table-sm align-middle">
  <thead class="table-light">
    <tr>
      <th class="text-nowrap">ID</th>
      <th>Calle</th>
      <th class="d-none d-sm-table-cell">Cdra</th>
      <th class="text-nowrap">Número</th>
      <th class="d-none d-md-table-cell">Tipo</th>
      <th class="d-none d-lg-table-cell">Nombre del edificio</th>
      <th class="d-none d-lg-table-cell">Nro. Locales</th>
      <th class="d-none d-lg-table-cell">Nro. Zócalos</th>
      <th class="d-none d-lg-table-cell">Nro. Pisos</th>
      <th class="d-none d-sm-table-cell">Uso múltiple</th>
      <th class="text-nowrap">Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?=h($r['id'])?></td>
        <td><?=h(($r['calle_catalogo'] ?? '') !== '' ? $r['calle_catalogo'] : ($r['calle'] ?? ''))?></td>
        <td class="d-none d-sm-table-cell"><?=h($r['cdra'])?></td>
        <td><?=h($r['num'])?></td>
        <td class="d-none d-md-table-cell"><?=h($r['tipo'])?></td>
        <td class="d-none d-lg-table-cell"><?=h($r['nombre'])?></td>
        <td class="d-none d-lg-table-cell"><?=h($r['nlocals'] ?? '')?></td>
        <td class="d-none d-lg-table-cell"><?=h($r['nro_zocalos'] ?? '')?></td>
        <td class="d-none d-lg-table-cell"><?=h($r['nro_pisos'] ?? '')?></td>
        <td class="d-none d-sm-table-cell"><?= !empty($r['uso_mul']) ? 'Sí' : 'No' ?></td>
        <td class="text-nowrap">
          <a class="btn btn-sm btn-outline-primary" href="?a=inmueble_edit&id=<?=$r['id']?>">Editar</a>
          <a class="btn btn-sm btn-outline-danger" href="?a=inmueble_delete&id=<?=$r['id']?>" onclick="return confirm('¿Eliminar inmueble? Se marcará como inactivo.');">Eliminar</a>
          <?php if (!empty($r['uso_mul'])): ?>
            <a class="btn btn-sm btn-success" href="?a=negocios&inmueble_id=<?=$r['id']?>">Ver negocios</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>


