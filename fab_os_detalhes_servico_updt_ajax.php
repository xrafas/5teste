<?php
// Habilitar exibição de erros (Use apenas em ambiente de desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('session.php');
include_once('../db_functions.php');
require_once("../db_functions_ext.php");

$con = dbConnect();

$response = ['success' => false, 'message' => 'Erro não especificado.', 'queries' => []];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['servicoId']) && isset($_POST['idOs']) && isset($_POST['idSrv']) && isset($_POST['produtos'])) {
    $conn_ext->begin_transaction();

    $servicoId = intval($_POST['servicoId']);
    $idSrv = intval($_POST['idSrv']);
    $idOs = intval($_POST['idOs']);
    $produtos = $_POST['produtos'];
      //$produtos = json_decode($_POST['produtos'], true);

    $ids = [];

    foreach ($produtos as $produto) {
        $id = isset($produto['id']) ? intval($produto['id']) : null;
        $produtoId = intval($produto['produtoId']);
        //$quantidade = intval($produto['quantidade']);
        $quantidade = floatval(str_replace(",", ".", $produto['quantidade']));

        $terceiro_posse = $produto['terceiro_posse'];

        $id = insereOuAtualizaProduto($conn_ext, $id, $servicoId, $produtoId, $quantidade, $terceiro_posse, $idSrv, $idOs, $response);

        if ($id) {
            $ids[] = $id;
        }
    }

    deletaProduto($conn_ext, $ids, $idSrv, $idOs, $servicoId, $response);

    $conn_ext->commit();

    $response['success'] = true;
    $response['message'] = 'Produtos atualizados com sucesso.';
}

header('Content-Type: application/json');

echo json_encode($response);

function insereOuAtualizaProduto($conn_ext, $id, $servicoId, $produtoId, $quantidade, $terceiro_posse, $idSrv, $idOs, &$response) {
    if ($id !== null && produtoExiste($conn_ext, $id)) {
        $sql = "UPDATE srv_cont_os_servico_padrao SET Qtdade = ?, terceiro_posse = ? WHERE Id = ?";
        $stmt = $conn_ext->prepare($sql);
        $stmt->bind_param("dsi", $quantidade, $terceiro_posse, $id);
        //$stmt->bind_param("isi", $quantidade, $terceiro_posse, $id);
    } else {
        $sql = "INSERT INTO srv_cont_os_servico_padrao (id_SRV, id_OS, Id_servico, id_produto, Qtdade, terceiro_posse) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn_ext->prepare($sql);
        $stmt->bind_param("iiiids", $idSrv, $idOs, $servicoId, $produtoId, $quantidade, $terceiro_posse);
    
        //$stmt->bind_param("iiiisi", $idSrv, $idOs, $servicoId, $produtoId, $quantidade, $terceiro_posse);
        $stmt->execute();
        return $stmt->insert_id;
    }

    if (!$stmt->execute()) {
        $response['success'] = false;
        $response['message'] = "Erro ao inserir/atualizar produto: " . $stmt->error;
    }
    return $id;
}

function produtoExiste($conn_ext, $id) {
    $sql = "SELECT * FROM srv_cont_os_servico_padrao WHERE Id = ?";
    $stmt = $conn_ext->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result && $result->num_rows > 0;
}

function deletaProduto($conn_ext, $ids, $idSrv, $idOs, $servicoId, &$response) {
    $ids = implode(',', array_map('intval', $ids));
    $sql = "DELETE FROM srv_cont_os_servico_padrao WHERE Id NOT IN ($ids) AND id_SRV = ? AND id_OS = ? AND Id_servico = ?";
    $stmt = $conn_ext->prepare($sql);
    $stmt->bind_param("iii", $idSrv, $idOs, $servicoId);
    if (!$stmt->execute()) {
        $response['success'] = false;
        $response['message'] = "Erro ao deletar produto: " . $stmt->error;
    }
}
