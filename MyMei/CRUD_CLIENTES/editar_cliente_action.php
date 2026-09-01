<?php
// CRUD_CLIENTES/editar_cliente_action.php
session_start();
require_once '../INCLUDES/conexao.php';
require_once '../INCLUDES/auth.php';
require_once '../INCLUDES/validators.php';

// Verifica autenticação e acesso
verificarAcesso();

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar_clientes.php');
    exit;
}

// Verifica se o ID do cliente foi enviado
if (empty($_POST['id_cliente']) || !is_numeric($_POST['id_cliente'])) {
    $_SESSION['mensagem_erro'] = "Cliente não especificado.";
    header('Location: listar_clientes.php');
    exit;
}

$id_cliente = (int)$_POST['id_cliente'];
$id_mei = $_SESSION['id_mei'];

// Array para armazenar erros e dados
$erros = [];
$dados = [];
$alteracoes = [];

try {
    // 1. VERIFICA SE O CLIENTE PERTENCE AO MEI (RN003)
    $sql_verifica = "SELECT * FROM clientes WHERE id_cliente = :id_cliente AND id_mei = :id_mei";
    $stmt_verifica = $pdo->prepare($sql_verifica);
    $stmt_verifica->execute([
        ':id_cliente' => $id_cliente,
        ':id_mei' => $id_mei
    ]);
    $cliente_original = $stmt_verifica->fetch();
    
    if (!$cliente_original) {
        $_SESSION['mensagem_erro'] = "Cliente não encontrado ou você não tem permissão para editá-lo.";
        header('Location: listar_clientes.php');
        exit;
    }

    // 2. VALIDAÇÃO DO TIPO DE CLIENTE
    if (empty($_POST['tipo_cliente']) || !in_array($_POST['tipo_cliente'], ['PF', 'PJ'])) {
        $erros[] = "Tipo de cliente inválido.";
    } else {
        $dados['tipo_cliente'] = $_POST['tipo_cliente'];
    }

    // 3. VALIDAÇÃO DO NOME (RN006)
    if (empty($_POST['nome_cliente']) || strlen(trim($_POST['nome_cliente'])) < 3) {
        $erros[] = "Nome/Razão Social é obrigatório (mínimo 3 caracteres).";
    } else {
        $dados['nome_cliente'] = trim($_POST['nome_cliente']);
        if ($dados['nome_cliente'] != $cliente_original['nome_cliente']) {
            $alteracoes[] = "Nome/Razão Social: '" . $cliente_original['nome_cliente'] . "' → '" . $dados['nome_cliente'] . "'";
        }
    }

    // 4. VALIDAÇÃO DO NOME FANTASIA (opcional)
    $dados['nome_fantasia_cliente'] = isset($_POST['nome_fantasia_cliente']) ? 
                                      trim($_POST['nome_fantasia_cliente']) : null;
    if ($dados['nome_fantasia_cliente'] != $cliente_original['nome_fantasia_cliente']) {
        $alteracoes[] = "Nome Fantasia: '" . ($cliente_original['nome_fantasia_cliente'] ?? '') . "' → '" . ($dados['nome_fantasia_cliente'] ?? '') . "'";
    }

    // 5. VALIDAÇÃO DE CPF (RN007)
    if ($dados['tipo_cliente'] == 'PF') {
        if (empty($_POST['cpf_cliente']) || !validarCPF($_POST['cpf_cliente'])) {
            $erros[] = "CPF é obrigatório para Pessoa Física e deve ser válido.";
        } else {
            $dados['cpf_cliente'] = preg_replace('/[^0-9]/', '', $_POST['cpf_cliente']);
            if ($dados['cpf_cliente'] != $cliente_original['cpf_cliente']) {
                $alteracoes[] = "CPF: '" . formatarDocumento($cliente_original['cpf_cliente'], 'PF') . "' → '" . formatarDocumento($dados['cpf_cliente'], 'PF') . "'";
            }
        }
        $dados['cnpj_cliente'] = null;
    } 
    // 6. VALIDAÇÃO DE CNPJ (RN008)
    else if ($dados['tipo_cliente'] == 'PJ') {
        if (empty($_POST['cnpj_cliente']) || !validarCNPJ($_POST['cnpj_cliente'])) {
            $erros[] = "CNPJ é obrigatório para Pessoa Jurídica e deve ser válido.";
        } else {
            $dados['cnpj_cliente'] = preg_replace('/[^0-9]/', '', $_POST['cnpj_cliente']);
            if ($dados['cnpj_cliente'] != $cliente_original['cnpj_cliente']) {
                $alteracoes[] = "CNPJ: '" . formatarDocumento($cliente_original['cnpj_cliente'], 'PJ') . "' → '" . formatarDocumento($dados['cnpj_cliente'], 'PJ') . "'";
            }
        }
        $dados['cpf_cliente'] = null;
    }

    // 7. VALIDAÇÃO DE CPF/CNPJ ÚNICO POR MEI (RN004)
    // Verifica se outro cliente do mesmo MEI já possui este documento
    if (!isset($erros)) {
        $sql_verifica_doc = "SELECT COUNT(*) as total FROM clientes 
                             WHERE id_mei = :id_mei 
                             AND id_cliente != :id_cliente
                             AND ((cpf_cliente = :cpf AND cpf_cliente IS NOT NULL AND cpf_cliente != '') 
                                  OR (cnpj_cliente = :cnpj AND cnpj_cliente IS NOT NULL AND cnpj_cliente != ''))";
        $stmt_verifica_doc = $pdo->prepare($sql_verifica_doc);
        $stmt_verifica_doc->execute([
            ':id_mei' => $id_mei,
            ':id_cliente' => $id_cliente,
            ':cpf' => $dados['cpf_cliente'] ?? '',
            ':cnpj' => $dados['cnpj_cliente'] ?? ''
        ]);
        $resultado_doc = $stmt_verifica_doc->fetch();
        
        if ($resultado_doc['total'] > 0) {
            $erros[] = "Este documento já está cadastrado para outro cliente do seu MEI.";
        }
    }

    // 8. VALIDAÇÃO DA INSCRIÇÃO ESTADUAL (opcional)
    $dados['inscricao_estadual_cliente'] = !empty($_POST['inscricao_estadual_cliente']) ? 
                                           trim($_POST['inscricao_estadual_cliente']) : null;
    if ($dados['inscricao_estadual_cliente'] != $cliente_original['inscricao_estadual_cliente']) {
        $alteracoes[] = "Inscrição Estadual: '" . ($cliente_original['inscricao_estadual_cliente'] ?? '') . "' → '" . ($dados['inscricao_estadual_cliente'] ?? '') . "'";
    }

    // 9. VALIDAÇÃO DO TELEFONE (RN005)
    if (empty($_POST['telefone_cliente']) || !validarTelefone($_POST['telefone_cliente'])) {
        $erros[] = "Telefone é obrigatório e deve ter 10 ou 11 dígitos.";
    } else {
        $dados['telefone_cliente'] = preg_replace('/[^0-9]/', '', $_POST['telefone_cliente']);
        if ($dados['telefone_cliente'] != $cliente_original['telefone_cliente']) {
            $alteracoes[] = "Telefone: '" . formatarTelefone($cliente_original['telefone_cliente']) . "' → '" . formatarTelefone($dados['telefone_cliente']) . "'";
        }
    }

    // 10. VALIDAÇÃO DO TELEFONE SECUNDÁRIO (opcional)
    $dados['telefone_secundario_cliente'] = !empty($_POST['telefone_secundario_cliente']) ? 
                                            preg_replace('/[^0-9]/', '', $_POST['telefone_secundario_cliente']) : null;
    if (!empty($dados['telefone_secundario_cliente']) && !validarTelefone($dados['telefone