<?php
session_start();
require_once '../INCLUDES/conexao_banco.php';
require_once '../INCLUDES/functions.php';

// ========== VERIFICAÇÕES INICIAIS ==========
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_mei'])) {
    header('Location: ../login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_mei = $_SESSION['id_mei'];

// RN002: Verifica status
$sql = "SELECT u.status_conta, m.status_mei 
        FROM usuarios u 
        JOIN mei m ON u.id_usuario = m.id_usuario 
        WHERE u.id_usuario = ? AND m.id_mei = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_usuario, $id_mei]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result || $result['status_conta'] != 'ativa' || $result['status_mei'] != 'ativo') {
    die('❌ Conta ou MEI inativo.');
}

// RN003: Verifica proprietário
$sql = "SELECT id_usuario FROM mei WHERE id_mei = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_mei]);
$mei = $stmt->fetch(PDO::FETCH_ASSOC);

if ($mei['id_usuario'] != $id_usuario) {
    die('❌ Sem permissão.');
}

// ========== PARÂMETROS ==========
$formato = $_GET['formato'] ?? 'CSV';
$periodo = $_GET['periodo'] ?? 'este_mes';
$status_servico = $_GET['status_servico'] ?? 'todos'; // Filtro extra

// Calcula período
$data_inicio = '';
$data_fim = '';
$data_atual = new DateTime();

switch($periodo) {
    case 'este_mes':
        $data_inicio = $data_atual->format('Y-m-01');
        $data_fim = $data_atual->format('Y-m-t');
        break;
    case 'mes_passado':
        $data_atual->modify('first day of previous month');
        $data_inicio = $data_atual->format('Y-m-01');
        $data_fim = $data_atual->format('Y-m-t');
        break;
    case 'este_ano':
        $data_inicio = $data_atual->format('Y-01-01');
        $data_fim = $data_atual->format('Y-12-31');
        break;
    case 'personalizado':
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['data_fim'] ?? date('Y-m-t');
        if ($data_inicio > $data_fim) {
            die('❌ Data inicial deve ser menor ou igual à data final');
        }
        break;
}

// ========== BUSCA DADOS ==========
$sql = "SELECT 
            o.id_os,
            o.numero_os,
            o.tipo_os,
            o.data_abertura,
            DATE_FORMAT(o.data_abertura, '%d/%m/%Y %H:%i') as data_abertura_formatada,
            o.data_prevista,
            DATE_FORMAT(o.data_prevista, '%d/%m/%Y') as data_prevista_formatada,
            o.data_conclusao,
            DATE_FORMAT(o.data_conclusao, '%d/%m/%Y %H:%i') as data_conclusao_formatada,
            o.descricao_os,
            o.valor_servicos,
            o.valor_produtos,
            o.valor_total,
            o.valor_pago,
            o.status_servico,
            o.status_pagamento,
            o.observacoes_os,
            c.nome_cliente,
            c.cpf_cliente,
            c.cnpj_cliente,
            c.telefone_cliente,
            c.email_cliente
        FROM ordens_servico o
        JOIN clientes c ON o.id_cliente = c.id_cliente
        WHERE o.id_mei = ? 
        AND DATE(o.data_abertura) BETWEEN ? AND ?";

// Filtro adicional por status (se não for 'todos')
if ($status_servico != 'todos') {
    $sql .= " AND o.status_servico = ?";
}

$sql .= " ORDER BY o.data_abertura DESC";

$stmt = $conn->prepare($sql);
if ($status_servico != 'todos') {
    $stmt->execute([$id_mei, $data_inicio, $data_fim, $status_servico]);
} else {
    $stmt->execute([$id_mei, $data_inicio, $data_fim]);
}
$ordens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========== GERA ARQUIVO ==========
switch(strtoupper($formato)) {
    case 'CSV':
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ordens_servico_' . date('Ymd') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'Número OS',
            'Cliente',
            'CPF/CNPJ',
            'Telefone',
            'E-mail',
            'Tipo',
            'Data Abertura',
            'Data Prevista',
            'Data Conclusão',
            'Descrição',
            'Valor Serviços',
            'Valor Produtos',
            'Valor Total',
            'Valor Pago',
            'Saldo',
            'Status Serviço',
            'Status Pagamento',
            'Observações'
        ], ';', '"');
        
        foreach ($ordens as $os) {
            $identificacao = !empty($os['cpf_cliente']) ? $os['cpf_cliente'] : $os['cnpj_cliente'];
            
            fputcsv($output, [
                $os['numero_os'],
                $os['nome_cliente'],
                $identificacao ?? '',
                $os['telefone_cliente'],
                $os['email_cliente'] ?? '',
                $os['tipo_os'],
                $os['data_abertura_formatada'],
                $os['data_prevista_formatada'] ?? '',
                $os['data_conclusao_formatada'] ?? '',
                $os['descricao_os'],
                number_format($os['valor_servicos'] ?? 0, 2, ',', '.'),
                number_format($os['valor_produtos'] ?? 0, 2, ',', '.'),
                number_format($os['valor_total'], 2, ',', '.'),
                number_format($os['valor_pago'], 2, ',', '.'),
                number_format($os['valor_total'] - $os['valor_pago'], 2, ',', '.'),
                $os['status_servico'],
                $os['status_pagamento'],
                $os['observacoes_os'] ?? ''
            ], ';', '"');
        }
        
        fclose($output);
        break;
        
    case 'JSON':
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="ordens_servico_' . date('Ymd') . '.json"');
        echo json_encode($ordens, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        break;
        
    default:
        die('❌ Formato não suportado');
}

// ========== REGISTRA LOG ==========
$sql_log = "INSERT INTO exportacoes_log 
            (id_mei, id_usuario, tipo_dado_exportado, formato_arquivo, 
             periodo_inicio, periodo_fim, ip_origem, status_exportacao) 
            VALUES (?, ?, 'Ordens_Servico', ?, ?, ?, ?, 'Sucesso')";
$stmt_log = $conn->prepare($sql_log);
$stmt_log->execute([
    $id_mei,
    $id_usuario,
    $formato,
    $data_inicio,
    $data_fim,
    $_SERVER['REMOTE_ADDR']
]);

exit;
?>
