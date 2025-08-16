<?php
// PHP/classes/SacMenu.php

class SacMenu {
    private $menuStructure;

    public function __construct() {
        // Definição do Menu SAC do Solumatch
        $this->menuStructure = [
            // Opções do Menu Principal
            'main_menu' => [
                [
                    'id' => '1',
                    'texto' => '1. Contratação de Serviços (Para Clientes)',
                    'submenu_id' => 'client_services',
                    'sinonimos' => ['1', 'contratação', 'clientes', 'contratar']
                ],
                [
                    'id' => '2',
                    'texto' => '2. Prestação de Serviços (Para Profissionais)',
                    'submenu_id' => 'pro_services',
                    'sinonimos' => ['2', 'prestação', 'profissionais', 'freelancer']
                ],
                [
                    'id' => '3',
                    'texto' => '3. Pagamentos e Cobranças',
                    'submenu_id' => 'payments',
                    'sinonimos' => ['3', 'pagamento', 'cobrança', 'financeiro']
                ],
                [
                    'id' => '4',
                    'texto' => '4. Suporte Geral / Falar com Atendente',
                    'resposta' => 'Para dúvidas gerais ou para falar com nossa equipe de suporte, por favor, envie um e-mail para suporte@solumatch.com. Nossa equipe responderá o mais breve possível.',
                    'sinonimos' => ['4', 'suporte', 'atendente', 'falar', 'contato', 'ajuda']
                ],
            ],
            // Submenu para Contratação de Serviços
            'client_services' => [
                [
                    'id' => '1',
                    'texto' => '1. Como publicar um projeto?',
                    'resposta' => 'Após fazer login, clique em "Publicar Projeto" (ou "Criar Novo Projeto"). Preença os detalhes do projeto, como título, descrição, categoria, orçamento e prazo. Quanto mais detalhes, melhores propostas você receberá!',
                    'sinonimos' => ['1', 'publicar projeto', 'criar projeto']
                ],
                [
                    'id' => '2',
                    'texto' => '2. Como escolher o profissional ideal?',
                    'resposta' => 'Após publicar seu projeto, você receberá propostas de diversos profissionais. Analise os perfis, portfólios, avaliações de clientes anteriores e a proposta enviada por cada um. Você também pode conversar com eles pelo chat da plataforma antes de tomar sua decisão.',
                    'sinonimos' => ['2', 'escolher profissional', 'selecionar']
                ],
                [
                    'id' => '3',
                    'texto' => '3. Sobre disputas e revisões de projetos',
                    'resposta' => 'Nosso objetivo é garantir sua satisfação. Se houver problemas, você pode solicitar revisões ao profissional. Se mesmo assim não houver acordo, você pode abrir uma disputa, e nossa equipe de suporte irá mediar a situação para encontrar uma solução justa.',
                    'sinonimos' => ['3', 'disputa', 'revisão', 'problema', 'insatisfação']
                ],
                [
                    'id' => '4',
                    'texto' => '4. Voltar ao menu principal',
                    'submenu_id' => 'main_menu',
                    'sinonimos' => ['4', 'voltar', 'menu principal', 'principal']
                ],
            ],
            // Submenu para Prestação de Serviços
            'pro_services' => [
                [
                    'id' => '1',
                    'texto' => '1. Como criar meu perfil profissional?',
                    'resposta' => 'Após se cadastrar, acesse a seção "Meu Perfil" ou "Configurações". Preencha todas as informações, adicione suas habilidades, experiência, portfólio e defina suas tarifas. Um perfil completo aumenta suas chances de ser selecionado.',
                    'sinonimos' => ['1', 'criar perfil', 'cadastro profissional']
                ],
                [
                    'id' => '2',
                    'texto' => '2. Como enviar uma proposta?',
                    'resposta' => 'Procure por projetos na seção "Buscar Projetos" (ou similar). Ao encontrar um que te interesse, clique nele e depois em "Enviar Proposta". Escreva uma proposta clara, destacando suas qualificações e como você pode agregar valor ao projeto.',
                    'sinonimos' => ['2', 'enviar proposta', 'candidatar']
                ],
                [
                    'id' => '3',
                    'texto' => '3. Como recebo pagamentos?',
                    'resposta' => 'Após o cliente aprovar o projeto finalizado, o valor que estava em caução é liberado para sua conta no Solumatch, descontando as taxas da plataforma. Você pode solicitar o saque para sua conta bancária ou outros métodos de pagamento disponíveis.',
                    'sinonimos' => ['3', 'receber pagamento', 'saque', 'retirar dinheiro']
                ],
                [
                    'id' => '4',
                    'texto' => '4. Quais as taxas para profissionais?',
                    'resposta' => 'O Solumatch cobra uma taxa de serviço sobre o valor do projeto finalizado, que varia de acordo com o histórico e volume de projetos com o mesmo cliente. Os detalhes exatos das taxas estão disponíveis na seção "Taxas e Pagamentos" em sua conta.',
                    'sinonimos' => ['4', 'taxas', 'comissão']
                ],
                [
                    'id' => '5',
                    'texto' => '5. Voltar ao menu principal',
                    'submenu_id' => 'main_menu',
                    'sinonimos' => ['5', 'voltar', 'menu principal', 'principal']
                ],
            ],
            // Submenu para Pagamentos
            'payments' => [
                [
                    'id' => '1',
                    'texto' => '1. Como funciona o pagamento de um projeto (caução)?',
                    'resposta' => 'O pagamento é feito em formato de "caução" (Escrow). Você deposita o valor do projeto no Solumatch antes do profissional iniciar. Este valor fica retido e seguro. O pagamento é liberado para o profissional somente quando você aprova o projeto finalizado.',
                    'sinonimos' => ['1', 'pagamento', 'caução', 'escrow']
                ],
                [
                    'id' => '2',
                    'texto' => '2. Taxas do Solumatch',
                    'resposta' => 'O Solumatch cobra uma taxa de serviço sobre o valor do projeto finalizado, que varia de acordo com o histórico e volume de projetos com o mesmo cliente. Os detalhes exatos das taxas estão disponíveis na seção "Taxas e Pagamentos" em sua conta.',
                    'sinonimos' => ['2', 'taxas', 'comissão', 'custos']
                ],
                [
                    'id' => '3',
                    'texto' => '3. Problemas com pagamentos / Saque não realizado',
                    'resposta' => 'Se você está com problemas relacionados a pagamentos ou saques, por favor, entre em contato diretamente com nosso suporte financeiro pelo e-mail financeiro@solumatch.com ou via chat com atendente (opção 4 do menu principal).',
                    'sinonimos' => ['3', 'problema pagamento', 'saque', 'dinheiro', 'não recebi']
                ],
                [
                    'id' => '4',
                    'texto' => '4. Voltar ao menu principal',
                    'submenu_id' => 'main_menu',
                    'sinonimos' => ['4', 'voltar', 'menu principal', 'principal']
                ],
            ]
        ];
    }

    public function getMenuText($menuId) {
        $menuItems = $this->menuStructure[$menuId] ?? [];
        if (empty($menuItems)) {
            return "Desculpe, este menu não foi encontrado. Por favor, digite 'menu' para voltar ao menu principal.";
        }
        $menuText = "Por favor, escolha uma das opções abaixo:\n\n";
        foreach ($menuItems as $item) {
            $menuText .= $item['texto'] . "\n";
        }
        return $menuText;
    }

    public function findOptionInMenu($userMessage, $menuId) {
        $userMessageLower = mb_strtolower(trim($userMessage), 'UTF-8');
        $currentMenuItems = $this->menuStructure[$menuId] ?? [];

        foreach ($currentMenuItems as $item) {
            // Tenta encontrar o ID exato (se o usuário digitou '1', '2', etc.)
            if ($userMessageLower === (string)$item['id']) {
                return $item;
            }
            // Tenta encontrar sinônimos
            foreach ($item['sinonimos'] as $sinonimo) {
                if (mb_strpos($userMessageLower, mb_strtolower($sinonimo, 'UTF-8')) !== false) {
                    return $item;
                }
            }
        }
        return null; // Opção não encontrada
    }
}