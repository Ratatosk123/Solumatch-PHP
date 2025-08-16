<?php
// solumatch_atualizado/templates/forgot_password.php

// Inclui o arquivo de configuração, que já estabelece a conexão $pdo
require_once __DIR__ . '/../../config.php'; // Caminho para o seu config.php
require_once __DIR__ . '/../classes/user.php';     // Caminho para a nova classe User

$message = '';
$message_type = ''; // 'success' ou 'error'

// A conexão $pdo já está disponível globalmente do config.php
$user = new User($pdo); // Passa a instância PDO para a classe User

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login'] ?? ''); // Pode ser e-mail ou número

    if (empty($login_input)) {
        $message = 'Por favor, insira seu e-mail cadastrado.';
        $message_type = 'error';
    } else {
        $userData = $user->findUserByLogin($login_input);

        if ($userData) {
            $token = $user->generateResetToken($userData['id']);

            if ($token) {
                // Montar o link de redefinição de senha
                $resetLink = "http://localhost/solumatch_atualizado/PHP/public/resetar_senha.php?token=" . $token;

                // --- INÍCIO DA ALTERAÇÃO ---
                // Agora, em vez de mostrar a URL completa, mostramos um texto amigável.
                // Esta é a forma correta e mais profissional de exibir o link.
                $message = "Um link para redefinição de senha foi enviado para o seu e-mail (se cadastrado). Por favor, verifique sua caixa de entrada e a pasta de spam. <br><br><strong>Link para resetar:</strong> <a href=\"{$resetLink}\" target=\"_blank\">Clique aqui para redefinir a senha</a>";
                $message_type = 'success';
                // --- FIM DA ALTERAÇÃO ---

            } else {
                $message = "Ocorreu um erro ao gerar o link de recuperação. Por favor, tente novamente.";
                $message_type = 'error';
                error_log("Falha ao gerar token para o usuário ID: " . $userData['id']);
            }
        } else {
            // Mensagem genérica para segurança, mesmo que o e-mail/número não exista.
            $message = "Se existir uma conta associada a este e-mail, um link para redefinição de senha foi enviado. Por favor, verifique sua caixa de entrada e a pasta de spam.";
            $message_type = 'success';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Senha - SoluMatch</title>
<link rel="stylesheet" href="../../CSS/esqueci_senha.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="../../misc/logo2.png" alt="Logo SoluMatch" class="logo" width="50vh">
        </div>
        <h2>Recuperar Senha</h2>
        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo $message; // Agora a mensagem contém HTML, mas é seguro porque nós o controlamos. ?>
            </div>
        <?php endif; ?>
        <form action="esqueci_senha.php" method="POST">
            <div class="form-group">
                <label for="login">E-mail Cadastrado:</label>
                <input type="text" id="login" name="login" required placeholder="Digite seu e-mail">
            </div>
            <button type="submit">Enviar Link de Recuperação</button>
        </form>
        <p style="margin-top: 20px;"><a href="../../templates/login.php">Voltar para o Login</a></p>
    </div>
</body>
</html>