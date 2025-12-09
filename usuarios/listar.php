<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$titulo_pagina = 'Usuarios - Listado';
require_once '../includes/header.php';

$rolesDisponibles = [
    'admin'     => 'Administrador',
    'vendedor'  => 'Vendedor',
    'supervisor'=> 'Supervisor',
];

// ===== Filtros =====
$busqueda = trim($_GET['q'] ?? '');
$rol      = $_GET['rol'] ?? '';
$orden    = $_GET['orden'] ?? 'recientes';

switch ($orden) {
    case 'nombre_asc':
        $orderBySql = 'nombre ASC';
        break;
    case 'nombre_desc':
        $orderBySql = 'nombre DESC';
        break;
    case 'usuario_asc':
        $orderBySql = 'usuario ASC';
        break;
    case 'usuario_desc':
        $orderBySql = 'usuario DESC';
        break;
    case 'id_asc':
        $orderBySql = 'id_usuario ASC';
        break;
    case 'id_desc':
        $orderBySql = 'id_usuario DESC';
        break;
    case 'recientes':
    default:
        $orden      = 'recientes';
        $orderBySql = 'fecha_creacion DESC';
        break;
}

$whereParts = [];
$params     = [];

if ($busqueda !== '') {
    $whereParts[] = '(nombre LIKE :q OR usuario LIKE :q OR email LIKE :q)';
    $params[':q'] = '%' . $busqueda . '%';
}

if ($rol !== '' && isset($rolesDisponibles[$rol])) {
    $whereParts[] = 'rol = :rol';
    $params[':rol'] = $rol;
}

$whereSql = '';
if (!empty($whereParts)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereParts);
}

// Total y listado
$sqlCount = "SELECT COUNT(*) FROM usuarios $whereSql";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$total_registros = (int)$stmtCount->fetchColumn();

$sql = "
    SELECT id_usuario, nombre, usuario, email, rol, fecha_creacion
    FROM usuarios
    $whereSql
    ORDER BY $orderBySql
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();
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
        <h2>Usuarios</h2>
        <small class="text-muted">
            Control de acceso al sistema. Administra nombres de usuario, emails y roles.
        </small>
    </div>

    <a href="crear.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Nuevo usuario
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
                    placeholder="Nombre, usuario o email..."
                    value="<?= htmlspecialchars($busqueda) ?>"
                >
            </div>

            <div class="col-md-3 col-lg-2">
                <label for="rol" class="form-label mb-1">Rol</label>
                <select name="rol" id="rol" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($rolesDisponibles as $k => $et): ?>
                        <option value="<?= $k ?>" <?= $rol === $k ? 'selected' : '' ?>>
                            <?= htmlspecialchars($et) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 col-lg-3">
                <label for="orden" class="form-label mb-1">Ordenar por</label>
                <select name="orden" id="orden" class="form-select">
                    <option value="recientes"   <?= $orden === 'recientes'   ? 'selected' : '' ?>>Más recientes</option>
                    <option value="id_asc"      <?= $orden === 'id_asc'      ? 'selected' : '' ?>>ID (menor a mayor)</option>
                    <option value="id_desc"     <?= $orden === 'id_desc'     ? 'selected' : '' ?>>ID (mayor a menor)</option>
                    <option value="nombre_asc"  <?= $orden === 'nombre_asc'  ? 'selected' : '' ?>>Nombre (A - Z)</option>
                    <option value="nombre_desc" <?= $orden === 'nombre_desc' ? 'selected' : '' ?>>Nombre (Z - A)</option>
                    <option value="usuario_asc" <?= $orden === 'usuario_asc' ? 'selected' : '' ?>>Usuario (A - Z)</option>
                    <option value="usuario_desc"<?= $orden === 'usuario_desc'? 'selected' : '' ?>>Usuario (Z - A)</option>
                </select>
            </div>

            <div class="col-md-2 col-lg-1">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <div class="col-md-2 col-lg-2">
                <a href="listar.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>

        <div class="mt-2">
            <span class="badge bg-light text-secondary badge-count">
                <i class="bi bi-list-ul"></i>
                <?= $total_registros ?> usuario<?= $total_registros === 1 ? '' : 's' ?>
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
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Creado</th>
                            <th style="width: 140px;" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="text-muted">#<?= $u['id_usuario'] ?></td>
                            <td><?= htmlspecialchars($u['nombre']) ?></td>
                            <td><?= htmlspecialchars($u['usuario']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php
                                $etRol = $rolesDisponibles[$u['rol']] ?? $u['rol'];
                                ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($etRol) ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars($u['fecha_creacion']) ?>
                                </small>
                            </td>
                            <td class="text-end">
                                <a href="editar.php?id=<?= $u['id_usuario'] ?>"
                                   class="btn btn-sm btn-outline-secondary me-1"
                                   title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="eliminar.php?id=<?= $u['id_usuario'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   title="Eliminar"
                                   onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
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
                <p class="mb-1">No se encontraron usuarios.</p>
                <small class="text-muted">Ajusta los filtros o crea un nuevo usuario.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
