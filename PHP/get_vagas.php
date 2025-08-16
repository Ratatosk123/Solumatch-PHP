<?php
// solumatch_atualizado/PHP/get_vagas.php

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php'; 

/**
 * Converte uma data/hora para um formato "tempo atrás".
 */
function time_ago($datetime, $full = false) {
    if ($datetime === null) {
        return 'Data indisponível';
    }
    try {
        $now = new DateTime;
        $ago = new DateTime($datetime);
    } catch (Exception $e) {
        return 'Data inválida';
    }
    
    $diff = $now->diff($ago);
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);

    $string = [
        'y' => ['value' => $diff->y, 'singular' => 'ano', 'plural' => 'anos'],
        'm' => ['value' => $diff->m, 'singular' => 'mês', 'plural' => 'meses'],
        'w' => ['value' => $weeks, 'singular' => 'semana', 'plural' => 'semanas'],
        'd' => ['value' => $days, 'singular' => 'dia', 'plural' => 'dias'],
        'h' => ['value' => $diff->h, 'singular' => 'hora', 'plural' => 'horas'],
        'i' => ['value' => $diff->i, 'singular' => 'minuto', 'plural' => 'minutos'],
        's' => ['value' => $diff->s, 'singular' => 'segundo', 'plural' => 'segundos'],
    ];
    
    $result_parts = [];
    foreach ($string as $key => $value) {
        if ($value['value'] > 0) {
            $result_parts[] = $value['value'] . ' ' . ($value['value'] > 1 ? $value['plural'] : $value['singular']);
        }
    }

    if (!$full) $result_parts = array_slice($result_parts, 0, 1);
    
    return !empty($result_parts) ? implode(', ', $result_parts) . ' atrás' : 'agora mesmo';
}

try {
    $category = $_GET['category'] ?? 'Todos';

    // A consulta com "v.*" já inclui a nova coluna, então não precisamos alterá-la.
    $sql = "SELECT v.*, u.nome as nome_empresa FROM vagas v JOIN usuarios u ON v.empresa_id = u.id";
    $params = [];

    if ($category !== 'Todos' && !empty($category)) {
        $sql .= " WHERE v.categoria = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY v.data_postagem DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vagas_do_banco = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $lista_de_jobs_formatada = [];
    foreach ($vagas_do_banco as $vaga) {
        
        $lista_de_jobs_formatada[] = [
            'id' => (int)$vaga['id'],
            'companyId' => (int)$vaga['empresa_id'],
            'companyName' => $vaga['nome_empresa'],
            'title' => $vaga['titulo'],
            'budget' => $vaga['salario'] !== null ? (float)$vaga['salario'] : 0,
            
            // =====================================================
            //     LINHA ADICIONADA PARA ENVIAR O TIPO DE ORÇAMENTO
            // =====================================================
            'tipo_orcamento' => $vaga['tipo_orcamento'], // <-- ESTA É A LINHA QUE FALTAVA

            'description' => $vaga['descricao'],
            'skills' => !empty($vaga['requisitos']) ? array_map('trim', explode(',', $vaga['requisitos'])) : [],
            'posted' => time_ago($vaga['data_postagem']),
            'proposals' => (int)$vaga['propostas_count'],
            'category' => $vaga['categoria'],
            'type' => $vaga['tipo_contratacao'], 
            'highlighted' => false
        ];
    }
    
    echo json_encode($lista_de_jobs_formatada, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
    error_log("Erro em get_vagas.php: " . $e->getMessage());
}
?>