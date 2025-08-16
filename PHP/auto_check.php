<?php
// solumatch_atualizado/PHP/auth_check.php
session_start(); // Inicia a sessão

// Verifica se a sessão 'user_id' está definida
if (!isset($_SESSION['user_id'])) {
    // Se não estiver logado, redireciona para a página de login
    header('Location: ../templates/login.php');
    exit(); // Garante que o script pare de executar após o redirecionamento
}

// Se o usuário estiver logado, o ID do usuário estará disponível em $_SESSION['user_id']
$loggedInUserId = $_SESSION['user_id'];

// Opcional: Você pode adicionar mais verificações aqui, como:
// - Verificar se o user_id existe no banco de dados.
// - Atualizar o tempo de vida da sessão.
?>