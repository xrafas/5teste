<?php
include("topo.php");
require_once("../db_functions_ext.php");
$con = dbConnect();

date_default_timezone_set('America/Sao_Paulo');
?>

<div class="content well span12">
    <div class="row">
        <div class="span12">
            <h2 class="text-center">Nova Ordem de Serviço</h2>

            <hr>

            <div class="box-form-container">
                <form method="post" id="os-form">

                    <ul>
                        <li>
                            Número do Contrato:<br>
                            <select name="id_SRV" required>
                                <option value=""></option>
                                <?php
                                $query_contratos = "SELECT srv_cont.Id AS ContratoId, clientes.Nome AS ClienteNome
                            FROM srv_cont
                            LEFT JOIN clientes ON srv_cont.Id_cliente = clientes.Id
                            ORDER BY srv_cont.Id ASC";

                                $result_contratos = $conn_ext->query($query_contratos);

                                if (!$result_contratos) {
                                    die("Erro na consulta result_contratos: " . mysqli_error($conn_ext));
                                }

                                while ($contrato = $result_contratos->fetch_assoc()) {
                                    echo '<option value="' . $contrato['ContratoId'] . '">Contrato Nº: ' . $contrato['ContratoId'] . ' - Cliente: ' . $contrato['ClienteNome'] . '</option>';
                                }
                                ?>
                            </select>
                        </li>






                        <li>
                            Data: <br>
                            <input type="date" name="Data" required>
                        </li>

                        <li>
                            Situação: <br>

                            <input type="text" name="Situacao" value="ABERTO" readonly>
                        </li>


                        <li>
                            Veículo:<br>
                            <select name="Id_Vec" required>
                                <option value=""></option>
                                <?php
                                $query_veiculos = "SELECT Id, descricao, placa FROM veiculos ORDER BY descricao ASC";

                                $result_veiculos = $conn_ext->query($query_veiculos);

                                if (!$result_veiculos) {
                                    die("Erro na consulta result_veiculos: " . mysqli_error($conn_ext));
                                }

                                while ($veiculo = $result_veiculos->fetch_assoc()) {
                                    echo '<option value="' . $veiculo['Id'] . '">' . $veiculo['descricao'] . ' placa: ' . $veiculo['placa'] . '</option>';
                                }
                                ?>
                            </select>
                        </li>

                        <input type="hidden" name="Id_Vendedor" value="<?php echo $_SESSION['id_vendedor']; ?>">
                        <input type="hidden" name="Usuario" value="<?php echo $_SESSION['nome']; ?>">


                        <li>
                            <input type="submit" value="Salvar">
                        </li>
                    </ul>
                </form>

            </div><!--CONTAINER-->

        </div><!--Span-->
    </div><!--Row-->
</div><!--Content-->

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#os-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: 'fab_os_nova_save_ajax.php',
                type: 'POST',
                data: formData,
                success: function(data) {
                    if (data.saved) {
                        alert("Ordem de Serviço salva com sucesso.");
                        // Redirecionar para fab_os_detalhes.php com o Id da última inserção como parâmetro de URL
                        location.href = "fab_os_detalhes.php?id_os=" + data.last_id;


                    } else {
                        alert("Houve um erro ao salvar a Ordem de Serviço. Tente novamente.");
                        console.log(data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                    alert("Erro ao salvar a Ordem de Serviço: " + jqXHR.responseText);
                }

            });
        });
    }); //final do ready function.
</script>

<?php include("base.php"); ?>