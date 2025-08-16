<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../config.php';

// Retorna uma lista vazia se o usuário não estiver logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Busca as 10 mensagens não lidas mais recentes para o usuário logado.
    // Ele junta com a tabela de usuários para pegar o nome do remetente.
    $sql = "SELECT 
                m.vaga_id,
                m.remetente_id,
                u.nome as remetente_nome,
                m.mensagem
            FROM mensagens_diretas m
            JOIN usuarios u ON m.remetente_id = u.id
            WHERE m.destinatario_id = ? AND m.lida = FALSE 
            ORDER BY m.data_envio DESC
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Envia os resultados como JSON
    echo json_encode($notifications, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Em caso de erro, retorna uma lista vazia para não quebrar o frontend
    http_response_code(500);
    error_log("Erro em get_notifications.php: " . $e->getMessage());
    echo json_encode([]);
}
?>