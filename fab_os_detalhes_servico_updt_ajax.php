<?php
//var_dump($_POST); die();
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include_once('session.php');

include_once('../db_functions.php');
require_once("../db_functions_ext.php");

$con = dbConnect();

$response = ['success' => false, 'message' => 'Erro não especificado.'];

if (isset($_POST['servicoId']) && isset($_POST['produtos'])) {




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
} else {
    echo json_encode(['success' => false, 'message' => 'Dados POST incompletos']);
}
function produtoExiste($conn_ext, $produtoId, $servicoId)
{
    global $response;
    $sql = "SELECT * FROM srv_cont_os_servico_padrao WHERE id_produto = ? AND Id_servico = ?";
    if ($stmt = $conn_ext->prepare($sql)) {
        $stmt->bind_param("ii", $produtoId, $servicoId);
        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $boundSql = getBoundSql($sql, "ii", array($produtoId, $servicoId));
        $response['queries'][] = $boundSql;

        $result = $stmt->get_result();
        $stmt->close();
        return $result->num_rows > 0;
    }
    return false;
}

function insereProduto($conn_ext, $produtoId, $descricao, $quantidade, $servicoId)
{
    global $response;
    $sql = "INSERT INTO srv_cont_os_servico_padrao (id_produto, Descricao, Qtdade, Id_servico) VALUES (?, ?, ?, ?)";
    if ($stmt = $conn_ext->prepare($sql)) {
        $stmt->bind_param("isii", $produtoId, $descricao, $quantidade, $servicoId);
        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $boundSql = getBoundSql($sql, "isii", array($produtoId, $descricao, $quantidade, $servicoId));
        $response['queries'][] = $boundSql;

        $stmt->close();
    }
}

function atualizaProduto($conn_ext, $produtoId, $descricao, $quantidade, $servicoId)
{
    global $response;
    $sql = "UPDATE srv_cont_os_servico_padrao SET Descricao = ?, Qtdade = ? WHERE id_produto = ? AND Id_servico = ?";
    if ($stmt = $conn_ext->prepare($sql)) {
        $stmt->bind_param("siii", $descricao, $quantidade, $produtoId, $servicoId);
        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $boundSql = getBoundSql($sql, "siii", array($descricao, $quantidade, $produtoId, $servicoId));
        $response['queries'][] = $boundSql;

        $stmt->close();
    }
}

function deletaProduto($conn_ext, $produtoIds, $servicoId)
{
    global $response;
    $ids = implode(',', $produtoIds);
    $sql = "DELETE FROM srv_cont_os_servico_padrao WHERE Id_servico = ? AND id_produto NOT IN ($ids)";
    if ($stmt = $conn_ext->prepare($sql)) {
        $stmt->bind_param("i", $servicoId);
        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        $boundSql = getBoundSql($sql, "i", array($servicoId));
        $response['queries'][] = $boundSql;

        $stmt->close();
    }
}
function getBoundSql($sql, $types, $params) {
    $values = [];
    for ($i = 0; $i < count($params); $i++) {
        if ($types[$i] == 's') {
            $values[] = "'" . $params[$i] . "'";
        } else {
            $values[] = $params[$i];
        }
    }

    $sql = str_replace("?", "%s", $sql);
    array_unshift($values, $sql);
    $boundSql = call_user_func_array('sprintf', $values);

    return $boundSql;
}
