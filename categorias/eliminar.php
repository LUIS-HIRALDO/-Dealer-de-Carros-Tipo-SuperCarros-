<?php
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

// Opción simple: eliminar directo y redirigir
$stmt = $pdo->prepare("DELETE FROM categorias WHERE id_categoria = :id");
$stmt->execute([':id' => $id]);

header('Location: listar.php');
exit;
