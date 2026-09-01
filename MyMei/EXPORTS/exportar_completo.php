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
        if ($data_inicio > $data_fim) {
            die('❌ Data inicial deve ser menor ou igual à data final');
        }
        break;
}

// ========== EXPORTAÇÃO COMPLETA ==========
// RN007: Múltiplos tipos selecionados

$tipos = [
    'clientes' => 'Clientes',
    'ordens_servico' => 'Ordens_Servico',
    'estoque' => 'Estoque',
    'movimentacoes' => 'Movimentacoes'
];

$arquivos_gerados = [];
$temp_dir = '../temp/';

// Cria diretório temp se não existir
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0777, true);
}

// Para cada tipo, chama o arquivo específico com parâmetros
foreach ($tipos as $tipo => $nome_tipo) {
    $arquivo = "exportar_{$tipo}.php";
    
    if (file_exists($arquivo)) {
        // O arquivo específico vai gerar e baixar, então precisamos capturar
        // Como cada arquivo já faz o download, vamos gerar e salvar em disco
        
        $nome_arquivo = "{$tipo}_" . date('Ymd') . '.' . strtolower($formato);
        $caminho_completo = $temp_dir . $nome_arquivo;
        
        // Usa cURL ou file_get_contents para chamar o script
        $url = "http://" . $_SERVER['HTTP_HOST'] . "/EXPORTS/{$arquivo}?formato={$formato}&periodo={$periodo}&data_inicio={$data_inicio}&data_fim={$data_fim}";
        
        // Como não podemos fazer requisição HTTP interna facilmente, 
        // vamos incluir o arquivo e capturar a saída
        ob_start();
        
        // Define variáveis necessárias
        $_GET['formato'] = $formato;
        $_GET['periodo'] = $periodo;
        $_GET['data_inicio'] = $data_inicio;
        $_GET['data_fim'] = $data_fim;
        
        include $arquivo;
        $conteudo = ob_get_clean();
        
        // Salva o conteúdo
        file_put_contents($caminho_completo, $conteudo);
        
        $arquivos_gerados[] = [
            'nome' => $nome_arquivo,
            'tipo' => $nome_tipo,
            'caminho' => $caminho_completo
        ];
    }
}

// ========== CRIA ZIP ==========
// RN007: Múltiplos arquivos em um ZIP
if (count($arquivos_gerados) > 1) {
    $zip_nome = 'exportacao_completa_' . date('Ymd') . '.zip';
    $zip_caminho = $temp_dir . $zip_nome;
    
    $zip = new ZipArchive();
    if ($zip->open($zip_caminho, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        foreach ($arquivos_gerados as $arquivo) {
            if (file_exists($arquivo['caminho'])) {
                $zip->addFile($arquivo['caminho'], $arquivo['nome']);
            }
        }
        $zip->close();
        
        // Remove arquivos individuais
        foreach ($arquivos_gerados as $arquivo) {
            if (file_exists($arquivo['caminho'])) {
                unlink($arquivo['caminho']);
            }
        }
        
        // Faz download do ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_nome . '"');
        header('Content-Length: ' . filesize($zip_caminho));
        readfile($zip_caminho);
        
        // Remove ZIP após download
        unlink($zip_caminho);
        
    } else {
        die('❌ Erro ao criar arquivo ZIP');
    }
} elseif (count($arquivos_gerados) == 1) {
    // Apenas um arquivo, faz download direto
    $arquivo = $arquivos_gerados[0];
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $arquivo['nome'] . '"');
    header('Content-Length: ' . filesize($arquivo['caminho']));
    readfile($arquivo['caminho']);
    unlink($arquivo['caminho']);
} else {
    die('❌ Nenhum arquivo foi gerado');
}

// ========== REGISTRA LOG ==========
// RN010: Log da exportação
$tipos_str = implode(', ', array_column($arquivos_gerados, 'tipo'));
$sql_log = "INSERT INTO exportacoes_log 
            (id_mei, id_usuario, tipo_dado_exportado, formato_arquivo, 
             periodo_inicio, periodo_fim, ip_origem, status_exportacao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Sucesso')";
$stmt_log = $conn->prepare($sql_log);
$stmt_log->execute([
    $id_mei,
    $id_usuario,
    $tipos_str,
    $formato,
    $data_inicio,
    $data_fim,
    $_SERVER['REMOTE_ADDR']
]);

exit;
?>
