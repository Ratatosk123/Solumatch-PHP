<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread_count' => 0]);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $sql = "SELECT COUNT(id) as unread_count FROM mensagens_diretas WHERE destinatario_id = ? AND lida = FALSE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['unread_count' => 0]);
}
?>