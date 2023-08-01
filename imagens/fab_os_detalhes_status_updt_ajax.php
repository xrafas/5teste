<?php
require_once("../db_functions_ext.php");

if (isset($_POST['status'], $_POST['usuario'], $_POST['os_id'])) {
    $status = $_POST['status'];
    $usuario = $_POST['usuario'];
    $os_id = $_POST['os_id'];

    $field_user = "";
    $field_date = "";
    $field_time = "";
    $new_status = "";

    switch ($status) {
        case 'pausar':
            $field_user = "Pausa_Usuario";
            $field_date = "Pausa_Data";
            $field_time = "Pausa_Hora";
            $new_status = "PAUSADO";
            break;
        case 'reiniciar':
            $field_user = "Execucao_Usuario";
            $field_date = "Execucao_Data";
            $field_time = "Execucao_Hora";
            $new_status = "EXECUTANDO";
            break;
        case 'finalizar':
            $field_user = "Finalizado_Usuario";
            $field_date = "Finalizado_Data";
            $field_time = "Finalizado_Hora";
            $new_status = "FINALIZADO";
            break;
        case 'cancelar':
            $field_user = "Cancela_Usuario";
            $field_date = "Cancela_Data";
            $field_time = "Cancela_Hora";
            $new_status = "CANCELADO";
            break;
    }

    // Pega a data e hora atual
    $date = date('Y-m-d');
    $time = date('H:i:s');

    $conn_ext->begin_transaction();
    try {
        // Update the status fields
        $sql = "UPDATE srv_cont_os SET $field_user = ?, $field_date = ?, $field_time = ?, Situacao = ? WHERE Id = ?";
        $stmt = $conn_ext->prepare($sql);
        $stmt->bind_param("ssssi", $usuario, $date, $time, $new_status, $os_id);
        $stmt->execute();
        
        $conn_ext->commit();
        echo json_encode(['message' => 'Status da Ordem de Serviço atualizada com sucesso', 'success' => true, 'new_status' => $new_status]);
    } catch (Exception $e) {
        $conn_ext->rollback();
        echo json_encode(['message' => 'Falha ao atualizar a Ordem de Serviço', 'success' => false]);
    }
}
?>
