<?php
// solumatch_atualizado/PHP/classes/User.php

// Não precisamos incluir Database.php aqui, pois User.php usará a conexão PDO diretamente.

class User {
    private $pdo; // Usaremos PDO aqui

    public function __construct(PDO $pdoConnection) {
        $this->pdo = $pdoConnection;
    }

    /**
     * Encontra um usuário pelo e-mail ou número de telefone.
     * @param string $login O e-mail ou número de telefone do usuário.
     * @return array|null Retorna os dados do usuário ou null se não encontrado.
     */
    public function findUserByLogin($login) {
        // Verifica se é e-mail ou número de telefone para selecionar o campo correto
        $campo = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'numero';
        
        try {
            // Utilizando PDO para prepared statements
            $stmt = $this->pdo->prepare("SELECT id, email, senha_hash, reset_token, reset_token_expiry FROM usuarios WHERE $campo = ?");
            $stmt->execute([$login]); // Passa os parâmetros diretamente no execute
            $user = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém o resultado como array associativo
            return $user;
        } catch (PDOException $e) {
            error_log("Erro na consulta de usuário (PDO): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Gera e salva um token de recuperação de senha para um usuário.
     * @param int $userId O ID do usuário.
     * @return string O token gerado.
     */
    public function generateResetToken($userId) {
        try {
            $token = bin2hex(random_bytes(32)); // Gerar um token único e aleatório (64 caracteres hexadecimais)
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expira em 1 hora

            // Utilizando PDO para prepared statements
            $stmt = $this->pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $success = $stmt->execute([$token, $expiry, $userId]); // Passa os parâmetros diretamente no execute

            return $success ? $token : false;
        } catch (PDOException $e) {
            error_log("Erro na atualização do token (PDO): " . $e->getMessage());
            return false;
        } catch (Exception $e) { // Para random_bytes (pode lançar exceção se não houver fonte de entropia suficiente)
            error_log("Erro ao gerar bytes aleatórios para token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida um token de recuperação de senha.
     * @param string $token O token a ser validado.
     * @return array|null Retorna os dados do usuário se o token for válido e não expirado, ou null.
     */
    public function validateResetToken($token) {
        $currentTime = date('Y-m-d H:i:s');
        try {
            // Utilizando PDO para prepared statements
            $stmt = $this->pdo->prepare("SELECT id, email FROM usuarios WHERE reset_token = ? AND reset_token_expiry > ?");
            $stmt->execute([$token, $currentTime]); // Passa os parâmetros diretamente no execute
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        } catch (PDOException $e) {
            error_log("Erro na validação do token (PDO): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Redefine a senha de um usuário e invalida o token de recuperação.
     * @param int $userId O ID do usuário.
     * @param string $newPassword A nova senha (será hashed).
     * @return bool True em caso de sucesso, false caso contrário.
     */
    public function resetPassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            // Invalida o token definindo-o como NULL e a expiração no passado
            // Utilizando PDO para prepared statements
            $stmt = $this->pdo->prepare("UPDATE usuarios SET senha_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $success = $stmt->execute([$hashedPassword, $userId]); // Passa os parâmetros diretamente no execute
            return $success;
        } catch (PDOException $e) {
            error_log("Erro na redefinição de senha (PDO): " . $e->getMessage());
            return false;
        }
    }
}