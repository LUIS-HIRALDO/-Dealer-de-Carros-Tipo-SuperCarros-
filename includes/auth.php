<?php
require_once __DIR__ . '/../config/db.php';


if (empty($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

