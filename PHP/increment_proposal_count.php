<?php
// solumatch_atualizado/PHP/increment_proposal_count.php

header('Content-Type: application/json');
require_once '../config.php';

// Pega os dados enviados pelo JavaScript
$input = json_decode(file_get_contents('php://input'), true);
$vagaId = $input['vaga_id'] ?? null;

if (!$vagaId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da vaga não fornecido.']);
    exit();
}

try {
    // Prepara e executa o comando para incrementar a contagem em 1
    $stmt = $pdo->prepare(
        "UPDATE vagas SET propostas_count = propostas_count + 1 WHERE id = ?"
    );
    $stmt->execute([$vagaId]);
    
    // Verifica se alguma linha foi de fato atualizada
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Contagem de propostas incrementada.']);
    } else {
        throw new Exception('Nenhuma vaga encontrada com o ID fornecido.');
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro em increment_proposal_count.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>