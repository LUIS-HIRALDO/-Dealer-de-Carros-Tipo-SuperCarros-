<?php
require_once '../config/db.php';

$titulo_pagina = 'Categorías - Crear';
require_once '../includes/header.php';

$nombre = '';
$descripcion = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre de la categoría es obligatorio.';
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion
        ]);

        header('Location: listar.php');
        exit;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Nueva categoría</h2>
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
                <input type="text" id="nombre" name="nombre" class="form-control"
                       value="<?= htmlspecialchars($nombre) ?>" required>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($descripcion) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
