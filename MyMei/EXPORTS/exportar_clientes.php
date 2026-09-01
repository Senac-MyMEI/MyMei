<?php
session_start();
require_once '../INCLUDES/conexao_banco.php';
require_once '../INCLUDES/functions.php';

// ========== VERIFICAÇÕES INICIAIS ==========
// RN001: Usuário deve estar logado e ter MEI vinculado
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_mei'])) {
    header('Location: ../login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$id_mei = $_SESSION['id_mei'];

// RN002: status_conta "ativa" e status_mei "ativo"
$sql = "SELECT u.status_conta, m.status_mei 
        FROM usuarios u 
        JOIN mei m ON u.id_usuario = m.id_usuario 
        WHERE u.id_usuario = ? AND m.id_mei = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_usuario, $id_mei]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result || $result['status_conta'] != 'ativa' || $result['status_mei'] != 'ativo') {
    die('❌ Conta ou MEI inativo. Não é possível exportar dados.');
}

// RN003: Apenas o proprietário do MEI pode exportar
$sql = "SELECT id_usuario FROM mei WHERE id_mei = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_mei]);
$mei = $stmt->fetch(PDO::FETCH_ASSOC);

if ($mei['id_usuario'] != $id_usuario) {
    die('❌ Você não tem permissão para exportar dados deste MEI.');
}

// ========== RECEBE PARÂMETROS ==========
$formato = $_GET['formato'] ?? 'CSV';
$periodo = $_GET['periodo'] ?? 'este_mes';

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
        // RN005: data_inicio deve ser <= data_fim
        if ($data_inicio > $data_fim) {
            die('❌ Data inicial deve ser menor ou igual à data final');
        }
        break;
    default:
        $data_inicio = date('Y-m-01');
        $data_fim = date('Y-m-t');
}

// ========== BUSCA DADOS ==========
// RN008: Dados apenas do MEI logado
$sql = "SELECT 
            id_cliente,
            tipo_cliente,
            nome_cliente,
            nome_fantasia_cliente,
            cpf_cliente,
            cnpj_cliente,
            inscricao_estadual_cliente,
            email_cliente,
            telefone_cliente,
            telefone_secundario_cliente,
            endereco_cep,
            endereco_logradouro,
            endereco_numero,
            endereco_complemento,
            endereco_bairro,
            endereco_cidade,
            endereco_uf,
            observacoes_cliente,
            DATE_FORMAT(data_cadastro, '%d/%m/%Y %H:%i') as data_cadastro_formatada,
            status_cliente
        FROM clientes 
        WHERE id_mei = ? 
        AND status_cliente = 'ativo'
        AND DATE(data_cadastro) BETWEEN ? AND ?
        ORDER BY nome_cliente";

$stmt = $conn->prepare($sql);
$stmt->execute([$id_mei, $data_inicio, $data_fim]);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========== GERA ARQUIVO ==========
// RN006: Formatos suportados
switch(strtoupper($formato)) {
    case 'CSV':
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="clientes_' . date('Ymd') . '.csv"');
        
        $output = fopen('php://output', 'w');
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalho (RN009: Senhas NUNCA são exportadas)
        fputcsv($output, [
            'ID',
            'Tipo',
            'Nome/Razão Social',
            'Nome Fantasia',
            'CPF',
            'CNPJ',
            'Inscrição Estadual',
            'E-mail',
            'Telefone',
            'Telefone Secundário',
            'CEP',
            'Logradouro',
            'Número',
            'Complemento',
            'Bairro',
            'Cidade',
            'UF',
            'Observações',
            'Data Cadastro',
            'Status'
        ], ';', '"');
        
        // Dados
        foreach ($clientes as $cliente) {
            fputcsv($output, [
                $cliente['id_cliente'],
                $cliente['tipo_cliente'] == 'PF' ? 'Pessoa Física' : 'Pessoa Jurídica',
                $cliente['nome_cliente'],
                $cliente['nome_fantasia_cliente'] ?? '',
                $cliente['cpf_cliente'] ?? '',
                $cliente['cnpj_cliente'] ?? '',
                $cliente['inscricao_estadual_cliente'] ?? '',
                $cliente['email_cliente'] ?? '',
                $cliente['telefone_cliente'],
                $cliente['telefone_secundario_cliente'] ?? '',
                $cliente['endereco_cep'] ?? '',
                $cliente['endereco_logradouro'] ?? '',
                $cliente['endereco_numero'] ?? '',
                $cliente['endereco_complemento'] ?? '',
                $cliente['endereco_bairro'] ?? '',
                $cliente['endereco_cidade'] ?? '',
                $cliente['endereco_uf'] ?? '',
                $cliente['observacoes_cliente'] ?? '',
                $cliente['data_cadastro_formatada'],
                $cliente['status_cliente']
            ], ';', '"');
        }
        
        fclose($output);
        break;
        
    case 'XLSX':
        // TODO: Implementar com PHPSpreadsheet
        die('Formato XLSX em desenvolvimento');
        break;
        
    case 'PDF':
        // TODO: Implementar com Dompdf
        die('Formato PDF em desenvolvimento');
        break;
        
    case 'JSON':
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="clientes_' . date('Ymd') . '.json"');
        echo json_encode($clientes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        break;
        
    default:
        die('❌ Formato não suportado');
}

// ========== REGISTRA LOG ==========
// RN010: Registrar log da exportação
$sql_log = "INSERT INTO exportacoes_log 
            (id_mei, id_usuario, tipo_dado_exportado, formato_arquivo, 
             periodo_inicio, periodo_fim, ip_origem, status_exportacao) 
            VALUES (?, ?, 'Clientes', ?, ?, ?, ?, 'Sucesso')";
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