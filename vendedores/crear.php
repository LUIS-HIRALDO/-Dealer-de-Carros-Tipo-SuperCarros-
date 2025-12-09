<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$titulo_pagina = 'Vendedores - Crear';
require_once '../includes/header.php';

$nombre   = '';
$telefono = '';
$email    = '';
$errores  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre del vendedor es obligatorio.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email no tiene un formato válido.';
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("
            INSERT INTO vendedor (nombre, telefono, email)
            VALUES (:nombre, :telefono, :email)
        ");
        $stmt->execute([
            ':nombre'   => $nombre,
            ':telefono' => $telefono,
            ':email'    => $email,
        ]);

        header('Location: listar.php');
        exit;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Nuevo vendedor</h2>
    <a href="listar.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-body">

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre *</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    class="form-control"
                    value="<?= htmlspecialchars($nombre) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input
                    type="text"
                    id="telefono"
                    name="telefono"
                    class="form-control"
                    value="<?= htmlspecialchars($telefono) ?>"
                >
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($email) ?>"
                >
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
