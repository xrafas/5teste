<?php
include("topo.php");
require_once("../db_functions_ext.php");
$con = dbConnect();

date_default_timezone_set('America/Sao_Paulo');

// Obtendo o ID da OS da URL
$os_id = isset($_GET['id_os']) ? intval($_GET['id_os']) : 0;

// Consulta para obter detalhes da OS
$sql = "SELECT scos.*, sc.Id AS IdContrato, sc.Tipo AS TipoOs, c.*, s.*, sp.* 
        FROM srv_cont_os scos 
        LEFT JOIN srv_cont sc ON scos.id_SRV = sc.Id 
        LEFT JOIN clientes c ON sc.Id_cliente = c.Id 
        LEFT JOIN srv_cont_os_servico s ON s.Id_OS = scos.Id
        LEFT JOIN srv_cont_os_servico_padrao sp ON sp.Id_servico = s.Id
        WHERE scos.Id = ?";



// Preparar declaração
$stmt = $conn_ext->prepare($sql);
$stmt->bind_param("i", $os_id);
$stmt->execute();

// Obtendo os detalhes da OS
$result = $stmt->get_result();
$os_details = $result->fetch_assoc();


$sql2 = "SELECT scos.*, sc.Id AS IdContrato, sc.Tipo AS TipoOs, c.*, s.*, sp.*, p.Descricao AS DescricaoProduto, sp.Qtdade AS QuantidadeProduto, sp.terceiro_posse AS TerceiroPosse
         FROM srv_cont_os scos 
         LEFT JOIN srv_cont sc ON scos.id_SRV = sc.Id 
         LEFT JOIN clientes c ON sc.Id_cliente = c.Id 
         LEFT JOIN srv_cont_os_servico s ON s.Id_OS = scos.Id
         LEFT JOIN srv_cont_os_servico_padrao sp ON sp.Id_servico = s.Id
         LEFT JOIN produtos p ON sp.id_produto = p.Id
         WHERE scos.Id = ?";




// Preparar declaração
$stmt2 = $conn_ext->prepare($sql2);
$stmt2->bind_param("i", $os_id);
$stmt2->execute();

// Obtendo os detalhes da OS
$result2 = $stmt2->get_result();


$servicos = [];

while ($row = $result2->fetch_assoc()) {
    $servicoId = $row['Id_servico']; // supondo que Id é o ID de srv_cont_os_servico

    if (!isset($servicos[$servicoId])) {
        $servicos[$servicoId] = [
            'tipo_servico' => $row['Tipo'], //  esta linha conforme o nome da coluna que contém o tipo de serviço
            'produtos' => []
        ];
    }

    if ($row['DescricaoProduto'] !== null) {
        $origem = ($row['TerceiroPosse'] === 'SIM') ? 'SP' : 'MG';
        $servicos[$servicoId]['produtos'][] = [
            'produtoId' => $row['id_produto'], //  esta linha para incluir o produtoId
            'descricao_produto' => $row['DescricaoProduto'],
            'quantidade' => $row['QuantidadeProduto'],
            'origem' => $origem
        ];
    }
}


?>

<div class="content well span12">
    <div class="row">
        <div class="span12">
            <!--
            <h2 class="text-center">Detalhes da Ordem de Serviço</h2>

            <hr>
-->

            <div class="box-form-container">
                <form method="post" id="os-form">

                    <b>Cliente:</b><br><br>

                    <ul>


                        <?php if (!empty($os_details['Nome'])) : ?>
                            <li>
                                Nome:<br>
                                <input type="text" value="<?= $os_details['Nome']; ?>" readonly>
                            </li>
                        <?php endif; ?>



                        <?php if (!empty($os_details['Endereco'])) : ?>
                            <li>
                                Endereço:<br>
                                <input type="text" value="<?= $os_details['Endereco']; ?>" readonly>
                            </li>
                        <?php endif; ?>


                        <?php if (!empty($os_details['Bairro'])) : ?>
                            <li>
                                Bairro:<br>
                                <input type="text" value="<?= $os_details['Bairro']; ?>" readonly>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($os_details['Complemento'])) : ?>
                            <li>
                                Complemento:<br>
                                <input type="text" value="<?= $os_details['Complemento']; ?>" readonly>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($os_details['DDD'])) : ?>
                            <li>
                                DDD:<br>
                                <input type="text" value="<?= $os_details['DDD']; ?>" readonly>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($os_details['Telefone'])) : ?>
                            <li>
                                Telefone:<br>
                                <input type="text" value="<?= $os_details['Telefone']; ?>" readonly>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($os_details['Telefone2'])) : ?>
                            <li>
                                Telefone 2:<br>
                                <input type="text" value="<?= $os_details['Telefone2']; ?>" readonly>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($os_details['Telefone_Alternativo'])) : ?>
                            <li>
                                Telefone Alternativo:<br>
                                <input type="text" value="<?= $os_details['Telefone_Alternativo']; ?>" readonly>
                            </li>
                        <?php endif; ?>


                    </ul>
                    <br><b>Detalhes:</b><br><br>

                    <ul>


                        <li>
                            Número do Contrato:<br>
                            <input type="text" value="<?= $os_details['IdContrato']; ?>" readonly>
                        </li>

                        <li>
                            Data: <br>
                            <input type="text" value="<?= $os_details['Data']; ?>" readonly>
                        </li>

                        <li>
                            Situação: <br>
                            <input type="text" value="<?= $os_details['Situacao']; ?>" readonly>
                        </li>


                        <li>
                            Tipo de OS:<br>
                            <input type="text" value="<?= $os_details['TipoOs']; ?>" readonly>
                        </li>

                    </ul>
                    <hr>



            </div>

            <?php




            foreach ($servicos as $servicoId => $servico) {
                echo '<div class="servico-container" data-servico-id="' . $servicoId . '">';

                echo '<b>Tipo de Serviço:</b> ' . $servico['tipo_servico'] . '<br>'; // Modificado para exibir o tipo de serviço

                echo '<table class="produtos-adicionados">';
                echo '<thead><tr><th>Produtos</th><th>Qnts</th><th>Origem</th><th></th></tr></thead>'; // Adicionada coluna para remoção
                echo '<tbody>';



                foreach ($servico['produtos'] as $produto) {
                    echo '<tr data-produto-id="' . $produto['produtoId'] . '">';
                    echo '<td>' . $produto['descricao_produto'] . '</td>';
                    echo '<td>' . $produto['quantidade'] . '</td>';
                    echo '<td>' . $produto['origem'] . '</td>';
                    echo '<td><button type="button" class="remover-produto">Remover</button></td>'; // Botão de remoção
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';

                //Modal para pesquisa de produtos
                echo '<div id="modal-produtos" data-servico-id="" style="display: none;">
                <input type="text" id="pesquisa-produto-' . $servicoId . '" class="pesquisa-produto" placeholder="Digite para pesquisar..." data-servico-id="' . $servicoId . '">

                <div id="resultados-produtos-' . $servicoId . '"></div>
            </div>
            <div id="modal-background" class="modal-background"></div>
            ';

                // Botões de adicionar e atualizar
                echo '<button type="button" class="adicionar-produto" data-servico-id="' . $servicoId . '">Adicionar Produto</button>';

                echo '<button type="button" class="atualizar-servico" data-servico-id="' . $servicoId . '">Atualizar (sincronizar)</button>';

                echo '</div><hr>';
            }


            ?>


            </ul>
            </form>

        </div><!--CONTAINER-->

    </div><!--Span-->
</div><!--Row-->
</div><!--Content-->

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#pesquisar-produtos').on('click', function() {
            // Exibe o modal de pesquisa de produtos
            $('#modal-produtos').show();
        });

        // Pesquisa de produtos
        $('.pesquisa-produto').on('keyup', function() {
            var servicoId = $(this).data('servico-id');
            var query = $(this).val();
            $.ajax({
                url: 'fab_os_pesquisar_produtos.php',
                type: 'GET',
                data: {
                    query: query,
                    servicoId: servicoId
                },
                success: function(response) {
                    $('#resultados-produtos-' + servicoId).html(response);
                }
            });
        });



        // Lidar com os que já vem carregados
        $('.adicionar-produto').on('click', function() {
            // Obtém o ID do serviço do atributo data-servico-id do botão
            var servicoId = $(this).data('servico-id');

            // Armazena o ID do serviço no modal
            $('#modal-produtos').data('servico-id', servicoId);

            // Exibe o modal de pesquisa de produtos
            $('#modal-background').show();
            $('#modal-produtos').show();
        });

        // Feche o modal se o plano de fundo escurecido for clicado
        $('#modal-background').on('click', function() {
            $('#modal-background').hide();
            $('#modal-produtos').hide();
        });



        $(document).on('click', '#modal-adicionar-produto', function() {
            var servicoId = $('#modal-produtos').data('servico-id');
            var produtoId = $('#modal-produtos select[name="produto"]').val();
            var quantidade = $('#modal-produtos input[name="quantidade"]').val();

            // Adicione o produto à tabela HTML
            $('.servico-container[data-servico-id="' + servicoId + '"] table').append('<tr><td>' + produtoId + '</td><td>' + quantidade + '</td><td>Origem</td><td><button type="button" class="remover-produto">Remover</button></td></tr>');
        });





        $(document).on('click', '.remover-produto', function() {
            removerProduto(this);
        });



        //att
        $(document).on('click', '.atualizar-servico', function() {
            var servicoId = $(this).data('servico-id');
            var produtos = [];

            $('.servico-container[data-servico-id="' + servicoId + '"] table tbody tr').each(function() {
                var produtoId = $(this).data('produto-id');
                var descricao = $(this).find('td').eq(0).text();
                var quantidade = $(this).find('td').eq(1).text();
                produtos.push({
                    produtoId: produtoId,
                    descricao: descricao,
                    quantidade: quantidade
                });
            });


            console.log('Data sent to server:', {
                servicoId,
                produtos
            }); // Log the data sent to server

            $.ajax({
                url: 'fab_os_detalhes_servico_updt_ajax.php',
                type: 'POST',
                data: {
                    servicoId: servicoId,
                    produtos: produtos
                },
                dataType: "json",
                // Dentro da função de sucesso do AJAX.
                success: function(response) {
                    console.log('Resposta do servidor:', response); // Adicione esta linha

                    // este trecho para exibir as consultas SQL no log do console
                    if (response.queries && response.queries.length > 0) {
                        console.log('Consultas SQL enviadas:');
                        response.queries.forEach(function(query) {
                            console.log(query);
                        });
                    }


                    if (response.success) {
                        alert('Dados salvos com sucesso!');
                    } else {
                        alert('Erro ao salvar os dados: ' + response.message);
                    }
                },

                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Erro ao salvar os dados.');
                    console.error('Error details:', jqXHR, textStatus, errorThrown); // Log the error details
                }
            });
        });




    }); //fim do ready

    function adicionarProduto(produtoId, produtoNome) {
        var quantidade = null;

        // Continue perguntando até receber uma quantidade numérica ou o usuário pressionar "Cancelar"
        while (true) {
            quantidade = prompt("Informe a quantidade para o produto: " + produtoNome);

            if (quantidade === null) {
                // O usuário pressionou "Cancelar", então não faça nada
                return;
            }

            quantidade = quantidade.replace(',', '.');

            if (!isNaN(quantidade) && quantidade.trim() !== '') {
                // Se a quantidade for um número, saia do loop
                break;
            } else {
                alert("Por favor, insira uma quantidade válida em números.");
            }
        }

        var origem = null;

        // Continue perguntando até receber 'MG' ou 'SP' ou o usuário pressionar "Cancelar"
        while (true) {
            origem = prompt("Informe a origem: 'MG' para Empresa (Ambienthal MG) ou 'SP' para Parceira (Ambienthal SP)", "MG").toUpperCase();

            if (origem === null) {
                // O usuário pressionou "Cancelar", então não faça nada
                return;
            }

            if (origem === 'MG' || origem === 'SP') {
                // Se a origem for 'MG' ou 'SP', saia do loop
                break;
            } else {
                alert("Por favor, insira 'MG' para Empresa (Ambienthal MG) ou 'SP' para Parceira (Ambienthal SP).");
            }
        }

        var rowHtml = '<tr data-produto-id="' + produtoId + '">';
        rowHtml += '<td>' + produtoNome + '</td>';
        rowHtml += '<td>' + quantidade + '</td>';
        rowHtml += '<td>' + origem + '</td>';
        rowHtml += '<td><button type="button" onclick="removerProduto(this)">Remover</button></td>';
        rowHtml += '</tr>';

        var servicoId = $('#modal-produtos').data('servico-id');
        $('.servico-container[data-servico-id="' + servicoId + '"] tbody').append(rowHtml);



        $('#produtos-adicionados').show(); // Exibe a tabela quando um produto é adicionado
        $('#modal-produtos').hide(); // Esconde o modal após adicionar o produto
        $('#modal-background').hide();
    }




    function removerProduto(button) {
        $(button).closest('tr').remove();
    }
</script>

<style>
    .produtos-adicionados {
        display: table;
        border-collapse: collapse;
        width: 100%;
    }

    .produtos-adicionados th,
    .produtos-adicionados td {
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .produtos-adicionados th {
        background-color: #f2f2f2;
    }

    .produtos-adicionados td {
        min-width: 15px;
        max-width: 150px;
    }

    .modal-background {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    #modal-produtos {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 20px;
        border: 1px solid #ccc;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        max-width: 80%;
        z-index: 1010;
    }

    #modal-produtos .modal-content {
        max-height: 300px;
        overflow-y: auto;
    }
</style>







<?php include("base.php"); ?>