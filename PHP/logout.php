<?php
// solumatch_atualizado/PHP/logout.php
session_start(); // Inicia a sessão

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Se a sessão for controlada por cookies, exclui o cookie da sessão.
// Nota: Isso irá destruir o cookie de sessão, não apenas os dados da sessão.
// Isso garante que uma nova sessão seja iniciada na próxima vez que o usuário acessar a página.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão.
session_destroy();

// Redireciona para a página de login ou home
header('Location: ../templates/login.php'); // Ou home.html/home.php
exit();
?>