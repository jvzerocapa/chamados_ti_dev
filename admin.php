<?php

session_start();

// Verifica se o usuário está logado e se o administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'ADMINISTRADOR') {
    // Redireciona para a página de login se não estiver logado
    header("Location: index.php");
    exit();
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

 
    <div class="quadro-admin">
        <h1>PAINEL ADMIN</h1>
        <button class="botao" onclick="window.location.href='cadastro.php'">Novo Usuario</button>
        <button onclick="window.location.href='sistema.php';">Ir para o sistema</button>
        <button onclick="window.location.href='relatorios.php';">Relatórios</button>
        <button onclick="window.location.href='index.php';">Sair</button>
    </div>


    
    <footer>
        <div class="rodape">
            <p>Powered by João Vitor</p>
        </div>
    </footer>
</body>
</html>