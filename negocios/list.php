<?php $rows = $rows ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Negocios (Formato 002)</h2>
  <a class="btn btn-primary" href="?a=negocio_new<?= $inmueble_id?('&inmueble_id='.$inmueble_id):''; ?>">Nuevo negocio</a>
</div>

<form class="row g-2 mb-3">
  <input type="hidden" name="a" value="negocios">
  <?php if (!$inmueble_id): ?>
  <div class="col-md-3">
    <input class="form-control" name="q" placeholder="Buscar por negocio, producto o calle" value="<?=h($q)?>">
  </div>
  <?php endif; ?>
  <div class="col-md-2">
    <button class="btn btn-outline-secondary w-100">Filtrar</button>
  </div>
</form>

<table class="table table-striped table-sm">
  <thead><tr>
    <th>ID</th><th>Negocio</th><th>Tipo</th><th>Inmueble</th><th>Dirección</th><th>Piso</th><th>Contacto</th><th>Teléfono</th><th>Acciones</th>
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
      <td><?=h($r['contacto'])?></td>
      <td><?=h($r['telefono'])?></td>
      <td>
        <a class="btn btn-sm btn-outline-primary" href="?a=negocio_edit&id=<?=$r['id']?>">Editar</a>
        <a class="btn btn-sm btn-outline-danger" href="?a=negocio_delete&id=<?=$r['id']?>" onclick="return confirm('¿Eliminar negocio?');">Eliminar</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
