<?php
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    $titulo_pagina = 'Vehículo no encontrado';
    require_once __DIR__ . '/../includes/header_public.php';
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
    require_once __DIR__ . '/../includes/header_public.php';
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
require_once __DIR__ . '/../includes/header_public.php';
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

        <?php
        // Si viene un return seguro (misma aplicación), usarlo para volver al catálogo y restaurar scroll
        $return = '';
        if (!empty($_GET['return'])) {
            $decoded = rawurldecode($_GET['return']);
            // Seguridad básica: sólo permitir rutas que comiencen con BASE_URL or '/'
            $base = defined('BASE_URL') ? BASE_URL : '/supercar';
            if (strpos($decoded, $base) === 0 || strpos($decoded, '/') === 0) {
                $return = $decoded;
            }
        }
        ?>
                <a href="<?= $return ? htmlspecialchars($return) : (defined('BASE_URL') ? BASE_URL : '') . '/catalogo.php' ?>" class="btn btn-outline-secondary mt-3">Volver al catálogo</a>

                <?php if ($v['id_estatus'] == 1): ?>
                        <!-- Botón para abrir modal de reserva -->
                        <button class="btn btn-success mt-3 ms-2" data-bs-toggle="modal" data-bs-target="#reserveModal">Reservar</button>
                <?php else: ?>
                        <button class="btn btn-secondary mt-3 ms-2" disabled>Estado: <?= htmlspecialchars($v['estatus'] ?? 'No disponible') ?></button>
                <?php endif; ?>
    </div>
</div>

<!-- Modal de reserva -->
<div class="modal fade" id="reserveModal" tabindex="-1" aria-labelledby="reserveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= defined('BASE_URL') ? BASE_URL : '' ?>/vehiculos/reservar.php">
            <div class="modal-header">
                <h5 class="modal-title" id="reserveModalLabel">Reservar: <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="id_vehiculo" value="<?= (int)$v['id_vehiculo'] ?>">
                    <div class="mb-3">
                        <label for="res-nombre" class="form-label">Nombre</label>
                        <input id="res-nombre" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="res-email" class="form-label">Email</label>
                        <input id="res-email" name="email" type="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="res-telefono" class="form-label">Teléfono</label>
                        <input id="res-telefono" name="telefono" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="res-nota" class="form-label">Mensaje (opcional)</label>
                        <textarea id="res-nota" name="nota" rows="3" class="form-control"></textarea>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar reserva</button>
            </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
