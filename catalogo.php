<?php
require_once __DIR__ . '/config/db.php';

$titulo_pagina = 'Catálogo - Vehículos disponibles';
require_once __DIR__ . '/includes/header.php';

// Filtros públicos simples
$busqueda     = trim($_GET['q'] ?? '');
$marca_id     = isset($_GET['marca_id']) ? (int)$_GET['marca_id'] : 0;
$categoria_id = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;
$orden        = $_GET['orden'] ?? 'recientes';

switch ($orden) {
    case 'precio_asc': $orderBySql = 'v.precio ASC'; break;
    case 'precio_desc': $orderBySql = 'v.precio DESC'; break;
    case 'anio_asc': $orderBySql = 'v.año ASC'; break;
    case 'anio_desc': $orderBySql = 'v.año DESC'; break;
    default: $orderBySql = 'v.id_vehiculo DESC'; $orden = 'recientes'; break;
}

$where = [];
$params = [];
if ($busqueda !== '') {
    $where[] = '(m.nombre LIKE :q OR mo.nombre LIKE :q OR v.color LIKE :q OR v.vin LIKE :q OR v.descripcion LIKE :q)';
    $params[':q'] = '%' . $busqueda . '%';
}
if ($marca_id > 0) {
    $where[] = 'v.id_marca = :marca_id';
    $params[':marca_id'] = $marca_id;
}
if ($categoria_id > 0) {
    $where[] = 'v.id_categoria = :categoria_id';
    $params[':categoria_id'] = $categoria_id;
}
$whereSql = '';
if (!empty($where)) $whereSql = 'WHERE ' . implode(' AND ', $where);

$sql = "
    SELECT v.id_vehiculo, v.año, v.precio, v.color, v.vin, v.descripcion, v.foto,
           c.nombre AS categoria, m.nombre AS marca, mo.nombre AS modelo, e.nombre AS estatus
    FROM vehiculos v
    INNER JOIN categorias c ON v.id_categoria = c.id_categoria
    INNER JOIN marcas m     ON v.id_marca     = m.id_marca
    INNER JOIN modelos mo   ON v.id_modelo    = mo.id_modelo
    INNER JOIN estatus e    ON v.id_estatus   = e.id_estatus
    $whereSql
    ORDER BY $orderBySql
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehiculos = $stmt->fetchAll();

// Listas para filtros
$categorias = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll();
$marcas     = $pdo->query("SELECT id_marca, nombre FROM marcas ORDER BY nombre")->fetchAll();

function imagen_path($foto) {
    $base = defined('BASE_URL') ? BASE_URL : '';
    $root = __DIR__; // project root
    if (empty($foto)) {
        return $base . '/img/placeholder.png';
    }

    // If the DB stores a full relative path like '/uploads/vehiculos/imagen.jpg'
    if (strpos($foto, '/uploads/vehiculos/') === 0) {
        $physical = $root . $foto; // __DIR__ + '/uploads/...'
        if (file_exists($physical)) return $base . $foto;
        // try without leading slash
        $physical2 = $root . '/' . ltrim($foto, '/');
        if (file_exists($physical2)) return $base . '/' . ltrim($foto, '/');
    }

    // Otherwise assume foto contains only the filename
    $filename = ltrim($foto, '/');
    $physical = $root . '/uploads/vehiculos/' . $filename;
    if (file_exists($physical)) {
        return $base . '/uploads/vehiculos/' . $filename;
    }

    return $base . '/img/placeholder.png';
}
?>

<div class="row mb-3">
    <div class="col-12">
        <h2>Catálogo de vehículos</h2>
        <p class="text-muted">Vehículos disponibles para la venta. Usa los filtros para encontrar lo que buscas.</p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="q" class="form-label">Buscar</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="Marca, modelo, color, VIN..." value="<?= htmlspecialchars($busqueda) ?>">
            </div>
            <div class="col-md-3">
                <label for="categoria_id" class="form-label">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-select">
                    <option value="0">Todas</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>" <?= $categoria_id === (int)$c['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="marca_id" class="form-label">Marca</label>
                <select name="marca_id" id="marca_id" class="form-select">
                    <option value="0">Todas</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= $m['id_marca'] ?>" <?= $marca_id === (int)$m['id_marca'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="orden" class="form-label">Orden</label>
                <select name="orden" id="orden" class="form-select">
                    <option value="recientes" <?= $orden === 'recientes' ? 'selected' : '' ?>>Más recientes</option>
                    <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Precio ↑</option>
                    <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio ↓</option>
                    <option value="anio_desc" <?= $orden === 'anio_desc' ? 'selected' : '' ?>>Año ↓</option>
                    <option value="anio_asc" <?= $orden === 'anio_asc' ? 'selected' : '' ?>>Año ↑</option>
                </select>
            </div>
            <div class="col-md-12 col-lg-12">
                <div class="d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
                    <a href="catalogo.php" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
    <?php if (!empty($vehiculos)): ?>
        <?php foreach ($vehiculos as $v): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?= imagen_path($v['foto']) ?>" class="card-img-top" alt="<?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>" style="height:200px; object-fit:cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1"><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></h5>
                        <p class="text-muted small mb-1"><?= htmlspecialchars($v['categoria']) ?> · <?= htmlspecialchars($v['año']) ?></p>
                        <p class="mb-2" style="flex:1;"><?= nl2br(htmlspecialchars(substr($v['descripcion'], 0, 120))) ?><?php if (strlen($v['descripcion'])>120) echo '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>$<?= number_format((float)$v['precio'], 2) ?></strong>
                            <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/vehiculos/ver.php?id=<?= $v['id_vehiculo'] ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">No se encontraron vehículos con los filtros seleccionados.</div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
