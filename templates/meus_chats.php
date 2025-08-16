<?php
// ... (código PHP no topo permanece o mesmo) ...
require_once __DIR__ . '/../PHP/auto_check.php';
require_once __DIR__ . '/../config.php';
$perfilLink = ($_SESSION['is_empresa'] ?? false) ? 'perfil_empresa.php' : 'meu_perfil.php';
$activeVagaId = filter_input(INPUT_GET, 'vaga_id', FILTER_VALIDATE_INT);
$activeOtherUserId = filter_input(INPUT_GET, 'conversa_com_id', FILTER_VALIDATE_INT);
if ($activeOtherUserId && isset($_SESSION['user_id']) && $activeOtherUserId == $_SESSION['user_id']) {
    header('Location: trabalhos.php');
    exit();
}
$activeOtherUserName = null;
$activeVagaTitle = null;
$activeOtherUserIsCompany = false;
if ($activeVagaId && $activeOtherUserId) {
    try {
        $stmtUser = $pdo->prepare("SELECT nome, (CASE WHEN CNPJ IS NOT NULL AND CNPJ != '' THEN TRUE ELSE FALSE END) as is_company FROM usuarios WHERE id = ?");
        $stmtUser->execute([$activeOtherUserId]);
        $outroUsuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($outroUsuario) {
            $activeOtherUserName = $outroUsuario['nome'];
            $activeOtherUserIsCompany = (bool)$outroUsuario['is_company'];
        }
        $stmtVaga = $pdo->prepare("SELECT titulo FROM vagas WHERE id = ?");
        $stmtVaga->execute([$activeVagaId]);
        $vaga = $stmtVaga->fetch(PDO::FETCH_ASSOC);
        if ($vaga) {
            $activeVagaTitle = $vaga['titulo'];
        }
    } catch (PDOException $e) {
        error_log("Erro ao pré-carregar dados do chat em meus_chats.php: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../misc/logo2.png" type="image/x-icon">
    <title>Minhas Mensagens | SoluMatch</title>
    <link rel="stylesheet" href="../CSS/trabalhos.css"> 
    <link rel="stylesheet" href="../CSS/meus_chats.css">
    <link rel="stylesheet" href="../CSS/acessibilidade.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <a href="trabalhos.php" aria-label="Ir para a página inicial de trabalhos">
                <div class="logo">
                    <img src="../misc/logo2.png" width="50vh">
                    <h2>Solu<span>Match</span></h2>
                </div>
            </a>
            <div class="navbar-icons-container">
            <button id="header-tts-toggle" class="header-accessibility-btn" aria-label="Ativar leitura de tela.">
                <img src="../misc/audio-icon.svg" alt="Ícone de áudio para acessibilidade">
            </button>
            <button id="voice-command-toggle" class="header-accessibility-btn" title="Ativar comando de voz" aria-label="Ativar comando de voz para controlar a acessibilidade">
                <img src="../misc/microphone-icon.svg" alt="Ícone de microfone para comando de voz">
            </button>
                <div class="dropdown">
                    <div class="profile-icon" id="profileIcon" aria-label="Abrir menu do perfil com opções de conta e sair">
                        <img class="profile" id="headerProfileIcon" src="../PHP/get_profile_picture.php?id=<?php echo $_SESSION['user_id']; ?>" alt="Perfil">
                    </div>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <div class="section"><a href="<?php echo htmlspecialchars($perfilLink); ?>">Meu perfil</a></div>
                         <div class="section"><a href="ajuda.html">Obter Ajuda</a></div>
                        <div class="section"><a href="../PHP/logout.php">Sair</a></div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="inbox-container">
        <aside class="conversation-list" id="conversationList">
            <div class="list-header">
                <h3>Todas as Mensagens</h3>
            </div>
            <div class="list-content" aria-label="Lista de conversas">
                </div>
        </aside>

        <section class="chat-view" id="chatView">
            <div class="chat-header" id="chatHeader" style="display: none;">
                
                <button class="back-to-list-btn" id="backToListBtn" aria-label="Voltar para a lista de conversas">&larr;</button>

                <img src="" alt="Foto de Perfil" class="chat-header-pic" id="chatHeaderPic">
                <div class="chat-info">
                    <a href="#" class="chat-profile-link" id="chatProfileLink" target="_blank">
                        <h2 id="chatWithUserName"></h2>
                    </a>
                    <p id="chatAboutVaga"></p>
                </div>
            </div>
            <div class="chat-messages" id="chatMessages" role="log" aria-live="polite">
                <p class="no-chat-selected" role="alert">Selecione uma conversa na lista para começar.</p>
            </div>
            <div class="chat-input-area" id="chatInputArea" style="display: none;">
                <input type="file" id="attachmentInput" style="display: none;" />
            
                <button id="attachmentBtn" title="Anexar arquivo" class="chat-action-btn" aria-label="Anexar um arquivo à mensagem">📎</button>
            
                <textarea id="messageInput" placeholder="Digite sua mensagem..." rows="1" aria-label="Caixa de texto para digitar sua mensagem"></textarea>
                
                <button id="sendMessageBtn" aria-label="Enviar mensagem">Enviar</button>
            </div>
        </section>
    </main>

    <script>
        // ... (bloco de script com variáveis PHP permanece o mesmo) ...
        const LOGGED_IN_USER_ID = <?php echo json_encode($_SESSION['user_id']); ?>;
        const ACTIVE_VAGA_ID = <?php echo json_encode($activeVagaId); ?>;
        const ACTIVE_OTHER_USER_ID = <?php echo json_encode($activeOtherUserId); ?>;
        const ACTIVE_OTHER_USER_NAME = <?php echo json_encode($activeOtherUserName); ?>;
        const ACTIVE_VAGA_TITLE = <?php echo json_encode($activeVagaTitle); ?>;
        const ACTIVE_OTHER_USER_IS_COMPANY = <?php echo json_encode($activeOtherUserIsCompany); ?>;
    </script>
    <script src="../JavaScript/dropdown_perfil.js"></script>
    <script src="../JavaScript/meus_chats.js"></script>
    <script src="../JavaScript/acessibilidade_tts.js"></script>
</body>
</html>