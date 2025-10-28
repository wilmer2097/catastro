<?php
// index.php - simple router
require __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$action = $_GET['a'] ?? 'home';

function view($path, $vars = []) {
  extract($vars);
  global $pdo;    
  include __DIR__ . '/partials/header.php';
  include __DIR__ . '/' . $path;
  include __DIR__ . '/partials/footer.php';
}

if ($action === 'home') {
  $stats = [
    'inmuebles_total' => (int)$pdo->query("SELECT COUNT(*) FROM inmuebles")->fetchColumn(),
    'inmuebles_multi' => (int)$pdo->query("SELECT COUNT(*) FROM inmuebles WHERE uso_mul = 1")->fetchColumn(),
    'negocios_total'  => (int)$pdo->query("SELECT COUNT(*) FROM negocios")->fetchColumn(),
    'negocios_imprenta' => (int)$pdo->query("SELECT COUNT(*) FROM negocios WHERE imprenta = 1")->fetchColumn(),
  ];

  $recentInmuebles = $pdo->query("SELECT id, calle, cdra, num, tipo, nombre, created_at FROM inmuebles ORDER BY created_at DESC LIMIT 5")->fetchAll();
  $recentNegocios = $pdo->query("SELECT n.id, n.nombre, n.tipo, n.created_at, i.calle, i.cdra, i.num AS num_predio FROM negocios n JOIN inmuebles i ON n.inmueble_id = i.id ORDER BY n.created_at DESC LIMIT 5")->fetchAll();

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
  $sql = "SELECT * FROM inmuebles WHERE 1";
  $params = [];
  if ($q) { $sql .= " AND (calle LIKE ? OR nombre LIKE ? OR tipo LIKE ? OR num LIKE ?)"; $params = array_fill(0,4,"%$q%"); }
  if ($uso !== '') { $sql .= " AND uso_mul = ?"; $params[] = (int)$uso; }
  $sql .= " ORDER BY calle, cdra, num";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll();
  view('inmuebles/list.php', ['rows'=>$rows, 'q'=>$q, 'uso'=>$uso]);
  exit;
}

if ($action === 'inmueble_new') {
  view('inmuebles/form.php', ['row'=>null]);
  exit;
}

if ($action === 'inmueble_save') {
  $data = [
    'calle'=>$_POST['calle'] ?? '',
    'cdra'=> (int)($_POST['cdra'] ?? 0),
    'num'=> $_POST['num'] ?? '',
    'uso_mul'=> isset($_POST['uso_mul']) ? 1 : 0,
    'tipo'=> $_POST['tipo'] ?? '',
    'nombre'=> $_POST['nombre'] ?? '',
    'nivel_z'=> isset($_POST['nivel_z']) ? 1 : 0,
    'nivel_a'=> isset($_POST['nivel_a']) ? 1 : 0,
    'dir1'=> $_POST['dir1'] ?? '',
    'dir2'=> $_POST['dir2'] ?? '',
    'dir3'=> $_POST['dir3'] ?? '',
    'dir4'=> $_POST['dir4'] ?? '',
    'nro'=> $_POST['nro'] ?? '',
    'ope'=> $_POST['ope'] ?? '',
  ];
  if (!($data['calle'] && $data['cdra'] && $data['num'] && $data['tipo'])) {
    $_SESSION['flash'] = "Completa Calle, Cdra, Num y Tipo.";
    header('Location: ?a=inmueble_new'); exit;
  }
  if (empty($_POST['id'])) {
    $stmt = $pdo->prepare("INSERT INTO inmuebles (calle, cdra, num, uso_mul, tipo, nombre, nivel_z, nivel_a, dir1, dir2, dir3, dir4, nro, ope) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute(array_values($data));
  } else {
    $data['id'] = (int)$_POST['id'];
    $stmt = $pdo->prepare("UPDATE inmuebles SET calle=?, cdra=?, num=?, uso_mul=?, tipo=?, nombre=?, nivel_z=?, nivel_a=?, dir1=?, dir2=?, dir3=?, dir4=?, nro=?, ope=? WHERE id=?");
    $stmt->execute(array_values($data));
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
  $pdo->prepare("DELETE FROM inmuebles WHERE id=?")->execute([$id]);
  header('Location: ?a=inmuebles'); exit;
}

/* ======================= NEGOCIOS ======================= */
if ($action === 'negocios') {
  $inmueble_id = (int)($_GET['inmueble_id'] ?? 0);
  $where = "1"; $params=[];
  if ($inmueble_id) { $where .= " AND n.inmueble_id=?"; $params[]=$inmueble_id; }
  $q = $_GET['q'] ?? '';
  if ($q) { $where .= " AND (n.nombre LIKE ? OR n.tipo LIKE ? OR n.productos LIKE ? OR i.nombre LIKE ? OR i.calle LIKE ?)";
           array_push($params, "%$q%","%$q%","%$q%","%$q%","%$q%"); }
  $sql = "SELECT n.*, i.calle, i.cdra, i.num AS num_predio, i.nombre AS inmueble_nombre
          FROM negocios n JOIN inmuebles i ON n.inmueble_id = i.id
          WHERE $where ORDER BY i.calle, i.cdra, n.nombre";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll();
  view('negocios/list.php', ['rows'=>$rows, 'inmueble_id'=>$inmueble_id, 'q'=>$q]);
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
    'num'=> $_POST['num'] ?? '',
    'tipo'=> $_POST['tipo'] ?? '',
    'nombre'=> $_POST['nombre'] ?? '',
    'productos'=> $_POST['productos'] ?? '',
    'contacto'=> $_POST['contacto'] ?? '',
    'cargo'=> $_POST['cargo'] ?? '',
    'telefono'=> $_POST['telefono'] ?? '',
    'ruc'=> $_POST['ruc'] ?? '',
    'tam_m2'=> $_POST['tam_m2'] ?? null,
    'imprenta'=> isset($_POST['imprenta']) ? 1 : 0,
  ];
  if (!$data['inmueble_id'] || !$data['nombre']) {
    $_SESSION['flash'] = "Selecciona un inmueble y coloca nombre del negocio.";
    header('Location: ?a=negocio_new'); exit;
  }
  if (empty($_POST['id'])) {
    $stmt = $pdo->prepare("INSERT INTO negocios (inmueble_id,piso,interior,num,tipo,nombre,productos,contacto,cargo,telefono,ruc,tam_m2,imprenta) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute(array_values($data));
  } else {
    $data['id'] = (int)$_POST['id'];
    $stmt = $pdo->prepare("UPDATE negocios SET inmueble_id=?, piso=?, interior=?, num=?, tipo=?, nombre=?, productos=?, contacto=?, cargo=?, telefono=?, ruc=?, tam_m2=?, imprenta=? WHERE id=?");
    $stmt->execute(array_values($data));
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
  $pdo->prepare("DELETE FROM negocios WHERE id=?")->execute([$id]);
  $iid = $r ? (int)$r['inmueble_id'] : 0;
  header('Location: ?a=negocios&inmueble_id=' . $iid); exit;
}

http_response_code(404);
echo "Ruta no encontrada";
