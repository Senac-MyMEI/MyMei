<!DOCTYPE html>
<html lang="pt-br">
<body>
    <div>       <!--    div form geral  -->
        <form method="POST">
            <div>   <!--    input 1 -->
                <label>Tipo de ordem de serviço:</label>
                <input type="radio" name="tipo_os" value="servicos" required> Serviços
                <input type="radio" name="tipo_os" value="produtos"> Produtos
                <input type="radio" name="tipo_os" value="misto"> Misto(Serviços + produtos)
            </div>
            <div>   <!--    input 2 -->
                <label>Valor dos serviços:</label>
                <input type="text">
            </div>
            <div>   <!--    input 3 -->
                <label>Descrição da ordem de serviço:</label>
                <input type="text">
            </div>
            <div>   <!--    input 4 -->
                <label>Data prevista:</label>
                <input type="text">
            </div>
            <div>   <!--    input 5 -->
                <label>Valor pago:</label>
                <input type="text">
            </div>
            <div>   <!--    input 6 -->
                <label>Status de pagamento:</label>
                <input type="text">
            </div>
            <div>   <!--    input 7 -->
                <label>Observações da ordem de serviço:</label>
                <input type="text">
            </div>
            <!--    ==========   VALORES INPUT HIDDEN   ==========   -->
            <div>   <!--    input 8 - STATUS OS -->
                <input type="hidden" name="status_os" value="aberto">
            </div>
            <div>   <!--    input 9 - DATA ABERTURA -->
                <input type="hidden" name="data_abertura" value="<?php echo date('Y-m-d H:i:s')?>">
            </div>
            <div>   <!--    input 10 - NÚMERO OS -->
                <input type="hidden" name="numero_os" value="OS-<?php echo date('Y') . '-' . str_pad(rand(1,9999), 4, '0', STR_PAD_LEFT);?>">
                    <!--    =========================================  FAZER MUDANÇAS  ============================================    -->
            </div>
            <div>
                <button type="submit">Cadastrar serviço</button>
            </div>
        </form>
    </div>
</body>
</html>