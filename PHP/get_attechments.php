<?php
// solumatch_atualizado/PHP/upload_chat_attachment.php

header('Content-Type: application/json; charset=utf-8');
require_once '../config.php';
session_start();

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    $response['message'] = 'Utilizador não autenticado.';
    echo json_encode($response);
    exit();
}

// Verifica se os dados necessários foram enviados via POST e FILES
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['attachment'])) {
    http_response_code(400);
    $response['message'] = 'Requisição inválida.';
    echo json_encode($response);
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$destinatarioId = $_POST['destinatario_id'] ?? null;
$vagaId = $_POST['vaga_id'] ?? null;
$file = $_FILES['attachment'];

if (empty($destinatarioId) || empty($vagaId)) {
    http_response_code(400);
    $response['message'] = 'Dados da conversa em falta.';
    echo json_encode($response);
    exit();
}

// Validações do ficheiro
if ($file['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'Erro no upload do ficheiro. Código: ' . $file['error'];
    http_response_code(500);
    echo json_encode($response);
    exit();
}

$maxSize = 10 * 1024 * 1024; // 10 MB
if ($file['size'] > $maxSize) {
    $response['message'] = 'Ficheiro demasiado grande. O tamanho máximo é 10MB.';
    http_response_code(413);
    echo json_encode($response);
    exit();
}

try {
    // Define o diretório de uploads e cria-o se não existir
    $uploadDir = __DIR__ . '/../uploads/chat_attachments/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Gera um nome de ficheiro único para evitar conflitos
    $originalName = basename($file['name']);
    $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
    $uniqueName = uniqid() . '-' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
    $destination = $uploadDir . $uniqueName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Se o upload foi bem-sucedido, guarda a referência na base de dados
        // Usamos um formato especial para identificar a mensagem como um ficheiro
        $messageContent = "[arquivo:" . $uniqueName . "|" . $originalName . "]";

        $stmt = $pdo->prepare(
            "INSERT INTO mensagens_diretas (vaga_id, remetente_id, destinatario_id, mensagem) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$vagaId, $loggedInUserId, $destinatarioId, $messageContent]);

        $response['success'] = true;
        $response['message'] = 'Ficheiro enviado com sucesso!';
    } else {
        throw new Exception('Falha ao mover o ficheiro para o destino.');
    }

} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
    error_log("Erro em upload_chat_attachment.php: " . $e->getMessage());
}

echo json_encode($response);

?>