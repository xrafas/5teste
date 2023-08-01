<?php

// Habilita a exibição de erros 

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/


include_once('session.php');
include_once('../db_functions.php');
require_once("../db_functions_ext.php");

// Estabelece uma conexão com o banco de dados do sysweb
$con = dbConnect();

// Define uma resposta padrão em caso de falha
$response = ['success' => false, 'message' => 'Erro não especificado.', 'queries' => []];

// Verifica se o método de requisição é POST  
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['servicoId'])) {

    $servicoId = intval($_POST['servicoId']);
    $idOs = intval($_POST['idOs']);
    $idSrv = intval($_POST['idSrv']);
    $conn_ext->begin_transaction();


    if (isset($_POST['acao']) && $_POST['acao'] == 'remover') {
        $produto = $_POST['produtos'][0];
        $produtoId = intval($_POST['produtoId']);
        if (isset($produto['idProdutoMov'])) {
            $idProdutoMov = intval($produto['idProdutoMov']);
        }

        if (isset($produto['quantidade'])) {
            $quantidade = $produto['quantidade'];
            $produtoQuantidade = floatval(str_replace(",", ".", $quantidade)); // Pega a quantidade do produto
        }
        $idPadrao = intval($_POST['id']);
        $produtos = $_POST['produtos'];

        // Remove o produto
        $ids = [$idPadrao]; // Coloque o produtoId em um array para a função deletaProduto
        deletaProduto($conn_ext, $ids, $idSrv, $idOs, $servicoId, $response);

        // Atualiza a quantidade restante após a remoção
        atualizaQuantidadeRestante($conn_ext, $idProdutoMov, 0, $produtoQuantidade, true);
    }

    // Verifica se está adicionando um produto
    elseif (isset($_POST['produtos']) && !empty($_POST['produtos'])) {
        // Obtém os dados do produto
        $produto = $_POST['produtos'][0];
        $produtoId = intval($produto['produtoId']);
        $idProdutoMov = intval($produto['idProdutoMov']);
        $quantidade = floatval(str_replace(",", ".", $produto['quantidade']));
        $terceiro_posse = $produto['terceiro_posse'];

        // Adiciona o produto
        $id = null; // Como você está adicionando um novo produto, o id deve ser null
        insereOuAtualizaProduto($conn_ext, $id, $servicoId, $produtoId, $idProdutoMov, $quantidade, $terceiro_posse, $idSrv, $idOs, $response);
    }



    // Finaliza a transação
    $conn_ext->commit();

    // Define uma resposta de sucesso
    $response['success'] = true;
    $response['message'] = 'Produtos atualizados com sucesso.';
}






// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

/*
echo '<pre>';
var_dump($response);
echo '</pre>';
echo '<pre>';
echo json_encode($response, JSON_PRETTY_PRINT);
echo '</pre>';
*/

// Envia a resposta em formato JSON
echo json_encode($response);

if ($json === false) {
    echo 'json_encode failed: ' . json_last_error_msg();
}


// Função que verifica se tem a quantidade disponível de um produto
function quantidadeDisponivel($conn_ext, $produtoId)
{
    // Consulta SQL para selecionar a quantidade disponível do produto
    $sql = "SELECT Qtdade_Restante FROM produtos_mov WHERE Id_Produto = ? ORDER BY Id DESC LIMIT 1";
    $stmt = $conn_ext->prepare($sql);
    $stmt->bind_param("i", $produtoId);
    $stmt->execute();
    $result = $stmt->get_result();
    // Se obteve resultado, retorna a quantidade disponível
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['Qtdade_Restante'];
    } else {
        return 0;
    }
}


// Função que atualiza a quantidade restante no saldo um produto
function atualizaQuantidadeRestante($conn_ext, $idProdutoMov, $quantidadeAtualizada, $quantidadeAnterior, $isRemoved = false)
{
    // Calcula a diferença de quantidade
    $deltaQuantidade = $quantidadeAnterior - $quantidadeAtualizada;

    // Se o produto foi removido, o deltaQuantidade deve ser igual à quantidade anterior.
    // Isso porque ao remover um produto, a quantidade restante deve ser aumentada pela quantidade que o produto tinha antes de ser removido.
    if ($isRemoved) {
        $deltaQuantidade = $quantidadeAnterior;
    }

    // Prepara e executa a query para atualizar a quantidade restante no saldo de produtos
    $sql = "UPDATE produtos_mov SET Qtdade_Restante = Qtdade_Restante + ? WHERE Id = ? ORDER BY Id DESC LIMIT 1";
    $stmt = $conn_ext->prepare($sql);
    $stmt->bind_param("di", $deltaQuantidade, $idProdutoMov);
    $stmt->execute();
}




// Função que obtém a quantidade de um produto
function getQuantidadeProduto($conn_ext, $id)
{
    // Prepara a consulta SQL para selecionar a quantidade do produto
    $sql = "SELECT Qtdade FROM srv_cont_os_servico_padrao WHERE Id = ?";
    $stmt = $conn_ext->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    // Se obteve resultado, retorna a quantidade
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['Qtdade'];
    } else {
        return 0;
    }
}

// Função que insere ou atualiza um produto na tabela de serviços
function insereOuAtualizaProduto($conn_ext, $id, $servicoId, $produtoId, $idProdutoMov, $quantidade, $terceiro_posse, $idSrv, $idOs, &$response)
{
    // Inicializa a quantidade anterior como 0
    $quantidadeAnterior = 0;

    // Verifica se a quantidade requisitada é maior que a quantidade disponível
    $quantidadeDisponivel = quantidadeDisponivel($conn_ext, $produtoId);
    if ($quantidade > $quantidadeDisponivel) {
        $response['message'] = 'Quantidade requisitada excede a quantidade disponível.';
        return null;
    }

    // Verifica se o produto existe. Se sim, atualiza
    if ($id !== null && produtoExiste($conn_ext, $id)) {
        $quantidadeAnterior = getQuantidadeProduto($conn_ext, $id);
        $sql = "UPDATE srv_cont_os_servico_padrao SET Qtdade = ?, terceiro_posse = ? WHERE Id = ?";
        $stmt = $conn_ext->prepare($sql);
        $stmt->bind_param("dsi", $quantidade, $terceiro_posse, $id);
        $stmt->execute();
    } else {
        // Se o produto não existe, insere um novo registro
        $sql = "INSERT INTO srv_cont_os_servico_padrao (id_SRV, id_OS, Id_servico, id_produto, Qtdade, Id_produto_mov, terceiro_posse) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn_ext->prepare($sql);
        $stmt->bind_param("iiidsid", $idSrv, $idOs, $servicoId, $produtoId, $quantidade, $idProdutoMov, $terceiro_posse);
        $stmt->execute();
        $id = $conn_ext->insert_id;
    }

    // Atualiza a quantidade restante do produto
    atualizaQuantidadeRestante($conn_ext, $idProdutoMov, $quantidade, $quantidadeAnterior);

    // Retorna o ID do produto inserido ou atualizado
    return $id;
}

// Função que verifica se um produto existe na tabela de serviços
function produtoExiste($conn_ext, $id)
{
    // Prepara a consulta SQL para verificar se o produto existe
    $sql = "SELECT * FROM srv_cont_os_servico_padrao WHERE Id = ?";
    $stmt = $conn_ext->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    // Retorna verdadeiro se o produto existe, falso caso contrário
    return $result && $result->num_rows > 0;
}

// Função que deleta um produto na tabela de serviços
function deletaProduto($conn_ext, $ids, $idSrv, $idOs, $servicoId, &$response)
{
    foreach ($ids as $id) {
        $sql = "DELETE FROM srv_cont_os_servico_padrao WHERE id_OS = ? AND id_SRV = ? AND Id = ?";
        $stmt = $conn_ext->prepare($sql);
        $stmt->bind_param("iii", $idOs, $idSrv, $id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Produto removido com sucesso.';
        } else {
            $response['message'] = 'Erro ao remover o produto da tabela produtos_mov.';
        }
    }
}





/*
// Configura o cabeçalho da resposta como JSON
header('Content-Type: application/json');

// Codifica a resposta como JSON e a envia
echo json_encode($response);
*/
