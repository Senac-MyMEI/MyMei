<?php
// CRUD_CLIENTES/cadastrar_cliente_action.php
session_start();
require_once '../INCLUDES/conexao.php';
require_once '../INCLUDES/auth.php';
require_once '../INCLUDES/validators.php';

// Verifica autenticação e acesso
verificarAcesso();

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastrar_cliente.php');
    exit;
}

// Array para armazenar erros
$erros = [];
$dados = [];

try {
    // 1. VALIDAÇÃO DO TIPO DE CLIENTE
    if (empty($_POST['tipo_cliente']) || !in_array($_POST['tipo_cliente'], ['PF', 'PJ'])) {
        $erros[] = "Tipo de cliente é obrigatório (PF ou PJ)";
    } else {
        $dados['tipo_cliente'] = $_POST['tipo_cliente'];
    }

    // 2. VALIDAÇÃO DO NOME
    if (empty($_POST['nome_cliente']) || strlen(trim($_POST['nome_cliente'])) < 3) {
        $erros[] = "Nome/Razão Social é obrigatório (mínimo 3 caracteres)";
    } else {
        $dados['nome_cliente'] = trim($_POST['nome_cliente']);
    }

    // 3. VALIDAÇÃO DO NOME FANTASIA (opcional)
    $dados['nome_fantasia_cliente'] = isset($_POST['nome_fantasia_cliente']) ? 
                                      trim($_POST['nome_fantasia_cliente']) : null;

    // 4. VALIDAÇÃO DE CPF
    if ($dados['tipo_cliente'] == 'PF') {
        if (empty($_POST['cpf_cliente']) || !validarCPF($_POST['cpf_cliente'])) {
            $erros[] = "CPF é obrigatório para Pessoa Física e deve ser válido";
        } else {
            $dados['cpf_cliente'] = preg_replace('/[^0-9]/', '', $_POST['cpf_cliente']);
        }
        $dados['cnpj_cliente'] = null;
    } 
    // 5. VALIDAÇÃO DE CNPJ
    else if ($dados['tipo_cliente'] == 'PJ') {
        if (empty($_POST['cnpj_cliente']) || !validarCNPJ($_POST['cnpj_cliente'])) {
            $erros[] = "CNPJ é obrigatório para Pessoa Jurídica e deve ser válido";
        } else {
            $dados['cnpj_cliente'] = preg_replace('/[^0-9]/', '', $_POST['cnpj_cliente']);
        }
        $dados['cpf_cliente'] = null;
    }

    // 6. VALIDAÇÃO DE CPF/CNPJ ÚNICO POR MEI
    if (!isset($erros)) {
        $sql = "SELECT COUNT(*) as total FROM clientes 
                WHERE id_mei = :id_mei 
                AND ((cpf_cliente = :cpf AND cpf_cliente IS NOT NULL) 
                     OR (cnpj_cliente = :cnpj AND cnpj_cliente IS NOT NULL))";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_mei' => $_SESSION['id_mei'],
            ':cpf' => $dados['cpf_cliente'],
            ':cnpj' => $dados['cnpj_cliente']
        ]);
        $resultado = $stmt->fetch();
        
        if ($resultado['total'] > 0) {
            $erros[] = "Este documento já está cadastrado para outro cliente do seu MEI";
        }
    }

    // 7. VALIDAÇÃO DO TELEFONE
    if (empty($_POST['telefone_cliente']) || !validarTelefone($_POST['telefone_cliente'])) {
        $erros[] = "Telefone é obrigatório e deve ter 10 ou 11 dígitos";
    } else {
        $dados['telefone_cliente'] = preg_replace('/[^0-9]/', '', $_POST['telefone_cliente']);
    }

    // 8. VALIDAÇÃO DO TELEFONE SECUNDÁRIO (opcional)
    if (!empty($_POST['telefone_secundario_cliente'])) {
        if (!validarTelefone($_POST['telefone_secundario_cliente'])) {
            $erros[] = "Telefone secundário deve ter 10 ou 11 dígitos";
        } else {
            $dados['telefone_secundario_cliente'] = preg_replace('/[^0-9]/', '', $_POST['telefone_secundario_cliente']);
        }
    } else {
        $dados['telefone_secundario_cliente'] = null;
    }

    // 9. VALIDAÇÃO DO E-MAIL (opcional)
    if (!empty($_POST['email_cliente'])) {
        if (!validarEmail($_POST['email_cliente'])) {
            $erros[] = "E-mail inválido";
        } else {
            $dados['email_cliente'] = trim($_POST['email_cliente']);
        }
    } else {
        $dados['email_cliente'] = null;
    }

    // 10. DADOS DE ENDEREÇO (opcionais)
    $dados['endereco_cep'] = !empty($_POST['endereco_cep']) ? 
                             preg_replace('/[^0-9]/', '', $_POST['endereco_cep']) : null;
    $dados['endereco_logradouro'] = !empty($_POST['endereco_logradouro']) ? 
                                    trim($_POST['endereco_logradouro']) : null;
    $dados['endereco_numero'] = !empty($_POST['endereco_numero']) ? 
                                trim($_POST['endereco_numero']) : null;
    $dados['endereco_complemento'] = !empty($_POST['endereco_complemento']) ? 
                                     trim($_POST['endereco_complemento']) : null;
    $dados['endereco_bairro'] = !empty($_POST['endereco_bairro']) ? 
                                trim($_POST['endereco_bairro']) : null;
    $dados['endereco_cidade'] = !empty($_POST['endereco_cidade']) ? 
                                trim($_POST['endereco_cidade']) : null;
    $dados['endereco_uf'] = !empty($_POST['endereco_uf']) ? 
                            trim($_POST['endereco_uf']) : null;

    // 11. OBSERVAÇÕES (opcional)
    $dados['observacoes_cliente'] = !empty($_POST['observacoes_cliente']) ? 
                                    trim($_POST['observacoes_cliente']) : null;

    // 12. VALIDAÇÃO DA INSCRIÇÃO ESTADUAL (opcional)
    $dados['inscricao_estadual_cliente'] = !empty($_POST['inscricao_estadual_cliente']) ? 
                                           trim($_POST['inscricao_estadual_cliente']) : null;

    // SE HOUVER ERROS, REDIRECIONA COM MENSAGENS
    if (!empty($erros)) {
        $_SESSION['mensagem_erro'] = implode('<br>', $erros);
        $_SESSION['dados_form'] = $_POST; // Preserva os dados preenchidos
        header('Location: cadastrar_cliente.php');
        exit;
    }

    // 13. INSERÇÃO NO BANCO DE DADOS
    $sql = "INSERT INTO clientes (
                id_mei, tipo_cliente, nome_cliente, nome_fantasia_cliente,
                cpf_cliente, cnpj_cliente, inscricao_estadual_cliente,
                email_cliente, telefone_cliente, telefone_secundario_cliente,
                endereco_cep, endereco_logradouro, endereco_numero, 
                endereco_complemento, endereco_bairro, endereco_cidade, 
                endereco_uf, observacoes_cliente, status_cliente
            ) VALUES (
                :id_mei, :tipo_cliente, :nome_cliente, :nome_fantasia_cliente,
                :cpf_cliente, :cnpj_cliente, :inscricao_estadual_cliente,
                :email_cliente, :telefone_cliente, :telefone_secundario_cliente,
                :endereco_cep, :endereco_logradouro, :endereco_numero,
                :endereco_complemento, :endereco_bairro, :endereco_cidade,
                :endereco_uf, :observacoes_cliente, 'ativo'
            )";

    $stmt = $pdo->prepare($sql);
    $dados['id_mei'] = $_SESSION['id_mei'];
    
    $stmt->execute($dados);

    // 14. REGISTRA LOG DA OPERAÇÃO
    $id_cliente = $pdo->lastInsertId();
    
    $sql_log = "INSERT INTO logs (id_mei, id_usuario, acao, tabela, id_registro, descricao, data_hora)
                VALUES (:id_mei, :id_usuario, 'CREATE', 'clientes', :id_registro, :descricao, NOW())";
    
    $stmt_log = $pdo->prepare($sql_log);
    $stmt_log->execute([
        ':id_mei' => $_SESSION['id_mei'],
        ':id_usuario' => $_SESSION['usuario_id'],
        ':id_registro' => $id_cliente,
        ':descricao' => "Cadastro de cliente: " . $dados['nome_cliente'] . " (Tipo: " . $dados['tipo_cliente'] . ")"
    ]);

    // 15. SUCESSO - REDIRECIONA COM MENSAGEM
    $_SESSION['mensagem_sucesso'] = "Cliente cadastrado com sucesso!";
    header('Location: listar_clientes.php');
    exit;

} catch (PDOException $e) {
    // Erro no banco de dados
    $_SESSION['mensagem_erro'] = "Erro ao cadastrar cliente: " . $e->getMessage();
    header('Location: cadastrar_cliente.php');
    exit;
}
?>