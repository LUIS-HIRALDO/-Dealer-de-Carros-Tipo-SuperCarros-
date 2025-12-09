<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM marcas WHERE id_marca = :id");
$stmt->execute([':id' => $id]);
$marca = $stmt->fetch();

if (!$marca) {
    header('Location: listar.php');
    exit;
}

$nombre  = $marca['nombre'];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre de la marca es obligatorio.';
    }

    if (empty($errores)) {
        $upd = $pdo->prepare("
            UPDATE marcas
            SET nombre = :nombre
            WHERE id_marca = :id
        ");
        $upd->execute([
            ':nombre' => $nombre,
            ':id'     => $id
        ]);

        header('Location: listar.php');
        exit;
    }
}

$titulo_pagina = 'Marcas - Editar';
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Editar marca</h2>
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
                <label for="nombre" class="form-label">Nombre de la marca *</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    class="form-control"
                    value="<?= htmlspecialchars($nombre) ?>"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Actualizar
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
