<?php
session_start();
include_once('conexao.php');
require('tfpdf/tfpdf.php'); // Biblioteca para exportar para PDF

// Inicialize variáveis de filtro
$filtroDataInicial = $_POST['filtroDataInicial'] ?? '';
$filtroDataFinal = $_POST['filtroDataFinal'] ?? '';
$filtroMotivo = $_POST['filtroMotivo'] ?? '';
$filtroSetor = $_POST['filtroSetor'] ?? '';

// Verifique se o status foi alterado
if (isset($_POST['alterarStatus'])) {
    $idChamado = $_POST['idChamado'];
    $novoStatus = $_POST['status'];

    $updateQuery = "UPDATE chamados SET status = ? WHERE id = ?";
    $stmtUpdate = $conexao->prepare($updateQuery);
    $stmtUpdate->bind_param("si", $novoStatus, $idChamado);

    if (!$stmtUpdate->execute()) {
        die("Erro ao atualizar o status: " . $stmtUpdate->error);
    }
}

// Inicialize arrays para armazenar filtros e valores
$filtros = [];
$valores = [];
$tipos = "";

// Filtro de datas
if (!empty($filtroDataInicial) && !empty($filtroDataFinal)) {
    // Ajustar o formato para incluir o intervalo completo do dia
    $dataInicialFormatada = date('Y-m-d 00:00:00', strtotime($filtroDataInicial));
    $dataFinalFormatada = date('Y-m-d 23:59:59', strtotime($filtroDataFinal));
    
    $filtros[] = "data_abertura BETWEEN ? AND ?";
    $valores[] = $dataInicialFormatada;
    $valores[] = $dataFinalFormatada;
    $tipos .= "ss";
}

// Filtro de motivo
if (!empty($filtroMotivo)) {
    $filtros[] = "motivo = ?";
    $valores[] = $filtroMotivo;
    $tipos .= "s";
}

// Filtro de setor
if (!empty($filtroSetor)) {
    $filtros[] = "setor = ?";
    $valores[] = $filtroSetor;
    $tipos .= "s";
}

// Construa a consulta SQL
$query = "SELECT id, usuario, motivo, descricao, setor, status, 
                 DATE_FORMAT(data_abertura, '%d/%m/%Y %H:%i:%s') as data_abertura_br 
          FROM chamados WHERE 1=1";

// Adicione os filtros à consulta
if (!empty($filtros)) {
    $query .= " AND " . implode(" AND ", $filtros);
}

// Prepare a consulta
$stmt = $conexao->prepare($query);

// Vincule os parâmetros dinamicamente
if (!empty($valores)) {
    $stmt->bind_param($tipos, ...$valores);
}

// Execute a consulta e verifique erros
if (!$stmt->execute()) {
    die("Erro na execução da consulta: " . $stmt->error);
}

$result = $stmt->get_result();
$totalChamados = $result->num_rows;

// Verifique se o botão de exportar para PDF foi clicado
if (isset($_POST['exportarPDF'])) {
    $pdf = new tFPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // Cabeçalho
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(255, 255, 255); // Branco
    $pdf->SetFillColor(35, 41, 38); // Fundo escuro
    $pdf->Cell(0, 10, utf8_decode('Relatório de Chamados'), 0, 1, 'C', true);
    $pdf->Ln(10);

    // Cabeçalho da Tabela
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(97, 105, 101); // Cinza escuro
    $pdf->SetTextColor(255, 255, 255); // Branco
    $pdf->Cell(20, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Usuario', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Motivo', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Setor', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Data/Hora', 1, 1, 'C', true);

    // Dados da Tabela
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Preto
    $pdf->SetFillColor(255, 255, 255); // Fundo branco

    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(20, 10, $row['id'], 1, 0, 'C', true);
        $pdf->Cell(40, 10, utf8_decode($row['usuario']), 1, 0, 'C', true);
        $pdf->Cell(50, 10, utf8_decode($row['motivo']), 1, 0, 'C', true);
        $pdf->Cell(30, 10, utf8_decode($row['setor']), 1, 0, 'C', true);
        $pdf->Cell(50, 10, $row['data_abertura_br'], 1, 1, 'C', true);
    }

    // Total de Chamados
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(35, 41, 38); // Fundo escuro
    $pdf->SetTextColor(255, 255, 255); // Branco
    $pdf->Cell(170, 10, utf8_decode('Total de Chamados:'), 1, 0, 'R', true);
    $pdf->Cell(30, 10, $totalChamados, 1, 0, 'C', true);

    // Saída do PDF
    $pdf->Output('D', 'relatorio_chamados.pdf');
    exit();
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios de Chamados</title>
    <link rel="stylesheet" href="relatorios.css">
</head>
<body>
    <h1>Relatórios de Chamados</h1>
    <form method="POST" class="form-filtro" >
        <label for="filtroDataInicial">Data Inicial:</label>
        <input type="date" id="filtroDataInicial" name="filtroDataInicial" value="<?= htmlspecialchars($filtroDataInicial) ?>">

        <label for="filtroDataFinal">Data Final:</label>
        <input type="date" id="filtroDataFinal" name="filtroDataFinal" value="<?= htmlspecialchars($filtroDataFinal) ?>">

        <label for="filtroMotivo">Motivo:</label>
        <select id="filtroMotivo" name="filtroMotivo" class="filtromotivo">
            <option value="">-- Selecione um motivo --</option>
            <option value="Programas em geral" <?= $filtroMotivo == "Programas em geral" ? 'selected' : '' ?>>Programas em geral</option>
            <option value="Problemas fisicos" <?= $filtroMotivo == "Problemas fisicos" ? 'selected' : '' ?>>Problemas Físicos</option>
            <option value="servidor" <?= $filtroMotivo == "Servidor" ? 'selected' : '' ?>>Servidor</option>
            <option value="genesis" <?= $filtroMotivo == "genesis" ? 'selected' : '' ?>>Gênesis</option>
            <option value="E-mails" <?= $filtroMotivo == "E-mails" ? 'selected' : '' ?>>E-mails</option>
            <option value="Ploomes" <?= $filtroMotivo == "Ploomes" ? 'selected' : '' ?>>Ploomes</option>
            <option value="Wifi" <?= $filtroMotivo == "Wifi" ? 'selected' : '' ?>>Wifi</option>
            <option value="Softcom" <?= $filtroMotivo == "Softcom" ? 'selected' : '' ?>>Softcom</option>
            <option value="Anydesk" <?= $filtroMotivo == "Anydesk" ? 'selected' : '' ?>>Anydesk</option>
            <option value="Senhas" <?= $filtroMotivo == "Senhas" ? 'selected' : '' ?>>Senhas</option>
            <option value="Sistemas de bancos" <?= $filtroMotivo == "Sistemas de bancos" ? 'selected' : '' ?>>Sistemas de bancos</option>
            <option value="Redes sociais" <?= $filtroMotivo == "Redes sociais" ? 'selected' : '' ?>>Redes sociais</option>
            <option value="Maxbot" <?= $filtroMotivo == "Maxbot" ? 'selected' : '' ?>>Maxbot</option>
            <option value="Outros" <?= $filtroMotivo == "Outros" ? 'selected' : '' ?>>Outros</option>
            <option value="Office" <?= $filtroMotivo == "Office" ? 'selected' : '' ?>>Office</option>
        </select>
        <label for="filtroSetor">Setor:</label>
        <select id="filtroSetor" name="filtroSetor" class="filtrosetor">
            <option value="">-- Selecione um Setor --</option>
            <option value="marketing" <?= $filtroSetor == "marketing" ? 'selected' : '' ?>>Marketing</option>
            <option value="tecnico" <?= $filtroSetor == "tecnico" ? 'selected' : '' ?>>Técnico</option>
            <option value="kazaseg" <?= $filtroSetor == "kazaseg" ? 'selected' : '' ?>>Kazaseg</option>
            <option value="alyconsultoria" <?= $filtroSetor == "alyconsultoria" ? 'selected' : '' ?>>Aly Consultoria</option>
            <option value="financeiro" <?= $filtroSetor == "financeiro" ? 'selected' : '' ?>>Financeiro</option>
            <option value="gerencia" <?= $filtroSetor == "gerencia" ? 'selected' : '' ?>>Gerência</option>
            <option value="comercial" <?= $filtroSetor == "comercial" ? 'selected' : '' ?>>Comercial</option>
        </select>



        <button type="submit" name="filtrar">Filtrar</button>
        <button type="submit" name="exportarPDF">Exportar para PDF</button>
        <button type="button" onclick="window.location.href='index.php';">Sair</button>
    </form>

    <table>
        <thead>
            <tr>
                <th class="coluna-id">ID</th>
                <th class="coluna-datahora">Data e Hora de Abertura</th>
                <th class="coluna-usuario">Usuário</th>
                <th>Motivo</th>
                <th>Descrição</th>
                <th class="coluna-setor">Setor</th>
                <th class="coluna-status">Status</th>
                
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['data_abertura_br']) ?></td>
                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                    <td><?= htmlspecialchars($row['motivo']) ?></td>
                    <td><?= htmlspecialchars($row['descricao']) ?></td>
                    <td><?= htmlspecialchars($row['setor']) ?></td>
                    <td>
                        <form method="POST" style="display: inline;" class="status">
                            <input type="hidden" name="idChamado" value="<?= $row['id'] ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="nao_iniciado" <?= $row['status'] == 'nao_iniciado' ? 'selected' : '' ?>>Não Iniciado</option>
                                <option value="em_andamento" <?= $row['status'] == 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                <option value="concluido" <?= $row['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                            </select>
                            <input type="hidden" name="alterarStatus" value="1">
                        </form>
                    </td>

                </tr>
                <?php endwhile; ?>
            <?php if ($totalChamados === 0): ?>
                <tr>
                    <td colspan="5">Nenhum chamado encontrado.</td>
                </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">Total de Chamados:</td>
                <td><?= $totalChamados ?></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
