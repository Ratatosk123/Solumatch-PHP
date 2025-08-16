<?php
// solumatch_atualizado/PHP/upload_perfil_check.php
header('Content-Type: application/json'); // Garante que a resposta será JSON

session_start(); // Inicie a sessão para obter o ID do usuário logado

// Inclua o arquivo de conexão com o banco de dados
// O config.php já tem a conexão PDO configurada
require_once '../config.php'; // Ajuste o caminho conforme sua estrutura

$response = ['success' => false, 'message' => ''];

// **IMPORTANTE**: Obtenha o user_id da sessão
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit();
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // Validações básicas do arquivo
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        $response['message'] = 'Tipo de arquivo não permitido. Apenas JPG, PNG, GIF.';
    } elseif ($file['size'] > $max_size) {
        $response['message'] = 'O arquivo é muito grande. Tamanho máximo: 5MB.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Erro no upload do arquivo: ' . $file['error'];
    } else {
        // Obter informações do arquivo
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        // Gerar um nome de arquivo único para evitar colisões
        $new_file_name = uniqid('profile_') . '.' . $file_extension;
        $upload_dir = '../uploads/profile_pictures/'; // Caminho relativo ao script PHP

        // Cria o diretório se não existir
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Permissões 0755, true para criar recursivamente
        }

        $destination_path = $upload_dir . $new_file_name;

        // Move o arquivo para o diretório de uploads
        if (move_uploaded_file($file['tmp_name'], $destination_path)) {
            // Atualizar o caminho da imagem no banco de dados
            try {
                // Antes de salvar o novo, você pode querer excluir o antigo se existir
                // CORREÇÃO AQUI: Altere 'users' para 'usuarios'
                $stmt_old_pic = $pdo->prepare("SELECT profile_picture FROM usuarios WHERE id = :user_id");
                $stmt_old_pic->execute([':user_id' => $user_id]);
                $old_picture = $stmt_old_pic->fetchColumn();

                if ($old_picture && $old_picture !== 'default_profile.jpg' && file_exists($upload_dir . $old_picture)) {
                    unlink($upload_dir . $old_picture); // Exclui o arquivo antigo
                }

                // CORREÇÃO AQUI: Altere 'users' para 'usuarios'
                $stmt = $pdo->prepare("UPDATE usuarios SET profile_picture = :profile_picture WHERE id = :user_id");
                $stmt->execute([
                    ':profile_picture' => $new_file_name, // Salvamos apenas o nome do arquivo
                    ':user_id' => $user_id
                ]);

                $response['success'] = true;
                $response['message'] = 'Foto de perfil atualizada com sucesso!';
                // Retorne o caminho completo se o JS precisar para atualizar diretamente
                $response['new_path'] = '../' . $destination_path;

            } catch (PDOException $e) {
                $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
                // Se houve erro no DB, apague o arquivo que acabou de ser movido
                if (file_exists($destination_path)) {
                    unlink($destination_path);
                }
            }
        } else {
            $response['message'] = 'Falha ao mover o arquivo enviado.';
        }
    }
} else {
    $response['message'] = 'Nenhum arquivo enviado ou método inválido.';
}

echo json_encode($response);
?>