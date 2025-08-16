<?php
// solumatch_atualizado/PHP/get_conversations.php

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Usuário não autenticado.']);
    exit();
}

$loggedInUserId = $_SESSION['user_id'];

try {
    // --- CONSULTA SQL CORRIGIDA E MAIS ROBUSTA ---
    // Esta nova consulta usa uma abordagem diferente (Window Function ROW_NUMBER)
    // para garantir que a última mensagem de CADA conversa (definida por usuário + vaga)
    // seja encontrada e listada corretamente.
    $sql = "
        WITH LatestMessages AS (
            SELECT
                m.*,
                ROW_NUMBER() OVER(
                    PARTITION BY LEAST(remetente_id, destinatario_id), GREATEST(remetente_id, destinatario_id), vaga_id 
                    ORDER BY data_envio DESC
                ) as rn
            FROM mensagens_diretas m
            WHERE :user_id IN (remetente_id, destinatario_id)
        )
        SELECT 
            other_user.id as other_user_id,
            other_user.nome as other_user_name,
            (CASE WHEN other_user.CNPJ IS NOT NULL AND other_user.CNPJ != '' THEN TRUE ELSE FALSE END) as other_user_is_company,
            v.id as vaga_id,
            v.titulo as vaga_titulo,
            lm.mensagem as last_message,
            lm.data_envio as last_message_time,
            (
                SELECT COUNT(*) 
                FROM mensagens_diretas 
                WHERE remetente_id = other_user.id 
                  AND destinatario_id = :user_id 
                  AND vaga_id = v.id 
                  AND lida = 0
            ) as unread_count
        FROM LatestMessages lm
        JOIN vagas v ON lm.vaga_id = v.id
        JOIN usuarios other_user ON other_user.id = IF(lm.remetente_id = :user_id, lm.destinatario_id, lm.remetente_id)
        WHERE lm.rn = 1
        ORDER BY lm.data_envio DESC;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $loggedInUserId]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($conversations, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Erro em get_conversations.php: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao buscar conversas.', 'details' => $e->getMessage()]);
}