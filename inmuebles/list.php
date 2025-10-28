<?php $rows = $rows ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Inmuebles (Formato 001)</h2>
  <a class="btn btn-primary" href="?a=inmueble_new">Nuevo inmueble</a>
</div>

<form class="row g-2 mb-3">
  <input type="hidden" name="a" value="inmuebles">
  <div class="col-md-5">
    <input class="form-control" name="q" placeholder="Buscar por calle/nombre/tipo/num" value="<?=h($q)?>">
  </div>
  <div class="col-md-3">
    <select class="form-select" name="uso">
      <option value="">Uso múltiple: Todos</option>
      <option value="1" <?= $uso==='1'?'selected':''; ?>>Sí</option>
      <option value="0" <?= $uso==='0'?'selected':''; ?>>No</option>
    </select>
  </div>
  <div class="col-md-2">
    <button class="btn btn-outline-secondary w-100">Filtrar</button>
  </div>
</form>

<table class="table table-striped table-sm">
  <thead><tr>
    <th>ID</th><th>Calle</th><th>Cdra</th><th>Número</th><th>Tipo</th><th>Nombre</th><th>Uso Múltiple</th><th>Niveles</th><th>Acciones</th>
  </tr></thead>
  <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?=h($r['id'])?></td>
        <td><?=h($r['calle'])?></td>
        <td><?=h($r['cdra'])?></td>
        <td><?=h($r['num'])?></td>
        <td><?=h($r['tipo'])?></td>
        <td><?=h($r['nombre'])?></td>
        <td><?= $r['uso_mul'] ? 'Sí' : 'No' ?></td>
        <td><?= $r['nivel_z']?'Z':'' ?><?= ($r['nivel_z']&&$r['nivel_a'])?'/':'' ?><?= $r['nivel_a']?'A':'' ?></td>
        <td>
          <a class="btn btn-sm btn-outline-primary" href="?a=inmueble_edit&id=<?=$r['id']?>">Editar</a>
          <a class="btn btn-sm btn-outline-danger" href="?a=inmueble_delete&id=<?=$r['id']?>" onclick="return confirm('¿Eliminar inmueble? Se eliminarán sus negocios.');">Eliminar</a>
          <?php if ($r['uso_mul']): ?>
            <a class="btn btn-sm btn-success" href="?a=negocios&inmueble_id=<?=$r['id']?>">Ver negocios</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
