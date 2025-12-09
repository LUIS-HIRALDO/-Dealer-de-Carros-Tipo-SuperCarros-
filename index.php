<?php
require_once __DIR__ . '/includes/auth.php'; 
require_once 'config/db.php';

$titulo_pagina = 'Supercar - Panel de control';
require_once 'includes/header.php';


$totales = [
    'categorias' => 0,
    'marcas'     => 0,
    'modelos'    => 0,
    'vehiculos'  => 0,
    'vendedores' => 0,
    'usuarios'   => 0,
];

$ultimos_vehiculos = [];

try {
    $totales['categorias'] = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $totales['marcas']     = $pdo->query("SELECT COUNT(*) FROM marcas")->fetchColumn();
    $totales['modelos']    = $pdo->query("SELECT COUNT(*) FROM modelos")->fetchColumn();
    $totales['vehiculos']  = $pdo->query("SELECT COUNT(*) FROM vehiculos")->fetchColumn();
    $totales['vendedores'] = $pdo->query("SELECT COUNT(*) FROM vendedor")->fetchColumn();
    $totales['usuarios']   = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();


    $sqlVeh = "
        SELECT v.id_vehiculo, v.precio, v.color, v.año,
               m.nombre AS marca, mo.nombre AS modelo, e.nombre AS estatus
        FROM vehiculos v
        INNER JOIN marcas m   ON v.id_marca = m.id_marca
        INNER INNER JOIN modelos mo ON v.id_modelo = mo.id_modelo
        INNER JOIN estatus e  ON v.id_estatus = e.id_estatus
        ORDER BY v.id_vehiculo DESC
        LIMIT 5
    ";
    $ultimos_vehiculos = $pdo->query($sqlVeh)->fetchAll();

} catch (PDOException $e) {

}
?>

<style>
.hero-card {
background-color: #000;
    color: #fff;
    border-radius: 1rem;
    padding: 2.5rem 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}
.dashboard-card {
    border-radius: 1rem;
    transition: .2s ease;
}
.dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.12);
}
.icon-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background-color: rgba(13,110,253,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-right: 8px;
}
.table-vehiculos thead {
    background: #f8f9fa;
}
</style>

<!-- HERO -->
<div class="row mb-4">
    <div class="col-lg-12 mb-3">
        <div class="hero-card h-100">
            <h1 class="mb-2">Bienvenido a Supercars</h1>
            <p class="lead">Administra inventario, modelos, marcas y vendedores desde un solo lugar.</p>

            <a href="vehiculos/listar.php" class="btn btn-light btn-sm me-2">
                <i class="bi bi-car-front-fill"></i> Ver inventario
            </a>
            <a href="vehiculos/crear.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-plus-circle"></i> Agregar vehículo
            </a>
        </div>
    </div>


</div>

<!-- TARJETAS DE ESTADISTICAS -->
<div class="row g-3 mb-4">

    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-1">
                    <div class="icon-circle"><i class="bi bi-folder"></i></div>
                    <h6 class="mb-0">Categorías</h6>
                </div>
                <h3><?= $totales['categorias'] ?></h3>
                <a class="small stretched-link" href="categorias/listar.php">Ver categorías</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-1">
                    <div class="icon-circle"><i class="bi bi-tag"></i></div>
                    <h6 class="mb-0">Marcas</h6>
                </div>
                <h3><?= $totales['marcas'] ?></h3>
                <a class="small stretched-link" href="marcas/listar.php">Ver marcas</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-1">
                    <div class="icon-circle"><i class="bi bi-car-front"></i></div>
                    <h6 class="mb-0">Modelos</h6>
                </div>
                <h3><?= $totales['modelos'] ?></h3>
                <a class="small stretched-link" href="modelos/listar.php">Ver modelos</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-1">
                    <div class="icon-circle"><i class="bi bi-car-front-fill"></i></div>
                    <h6 class="mb-0">Vehículos</h6>
                </div>
                <h3><?= $totales['vehiculos'] ?></h3>
                <a class="small stretched-link" href="vehiculos/listar.php">Ver vehículos</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-1">
                    <div class="icon-circle"><i class="bi bi-person-badge"></i></div>
                    <h6 class="mb-0">Vendedores</h6>
                </div>
                <h3><?= $totales['vendedores'] ?></h3>
                <a class="small stretched-link" href="vendedores/listar.php">Ver vendedores</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-xl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-1">
                    <div class="icon-circle"><i class="bi bi-people"></i></div>
                    <h6 class="mb-0">Usuarios</h6>
                </div>
                <h3><?= $totales['usuarios'] ?></h3>
                <a class="small stretched-link" href="usuarios/listar.php">Ver usuarios</a>
            </div>
        </div>
    </div>

</div>

<!-- ACCESOS RÁPIDOS Y ÚLTIMOS VEHÍCULOS -->
<div class="row mb-4">

    <div class="col-lg-6 mb-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5> Accesos rápidos</h5>
                <p class="text-muted">Tareas frecuentes:</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="vehiculos/crear.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Vehículo
                    </a>
                    <a href="marcas/crear.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-tag"></i> Marca
                    </a>
                    <a href="modelos/crear.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-car-front"></i> Modelo
                    </a>
                    <a href="vendedores/crear.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person-badge"></i> Vendedor
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos vehículos -->
    <div class="col-lg-6 mb-3">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h5><i class="bi bi-clock-history"></i> Últimos vehículos</h5>

                <?php if (!empty($ultimos_vehiculos)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-vehiculos mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vehículo</th>
                                    <th>Año</th>
                                    <th>Color</th>
                                    <th>Precio</th>
                                    <th>Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($ultimos_vehiculos as $veh): ?>
                                <tr>
                                    <td><?= $veh['id_vehiculo'] ?></td>
                                    <td><?= $veh['marca'] . ' ' . $veh['modelo'] ?></td>
                                    <td><?= $veh['año'] ?></td>
                                    <td><?= $veh['color'] ?></td>
                                    <td>$<?= number_format($veh['precio'], 2) ?></td>
                                    <td><?= $veh['estatus'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay vehículos registrados aún.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>
