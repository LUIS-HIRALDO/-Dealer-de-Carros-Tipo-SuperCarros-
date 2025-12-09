<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$titulo_pagina = 'Modelos - Listado';
require_once '../includes/header.php';


$busqueda  = trim($_GET['q'] ?? '');
$orden     = $_GET['orden'] ?? 'recientes';
$marca_id  = isset($_GET['marca_id']) ? (int)$_GET['marca_id'] : 0;


switch ($orden) {
    case 'nombre_asc':
        $orderBySql = 'mo.nombre ASC';
        break;
    case 'nombre_desc':
        $orderBySql = 'mo.nombre DESC';
        break;
    case 'marca_asc':
        $orderBySql = 'm.nombre ASC, mo.nombre ASC';
        break;
    case 'marca_desc':
        $orderBySql = 'm.nombre DESC, mo.nombre ASC';
        break;
    case 'id_asc':
        $orderBySql = 'mo.id_modelo ASC';
        break;
    case 'id_desc':
        $orderBySql = 'mo.id_modelo DESC';
        break;
    case 'recientes':
    default:
        $orden      = 'recientes';
        $orderBySql = 'mo.id_modelo DESC';
        break;
}

$whereParts = [];
$params     = [];

if ($busqueda !== '') {
    $whereParts[] = '(mo.nombre LIKE :busqueda OR m.nombre LIKE :busqueda)';
    $params[':busqueda'] = '%' . $busqueda . '%';
}

if ($marca_id > 0) {
    $whereParts[] = 'mo.id_marca = :marca_id';
    $params[':marca_id'] = $marca_id;
}

$whereSql = '';
if (!empty($whereParts)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereParts);
}

$sqlCount = "
    SELECT COUNT(*)
    FROM modelos mo
    INNER JOIN marcas m ON mo.id_marca = m.id_marca
    $whereSql
";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total_registros = (int)$stmtCount->fetchColumn();

$sql = "
    SELECT mo.id_modelo, mo.nombre AS modelo, m.id_marca, m.nombre AS marca
    FROM modelos mo
    INNER JOIN marcas m ON mo.id_marca = m.id_marca
    $whereSql
    ORDER BY $orderBySql
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$modelos = $stmt->fetchAll();


$stmtMarcas = $pdo->query("SELECT id_marca, nombre FROM marcas ORDER BY nombre ASC");
$marcas = $stmtMarcas->fetchAll();
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
    .page-header-super h2 { margin-bottom: 0; }
    .filter-card { border-radius: 1rem; }
    .badge-count { font-size: .8rem; }
</style>

<div class="page-header-super">
    <div>
        <h2>Modelos</h2>
        <small class="text-muted">
            Gestiona los modelos de vehículos asociados a cada marca.
        </small>
    </div>

    <a href="crear.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Nuevo modelo
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
                    placeholder="Modelo o marca..."
                    value="<?= htmlspecialchars($busqueda) ?>"
                >
            </div>

            <div class="col-md-3">
                <label for="marca_id" class="form-label mb-1">Marca</label>
                <select name="marca_id" id="marca_id" class="form-select">
                    <option value="0">Todas las marcas</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= $m['id_marca'] ?>"
                            <?= $marca_id === (int)$m['id_marca'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="orden" class="form-label mb-1">Ordenar por</label>
                <select name="orden" id="orden" class="form-select">
                    <option value="recientes"   <?= $orden === 'recientes'   ? 'selected' : '' ?>>Más recientes</option>
                    <option value="id_asc"      <?= $orden === 'id_asc'      ? 'selected' : '' ?>>ID (menor a mayor)</option>
                    <option value="id_desc"     <?= $orden === 'id_desc'     ? 'selected' : '' ?>>ID (mayor a menor)</option>
                    <option value="nombre_asc"  <?= $orden === 'nombre_asc'  ? 'selected' : '' ?>>Modelo (A - Z)</option>
                    <option value="nombre_desc" <?= $orden === 'nombre_desc' ? 'selected' : '' ?>>Modelo (Z - A)</option>
                    <option value="marca_asc"   <?= $orden === 'marca_asc'   ? 'selected' : '' ?>>Marca (A - Z)</option>
                    <option value="marca_desc"  <?= $orden === 'marca_desc'  ? 'selected' : '' ?>>Marca (Z - A)</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
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
                <?= $total_registros ?> resultado<?= $total_registros === 1 ? '' : 's' ?>
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
                            <th style="width: 80px;">ID</th>
                            <th style="width: 220px;">Modelo</th>
                            <th>Marca</th>
                            <th style="width: 140px;" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($modelos as $row): ?>
                        <tr>
                            <td class="text-muted">#<?= $row['id_modelo'] ?></td>
                            <td><strong><?= htmlspecialchars($row['modelo']) ?></strong></td>
                            <td><?= htmlspecialchars($row['marca']) ?></td>
                            <td class="text-end">
                                <a href="editar.php?id=<?= $row['id_modelo'] ?>"
                                   class="btn btn-sm btn-outline-secondary me-1"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="eliminar.php?id=<?= $row['id_modelo'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   title="Eliminar"
                                   onclick="return confirm('¿Seguro que deseas eliminar este modelo?');">
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
                <p class="mb-1">No se encontraron modelos.</p>
                <small class="text-muted">
                    Ajusta los filtros o crea un nuevo modelo.
                </small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
