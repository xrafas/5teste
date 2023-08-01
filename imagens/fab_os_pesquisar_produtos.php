<?php
include_once('session.php');
include_once('../db_functions.php');
require_once("../db_functions_ext.php");

$con = dbConnect();

// Recebendo a consulta do usuário
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Consultando os produtos
$sql = "SELECT pm.*, pm.Id AS Id_produto_mov, p.Descricao, p.Codigo FROM produtos_mov pm INNER JOIN produtos p ON pm.Id_Produto = p.Id WHERE p.Descricao LIKE ? OR p.Codigo LIKE ? LIMIT 15";

$stmt = $conn_ext->prepare($sql);

$likeQuery = '%' . $query . '%';

$stmt->bind_param("ss", $likeQuery, $likeQuery);

$stmt->execute();

$result = $stmt->get_result();

// Exibindo os produtos
while ($row = $result->fetch_assoc()) {
    if ($row['Qtdade_Restante'] > 0) {
        echo '<div onclick="adicionarProduto(' . $row['Id_Produto'] . ', \'' . $row['Descricao'] . '\', ' . $row['Id_produto_mov'] . ')">' . $row['Descricao'] . ' (Quantidade Restante: <b>' . $row['Qtdade_Restante'] . '</b>, Lote: <b>' . $row['Lote'] . '</b>, Código: <b>' . $row['Codigo'] . '</b>)</div>';
       } else {
        echo '<div style="color: red;">' . $row['Descricao'] . ' (Quantidade Restante: ' . $row['Qtdade_Restante'] . ', Lote: ' . $row['Lote'] . ', Código: ' . $row['Codigo'] . ')</div>';
    }
}
