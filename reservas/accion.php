<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// CSRF
if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400);
    exit('Token CSRF inválido');
}

$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($action === 'cancel' && $id > 0) {
    try {
        $pdo->beginTransaction();
        // load reservation
        $stmt = $pdo->prepare('SELECT * FROM reservas WHERE id_reserva = :id FOR UPDATE');
        $stmt->execute([':id'=>$id]);
        $r = $stmt->fetch();
        if (!$r) {
            $pdo->rollBack();
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/reservas/listar.php?error=not_found");
            exit;
        }

        if ($r['estado'] != 1) {
            $pdo->rollBack();
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/reservas/listar.php?msg=already_cancelled");
            exit;
        }

        // mark reservation cancelled
        $upd = $pdo->prepare('UPDATE reservas SET estado = 0 WHERE id_reserva = :id');
        $upd->execute([':id'=>$id]);

        // revert vehicle status to Disponible (1) if it's currently Reservado (2)
        if (!empty($r['id_vehiculo'])) {
            $u2 = $pdo->prepare('UPDATE vehiculos SET id_estatus = 1 WHERE id_vehiculo = :id AND id_estatus = 2');
            $u2->execute([':id'=>$r['id_vehiculo']]);
        }

        $pdo->commit();
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/reservas/listar.php?msg=cancelled");
        exit;
    } catch (Exception $e) {
        try { $pdo->rollBack(); } catch (Exception $e2) {}
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/reservas/listar.php?error=internal");
        exit;
    }
}

header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . "/reservas/listar.php");
exit;
