<?php

include_once('session.php');
include_once('../db_functions.php');
require_once("../db_functions_ext.php");

$response = ['success' => false, 'message' => 'Erro não especificado.', 'servico' => null];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tipo_servico_descricao']) && isset($_POST['idOs']) && isset($_POST['idSrv'])) {

    // Lendo o valor de Local_tratamento da requisição POST.
    $local_tratamento_descricao = $_POST['local_tratamento_descricao'];
    $tipo_servico_descricao = $_POST['tipo_servico_descricao'];
    $idOs = intval($_POST['idOs']);
    $idSrv = intval($_POST['idSrv']);

    $sql = "INSERT INTO srv_cont_os_servico (id_SRV, Id_OS, Tipo, Local_tratamento) VALUES (?, ?, ?, ?)";
    $stmt = $conn_ext->prepare($sql);
    $stmt->bind_param("iiss", $idSrv, $idOs, $tipo_servico_descricao, $local_tratamento_descricao);



    if ($stmt->execute()) {
        $new_service_id = $conn_ext->insert_id; // Obtenha o ID do novo serviço inserido

        $servico = [
            'id' => $new_service_id,
            'tipo_servico' => $tipo_servico_descricao, // Usando $tipo que foi obtido da consulta
            'local_tratamento' => $local_tratamento_descricao // Usando $tipo que foi obtido da consulta
        ];

        $response['success'] = true;
        $response['message'] = 'Serviço inserido com sucesso!';
        $response['servico'] = $servico;
    } else {
        $response['message'] = 'Falha ao inserir serviço no banco de dados.';
    }

    $conn_ext->commit();
}

echo json_encode($response);
