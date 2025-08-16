<?php

class ChatHistory {
    private $db;

    public function __construct(mysqli $dbConnection) {
        $this->db = $dbConnection;
    }

    public function getHistory($sessionId) {
        $history = [];
        $stmt = $this->db->prepare("SELECT role, message FROM historico_chat WHERE sessao_id = ? ORDER BY timestamp ASC");
        if ($stmt === false) {
            error_log("Erro na preparação da consulta de histórico: " . $this->db->error);
            return [];
        }
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $history[] = [
                'role' => $row['role'],
                'parts' => [['text' => $row['message']]]
            ];
        }
        $stmt->close();
        return $history;
    }

    public function getCurrentMenuId($sessionId) {
        $currentMenuId = 'main_menu'; // Default
        $stmt = $this->db->prepare("SELECT current_menu_id FROM historico_chat WHERE sessao_id = ? ORDER BY timestamp DESC LIMIT 1");
        if ($stmt === false) {
            error_log("Erro na preparação da consulta de estado do menu: " . $this->db->error);
            return $currentMenuId;
        }
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $currentMenuId = $row['current_menu_id'] ?? 'main_menu';
        }
        $stmt->close();
        return $currentMenuId;
    }

    public function saveMessage($sessionId, $role, $message, $currentMenuId) {
        $stmt = $this->db->prepare("INSERT INTO historico_chat (sessao_id, role, message, current_menu_id) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            error_log("Erro na preparação da consulta de inserção: " . $this->db->error);
            return false;
        }
        $stmt->bind_param("ssss", $sessionId, $role, $message, $currentMenuId);
        $success = $stmt->execute();
        $stmt->close();
        if (!$success) {
            error_log("Erro ao salvar mensagem no histórico: " . $stmt->error);
        }
        return $success;
    }
}