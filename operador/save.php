<?php
require_once __DIR__ . '/../auth.php';
if (!es_admin()) { die("Acceso denegado."); }

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$opeUser     = trim($_POST['ope_user'] ?? '');
$opeNombre   = trim($_POST['ope_nombre'] ?? '');
$opeLogin    = trim($_POST['ope_login'] ?? '');
$opeTelefono = trim($_POST['ope_telefono'] ?? '');
$opePass     = $_POST['ope_pass'] ?? '';
$perfId      = isset($_POST['perf_id']) ? (int)$_POST['perf_id'] : 0;
$bestado     = isset($_POST['bestado']) ? (int)$_POST['bestado'] : 1;

// Guardar datos enviados (para repoblar formulario si falla)
$_SESSION['form_data'] = [
    'ope_id'       => $id,
    'ope_user'     => $opeUser,
    'ope_nombre'   => $opeNombre,
    'ope_login'    => $opeLogin,
    'ope_telefono' => $opeTelefono,
    'perf_id'      => $perfId,
    'bestado'      => $bestado
];

// Validación básica
if ($opeUser === '' || $opeNombre === '' || $opeLogin === '' || !$perfId) {
    $_SESSION['form_error'] = "Completa usuario, nombre, correo y perfil.";
    header("Location: index.php?a=" . ($id ? "operador_edit&id=$id" : "operador_new"));
    exit;
}

try {
    // Validar duplicados
    $dupSql = "SELECT ope_id FROM operador WHERE (ope_user = ? OR ope_login = ?) AND ope_id <> ?";
    $dup = $pdo->prepare($dupSql);
    $dup->execute([$opeUser, $opeLogin, $id]);

    if ($dup->fetch()) {
        $_SESSION['form_error'] = "El usuario o correo ya está en uso.";
        header("Location: index.php?a=" . ($id ? "operador_edit&id=$id" : "operador_new"));
        exit;
    }

    // Crear nuevo operador
    if ($id === 0) {

        if ($opePass === '') {
            $_SESSION['form_error'] = "Ingresa una contraseña.";
            header("Location: index.php?a=operador_new");
            exit;
        }

        $hash = password_hash($opePass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO operador 
        (ope_user, ope_pass, ope_nombre, ope_login, ope_img, perf_id, ope_telefono, bestado, fec_cre)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$params = [
    $opeUser,
    $hash,
    $opeNombre,
    $opeLogin,
    'default-avatar.png', // ahora sí es un parámetro
    $perfId,
    $opeTelefono,
    $bestado
];

$stmt = $pdo->prepare($sql);
$stmt->execute($params);


    } else {
        // Editar existente
        if ($perfId === 1) { $bestado = 1; }

        $fields = [
            "ope_user = ?",
            "ope_nombre = ?",
            "ope_login = ?",
            "ope_telefono = ?",
            "perf_id = ?",
            "bestado = ?"
        ];

        $params = [$opeUser, $opeNombre, $opeLogin, $opeTelefono, $perfId, $bestado];

        if ($opePass !== '') {
            $fields[] = "ope_pass = ?";
            $params[] = password_hash($opePass, PASSWORD_DEFAULT);
        }

        $params[] = $id;

        $sql = "UPDATE operador SET " . implode(", ", $fields) . ", fec_mod = NOW() WHERE ope_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    unset($_SESSION['form_data'], $_SESSION['form_error']);

    $_SESSION['flash'] = "Operador guardado correctamente.";
    header("Location: index.php?a=operadores");
    exit;

} catch (Throwable $e) {

    $_SESSION['form_error'] = "Error al guardar el operador: " . $e->getMessage();
    header("Location: index.php?a=" . ($id ? "operador_edit&id=$id" : "operador_new"));
    exit;
}
