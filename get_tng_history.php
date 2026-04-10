<?php
require_once __DIR__ . '/../config/helpers.php';
session_start();

$admin = $_SESSION['user'] ?? null;
if (!$admin || $admin['role'] !== 'admin') {
    respond(false, 'Unauthorized');
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT 
        admin_name,
        old_qr_path,
        new_qr_path,
        old_receiver_name,
        new_receiver_name,
        changed_at
    FROM tng_qr_history
    ORDER BY changed_at DESC
    LIMIT 20
");
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

respond(true, 'OK', ['history' => $history]);