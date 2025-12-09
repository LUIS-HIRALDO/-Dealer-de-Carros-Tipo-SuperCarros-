<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

// Cargar vehículo
$stmt = $pdo->prepare("SELECT * FROM vehiculos WHERE id_vehiculo = :id");
$stmt->execute([':id' => $id]);
$vehiculo = $stmt->fetch();

if (!$vehiculo) {
    header('Location: listar.php');
    exit;
}

// Listas para selects
$categorias = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll();
$marcas     = $pdo->query("SELECT id_marca, nombre FROM marcas ORDER BY nombre")->fetchAll();
$modelos    = $pdo->query("SELECT id_modelo, id_marca, nombre FROM modelos ORDER BY nombre")->fetchAll();
$estatus    = $pdo->query("SELECT id_estatus, nombre FROM estatus ORDER BY nombre")->fetchAll();
$vendedores = $pdo->query("SELECT id_vendedor, nombre FROM vendedor ORDER BY nombre")->fetchAll();

$id_categoria = (int)$vehiculo['id_categoria'];
$id_marca     = (int)$vehiculo['id_marca'];
$id_modelo    = (int)$vehiculo['id_modelo'];
$id_estatus   = (int)$vehiculo['id_estatus'];
$id_vendedor  = (int)$vehiculo['id_vendedor'];
$anio         = $vehiculo['año'];
$precio       = $vehiculo['precio'];
$color        = $vehiculo['color'];
$vin          = $vehiculo['vin'];
$descripcion  = $vehiculo['descripcion'];
$fotoActual   = $vehiculo['foto']; // puede ser null
$errores      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_categoria = (int)($_POST['id_categoria'] ?? 0);
    $id_marca     = (int)($_POST['id_marca'] ?? 0);
    $id_modelo    = (int)($_POST['id_modelo'] ?? 0);
    $id_estatus   = (int)($_POST['id_estatus'] ?? 0);
    $id_vendedor  = (int)($_POST['id_vendedor'] ?? 0);
    $anio         = trim($_POST['anio'] ?? '');
    $precio       = trim($_POST['precio'] ?? '');
    $color        = trim($_POST['color'] ?? '');
    $vin          = trim($_POST['vin'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');

    if ($id_categoria <= 0) $errores[] = 'Debes seleccionar una categoría.';
    if ($id_marca <= 0)     $errores[] = 'Debes seleccionar una marca.';
    if ($id_modelo <= 0)    $errores[] = 'Debes seleccionar un modelo.';
    if ($id_estatus <= 0)   $errores[] = 'Debes seleccionar un estatus.';
    if ($id_vendedor <= 0)  $errores[] = 'Debes seleccionar un vendedor.';
    if ($anio === '' || !ctype_digit($anio)) $errores[] = 'Debes indicar un año válido.';
    if ($precio === '' || !is_numeric($precio)) $errores[] = 'Debes indicar un precio válido.';

    $fotoPath = $fotoActual;

    // ¿Subieron una nueva foto?
    if (!empty($_FILES['foto']['name'])) {
        if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['foto']['tmp_name'];
            $nombreOriginal = basename($_FILES['foto']['name']);
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $extPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $extPermitidas)) {
                $errores[] = 'La foto debe ser una imagen (jpg, jpeg, png, gif, webp).';
            } else {
                $nuevoNombre = uniqid('veh_', true) . '.' . $ext;
                $uploadDir   = __DIR__ . '/../uploads/vehiculos/';
                $rutaRelativa = '/uploads/vehiculos/' . $nuevoNombre;

                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }

                if (!move_uploaded_file($tmpName, $uploadDir . $nuevoNombre)) {
                    $errores[] = 'No se pudo guardar la imagen en el servidor.';
                } else {
                    // Borrar foto anterior si existía
                    if ($fotoActual && file_exists(__DIR__ . '/..' . $fotoActual)) {
                        @unlink(__DIR__ . '/..' . $fotoActual);
                    }
                    $fotoPath = $rutaRelativa;
                }
            }
        } else {
            $errores[] = 'Error al subir la imagen (código ' . $_FILES['foto']['error'] . ').';
        }
    }

    if (empty($errores)) {
        $upd = $pdo->prepare("
            UPDATE vehiculos
            SET id_categoria = :id_categoria,
                id_marca     = :id_marca,
                id_modelo    = :id_modelo,
                id_estatus   = :id_estatus,
                id_vendedor  = :id_vendedor,
                año          = :anio,
                precio       = :precio,
                color        = :color,
                vin          = :vin,
                descripcion  = :descripcion,
                foto         = :foto
            WHERE id_vehiculo = :id
        ");
        $upd->execute([
            ':id_categoria' => $id_categoria,
            ':id_marca'     => $id_marca,
            ':id_modelo'    => $id_modelo,
            ':id_estatus'   => $id_estatus,
            ':id_vendedor'  => $id_vendedor,
            ':anio'         => (int)$anio,
            ':precio'       => (float)$precio,
            ':color'        => $color,
            ':vin'          => $vin,
            ':descripcion'  => $descripcion,
            ':foto'         => $fotoPath,
            ':id'           => $id,
        ]);

        header('Location: listar.php');
        exit;
    }
}

$titulo_pagina = 'Vehículos - Editar';
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Editar vehículo</h2>
    <a href="listar.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="row g-3" enctype="multipart/form-data">

            <div class="col-md-4">
                <label class="form-label" for="id_categoria">Categoría *</label>
                <select name="id_categoria" id="id_categoria" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id_categoria'] ?>"
                            <?= $id_categoria === (int)$c['id_categoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="id_marca">Marca *</label>
                <select name="id_marca" id="id_marca" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= $m['id_marca'] ?>"
                            <?= $id_marca === (int)$m['id_marca'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="id_modelo">Modelo *</label>
                <select name="id_modelo" id="id_modelo" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($modelos as $mo): ?>
                        <option
                            value="<?= $mo['id_modelo'] ?>"
                            data-marca="<?= $mo['id_marca'] ?>"
                            <?= $id_modelo === (int)$mo['id_modelo'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($mo['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Se filtrará según la marca seleccionada.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="id_estatus">Estatus *</label>
                <select name="id_estatus" id="id_estatus" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($estatus as $e): ?>
                        <option value="<?= $e['id_estatus'] ?>"
                            <?= $id_estatus === (int)$e['id_estatus'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="id_vendedor">Vendedor *</label>
                <select name="id_vendedor" id="id_vendedor" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($vendedores as $ve): ?>
                        <option value="<?= $ve['id_vendedor'] ?>"
                            <?= $id_vendedor === (int)$ve['id_vendedor'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ve['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label" for="anio">Año *</label>
                <input type="number" name="anio" id="anio" class="form-control"
                       value="<?= htmlspecialchars($anio) ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="precio">Precio *</label>
                <input type="number" step="0.01" name="precio" id="precio" class="form-control"
                       value="<?= htmlspecialchars($precio) ?>" required>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="color">Color</label>
                <input type="text" name="color" id="color" class="form-control"
                       value="<?= htmlspecialchars($color) ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label" for="vin">VIN</label>
                <input type="text" name="vin" id="vin" class="form-control"
                       value="<?= htmlspecialchars($vin) ?>">
            </div>

            <div class="col-12">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?= htmlspecialchars($descripcion) ?></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="foto">Foto del vehículo</label>
                <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                <div class="form-text">Si subes una nueva foto, se reemplazará la actual.</div>

                <?php if ($fotoActual): ?>
                    <div class="mt-2">
                        <p class="mb-1">Foto actual:</p>
                        <img src="..<?= htmlspecialchars($fotoActual) ?>" alt="Foto vehículo"
                             style="max-width: 150px; border-radius: 8px; border:1px solid #ddd;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Filtrar modelos por marca
document.addEventListener('DOMContentLoaded', function () {
    const selMarca   = document.getElementById('id_marca');
    const selModelo  = document.getElementById('id_modelo');
    const allOptions = Array.from(selModelo.options);

    function filtrarModelos() {
        const marcaId = selMarca.value;
        const selected = selModelo.value;
        selModelo.innerHTML = '<option value=\"\">Seleccione...</option>';

        allOptions.forEach(opt => {
            const m = opt.getAttribute('data-marca');
            if (!m) return;
            if (marcaId === '' || marcaId === m) {
                selModelo.appendChild(opt);
            }
        });

        if (selected) {
            selModelo.value = selected;
        }
    }

    selMarca.addEventListener('change', filtrarModelos);
    filtrarModelos();
});
</script>

<?php require_once '../includes/footer.php'; ?>
