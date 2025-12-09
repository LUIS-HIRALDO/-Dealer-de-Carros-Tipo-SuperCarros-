<?php
require_once __DIR__ . '/../includes/auth.php'; 
require_once '../config/db.php';

$rolesDisponibles = [
    'admin'     => 'Administrador',
    'vendedor'  => 'Vendedor',
    'supervisor'=> 'Supervisor',
];

$titulo_pagina = 'Usuarios - Crear';
require_once '../includes/header.php';

$nombre   = '';
$usuario  = '';
$email    = '';
$rol      = 'vendedor';
$errores  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $usuario  = trim($_POST['usuario'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $rol      = $_POST['rol'] ?? 'vendedor';
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
    if ($password === '' || $password2 === '') {
        $errores[] = 'Debes escribir y confirmar la contraseña.';
    } elseif ($password !== $password2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }
    if (!isset($rolesDisponibles[$rol])) {
        $errores[] = 'El rol seleccionado no es válido.';
    }

    // Validar unicidad (usuario / email)
    if (empty($errores)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario OR email = :email");
        $stmt->execute([
            ':usuario' => $usuario,
            ':email'   => $email,
        ]);
        $existe = (int)$stmt->fetchColumn();
        if ($existe > 0) {
            $errores[] = 'Ya existe un usuario con ese nombre de usuario o email.';
        }
    }

    if (empty($errores)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, usuario, email, password, rol)
            VALUES (:nombre, :usuario, :email, :password, :rol)
        ");
        $stmt->execute([
            ':nombre'   => $nombre,
            ':usuario'  => $usuario,
            ':email'    => $email,
            ':password' => $hash,
            ':rol'      => $rol,
        ]);

        header('Location: listar.php');
        exit;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Nuevo usuario</h2>
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

            <div class="col-md-3">
                <label for="password" class="form-label">Contraseña *</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    required
                >
            </div>

            <div class="col-md-3">
                <label for="password2" class="form-label">Repetir contraseña *</label>
                <input
                    type="password"
                    id="password2"
                    name="password2"
                    class="form-control"
                    required
                >
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
