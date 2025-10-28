<?php
$stats = $stats ?? [
  'inmuebles_total' => 0,
  'inmuebles_multi' => 0,
  'negocios_total' => 0,
  'negocios_imprenta' => 0,
];
$recentInmuebles = $recentInmuebles ?? [];
$recentNegocios = $recentNegocios ?? [];
?>

<div class="py-4">
  <div class="bg-light border rounded-3 p-4 mb-4">
    <h1 class="mb-3">Catastro de Locales Comerciales</h1>
    <p class="mb-0">Administra la información de predios (Formato 001) y negocios (Formato 002) desde un solo panel. Utiliza los accesos rápidos para registrar nuevos datos o revisar los listados existentes.</p>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card h-100 border-primary">
        <div class="card-body">
          <h6 class="card-subtitle text-muted">Inmuebles registrados</h6>
          <p class="display-6 mb-1"><?=number_format($stats['inmuebles_total'])?></p>
          <small class="text-muted">Incluye viviendas, galerías y otros locales.</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0">
          <a class="btn btn-primary w-100" href="?a=inmueble_new">Añadir inmueble</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="card-subtitle text-muted">Uso múltiple</h6>
          <p class="display-6 mb-1"><?=number_format($stats['inmuebles_multi'])?></p>
          <small class="text-muted">Predios con más de un negocio interno.</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0">
          <a class="btn btn-outline-secondary w-100" href="?a=inmuebles&uso=1">Ver listado</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card h-100 border-success">
        <div class="card-body">
          <h6 class="card-subtitle text-muted">Negocios registrados</h6>
          <p class="display-6 mb-1"><?=number_format($stats['negocios_total'])?></p>
          <small class="text-muted">Comercios vinculados a los inmuebles.</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0">
          <a class="btn btn-success w-100" href="?a=negocio_new">Registrar negocio</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="card-subtitle text-muted">Negocios con imprenta</h6>
          <p class="display-6 mb-1"><?=number_format($stats['negocios_imprenta'])?></p>
          <small class="text-muted">Comercios que emiten comprobantes.</small>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0">
          <a class="btn btn-outline-secondary w-100" href="?a=negocios&q=imprenta">Buscar imprentas</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header bg-white">
          <strong>Últimos inmuebles registrados</strong>
        </div>
        <div class="card-body p-0">
          <?php if ($recentInmuebles): ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recentInmuebles as $item): ?>
                <?php $fecha = !empty($item['created_at']) ? date('d/m/Y', strtotime($item['created_at'])) : 'Sin fecha'; ?>
                <a class="list-group-item list-group-item-action" href="?a=inmueble_edit&id=<?=$item['id']?>">
                  <div class="d-flex justify-content-between">
                    <div>
                      <div class="fw-semibold"><?=h($item['calle'])?> C<?=h($item['cdra'])?> #<?=h($item['num'])?></div>
                      <div class="small text-muted"><?=h($item['tipo'])?> <?= $item['nombre'] ? '· '.h($item['nombre']) : '' ?></div>
                    </div>
                    <small class="text-muted"><?=$fecha?></small>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="p-3 text-muted mb-0">Aún no se han registrado inmuebles.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header bg-white">
          <strong>Últimos negocios registrados</strong>
        </div>
        <div class="card-body p-0">
          <?php if ($recentNegocios): ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recentNegocios as $item): ?>
                <?php $fecha = !empty($item['created_at']) ? date('d/m/Y', strtotime($item['created_at'])) : 'Sin fecha'; ?>
                <a class="list-group-item list-group-item-action" href="?a=negocio_edit&id=<?=$item['id']?>">
                  <div class="d-flex justify-content-between">
                    <div>
                      <div class="fw-semibold"><?=h($item['nombre'])?></div>
                      <div class="small text-muted"><?=h($item['tipo'])?> · <?=h($item['calle'])?> C<?=h($item['cdra'])?> #<?=h($item['num_predio'])?></div>
                    </div>
                    <small class="text-muted"><?=$fecha?></small>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="p-3 text-muted mb-0">Aún no se han registrado negocios.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
