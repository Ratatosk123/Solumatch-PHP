<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | X</title>
  <link rel="stylesheet" href="../CSS/login.css" />
</head>
<body>
  <form action="login.php">

    <div class="container">
      <div class="login-box">
        <img src="../misc/logo.png" class="logo" />
        <h2>Entrar no SoluMatch</h2>
        <form>
          <input type="text" placeholder="Celular, e-mail ou nome de usuário" required />
          <input type="password" placeholder="Senha" required />
          <button type="submit">Entrar</button>
        </form>
        <div class="links">
          <a href="#">Esqueceu a senha?</a>
        </div>
        <div class="links">
          <a href="cadastro.php" target="_blank">Cadastre-se</a>
        </div>
      </div>
    </div>
  </form>
  <script src="../JavaScript/formatarCNPJ.js"></script>
</body>
</html>
