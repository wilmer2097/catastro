<?php
// index.php - simple router
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permisos.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$action = $_GET['a'] ?? 'home';

if (empty($_SESSION['loggedin'])) {
  $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
  header('Location: login.php');
  exit;
}

function view($path, $vars = []) {
  extract($vars);
  global $pdo;    
  include __DIR__ . '/partials/header.php';
  include __DIR__ . '/' . $path;
  include __DIR__ . '/partials/footer.php';
}

// Utilidad: verificar existencia de columna en una tabla (cache simple por request)
function table_has_column(PDO $pdo, string $table, string $column): bool {
  static $cache = [];
  $key = $table;
  if (!isset($cache[$key])) {
    try {
      $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN, 0);
      $cache[$key] = array_flip($cols);
    } catch (Throwable $e) {
      $cache[$key] = [];
    }
  }
  return isset($cache[$key][$column]);
}

if ($action === 'home') {
  $stats = [
    'inmuebles_total' => (int)$pdo->query("SELECT COUNT(*) FROM inmuebles WHERE bestado = 1")->fetchColumn(),
    'inmuebles_multi' => (int)$pdo->query("SELECT COUNT(*) FROM inmuebles WHERE bestado = 1 AND uso_mul = 1")->fetchColumn(),
    'negocios_total'  => (int)$pdo->query("SELECT COUNT(*) FROM negocios WHERE bestado = 1")->fetchColumn(),
    'negocios_imprenta' => (int)$pdo->query("SELECT COUNT(*) FROM negocios WHERE bestado = 1 AND imprenta = 1")->fetchColumn(),
  ];

  $recentInmuebles = $pdo->query("SELECT id, calle, cdra, num, tipo, nombre, created_at FROM inmuebles WHERE bestado = 1 ORDER BY created_at DESC LIMIT 5")->fetchAll();
  $recentNegocios = $pdo->query("SELECT n.id, n.nombre, n.tipo, n.created_at, i.calle, i.cdra, i.num AS num_predio FROM negocios n JOIN inmuebles i ON n.inmueble_id = i.id WHERE n.bestado = 1 ORDER BY n.created_at DESC LIMIT 5")->fetchAll();

  view('home.php', [
    'stats' => $stats,
    'recentInmuebles' => $recentInmuebles,
    'recentNegocios' => $recentNegocios,
  ]);
  exit;
}

/* ======================= INMUEBLES ======================= */
if ($action === 'inmuebles') {
  // List & filters
  $q = $_GET['q'] ?? '';
  $uso = $_GET['uso'] ?? '';
  $operador = isset($_GET['operador']) ? (int)$_GET['operador'] : 0;
  $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : 0;
  $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : 0;

  $sql = "SELECT i.*, c.nombre AS calle_catalogo
          FROM inmuebles i
          LEFT JOIN calles c ON i.calle_id = c.id
          WHERE i.bestado = 1";
  $params = [];
  if ($q) { $sql .= " AND (i.calle LIKE ? OR i.nombre LIKE ? OR i.tipo LIKE ? OR i.num LIKE ?)"; $params = array_fill(0,4,"%$q%"); }
  if ($uso !== '') { $sql .= " AND i.uso_mul = ?"; $params[] = (int)$uso; }
  if ($operador) { $sql .= " AND i.ope_id = ?"; $params[] = $operador; }
  if ($anio)   { $sql .= " AND YEAR(i.created_at) = ?"; $params[] = $anio; }
  if ($mes)    { $sql .= " AND MONTH(i.created_at) = ?"; $params[] = $mes; }
  $sql .= " ORDER BY COALESCE(c.nombre, i.calle), i.cdra, i.num";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll();
  // Catalogos de filtros
  try {
    $operadores = $pdo->query("SELECT ope_id, ope_nombre FROM operador WHERE bestado = 1 ORDER BY ope_nombre")->fetchAll();
  } catch (Throwable $e) { $operadores = []; }
  try {
    $anios = $pdo->query("SELECT DISTINCT YEAR(created_at) AS anio FROM inmuebles WHERE created_at IS NOT NULL ORDER BY anio DESC")->fetchAll();
  } catch (Throwable $e) { $anios = []; }
  view('inmuebles/list.php', [
    'rows'=>$rows,
    'q'=>$q,
    'uso'=>$uso,
    'operador'=>$operador,
    'anio'=>$anio,
    'mes'=>$mes,
    'operadores'=>$operadores,
    'anios'=>$anios,
  ]);
  exit;
}

if ($action === 'inmueble_new') {
  view('inmuebles/form.php', ['row'=>null]);
  exit;
}

if ($action === 'inmueble_save') {
  $redirectUrl = !empty($_POST['id']) ? '?a=inmueble_edit&id='.(int)$_POST['id'] : '?a=inmueble_new';
  $data = [
    'calle_id'=> isset($_POST['calle_id']) ? (int)$_POST['calle_id'] : 0,
    'calle'=>'',
    'cdra'=> (isset($_POST['cdra']) && $_POST['cdra'] !== '') ? (int)$_POST['cdra'] : 0,
    'num'=> trim($_POST['num'] ?? ''),
    'uso_mul'=> isset($_POST['uso_mul']) ? 1 : 0,
    'tipo'=> $_POST['tipo'] ?? '',
    'nombre'=> $_POST['nombre'] ?? '',
    // Nuevos/normativos: numeros de locales, zocalos, pisos
    'nro_locales'=> isset($_POST['nro_locales']) && $_POST['nro_locales'] !== '' ? (int)$_POST['nro_locales'] : null,
    'nro_sotanos'=> isset($_POST['nro_sotanos']) && $_POST['nro_sotanos'] !== '' ? (int)$_POST['nro_sotanos'] : null,
    'nro_pisos'=> isset($_POST['nro_pisos']) && $_POST['nro_pisos'] !== '' ? (int)$_POST['nro_pisos'] : null,
    'dir1'=> '',
    'dir2'=> $_POST['dir2'] ?? '',
    'dir3'=> $_POST['dir3'] ?? '',
    'dir4'=> $_POST['dir4'] ?? '',
  ];

  if (!$data['calle_id'] || !$data['cdra'] || !$data['num'] || !$data['tipo']) {
    $_SESSION['flash'] = "Completa Calle, Cdra, Num y Tipo.";
    header('Location: ' . $redirectUrl); exit;
  }

  try {
    $stc = $pdo->prepare('SELECT nombre, cdra_ini, cdra_fin FROM calles WHERE id = ?');
    $stc->execute([$data['calle_id']]);
    $calle = $stc->fetch();
  } catch (Throwable $e) {
    $calle = false;
  }

  if (!$calle) {
    $_SESSION['flash'] = 'Selecciona una calle valida del catalogo.';
    header('Location: ' . $redirectUrl); exit;
  }

  $ci = (int)$calle['cdra_ini']; $cf = (int)$calle['cdra_fin'];
  if ($data['cdra'] < $ci || $data['cdra'] > $cf) {
    $_SESSION['flash'] = 'La Cdra debe estar entre C'.$ci.' y C'.$cf.' para la calle seleccionada.';
    header('Location: ' . $redirectUrl); exit;
  }

  try {
    $stt = $pdo->prepare('SELECT COUNT(*) FROM tipos_inmueble WHERE nombre = ?');
    $stt->execute([$data['tipo']]);
    if (!(int)$stt->fetchColumn()) {
      $_SESSION['flash'] = 'Selecciona un tipo valido.';
      header('Location: ' . $redirectUrl); exit;
    }
  } catch (Throwable $e) { /* si no existe catalogo, continuar */ }

  $data['calle'] = $calle['nombre'];
  $data['dir1'] = $calle['nombre'];

  if (empty($_POST['id'])) {
    $opeId = (int)($_SESSION['operador_id'] ?? 0);
    // Construir INSERT dinamico segun columnas disponibles
    $cols = ['calle','cdra','num','uso_mul','tipo','nombre','dir1','dir2','dir3','dir4','ope_id'];
    $vals = [$data['calle'],$data['cdra'],$data['num'],$data['uso_mul'],$data['tipo'],$data['nombre'],$data['dir1'],$data['dir2'],$data['dir3'],$data['dir4'],$opeId];
    if ($data['calle_id'] && table_has_column($pdo,'inmuebles','calle_id')) { $cols[]='calle_id'; $vals[]=$data['calle_id']; }
    if ($data['nro_locales'] !== null && table_has_column($pdo,'inmuebles','nro_locales')) { $cols[]='nro_locales'; $vals[]=$data['nro_locales']; }
    if ($data['nro_sotanos'] !== null && table_has_column($pdo,'inmuebles','nro_sotanos')) { $cols[]='nro_sotanos'; $vals[]=$data['nro_sotanos']; }
    if ($data['nro_pisos'] !== null && table_has_column($pdo,'inmuebles','nro_pisos')) { $cols[]='nro_pisos'; $vals[]=$data['nro_pisos']; }
    $placeholders = rtrim(str_repeat('?,', count($cols)),',');
    $colsSql = implode(', ',$cols);
    $stmt = $pdo->prepare("INSERT INTO inmuebles ($colsSql) VALUES ($placeholders)");
    $params = $vals;
    $stmt->execute($params);
    try {
      $newId = (int)$pdo->lastInsertId();
      if ($newId) {
        $pdo->prepare("UPDATE inmuebles SET nro = ? WHERE id = ?")->execute([(string)$newId, $newId]);
      }
    } catch (Throwable $e) { /* nro opcional */ }
  } else {
    $data['ope_mod'] = (int)($_SESSION['operador_id'] ?? 0);
    $data['id'] = (int)$_POST['id'];
    // Construir UPDATE dinamico
    $sets = ['calle=?','cdra=?','num=?','uso_mul=?','tipo=?','nombre=?','dir1=?','dir2=?','dir3=?','dir4=?','ope_mod=?'];
    $vals = [
      $data['calle'],$data['cdra'],$data['num'],$data['uso_mul'],$data['tipo'],$data['nombre'],
      $data['dir1'],$data['dir2'],$data['dir3'],$data['dir4'],$data['ope_mod']
    ];
    if ($data['calle_id'] && table_has_column($pdo,'inmuebles','calle_id')) { $sets[]='calle_id=?'; $vals[]=$data['calle_id']; }
    if ($data['nro_locales'] !== null && table_has_column($pdo,'inmuebles','nro_locales')) { $sets[]='nro_locales=?'; $vals[]=$data['nro_locales']; }
    if ($data['nro_sotanos'] !== null && table_has_column($pdo,'inmuebles','nro_sotanos')) { $sets[]='nro_sotanos=?'; $vals[]=$data['nro_sotanos']; }
    if ($data['nro_pisos'] !== null && table_has_column($pdo,'inmuebles','nro_pisos')) { $sets[]='nro_pisos=?'; $vals[]=$data['nro_pisos']; }
    $setsSql = implode(', ',$sets);
    $stmt = $pdo->prepare("UPDATE inmuebles SET $setsSql WHERE id=?");
    $vals[] = $data['id'];
    $stmt->execute($vals);
    try { $pdo->prepare("UPDATE inmuebles SET fec_mod = NOW() WHERE id=?")->execute([$data['id']]); } catch (Throwable $e) { /* columna opcional */ }
  }
  header('Location: ?a=inmuebles'); exit;
}

if ($action === 'inmueble_edit') {
  $id = (int)($_GET['id'] ?? 0);
  $stmt = $pdo->prepare("SELECT * FROM inmuebles WHERE id=?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if (!$row) { http_response_code(404); echo "No encontrado"; exit; }
  view('inmuebles/form.php', ['row'=>$row]);
  exit;
}

if ($action === 'inmueble_delete') {
  $id = (int)($_GET['id'] ?? 0);
  // Intentar registrar operador/modificacion antes de eliminar
  try {
    $opeMod = (int)($_SESSION['operador_id'] ?? 0);
    $pdo->prepare("UPDATE inmuebles SET ope_mod=?, fec_mod=NOW() WHERE id=?")->execute([$opeMod,$id]);
  } catch (Throwable $e) { /* columnas opcionales */ }
  $opeMod = (int)($_SESSION['operador_id'] ?? 0);
  $pdo->prepare("UPDATE inmuebles SET bestado = 0, ope_mod = ?, fec_mod = NOW() WHERE id = ?")->execute([$opeMod, $id]);
  header('Location: ?a=inmuebles'); exit;
}

if ($action === 'perfil') {
  view('perfil.php', []);
  exit;
}

/* ======================= OPERADORES (admin) ======================= */
if ($action === 'operadores') {
  if (!es_admin()) { die("Acceso denegado."); }
  $sql = "SELECT o.*, p.perf_nombre FROM operador o LEFT JOIN perfil p ON o.perf_id = p.perf_id";
  try { $rows = $pdo->query($sql)->fetchAll(); } catch (Throwable $e) { $rows = []; }
  view('operador/list.php', ['rows'=>$rows]);
  exit;
}

if ($action === 'operador_new') {
  if (!es_admin()) { die("Acceso denegado."); }
  try { $perfiles = $pdo->query("SELECT perf_id, perf_nombre FROM perfil WHERE bestado = 1 ORDER BY perf_id ASC")->fetchAll(); } catch (Throwable $e) { $perfiles = []; }
  view('operador/form.php', ['row'=>null, 'perfiles'=>$perfiles]);
  exit;
}

if ($action === 'operador_edit') {
  if (!es_admin()) { die("Acceso denegado."); }
  $id = (int)($_GET['id'] ?? 0);
  $stmt = $pdo->prepare("SELECT * FROM operador WHERE ope_id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if (!$row) { http_response_code(404); echo "No encontrado"; exit; }
  try { $perfiles = $pdo->query("SELECT perf_id, perf_nombre FROM perfil WHERE bestado = 1 ORDER BY perf_id ASC")->fetchAll(); } catch (Throwable $e) { $perfiles = []; }
  view('operador/form.php', ['row'=>$row, 'perfiles'=>$perfiles]);
  exit;
}

if ($action === 'operador_save') {
  if (!es_admin()) { die("Acceso denegado."); }

  $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $opeUser     = trim($_POST['ope_user'] ?? '');
  $opeNombre   = trim($_POST['ope_nombre'] ?? '');
  $opeLogin    = trim($_POST['ope_login'] ?? '');
  $opeTelefono = trim($_POST['ope_telefono'] ?? '');
  $opePass     = $_POST['ope_pass'] ?? '';
  $perfId      = isset($_POST['perf_id']) ? (int)$_POST['perf_id'] : 0;
  $bestado     = isset($_POST['bestado']) ? (int)$_POST['bestado'] : 1;

  // Validación mínima
  if ($opeUser === '' || $opeNombre === '' || $opeLogin === '' || !$perfId) {
    $_SESSION['flash'] = 'Completa usuario, nombre, correo y perfil.';
    $redir = $id ? 'operador_edit&id='.$id : 'operador_new';
    header('Location: index.php?a='.$redir);
    exit;
  }

  try {

    /* =====================================================
       VALIDAR DUPLICADOS ope_user y ope_login
       ===================================================== */
    $dup = $pdo->prepare(
      "SELECT ope_id FROM operador 
       WHERE (ope_user = ? OR ope_login = ?) AND ope_id <> ?"
    );
    $dup->execute([$opeUser, $opeLogin, $id]);

    if ($dup->fetch()) {
      $_SESSION['flash'] = "El usuario o correo ya está en uso.";
      $redir = $id ? 'operador_edit&id='.$id : 'operador_new';
      header('Location: index.php?a='.$redir);
      exit;
    }

    /* =====================================================
       INSERTAR NUEVO OPERADOR
       ===================================================== */
    if ($id === 0) {

      if ($opePass === '') {
        $_SESSION['flash'] = "Ingresa una contraseña.";
        header("Location: index.php?a=operador_new");
        exit;
      }

      $sql = "INSERT INTO operador
              (ope_user, ope_pass, ope_nombre, ope_login, ope_img,
               perf_id, ope_telefono, bestado, fec_cre)
              VALUES (?,?,?,?, 'default-avatar.png', ?, ?, ?, NOW())";

      $params = [
        $opeUser,
        $opePass,        // <-- contraseña en texto plano
        $opeNombre,
        $opeLogin,
        $perfId,
        $opeTelefono,
        $bestado
      ];

      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);

    } else {

      /* =====================================================
         ACTUALIZACIÓN EXISTENTE
         ===================================================== */

      // Admin principal nunca se desactiva
      if ($perfId === 1) { $bestado = 1; }

      $fields = [
        "ope_user = ?",
        "ope_nombre = ?",
        "ope_login = ?",
        "ope_telefono = ?",
        "perf_id = ?",
        "bestado = ?"
      ];

      $params = [
        $opeUser,
        $opeNombre,
        $opeLogin,
        $opeTelefono,
        $perfId,
        $bestado
      ];

      // Si se envía nueva contraseña → se actualiza
      if ($opePass !== '') {
        $fields[] = "ope_pass = ?";
        $params[] = $opePass;   // <-- contraseña en texto plano
      }

      $params[] = $id;

      $sql = "UPDATE operador SET ".implode(', ', $fields).", fec_mod = NOW()
              WHERE ope_id = ?";

      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
    }

    $_SESSION['flash'] = 'Operador guardado correctamente.';
    header('Location: index.php?a=operadores');
    exit;

  } catch (Throwable $e) {
    $_SESSION['flash'] = 'Error al guardar el operador: ' . $e->getMessage();
    $redir = $id ? 'operador_edit&id='.$id : 'operador_new';
    header('Location: index.php?a='.$redir);
    exit;
  }
}

if ($action === 'operador_toggle') {
  if (!es_admin()) { die("Acceso denegado."); }
  $id = (int)($_GET['id'] ?? 0);
  $estado = isset($_GET['b']) ? (int)$_GET['b'] : 0;
  try {
    // Evitar desactivar admins
    $stmt = $pdo->prepare("SELECT perf_id FROM operador WHERE ope_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && (int)$row['perf_id'] === 1 && $estado == 0) {
      $_SESSION['flash'] = 'No se puede desactivar el administrador.';
    } else {
      $pdo->prepare("UPDATE operador SET bestado = ? WHERE ope_id = ?")->execute([$estado, $id]);
    }
  } catch (Throwable $e) { $_SESSION['flash'] = 'No se pudo cambiar el estado.'; }
  header('Location: index.php?a=operadores'); exit;
}

/* ======================= PERFILES (admin) ======================= */
if ($action === 'perfiles') {
  if (!es_admin()) { die("Acceso denegado."); }
  try { $rows = $pdo->query("SELECT * FROM perfil ORDER BY perf_id ASC")->fetchAll(); } catch (Throwable $e) { $rows = []; }
  view('perfiles/list.php', ['rows'=>$rows]);
  exit;
}

if ($action === 'perfil_new') {
  if (!es_admin()) { die("Acceso denegado."); }
  view('perfiles/form.php', ['row'=>null]);
  exit;
}

if ($action === 'perfil_edit') {
  if (!es_admin()) { die("Acceso denegado."); }
  $id = (int)($_GET['id'] ?? 0);
  $stmt = $pdo->prepare("SELECT * FROM perfil WHERE perf_id = ?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if (!$row) { http_response_code(404); echo "No encontrado"; exit; }
  view('perfiles/form.php', ['row'=>$row]);
  exit;
}

if ($action === 'perfil_save') {
  if (!es_admin()) { die("Acceso denegado."); }
  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $nombre = trim($_POST['perf_nombre'] ?? '');
  $desc = trim($_POST['perf_descripcion'] ?? '');
  $bestado = isset($_POST['bestado']) ? (int)$_POST['bestado'] : 1;
  if ($nombre === '') {
    $_SESSION['flash'] = 'Completa nombre de perfil.';
    $redir = $id ? 'perfil_edit&id='.$id : 'perfil_new';
    header('Location: index.php?a='.$redir); exit;
  }
  if ($id === 0) {
    $sql = "INSERT INTO perfil (perf_nombre, perf_descripcion, bestado, fec_cre) VALUES (?,?,?,NOW())";
    $params = [$nombre, $desc, $bestado];
  } else {
    if ($id === 1) { $bestado = 1; }
    $sql = "UPDATE perfil SET perf_nombre = ?, perf_descripcion = ?, bestado = ? WHERE perf_id = ?";
    $params = [$nombre, $desc, $bestado, $id];
  }
  try {
    $pdo->prepare($sql)->execute($params);
    $_SESSION['flash'] = 'Perfil guardado correctamente.';
  } catch (Throwable $e) {
    $_SESSION['flash'] = 'Error al guardar el perfil.';
  }
  header('Location: index.php?a=perfiles'); exit;
}

/* ======================= CALLES ======================= */
if ($action === 'calles') {
  if (!es_admin()) { die("Acceso denegado."); }
  try {
    $sql = "SELECT id, nombre, cdra_ini, cdra_fin";
    $sql .= table_has_column($pdo, 'calles', 'bestado') ? " , bestado FROM calles WHERE bestado = 1" : " FROM calles";
    $sql .= " ORDER BY nombre";
    $rows = $pdo->query($sql)->fetchAll();
  } catch (Throwable $e) { $rows = []; }
  view('calles/list.php', ['rows'=>$rows]);
  exit;
}

if ($action === 'calle_new') {
  if (!es_admin()) { die("Acceso denegado."); }
  view('calles/form.php', ['row'=>null]);
  exit;
}

if ($action === 'calle_edit') {
  if (!es_admin()) { die("Acceso denegado."); }
  $id = (int)($_GET['id'] ?? 0);
  $stmt = $pdo->prepare("SELECT * FROM calles WHERE id=?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if (!$row) { http_response_code(404); echo "No encontrado"; exit; }
  view('calles/form.php', ['row'=>$row]);
  exit;
}

if ($action === 'calle_save') {
  if (!es_admin()) { die("Acceso denegado."); }
  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $nombre = trim((string)($_POST['nombre'] ?? ''));
  $cdraIni = isset($_POST['cdra_ini']) ? (int)$_POST['cdra_ini'] : 0;
  $cdraFin = isset($_POST['cdra_fin']) ? (int)$_POST['cdra_fin'] : 0;
  if ($nombre === '' || !$cdraIni || !$cdraFin) {
    $_SESSION['flash'] = 'Completa nombre y cuadras.';
    header('Location: ' . ($id ? '?a=calle_edit&id='.$id : '?a=calle_new')); exit;
  }
  if ($cdraIni > $cdraFin) {
    $_SESSION['flash'] = 'La cuadra inicial debe ser menor o igual a la final.';
    header('Location: ' . ($id ? '?a=calle_edit&id='.$id : '?a=calle_new')); exit;
  }
  if ($id === 0) {
    $cols = ['nombre','cdra_ini','cdra_fin'];
    $vals = [$nombre, $cdraIni, $cdraFin];
    if (table_has_column($pdo,'calles','bestado')) { $cols[]='bestado'; $vals[] = 1; }
    $placeholders = rtrim(str_repeat('?,', count($cols)), ',');
    $pdo->prepare("INSERT INTO calles (".implode(',', $cols).") VALUES ($placeholders)")->execute($vals);
  } else {
    $sets = ['nombre=?','cdra_ini=?','cdra_fin=?'];
    $vals = [$nombre,$cdraIni,$cdraFin];
    $sql = "UPDATE calles SET ".implode(',', $sets)." WHERE id=?";
    $vals[] = $id;
    $pdo->prepare($sql)->execute($vals);
  }
  header('Location: ?a=calles'); exit;
}

if ($action === 'calle_delete') {
  if (!es_admin()) { die("Acceso denegado."); }
  $id = (int)($_GET['id'] ?? 0);
  if (table_has_column($pdo,'calles','bestado')) {
    $pdo->prepare("UPDATE calles SET bestado = 0 WHERE id = ?")->execute([$id]);
  } else {
    $pdo->prepare("DELETE FROM calles WHERE id = ?")->execute([$id]);
  }
  header('Location: ?a=calles'); exit;
}

/* ======================= REPORTES ======================= */
if ($action === 'reporte_inmuebles') {
  if (!es_admin()) { die("Acceso denegado."); }
  $operador = isset($_GET['operador']) ? (int)$_GET['operador'] : 0;
  $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : 0;
  $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : 0;

  $conditions = ["i.bestado = 1", "i.created_at IS NOT NULL"];
  $params = [];
  if ($operador) { $conditions[] = "i.ope_id = ?"; $params[] = $operador; }
  if ($anio) { $conditions[] = "YEAR(i.created_at) = ?"; $params[] = $anio; }
  if ($mes) { $conditions[] = "MONTH(i.created_at) = ?"; $params[] = $mes; }

  $sql = "SELECT i.id, i.calle, i.cdra, i.num, i.nombre, i.created_at, i.ope_id, c.nombre AS calle_catalogo, o.ope_nombre
          FROM inmuebles i
          LEFT JOIN calles c ON i.calle_id = c.id
          LEFT JOIN operador o ON i.ope_id = o.ope_id
          WHERE " . implode(' AND ', $conditions) . "
          ORDER BY i.created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll();

  try {
    $operadores = $pdo->query("SELECT ope_id, ope_nombre FROM operador WHERE bestado = 1 ORDER BY ope_nombre")->fetchAll();
  } catch (Throwable $e) { $operadores = []; }
  try {
    $anios = $pdo->query("SELECT DISTINCT YEAR(created_at) AS anio FROM inmuebles WHERE created_at IS NOT NULL ORDER BY anio DESC")->fetchAll();
  } catch (Throwable $e) { $anios = []; }

  view('reportes/inmuebles_detalle.php', [
    'rows' => $rows,
    'operadores' => $operadores,
    'operador' => $operador,
    'anio' => $anio,
    'mes' => $mes,
    'anios' => $anios,
  ]);
  exit;
}

if ($action === 'reporte_negocios') {
  if (!es_admin()) { die("Acceso denegado."); }
  $operador = isset($_GET['operador']) ? (int)$_GET['operador'] : 0;
  $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : 0;
  $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : 0;

  $conditions = ["n.bestado = 1", "n.created_at IS NOT NULL"];
  $params = [];
  if ($operador) { $conditions[] = "n.ope_id = ?"; $params[] = $operador; }
  if ($anio) { $conditions[] = "YEAR(n.created_at) = ?"; $params[] = $anio; }
  if ($mes) { $conditions[] = "MONTH(n.created_at) = ?"; $params[] = $mes; }

  $sql = "SELECT n.id, n.nombre, n.tipo, n.created_at, n.ope_id, i.calle, i.cdra, i.num, i.nombre AS inmueble_nombre, o.ope_nombre
          FROM negocios n
          JOIN inmuebles i ON n.inmueble_id = i.id
          LEFT JOIN operador o ON n.ope_id = o.ope_id
          WHERE " . implode(' AND ', $conditions) . "
          ORDER BY n.created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll();

  try {
    $operadores = $pdo->query("SELECT ope_id, ope_nombre FROM operador WHERE bestado = 1 ORDER BY ope_nombre")->fetchAll();
  } catch (Throwable $e) { $operadores = []; }
  try {
    $anios = $pdo->query("SELECT DISTINCT YEAR(created_at) AS anio FROM negocios WHERE created_at IS NOT NULL ORDER BY anio DESC")->fetchAll();
  } catch (Throwable $e) { $anios = []; }

  view('reportes/negocios_detalle.php', [
    'rows' => $rows,
    'operadores' => $operadores,
    'operador' => $operador,
    'anio' => $anio,
    'mes' => $mes,
    'anios' => $anios,
  ]);
  exit;
}

/* ======================= NEGOCIOS ======================= */
if ($action === 'negocios') {
  $inmueble_id = (int)($_GET['inmueble_id'] ?? 0);
  $where = "n.bestado = 1"; $params=[];
  if ($inmueble_id) { $where .= " AND n.inmueble_id=?"; $params[]=$inmueble_id; }
  $q = $_GET['q'] ?? '';
  if ($q) { $where .= " AND (n.nombre LIKE ? OR n.tipo LIKE ? OR n.productos LIKE ? OR i.nombre LIKE ? OR i.calle LIKE ?)";
           array_push($params, "%$q%","%$q%","%$q%","%$q%","%$q%"); }
  $operador = isset($_GET['operador']) ? (int)$_GET['operador'] : 0;
  $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : 0;
  $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : 0;
  if ($operador) { $where .= " AND n.ope_id = ?"; $params[] = $operador; }
  if ($anio) { $where .= " AND YEAR(n.created_at) = ?"; $params[] = $anio; }
  if ($mes) { $where .= " AND MONTH(n.created_at) = ?"; $params[] = $mes; }
  $sql = "SELECT n.*, i.calle, i.cdra, i.num AS num_predio, i.nombre AS inmueble_nombre
          FROM negocios n JOIN inmuebles i ON n.inmueble_id = i.id
          WHERE $where ORDER BY i.calle, i.cdra, n.nombre";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll();
  // Catalogos de filtros
  try { $operadores = $pdo->query("SELECT ope_id, ope_nombre FROM operador WHERE bestado = 1 ORDER BY ope_nombre")->fetchAll(); } catch (Throwable $e) { $operadores = []; }
  try { $anios = $pdo->query("SELECT DISTINCT YEAR(created_at) AS anio FROM negocios WHERE created_at IS NOT NULL ORDER BY anio DESC")->fetchAll(); } catch (Throwable $e) { $anios = []; }
  try {
    $inmueblesCatalogo = $pdo->query("SELECT id, nombre, calle, cdra, num FROM inmuebles WHERE bestado = 1 ORDER BY COALESCE(NULLIF(nombre,''), calle), cdra, num")->fetchAll();
  } catch (Throwable $e) { $inmueblesCatalogo = []; }
  view('negocios/list.php', [
    'rows'=>$rows,
    'inmueble_id'=>$inmueble_id,
    'q'=>$q,
    'operador'=>$operador,
    'anio'=>$anio,
    'mes'=>$mes,
    'operadores'=>$operadores,
    'anios'=>$anios,
    'inmuebles'=>$inmueblesCatalogo,
  ]);
  exit;
}

if ($action === 'negocio_new') {
  $inmueble_id = (int)($_GET['inmueble_id'] ?? 0);
  view('negocios/form.php', ['row'=>['inmueble_id'=>$inmueble_id]]);
  exit;
}

if ($action === 'negocio_save') {
  $data = [
    'inmueble_id'=>(int)($_POST['inmueble_id'] ?? 0),
    'piso'=> $_POST['piso'] ?? '',
    'interior'=> $_POST['interior'] ?? '',
    'tipo'=> $_POST['tipo'] ?? '',
    'nombre'=> $_POST['nombre'] ?? '',
    'productos'=> $_POST['productos'] ?? '',
    'r1'=> $_POST['r1'] ?? '',
    'r2'=> $_POST['r2'] ?? '',
    'r3'=> $_POST['r3'] ?? '',
    'r4'=> $_POST['r4'] ?? '',
    'r5'=> $_POST['r5'] ?? '',
    'r6'=> $_POST['r6'] ?? '',
    'contacto'=> $_POST['contacto'] ?? '',
    'cargo'=> $_POST['cargo'] ?? '',
    'telefono'=> $_POST['telefono'] ?? '',
    'ruc'=> $_POST['ruc'] ?? '',
    'tam_m2'=> $_POST['tam_m2'] ?? null,
    'imprenta'=> isset($_POST['imprenta']) ? 1 : 0,
  ];
  // Normalizar metros: enviar NULL si viene vacío para no romper DECIMAL
  if ($data['tam_m2'] === '' || $data['tam_m2'] === null) {
    $data['tam_m2'] = null;
  } else {
    $data['tam_m2'] = (float)$data['tam_m2'];
  }
  if (!$data['inmueble_id'] || !$data['nombre']) {
    $_SESSION['flash'] = "Selecciona un inmueble y coloca nombre del negocio.";
    header('Location: ?a=negocio_new'); exit;
  }
  if (empty($_POST['id'])) {
    $opeId = (int)($_SESSION['operador_id'] ?? 0);
    $stmt = $pdo->prepare("INSERT INTO negocios (inmueble_id,piso,interior,tipo,nombre,productos,r1,r2,r3,r4,r5,r6,contacto,cargo,telefono,ruc,tam_m2,imprenta,ope_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $params = [
      $data['inmueble_id'],$data['piso'],$data['interior'],$data['tipo'],$data['nombre'],$data['productos'],
      $data['r1'],$data['r2'],$data['r3'],$data['r4'],$data['r5'],$data['r6'],
      $data['contacto'],$data['cargo'],$data['telefono'],$data['ruc'],$data['tam_m2'],$data['imprenta'],$opeId
    ];
    $stmt->execute($params);
  } else {
    $data['id'] = (int)$_POST['id'];
    $opeMod = (int)($_SESSION['operador_id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE negocios SET inmueble_id=?, piso=?, interior=?, tipo=?, nombre=?, productos=?, r1=?, r2=?, r3=?, r4=?, r5=?, r6=?, contacto=?, cargo=?, telefono=?, ruc=?, tam_m2=?, imprenta=?, ope_mod=? WHERE id=?");
    $params = [
      $data['inmueble_id'],$data['piso'],$data['interior'],$data['tipo'],$data['nombre'],$data['productos'],
      $data['r1'],$data['r2'],$data['r3'],$data['r4'],$data['r5'],$data['r6'],
      $data['contacto'],$data['cargo'],$data['telefono'],$data['ruc'],$data['tam_m2'],$data['imprenta'],$opeMod,$data['id']
    ];
    $stmt->execute($params);
  }
  header('Location: ?a=negocios&inmueble_id=' . $data['inmueble_id']); exit;
}

if ($action === 'negocio_edit') {
  $id = (int)($_GET['id'] ?? 0);
  $stmt = $pdo->prepare("SELECT * FROM negocios WHERE id=?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if (!$row) { http_response_code(404); echo "No encontrado"; exit; }
  view('negocios/form.php', ['row'=>$row]);
  exit;
}

if ($action === 'negocio_delete') {
  $id = (int)($_GET['id'] ?? 0);
  // Keep inmueble_id for redirect
  $stmt = $pdo->prepare("SELECT inmueble_id FROM negocios WHERE id=?");
  $stmt->execute([$id]);
  $r = $stmt->fetch();
  // Baja logica en lugar de DELETE
  $opeMod = (int)($_SESSION['operador_id'] ?? 0);
  $pdo->prepare("UPDATE negocios SET bestado = 0, ope_mod = ?, fec_mod = NOW() WHERE id = ?")->execute([$opeMod, $id]);
  $iid = $r ? (int)$r['inmueble_id'] : 0;
  header('Location: ?a=negocios&inmueble_id=' . $iid); exit;
}

http_response_code(404);
echo "Ruta no encontrada";
