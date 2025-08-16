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
        case 'update_profile_info':
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, numero = ?, endereco = ?, cep = ?, sobre_mim = ? WHERE id = ?");
            $stmt->execute([
                trim($_POST['name'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''),
                trim($_POST['address'] ?? ''), trim($_POST['cep'] ?? ''), trim($_POST['about_me'] ?? ''),
                $loggedInUserId
            ]);
            $response['message'] = 'Perfil atualizado com sucesso!';
            break;

        case 'update_user_skills':
            $skillsJson = $_POST['skills'] ?? '[]';
            $skillsArray = json_decode($skillsJson, true);
            if (!is_array($skillsArray)) throw new Exception("Dados de habilidades inválidos.");
            $stmtDelete = $pdo->prepare("DELETE FROM usuario_habilidades WHERE usuario_id = ?");
            $stmtDelete->execute([$loggedInUserId]);
            if (!empty($skillsArray)) {
                $stmtFind = $pdo->prepare("SELECT id FROM habilidades WHERE nome = ?");
                $stmtCreate = $pdo->prepare("INSERT INTO habilidades (nome) VALUES (?)");
                $stmtLink = $pdo->prepare("INSERT INTO usuario_habilidades (usuario_id, habilidade_id) VALUES (?, ?)");
                foreach ($skillsArray as $skillName) {
                    $trimmedSkillName = trim($skillName);
                    if (empty($trimmedSkillName)) continue;
                    $stmtFind->execute([$trimmedSkillName]);
                    $skillId = $stmtFind->fetchColumn();
                    if (!$skillId) {
                        $stmtCreate->execute([$trimmedSkillName]);
                        $skillId = $pdo->lastInsertId();
                    }
                    $stmtLink->execute([$loggedInUserId, $skillId]);
                }
            }
            $response['message'] = 'Habilidades atualizadas!';
            break;

        case 'remove_skill':
            $skillName = trim($_POST['skill_name'] ?? '');
            if(empty($skillName)) throw new Exception("Nome da Habilidade inválido.");
            $stmtDelete = $pdo->prepare("DELETE FROM usuario_habilidades WHERE usuario_id = ? AND habilidade_id = (SELECT id FROM habilidades WHERE nome = ?)");
            $stmtDelete->execute([$loggedInUserId, $skillName]);
            $response['message'] = 'Habilidade removida!';
            break;

        case 'add_experience':
        case 'update_experience':
            $id = $_POST['id'] ?? null;
            $data = [
                'titulo' => trim($_POST['titulo'] ?? ''),
                'empresa' => trim($_POST['empresa'] ?? ''),
                'data_inicio' => trim($_POST['data_inicio'] ?? ''),
                'data_fim' => empty($_POST['data_fim']) ? null : trim($_POST['data_fim']),
                'descricao' => trim($_POST['descricao'] ?? '')
            ];
            if (empty($data['titulo']) || empty($data['empresa']) || empty($data['data_inicio'])) throw new Exception("Título, Empresa e Data de Início são obrigatórios.");
            if ($action === 'add_experience') {
                $sql = "INSERT INTO experiencias (usuario_id, titulo, empresa, data_inicio, data_fim, descricao) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [$loggedInUserId, $data['titulo'], $data['empresa'], $data['data_inicio'], $data['data_fim'], $data['descricao']];
                $response['message'] = 'Experiência adicionada!';
            } else {
                $sql = "UPDATE experiencias SET titulo = ?, empresa = ?, data_inicio = ?, data_fim = ?, descricao = ? WHERE id = ? AND usuario_id = ?";
                $params = [$data['titulo'], $data['empresa'], $data['data_inicio'], $data['data_fim'], $data['descricao'], $id, $loggedInUserId];
                $response['message'] = 'Experiência atualizada!';
            }
            $pdo->prepare($sql)->execute($params);
            break;

        case 'remove_experience':
            $id = $_POST['id'] ?? null;
            if(empty($id)) throw new Exception("ID da Experiência inválido.");
            $stmt = $pdo->prepare("DELETE FROM experiencias WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $loggedInUserId]);
            $response['message'] = 'Experiência removida!';
            break;
            
        case 'add_education':
        case 'update_education':
            $id = $_POST['id'] ?? null;
            $data = [
                'titulo' => trim($_POST['titulo'] ?? ''),
                'instituicao' => trim($_POST['instituicao'] ?? ''),
                'data_inicio' => trim($_POST['data_inicio'] ?? ''),
                'data_fim' => empty($_POST['data_fim']) ? null : trim($_POST['data_fim'])
            ];
            if (empty($data['titulo']) || empty($data['instituicao']) || empty($data['data_inicio'])) throw new Exception("Título, Instituição e Data de Início são obrigatórios.");
            if ($action === 'add_education') {
                $sql = "INSERT INTO educacao (usuario_id, titulo, instituicao, data_inicio, data_fim) VALUES (?, ?, ?, ?, ?)";
                $params = [$loggedInUserId, $data['titulo'], $data['instituicao'], $data['data_inicio'], $data['data_fim']];
                $response['message'] = 'Formação adicionada!';
            } else {
                $sql = "UPDATE educacao SET titulo = ?, instituicao = ?, data_inicio = ?, data_fim = ? WHERE id = ? AND usuario_id = ?";
                $params = [$data['titulo'], $data['instituicao'], $data['data_inicio'], $data['data_fim'], $id, $loggedInUserId];
                $response['message'] = 'Formação atualizada!';
            }
            $pdo->prepare($sql)->execute($params);
            break;

        case 'remove_education':
            $id = $_POST['id'] ?? null;
            if(empty($id)) throw new Exception("ID da Formação inválido.");
            $stmt = $pdo->prepare("DELETE FROM educacao WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $loggedInUserId]);
            $response['message'] = 'Formação removida!';
            break;

        default:
            throw new Exception('Ação inválida.');
    }

    $pdo->commit();
    $response['success'] = true;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
    error_log("Erro no update_perfil.php: " . $e->getMessage());
}

echo json_encode($response);
?>