<?php
require_once __DIR__ . '/../PHP/auto_check.php';
require_once __DIR__ . '/../config.php';

// --- LÓGICA PARA VISÃO PÚBLICA E DO DONO ---

// 1. Determina qual perfil visualizar: o da URL ($_GET['id']) ou o do próprio usuário logado.
$profileId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? $_SESSION['user_id'];

// 2. Verifica se o usuário logado é o dono do perfil que está sendo visto.
$isOwnerView = (isset($_SESSION['user_id']) && $profileId == $_SESSION['user_id']);

// 3. Define o link do menu superior (sempre leva para o próprio perfil do usuário logado)
$perfilLink = (isset($_SESSION['is_empresa']) && $_SESSION['is_empresa'] === true) 
              ? 'perfil_empresa.php' 
              : 'meu_perfil.php';

// Inicializa as variáveis para evitar erros
$userData = null;
$userSkills = [];
$userExperiences = [];
$userEducation = [];

// 4. Busca no banco de dados as informações do perfil a ser exibido (usando $profileId)
if ($profileId) {
    try {
        $stmtUser = $pdo->prepare("SELECT nome, email, numero, endereco, cep, CPF, CNPJ, sobre_mim FROM usuarios WHERE id = :id");
        $stmtUser->execute([':id' => $profileId]);
        $userData = $stmtUser->fetch(PDO::FETCH_OBJ);

        if (!$userData) {
            die("Perfil de usuário não encontrado.");
        }

        $stmtSkills = $pdo->prepare("SELECT h.nome FROM habilidades h JOIN usuario_habilidades uh ON h.id = uh.habilidade_id WHERE uh.usuario_id = :id ORDER BY h.nome");
        $stmtSkills->execute([':id' => $profileId]);
        $userSkills = $stmtSkills->fetchAll(PDO::FETCH_COLUMN, 0);

        $stmtExperiences = $pdo->prepare("SELECT * FROM experiencias WHERE usuario_id = :id ORDER BY data_inicio DESC");
        $stmtExperiences->execute([':id' => $profileId]);
        $userExperiences = $stmtExperiences->fetchAll(PDO::FETCH_ASSOC);

        $stmtEducation = $pdo->prepare("SELECT * FROM educacao WHERE usuario_id = :id ORDER BY data_inicio DESC");
        $stmtEducation->execute([':id' => $profileId]);
        $userEducation = $stmtEducation->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Erro ao carregar dados do perfil: " . $e->getMessage());
        die("Erro ao carregar dados do perfil. Tente novamente mais tarde.");
    }
} else {
    die("ID de perfil inválido.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../misc/logo2.png" type="image/x-icon">
    <title><?php echo htmlspecialchars($userData->nome); ?> | SoluMatch</title>
    <link rel="stylesheet" href="../CSS/trabalhos.css"> <link rel="stylesheet" href="../CSS/meu_perfil.css">
    <link rel="stylesheet" href="../CSS/perfil_empresa.css"> </head>
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

    <main class="profile-container">
        <aside class="profile-sidebar">
            <div class="profile-header-section">
                <div class="profile-pic-container <?php if($isOwnerView) echo 'editable'; ?>">
                    <img src="../PHP/get_profile_picture.php?id=<?php echo $profileId; ?>" alt="Foto de Perfil" class="profile-pic" id="profilePicDisplay">
                    <?php if ($isOwnerView): ?>
                        <div class="profile-pic-overlay"><span>Alterar Foto</span></div>
                        <input type="file" id="profilePicInput" name="profile_picture" style="display: none;">
                    <?php endif; ?>
                </div>

                <h1 class="profile-name" id="profileName"><?php echo htmlspecialchars($userData->nome); ?></h1>
                <p class="profile-headline">Especialista em Desenvolvimento</p>
                <?php if ($isOwnerView): ?>
                    <button class="edit-profile-btn" id="editProfileBtn">Editar Perfil</button>
                <?php endif; ?>
            </div>

            <div class="profile-section">
                <h3>Informações de Contato</h3>
                <p><strong>Email:</strong> <span id="userEmail"><?php echo htmlspecialchars($userData->email); ?></span></p>
                <?php if ($isOwnerView): ?>
                    <p><strong>Telefone:</strong> <span id="userPhone"><?php echo htmlspecialchars($userData->numero ?? 'N/A'); ?></span></p>
                    <p><strong>Endereço:</strong> <span id="userAddress"><?php echo htmlspecialchars($userData->endereco ?? 'N/A'); ?></span></p>
                    <p><strong>CEP:</strong> <span id="userCEP"><?php echo htmlspecialchars($userData->cep ?? 'N/A'); ?></span></p>
                    <p><strong>CPF:</strong> <span id="userCPF"><?php echo htmlspecialchars($userData->CPF ?? 'N/A'); ?></span></p>
                <?php endif; ?>
            </div>
        </aside>

        <section class="profile-main-content">
            <div class="profile-section about-me">
                <h2>Sobre Mim</h2>
                <p id="aboutMeText"><?php echo nl2br(htmlspecialchars($userData->sobre_mim ?? 'Nenhuma descrição.')); ?></p>
            </div>

            <div class="profile-section skills-section">
                <h2>Habilidades
                    <?php if ($isOwnerView): ?>
                        <button class="add-btn" id="addSkillBtn">+</button>
                    <?php endif; ?>
                </h2>
                <div class="skills-grid" id="userSkillsGrid">
                    <?php if (!empty($userSkills)): foreach ($userSkills as $skill): ?>
                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?>
                            <?php if ($isOwnerView): ?>
                                <span class="remove-item-btn" data-type="skill" data-value="<?php echo htmlspecialchars($skill); ?>">&times;</span>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; else: ?>
                        <p id="noSkillsMessage">Nenhuma habilidade informada.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-section experience-section">
                <h2>Experiência
                    <?php if ($isOwnerView): ?>
                        <button class="add-btn" id="addExperienceBtn">+</button>
                    <?php endif; ?>
                </h2>
                <div id="userExperiencesList">
                    <?php if (!empty($userExperiences)): foreach ($userExperiences as $exp): ?>
                        <div class="experience-item">
                            <h3><?php echo htmlspecialchars($exp['titulo']); ?></h3>
                            <p><?php echo htmlspecialchars($exp['empresa']); ?>, <?php echo date('Y', strtotime($exp['data_inicio'])); ?> - <?php echo $exp['data_fim'] ? date('Y', strtotime($exp['data_fim'])) : 'Atual'; ?></p>
                            <p><?php echo nl2br(htmlspecialchars($exp['descricao'])); ?></p>
                            <?php if ($isOwnerView): ?>
                                <div class="item-actions">
                                    <button class="edit-item-btn" data-type="experience" data-id="<?php echo $exp['id']; ?>">Editar</button>
                                    <button class="remove-item-btn" data-type="experience" data-id="<?php echo $exp['id']; ?>">&times;</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; else: ?>
                         <p id="noExperiencesMessage">Nenhuma experiência informada.</p>
                    <?php endif; ?>
                </div>
            </div>

             <div class="profile-section education-section">
                <h2>Educação
                    <?php if ($isOwnerView): ?>
                        <button class="add-btn" id="addEducationBtn">+</button>
                    <?php endif; ?>
                </h2>
                <div id="userEducationList">
                     <?php if (!empty($userEducation)): foreach ($userEducation as $edu): ?>
                        <div class="education-item">
                            <h3><?php echo htmlspecialchars($edu['titulo']); ?></h3>
                            <p><?php echo htmlspecialchars($edu['instituicao']); ?>, <?php echo date('Y', strtotime($edu['data_inicio'])); ?> - <?php echo $edu['data_fim'] ? date('Y', strtotime($edu['data_fim'])) : 'Atual'; ?></p>
                            <?php if ($isOwnerView): ?>
                                <div class="item-actions">
                                    <button class="edit-item-btn" data-type="education" data-id="<?php echo $edu['id']; ?>">Editar</button>
                                    <button class="remove-item-btn" data-type="education" data-id="<?php echo $edu['id']; ?>">&times;</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; else: ?>
                        <p id="noEducationMessage">Nenhuma formação informada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    
    <?php if ($isOwnerView): ?>
    
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Editar Perfil</h2>
            <form id="editProfileForm">
                <div class="form-group"><label>Nome Completo</label><input type="text" name="name" value="<?php echo htmlspecialchars($userData->nome); ?>"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($userData->email); ?>"></div>
                <div class="form-group"><label>Telefone</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($userData->numero); ?>"></div>
                <div class="form-group"><label>Endereço</label><input type="text" name="address" value="<?php echo htmlspecialchars($userData->endereco); ?>"></div>
                <div class="form-group"><label>CEP</label><input type="text" name="cep" value="<?php echo htmlspecialchars($userData->cep); ?>"></div>
                <div class="form-group"><label>Sobre Mim</label><textarea name="about_me" rows="5"><?php echo htmlspecialchars($userData->sobre_mim); ?></textarea></div>
                <button type="submit" class="postar-vaga-btn">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <div id="addSkillModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Editar Habilidades</h2>
            <form id="skillsForm">
                <div class="form-group">
                    <label>Habilidades (separadas por vírgula)</label>
                    <input type="text" id="skillsInput" placeholder="Ex: HTML, CSS, JavaScript, Liderança">
                </div>
                <button type="button" id="saveSkillsBtn" class="postar-vaga-btn">Salvar Habilidades</button>
            </form>
        </div>
    </div>
    
    <div id="editExperienceModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2 id="experienceModalTitle">Adicionar Experiência</h2>
            <form id="experienceForm">
                <input type="hidden" name="id">
                <div class="form-group"><label>Cargo / Título</label><input type="text" name="titulo" required></div>
                <div class="form-group"><label>Empresa</label><input type="text" name="empresa" required></div>
                <div class="form-group"><label>Data de Início</label><input type="date" name="data_inicio" required></div>
                <div class="form-group"><label>Data de Fim</label><input type="date" name="data_fim" id="expEndDate"></div>
                <div class="form-group"><input type="checkbox" id="expCurrentJob" name="current_job"> <label for="expCurrentJob">Trabalho aqui atualmente</label></div>
                <div class="form-group"><label>Descrição</label><textarea name="descricao" rows="5"></textarea></div>
                <button type="submit" class="postar-vaga-btn">Salvar Experiência</button>
            </form>
        </div>
    </div>

    <div id="editEducationModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2 id="educationModalTitle">Adicionar Formação</h2>
            <form id="educationForm">
                <input type="hidden" name="id">
                <div class="form-group"><label>Curso / Título</label><input type="text" name="titulo" required></div>
                <div class="form-group"><label>Instituição de Ensino</label><input type="text" name="instituicao" required></div>
                <div class="form-group"><label>Data de Início</label><input type="date" name="data_inicio" required></div>
                <div class="form-group"><label>Data de Fim</label><input type="date" name="data_fim" id="eduEndDate"></div>
                <div class="form-group"><input type="checkbox" id="eduCurrentStudy" name="current_study"> <label for="eduCurrentStudy">Ainda estou cursando</tlabel></div>
                <button type="submit" class="postar-vaga-btn">Salvar Formação</button>
            </form>
        </div>
    </div>

    <script src="../JavaScript/editor_perfil.js"></script>
    <script src="../JavaScript/perfil_usuario_upload.js"></script>
    <?php endif; ?>
    <script src="../JavaScript/acessibilidade_tts.js"></script>
    <script src="../JavaScript/dropdown_perfil.js"></script>
</body>
</html>