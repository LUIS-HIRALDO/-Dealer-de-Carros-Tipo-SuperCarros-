<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$rolesDisponibles = [
    'admin'     => 'Administrador',
    'vendedor'  => 'Vendedor',
    'supervisor'=> 'Supervisor',
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

// Cargar usuario
$stmt = $pdo->prepare("
    SELECT id_usuario, nombre, usuario, email, rol
    FROM usuarios
    WHERE id_usuario = :id
");
$stmt->execute([':id' => $id]);
$usuarioData = $stmt->fetch();

if (!$usuarioData) {
    header('Location: listar.php');
    exit;
}

$nombre  = $usuarioData['nombre'];
$usuario = $usuarioData['usuario'];
$email   = $usuarioData['email'];
$rol     = $usuarioData['rol'];

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $usuario  = trim($_POST['usuario'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $rol      = $_POST['rol'] ?? $rol;
    $password = $_POST['password'] ?? '';
    $password2= $_POST['password2'] ?? '';

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($usuario === '') {
        $errores[] = 'El nombre de usuario es obligatorio.';
    }
    if ($email === '') {
        $errores[] = 'El email es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email no tiene un formato válido.';
    }
    if (!isset($rolesDisponibles[$rol])) {
        $errores[] = 'El rol seleccionado no es válido.';
    }

    // Validar unicidad para este usuario
    if (empty($errores)) {
        $stmtChk = $pdo->prepare("
            SELECT COUNT(*)
            FROM usuarios
            WHERE (usuario = :usuario OR email = :email)
              AND id_usuario <> :id
        ");
        $stmtChk->execute([
            ':usuario' => $usuario,
            ':email'   => $email,
            ':id'      => $id,
        ]);
        $existe = (int)$stmtChk->fetchColumn();
        if ($existe > 0) {
            $errores[] = 'Ya existe otro usuario con ese nombre de usuario o email.';
        }
    }

    // Validar contraseña solo si se quiere cambiar
    $cambiarPassword = false;
    if ($password !== '' || $password2 !== '') {
        if ($password === '' || $password2 === '') {
            $errores[] = 'Si deseas cambiar la contraseña, debes escribir y confirmar ambos campos.';
        } elseif ($password !== $password2) {
            $errores[] = 'Las nuevas contraseñas no coinciden.';
        } else {
            $cambiarPassword = true;
        }
    }

    if (empty($errores)) {
        if ($cambiarPassword) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sqlUpd = "
                UPDATE usuarios
                SET nombre = :nombre,
                    usuario = :usuario,
                    email = :email,
                    rol = :rol,
                    password = :password
                WHERE id_usuario = :id
            ";
            $params = [
                ':nombre'   => $nombre,
                ':usuario'  => $usuario,
                ':email'    => $email,
                ':rol'      => $rol,
                ':password' => $hash,
                ':id'       => $id,
            ];
        } else {
            $sqlUpd = "
                UPDATE usuarios
                SET nombre = :nombre,
                    usuario = :usuario,
                    email = :email,
                    rol = :rol
                WHERE id_usuario = :id
            ";
            $params = [
                ':nombre'   => $nombre,
                ':usuario'  => $usuario,
                ':email'    => $email,
                ':rol'      => $rol,
                ':id'       => $id,
            ];
        }

        $upd = $pdo->prepare($sqlUpd);
        $upd->execute($params);

        header('Location: listar.php');
        exit;
    }
}

$titulo_pagina = 'Usuarios - Editar';
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Editar usuario</h2>
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

        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre completo *</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    class="form-control"
                    value="<?= htmlspecialchars($nombre) ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="usuario" class="form-label">Usuario *</label>
                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    class="form-control"
                    value="<?= htmlspecialchars($usuario) ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">Email *</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                >
            </div>

            <div class="col-md-3">
                <label for="rol" class="form-label">Rol *</label>
                <select name="rol" id="rol" class="form-select" required>
                    <?php foreach ($rolesDisponibles as $k => $et): ?>
                        <option value="<?= $k ?>" <?= $rol === $k ? 'selected' : '' ?>>
                            <?= htmlspecialchars($et) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <hr>
                <p class="mb-1"><strong>Cambiar contraseña (opcional)</strong></p>
                <small class="text-muted">
                    Deja estos campos vacíos si NO deseas cambiar la contraseña.
                </small>
            </div>

            <div class="col-md-3">
                <label for="password" class="form-label">Nueva contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                >
            </div>

            <div class="col-md-3">
                <label for="password2" class="form-label">Repetir nueva contraseña</label>
                <input
                    type="password"
                    id="password2"
                    name="password2"
                    class="form-control"
                >
            </div>

            <div class="col-12 mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
