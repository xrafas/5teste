<?php
include_once('session.php');
include_once('../db_functions.php');
require_once("../db_functions_ext.php");

// Estabelece uma conexão com o banco de dados do sysweb
$con = dbConnect();

// Verifica se o método de requisição é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idProdutoMov'])) {
    $idProdutoMov = intval($_POST['idProdutoMov']);

    // Consulta o saldo disponível na tabela produtos_mov
    $saldoDisponivel = obterSaldoDisponivel($conn_ext, $idProdutoMov);

    // Define a resposta como sucesso e envia o saldo disponível
    $response = ['success' => true, 'saldoDisponivel' => $saldoDisponivel];
} else {
    // Caso contrário, define a resposta como falha
    $response = ['success' => false, 'message' => 'Requisição inválida.'];
}

// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

// Envia a resposta em formato JSON
echo json_encode($response);

// Função que obtém o saldo disponível na tabela produtos_mov
function obterSaldoDisponivel($conn_ext, $idProdutoMov){  

    // Consulta o saldo disponível na tabela produtos_mov
    $sql = "SELECT Qtdade_Restante FROM produtos_mov WHERE Id = ?";
    $stmt = $conn_ext->prepare($sql);
    $stmt->bind_param("i", $idProdutoMov);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['Qtdade_Restante'];
    } else {
        return 0;
    }
}
