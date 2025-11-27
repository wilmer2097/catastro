<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function es_admin() {
    return isset($_SESSION["operador"]["perf_id"]) && $_SESSION["operador"]["perf_id"] == 1;
}

function tiene_permiso($minimo) {
    return isset($_SESSION["operador"]["perf_id"]) && $_SESSION["operador"]["perf_id"] <= $minimo;
}
?>

