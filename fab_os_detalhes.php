<?php
ob_start(); // Iniciar controle de saída

include("topo.php");
require_once("../db_functions_ext.php");
$con = dbConnect();

date_default_timezone_set('America/Sao_Paulo');

// Obtendo o ID da OS da URL
$os_id = isset($_GET['id_os']) ? intval($_GET['id_os']) : 0;


// Obtendo o ID do vendedor logado
$id_vendedor_logado = isset($_SESSION['id_vendedor']) ? intval($_SESSION['id_vendedor']) : 0;

// Verificando se o usuário tem permissão p1
$tem_permissao_p1 = isset($_SESSION['p1']) && $_SESSION['p1'] == '1';

// Se o usuário não tiver permissão p1, verifica se ele está vinculado à OS
if (!$tem_permissao_p1) {
    $sql_verificacao = "SELECT * FROM srv_cont_os_servico_colaborador 
                        WHERE Id_OS = ? AND Id_Vendedor = ?";

    // Preparar declaração
    $stmt_verificacao = $conn_ext->prepare($sql_verificacao);
    $stmt_verificacao->bind_param("ii", $os_id, $id_vendedor_logado);
    $stmt_verificacao->execute();

    // Verificar se o usuário está vinculado à OS
    $result_verificacao = $stmt_verificacao->get_result();
    if ($result_verificacao->num_rows == 0) {
        // Se não estiver vinculado, redirecionar para fab_os.php
        header("Location: fab_os.php");
        exit;
    }
}


// Consulta para obter detalhes da OS
$sql = "SELECT scos.*, sc.Id AS IdContrato, sc.Tipo AS TipoOs, sc.Id_cliente AS ClienteId, c.*, s.*, sp.* 
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


// Obtendo o ID do cliente
$id_cliente = $os_details['ClienteId'];
/*
echo $id_cliente;
echo 'id_cliente para <<';
*/

// Consulta para verificar se existem iscas relacionadas ao cliente
$sql_iscas = "SELECT * FROM clientes_iscas WHERE Id_cliente = ?";
$stmt_iscas = $conn_ext->prepare($sql_iscas);
$stmt_iscas->bind_param("i", $id_cliente);
$stmt_iscas->execute();

// Verificando se há resultados
$result_iscas = $stmt_iscas->get_result();
if ($result_iscas->num_rows > 0) {
    $iscas_found = true;
} else {
    $iscas_found = false;
}






$sql2 = "SELECT scos.*, s.Id AS Id_servicos, sc.Id AS IdContrato, sc.Tipo AS TipoOs, s.Local_tratamento AS LocalTratamento, c.*, s.*, sp.*, p.Descricao AS DescricaoProduto, sp.Qtdade AS QuantidadeProduto, sp.terceiro_posse AS TerceiroPosse
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
    $servicoId = $row['Id_servicos']; // supondo que Id é o ID de srv_cont_os_servico

    if (!isset($servicos[$servicoId])) {
        $servicos[$servicoId] = [
            'idOs' => $os_id, // Salvando idOs
            'idSrv' => $row['IdContrato'], // Salvando idSrv
            'tipo_servico' => $row['Tipo'],
            'local_tratamento' => $row['LocalTratamento'], // Armazenando o valor de LocalTratamento
            'produtos' => []
        ];
    }

    if ($row['DescricaoProduto'] !== null) {
        $origem = ($row['TerceiroPosse'] === 'SIM') ? 'SP' : 'MG';
        $servicos[$servicoId]['produtos'][] = [
            'id' => $row['Id'],  // adicionando o ID único da entrada
            'produtoId' => $row['id_produto'],
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

            <div>
                <label><b>Status da OS:</b></label><br>
                <ul>
                    <label>Situação atual:</label>
                    <input type="text" id="situacao_atual" value="<?= $os_details['Situacao']; ?>" readonly>
                    <input type="hidden" name="usuario" id="usuario" value='<?php echo $_SESSION['nome']; ?>' readonly>
                    <br>
                    <input type="button" name="reiniciar" id="reiniciar" value="Iniciar/Reiniciar">
                    <input type="button" name="pausar" id="pausar" value="Pausar">
                    <input type="button" name="finalizar" id="finalizar" value="Finalizar">
                    <input type="button" name="cancelar" id="cancelar" value="Cancelar" style="margin-left: 20px;">
                </ul>
            </div>
            <hr>

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
                            Tipo de OS:<br>
                            <input type="text" value="<?= $os_details['TipoOs']; ?>" readonly>
                        </li>

                    </ul>
                    <hr>



            </div>

            <?php




if (empty($servicos)) {
    echo 'Nenhum serviço encontrado.';
} else {
    // Se houver serviços, continue com o loop e exiba os detalhes
    foreach ($servicos as $servicoId => $servico) {
        if (count($servico['produtos']) > 0) {
                echo '<div class="servico-container" data-servico-id="' . $servicoId . '" data-id-os="' . $servico['idOs'] . '" data-id-srv="' . $servico['idSrv'] . '">';

                echo '<b>Tipo de Serviço:</b> ' . $servico['tipo_servico'] . '<br>'; // Modificado para exibir o tipo de serviço
                // Exibindo o valor de Local_tratamento no loop dos serviços.
                echo '<b>Local de Tratamento:</b> ' . $servico['local_tratamento'] . '<br>';

                echo '<table class="produtos-adicionados">';
                echo '<thead><tr><th>Produtos</th><th>Qnts</th><th>Origem</th><th></th></tr></thead>'; // Adicionada coluna para remoção
                echo '<tbody>';



                foreach ($servico['produtos'] as $produto) {
                    echo '<tr data-id="' . $produto['id'] . '" data-produto-id="' . $produto['produtoId'] . '">';

                    echo '<td>' . $produto['descricao_produto'] . '</td>';
                    echo '<td>' . $produto['quantidade'] . '</td>';
                    echo '<td>' . $produto['origem'] . '</td>';
                    echo '<td><button type="button" class="remover-produto">Remover</button></td>'; // Botão de remoção
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';

            }

                //Modal para pesquisa de produtos
                echo '<div id="modal-produtos" data-servico-id="" style="display: none;">
                <input type="text" id="pesquisa-produto-' . $servicoId . '" class="pesquisa-produto" placeholder="Digite para pesquisar..." data-servico-id="' . $servicoId . '">

                <div id="resultados-produtos-' . $servicoId . '"></div>
            </div>
            <div id="modal-background" class="modal-background"></div>
            ';
            if (count($servico['produtos']) > 0) {

                // Botões de adicionar e atualizar
                echo '<button type="button" class="adicionar-produto" data-servico-id="' . $servicoId . '">Adicionar Produto</button>';

                echo '<button type="button" class="atualizar-servico" data-servico-id="' . $servicoId . '">Atualizar (sincronizar)</button>';

                echo '</div><hr>';
            }
            }
        }


            //esta parte após o loop foreach dos serviços

            $idOs = $os_id;
            $idSrv = $os_details['IdContrato'];

            echo '<div class="adicionar-servico-container" data-id-os="' . $idOs . '" data-id-srv="' . $idSrv . '">';

            echo '<button type="button" id="adicionar-servico">Adicionar Serviço</button>';
            echo '<div class="novo-servico-select" style="display: none;">'; // Adicionado estilo display: none
            $query = "SELECT * FROM srv_tipo_servico WHERE status='ativo'";

            $stmt3 = $conn_ext->prepare($query);
            $stmt3->execute();
            $result3 = $stmt3->get_result();

            echo '<select name="novo_tipo_servico" required>';
            echo '<option value="">Selecione um tipo de serviço</option>';
            while ($row = $result3->fetch_assoc()) {
                echo '<option value="' . $row['descricao'] . '">' . $row['descricao'] . '</option>';
            }
            echo '</select>';
            // Adicionando um campo de entrada para Local_tratamento ao adicionar um novo serviço.
            echo '<input type="text" name="local_tratamento" placeholder="Local de Tratamento" required>';

            echo '<button type="button" id="salvar-novo-servico">Salvar Serviço</button>';
            echo '</div>'; // Fim do div .novo-servico-select
            echo '</div>';

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

        function updateOS(status) {
            var usuario = $('#usuario').val();
            var os_id = <?php echo $os_id; ?>;
            $.ajax({
                url: 'fab_os_detalhes_status_updt_ajax.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    status: status,
                    usuario: usuario,
                    os_id: os_id
                },
                success: function(response) {
                    alert(response.message);
                    if (response.success) {
                        $('#situacao_atual').val(response.new_status);
                    }
                }
            });
        }

        $('#pausar').on('click', function() {
            updateOS('pausar');
        });

        $('#reiniciar').on('click', function() {
            updateOS('reiniciar');
        });

        $('#finalizar').on('click', function() {
            updateOS('finalizar');
        });

        $('#cancelar').on('click', function() {
            updateOS('cancelar');
        });


        //perguntar ao usuário se ele deseja ser redirecionado para isca
        var iscasFound = <?php echo $iscas_found ? 'true' : 'false'; ?>;
        var idCliente = <?php echo $id_cliente; ?>;

        if (iscasFound) {
            var redirect = confirm("Foi encontrado porta iscas relacionado a este cliente. Deseja ser redirecionado para o controle dos porta iscas?");
            if (redirect) {
                window.location.href = "fab_porta_isca.php?id_cliente=" + idCliente + "&id_os=" + <?php echo $os_id; ?>;
            } else {
                $('.box-form-container').append('<button id="goToIscas" style="margin-top: 20px;">Ir para o controle de porta iscas</button><hr>');
                $('#goToIscas').on('click', function() {
                    window.location.href = "fab_porta_isca.php?id_cliente=" + idCliente + "&id_os=" + <?php echo $os_id; ?>;
                });
            }
        }






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
        $(document).on('click', '.adicionar-produto', function() {
            // Obtém o ID do serviço do atributo data-servico-id do botão
            var servicoId = $(this).data('servico-id');
            
            console.log("Botão Adicionar Produto clicado para o serviço ID: ", servicoId);

            // Armazena o ID do serviço no modal
            $('#modal-produtos').data('servico-id', servicoId);

            // Exibe o modal de pesquisa de produtos
            $('#modal-background').show();
            $('#modal-produtos').show();

            // Define o foco no campo de pesquisa de produtos
            $('.pesquisa-produto').focus();
        });

        $(document).on('click', '.adicionar-produto2', function() {
            // Obtém o ID do serviço do atributo data-servico-id do botão
            var servicoId = $(this).data('servico-id');
            
            console.log("Botão Adicionar Produto clicado para o serviço ID: ", servicoId);

            // Armazena o ID do serviço no modal
            $('#modal-produtos').data('servico-id', servicoId);

            // Exibe o modal de pesquisa de produtos
            $('#modal-background').show();
            $('#modal-produtos').show();

            // Define o foco no campo de pesquisa de produtos
            $('.pesquisa-produto').focus();
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
            var idOs = $(this).closest('.servico-container').data('id-os'); // Aqui o idOs
            var idSrv = $(this).closest('.servico-container').data('id-srv'); // E aqui o idSrv

            var produtos = [];

            $('.servico-container[data-servico-id="' + servicoId + '"] table tbody tr').each(function() {
                var id = $(this).data('id');
                var produtoId = $(this).data('produto-id');
                var descricao = $(this).find('td').eq(0).text();
                var quantidade = $(this).find('td').eq(1).text();
                var origem = $(this).find('td').eq(2).text();
                var terceiro_posse = (origem === 'SP') ? 'SIM' : 'NÃO';
                produtos.push({
                    id: id, // Adicione isso aqui
                    produtoId: produtoId,
                    descricao: descricao,
                    quantidade: quantidade,
                    terceiro_posse: terceiro_posse
                });
            });



            console.log('Dados enviados para o servidor:', {
                servicoId,
                idOs,
                idSrv,
                produtos
            }); // Log Dados enviados para o servidor

            $.ajax({
                url: 'fab_os_detalhes_servico_updt_ajax.php',
                type: 'POST',
                data: {
                    servicoId: servicoId,
                    idOs: idOs,
                    idSrv: idSrv,
                    produtos: produtos
                },
                dataType: "json",
                //dataType: "text",
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
                    alert('Erro ao salvar os dados. ' + textStatus);
                }
            });
        });






        //novo serviço add
        $('#adicionar-servico').on('click', function() {
            $('.novo-servico-select').show();
            $('.novo-servico-select').focus();
        });



        $(document).on('click', '#salvar-novo-servico', function() {
            // Dentro do evento de click de #salvar-novo-servico
            var localTratamento = $('input[name="local_tratamento"]').val();
            var novoTipoServicoDescricao = $('select[name="novo_tipo_servico"]').val();
            var idOs = $('.adicionar-servico-container').data('id-os');
            var idSrv = $('.adicionar-servico-container').data('id-srv');

            // Verifica se o tipo de serviço foi selecionado
            if (novoTipoServicoDescricao) {
                // Verifica se o local de tratamento foi preenchido
                if (localTratamento) {
                    // Se ambos estão preenchidos, faça a requisição AJAX
                    $.ajax({
                        url: 'fab_os_detalhes_novo_servico_ajax.php',
                        type: 'POST',
                        data: {
                            tipo_servico_descricao: novoTipoServicoDescricao, // removido o 'data: {}' desnecessário
                            local_tratamento_descricao: localTratamento,
                            idOs: idOs,
                            idSrv: idSrv
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                console.log(response); // adicionado para fins de depuração
                                alert(response.message);
                                // var novoServicoHtml = '<div class="servico-container" data-servico-id="' + response.servico.id + '">';

                                var novoServicoHtml = '<div class="servico-container" data-servico-id="' + response.servico.id + '" data-id-os="' + idOs + '" data-id-srv="' + idSrv + '">';

                                // novoServicoHtml += '<b>Tipo de Serviço:</b> ' + response.servico.tipo + '<br>';

                                novoServicoHtml += '<b>Tipo de Serviço:</b> ' + response.servico.tipo_servico + '<br>';

                                novoServicoHtml += '<b>Local de Tratamento:</b> ' + response.servico.local_tratamento + '<br>';

                                novoServicoHtml += '<table class="produtos-adicionados">';
                                novoServicoHtml += '<thead><tr><th>Produtos</th><th>Qnts</th><th>Origem</th><th></th></tr></thead>';
                                novoServicoHtml += '<tbody>';
                                novoServicoHtml += '</tbody>';
                                novoServicoHtml += '</table>';

                                novoServicoHtml += '<button type="button" class="adicionar-produto2" data-servico-id="' + response.servico.id +
                                    '">Adicionar Produto</button>';

                                novoServicoHtml += '<button type="button" class="atualizar-servico" data-servico-id="' + response.servico.id +
                                    '">Atualizar (sincronizar)</button>';

                                novoServicoHtml += '</div><hr>';




                                $('.adicionar-servico-container').before(novoServicoHtml);
                                $('.novo-servico-select').hide();

                            } else {
                                alert(response.message);
                            }
                        }
                    });
                } else {
                    alert('Por favor, preencha o local de tratamento.');
                }
            } else {
                alert('Por favor, selecione um tipo de serviço.');
            }
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

        $('html, body').animate({
            scrollTop: $('.servico-container[data-servico-id="' + servicoId + '"]').offset().top
        }, 0); // 1000ms para animação, você pode ajustar esse valor


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
