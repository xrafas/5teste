<?php
include_once('session.php');
include_once('../db_functions.php');
require_once("../db_functions_ext.php");



$tipoServico = $_POST['tipoServico'];
$produtos = $_POST['produtos'];

// Aqui você pode inserir os dados no banco de dados
foreach ($produtos as $produto) {
    $produtoId = $produto['produtoId'];
    $quantidade = $produto['quantidade'];
    $origem = $produto['origem'];
    
    // Exemplo de inserção no banco de dados (atualize conforme necessário)
    $sql = "INSERT INTO sua_tabela (tipo_servico, produto_id, quantidade, origem) VALUES ('$tipoServico', '$produtoId', '$quantidade', '$origem')";
    
    if ($conn_ext->query($sql) === FALSE) {
        echo "Erro: " . $sql . "<br>" . $conn_ext->error;
    }
}

echo "Dados inseridos com sucesso!";

$conn_ext->close();
