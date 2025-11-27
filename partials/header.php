<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catastro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/app.css" rel="stylesheet">
  <style>
    body { margin: 0; padding: 0; }
    nav, .navbar { margin: 0; padding: 0; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php?a=home">Catastro</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Alternar navegaciÃ³n">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <div class="navbar-nav me-auto">
        <a class="nav-link" href="index.php?a=inmuebles">Inmuebles</a>
        <a class="nav-link" href="index.php?a=negocios">Negocios</a>
        <?php if (function_exists('es_admin') && es_admin()): ?>
          <a class="nav-link" href="index.php?a=calles">Calles</a>
          <a class="nav-link" href="index.php?a=operadores">Operadores</a>
          <a class="nav-link" href="index.php?a=perfiles">Perfiles</a>
        <?php endif; ?>
      </div>
      <div class="navbar-nav ms-auto align-items-lg-center">
        <?php if (!empty($_SESSION['loggedin'])): ?>
          <span class="navbar-text text-white-50 me-lg-3">
            <?=h($_SESSION['operador_nombre'] ?? 'Usuario')?>
          </span>
          <a class="nav-link" href="index.php?a=perfil">Editar perfil</a>
          <a class="nav-link" href="logout.php">Cerrar sesión</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<div class="container py-3">
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <?=h($_SESSION['flash'])?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>
