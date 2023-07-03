<?php
ob_start(); // Iniciar controle de saída
include("topo.php");
require_once("../db_functions_ext.php");
$con = dbConnect();

date_default_timezone_set('America/Sao_Paulo');

$idClienteParam = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
$idOSParam = isset($_GET['id_os']) ? $_GET['id_os'] : null;
$nomeCliente = '';

// Obtendo o ID do vendedor logado
$id_vendedor_logado = isset($_SESSION['id_vendedor']) ? intval($_SESSION['id_vendedor']) : 0;

// Verificando se o usuário tem permissão p1
$tem_permissao_p1 = isset($_SESSION['p1']) && $_SESSION['p1'] == '1';
/*echo $_SESSION['p1'];
echo 'p1 e idvendedor';
echo $_SESSION['id_vendedor'];
echo 'idcliente';
echo $_GET['id_cliente'];
*/

//condição para expulsar se não tiver permissão

if (!$tem_permissao_p1) {
    // Verificar se o usuário tem permissão para ver a página com o Id_cliente específico
    $stmt = $conn_ext->prepare("
        SELECT * 
        FROM srv_cont_os_servico_colaborador as scosc
        INNER JOIN srv_cont_os as sco ON scosc.Id_OS = sco.Id
        INNER JOIN srv_cont as sc ON sco.id_SRV = sc.Id
        WHERE scosc.Id_Vendedor = ? AND sc.Id_cliente = ?
    ");
    $stmt->bind_param("ii", $id_vendedor_logado, $idClienteParam);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // O usuário não tem permissão para ver a página
        header("Location: index.php");
        exit();
    }
}



// trazer o nome do cliente
if ($idClienteParam !== null) {
    $stmt = $conn_ext->prepare("SELECT Nome FROM clientes WHERE Id = ?");
    $stmt->bind_param("i", $idClienteParam);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $nomeCliente = $result->fetch_assoc()['Nome'];
    }
}


?>

<div class="content well span12">
    <div class="row">
        <div class="span12">
            <a href="fab_os_detalhes.php?id_os=<?= isset($_GET['id_os']) ? $_GET['id_os'] : 0 ?>" class="btn btn-secondary">Voltar para Detalhes da OS</a>

            <div class="box-adress" style="padding: 20px; background-color: #f9f9f9; border-radius: 10px;">
                <div style="margin-bottom: 10px;">
                    <i class="fas fa-search"></i> <strong style="font-size: 1.2em;">Filtro de busca</strong>
                </div>
                <div style="display: flex; align-items: center;">
                    <div style="margin-right: 10px;">
                        <label for="codigo" style="display: block; font-weight: bold; margin-bottom: 5px;">Código:</label>
                        <input type="number" id="codigo" name="codigo" placeholder="Digite o código" style="width: 50px; padding: 5px;" value="<?php echo isset($_GET['codigo']) ? $_GET['codigo'] : ''; ?>">
                    </div>
                    <div style="margin-right: 10px;">
                        <label for="setor" style="display: block; font-weight: bold; margin-bottom: 5px;">Setor:</label>
                        <select id="setor" name="setor" style="min-width: 50px; padding: 5px;">
                            <option value=""></option>
                            <?php
                            $query = "SELECT DISTINCT setor FROM clientes_iscas_mov WHERE Id_cliente = ?";
                            $stmt = $conn_ext->prepare($query);
                            $stmt->bind_param("i", $idClienteParam);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $selectedSetor = isset($_GET['setor']) ? $_GET['setor'] : '';
                            while ($row = $result->fetch_assoc()) {
                                $selectedAttribute = ($row['setor'] === $selectedSetor) ? 'selected' : '';
                                echo "<option value='" . $row['setor'] . "' $selectedAttribute>" . $row['setor'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <input type="button" id="b_ir" name="b_ir" value="IR" onclick="javascript:filtrar();" style="padding: 10px 20px; background-color: #007bff; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
                    </div>
                </div>
            </div>




            <div class="box-form-container">

                <h2>Gerenciamento de Porta Iscas - <?= $nomeCliente ?></h2>


                <!-- Tabela de porta iscas -->
                <table id="porta-iscas-table" class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Setor</th> <!-- Nova coluna para o setor -->
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <!-- As linhas serão preenchidas via AJAX -->
                    </tbody>
                </table>

                <!-- Button trigger modal -->
                <button type="button" id="add-porta-isca">Adicionar Porta Isca</button>

                <!-- Modal -->
                <div id="porta-iscas-modal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 id="porta-iscas-modal-label"></h2>
                            <!--<span id="modal-close">&times;</span>-->
                        </div>
                        <div class="modal-body">

                            <!-- Formulário de adicionar/alterar porta iscas -->
                            <form id="porta-iscas-form">
                                <input type="hidden" id="id" name="id">

                                <div>
                                    <label for="setor">Setor:</label>
                                    <select id="setor" name="setor">
                                        <!-- As opções serão preenchidas via AJAX -->
                                    </select>
                                </div>
                                <div>
                                    <label for="descricao">Descrição:</label>
                                    <input type="text" id="descricao" name="descricao">
                                </div>
                                <div>
                                    <label for="tipo">Tipo:</label>
                                    <select id="tipo" name="tipo">
                                        <option value="TOXICA">Tóxica</option>
                                        <option value="ATOXICA">Atóxica</option>
                                        <option value="LUMINOSA">Luminosa</option>
                                        <option value="FEROMONICA">Ferômonica</option>
                                    </select>
                                </div>

                            </form>

                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="close-modal" class="btn btn-secondary">Fechar</button>
                            <button type="button" id="save-button" class="btn btn-primary">Salvar</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
    $(document).ready(function() {

        var idClienteParam = new URLSearchParams(window.location.search).get('id_cliente');
        var idOsParam = new URLSearchParams(window.location.search).get('id_os');
        var codigo = document.getElementById("codigo").value;
        var setor = document.getElementById("setor").value;

        // Função para carregar a tabela
        function loadTable(idCliente, codigo, setor) {
            var data = {
                action: 'get_all',
                id_cliente: idCliente,
                id_os: idOsParam
            };

            if (codigo) {
                data.codigo = codigo;
            }

            if (setor) {
                data.setor = setor;
            }

            $.ajax({
                url: 'fab_porta_isca_ajax.php',
                type: 'GET',
                data: data,
                success: function(response) {
                    $('#porta-iscas-table tbody').html(response);
                }
            });
        }

        // Carregar a tabela
        loadTable(idClienteParam, codigo, setor);


        // Função para carregar a Setores
        function loadSetores(id) {
            $.ajax({
                url: 'fab_porta_isca_ajax.php',
                type: 'GET',
                data: {
                    action: 'get_setores',
                    id: id // Aqui estamos passando o id para a requisição
                },
                success: function(response) {
                    $('#setor').html(response);
                }
            });
        }

        // Botão Adicionar Porta Isca
        $('#add-porta-isca').on('click', function() {
            $(this).prop('disabled', true).text('Carregando...'); // Desabilitar o botão e alterar texto para Carregando
            loadSetores(null);
            $('#porta-iscas-modal-label').text('Adicionar Porta Isca');
            $('#porta-iscas-form')[0].reset();
            $('#id').val(''); // Certificando de limpar o campo id.
            $('#porta-iscas-modal').css('display', 'block');
            $(this).prop('disabled', false).text('Adicionar Porta Isca'); // Reabilitar o botão
        });

        // Botão Salvar do formulário
        $('#save-button').on('click', function() {
            $(this).prop('disabled', true); // Desativar o botão salvar durante a requisição

            var formData = $('#porta-iscas-form').serialize() + '&action=save&id_cliente=' + idClienteParam;

            $.ajax({
                url: 'fab_porta_isca_ajax.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    alert(response);
                    loadTable(idClienteParam);
                    /*
                    $('#porta-iscas-modal').modal('hide');
                    $('body').removeClass('modal-open'); // Isso remove o fundo preto do modal
                    $('.modal-backdrop').remove(); // Isso remove o fundo preto do modal
                    */
                    $('#porta-iscas-modal').css('display', 'none');
                },
                complete: function() {
                    $('#save-button').prop('disabled', false); // Reativar o botão salvar após a requisição
                }
            });
        });


        // Botão Fechar do modal
        $('#close-modal').on('click', function() {
            $('#porta-iscas-modal').css('display', 'none');
        });


        // Ação de editar na tabela
        $('#porta-iscas-table').on('click', '.edit-btn', function() {
            $(this).prop('disabled', true).text('Carregando...'); // Desabilitar o botão e alterar texto para Carregando

            var id = $(this).data('id');
            //loadSetores(); //usado mais para baixo 
            $.ajax({
                url: 'fab_porta_isca_ajax.php',
                type: 'GET',
                data: {
                    action: 'get_single',
                    id: id
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    //console.log(data); //  para depurar
                    loadSetores(id); // Passa o setor para a função loadSetores            
                    $('#id').val(data.Id);
                    $('#id_cliente').val(idClienteParam); //  idClienteParam 
                    $('#descricao').val(data.Descricao);
                    $('#status').val(data.Status);
                    //$('#setor').val(data.Setor);
                    $('#tipo').val(data.Tipo);

                    $('#porta-iscas-modal-label').text('Editar Porta Isca');

                    $('#porta-iscas-modal').css('display', 'block');

                },
                complete: function() {
                    // Reabilitar o botão
                    $('.edit-btn').prop('disabled', false).text('Editar');
                }
            });
        });


        // Ação de deletar na tabela
        $('#porta-iscas-table').on('click', '.delete-btn', function() {
            var id = $(this).data('id');

            if (confirm('Você tem certeza que deseja deletar?')) {
                if (confirm("ATENÇÃO: Se você prosseguir, o porta iscas será EXCLUÍDO permanentemente. Deseja continuar?")) {
                    $.ajax({
                        url: 'fab_porta_isca_ajax.php',
                        type: 'POST',
                        data: {
                            action: 'delete',
                            id: id
                        },
                        success: function(response) {
                            alert(response);
                            loadTable(idClienteParam);
                        }
                    });
                }
            }
        });



    });

    window.onclick = function(event) {
        var modal = document.getElementById('porta-iscas-modal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Fechando o modal com o botão 'X'
    document.getElementById('modal-close').onclick = function() {
        document.getElementById('porta-iscas-modal').style.display = 'none';
    }

    function filtrar() {
        var codigo = document.getElementById("codigo").value;
        var setor = document.getElementById("setor").value;
        var idClienteParam = new URLSearchParams(window.location.search).get('id_cliente');
        var idOsParam = new URLSearchParams(window.location.search).get('id_os');
        var url = 'fab_porta_isca.php?id_cliente=' + idClienteParam + '&id_os=' + idOsParam;

        if (codigo) {
            url += '&codigo=' + codigo;
        }
        if (setor) {
            url += '&setor=' + setor;
        }
        location.href = url;
    }
</script>