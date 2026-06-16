<?php
require_once("config/database.php");
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MyMei - Produtos</title>
  </head>
  <body>
    <h1>Lista de Produtos</h1>
    <button onclick="adicionarLinha()">Adicionar Linha</button>
    <table style="border: 1px solid black; border-collapse: collapse">
      <thead>
        <tr>
          <th style="padding: 8px; border: 1px solid black">Cod. Barras</th>
          <th style="padding: 8px; border: 1px solid black">Produto</th>
          <th style="padding: 8px; border: 1px solid black">Quantidade</th>
          <th style="padding: 8px; border: 1px solid black">Un/Cx</th>
          <th style="padding: 8px; border: 1px solid black">Categoria</th>
          <th style="padding: 8px; border: 1px solid black">Preço</th>
        </tr>
      </thead>
      <tbody id="corpo-tabela"></tbody>
    </table>
    <script src="js/script.js"></script>
  </body>
</html>
