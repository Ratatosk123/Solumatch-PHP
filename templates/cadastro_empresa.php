<?php
session_start();
require_once('../config.php');

$erros = ['geral' => ''];

if (isset($_POST['cadastrar'])) {
    try {
        // Validações
        if (empty($_POST['senha']) || $_POST['senha'] !== $_POST['confirmar_senha']) {
            throw new Exception("As senhas não coincidem ou estão vazias.");
        }
        if (empty($_POST['CNPJ'])) {
            throw new Exception("O CNPJ é obrigatório para empresas.");
        }

        // Validação de email único
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->rowCount() > 0) throw new Exception("Este e-mail já está cadastrado.");

        // Validação de CNPJ único
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE CNPJ = ?");
        $stmt->execute([$_POST['CNPJ']]);
        if ($stmt->rowCount() > 0) throw new Exception("Este CNPJ já está cadastrado.");
        
        // Prepara os dados para inserção no banco
        $dados = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'numero' => $_POST['numero'],
            'endereco' => $_POST['endereco'],
            'cep' => $_POST['cep'],
            'CPF' => null, // CPF será sempre nulo para empresas
            'CNPJ' => $_POST['CNPJ'],
            'senha_hash' => password_hash($_POST['senha'], PASSWORD_DEFAULT)
        ];
        
        // Insere os dados
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, numero, endereco, cep, CPF, CNPJ, senha_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array_values($dados));
        
        // Redireciona para a página de login após o sucesso
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        $erros['geral'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../misc/logo2.png" type="image/x-icon">
    <title>Cadastro de Empresa | SoluMatch</title>
    <link rel="stylesheet" href="../CSS/cadastro.css">
</head>
<body>
    <section class="formulario">
        <img src="../misc/logo.png" class="logo-form">
        <h1>Cadastro de Empresa</h1>
        
        <?php if (!empty($erros['geral'])): ?>
            <div class="alert-danger" style="color: red; background-color: #ffeeee; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?= htmlspecialchars($erros['geral']) ?></div>
        <?php endif; ?>

        <form method="POST" action="cadastro_empresa.php">
            <input name="nome" type="text" placeholder="Nome da Empresa:" required>
            <input name="email" type="email" placeholder="Email de Contato:" required>
            <input name="numero" type="tel" placeholder="Telefone:" required>
            <input name="endereco" type="text" placeholder="Endereço da Sede:" required>
            <input name="cep" type="text" placeholder="CEP:" required>
            <input name="CNPJ" type="text" placeholder="CNPJ:" required>
            
            <input type="password" name="senha" placeholder="Senha:" required minlength="8">
            <input type="password" name="confirmar_senha" placeholder="Confirme a senha:" required>
            
            <button name="cadastrar" type="submit" class="btn_cadastrar">Cadastrar Empresa</button>
        </form>
        
        <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
        <p>Ou <a href="cadastro_usuario.php">cadastre-se como usuário</a></p>
    </section>

    <script src="../JavaScript/formatarTelefone.js"></script>
    <script src="../JavaScript/formatarCNPJ.js"></script>
    <script src="../JavaScript/formatarCEP.js"></script>
</body>
</html>