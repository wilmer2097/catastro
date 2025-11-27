<?php $rows = $rows ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Calles</h2>
  <a class="btn btn-primary" href="?a=calle_new">Nueva calle</a>
</div>
<div class="table-responsive">
  <table class="table table-striped table-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Cdra inicial</th>
        <th>Cdra final</th>
        <th class="text-nowrap">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?=h($r['id'])?></td>
          <td><?=h($r['nombre'])?></td>
          <td><?=h($r['cdra_ini'])?></td>
          <td><?=h($r['cdra_fin'])?></td>
          <td class="text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="?a=calle_edit&id=<?= (int)$r['id'] ?>">Editar</a>
            <a class="btn btn-sm btn-outline-danger" href="?a=calle_delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('¿Eliminar calle?');">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

