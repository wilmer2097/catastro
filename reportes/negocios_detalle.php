<?php
$rows = $rows ?? [];
$operadores = $operadores ?? [];
$operador = $operador ?? 0;
$anio = $anio ?? 0;
$mes = $mes ?? 0;
$anios = $anios ?? [];
$meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Reporte de negocios</h2>
  <a class="btn btn-outline-secondary" href="index.php?a=negocios">Volver</a>
</div>

<form class="row g-2 mb-3" method="get" action="index.php">
  <input type="hidden" name="a" value="reporte_negocios">
  <div class="col-12 col-md-4">
    <label class="form-label">Operador</label>
    <select name="operador" class="form-select">
      <option value="0">Todos</option>
      <?php foreach ($operadores as $op): $sel = ((int)$operador === (int)$op['ope_id']) ? 'selected' : ''; ?>
        <option value="<?=h($op['ope_id'])?>" <?=$sel?>><?=h($op['ope_nombre'])?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-4">
    <label class="form-label">Año</label>
    <select name="anio" class="form-select">
      <option value="0">Todos</option>
      <?php foreach ($anios as $a): $sel = ((int)$anio === (int)$a['anio']) ? 'selected' : ''; ?>
        <option value="<?=h($a['anio'])?>" <?=$sel?>><?=h($a['anio'])?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-6 col-md-4">
    <label class="form-label">Mes</label>
    <select name="mes" class="form-select">
      <option value="0">Todos</option>
      <?php foreach ($meses as $k=>$v): $sel = ((int)$mes === (int)$k) ? 'selected' : ''; ?>
        <option value="<?=h($k)?>" <?=$sel?>><?=h($v)?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12">
    <button class="btn btn-primary" type="submit">Aplicar</button>
  </div>
</form>

<p><strong>Total de registros encontrados: <?=count($rows)?></strong></p>

<div class="table-responsive">
  <table class="table table-striped table-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Fecha</th>
        <th>Operador</th>
        <th>Negocio</th>
        <th>Dirección</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($rows): ?>
        <?php foreach ($rows as $r): ?>
          <?php
            $fecha = $r['created_at'] ? date('Y-m-d', strtotime($r['created_at'])) : '';
            $dir = $r['calle'] ?? '';
            if ($dir !== '' && isset($r['num']) && $r['num'] !== '') { $dir .= ' '.$r['num']; }
            if ($dir !== '' && isset($r['cdra']) && $r['cdra'] !== '') { $dir .= ' C'.$r['cdra']; }
          ?>
          <tr>
            <td><?=h($r['id'])?></td>
            <td><?=h($fecha)?></td>
            <td><?=h($r['ope_nombre'] ?? '')?></td>
            <td><?=h($r['nombre'])?><?= $r['tipo'] ? ' ('.h($r['tipo']).')' : '' ?></td>
            <td><?=h($dir)?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center text-muted">Sin datos</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

