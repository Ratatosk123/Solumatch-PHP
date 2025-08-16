
<?php
// PHP/classes/Database.php

/**
 * Classe para gerenciar a conexão com o banco de dados.
 * Esta versão é adaptada para trabalhar com a conexão PDO 
 * criada no arquivo principal config.php.
 */
class Database {
    private $host;
    private $user;
    private $pass;
    private $dbName;
    private $conn;

    public function __construct($host, $user, $pass, $dbName) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->dbName = $dbName;
        $this->connect();
    }

    private function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbName);
        if ($this->conn->connect_error) {
            // Em ambiente de produção, logue o erro, mas não mostre ao usuário por segurança.
            // error_log("Falha na conexão com o banco de dados: " . $this->conn->connect_error);
            throw new Exception("Falha na conexão com o banco de dados: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
    }
}
}