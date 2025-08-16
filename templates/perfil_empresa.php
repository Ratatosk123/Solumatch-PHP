<?php
require_once __DIR__ . '/../PHP/auto_check.php';
require_once __DIR__ . '/../config.php';

// --- LÓGICA PARA VISÃO PÚBLICA E DO DONO ---

// 1. Determina qual perfil visualizar: o da URL (id) ou o do usuário logado
$profileId = $_GET['id'] ?? $_SESSION['user_id'];

// 2. Verifica se o usuário logado é o dono do perfil que está sendo visto
$isOwnerView = (isset($_SESSION['user_id']) && $profileId == $_SESSION['user_id']);

// 3. Define o link do menu superior (sempre leva para o próprio perfil)
$perfilLink = (isset($_SESSION['is_empresa']) && $_SESSION['is_empresa'] === true) 
              ? 'perfil_empresa.php' 
              : 'meu_perfil.php';

// 4. Busca os dados do perfil a ser exibido (com base no $profileId)
$empresaData = null;
$vagas = [];

if ($profileId) {
    try {
        $stmtEmpresa = $pdo->prepare("SELECT id, nome, email, CNPJ, endereco, cep, sobre_mim FROM usuarios WHERE id = :id AND CNPJ IS NOT NULL");
        $stmtEmpresa->execute([':id' => $profileId]);
        $empresaData = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

        // Se não encontrou uma empresa com esse ID, encerra a página.
        if (!$empresaData) {
            die("Perfil de empresa não encontrado.");
        }

        $stmtVagas = $pdo->prepare("SELECT * FROM vagas WHERE empresa_id = :id ORDER BY data_postagem DESC");
        $stmtVagas->execute([':id' => $profileId]);
        $vagas = $stmtVagas->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Erro ao carregar dados do perfil da empresa: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../misc/logo2.png" type="image/x-icon">
    <title><?php echo htmlspecialchars($empresaData['nome'] ?? 'Perfil da Empresa'); ?> | SoluMatch</title>
    <link rel="stylesheet" href="../CSS/perfil_empresa.css">
    <link rel="stylesheet" href="../CSS/trabalhos.css">
</head>
<body>

    <header class="header">
        <nav class="navbar">
            <a href="trabalhos.php">
                <div class="logo"><img src="../misc/logo2.png" width="50vh"><h2>Solu<span>Match</span></h2></div>
            </a>
            <div class="navbar-icons-container">
            <button id="header-tts-toggle" class="header-accessibility-btn" aria-label="Ativar leitura de tela.">
                <img src="../misc/audio-icon.svg" alt="Ícone de áudio para acessibilidade">
            </button>
            <button id="voice-command-toggle" class="header-accessibility-btn" title="Ativar comando de voz" aria-label="Ativar comando de voz para controlar a acessibilidade">
                <img src="../misc/microphone-icon.svg" alt="Ícone de microfone para comando de voz">
            </button>
            <div class="dropdown">
                <div class="profile-icon" id="profileIcon"><img class="profile" id="headerProfileIcon" src="../PHP/get_profile_picture.php?id=<?php echo $_SESSION['user_id']; ?>" alt="Perfil"></div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <div class="section"><a href="<?php echo htmlspecialchars($perfilLink); ?>">Meu perfil</a></div>
                    <div class="section"><a href="meus_chats.php">Minhas Mensagens</a></div>
                     <div class="section"><a href="ajuda.html">Obter Ajuda</a></div>
                    <div class="section"><a href="../PHP/logout.php">Sair</a></div>
                </div>
            </div>
          </div>
        </nav>
    </header>

    <div class="page-container">
        
        <aside class="profile-sidebar">
            <div class="profile-header-section">
                
                <div class="profile-pic-container <?php if($isOwnerView) echo 'editable'; ?>">
                    <img src="../PHP/get_profile_picture.php?id=<?php echo htmlspecialchars($profileId); ?>" alt="Foto da Empresa" class="company-profile-pic" id="empresaProfilePic">
                    <?php if ($isOwnerView): // Só mostra a opção de alterar foto se for o dono do perfil ?>
                        <div class="profile-pic-overlay"><span>Alterar Foto</span></div>
                        <input type="file" id="profilePicInput" name="profile_picture" accept="image/png, image/jpeg, image/gif" style="display: none;">
                    <?php endif; ?>
                </div>

                <h1 class="profile-name" id="empresaNome"><?php echo htmlspecialchars($empresaData['nome']); ?></h1>
                <p class="profile-headline">Empresa Verificada</p>
                
                <?php if ($isOwnerView): // Só mostra o botão de editar se for o dono do perfil ?>
                    <button class="edit-profile-btn" id="editEmpresaBtn">Editar Perfil</button>
                <?php endif; ?>
            </div>

            <div class="profile-section">
                <h3>Informações da Empresa</h3>
                <p><strong>Email:</strong> <span id="empresaEmail"><?php echo htmlspecialchars($empresaData['email']); ?></span></p>
                <p><strong>CNPJ:</strong> <span><?php echo htmlspecialchars($empresaData['CNPJ']); ?></span></p>
                <p><strong>Endereço:</strong> <span id="empresaEndereco"><?php echo htmlspecialchars($empresaData['endereco'] ?? 'Não informado'); ?></span></p>
                <p><strong>CEP:</strong> <span id="empresaCep"><?php echo htmlspecialchars($empresaData['cep'] ?? 'Não informado'); ?></span></p>
            </div>
        </aside>

        <main class="main-content-area">
            <div class="card" style="margin-bottom: 20px;">
                <div class="vagas-header"><h2>Sobre Nós</h2></div>
                <p style="line-height: 1.6;" id="empresaSobre">
                    <?php echo nl2br(htmlspecialchars($empresaData['sobre_mim'] ?? 'Nenhuma descrição fornecida.')); ?>
                </p>
            </div>

            <div class="card">
                <div class="vagas-header">
                    <h2>Vagas Publicadas</h2>
                    <?php if ($isOwnerView): // Só mostra o botão de postar vaga se for o dono do perfil ?>
                        <button class="postar-vaga-btn" id="postarVagaBtn">Postar Nova Vaga</button>
                    <?php endif; ?>
                </div>
                <div class="vagas-list" id="listaDeVagas">
                    <?php if (!empty($vagas)): foreach ($vagas as $vaga): ?>
                        <div class="vaga-item" data-vaga-id="<?php echo $vaga['id']; ?>">
                            <h3><?php echo htmlspecialchars($vaga['titulo']); ?></h3>
                            <span class="vaga-category-tag"><?php echo htmlspecialchars($vaga['categoria']); ?></span>
                            <p><?php echo nl2br(htmlspecialchars($vaga['descricao'])); ?></p>
                            <?php if ($isOwnerView): // Mostra ações apenas para o dono do perfil ?>
                                <div class="item-actions">
                                    <button class="edit-item-btn" data-vaga='<?php echo htmlspecialchars(json_encode($vaga), ENT_QUOTES, 'UTF-8'); ?>'>Editar</button>
                                    <button class="remove-item-btn" data-vaga-id="<?php echo $vaga['id']; ?>">&times;</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; else: ?>
                        <p>Nenhuma vaga postada por esta empresa no momento.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <?php if ($isOwnerView): ?>
        <div id="vagaModal" class="modal">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2 id="vagaModalTitle">Adicionar Nova Vaga</h2>
                <form id="vagaForm">
                    <input type="hidden" name="id" value="">
                    <div class="form-group">
                        <label for="titulo">Título da Vaga</label>
                        <input type="text" name="titulo" required>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição Completa</label>
                        <textarea name="descricao" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="requisitos">Requisitos (separados por vírgula)</label>
                        <input type="text" name="requisitos" placeholder="Ex: PHP, MySQL, API REST">
                    </div>
                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <select name="categoria" required>
                            <option value="">Selecione uma Categoria</option>
                            <option value="Programação">Programação</option>
                            <option value="Design">Design</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Redação">Redação</option>
                            <option value="Suporte Administrativo">Suporte Administrativo</option>
                            <option value="Jurídico">Jurídico</option>
                            <option value="Finanças">Finanças</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo_contratacao">Tipo de Contratação</label>
                        <select name="tipo_contratacao" required>
                             <option value="">Selecione o Tipo</option>
                             <option value="Preço Fixo / PJ / Freelance">Preço Fixo / PJ / Freelance</option>
                             <option value="CLT">CLT</option>
                             <option value="Estágio">Estágio</option>
                        </select>
                    </div>
                     <div class="form-group">
                        <label for="localizacao">Localização</label>
                        <input type="text" name="localizacao" placeholder="Ex: Remoto, São Paulo - SP">
                    </div>
                    <div class="form-group">
                        <label for="tipo_orcamento">Tipo de Orçamento</label>
                        <select name="tipo_orcamento" required>
                            <option value="fixo">Preço Fixo (Contratual)</option>
                            <option value="por_hora">Por Hora</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="salario">Salário / Orçamento (opcional)</label>
                        <input type="number" name="salario" step="0.01" placeholder="Deixe em branco se for a combinar">
                    </div>
                    <button type="submit" class="postar-vaga-btn">Salvar Vaga</button>
                </form>
            </div>
        </div>

        <div id="editEmpresaModal" class="modal">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2>Editar Perfil da Empresa</h2>
                <form id="editEmpresaForm">
                    <div class="form-group">
                        <label for="name">Nome da Empresa</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email de Contato</label>
                        <input type="email" name="email" required>
                    </div>
                     <div class="form-group">
                        <label for="address">Endereço</label>
                        <input type="text" name="address">
                    </div>
                    <div class="form-group">
                        <label for="cep">CEP</label>
                        <input type="text" name="cep">
                    </div>
                    <div class="form-group">
                        <label for="about_me">Sobre Nós</label>
                        <textarea name="about_me" rows="6"></textarea>
                    </div>
                    <button type="submit" class="postar-vaga-btn">Salvar Alterações</button>
                </form>
            </div>
        </div>
        
        <script src="../JavaScript/perfil_empresa.js"></script>
        <script src="../JavaScript/perfil_empresa_upload.js"></script>
    <?php endif; ?>
    <script src="../JavaScript/acessibilidade_tts.js"></script>
    <script src="../JavaScript/dropdown_perfil.js"></script>
</body>
</html>