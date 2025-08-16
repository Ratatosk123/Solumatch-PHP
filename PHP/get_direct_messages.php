<?php
// solumatch_atualizado/PHP/get_direct_messages.php

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Usuário não autenticado.']);
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$otherUserId = filter_input(INPUT_GET, 'other_user_id', FILTER_VALIDATE_INT);
$vagaId = filter_input(INPUT_GET, 'vaga_id', FILTER_VALIDATE_INT);

if (!$otherUserId || !$vagaId) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros inválidos.']);
    exit();
}

try {
    // Marca as mensagens recebidas como lidas
    $updateStmt = $pdo->prepare(
        "UPDATE mensagens_diretas SET lida = TRUE WHERE vaga_id = ? AND remetente_id = ? AND destinatario_id = ?"
    );
    $updateStmt->execute([$vagaId, $otherUserId, $loggedInUserId]);
    
    // Busca o histórico de mensagens
    $stmt = $pdo->prepare("
        SELECT * FROM mensagens_diretas 
        WHERE vaga_id = :vaga_id 
        AND ((remetente_id = :user1 AND destinatario_id = :user2) OR (remetente_id = :user2 AND destinatario_id = :user1))
        ORDER BY data_envio ASC
    ");

    $stmt->execute([
        ':vaga_id' => $vagaId,
        ':user1' => $loggedInUserId,
        ':user2' => $otherUserId
    ]);
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($messages, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro em get_direct_messages.php: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao buscar mensagens.']);
}