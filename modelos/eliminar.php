<?php
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM modelos WHERE id_modelo = :id");
$stmt->execute([':id' => $id]);

header('Location: listar.php');
exit;
