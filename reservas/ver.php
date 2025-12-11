<?php
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    exit('Reserva no encontrada');
}

$stmt = $pdo->prepare('SELECT r.*, v.precio AS precio, v.id_vehiculo, v.id_estatus, m.nombre AS marca, mo.nombre AS modelo
  FROM reservas r
  LEFT JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
  LEFT JOIN marcas m ON v.id_marca = m.id_marca
  LEFT JOIN modelos mo ON v.id_modelo = mo.id_modelo
  WHERE r.id_reserva = :id LIMIT 1');
$stmt->execute([':id'=>$id]);
$r = $stmt->fetch();
if (!$r) {
    http_response_code(404);
    exit('Reserva no encontrada');
}

$titulo_pagina = 'Reserva #' . $r['id_reserva'];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-3">
  <div class="col-12">
    <h2>Reserva #<?= $r['id_reserva'] ?></h2>
    <p class="text-muted">Fecha: <?= $r['fecha_reserva'] ?></p>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5>Cliente</h5>
    <p><strong><?= htmlspecialchars($r['nombre']) ?></strong><br><?= htmlspecialchars($r['email']) ?><br><?= htmlspecialchars($r['telefono']) ?></p>

    <h5>Vehículo</h5>
    <?php if ($r['id_vehiculo']): ?>
      <p><?= htmlspecialchars($r['marca'] . ' ' . $r['modelo']) ?> (ID <?= $r['id_vehiculo'] ?>) - Precio: <?= number_format((float)$r['precio'],2) ?></p>
      <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/vehiculos/ver.php?id=<?= $r['id_vehiculo'] ?>" class="btn btn-sm btn-outline-primary">Abrir vehículo</a>
    <?php else: ?>
      <p class="text-muted">Vehículo no disponible</p>
    <?php endif; ?>

    <h5 class="mt-3">Nota</h5>
    <p><?= nl2br(htmlspecialchars($r['nota'])) ?></p>

    <h6>Meta</h6>
    <p>IP: <?= htmlspecialchars($r['ip'] ?? '') ?> | Estado: <?= $r['estado']==1 ? 'Activa' : 'Cancelada' ?></p>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
