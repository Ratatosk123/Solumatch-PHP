<?php
// PHP/chat_api.php
// Este é o ponto de entrada para as requisições do chatbot

// Autoload das classes
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

// --- Configurações CORS ---
// Permite requisições de origens específicas em ambiente de desenvolvimento.
// É crucial que esta lista inclua a URL EXATA de onde seu frontend está sendo servido.
// Em produção, use uma origem específica ou mecanismos de segurança mais robustos.
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowed_origins = [
        'http://127.0.0.1:5500', // Ex: Live Server do VS Code
        'http://localhost',      // Ex: Apache servindo via localhost padrão (porta 80 implícita)
        'http://localhost:80',   // Ex: Apache servindo via localhost porta 80 explícita
        'http://localhost:8080', // Ex: Outras portas comuns do Apache/outros servidores
        // Adicione outras origens EXATAS aqui se necessário
        // Por exemplo, se seu projeto está em http://localhost/solumatch_atualizado/, a origem é http://localhost
    ];

    if (in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    } else {
        // Se a origem não for permitida, você pode logar ou retornar um erro 403 Forbidden
        error_log("Tentativa de acesso CORS não permitida de origem: " . $_SERVER['HTTP_ORIGIN']);
        http_response_code(403); // Forbidden
        echo json_encode(['error' => 'Acesso não permitido de sua origem.']);
        exit();
    }
} else {
    // Para requisições que não têm HTTP_ORIGIN (ex: testes via Postman, curl, etc.)
    // ou como fallback para ambientes onde a origem não é enviada (raro em navegadores)
    // Considere qual origem você quer permitir por padrão.
    // É mais seguro deixar uma origem específica ou * se tiver certeza do ambiente.
    // Para seu caso, se o Apache estiver servindo em localhost, http://localhost é o padrão.
    header('Access-Control-Allow-Origin: http://localhost'); 
}

header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400'); // Cache para requisições OPTIONS

// --- Lida com requisições OPTIONS (Preflight Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0); // Termina a execução para requisições preflight
}

// --- Configurações da Aplicação ---
$dbHost = 'localhost';
$dbUser = 'root';     // <<<< ATUALIZE SEU USUÁRIO DO BANCO DE DADOS AQUI >>>>
$dbPass = '';         // <<<< ATUALIZE SUA SENHA DO BANCO DE DADOS AQUI >>>>
$dbName = 'solu_match'; // <<<< ATUALIZE O NOME DO SEU BANCO DE DADOS AQUI >>>>

$geminiApiKey = 'AIzaSyCbRLzTlb57eZ2g-Iy26Nc03JGih5hTtM0'; // <<<< ATUALIZE SUA CHAVE AQUI >>>>
$geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $geminiApiKey;

try {
    // Inicializa a conexão com o banco de dados
    $database = new Database($dbHost, $dbUser, $dbPass, $dbName);
    $conn = $database->getConnection();

    // Inicializa o Chatbot
    $chatbot = new Chatbot($conn, $geminiApiKey, $geminiApiUrl);

    // Recebe os dados do Frontend
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = $input['message'] ?? '';
    $sessionId = $input['session_id'] ?? uniqid('chat_');

    // Lógica para a primeira interação ou solicitação do menu inicial
    // Se a mensagem do usuário for vazia OU se for uma requisição específica para o menu inicial
    // usamos 'INICIAR_CHAT' como um marcador interno para acionar o menu principal.
    if (empty($userMessage) || (isset($_GET['initial_menu']) && $_GET['initial_menu'] === 'true')) {
        $userMessage = 'INICIAR_CHAT'; // Marcador interno para o PHP entender que é a primeira interação.
    }

    // Processa a mensagem do usuário e obtém a resposta do bot
    $response = $chatbot->processMessage($userMessage, $sessionId);

    // Retorna a resposta para o Frontend
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    error_log("Erro crítico no chat_api.php: " . $e->getMessage());
} finally {
    // Garante que a conexão com o banco de dados seja fechada
    if (isset($database)) {
        $database->closeConnection();
    }
}
?>