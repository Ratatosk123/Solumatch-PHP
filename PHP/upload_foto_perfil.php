<?php
// solumatch_atualizado/PHP/upload_foto_perfil.php

header('Content-Type: application/json');
session_start();
require_once '../config.php';

$response = ['success' => false, 'message' => ''];

// --- VERIFICAÇÃO 1: Autenticação ---
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Erro de Autenticação: Sessão de usuário não encontrada.';
    echo json_encode($response);
    exit();
}
$user_id = $_SESSION['user_id'];

// --- VERIFICAÇÃO 2: Método e Arquivo ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['profile_picture'])) {
    $response['message'] = 'Erro de Requisição: Nenhum arquivo enviado ou método inválido.';
    echo json_encode($response);
    exit();
}

$file = $_FILES['profile_picture'];

// --- VERIFICAÇÃO 3: Erros de Upload do PHP ---
if ($file['error'] !== UPLOAD_ERR_OK) {
    // Converte o código de erro em uma mensagem legível
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => 'O arquivo excede o limite de tamanho definido no servidor (upload_max_filesize).',
        UPLOAD_ERR_FORM_SIZE  => 'O arquivo excede o limite de tamanho definido no formulário.',
        UPLOAD_ERR_PARTIAL    => 'O upload do arquivo foi feito apenas parcialmente.',
        UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi enviado.',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta uma pasta temporária no servidor.',
        UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
        UPLOAD_ERR_EXTENSION  => 'Uma extensão do PHP interrompeu o upload do arquivo.',
    ];
    $error_message = $upload_errors[$file['error']] ?? 'Erro desconhecido no upload.';
    $response['message'] = "Erro de Servidor: $error_message";
    echo json_encode($response);
    exit();
}

// --- VERIFICAÇÃO 4: Validações de Tipo e Tamanho do Arquivo ---
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    $response['message'] = 'Tipo de arquivo não permitido. Envie apenas JPG, PNG ou GIF.';
} elseif ($file['size'] > $max_size) {
    $response['message'] = 'O arquivo é muito grande. O tamanho máximo permitido é 5MB.';
} else {
    // --- SE TUDO ESTIVER OK, PROSSEGUE PARA O BANCO DE DADOS ---
    try {
        // LÊ O CONTEÚDO BINÁRIO DO ARQUIVO E O TIPO MIME
        $imageData = file_get_contents($file['tmp_name']);
        $imageType = $file['type'];

        // PREPARA E EXECUTA A ATUALIZAÇÃO NO BANCO DE DADOS
        $stmt = $pdo->prepare(
            "UPDATE usuarios SET profile_picture = :profile_picture, profile_picture_type = :image_type WHERE id = :user_id"
        );

        // Associa os parâmetros, especificando que profile_picture é um BLOB
        $stmt->bindParam(':profile_picture', $imageData, PDO::PARAM_LOB);
        $stmt->bindParam(':image_type', $imageType, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        
        $stmt->execute();

        // Verifica se a linha foi de fato atualizada
        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Foto de perfil atualizada com sucesso!';
        } else {
            // Isso pode acontecer se o ID do usuário não existir, o que é improvável aqui
            throw new PDOException("Nenhuma linha foi atualizada. O usuário com ID $user_id pode não existir.");
        }

    } catch (PDOException $e) {
        $response['message'] = 'Erro no banco de dados. Verifique se a estrutura da tabela está correta. Detalhe: ' . $e->getMessage();
        // Log do erro para depuração no servidor
        error_log("Erro no upload de BLOB para o usuário ID $user_id: " . $e->getMessage());
    }
}

echo json_encode($response);
?>