<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$titulo_pagina = 'Vendedores - Listado';
require_once '../includes/header.php';

// ============ Filtros ============
$busqueda = trim($_GET['q'] ?? '');
$orden    = $_GET['orden'] ?? 'recientes';

switch ($orden) {
    case 'nombre_asc':
        $orderBySql = 'nombre ASC';
        break;
    case 'nombre_desc':
        $orderBySql = 'nombre DESC';
        break;
    case 'id_asc':
        $orderBySql = 'id_vendedor ASC';
        break;
    case 'id_desc':
        $orderBySql = 'id_vendedor DESC';
        break;
    case 'recientes':
    default:
        $orden      = 'recientes';
        $orderBySql = 'id_vendedor DESC';
        break;
}

$whereSql = '';
$params   = [];

if ($busqueda !== '') {
    $whereSql = "WHERE nombre LIKE :q OR telefono LIKE :q OR email LIKE :q";
    $params[':q'] = '%' . $busqueda . '%';
}

// ============ Total y listado ============
$sqlCount = "SELECT COUNT(*) FROM vendedor $whereSql";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total_registros = (int)$stmtCount->fetchColumn();

$sql = "SELECT * FROM vendedor $whereSql ORDER BY $orderBySql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vendedores = $stmt->fetchAll();
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
        <h2>Vendedores</h2>
        <small class="text-muted">
            Gestiona los vendedores que atienden las ventas de vehículos.
        </small>
    </div>

    <a href="crear.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Nuevo vendedor
    </a>
</div>

<!-- FILTROS -->
<div class="card filter-card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label for="q" class="form-label mb-1">Buscar</label>
                <input
                    type="text"
                    name="q"
                    id="q"
                    class="form-control"
                    placeholder="Nombre, teléfono o email..."
                    value="<?= htmlspecialchars($busqueda) ?>"
                >
            </div>

            <div class="col-md-4 col-lg-3">
                <label for="orden" class="form-label mb-1">Ordenar por</label>
                <select name="orden" id="orden" class="form-select">
                    <option value="recientes"   <?= $orden === 'recientes'   ? 'selected' : '' ?>>Más recientes</option>
                    <option value="id_asc"      <?= $orden === 'id_asc'      ? 'selected' : '' ?>>ID (menor a mayor)</option>
                    <option value="id_desc"     <?= $orden === 'id_desc'     ? 'selected' : '' ?>>ID (mayor a menor)</option>
                    <option value="nombre_asc"  <?= $orden === 'nombre_asc'  ? 'selected' : '' ?>>Nombre (A - Z)</option>
                    <option value="nombre_desc" <?= $orden === 'nombre_desc' ? 'selected' : '' ?>>Nombre (Z - A)</option>
                </select>
            </div>

            <div class="col-md-3 col-lg-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>

            <div class="col-md-3 col-lg-2">
                <a href="listar.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>

        <div class="mt-2">
            <span class="badge bg-light text-secondary badge-count">
                <i class="bi bi-list-ul"></i>
                <?= $total_registros ?> vendedor<?= $total_registros === 1 ? '' : 'es' ?>
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
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th style="width: 140px;" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($vendedores as $v): ?>
                        <tr>
                            <td class="text-muted">#<?= $v['id_vendedor'] ?></td>
                            <td><strong><?= htmlspecialchars($v['nombre']) ?></strong></td>
                            <td><?= htmlspecialchars($v['telefono']) ?></td>
                            <td><?= htmlspecialchars($v['email']) ?></td>
                            <td class="text-end">
                                <a href="editar.php?id=<?= $v['id_vendedor'] ?>"
                                   class="btn btn-sm btn-outline-secondary me-1"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="eliminar.php?id=<?= $v['id_vendedor'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   title="Eliminar"
                                   onclick="return confirm('¿Seguro que deseas eliminar este vendedor?');">
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
                <p class="mb-1">No se encontraron vendedores.</p>
                <small class="text-muted">Puedes ajustar los filtros o crear un nuevo vendedor.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
