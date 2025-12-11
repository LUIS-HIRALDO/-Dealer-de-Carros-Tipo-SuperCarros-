<?php
// includes/header_public.php - cabecera pública para clientes (sin menú admin)
if (!isset($titulo_pagina)) {
    $titulo_pagina = 'Supercar - Catálogo';
}

$BASE_URL = defined('BASE_URL') ? BASE_URL : '/supercar';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?></title>

    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/css/animations.css">
    <style>
        /* Pequeños ajustes estéticos para catálogo */
        .hero {
            background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(58,123,213,1) 35%, rgba(0,212,255,1) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .card-img-top { transition: transform .25s ease; }
        .card:hover .card-img-top { transform: scale(1.03); }
        /* Forzar icono en color oscuro para que se vea sobre fondos claros */
        .brand-logo {
            height:44px;
            filter: brightness(0) saturate(100%);
            /* fallback if SVG or PNG has hard colors */
        }
        /* Hacer que la marca no sea un enlace clickable (usar un contenedor en su lugar) */
        .navbar-brand.no-link { text-decoration: none; cursor:default; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">

        <div class="navbar-brand d-flex align-items-center gap-2 no-link">
            <img src="<?= $BASE_URL ?>/img/supercar.png" alt="Supercar" class="brand-logo">
            <span class="fw-bold">Supercar</span>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic" aria-controls="navbarPublic" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarPublic">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>/catalogo.php">Catálogo</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>/contacto.php">Contacto</a></li>

            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (!empty($_SESSION['usuario'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? $_SESSION['usuario']['user'] ?? 'Usuario') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?= $BASE_URL ?>/usuarios/editar.php?id=<?= urlencode($_SESSION['usuario']['id'] ?? '') ?>">Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $BASE_URL ?>/logout.php">Cerrar sesión</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-outline-primary" href="<?= $BASE_URL ?>/login.php">Entrar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">

<?php
// fin de header_public
?>
