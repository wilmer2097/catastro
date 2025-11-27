<?php
$rows = $rows ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Perfiles</h2>
  <a class="btn btn-primary" href="index.php?a=perfil_new">Nuevo perfil</a>
</div>
<div class="table-responsive">
  <table class="table table-striped table-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Estado</th>
        <th class="text-nowrap">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($rows): ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=h($r['perf_id'])?></td>
            <td><?=h($r['perf_nombre'])?></td>
            <td><?=h($r['perf_descripcion'])?></td>
            <td><?= !empty($r['bestado']) ? 'Activo' : 'Inactivo' ?></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="index.php?a=perfil_edit&id=<?= (int)$r['perf_id'] ?>">Editar</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-center text-muted">Sin datos</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
