<?php
require_once __DIR__ . '/../auth.php';
if (!es_admin()) { die("Acceso denegado."); }

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nombre = trim($_POST['perf_nombre'] ?? '');
$desc = trim($_POST['perf_descripcion'] ?? '');
$bestado = isset($_POST['bestado']) ? (int)$_POST['bestado'] : 1;

if ($nombre === '') {
    $_SESSION['flash'] = 'Completa nombre de perfil.';
    header('Location: index.php?a=' . ($id ? 'perfil_edit&id='.$id : 'perfil_new'));
    exit();
}

try {
    if ($id === 0) {
        $sql = "INSERT INTO perfil (perf_nombre, perf_descripcion, bestado, fec_cre) VALUES (?,?,?,NOW())";
        $params = [$nombre, $desc, $bestado];
    } else {
        if ($id === 1) { $bestado = 1; }
        $sql = "UPDATE perfil SET perf_nombre = ?, perf_descripcion = ?, bestado = ? WHERE perf_id = ?";
        $params = [$nombre, $desc, $bestado, $id];
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $_SESSION['flash'] = 'Perfil guardado correctamente.';
} catch (Throwable $e) {
    $_SESSION['flash'] = 'Error al guardar el perfil.';
}

header('Location: index.php?a=perfiles');
exit();
