<?php
// Inicia a sessão
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once('conexao.php');

    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    // Verificação especial para administrador
    if ($usuario === 'ADMINISTRADOR' && $senha === '436904d31t3C@') {
        // Redireciona para o cadastro.php se for administrador
        $_SESSION['usuario'] = $usuario; // Armazena o usuário na sessão
        header("Location: admin.php");
        exit();
    }

    // Verifica se o usuário comum existe no banco de dados
    $query = "SELECT * FROM usuarios WHERE usuario = ? AND senha = ?";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param("ss", $usuario, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login bem-sucedido
        $_SESSION['usuario'] = $usuario; // Salva o usuário na sessão
        header("Location: sistema.php"); // Redireciona para o sistema
        exit();
    } else {
        // Login falhou
        $mensagem = "Usuário ou senha inválidos!";
    }
}


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <title>Tela de Login</title>
        <link rel="stylesheet" href="style.css">
        


</head>
<body>
 
    
    <header>
        <div class="imagem">
                <nav>
                    <img src="https://lh3.googleusercontent.com/pw/AP1GczPyx460LGlahQP2HSl3BJzuiQuHv2YBQYjbkUgi1PBmv_6akKu-siu7twNFjtTRp4OW8RLSBl8VHatyG0xpDlAG4r-Tz9oH1eJsUa3a5_NLG5RfZU6WcLjxitHuAfhieeqQf3WMY5YPIpxSohNQ-Zk=w189-h98-s-no-gm?authuser=0">
                </nav>
        </div>
    </header>

 
    <div class="login-quadro">
        <h1>LOGIN</h1>
        <form action="index.php" method="post">
            <input type="text" name="usuario" placeholder="Usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
        <?php if (!empty($mensagem)): ?>
            <p id="erro-popup" class="erro-popup"><?php echo $mensagem; ?></p>
        <?php endif; ?>
    </div>



    <script>
    // Exibe o pop-up se houver mensagem
    window.onload = function () {
        const popup = document.getElementById('erro-popup');
        if (popup && popup.textContent.trim() !== "") {
            popup.style.display = 'block'; // Mostra o pop-up
            setTimeout(() => {
                popup.style.display = 'none'; // Oculta o pop-up após 3 segundos
            }, 3000); // 3 segundos
        }
    };
</script>
    <footer>
        <div class="rodape">
            <p><img src="https://lh3.googleusercontent.com/pw/AP1GczOy-FS3ZdUtU3VQOXtEsf7K07CCKGYDfQzgh-U9RrvzBzmFMuidtUaf-oaCtMTmzM9-K2WuzrG79zLah0JwNQxDJQ7k6Dzcez8z6HUhygQ1vFFS2Nj8niKToy0wcRSNCmg5GV_z7JDUBiuMzbx76EI=w264-h72-s-no-gm?authuser=0"></p>
        </div>
    </footer>
</body>
</html>