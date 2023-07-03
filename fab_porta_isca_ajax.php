<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de sessão para ter acesso a variáveis de sessão
include_once('session.php');

// Inclui funções de banco de dados do sysweb
include_once('../db_functions.php');

// Inclui funções de banco de dados externas
require_once("../db_functions_ext.php");

// Obtém a ação requerida através de uma requisição
$action = $_REQUEST['action'];

// Escolhe a ação a ser executada
switch ($action) {
    case 'get_all':
        // Verifica se id_cliente está definido na URL, senão, define como null
        $id_cliente = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
        $id_os = isset($_GET['id_os']) ? $_GET['id_os'] : null;
        $codigo = isset($_GET['codigo']) ? $_GET['codigo'] : null;
        $setor = isset($_GET['setor']) ? $_GET['setor'] : null;


        // Inicia a query SQL
        //$sql = "SELECT DISTINCT cim.Id, cim.Descricao as Descricao_Pi, cim.Id_cliente, cim.setor, ci.Descricao as setor_descricao, cim.Tipo, ci.Status as ci_status FROM clientes_iscas_mov AS cim INNER JOIN clientes_iscas AS ci ON cim.setor = ci.Descricao";
        $sql = "SELECT cim.Id, cim.Descricao as Descricao_Pi, cim.Id_cliente, cim.setor, 
        MAX(ci.Descricao) as setor_descricao, cim.Tipo, MAX(ci.Status) as ci_status 
 FROM clientes_iscas_mov AS cim 
 INNER JOIN clientes_iscas AS ci ON cim.setor = ci.Descricao ";

        $where_clause = [];

        if ($id_cliente !== null) {
            $where_clause[] = "cim.Id_cliente = ?";
        }
        if ($setor !== null) {
            $where_clause[] = "cim.setor = ?";
        }

        if ($codigo !== null) {
            $where_clause[] = "cim.Id = ?";
        }

        if (!isset($_SESSION['p1']) || !$_SESSION['p1']) {
            $where_clause[] = "ci.Status = 'ATIVO'";
        }

        if (!empty($where_clause)) {
            $sql .= " WHERE " . implode(" AND ", $where_clause);
        }

        $sql .= " GROUP BY cim.Id, cim.Descricao, cim.Id_cliente, cim.setor, cim.Tipo ORDER BY cim.Id ASC";


        // Prepara a query SQL
        $stmt = $conn_ext->prepare($sql);

        $bind_params = [];

        if ($id_cliente !== null) {
            $bind_params[] = $id_cliente;
        }
        
        if ($setor !== null) {
            $bind_params[] = $setor;
        }
        
        if ($codigo  !== null) {
            $bind_params[] = $codigo;
        }
        
        if (!empty($bind_params)) {
            $stmt->bind_param(str_repeat("s", count($bind_params)), ...$bind_params);
        }
        

        $stmt->execute();


        // Obtém os resultados da query
        $result = $stmt->get_result();


        //$dados = [];        // essa var $dados com um array vazio está precisando de ajustes.



        // Itera sobre os resultados e exibe na tabela
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Id']}</td>";
            echo "<td>{$row['setor']}</td>";
            echo "<td>{$row['Descricao_Pi']}</td>";
            echo "<td>{$row['Tipo']}</td>"; // Trocando 'status' por 'Tipo'
            echo "<td>";

            // Se o usuário tiver permissão p1, mostra os botões de editar e deletar
            if (isset($_SESSION['p1']) && $_SESSION['p1']) {
                echo "<button class='edit-btn btn btn-primary' data-id='{$row['Id']}'>Editar</button> <button class='delete-btn btn btn-danger' data-id='{$row['Id']}'>Deletar</button>";
            }

            // Mostra o botão de monitorar para todos os usuários
            echo "<button class='monitor-btn btn btn-success' data-id='{$row['Id']}' style='margin-left:10px;'>Monitorar</button>";

            echo "</td>";
            echo "</tr>";
        }

        // Encerra a declaração e a conexão
        $stmt->close();
        $conn_ext->close();
        break;




    case 'get_single':
        // Obtém o id através da URL
        $id = $_GET['id'];

        // Define a query SQL para obter uma única linha com base no id
        $sql = "SELECT cim.*, ci.Descricao as setor_descricao FROM clientes_iscas_mov AS cim INNER JOIN clientes_iscas AS ci ON cim.setor = ci.Descricao WHERE cim.Id = ?";

        // Prepara a query SQL
        $stmt = $conn_ext->prepare($sql);

        // Vincula o parâmetro id
        $stmt->bind_param("i", $id);

        // Executa a query
        $stmt->execute();

        // Obtém o resultado
        $result = $stmt->get_result();

        // Verifica se há um resultado
        if ($result->num_rows > 0) {
            // Obtém os dados em forma de array associativo
            $row = $result->fetch_assoc();

            // Retorna os dados no formato JSON
            echo json_encode($row);
        }

        // Encerra a declaração e a conexão
        $stmt->close();
        $conn_ext->close();
        break;


    case 'save':
        // Obtém os dados do POST
        $id = $_POST['id'];
        $descricao = $_POST['descricao'];
        $id_cliente = $_POST['id_cliente'];
        $setor = $_POST['modalSetor'];
        $tipo = $_POST['tipo'];

        // Verifica se o ID é nulo (novo registro) ou não (atualização)
        if ($id == null) {
            // Define a query SQL para inserir novo registro
            $sql = "INSERT INTO clientes_iscas_mov (Descricao, Id_cliente, setor, Tipo) VALUES (?, ?, ?, ?)";

            // Prepara a query SQL
            $stmt = $conn_ext->prepare($sql);

            // Faz o bind dos valores
            $stmt->bind_param("siss", $descricao, $id_cliente, $setor, $tipo);
        } else {
            // Define a query SQL para atualizar o registro existente
            $sql = "UPDATE clientes_iscas_mov SET Descricao = ?, Id_cliente = ?, setor = ?, Tipo = ? WHERE Id = ?";

            // Prepara a query SQL
            $stmt = $conn_ext->prepare($sql);

            // Faz o bind dos valores
            $stmt->bind_param("sissi", $descricao, $id_cliente, $setor, $tipo, $id);
        }

        // Executa a query SQL
        $stmt->execute();

        // Retorna sucessoif ($id == null) {
        if ($id == null) {
            echo "Porta iscas adicionado com sucesso!";
        } else {
            echo "Porta iscas atualizado com sucesso!";
        }
        break;

    case 'delete':
        // Obtém o ID através da URL
        $id = $_POST['id'];

        // Define a query SQL para deletar a linha com base no ID
        $sql = "DELETE FROM clientes_iscas_mov WHERE Id = ?";

        // Prepara a query SQL
        $stmt = $conn_ext->prepare($sql);

        // Faz o bind do valor ID
        $stmt->bind_param("i", $id);

        // Executa a query SQL
        $stmt->execute();

        // Retorna sucesso
        echo "Porta iscas deletado com sucesso!";
        break;

        case 'get_setores':
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            $id_cliente = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
            $selectedSetor = null;
        
            // Obtendo o setor selecionado de clientes_iscas_mov
            if ($id !== null) {
                $stmt = $conn_ext->prepare("SELECT setor FROM clientes_iscas_mov WHERE Id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $selectedSetor = $result->fetch_assoc()['setor'];
                }
            }
        
            // Obtendo os setores da tabela clientes_iscas
            $sql = "SELECT DISTINCT Descricao FROM clientes_iscas WHERE Id_cliente = ? AND Status = 'ATIVO' ORDER BY Descricao";
            $stmt = $conn_ext->prepare($sql);
            $stmt->bind_param("s", $id_cliente);
            $stmt->execute();
            $result = $stmt->get_result();
        
            while ($row = $result->fetch_assoc()) {
                // Marcar a opção como selecionada se for o valor salvo
                $selected = ($row['Descricao'] == $selectedSetor) ? 'selected' : '';
                echo '<option value="' . $row['Descricao'] . '" ' . $selected . '>' . $row['Descricao'] . '</option>';
            }
            break;
        





    default:
        echo "Ação não reconhecida!";
        break;
}
