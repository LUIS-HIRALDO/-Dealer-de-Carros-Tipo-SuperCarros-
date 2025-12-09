<?php
require_once __DIR__ . '/config/db.php';

if (!empty($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$usuario  = '';
$errores  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $errores[] = 'Debes ingresar usuario y contraseña.';
    } else {
        $sql = "SELECT * FROM usuarios WHERE usuario = :user OR email = :user LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user' => $usuario]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row['password'])) {

            $_SESSION['usuario'] = [
                'id'     => $row['id_usuario'],
                'nombre' => $row['nombre'],
                'user'   => $row['usuario'],
                'email'  => $row['email'],
                'rol'    => $row['rol'],
            ];

            header('Location: ' . BASE_URL . '/index.php');
            exit;
        } else {
            $errores[] = 'Usuario o contraseña incorrectos.';
        }
    }
}

$titulo_pagina = 'Supercar - Iniciar sesión';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/estilos.css">

    <style>
        body {
            background-color: #0d0d0d;
            color: #f1f1f1;
        }

        .login-card {
            background-color: #1a1a1a;
            border: 1px solid #333;
        }

        .login-card input {
            background-color: #111;
            border: 1px solid #444;
            color: #fff;
        }

        .login-card input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 5px #0d6efd55;
        }

        .btn-login {
            background-color: #0d6efd;
            border: none;
        }

        .btn-login:hover {
            background-color: #0b5ed7;
        }

        label {
            font-weight: 500;
        }

        .login-logo {
            filter: brightness(1.2);
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="card shadow login-card p-4" style="max-width: 420px; width:100%;">
        <div class="text-center mb-3">
            <img src="<?= BASE_URL ?>/img/supercar.png" alt="Supercar" 
                 style="height:70px;" class="login-logo">
        </div>

        <h5 class="text-center mb-3 text-white">Iniciar sesión</h5>

        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errores as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <!-- debug removed: no debug flag output in production -->
            <div class="mb-3">
                <label for="usuario" class="form-label text-white">Usuario o email</label>
                <input type="text" name="usuario" id="usuario"
                       class="form-control"
                       value="<?= htmlspecialchars($usuario) ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-white">Contraseña</label>
                <input type="password" name="password" id="password"
                       class="form-control" required>
            </div>

            <button type="submit" class="btn btn-login w-100 py-2 mt-2">
                <i class="bi bi-box-arrow-in-right"></i> Entrar
            </button>
        </form>

        <div class="text-center mt-3">
            <small class="text-secondary">&copy; <?= date('Y') ?> Supercar</small>
        </div>
    </div>
</div>

</body>
</html>
