<?php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    $pdo->beginTransaction();

    switch ($action) {
        case 'add_vaga':
        case 'update_vaga':
            $id = $_POST['id'] ?? null;
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $requisitos = trim($_POST['requisitos'] ?? '');
            $categoria = trim($_POST['categoria'] ?? '');
            $tipo_contratacao = trim($_POST['tipo_contratacao'] ?? '');
            $localizacao = trim($_POST['localizacao'] ?? '');
            $salario = !empty($_POST['salario']) ? (float)$_POST['salario'] : null;
            // Pegando o novo campo
            $tipo_orcamento = trim($_POST['tipo_orcamento'] ?? 'fixo');

            if (empty($titulo) || empty($descricao) || empty($tipo_contratacao) || empty($categoria)) {
                throw new Exception("Título, Descrição, Categoria e Tipo de Contratação são obrigatórios.");
            }
             // Validação para o novo campo
            if (!in_array($tipo_orcamento, ['por_hora', 'fixo'])) {
                throw new Exception("Tipo de orçamento inválido.");
            }

            if ($action === 'add_vaga') {
                // Adicionando 'tipo_orcamento' ao INSERT
                $sql = "INSERT INTO vagas (empresa_id, titulo, descricao, requisitos, categoria, tipo_contratacao, localizacao, salario, tipo_orcamento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [ $loggedInUserId, $titulo, $descricao, $requisitos, $categoria, $tipo_contratacao, $localizacao, $salario, $tipo_orcamento ];
                $response['message'] = 'Vaga postada com sucesso!';
            } else {
                if (empty($id)) throw new Exception("ID da vaga é necessário para atualização.");
                // Adicionando 'tipo_orcamento' ao UPDATE
                $sql = "UPDATE vagas SET titulo = ?, descricao = ?, requisitos = ?, categoria = ?, tipo_contratacao = ?, localizacao = ?, salario = ?, tipo_orcamento = ? WHERE id = ? AND empresa_id = ?";
                $params = [ $titulo, $descricao, $requisitos, $categoria, $tipo_contratacao, $localizacao, $salario, $tipo_orcamento, $id, $loggedInUserId ];
                $response['message'] = 'Vaga atualizada com sucesso!';
            }
            $pdo->prepare($sql)->execute($params);
            break;

        case 'delete_vaga':
            $id = $_POST['id'] ?? null;
            if(empty($id)) throw new Exception("ID da Vaga inválido.");
            $stmt = $pdo->prepare("DELETE FROM vagas WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$id, $loggedInUserId]);
            $response['message'] = 'Vaga removida com sucesso!';
            break;

        default:
            throw new Exception('Ação inválida.');
    }

    $pdo->commit();
    $response['success'] = true;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['message'] = $e->getMessage();
    error_log("Erro no vagas_handler.php: " . $e->getMessage());
}

echo json_encode($response);
?>