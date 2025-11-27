<?php
require_once __DIR__ . '/../auth.php';

// Totales por año
try {
  $porAnio = $pdo->query("SELECT YEAR(created_at) AS anio, COUNT(*) AS total FROM inmuebles WHERE bestado = 1 AND created_at IS NOT NULL GROUP BY YEAR(created_at) ORDER BY anio DESC")->fetchAll();
} catch (Throwable $e) { $porAnio = []; }

// Totales por mes (año-mes)
try {
  $porMes = $pdo->query("SELECT YEAR(created_at) AS anio, MONTH(created_at) AS mes, COUNT(*) AS total FROM inmuebles WHERE bestado = 1 AND created_at IS NOT NULL GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY anio DESC, mes DESC")->fetchAll();
} catch (Throwable $e) { $porMes = []; }

include __DIR__ . '/../partials/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Reporte acumulado de inmuebles</h2>
  <a class="btn btn-outline-secondary" href="../index.php?a=inmuebles">Volver al listado</a>
  
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header bg-white"><strong>Totales por año</strong></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-sm mb-0">
            <thead class="table-light"><tr><th>Año</th><th>Total</th></tr></thead>
            <tbody>
              <?php foreach ($porAnio as $r): ?>
                <tr><td><?=h($r['anio'])?></td><td><?=h($r['total'])?></td></tr>
              <?php endforeach; ?>
              <?php if (!$porAnio): ?><tr><td colspan="2" class="text-muted">Sin datos</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header bg-white"><strong>Totales por mes</strong></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-sm mb-0">
            <thead class="table-light"><tr><th>Año</th><th>Mes</th><th>Total</th></tr></thead>
            <tbody>
              <?php $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre']; ?>
              <?php foreach ($porMes as $r): ?>
                <tr><td><?=h($r['anio'])?></td><td><?=h($meses[(int)$r['mes']] ?? $r['mes'])?></td><td><?=h($r['total'])?></td></tr>
              <?php endforeach; ?>
              <?php if (!$porMes): ?><tr><td colspan="3" class="text-muted">Sin datos</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

