--CRIAÇÃO DO BANCO DE DADOS MYMEI--

CREATE DATABASE IF NOT EXISTS MyMei;

--CRIAÇÃO DA TABELA USUARIOS-----------------------------------------------------------------------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS usuarios (
    -- Chave primária
    id_usuario INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do usuário',
    
    -- Dados pessoais
    cpf_usuario VARCHAR(11) NULL COMMENT 'CPF do usuário (apenas números)',
    nome_usuario VARCHAR(100) NOT NULL COMMENT 'Nome completo do usuário',
    email_usuario VARCHAR(100) NOT NULL COMMENT 'E-mail para acesso e contato',
    telefone_usuario VARCHAR(15) NOT NULL COMMENT 'Telefone para contato (com DDD)',
    
    -- Segurança
    senha_hash VARCHAR(255) NOT NULL COMMENT 'Hash da senha de acesso',
    
    -- Plano e pagamento
    plano_usuario ENUM('basico', 'premium', 'teste') NOT NULL DEFAULT 'teste' COMMENT 'Plano contratado',
    status_pagamento ENUM('pago', 'pendente', 'cancelado', 'expirado') NOT NULL DEFAULT 'pendente' COMMENT 'Situação do pagamento',
    
    -- Termos de uso
    aceite_termos BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Aceite dos Termos de Uso (1 = sim, 0 = não)',
    data_aceite_termos DATETIME NULL COMMENT 'Data e hora do aceite dos termos',
    
    -- Controle da conta
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação da conta',
    data_cancelamento DATE NULL COMMENT 'Data de cancelamento da conta (se aplicável)',
    status_conta ENUM('ativa', 'inativa', 'suspensa', 'cancelada') NOT NULL DEFAULT 'ativa' COMMENT 'Situação da conta',
    
    -- Chave primária
    PRIMARY KEY (id_usuario),
    
    -- Chaves únicas (evita duplicidade)
    UNIQUE KEY uk_usuario_email (email_usuario),
    UNIQUE KEY uk_usuario_cpf (cpf_usuario)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cadastro de usuários do sistema MyMei';

--CRIAÇÃO DA TABELA MEI-----------------------------------------------------------------------------------------------------------------------------------


CREATE TABLE IF NOT EXISTS mei (
    -- Chave primária
    id_mei INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da empresa MEI',
    
    -- Chave estrangeira (vinculo com o usuário)
    id_usuario INT NOT NULL COMMENT 'Referência ao usuário proprietário da conta',
    
    -- Dados da empresa
    cnpj_mei CHAR(14) NOT NULL COMMENT 'CNPJ da empresa (apenas números, 14 dígitos)',
    razao_social_mei VARCHAR(100) NOT NULL COMMENT 'Razão social oficial da empresa',
    nome_fantasia_mei VARCHAR(100) NULL COMMENT 'Nome fantasia ou nome comercial (opcional)',
    
    -- Contato da empresa
    email_mei VARCHAR(100) NOT NULL COMMENT 'E-mail oficial da empresa',
    telefone_mei VARCHAR(15) NOT NULL COMMENT 'Telefone da empresa (com DDD)',
    
    -- Dados fiscais
    data_abertura DATE NOT NULL COMMENT 'Data de abertura do CNPJ na Receita Federal',
    status_mei ENUM('ativo', 'inativo', 'suspenso', 'baixado') NOT NULL DEFAULT 'ativo' COMMENT 'Situação cadastral da empresa',
    
    -- Chave primária
    PRIMARY KEY (id_mei),
    
    -- Chaves únicas (evita duplicidade)
    UNIQUE KEY uk_mei_cnpj (cnpj_mei),
    UNIQUE KEY uk_mei_usuario (id_usuario),
    
    -- Chave estrangeira (relacionamento com usuarios)
    CONSTRAINT fk_mei_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cadastro da empresa do Microempreendedor Individual';


--CRIAÇÃO DA TABELA CLIENTES----------------------------------------------------------------------------------------------------------------------------------------------------


CREATE TABLE IF NOT EXISTS clientes (
    -- Chave primária
    id_cliente INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do cliente',
    
    -- Chave estrangeira (vínculo com o MEI)
    id_mei INT NOT NULL COMMENT 'Referência ao MEI proprietário do cliente',
    
    -- Tipo do cliente
    tipo_cliente ENUM('PF', 'PJ') NOT NULL COMMENT 'Tipo do cliente: PF (Pessoa Física) ou PJ (Pessoa Jurídica)',
    
    -- Dados básicos
    nome_cliente VARCHAR(100) NOT NULL COMMENT 'Nome completo (PF) ou Razão Social (PJ)',
    nome_fantasia_cliente VARCHAR(100) NULL COMMENT 'Nome fantasia (apenas para PJ)',
    
    -- Documentos (condicionais)
    cpf_cliente CHAR(11) NULL COMMENT 'CPF do cliente (obrigatório se tipo = PF)',
    cnpj_cliente CHAR(14) NULL COMMENT 'CNPJ do cliente (obrigatório se tipo = PJ)',
    inscricao_estadual_cliente VARCHAR(20) NULL COMMENT 'Inscrição estadual (apenas para PJ, quando aplicável)',
    
    -- Contato
    email_cliente VARCHAR(100) NULL COMMENT 'E-mail do cliente para contato e envio de notas',
    telefone_cliente VARCHAR(15) NOT NULL COMMENT 'Telefone principal do cliente (com DDD)',
    telefone_secundario_cliente VARCHAR(15) NULL COMMENT 'Telefone alternativo (opcional)',
    
    -- Endereço
    endereco_cep CHAR(8) NULL COMMENT 'CEP do endereço (apenas números)',
    endereco_logradouro VARCHAR(100) NULL COMMENT 'Rua, avenida, praça, etc.',
    endereco_numero VARCHAR(10) NULL COMMENT 'Número do imóvel',
    endereco_complemento VARCHAR(50) NULL COMMENT 'Complemento (apto, sala, bloco, etc.)',
    endereco_bairro VARCHAR(50) NULL COMMENT 'Bairro',
    endereco_cidade VARCHAR(50) NULL COMMENT 'Cidade',
    endereco_uf CHAR(2) NULL COMMENT 'Estado (sigla, ex: SP, RJ, MG)',
    
    -- Observações e controle
    observacoes_cliente TEXT NULL COMMENT 'Campo livre para anotações relevantes sobre o cliente',
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora do cadastro do cliente',
    status_cliente ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo' COMMENT 'Situação do cliente',
    
    -- Chave primária
    PRIMARY KEY (id_cliente),
    
    -- Chave estrangeira (relacionamento com mei)
    CONSTRAINT fk_clientes_mei FOREIGN KEY (id_mei) REFERENCES mei(id_mei) ON DELETE CASCADE ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cadastro de clientes do MEI';

--CRIAÇÃO DA TABELA ORDENS DE SERVIÇO---------------------------------------------------------------------------------------------------------------------------


CREATE TABLE IF NOT EXISTS ordens_servico (
    -- Chave primária
    id_os INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da Ordem de Serviço',
    
    -- Chaves estrangeiras
    id_mei INT NOT NULL COMMENT 'Referência ao MEI proprietário da OS',
    id_cliente INT NOT NULL COMMENT 'Referência ao cliente destinatário da OS',
    
    -- Identificação da OS
    numero_os VARCHAR(20) NOT NULL COMMENT 'Número único sequencial da OS (ex: OS-2026-0001)',
    tipo_os ENUM('Servico', 'Produto', 'Misto') NOT NULL COMMENT 'Classificação da OS: Servico, Produto, Misto',
    
    -- Datas
    data_abertura DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora de criação da OS',
    data_prevista DATE NULL COMMENT 'Data estimada para execução do serviço ou entrega dos produtos',
    data_conclusao DATETIME NULL COMMENT 'Data e hora em que a OS foi efetivamente concluída',
    
    -- Descrição
    descricao_os TEXT NOT NULL COMMENT 'Descrição geral da transação',
    
    -- Valores
    valor_servicos DECIMAL(10,2) NULL COMMENT 'Valor total dos serviços prestados',
    valor_produtos DECIMAL(10,2) NULL COMMENT 'Valor total dos produtos vendidos (calculado)',
    valor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Soma de valor_servicos + valor_produtos',
    valor_pago DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Montante já recebido do cliente',
    
    -- Status
    status_servico ENUM('Aberto', 'Em andamento', 'Concluido', 'Cancelado') NOT NULL DEFAULT 'Aberto' COMMENT 'Situação operacional da OS',
    status_pagamento ENUM('Pendente', 'Parcial', 'Pago', 'Cancelado') NOT NULL DEFAULT 'Pendente' COMMENT 'Situação financeira da OS',
    
    -- Observações e controle
    observacoes_os TEXT NULL COMMENT 'Campo livre para anotações relevantes',
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última modificação',
    
    -- Chave primária
    PRIMARY KEY (id_os),
    
    -- Chave única para o número da OS
    UNIQUE KEY uk_ordens_numero_os (numero_os),
    
    -- Índices para busca
    INDEX idx_ordens_mei (id_mei),
    INDEX idx_ordens_cliente (id_cliente),
    INDEX idx_ordens_status_servico (status_servico),
    INDEX idx_ordens_status_pagamento (status_pagamento),
    INDEX idx_ordens_data_abertura (data_abertura),
    
    -- Chaves estrangeiras
    CONSTRAINT fk_ordens_mei FOREIGN KEY (id_mei) REFERENCES mei(id_mei) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_ordens_cliente FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro principal das Ordens de Serviço';


--CRIAÇÃO DA TABELA PRODUTOS-------------------------------------------------------------------------------------------------------------------------------


CREATE TABLE IF NOT EXISTS produtos (
    -- Chave primária
    id_produto INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do produto',
    
    -- Chave estrangeira (vínculo com o MEI)
    id_mei INT NOT NULL COMMENT 'Referência ao MEI proprietário do produto',
    
    -- Dados básicos do produto
    nome_produto VARCHAR(100) NOT NULL COMMENT 'Nome do produto (ex: Capinha de Celular Azul)',
    descricao_produto TEXT NULL COMMENT 'Descrição detalhada do produto',
    categoria VARCHAR(50) NULL COMMENT 'Agrupamento do produto por tipo',
    codigo_barras VARCHAR(50) NULL COMMENT 'Código de barras do produto',
    unidade_medida VARCHAR(10) NOT NULL DEFAULT 'UN' COMMENT 'Unidade: UN, PC, KG, L, M, PAR',
    
    -- Controle de estoque
    quantidade_estoque INT NOT NULL DEFAULT 0 COMMENT 'Saldo atual disponível em estoque',
    estoque_minimo INT NULL COMMENT 'Quantidade que dispara alerta de reposição',
    
    -- Preços
    preco_custo DECIMAL(10,2) NOT NULL COMMENT 'Preço de compra do produto',
    preco_venda DECIMAL(10,2) NOT NULL COMMENT 'Preço de venda ao cliente',
    
    -- Fornecedor e controle
    fornecedor VARCHAR(100) NULL COMMENT 'Nome do fornecedor habitual',
    status_produto ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo' COMMENT 'Situação do produto',
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro do produto',
    
    -- Chave primária
    PRIMARY KEY (id_produto),
    
    -- Índices para busca
    INDEX idx_produto_mei (id_mei),
    INDEX idx_produto_categoria (categoria),
    INDEX idx_produto_status (status_produto),
    INDEX idx_produto_nome (nome_produto),
    
    -- Chave estrangeira
    CONSTRAINT fk_produtos_mei FOREIGN KEY (id_mei) REFERENCES mei(id_mei) ON DELETE CASCADE ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cadastro de produtos e saldo atual do estoque';


--CRIAÇÃO D ATABELA OS PRODUTOS-----------------------------------------------------------------------------------------------------------------------------------------------------------


-- Garantir que estamos no banco correto
USE MyMei;

-- Primeiro, remover a tabela se existir (para recriar)
DROP TABLE IF EXISTS os_produtos;

-- Criar a tabela
CREATE TABLE os_produtos (
    -- Chave primária
    id_os_produto INT NOT NULL AUTO_INCREMENT,
    
    -- Chaves estrangeiras (com tipos IDÊNTICOS às tabelas originais)
    id_os INT NOT NULL,
    id_produto INT NOT NULL,
    
    -- Dados do item
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    
    -- Chave primária
    PRIMARY KEY (id_os_produto),
    
    -- Índices
    INDEX idx_id_os (id_os),
    INDEX idx_id_produto (id_produto),
    
    -- Chave única (evita mesmo produto repetido na mesma OS)
    UNIQUE KEY uk_os_produto (id_os, id_produto)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar as chaves estrangeiras SEPARADAMENTE
ALTER TABLE os_produtos
ADD CONSTRAINT fk_os_produtos_os 
FOREIGN KEY (id_os) REFERENCES ordens_servico(id_os) 
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE os_produtos
ADD CONSTRAINT fk_os_produtos_produto 
FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) 
ON DELETE RESTRICT ON UPDATE CASCADE;

--CRIAÇÃO DA TABELA MOVIMENTAÇÃO ESTOQUE----------------------------------------------------------------------------------------------------------------------------------------


CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
    -- Chave primária
    id_movimentacao INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da movimentação',
    
    -- Chave estrangeira (vínculo com o produto)
    id_produto INT NOT NULL COMMENT 'Referência ao produto movimentado',
    
    -- Dados da movimentação
    tipo_movimentacao ENUM('Entrada', 'Saida') NOT NULL COMMENT 'Direção: Entrada ou Saida',
    quantidade INT NOT NULL COMMENT 'Quantidade movimentada (deve ser > 0)',
    motivo ENUM('Compra', 'Venda', 'Ajuste', 'Perda', 'Devolucao') NOT NULL COMMENT 'Razão da movimentação',
    
    -- Links para outras tabelas (condicionais)
    id_os_relacionada INT NULL COMMENT 'ID da OS (quando motivo = Venda)',
    fornecedor VARCHAR(100) NULL COMMENT 'Nome do fornecedor (quando motivo = Compra)',
    
    -- Observação e auditoria
    observacao_movimentacao TEXT NULL COMMENT 'Anotações adicionais sobre a movimentação',
    usuario_responsavel VARCHAR(100) NOT NULL COMMENT 'Usuário que realizou a movimentação',
    data_movimentacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora da movimentação',
    
    -- Chave primária
    PRIMARY KEY (id_movimentacao),
    
    -- Índices para busca
    INDEX idx_movimentacao_produto (id_produto),
    INDEX idx_movimentacao_tipo (tipo_movimentacao),
    INDEX idx_movimentacao_motivo (motivo),
    INDEX idx_movimentacao_data (data_movimentacao),
    INDEX idx_movimentacao_os (id_os_relacionada),
    
    -- Chaves estrangeiras
    CONSTRAINT fk_movimentacao_produto FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movimentacao_os FOREIGN KEY (id_os_relacionada) REFERENCES ordens_servico(id_os) ON DELETE SET NULL ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Histórico de movimentações do estoque';


--CRIAÇÃO DA TABELA AGENDA---------------------------------------------------------------------------------------------------------------------------
-- =====================================================
-- TABELA: agenda
-- Descrição: Compromissos e eventos do MEI (integrados com OS)
-- Dependência: mei (id_mei), ordens_servico (id_os), clientes (id_cliente)
-- =====================================================

CREATE TABLE IF NOT EXISTS agenda (
    -- Chave primária
    id_evento INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único do evento',
    
    -- Chave estrangeira (vínculo com o MEI)
    id_mei INT NOT NULL COMMENT 'Referência ao MEI proprietário do evento',
    
    -- Links para outras tabelas (opcionais)
    id_os_relacionada INT NULL COMMENT 'OS que originou o evento (se aplicável)',
    id_cliente_relacionado INT NULL COMMENT 'Cliente vinculado ao evento (se aplicável)',
    
    -- Tipo do evento
    tipo_evento ENUM('OS', 'Avulso') NOT NULL COMMENT 'OS (automático) ou Avulso (manual)',
    
    -- Dados do evento
    titulo_evento VARCHAR(100) NOT NULL COMMENT 'Título resumido do compromisso',
    descricao_evento TEXT NULL COMMENT 'Descrição detalhada do compromisso',
    
    -- Datas e horários
    data_inicio DATETIME NOT NULL COMMENT 'Data e hora de início do compromisso',
    data_fim DATETIME NULL COMMENT 'Data e hora de término (se vazio = dia inteiro)',
    
    -- Recorrência
    recorrencia ENUM('Nenhuma', 'Diario', 'Semanal', 'Mensal', 'Anual') NOT NULL DEFAULT 'Nenhuma' COMMENT 'Periodicidade do evento',
    dia_semana_recorrencia VARCHAR(20) NULL COMMENT 'Dias da semana (Segunda,Terca,Quarta,Quinta,Sexta,Sabado,Domingo)',
    data_recorrencia_fim DATE NULL COMMENT 'Data final para eventos recorrentes',
    
    -- Personalização
    cor_evento VARCHAR(7) NULL COMMENT 'Cor hexadecimal (ex: #FF5733)',
    
    -- Notificações
    notificar_antes INT NULL COMMENT 'Minutos antes para notificar (15, 30, 60)',
    notificacao_enviada BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Evita envios duplicados',
    
    -- Status e controle
    status_evento ENUM('Pendente', 'Concluido', 'Cancelado') NOT NULL DEFAULT 'Pendente' COMMENT 'Situação do evento',
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última atualização',
    
    -- Chave primária
    PRIMARY KEY (id_evento),
    
    -- Índices para busca
    INDEX idx_agenda_mei (id_mei),
    INDEX idx_agenda_os (id_os_relacionada),
    INDEX idx_agenda_cliente (id_cliente_relacionado),
    INDEX idx_agenda_data_inicio (data_inicio),
    INDEX idx_agenda_status (status_evento),
    INDEX idx_agenda_tipo (tipo_evento),
    
    -- Chaves estrangeiras
    CONSTRAINT fk_agenda_mei FOREIGN KEY (id_mei) REFERENCES mei(id_mei) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_agenda_os FOREIGN KEY (id_os_relacionada) REFERENCES ordens_servico(id_os) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_agenda_cliente FOREIGN KEY (id_cliente_relacionado) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Compromissos e eventos do MEI';

--CRIAÇÃO DA TABELA EXPORTAÇÃO LOG-----------------------------------------------------------------------------------------------------------------------
-- =====================================================
-- TABELA: exportacoes_log
-- Descrição: Auditoria de todas as exportações de dados realizadas
-- Dependência: mei (id_mei), usuarios (id_usuario)
-- =====================================================

CREATE TABLE IF NOT EXISTS exportacoes_log (
    -- Chave primária
    id_exportacao INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da exportação',
    
    -- Chaves estrangeiras
    id_mei INT NOT NULL COMMENT 'Referência ao MEI que realizou a exportação',
    id_usuario INT NOT NULL COMMENT 'Referência ao usuário que executou a exportação',
    
    -- Dados da exportação
    tipo_dado_exportado ENUM('Clientes', 'Ordens_Servico', 'Estoque', 'Movimentacoes', 'Relatorio_Financeiro', 'Exportacao_Completa') NOT NULL COMMENT 'Conjunto de dados exportado',
    formato_arquivo ENUM('CSV', 'XLSX', 'PDF') NOT NULL COMMENT 'Formato do arquivo gerado',
    
    -- Período (para relatórios)
    periodo_inicio DATE NULL COMMENT 'Data inicial do período filtrado',
    periodo_fim DATE NULL COMMENT 'Data final do período filtrado',
    
    -- Filtros aplicados (JSON)
    incluir_filtros JSON NULL COMMENT 'Filtros aplicados na exportação (ex: {"status_servico": "Concluido"})',
    
    -- Metadados do arquivo
    tamanho_arquivo_kb INT NULL COMMENT 'Tamanho do arquivo em kilobytes',
    
    -- Status e controle
    status_exportacao ENUM('Sucesso', 'Falha', 'Em_andamento') NOT NULL DEFAULT 'Em_andamento' COMMENT 'Situação da exportação',
    mensagem_erro TEXT NULL COMMENT 'Descrição do erro (se houver falha)',
    
    -- Segurança
    ip_origem VARCHAR(45) NOT NULL COMMENT 'Endereço IP de quem solicitou a exportação',
    
    -- Data e hora
    data_exportacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora da exportação',
    
    -- Chave primária
    PRIMARY KEY (id_exportacao),
    
    -- Índices para busca
    INDEX idx_exportacao_mei (id_mei),
    INDEX idx_exportacao_usuario (id_usuario),
    INDEX idx_exportacao_data (data_exportacao),
    INDEX idx_exportacao_status (status_exportacao),
    INDEX idx_exportacao_tipo (tipo_dado_exportado),
    
    -- Chaves estrangeiras
    CONSTRAINT fk_exportacao_mei FOREIGN KEY (id_mei) REFERENCES mei(id_mei) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_exportacao_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de auditoria de exportações de dados';





