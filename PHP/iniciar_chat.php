<?php
// solumatch_atualizado/PHP/iniciar_chat.php

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
session_start();

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$loggedInUserId = $_SESSION['user_id'];
$vagaId = $input['vaga_id'] ?? null;
$empresaId = $input['empresa_id'] ?? null;

if (empty($vagaId) || empty($empresaId)) {
    http_response_code(400);
    $response['message'] = 'Dados incompletos para iniciar o chat.';
    echo json_encode($response);
    exit();
}

try {
    // Verifica se já existe alguma mensagem trocada para esta vaga
    $stmtCheck = $pdo->prepare(
        "SELECT id FROM mensagens_diretas 
         WHERE vaga_id = ? 
           AND ((remetente_id = ? AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = ?))"
    );
    $stmtCheck->execute([$vagaId, $loggedInUserId, $empresaId, $empresaId, $loggedInUserId]);
    
    // Se não encontrar nenhuma mensagem, insere a primeira
    if ($stmtCheck->fetchColumn() === false) {
        $initialMessage = "Olá, tenho interesse nesta vaga e gostaria de conversar.";
        
        $stmtInsert = $pdo->prepare(
            "INSERT INTO mensagens_diretas (vaga_id, remetente_id, destinatario_id, mensagem) VALUES (?, ?, ?, ?)"
        );
        $stmtInsert->execute([$vagaId, $loggedInUserId, $empresaId, $initialMessage]);
    }

    $response['success'] = true;
    $response['message'] = 'Chat iniciado ou já existente.';

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Erro de banco de dados: ' . $e->getMessage();
    error_log("Erro em iniciar_chat.php: " . $e->getMessage());
}

echo json_encode($response);