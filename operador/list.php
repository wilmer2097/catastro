<?php
$rows = $rows ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Operadores</h2>
  <a class="btn btn-primary" href="index.php?a=operador_new">Nuevo operador</a>
</div>
<div class="table-responsive">
  <table class="table table-striped table-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Nombre</th>
        <th>Correo</th>
        <th>TelÃ©fono</th>
        <th>Perfil</th>
        <th>Estado</th>
        <th class="text-nowrap">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($rows): ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?=h($r['ope_id'])?></td>
            <td><?=h($r['ope_user'])?></td>
            <td><?=h($r['ope_nombre'])?></td>
            <td><?=h($r['ope_login'])?></td>
            <td><?=h($r['ope_telefono'] ?? '')?></td>
            <td><?=h($r['perf_nombre'] ?? '')?></td>
            <td><?= !empty($r['bestado']) ? 'Activo' : 'Inactivo' ?></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="index.php?a=operador_edit&id=<?= (int)$r['ope_id'] ?>">Editar</a>
              <?php if ((int)$r['perf_id'] !== 1): ?>
                <?php if (!empty($r['bestado'])): ?>
                  <a class="btn btn-sm btn-outline-danger" href="index.php?a=operador_toggle&id=<?= (int)$r['ope_id'] ?>&b=0" onclick="return confirm('Â¿Desactivar operador?');">Desactivar</a>
                <?php else: ?>
                  <a class="btn btn-sm btn-outline-success" href="index.php?a=operador_toggle&id=<?= (int)$r['ope_id'] ?>&b=1">Activar</a>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" class="text-center text-muted">Sin datos</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
