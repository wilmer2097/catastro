<?php /* partials/header.php */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catastro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="?a=home">Catastro</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Alternar navegación">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <div class="navbar-nav me-auto">
        <a class="nav-link" href="?a=inmuebles">Inmuebles</a>
        <a class="nav-link" href="?a=negocios">Negocios</a>
      </div>
      <div class="navbar-nav ms-auto align-items-lg-center">
        <?php if (!empty($_SESSION['loggedin'])): ?>
          <span class="navbar-text text-white-50 me-lg-3">
            <?=h($_SESSION['operador_nombre'] ?? 'Usuario')?>
          </span>
          <a class="nav-link" href="perfil.php">Editar perfil</a>
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
