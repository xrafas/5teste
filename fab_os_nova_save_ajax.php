<?php
//error_log("Iniciando script de salvamento...");
header('Content-Type: application/json');

include_once('session.php');
include_once('../db_functions.php');
require_once("../db_functions_ext.php");

$con = dbConnect();

// Verificando se os campos necessários estão presentes
if (
    isset($_POST['id_SRV']) && isset($_POST['Data']) && isset($_POST['Situacao']) && isset($_POST['Id_Vec'])
    && isset($_POST['Id_Vendedor']) && isset($_POST['Usuario'])
) {

    $id_SRV = $_POST['id_SRV'];
    $data = $_POST['Data'];
    $situacao = 'ABERTO';
    $id_Vec = $_POST['Id_Vec'];
    $id_vendedor = $_POST['Id_Vendedor'];
    $usuario = $_POST['Usuario'];

    // Preparando a query SQL
    $sql = "INSERT INTO srv_cont_os (id_SRV, Data, Situacao, Id_Vec) VALUES (?, ?, ?, ?)";

    // Preparando a declaração
    if ($stmt = $conn_ext->prepare($sql)) {

        // Ligando parâmetros
        $stmt->bind_param("issi", $id_SRV, $data, $situacao, $id_Vec);

        // Executando a declaração
        if ($stmt->execute()) {
            // Obter o último Id inserido
            $last_id = $conn_ext->insert_id;

            // Inserir na tabela srv_cont_os_servico_colaborador
            $sql_colaborador = "INSERT INTO srv_cont_os_servico_colaborador (Id_OS, Id_Vendedor, Data, Usuario)
                                VALUES (?, ?, ?, ?)";
            if ($stmt2 = $conn_ext->prepare($sql_colaborador)) {
                $stmt2->bind_param("iiss", $last_id, $id_vendedor, $data, $usuario);
                if (!$stmt2->execute()) {
                    // Enviando resposta de falha na inserção em srv_cont_os_servico_colaborador
                    echo json_encode(['saved' => false, 'error' => $stmt2->error]);
                    $stmt2->close();
                    exit();
                }
                $stmt2->close();
            } else {
                // Enviando resposta de falha na preparação da declaração
                echo json_encode(['saved' => false, 'error' => $conn_ext->error]);
                exit();
            }

            // Enviando resposta de sucesso juntamente com o último Id inserido
            echo json_encode(['saved' => true, 'last_id' => $last_id]);

        } else {
            // Enviando resposta de falha
            echo json_encode(['saved' => false, 'error' => $stmt->error]);
        }

        // Fechando a declaração
        $stmt->close();
    } else {
        // Enviando resposta de falha na preparação da declaração
        echo json_encode(['saved' => false, 'error' => $conn_ext->error]);
    }

    // Fechando a conexão
    $conn_ext->close();
} else {
    // Enviando resposta de campos não enviados
    echo json_encode(['saved' => false, 'error' => 'Campos necessários não enviados.']);
}

//error_log("final.");
