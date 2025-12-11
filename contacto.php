<?php
require_once __DIR__ . '/config/db.php';

$titulo_pagina = 'Contacto - Supercar';
require_once __DIR__ . '/includes/header_public.php';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simple honeypot
    $honeypot = trim($_POST['website'] ?? '');
    if ($honeypot !== '') {
        // probable bot
        $errors[] = 'Envio detectado como spam.';
    }

    // CSRF
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'Token de seguridad inválido.';
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $vehiculo_id = isset($_POST['vehiculo_id']) ? (int)$_POST['vehiculo_id'] : 0;

    if ($nombre === '' || strlen($nombre) < 2) $errors[] = 'Por favor indica tu nombre.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Por favor indica un email válido.';
    if ($mensaje === '' || strlen($mensaje) < 8) $errors[] = 'El mensaje debe tener al menos 8 caracteres.';

    if (empty($errors)) {
        // Preparar objeto de mensaje
        $now = date('c');
        $entry = [
            'fecha' => $now,
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'vehiculo_id' => $vehiculo_id,
            'mensaje' => $mensaje,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ];

        // Guardar en uploads/contacts (crear carpeta si no existe)
        $uploadDir = __DIR__ . '/uploads/contacts';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $filename = $uploadDir . '/' . time() . '_' . bin2hex(random_bytes(6)) . '.json';
        file_put_contents($filename, json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // Intentar enviar correo (opcional) — ajustar destinatario según configuración real
        $admin_email = 'ventas@supercar.local'; // <-- Cambia esto por el email real
        $subject = 'Nuevo mensaje de contacto desde el sitio - ' . $nombre;
        $body = "Fecha: $now\nNombre: $nombre\nEmail: $email\nTeléfono: $telefono\nVehículo: $vehiculo_id\n\nMensaje:\n$mensaje\n";
        $headers = "From: " . htmlspecialchars($nombre) . " <" . $email . ">\r\n";

        // En servidores locales es posible que mail() no esté configurado; esto es opcional.
        if (function_exists('mail')) {
            @mail($admin_email, $subject, $body, $headers);
        }

        $success = true;
        // Regenerar token para evitar reenvíos
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
}
?>

<div class="row mb-3">
    <div class="col-12">
        <h2>Contacto</h2>
        <p class="text-muted">Rellena el formulario y nos pondremos en contacto contigo lo antes posible.</p>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">Gracias, tu mensaje ha sido recibido. Te contactaremos pronto.</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-body">
                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="text" name="website" value="" style="display:none;" autocomplete="off">

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input id="nombre" name="nombre" class="form-control" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono (opcional)</label>
                        <input id="telefono" name="telefono" class="form-control" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="mensaje" class="form-label">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" rows="6" class="form-control" required><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
                    </div>
                    <input type="hidden" name="vehiculo_id" value="<?= isset($_GET['vehiculo_id']) ? (int)$_GET['vehiculo_id'] : 0 ?>">

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary">Enviar mensaje</button>
                        <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-body">
                <h5>Oficina de ventas</h5>
                <p class="mb-1"><strong>Email:</strong> ventas@supercar.local</p>
                <p class="mb-1"><strong>Teléfono:</strong> +1 555 123 456</p>
                <hr>
                <h6>Horario</h6>
                <p class="mb-0">Lun - Vie: 9:00 - 18:00<br>Sáb: 10:00 - 14:00</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6>Visítanos</h6>
                <p class="small text-muted mb-0">Estamos ubicados en la Av. Principal 123. Usa el mapa en la página principal para obtener indicaciones.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
