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

// RN002
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

// RN003
$sql = "SELECT id_usuario FROM mei WHERE id_mei = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_mei]);
$mei = $stmt->fetch(PDO::FETCH_ASSOC);

if ($mei['id_usuario'] != $id_usuario) {
    die('❌ Sem permissão.');
}

// ========== PARÂMETROS ==========
$formato = $_GET['formato'] ?? 'CSV';
$categoria = $_GET['categoria'] ?? 'todos';
$status_produto = $_GET['status'] ?? 'todos';

// ========== BUSCA DADOS ==========
$sql = "SELECT 
            id_produto,
            nome_produto,
            descricao_produto,
            categoria,
            codigo_barras,
            unidade_medida,
            quantidade_estoque,
            estoque_minimo,
            preco_custo,
            preco_venda,
            fornecedor,
            status_produto,
            DATE_FORMAT(data_cadastro, '%d/%m/%Y') as data_cadastro_formatada,
            CASE 
                WHEN quantidade_estoque <= estoque_minimo THEN '⚠️ Baixo'
                WHEN quantidade_estoque = 0 THEN '🚫 Zerado'
                ELSE '✅ Normal'
            END as situacao_estoque
        FROM produtos 
        WHERE id_mei = ?";

// Filtros
if ($categoria != 'todos') {
    $sql .= " AND categoria = ?";
}
if ($status_produto != 'todos') {
    $sql .= " AND status_produto = ?";
}

$sql .= " ORDER BY nome_produto";

$stmt = $conn->prepare($sql);
$params = [$id_mei];
if ($categoria != 'todos') {
    $params[] = $categoria;
}
if ($status_produto != 'todos') {
    $params[] = $status_produto;
}
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========== GERA ARQUIVO ==========
switch(strtoupper($formato)) {
    case 'CSV':
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="estoque_' . date('Ymd') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'ID',
            'Produto',
            'Descrição',
            'Categoria',
            'Código Barras',
            'Unidade',
            'Quantidade',
            'Estoque Mínimo',
            'Situação',
            'Preço Custo',
            'Preço Venda',
            'Margem (%)',
            'Fornecedor',
            'Status',
            'Data Cadastro'
        ], ';', '"');
        
        foreach ($produtos as $produto) {
            $margem = $produto['preco_custo'] > 0 ? 
                (($produto['preco_venda'] - $produto['preco_custo']) / $produto['preco_custo'] * 100) : 0;
            
            fputcsv($output, [
                $produto['id_produto'],
                $produto['nome_produto'],
                $produto['descricao_produto'] ?? '',
                $produto['categoria'] ?? '',
                $produto['codigo_barras'] ?? '',
                $produto['unidade_medida'],
                $produto['quantidade_estoque'],
                $produto['estoque_minimo'] ?? '0',
                $produto['situacao_estoque'],
                number_format($produto['preco_custo'], 2, ',', '.'),
                number_format($produto['preco_venda'], 2, ',', '.'),
                number_format($margem, 2, ',', '.'),
                $produto['fornecedor'] ?? '',
                $produto['status_produto'],
                $produto['data_cadastro_formatada']
            ], ';', '"');
        }
        
        fclose($output);
        break;
        
    case 'JSON':
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="estoque_' . date('Ymd') . '.json"');
        echo json_encode($produtos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        break;
        
    default:
        die('❌ Formato não suportado');
}

// ========== REGISTRA LOG ==========
$sql_log = "INSERT INTO exportacoes_log 
            (id_mei, id_usuario, tipo_dado_exportado, formato_arquivo, 
             ip_origem, status_exportacao) 
            VALUES (?, ?, 'Estoque', ?, ?, 'Sucesso')";
$stmt_log = $conn->prepare($sql_log);
$stmt_log->execute([
    $id_mei,
    $id_usuario,
    $formato,
    $_SERVER['REMOTE_ADDR']
]);

exit;
?>