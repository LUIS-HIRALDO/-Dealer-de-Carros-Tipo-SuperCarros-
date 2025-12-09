<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$titulo_pagina = 'Vehículos - Listado';
require_once '../includes/header.php';

// ====== Filtros ======
$busqueda     = trim($_GET['q'] ?? '');
$categoria_id = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;
$marca_id     = isset($_GET['marca_id']) ? (int)$_GET['marca_id'] : 0;
$estatus_id   = isset($_GET['estatus_id']) ? (int)$_GET['estatus_id'] : 0;
$orden        = $_GET['orden'] ?? 'recientes';

switch ($orden) {
    case 'precio_asc':
        $orderBySql = 'v.precio ASC';
        break;
    case 'precio_desc':
        $orderBySql = 'v.precio DESC';
        break;
    case 'anio_asc':
        $orderBySql = 'v.año ASC';
        break;
    case 'anio_desc':
        $orderBySql = 'v.año DESC';
        break;
    case 'marca_asc':
        $orderBySql = 'm.nombre ASC, mo.nombre ASC';
        break;
    case 'marca_desc':
        $orderBySql = 'm.nombre DESC, mo.nombre ASC';
        break;
    case 'recientes':
    default:
        $orden      = 'recientes';
        $orderBySql = 'v.id_vehiculo DESC';
        break;
}

$whereParts = [];
$params     = [];

if ($busqueda !== '') {
    $whereParts[] = '(m.nombre LIKE :q OR mo.nombre LIKE :q OR v.color LIKE :q OR v.vin LIKE :q OR v.descripcion LIKE :q)';
    $params[':q'] = '%' . $busqueda . '%';
}
if ($categoria_id > 0) {
    $whereParts[] = 'v.id_categoria = :categoria_id';
    $params[':categoria_id'] = $categoria_id;
}
if ($marca_id > 0) {
    $whereParts[] = 'v.id_marca = :marca_id';
    $params[':marca_id'] = $marca_id;
}
if ($estatus_id > 0) {
    $whereParts[] = 'v.id_estatus = :estatus_id';
    $params[':estatus_id'] = $estatus_id;
}

$whereSql = '';
if (!empty($whereParts)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereParts);
}

// ====== Total y listado ======
$sqlCount = "
    SELECT COUNT(*)
    FROM vehiculos v
    INNER JOIN categorias c ON v.id_categoria = c.id_categoria
    INNER JOIN marcas m     ON v.id_marca     = m.id_marca
    INNER JOIN modelos mo   ON v.id_modelo    = mo.id_modelo
    INNER JOIN estatus e    ON v.id_estatus   = e.id_estatus
    INNER JOIN vendedor ve  ON v.id_vendedor  = ve.id_vendedor
    $whereSql
";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total_registros = (int)$stmtCount->fetchColumn();

$sql = "
    SELECT v.id_vehiculo, v.año, v.precio, v.color, v.vin, v.descripcion,v.foto,
           c.nombre  AS categoria,
           m.nombre  AS marca,
           mo.nombre AS modelo,
           e.nombre  AS estatus,
           ve.nombre AS vendedor
    FROM vehiculos v
    INNER JOIN categorias c ON v.id_categoria = c.id_categoria
    INNER JOIN marcas m     ON v.id_marca     = m.id_marca
    INNER JOIN modelos mo   ON v.id_modelo    = mo.id_modelo
    INNER JOIN estatus e    ON v.id_estatus   = e.id_estatus
    INNER JOIN vendedor ve  ON v.id_vendedor  = ve.id_vendedor
    $whereSql
    ORDER BY $orderBySql
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehiculos = $stmt->fetchAll();

// Listas para filtros
$categorias = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll();
$marcas     = $pdo->query("SELECT id_marca, nombre FROM marcas ORDER BY nombre")->fetchAll();
$estatus    = $pdo->query("SELECT id_estatus, nombre FROM estatus ORDER BY nombre")->fetchAll();
?>

<style>
    .page-header-super {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .page-header-super h2 {
        margin-bottom: 0;
    }

    .filter-card {
        border-radius: 1rem;
    }

    .badge-count {
        font-size: .8rem;
    }
</style>

<div class="page-header-super">
    <div>
        <h2>Vehículos</h2>
        <small class="text-muted">
            Inventario general de vehículos. Filtra por marca, categoría, estado, etc.
        </small>
    </div>

    <a href="crear.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Nuevo vehículo
    </a>
</div>

<!-- FILTROS -->
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">

            <div class="col-md-4">
                <label for="q" class="form-label mb-1">Buscar</label>
                <input
                    type="text"
                    name="q"
                    id="q"
                    class="form-control"
                    placeholder="Marca, modelo, color, VIN..."
                    value="<?= htmlspecialchars($busqueda) ?>">
            </div>

            <div class="col-md-3 col-lg-2">
                <label for="categoria_id" class="form-label mb-1">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-select">
                    <option value="0">Todas</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>"
                            <?= $categoria_id === (int)$c['id_categoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 col-lg-2">
                <label for="marca_id" class="form-label mb-1">Marca</label>
                <select name="marca_id" id="marca_id" class="form-select">
                    <option value="0">Todas</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= $m['id_marca'] ?>"
                            <?= $marca_id === (int)$m['id_marca'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 col-lg-2">
                <label for="estatus_id" class="form-label mb-1">Estatus</label>
                <select name="estatus_id" id="estatus_id" class="form-select">
                    <option value="0">Todos</option>
                    <?php foreach ($estatus as $e): ?>
                        <option value="<?= $e['id_estatus'] ?>"
                            <?= $estatus_id === (int)$e['id_estatus'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 col-lg-2">
                <label for="orden" class="form-label mb-1">Ordenar por</label>
                <select name="orden" id="orden" class="form-select">
                    <option value="recientes" <?= $orden === 'recientes'   ? 'selected' : '' ?>>Más recientes</option>
                    <option value="precio_asc" <?= $orden === 'precio_asc'  ? 'selected' : '' ?>>Precio (menor a mayor)</option>
                    <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio (mayor a menor)</option>
                    <option value="anio_asc" <?= $orden === 'anio_asc'    ? 'selected' : '' ?>>Año (más viejo primero)</option>
                    <option value="anio_desc" <?= $orden === 'anio_desc'   ? 'selected' : '' ?>>Año (más nuevo primero)</option>
                    <option value="marca_asc" <?= $orden === 'marca_asc'   ? 'selected' : '' ?>>Marca (A - Z)</option>
                    <option value="marca_desc" <?= $orden === 'marca_desc'  ? 'selected' : '' ?>>Marca (Z - A)</option>
                </select>
            </div>

            <div class="col-md-3 col-lg-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <a href="listar.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>

        <div class="mt-2">
            <span class="badge bg-light text-secondary badge-count">
                <i class="bi bi-list-ul"></i>
                <?= $total_registros ?> vehículo<?= $total_registros === 1 ? '' : 's' ?>
                <?= $busqueda !== '' ? ' para "' . htmlspecialchars($busqueda) . '"' : '' ?>
            </span>
        </div>
    </div>
</div>

<!-- LISTADO -->
<div class="card">
    <div class="card-body p-0">
        <?php if ($total_registros > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Vehículo</th>
                            <th>Categoría</th>
                            <th>Año</th>
                            <th>Color</th>
                            <th>Precio</th>
                            <th>Estatus</th>
                            <th>Vendedor</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehiculos as $v): ?>
                            <tr>
                                <td class="text-muted">#<?= $v['id_vehiculo'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></strong><br>
                                    <small class="text-muted">VIN: <?= htmlspecialchars($v['vin']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($v['categoria']) ?></td>
                                <td><?= htmlspecialchars($v['año']) ?></td>
                                <td><?= htmlspecialchars($v['color']) ?></td>
                                <td>$<?= number_format((float)$v['precio'], 2) ?></td>
                                <td><?= htmlspecialchars($v['estatus']) ?></td>
                                <td><?= htmlspecialchars($v['vendedor']) ?></td>
                                <td class="text-end">
                                    <a href="editar.php?id=<?= $v['id_vehiculo'] ?>"
                                        class="btn btn-sm btn-outline-secondary me-1"
                                        title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $v['id_vehiculo'] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Eliminar"
                                        onclick="return confirm('¿Seguro que deseas eliminar este vehículo?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                               
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-4">
                <p class="mb-1">No se encontraron vehículos.</p>
                <small class="text-muted">Ajusta los filtros o agrega un nuevo vehículo.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>