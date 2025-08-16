<?php
// solumatch_atualizado/templates/reset_password.php

// Inclui o arquivo de configuração, que já estabelece a conexão $pdo
require_once __DIR__ . '/../../config.php'; // Caminho para o seu config.php
require_once __DIR__ . '/../classes/user.php';     // Caminho para a nova classe User

$message = '';
$message_type = ''; // 'success' ou 'error'
$token = $_GET['token'] ?? ''; // Obtém o token da URL
$validToken = false;
$userId = null;

// A conexão $pdo já está disponível globalmente do config.php
$user = new User($pdo); // Passa a instância PDO para a classe User


// --- Validar o token primeiro ---
if (!empty($token)) {
    $userData = $user->validateResetToken($token);
    if ($userData) {
        $validToken = true;
        $userId = $userData['id'];
    } else {
        $message = 'Link de redefinição de senha inválido ou expirado. Por favor, solicite um novo.';
        $message_type = 'error';
    }
} else {
    $message = 'Token de redefinição de senha não fornecido.';
    $message_type = 'error';
}

// --- Processar a redefinição de senha se o formulário for enviado ---
if ($validToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        $message = 'Por favor, preencha todos os campos.';
        $message_type = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'As senhas não coincidem.';
        $message_type = 'error';
    } elseif (strlen($newPassword) < 8) { // Requisito de senha: mínimo de 8 caracteres
        $message = 'A senha deve ter pelo menos 8 caracteres.';
        $message_type = 'error';
    }
    // NOVAS REGRAS DE COMPLEXIDADE (PHP):
    elseif (!preg_match('/[A-Z]/', $newPassword)) { // Pelo menos uma letra maiúscula
        $message = 'A senha deve conter pelo menos uma letra maiúscula.';
        $message_type = 'error';
    } elseif (!preg_match('/[a-z]/', $newPassword)) { // Pelo menos uma letra minúscula
        $message = 'A senha deve conter pelo menos uma letra minúscula.';
        $message_type = 'error';
    } elseif (!preg_match('/[0-9]/', $newPassword)) { // Pelo menos um número
        $message = 'A senha deve conter pelo menos um número.';
        $message_type = 'error';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $newPassword)) { // Pelo menos um caractere especial
        $message = 'A senha deve conter pelo menos um caractere especial (ex: !, @, #, $).';
        $message_type = 'error';
    }
    // FIM DAS NOVAS REGRAS DE COMPLEXIDADE
    else {
        if ($user->resetPassword($userId, $newPassword)) {
            $message = 'Sua senha foi redefinida com sucesso! Você já pode fazer login com sua nova senha.';
            $message_type = 'success';
            $validToken = false; // Invalida o formulário para evitar reuso e mostra o link de login
        } else {
            $message = 'Ocorreu um erro ao redefinir sua senha. Por favor, tente novamente.';
            $message_type = 'error';
        }
    }
}
// Não é necessário fechar a conexão $pdo aqui, pois ela é gerenciada pelo config.php.
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Solumatch</title>
    <link rel="stylesheet" href="../../CSS/resetar_senha.css">
    
</head>
<body>

      <div class="container">
        <div class="logo">
            <img src="../../misc/logo2.png" class="logo" width="50vh">
        <h2>Redefinir Senha</h2>
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($validToken): ?>
            <form action="resetar_senha.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                <div class="form-group">
                    <label for="password">Nova Senha:</label>
                    <input type="password" id="password" name="password" required>
                    <ul class="password-requirements">
                        <li id="reqLength"><span class="icon"></span> Pelo menos 8 caracteres</li>
                        <li id="reqUpper"><span class="icon"></span> Pelo menos uma letra maiúscula</li>
                        <li id="reqLower"><span class="icon"></span> Pelo menos uma letra minúscula</li>
                        <li id="reqNumber"><span class="icon"></span> Pelo menos um número</li>
                        <li id="reqSpecial"><span class="icon"></span> Pelo menos um caractere especial</li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Senha:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Redefinir Senha</button>
            </form>
        <?php elseif ($message_type === 'success'): ?>
             <p><a href="../../templates/login.php">Ir para a página de Login</a></p>
        <?php else: ?>
            <p>Se você precisa de um novo link de redefinição, por favor, <a href="esqueci_senha.php">clique aqui</a>.</p>
        <?php endif; ?>
    </div>

    <script src="../../JavaScript/resetar_senha.js"></script>
</body>
</html>