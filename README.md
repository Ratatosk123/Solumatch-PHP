<?php
$host = getenv('localhost');
$dbname = getenv('');
$usuario = getenv('root');
$senha = getenv('');

$geminiApiKey = getenv('GEMINI_API_KEY');
?>  


QUERY 



--
-- Base de Dados: `solu_match`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--
-- Armazena os dados de profissionais (com CPF) e empresas (com CNPJ).
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `CPF` varchar(14) DEFAULT NULL,
  `CNPJ` varchar(18) DEFAULT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `CPF` (`CPF`),
  UNIQUE KEY `CNPJ` (`CNPJ`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `vagas`
--
-- Armazena as vagas de trabalho publicadas pelas empresas.
--

CREATE TABLE `vagas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `requisitos` text,
  `categoria` varchar(100) NOT NULL,
  `tipo_contratacao` varchar(50) NOT NULL,
  `localizacao` varchar(255) DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `tipo_orcamento` enum('por_hora','fixo') DEFAULT 'fixo',
  PRIMARY KEY (`id`),
  KEY `empresa_id` (`empresa_id`),
  CONSTRAINT `vagas_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `conversas`
--
-- Tabela para gerenciar as conversas do chat entre dois usuários.
--

CREATE TABLE `conversas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `participante1_id` INT NOT NULL,
  `participante2_id` INT NOT NULL,
  `criada_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`participante1_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`participante2_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `mensagens`
--
-- Tabela para armazenar as mensagens trocadas em uma conversa.
--

CREATE TABLE `mensagens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversa_id` INT NOT NULL,
  `remetente_id` INT NOT NULL,
  `destinatario_id` INT NOT NULL,
  `mensagem` TEXT NOT NULL,
  `enviada_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `lida` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`conversa_id`) REFERENCES `conversas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`remetente_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`destinatario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;