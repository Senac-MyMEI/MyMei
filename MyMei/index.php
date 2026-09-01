<!DOCTYPE html>
<!-- ↑ Diz ao navegador que é um documento HTML5 (versão mais moderna) -->

<html lang="en">
<!-- ↑ Define o idioma da página como inglês. Se for português, mude para "pt-BR" -->

<head>
    <!-- ↑ Cabeçalho da página (informações que não aparecem na tela) -->
    
    <meta charset="UTF-8">
    <!-- ↑ Define que o texto usa caracteres especiais (acentos, ç, etc.) -->
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ↑ Deixa o site responsivo em celulares/tablets. NÃO MEXA AQUI! -->
    
    <link rel="stylesheet" href="ASSETS/bootstrap.css">
    <!-- ↑ CARREGANDO O CSS DO BOOTSTRAP (aquele que arrumamos!) 
         DICA: Se quiser trocar o tema, é só baixar outro do Bootswatch e substituir -->
    
    <title>Milton</title>
    <!-- ↑ Título que aparece na aba do navegador. 
         DICA: Mude para o nome do seu sistema MEI, ex: "MyMei - Sistema" -->
</head>

<body>
    <!-- ↑ CORPO DA PÁGINA (tudo que aparece na tela) -->

    <!-- ========================================================= -->
    <!-- BARRA DE NAVEGAÇÃO (NAVBAR) -->
    <!-- ========================================================= -->
    
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
    <!-- ↑ INÍCIO DA NAVBAR
         CLASSES DO BOOTSTRAP:
         - navbar: define que é uma barra de navegação
         - navbar-expand-lg: em telas GRANDES (lg) fica expandida, em pequenas vira menu hambúrguer
         - bg-primary: fundo na cor primária (azul do tema Cerulean)
         - data-bs-theme="dark": texto em cores claras (branco) para contrastar com fundo escuro
         
         🎨 DICA DE PERSONALIZAÇÃO:
         - bg-primary → bg-success (verde) / bg-danger (vermelho) / bg-warning (amarelo)
         - data-bs-theme="dark" → "light" (texto escuro) se mudar para fundo claro
    -->
    
        <div class="container-fluid">
        <!-- ↑ container-fluid: ocupa 100% da largura da tela
             DICA: Se quiser centralizado com bordas, use só "container"
        -->
        
            <a class="navbar-brand" href="#">Milton</a>
            <!-- ↑ LOGO/MARCA do sistema
                 DICA: Troque "Navbar" pelo nome do seu MEI ou coloque uma imagem:
                 <img src="ASSETS/logo.png" alt="MyMei" height="40">
            -->
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
            <!-- ↑ BOTÃO DO MENU HAMBÚRGUER (aparece em telas pequenas)
                 - data-bs-target="#navbarColor01": aponta para o ID do menu que vai abrir/fechar
                 - aria-*: para acessibilidade (leitores de tela)
            -->
                <span class="navbar-toggler-icon"></span>
                <!-- ↑ Ícone do hambúrguer (três tracinhos) -->
            </button>
            
            <div class="collapse navbar-collapse" id="navbarColor01">
            <!-- ↑ MENU QUE EXPANDE/COLAPSA
                 - collapse: começa recolhido em telas pequenas
                 - navbar-collapse: comportamento de menu responsivo
                 - id="navbarColor01": igual ao data-bs-target do botão acima
            -->
                
                <ul class="navbar-nav me-auto">
                <!-- ↑ LISTA DE ITENS DO MENU
                     - navbar-nav: estiliza como menu
                     - me-auto: margin-end auto (empurra os itens para a esquerda)
                -->
                    
                    <li class="nav-item">
                    <!-- ↑ Cada item do menu -->
                        <a class="nav-link active" href="#">
                        <!-- ↑ LINK do menu
                             - active: destaca como página atual (pode mudar conforme a página)
                        -->
                            Home
                            <span class="visually-hidden">(current)</span>
                            <!-- ↑ visually-hidden: esconde visualmente, mas mantém para leitores de tela -->
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="#">Planos</a>
                        <!-- ↑ DICA: Troque "Features" por "Clientes", "Produtos", "OS" etc. -->
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="AUTENTIFICACAO/fazer_login.php">Login</a>
                        <!-- ↑ DICA: Troque por "Financeiro" ou "Dashboard" -->
                    </li>
                    
                    <li class="nav-item dropdown">
                    <!-- ↑ ITEM COM DROPDOWN (submenu) -->
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            Dropdown
                        </a>
                        <!-- ↑ data-bs-toggle="dropdown": ativa o submenu ao clicar -->
                        
                        <div class="dropdown-menu">
                        <!-- ↑ MENU SUSPENSO que aparece -->
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Another action</a>
                            <a class="dropdown-item" href="#">Something else here</a>
                            <div class="dropdown-divider"></div>
                            <!-- ↑ divisor (linha cinza) -->
                            <a class="dropdown-item" href="#">Separated link</a>
                        </div>
                    </li>
                </ul>
                
                <form class="d-flex">
                <!-- ↑ FORMULÁRIO DE BUSCA
                     - d-flex: flexbox para alinhar os itens na horizontal
                -->
                    <input class="form-control me-sm-2" type="search" placeholder="Search">
                    <!-- ↑ CAMPO DE BUSCA
                         - form-control: estilização do Bootstrap
                         - me-sm-2: margem à direita em telas pequenas
                         DICA: Troque "Search" por "Buscar..." em português
                    -->
                    
                    <button class="btn btn-secondary my-2 my-sm-0" type="submit">
                    <!-- ↑ BOTÃO DE BUSCAR
                         - btn: estilização de botão
                         - btn-secondary: cor secundária (cinza)
                         - my-2 my-sm-0: margem vertical em telas pequenas
                         
                         🎨 CORES DE BOTÕES:
                         btn-primary (azul) | btn-success (verde) | btn-danger (vermelho)
                         btn-warning (amarelo) | btn-info (azul claro) | btn-dark (preto)
                    -->
                        Search
                    </button>
                </form>
            </div>
        </div>
    </nav>
    
    
    
</body>
</html>