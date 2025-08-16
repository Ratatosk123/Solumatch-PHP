<?php
// solumatch_atualizado/PHP/send_direct_message.php

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$loggedInUserId = $_SESSION['user_id'];
$destinatarioId = $input['destinatario_id'] ?? null;
$vagaId = $input['vaga_id'] ?? null;
$mensagem = trim($input['mensagem'] ?? '');

if (empty($destinatarioId) || empty($vagaId) || empty($mensagem)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit();
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO mensagens_diretas (vaga_id, remetente_id, destinatario_id, mensagem) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$vagaId, $loggedInUserId, $destinatarioId, $mensagem]);

    echo json_encode(['success' => true, 'message' => 'Mensagem enviada.']);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro em send_direct_message.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem.']);
}