<?php
if (isset($_POST['cadastrar'])) {
    // Inclui o arquivo de conexão
    require_once('../config.php');

    try {
        // Verifica se a senha foi enviada
        if (empty($_POST['senha'])) {
            throw new Exception("Senha é obrigatória");
        }

        // Criptografa a senha
        $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        // Prepara e executa a inserção no banco
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, numero, endereco, cep, cpf, CNPJ, senha_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nome'],
            $_POST['email'],
            $_POST['numero'],
            $_POST['endereco'],
            $_POST['cep'],
            $_POST['cpf'],  
            $_POST['CNPJ'],
            $senha_hash
        ]);

        echo "Usuário cadastrado com sucesso!";
    } catch (PDOException $e) {
        echo "Erro ao cadastrar: " . $e->getMessage();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
?>


<!-- HTML -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/cadastro.css">
    <title>Cadastre-se</title>
</head>
<body>
    
    <section class="formulario">
        <img src="../misc/logo.png" class="logo-form">
        <h1>Cadastre-se na SoluMatch</h1>
        <form method="POST" action="cadastro.php"> 
            
        <input name="nome" type="text" placeholder="Nome:">
        
        <input name="email" type="text" placeholder="Email:">
        
        <input class="numero" name="numero" type="tel" placeholder="Telefone:">
        
        <input name="endereco" type="text" placeholder="Endereço:">
        
        <input name="cep" type="numberx" placeholder="CEP:">
        
        <input name="CPF" type="text" placeholder="CPF:">
        
        <input class="cnpj" name="CNPJ" type="text" placeholder="CNPJ (se existir):">
        
        <input type="password" name="senha" placeholder="Senha:">
        
        <input type="password" name="confirmar_senha" placeholder="Confirme a senha:">
        

        <button class="btn_cadastrar" src="login.php" type="submit">Cadastrar</button>
        </form>
        <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
    </section>
    <script src="../JavaScript/formatarTelefone.js"></script>
    <script src="../JavaScript/formatarCNPJ.js"></script>
    <script src="../JavaScript/formatarCEP.js"></script>
</body>
</html>