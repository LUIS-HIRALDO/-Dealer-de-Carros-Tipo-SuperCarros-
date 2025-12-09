<?php
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    $titulo_pagina = 'Vehículo no encontrado';
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="alert alert-danger">ID de vehículo inválido.</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$sql = "
    SELECT v.*, c.nombre AS categoria, m.nombre AS marca, mo.nombre AS modelo, e.nombre AS estatus,
           ve.nombre AS vendedor, ve.telefono AS vendedor_telefono, ve.email AS vendedor_email
    FROM vehiculos v
    INNER JOIN categorias c ON v.id_categoria = c.id_categoria
    INNER JOIN marcas m     ON v.id_marca     = m.id_marca
    INNER JOIN modelos mo   ON v.id_modelo    = mo.id_modelo
    INNER JOIN estatus e    ON v.id_estatus   = e.id_estatus
    LEFT JOIN vendedor ve  ON v.id_vendedor  = ve.id_vendedor
    WHERE v.id_vehiculo = :id
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$v = $stmt->fetch();

if (!$v) {
    http_response_code(404);
    $titulo_pagina = 'Vehículo no encontrado';
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="alert alert-warning">No se encontró el vehículo solicitado.</div>';
    echo '<a href="' . (defined('BASE_URL') ? BASE_URL : '') . '/catalogo.php' . '" class="btn btn-secondary">Volver al catálogo</a>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

function imagen_path($foto) {
    $base = defined('BASE_URL') ? BASE_URL : '';
    $root = dirname(__DIR__); // project root
    if (empty($foto)) {
        return $base . '/img/placeholder.png';
    }

    // If DB stores '/uploads/vehiculos/imagen.jpg'
    if (strpos($foto, '/uploads/vehiculos/') === 0) {
        $physical = $root . $foto; // project_root + '/uploads/...'
        if (file_exists($physical)) return $base . $foto;
        $physical2 = $root . '/' . ltrim($foto, '/');
        if (file_exists($physical2)) return $base . '/' . ltrim($foto, '/');
    }

    // Otherwise assume only filename
    $filename = ltrim($foto, '/');
    $physical = $root . '/uploads/vehiculos/' . $filename;
    if (file_exists($physical)) {
        return $base . '/uploads/vehiculos/' . $filename;
    }

    return $base . '/img/placeholder.png';
}

$titulo_pagina = htmlspecialchars($v['marca'] . ' ' . $v['modelo']);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <img src="<?= imagen_path($v['foto']) ?>" alt="<?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>" class="img-fluid">
    </div>
    <div class="col-md-6">
        <h2><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></h2>
        <p class="text-muted"><?= htmlspecialchars($v['categoria']) ?> · <?= htmlspecialchars($v['año']) ?> · <?= htmlspecialchars($v['color']) ?></p>
        <h3 class="text-primary">$<?= number_format((float)$v['precio'], 2) ?></h3>

        <p><?= nl2br(htmlspecialchars($v['descripcion'])) ?></p>

        <hr>
        <h5>Vendedor</h5>
        <?php if (!empty($v['vendedor'])): ?>
            <p><strong><?= htmlspecialchars($v['vendedor']) ?></strong><br>
            Tel: <?= htmlspecialchars($v['vendedor_telefono'] ?? '') ?><br>
            Email: <?= htmlspecialchars($v['vendedor_email'] ?? '') ?></p>
        <?php else: ?>
            <p class="text-muted">Información del vendedor no disponible.</p>
        <?php endif; ?>

        <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/catalogo.php" class="btn btn-outline-secondary mt-3">Volver al catálogo</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
