function createProductRow() {
  return `
    <tr>
      <td style="padding: 8px; border: 1px solid black">-</td>
      <td style="padding: 8px; border: 1px solid black">R$-</td>
      <td style="padding: 8px; border: 1px solid black">-</td>
      <td style="padding: 8px; border: 1px solid black">-</td>
      <td style="padding: 8px; border: 1px solid black">-</td>
      <td style="padding: 8px; border: 1px solid black">-</td>
    </tr>
  `;
}
function adicionarLinha() {
  const novaLinha = createProductRow();
  const tabela = document.getElementById("corpo-tabela");
  tabela.insertAdjacentHTML("beforeend", novaLinha);
}
