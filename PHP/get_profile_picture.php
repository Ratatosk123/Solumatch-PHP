<?php
// solumatch_atualizado/PHP/get_profile_picture.php

require_once '../config.php';
session_start();

// Pega o ID do usuário da URL, ou do usuário logado como fallback
$userId = $_GET['id'] ?? $_SESSION['user_id'] ?? null;

if (!$userId) {
    http_response_code(400); // Bad Request
    exit('ID de usuário não fornecido.');
}

try {
    $stmt = $pdo->prepare("SELECT profile_picture, profile_picture_type FROM usuarios WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && !empty($user['profile_picture']) && !empty($user['profile_picture_type'])) {
        // Envia o cabeçalho de tipo de conteúdo para o navegador
        header('Content-Type: ' . $user['profile_picture_type']);
        // Envia os dados binários da imagem
        echo $user['profile_picture'];
    } else {
        // Se não houver imagem, envia a imagem padrão
        $defaultImagePath = '../misc/Perfil_imagem.jpg';
        header('Content-Type: image/jpeg');
        readfile($defaultImagePath);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    error_log("Erro ao buscar imagem do banco: " . $e->getMessage());
    exit('Erro ao carregar imagem.');
}
?>