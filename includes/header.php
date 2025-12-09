<?php
// includes/header.php
if (!isset($titulo_pagina)) {
    $titulo_pagina = 'Supercar - Panel principal';
}


$BASE_URL = '/supercar';
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

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <a class="navbar-brand" href="<?= $BASE_URL ?>/index.php">
            <img src="<?= $BASE_URL ?>/img/supercar.png" alt="Supercar" 
                 class="img-fluid" style="height:50px;">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>/categorias/listar.php">Categorías</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>/marcas/listar.php">Marcas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>/modelos/listar.php">Modelos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>/vehiculos/listar.php">Vehículos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>/vendedores/listar.php">Vendedores</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $BASE_URL ?>/usuarios/listar.php">Usuarios</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
