<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$titulo_pagina = 'Modelos - Crear';
require_once '../includes/header.php';

// Cargar marcas para el select
$stmtMarcas = $pdo->query("SELECT id_marca, nombre FROM marcas ORDER BY nombre ASC");
$marcas = $stmtMarcas->fetchAll();

$id_marca = 0;
$nombre   = '';
$errores  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_marca = isset($_POST['id_marca']) ? (int)$_POST['id_marca'] : 0;
    $nombre   = trim($_POST['nombre'] ?? '');

    if ($id_marca <= 0) {
        $errores[] = 'Debes seleccionar una marca.';
    }
    if ($nombre === '') {
        $errores[] = 'El nombre del modelo es obligatorio.';
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("
            INSERT INTO modelos (id_marca, nombre)
            VALUES (:id_marca, :nombre)
        ");
        $stmt->execute([
            ':id_marca' => $id_marca,
            ':nombre'   => $nombre
        ]);

        header('Location: listar.php');
        exit;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Nuevo modelo</h2>
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
                <label for="id_marca" class="form-label">Marca *</label>
                <select name="id_marca" id="id_marca" class="form-select" required>
                    <option value="">Seleccione una marca...</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= $m['id_marca'] ?>"
                            <?= $id_marca === (int)$m['id_marca'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">
                    ¿No encuentras la marca? <a href="../marcas/crear.php">Crear marca</a>.
                </div>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del modelo *</label>
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
                <i class="bi bi-save"></i> Guardar
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
