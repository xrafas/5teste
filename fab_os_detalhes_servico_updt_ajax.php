<?php
//var_dump($_POST); die();
include_once('session.php');

include_once('../db_functions.php');
require_once("../db_functions_ext.php");

$con = dbConnect();

$response = ['success' => false, 'message' => 'Erro não especificado.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servicoId = $_POST['servicoId'];
    $produtos = $_POST['produtos'];

    // Coletando os IDs dos produtos que estão sendo atualizados
    $produtoIds = [];
    foreach ($produtos as $produto) {
        $produtoId = $produto['produtoId'];
        $produtoIds[] = $produtoId;
        $descricao = $produto['descricao'];
        $quantidade = $produto['quantidade'];

        if (produtoExiste($conn_ext, $produtoId, $servicoId)) {
            atualizaProduto($conn_ext, $produtoId, $descricao, $quantidade, $servicoId);
        } else {
            if ($quantidade > 0) {
                insereProduto($conn_ext, $produtoId, $descricao, $quantidade, $servicoId);
            }
        }
    }

    // Deletando os produtos que foram removidos no front-end
    deletaProduto($conn_ext, $produtoIds, $servicoId);

    $response['success'] = true;
    $response['message'] = "Dados atualizados com sucesso.";
}

echo json_encode($response);

function produtoExiste($conn_ext, $produtoId, $servicoId)
{
    $sql = "SELECT * FROM srv_cont_os_servico_padrao WHERE id_produto = ? AND Id_servico = ?";
    if ($stmt = $conn_ext->prepare($sql)) {
        $stmt->bind_param("ii", $produtoId, $servicoId);
        $stmt->execute();
        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $result = $stmt->get_result();
        $stmt->close();
        return $result->num_rows > 0;
    }
    return false;
}

function insereProduto($conn_ext, $produtoId, $descricao, $quantidade, $servicoId)
{
    $sql = "INSERT INTO srv_cont_os_servico_padrao (id_produto, Descricao, Qtdade, Id_servico) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn_ext->prepare($sql)) {
        $stmt->bind_param("isii", $produtoId, $descricao, $quantidade, $servicoId);
        $stmt->execute();
        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $stmt->close();
    }
}

function atualizaProduto($conn_ext, $produtoId, $descricao, $quantidade, $servicoId)
{
    $sql = "UPDATE srv_cont_os_servico_padrao SET Descricao = ?, Qtdade = ? WHERE id_produto = ? AND Id_servico = ?";
    if ($stmt = $conn_ext->prepare($sql)) {
        $stmt->bind_param("siii", $descricao, $quantidade, $produtoId, $servicoId);
        $stmt->execute();
        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $stmt->close();
    }
}

function deletaProduto($conn_ext, $produtoIds, $servicoId)
{
    $ids = implode(',', $produtoIds);
    $sql = "DELETE FROM srv_cont_os_servico_padrao WHERE Id_servico = ? AND id_produto NOT IN ($ids)";
    if ($stmt = $conn_ext->prepare($sql)) {
        $stmt->bind_param("i", $servicoId);
        $stmt->execute();
        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $stmt->close();
    }
}
