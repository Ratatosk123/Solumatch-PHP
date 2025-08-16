<?php
// Define que a resposta será no formato JSON
header('Content-Type: application/json');

// Inclui o arquivo de conexão com o banco de dados
require_once '../config.php'; 

// Prepara a resposta padrão
$response = ['success' => false, 'message' => '', 'skills' => []];

try {
    // CORRETO: Seleciona a coluna "nome" da tabela "habilidades"
    $stmt = $pdo->query("SELECT nome FROM habilidades ORDER BY nome ASC");
    
    // Pega todos os resultados da coluna 'nome' em um array simples
    $allSkills = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 

    // Se a consulta foi bem-sucedida, atualiza a resposta
    $response['success'] = true;
    $response['skills'] = $allSkills;

} catch (PDOException $e) {
    // Se ocorrer um erro no banco de dados, registra na resposta
    $response['message'] = 'Erro ao buscar habilidades: ' . $e->getMessage();
    error_log("Erro no skills.php: " . $e->getMessage());
}

// Envia a resposta final em formato JSON
echo json_encode($response);
?>