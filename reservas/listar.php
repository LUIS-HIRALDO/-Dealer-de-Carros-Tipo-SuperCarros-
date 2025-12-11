<?php
require_once __DIR__ . '/../config/db.php';

$titulo_pagina = 'Reservas - Administración';
require_once __DIR__ . '/../includes/header.php';

// Ensure reservas table exists (in case)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS reservas (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {}

$q = $pdo->query("SELECT r.*, v.precio AS precio, v.id_vehiculo,
                  m.nombre AS marca, mo.nombre AS modelo
                  FROM reservas r
                  LEFT JOIN vehiculos v ON v.id_vehiculo = r.id_vehiculo
                  LEFT JOIN marcas m ON v.id_marca = m.id_marca
                  LEFT JOIN modelos mo ON v.id_modelo = mo.id_modelo
                  ORDER BY r.fecha_reserva DESC");
$reservas = $q->fetchAll();
?>

<div class="row mb-3">
    <div class="col-12">
        <h2>Reservas</h2>
        <p class="text-muted">Listado de reservas recibidas.</p>
    </div>
</div>

<div class="table-responsive">
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>ID</th>
        <th>Fecha</th>
        <th>Vehículo</th>
        <th>Cliente</th>
        <th>Contacto</th>
        <th>IP</th>
        <th>Estado</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reservas as $r): ?>
        <tr>
          <td><?= $r['id_reserva'] ?></td>
          <td><?= $r['fecha_reserva'] ?></td>
          <td><?php if ($r['id_vehiculo']) echo htmlspecialchars($r['marca'] . ' ' . $r['modelo']) . ' (ID ' . $r['id_vehiculo'] . ')'; else echo 'N/A'; ?></td>
          <td><?= htmlspecialchars($r['nombre']) ?><br><small><?= htmlspecialchars($r['email']) ?></small></td>
          <td><?= htmlspecialchars($r['telefono']) ?></td>
          <td><?= htmlspecialchars($r['ip'] ?? '') ?></td>
          <td><?= $r['estado'] == 1 ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Cancelada</span>' ?></td>
          <td class="text-end">
            <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/reservas/ver.php?id=<?= $r['id_reserva'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
            <?php if ($r['estado'] == 1): ?>
              <form method="post" action="<?= defined('BASE_URL') ? BASE_URL : '' ?>/reservas/accion.php" style="display:inline-block;" onsubmit="return confirm('Cancelar reserva?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="id" value="<?= $r['id_reserva'] ?>">
                <button class="btn btn-sm btn-danger">Cancelar</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
