<?php
// CRUD_CLIENTES/editar_cliente.php
session_start();
require_once '../INCLUDES/auth.php';
require_once '../INCLUDES/conexao.php';

// Verifica autenticação e acesso
verificarAcesso();

// Verifica se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "Cliente não especificado.";
    header('Location: listar_clientes.php');
    exit;
}

$id_cliente = (int)$_GET['id'];
$id_mei = $_SESSION['id_mei'];

try {
    // Busca os dados do cliente
    $sql = "SELECT * FROM clientes WHERE id_cliente = :id_cliente AND id_mei = :id_mei";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_cliente' => $id_cliente,
        ':id_mei' => $id_mei
    ]);
    
    $cliente = $stmt->fetch();
    
    // Verifica se o cliente existe e pertence ao MEI
    if (!$cliente) {
        $_SESSION['mensagem_erro'] = "Cliente não encontrado ou você não tem permissão para editá-lo.";
        header('Location: listar_clientes.php');
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['mensagem_erro'] = "Erro ao carregar dados do cliente.";
    header('Location: listar_clientes.php');
    exit;
}

// Verifica se tem mensagem de erro/sucesso vinda da action
$mensagem_erro = isset($_SESSION['mensagem_erro']) ? $_SESSION['mensagem_erro'] : null;
$mensagem_sucesso = isset($_SESSION['mensagem_sucesso']) ? $_SESSION['mensagem_sucesso'] : null;
unset($_SESSION['mensagem_erro'], $_SESSION['mensagem_sucesso']);

$page_title = "Editar Cliente";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyMei - <?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <h1><i class="fas fa-user-edit"></i> <?php echo $page_title; ?></h1>
                <p class="subtitle">Cliente #<?php echo $cliente['id_cliente']; ?> - <?php echo htmlspecialchars($cliente['nome_cliente']); ?></p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="visualizar_cliente.php?id=<?php echo $cliente['id_cliente']; ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i> Ver Dados
                </a>
                <a href="listar_clientes.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <!-- Mensagens -->
        <?php if ($mensagem_erro): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $mensagem_erro; ?>
            </div>
        <?php endif; ?>

        <?php if ($mensagem_sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $mensagem_sucesso; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de Edição -->
        <form id="formEditarCliente" action="editar_cliente_action.php" method="POST" novalidate>
            <!-- ID do Cliente (hidden) -->
            <input type="hidden" name="id_cliente" value="<?php echo $cliente['id_cliente']; ?>">
            <input type="hidden" name="tipo_cliente_original" value="<?php echo $cliente['tipo_cliente']; ?>">

            <!-- Tipo do Cliente (Apenas leitura) -->
            <div class="form-section">
                <h3><i class="fas fa-user-tag"></i> Tipo de Cliente</h3>
                <div class="radio-group" style="background: #f0f0f0; padding: 15px; border-radius: 6px;">
                    <label style="cursor: not-allowed; opacity: 0.7;">
                        <input type="radio" name="tipo_cliente" value="PF" 
                               <?php echo $cliente['tipo_cliente'] == 'PF' ? 'checked' : ''; ?> 
                               disabled>
                        <span class="radio-label">Pessoa Física</span>
                    </label>
                    <label style="cursor: not-allowed; opacity: 0.7;">
                        <input type="radio" name="tipo_cliente" value="PJ" 
                               <?php echo $cliente['tipo_cliente'] == 'PJ' ? 'checked' : ''; ?> 
                               disabled>
                        <span class="radio-label">Pessoa Jurídica</span>
                    </label>
                </div>
                <p class="helper-text"><i class="fas fa-info-circle"></i> O tipo de cliente não pode ser alterado após o cadastro.</p>
                <input type="hidden" name="tipo_cliente" value="<?php echo $cliente['tipo_cliente']; ?>">
            </div>

            <!-- Dados Pessoais -->
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Dados Pessoais</h3>
                
                <div class="form-group">
                    <label for="nome_cliente">
                        Nome / Razão Social <span class="required">*</span>
                    </label>
                    <input type="text" id="nome_cliente" name="nome_cliente" 
                           value="<?php echo htmlspecialchars($cliente['nome_cliente']); ?>"
                           placeholder="Ex: João da Silva ou Empresa XYZ" required>
                    <span class="helper-text">Para PF: nome completo; Para PJ: razão social</span>
                </div>

                <div class="form-group" id="grupo_nome_fantasia" 
                     style="<?php echo $cliente['tipo_cliente'] == 'PJ' ? 'display:block;' : 'display:none;'; ?>">
                    <label for="nome_fantasia_cliente">Nome Fantasia</label>
                    <input type="text" id="nome_fantasia_cliente" name="nome_fantasia_cliente" 
                           value="<?php echo htmlspecialchars($cliente['nome_fantasia_cliente']); ?>"
                           placeholder="Ex: Meu Negócio">
                    <span class="helper-text">Apenas para Pessoa Jurídica</span>
                </div>

                <!-- CPF/CNPJ - Dependendo do tipo -->
                <div class="form-group" id="grupo_cpf" 
                     style="<?php echo $cliente['tipo_cliente'] == 'PF' ? 'display:block;' : 'display:none;'; ?>">
                    <label for="cpf_cliente">
                        CPF <span class="required">*</span>
                    </label>
                    <input type="text" id="cpf_cliente" name="cpf_cliente" 
                           value="<?php echo !empty($cliente['cpf_cliente']) ? formatarDocumento($cliente['cpf_cliente'], 'PF') : ''; ?>"
                           placeholder="000.000.000-00" maxlength="14" required>
                    <span class="helper-text">Obrigatório para Pessoa Física</span>
                </div>

                <div class="form-group" id="grupo_cnpj" 
                     style="<?php echo $cliente['tipo_cliente'] == 'PJ' ? 'display:block;' : 'display:none;'; ?>">
                    <label for="cnpj_cliente">
                        CNPJ <span class="required">*</span>
                    </label>
                    <input type="text" id="cnpj_cliente" name="cnpj_cliente" 
                           value="<?php echo !empty($cliente['cnpj_cliente']) ? formatarDocumento($cliente['cnpj_cliente'], 'PJ') : ''; ?>"
                           placeholder="00.000.000/0000-00" maxlength="18" required>
                    <span class="helper-text">Obrigatório para Pessoa Jurídica</span>
                </div>

                <div class="form-group" id="grupo_inscricao_estadual" 
                     style="<?php echo $cliente['tipo_cliente'] == 'PJ' ? 'display:block;' : 'display:none;'; ?>">
                    <label for="inscricao_estadual_cliente">Inscrição Estadual</label>
                    <input type="text" id="inscricao_estadual_cliente" name="inscricao_estadual_cliente" 
                           value="<?php echo htmlspecialchars($cliente['inscricao_estadual_cliente']); ?>"
                           placeholder="Ex: 123.456.789">
                    <span class="helper-text">Apenas para Pessoa Jurídica (quando aplicável)</span>
                </div>
            </div>

            <!-- Contato -->
            <div class="form-section">
                <h3><i class="fas fa-phone"></i> Contato</h3>
                
                <div class="form-group">
                    <label for="telefone_cliente">
                        Telefone Principal <span class="required">*</span>
                    </label>
                    <input type="text" id="telefone_cliente" name="telefone_cliente" 
                           value="<?php echo formatarTelefone($cliente['telefone_cliente']); ?>"
                           placeholder="(11) 99999-9999" required>
                    <span class="helper-text">Com DDD (10 ou 11 dígitos)</span>
                </div>

                <div class="form-group">
                    <label for="telefone_secundario_cliente">Telefone Secundário</label>
                    <input type="text" id="telefone_secundario_cliente" name="telefone_secundario_cliente" 
                           value="<?php echo !empty($cliente['telefone_secundario_cliente']) ? formatarTelefone($cliente['telefone_secundario_cliente']) : ''; ?>"
                           placeholder="(11) 99999-9999">
                </div>

                <div class="form-group">
                    <label for="email_cliente">E-mail</label>
                    <input type="email" id="email_cliente" name="email_cliente" 
                           value="<?php echo htmlspecialchars($cliente['email_cliente']); ?>"
                           placeholder="cliente@email.com">
                    <span class="helper-text">Recomendado para enviar notas fiscais</span>
                </div>
            </div>

            <!-- Endereço -->
            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> Endereço</h3>
                
                <div class="form-row">
                    <div class="form-group col-3">
                        <label for="endereco_cep">CEP</label>
                        <input type="text" id="endereco_cep" name="endereco_cep" 
                               value="<?php echo !empty($cliente['endereco_cep']) ? substr($cliente['endereco_cep'], 0, 5) . '-' . substr($cliente['endereco_cep'], 5) : ''; ?>"
                               placeholder="00000-000" maxlength="9">
                    </div>
                    <div class="form-group col-9">
                        <label for="endereco_logradouro">Logradouro</label>
                        <input type="text" id="endereco_logradouro" name="endereco_logradouro" 
                               value="<?php echo htmlspecialchars($cliente['endereco_logradouro']); ?>"
                               placeholder="Rua, Avenida, etc.">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-3">
                        <label for="endereco_numero">Número</label>
                        <input type="text" id="endereco_numero" name="endereco_numero" 
                               value="<?php echo htmlspecialchars($cliente['endereco_numero']); ?>"
                               placeholder="123">
                    </div>
                    <div class="form-group col-3">
                        <label for="endereco_complemento">Complemento</label>
                        <input type="text" id="endereco_complemento" name="endereco_complemento" 
                               value="<?php echo htmlspecialchars($cliente['endereco_complemento']); ?>"
                               placeholder="Apto 101">
                    </div>
                    <div class="form-group col-6">
                        <label for="endereco_bairro">Bairro</label>
                        <input type="text" id="endereco_bairro" name="endereco_bairro" 
                               value="<?php echo htmlspecialchars($cliente['endereco_bairro']); ?>"
                               placeholder="Centro">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-8">
                        <label for="endereco_cidade">Cidade</label>
                        <input type="text" id="endereco_cidade" name="endereco_cidade" 
                               value="<?php echo htmlspecialchars($cliente['endereco_cidade']); ?>"
                               placeholder="São Paulo">
                    </div>
                    <div class="form-group col-4">
                        <label for="endereco_uf">UF</label>
                        <select id="endereco_uf" name="endereco_uf">
                            <option value="">Selecione</option>
                            <option value="AC" <?php echo $cliente['endereco_uf'] == 'AC' ? 'selected' : ''; ?>>AC</option>
                            <option value="AL" <?php echo $cliente['endereco_uf'] == 'AL' ? 'selected' : ''; ?>>AL</option>
                            <option value="AP" <?php echo $cliente['endereco_uf'] == 'AP' ? 'selected' : ''; ?>>AP</option>
                            <option value="AM" <?php echo $cliente['endereco_uf'] == 'AM' ? 'selected' : ''; ?>>AM</option>
                            <option value="BA" <?php echo $cliente['endereco_uf'] == 'BA' ? 'selected' : ''; ?>>BA</option>
                            <option value="CE" <?php echo $cliente['endereco_uf'] == 'CE' ? 'selected' : ''; ?>>CE</option>
                            <option value="DF" <?php echo $cliente['endereco_uf'] == 'DF' ? 'selected' : ''; ?>>DF</option>
                            <option value="ES" <?php echo $cliente['endereco_uf'] == 'ES' ? 'selected' : ''; ?>>ES</option>
                            <option value="GO" <?php echo $cliente['endereco_uf'] == 'GO' ? 'selected' : ''; ?>>GO</option>
                            <option value="MA" <?php echo $cliente['endereco_uf'] == 'MA' ? 'selected' : ''; ?>>MA</option>
                            <option value="MT" <?php echo $cliente['endereco_uf'] == 'MT' ? 'selected' : ''; ?>>MT</option>
                            <option value="MS" <?php echo $cliente['endereco_uf'] == 'MS' ? 'selected' : ''; ?>>MS</option>
                            <option value="MG" <?php echo $cliente['endereco_uf'] == 'MG' ? 'selected' : ''; ?>>MG</option>
                            <option value="PA" <?php echo $cliente['endereco_uf'] == 'PA' ? 'selected' : ''; ?>>PA</option>
                            <option value="PB" <?php echo $cliente['endereco_uf'] == 'PB' ? 'selected' : ''; ?>>PB</option>
                            <option value="PR" <?php echo $cliente['endereco_uf'] == 'PR' ? 'selected' : ''; ?>>PR</option>
                            <option value="PE" <?php echo $cliente['endereco_uf'] == 'PE' ? 'selected' : ''; ?>>PE</option>
                            <option value="PI" <?php echo $cliente['endereco_uf'] == 'PI' ? 'selected' : ''; ?>>PI</option>
                            <option value="RJ" <?php echo $cliente['endereco_uf'] == 'RJ' ? 'selected' : ''; ?>>RJ</option>
                            <option value="RN" <?php echo $cliente['endereco_uf'] == 'RN' ? 'selected' : ''; ?>>RN</option>
                            <option value="RS" <?php echo $cliente['endereco_uf'] == 'RS' ? 'selected' : ''; ?>>RS</option>
                            <option value="RO" <?php echo $cliente['endereco_uf'] == 'RO' ? 'selected' : ''; ?>>RO</option>
                            <option value="RR" <?php echo $cliente['endereco_uf'] == 'RR' ? 'selected' : ''; ?>>RR</option>
                            <option value="SC" <?php echo $cliente['endereco_uf'] == 'SC' ? 'selected' : ''; ?>>SC</option>
                            <option value="SP" <?php echo $cliente['endereco_uf'] == 'SP' ? 'selected' : ''; ?>>SP</option>
                            <option value="SE" <?php echo $cliente['endereco_uf'] == 'SE' ? 'selected' : ''; ?>>SE</option>
                            <option value="TO" <?php echo $cliente['endereco_uf'] == 'TO' ? 'selected' : ''; ?>>TO</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="form-section">
                <h3><i class="fas fa-pen"></i> Observações</h3>
                <div class="form-group">
                    <label for="observacoes_cliente">Observações</label>
                    <textarea id="observacoes_cliente" name="observacoes_cliente" 
                              rows="4" placeholder="Ex: Cliente prefere contato por WhatsApp; Tem restrição de horário"><?php echo htmlspecialchars($cliente['observacoes_cliente']); ?></textarea>
                </div>
            </div>

            <!-- Status do Cliente -->
            <div class="form-section">
                <h3><i class="fas fa-toggle-on"></i> Status do Cliente</h3>
                <div class="form-group">
                    <label style="font-weight: 600;">Situação do Cliente</label>
                    <div style="display: flex; gap: 20px; margin-top: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 20px; background: <?php echo $cliente['status_cliente'] == 'ativo' ? '#d4edda' : '#f8f9fa'; ?>; border: 2px solid <?php echo $cliente['status_cliente'] == 'ativo' ? '#28a745' : '#dce0e5'; ?>; border-radius: 6px;">
                            <input type="radio" name="status_cliente" value="ativo" 
                                   <?php echo $cliente['status_cliente'] == 'ativo' ? 'checked' : ''; ?>>
                            <i class="fas fa-circle" style="color: #28a745;"></i>
                            <span>Ativo</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 20px; background: <?php echo $cliente['status_cliente'] == 'inativo' ? '#f8d7da' : '#f8f9fa'; ?>; border: 2px solid <?php echo $cliente['status_cliente'] == 'inativo' ? '#dc3545' : '#dce0e5'; ?>; border-radius: 6px;">
                            <input type="radio" name="status_cliente" value="inativo" 
                                   <?php echo $cliente['status_cliente'] == 'inativo' ? 'checked' : ''; ?>>
                            <i class="fas fa-circle" style="color: #dc3545;"></i>
                            <span>Inativo</span>
                        </label>
                    </div>
                    <span class="helper-text" style="margin-top: 10px;">
                        <i class="fas fa-info-circle"></i> 
                        Clientes inativos não podem ser selecionados em novas Ordens de Serviço, mas mantêm o histórico.
                    </span>
                </div>
            </div>

            <!-- Informações Adicionais (apenas leitura) -->
            <div class="form-section" style="background: #f8f9fa; border: 1px dashed #dee2e6;">
                <h3><i class="fas fa-info-circle"></i> Informações do Cadastro</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">
                    <div>
                        <strong>Data de Cadastro:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($cliente['data_cadastro'])); ?>
                    </div>
                    <div>
                        <strong>ID do Cliente:</strong><br>
                        #<?php echo $cliente['id_cliente']; ?>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="btnSalvar">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Restaurar
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusao(<?php echo $cliente['id_cliente']; ?>)">
                    <i class="fas fa-trash"></i> Excluir Cliente
                </button>
            </div>
        </form>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div id="modalExclusao" class="modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000;">
        <div style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%;">
            <h3 style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão</h3>
            <p style="margin: 20px 0; line-height: 1.6;">
                Tem certeza que deseja excluir este cliente?<br>
                <strong><?php echo htmlspecialchars($cliente['nome_cliente']); ?></strong>
            </p>
            <p style="color: #6c757d; font-size: 14px;">
                <i class="fas fa-info-circle"></i> Clientes com Ordens de Serviço vinculadas não podem ser excluídos fisicamente, apenas inativados.
            </p>
            <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: flex-end;">
                <button onclick="fecharModalExclusao()" class="btn btn-secondary">Cancelar</button>
                <a href="excluir_cliente.php?id=<?php echo $cliente['id_cliente']; ?>" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Confirmar Exclusão
                </a>
            </div>
        </div>
    </div>

    <script>
        // Função para abrir modal de exclusão
        function confirmarExclusao(id) {
            document.getElementById('modalExclusao').style.display = 'flex';
        }

        function fecharModalExclusao() {
            document.getElementById('modalExclusao').style.display = 'none';
        }

        // Fechar modal ao clicar fora
        document.getElementById('modalExclusao').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalExclusao();
            }
        });
    </script>

    <script src="../JS/editar_cliente.js"></script>
</body>
</html>