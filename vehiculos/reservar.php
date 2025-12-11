<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// CSRF
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400);
    exit('Token CSRF inválido');
}

$id_vehiculo = isset($_POST['id_vehiculo']) ? (int)$_POST['id_vehiculo'] : 0;
$nombre = trim($_POST['nombre'] ?? '');
$email  = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$nota = trim($_POST['nota'] ?? '');

$errors = [];
if ($id_vehiculo <= 0) $errors[] = 'Vehículo inválido.';
if ($nombre === '' || strlen($nombre) < 2) $errors[] = 'Nombre inválido.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';

if (!empty($errors)) {
    // redirect back with errors in query (simpler UX)
    $qs = http_build_query(['error' => implode(' | ', $errors)]);
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/vehiculos/ver.php?id={$id_vehiculo}&{$qs}");
    exit;
}

// Ensure reservas table exists (with useful columns)
$createSql = "CREATE TABLE IF NOT EXISTS reservas (
  id_reserva INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_vehiculo INT NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  telefono VARCHAR(50) DEFAULT NULL,
  nota TEXT DEFAULT NULL,
  ip VARCHAR(45) DEFAULT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  fecha_reserva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$pdo->exec($createSql);

// Try to add missing columns if older table exists (ignore errors)
try { $pdo->exec("ALTER TABLE reservas ADD COLUMN IF NOT EXISTS ip VARCHAR(45) DEFAULT NULL"); } catch (Exception $e) {}
try { $pdo->exec("ALTER TABLE reservas ADD COLUMN IF NOT EXISTS estado TINYINT(1) NOT NULL DEFAULT 1"); } catch (Exception $e) {}

// Get requester ip
$ip = $_SERVER['REMOTE_ADDR'] ?? null;

try {
    // Transaction + SELECT FOR UPDATE (optimistic/row-level lock) to avoid race conditions
    $pdo->beginTransaction();

    // Lock vehicle row
    $stmt = $pdo->prepare('SELECT id_vehiculo, id_estatus FROM vehiculos WHERE id_vehiculo = :id LIMIT 1 FOR UPDATE');
    $stmt->execute([':id' => $id_vehiculo]);
    $veh = $stmt->fetch();
    if (!$veh) {
        $pdo->rollBack();
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/catalogo.php?error=vehiculo_no_encontrado");
        exit;
    }

    if ($veh['id_estatus'] != 1) {
        $pdo->rollBack();
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/vehiculos/ver.php?id={$id_vehiculo}&error=vehiculo_no_disponible");
        exit;
    }

    // Prevent duplicate reservations by same email or IP for same vehicle within 15 minutes
    $checkDup = $pdo->prepare("SELECT COUNT(*) AS cnt FROM reservas WHERE id_vehiculo = :id AND estado = 1 AND (email = :email OR ip = :ip) AND fecha_reserva > (NOW() - INTERVAL 15 MINUTE)");
    $checkDup->execute([':id'=>$id_vehiculo, ':email'=>$email, ':ip'=>$ip]);
    $dupRow = $checkDup->fetch();
    if ($dupRow && $dupRow['cnt'] > 0) {
        $pdo->rollBack();
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/vehiculos/ver.php?id={$id_vehiculo}&error=duplicated_reservation");
        exit;
    }

    // Insert reservation
    $ins = $pdo->prepare('INSERT INTO reservas (id_vehiculo, nombre, email, telefono, nota, ip) VALUES (:id_vehiculo, :nombre, :email, :telefono, :nota, :ip)');
    $ins->execute([
        ':id_vehiculo' => $id_vehiculo,
        ':nombre' => $nombre,
        ':email' => $email,
        ':telefono' => $telefono,
        ':nota' => $nota,
        ':ip' => $ip,
    ]);

    // Update vehicle status to Reservado (2)
    $upd = $pdo->prepare('UPDATE vehiculos SET id_estatus = 2 WHERE id_vehiculo = :id');
    $upd->execute([':id' => $id_vehiculo]);

    $pdo->commit();

    // Send emails (admin + client) if mail() available
    $admin_email = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'ventas@supercar.local';
    $subjectAdmin = 'Nueva reserva - Vehículo ID ' . $id_vehiculo;
    $bodyAdmin = "Se ha realizado una nueva reserva.\n\nVehículo ID: {$id_vehiculo}\nNombre: {$nombre}\nEmail: {$email}\nTeléfono: {$telefono}\nIP: {$ip}\nNota: {$nota}\n";
    $headers = 'From: no-reply@' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\r\n";
    if (function_exists('mail')) {
        @mail($admin_email, $subjectAdmin, $bodyAdmin, $headers);
    }

    // Mail to client
    $subjectClient = 'Confirmación de reserva - Supercar';
    $bodyClient = "Hola {$nombre},\n\nHemos recibido tu solicitud de reserva para el vehículo ID {$id_vehiculo}. Nos pondremos en contacto contigo pronto.\n\nGracias,\nSupercar\n";
    if (function_exists('mail')) {
        @mail($email, $subjectClient, $bodyClient, $headers);
    }

    // Redirect back with success
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/vehiculos/ver.php?id={$id_vehiculo}&reserved=1");
    exit;

} catch (Exception $e) {
    try { $pdo->rollBack(); } catch (Exception $e2) {}
    // Redirect with error
    header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/vehiculos/ver.php?id={$id_vehiculo}&error=internal_error");
    exit;
}

?>
