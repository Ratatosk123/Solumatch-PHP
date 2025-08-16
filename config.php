<?php
// Configurações de conexão com o banco de dados
$host = 'localhost';
$dbname = 'solu_match';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $usuario, $senha);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro na conexão: " . $e->getMessage());
    die("Erro no sistema. Por favor, tente mais tarde.");
}

?>