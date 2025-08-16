<?php
// PHP/classes/Chatbot.php

require_once 'database.php';
require_once 'historicochat.php';
require_once 'sacmenu.php';

class Chatbot {
    private $db;
    private $chatHistory;
    private $sacMenu;
    private $geminiApiKey;
    private $geminiApiUrl;

    public function __construct(mysqli $dbConnection, $geminiApiKey, $geminiApiUrl) {
        $this->db = $dbConnection;
        $this->chatHistory = new ChatHistory($dbConnection);
        $this->sacMenu = new SacMenu();
        $this->geminiApiKey = $geminiApiKey;
        $this->geminiApiUrl = $geminiApiUrl;
    }

    public function processMessage($userMessage, $sessionId) {
        $botReply = '';
        $currentMenuId = $this->chatHistory->getCurrentMenuId($sessionId);

        // Salvar a mensagem do usuário antes de processá-la
        $this->chatHistory->saveMessage($sessionId, 'user', $userMessage, $currentMenuId);

        // Lógica de "reset" para o menu principal se o usuário digitar algo como "menu"
        $userMessageLower = mb_strtolower(trim($userMessage), 'UTF-8');
        if ($userMessageLower === 'menu' || $userMessageLower === 'iniciar' || $userMessageLower === 'começar' || $userMessageLower === 'voltar ao menu principal') {
            $currentMenuId = 'main_menu';
            $botReply = "Certo! Aqui está o menu principal:\n\n" . $this->sacMenu->getMenuText($currentMenuId);
        } else {
            // Tenta encontrar a opção na mensagem do usuário no menu atual
            $selectedOption = $this->sacMenu->findOptionInMenu($userMessage, $currentMenuId);

            if ($selectedOption) {
                if (isset($selectedOption['resposta'])) {
                    // Opção é uma resposta direta
                    $botReply = $selectedOption['resposta'];
                    // Após uma resposta direta, sugere o menu principal novamente
                    $botReply .= "\n\nPosso ajudar com mais algo? Por favor, digite 'menu' para ver as opções ou escolha uma das opções abaixo:\n" . $this->sacMenu->getMenuText('main_menu');
                    $currentMenuId = 'main_menu'; // Volta para o menu principal
                } elseif (isset($selectedOption['submenu_id'])) {
                    // Opção leva a um submenu
                    $currentMenuId = $selectedOption['submenu_id'];
                    $botReply = $this->sacMenu->getMenuText($currentMenuId);
                }
            } else {
                // Se a mensagem do usuário não corresponde a uma opção do menu atual
                // E não é um comando para voltar ao menu principal
                $botReply = "Desculpe, não entendi sua solicitação ou ela não corresponde a uma opção válida no menu atual. Por favor, escolha uma das opções abaixo ou digite 'menu' para voltar ao menu principal:\n\n" . $this->sacMenu->getMenuText($currentMenuId);

              
            }
        }

        // Salvar a resposta do bot e o novo estado do menu
        $this->chatHistory->saveMessage($sessionId, 'model', $botReply, $currentMenuId);

        return [
            'reply' => $botReply,
            'session_id' => $sessionId,
            'current_menu' => $currentMenuId
        ];
    }
}