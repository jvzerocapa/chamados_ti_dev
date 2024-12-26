<?php


session_start();

// Verifica se o usuário está logado e se o administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'ADMINISTRADOR') {
    // Redireciona para a página de login se não estiver logado
    header("Location: index.php");
    exit();
}

$mensagem = "";

if (isset($_POST['submit'])) {
    include_once('conexao.php');

    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    $email = $_POST['email'];


    // Verificar se o usuário já existe no banco de dados
    $query = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
    $result = mysqli_query($conexao, $query);

    if (mysqli_num_rows($result) > 0) {
        // Usuário já existe
        $mensagem = "O usuário já está cadastrado!";
    } else {
        // Inserir o novo usuário
        $insert = "INSERT INTO usuarios(usuario, senha, email) VALUES ('$usuario', '$senha', '$email')";
        if (mysqli_query($conexao, $insert)) {
            $mensagem = "Usuário cadastrado com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar usuário!";
        }
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
    <title>Tela de Cadastro</title>
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
        <h1>Novo usuário</h1>
        <form action="cadastro.php" method="POST">
            <input type="text" name="email" placeholder="Email" required>
            <input type="text" name="usuario" placeholder="Usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <div class="botoes">
            <button type="submit" name="submit">Cadastrar</button>
            <button onclick="window.location.href='index.php';">Login</button>
            </div>
        </form>
    </div>


        <!-- Pop-up -->
        <div id="popup" class="popup <?php echo (strpos($mensagem, 'sucesso') !== false) ? 'success' : 'error'; ?>">
        <?php echo $mensagem; ?>
    </div>

    <script>
        // Exibe o pop-up se houver mensagem
        window.onload = function () {
            const popup = document.getElementById('popup');
            if (popup.textContent.trim() !== "") {
                popup.style.display = 'block'; // Mostra o pop-up
                setTimeout(() => {
                    popup.style.display = 'none'; // Oculta o pop-up após 3 segundos
                }, 3000); // 3 segundos
            }
        };
    </script>

    <footer>
        <div class="rodape">
            <p>Powered by João Vitor</p>
        </div>
    </footer>
</body>
</html>