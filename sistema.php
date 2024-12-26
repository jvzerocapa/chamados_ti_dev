<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/vendor/autoload.php'; // PHPMailer caminho correto

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once('conexao.php');

    $usuario = $_SESSION['usuario']; // Usuário logado
    $motivo = $_POST['motivo'];
    $descricao = $_POST['descricaodochamado'];
    $setor = $_POST['setor'];

    // Busca o e-mail do usuário cadastrado
    $query_email = "SELECT email FROM usuarios WHERE usuario = ?";
    $stmt_email = $conexao->prepare($query_email);
    $stmt_email->bind_param("s", $usuario);
    $stmt_email->execute();
    $result = $stmt_email->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $emailUsuario = $row['email'];
    } else {
        $mensagem = "Erro: E-mail do usuário não encontrado.";
        exit();
    }

    // Insere o chamado no banco de dados
    $query = "INSERT INTO chamados (usuario, motivo, descricao, setor) VALUES (?, ?, ?, ?)";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param("ssss", $usuario, $motivo, $descricao, $setor);

    if ($stmt->execute()) {
        $mensagem = "Chamado aberto com sucesso!";

        // Envio de e-mail com PHPMailer
        try {
            // Inicializa o PHPMailer
            $mail = new PHPMailer(true);

            // Configurações SMTP
            $mail->isSMTP();
            $mail->Host       = 'mail.adeltec.com.br';       // Servidor SMTP
            $mail->SMTPAuth   = true;                        // Ativa autenticação SMTP
            $mail->Username   = 'chamadosti@adeltec.com.br'; // Usuário SMTP
            $mail->Password   = 'chamadosti43690';           // Senha do e-mail SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SSL
            $mail->Port       = 465;                         // Porta SMTP para SSL

            // Remetente e destinatários
            $mail->setFrom('chamadosti@adeltec.com.br', 'Chamados T.I');
            $mail->addAddress($emailUsuario , 'E-mail do Usuário'); // E-mail do usuário
            $mail->addReplyTo('informatica@adeltec.com.br', 'Informatica Adeltec');
            $mail->addCC('informatica@adeltec.com.br'); // Adiciona cópia para informatica@adeltec.com.br

            // Conteúdo do e-mail
            $mail->isHTML(true); // Define formato HTML
            $mail->Subject = "Novo Chamado Aberto: $motivo";
            $mail->Body    = "<p>Ola, <strong>$usuario do Setor $setor</strong></p>
                              <p>Seu chamado foi aberto com sucesso!</p>
                              <p><strong>Motivo:</strong> $motivo</p>
                              <p><strong>Descricao:</strong> $descricao</p>
                              <p>Obrigado por entrar em contato. <br><br>
                              <img src=https://lh3.googleusercontent.com/pw/AP1GczOghes6WodxCKj2KySn9IkI8RXCnFpxlzfgUQGmoefMV1o1kITBqEBDgjHJx8MHPYshsjgUo0Yxwxtkq5lhGBdt0PB4YmqZzwpYezlkTzb6-G3knDEUoWjfXtbeBRzDVJndvJruLiclGet_Ul4uH2I=w320-h120-s-no-gm?authuser=0></img></p>";

            // Envia o e-mail
            $mail->send();
        } catch (Exception $e) {
            $mensagem = "Chamado aberto, mas houve um erro ao enviar o e-mail: {$mail->ErrorInfo}";
        }

        // Redireciona para evitar reenvio do formulário
        header("Location: sistema.php?success=1");
        exit();
    } else {
        $mensagem = "Erro ao abrir o chamado.";
    }
}

// Exibe a mensagem de sucesso se redirecionado
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $mensagem = "Chamado aberto com sucesso!";
}
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamado T.I</title>
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


    <div class="chamados">
            <h2>Selecione o Motivo</h2>
        <form action="sistema.php" method="POST">
            <label for="motivo"><strong>Motivo</strong></label>
            <select name="motivo" id="motivo" required>
                <option value="">-- Selecione uma opção --</option>
                <option value="Programas em geral">Sistemas Rh</option>
                <option value="Problemas fisicos">Problemas Físicos</option>
                <option value="Servidor">Servidor</option>
                <option value="Genesis">Gênesis</option>
                <option value="E-mails">E-mails</option>
                <option value="Senhas">Senhas</option>
                <option value="Ploomes">Ploomes</option>
                <option value="Wifi">Wifi</option>
                <option value="Softcom">Softcom</option>
                <option value="Anydesk">Anydesk</option>
                <option value="Sistema de bancos">Sistema de bancos</option>
                <option value="Redes sociais">Redes sociais</option>
                <option value="Maxbot">Maxbot</option>
            </select>
            <label for="setor"><strong>Setor</strong></label>
            <select name="setor" id="setor" required>
                <option value="">-- Selecione um setor --</option>
                <option value="marketing">Marketing</option>
                <option value="tecnico">Tecnico</option>
                <option value="kazaseg">Kazaseg</option>
                <option value="alyconsultoria">Aly consultoria</option>
                <option value="financeiro">Financeiro</option>
                <option value="gerencia">Gerencia</option>
                <option value="comercial">Comercial</option>
                </select>
                <label for="descrição"><strong>Descrição</strong></label>
                <textarea id="descricaodochamado" name="descricaodochamado" rows="4" cols="50" placeholder=" Descreva o seu problema/duvida"></textarea>
            <br><br>
            <div class="botoes">
            <button type="submit">Enviar</button>
            <button onclick="window.location.href='index.php';">Sair</button>
            </div>        
        </form>
       
    </div>

    <!-- Pop-up -->
    <div id="popup" class="popup"><?php echo $mensagem; ?></div>

    <script>
        // Exibe o pop-up se houver mensagem
        window.onload = function () {
            const popup = document.getElementById('popup');
            if (popup.textContent.trim() !== "") {
                popup.style.display = 'block'; // Mostra o pop-up
                setTimeout(() => {
                    popup.style.display = 'none'; // Oculta o pop-up após 3 segundos
                }, 2000);
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
