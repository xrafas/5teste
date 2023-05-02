<!doctype html>
<html>

<head>
    <meta charset="utf-8">

    <title>Ambienthal</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

</head>


<style type="text/css">
    body {
        background-color: #CCFBD5;
    }
</style>

<body>
    <h1><img src="http://ambienthal.mgnettecnologiaweb.com.br/Content/images/logAmbienthal.png">Ordem de Serviço</h1>
    <hr>
    </hr>
    <ul>





        <div>
            <label>Cliente (Razão Social):</label>
            <input type="text" id="cliente" name="cliente" value="">

            <label>Contrato N°:</label>
            <input type="text" id="contrato" name="contrato" value="">

            <br>

            <label>OS N°:</label>
            <input type="text" id="os" name="os" value="">
            
            <label>Data de lançamento:</label>
            <input type="date" id="data_lancamento" name="data_lancamento" value="">

            <br>

            <label>Data e Hora do Serviço:</label>
            <input type="date" id="data_servico" name="data_servico" value="">
            <input type="time" id="hora_servico" name="hora_servico" value="">

            <label>Data Venc. Garantia:</label>
            <input type="date" id="data_garantia" name="data_garantia" value="">

            <br>

            <label>Valor do Serviço:</label>
            <input type="text" id="valor" name="valor" value="Não disponível">

        </div>

        <hr>
        <div>

            <label>Descrição Area Interna:</label>
            <br>
            <textarea rows="2" cols="30">
												</textarea>

            <br>
            <label>Descrição Area Externa:</label>
            <br>
            <textarea rows="2" cols="30">
												</textarea>

            <br>

            <label>Observação:</label>
            <br>
            <textarea rows="4" cols="80">
												</textarea>

        </div>
        </hr>
        <hr>

        <label><b>Tipo de OS:</b></label>
    

   

        <br>


       
            <label><b>Tipo:</b></label>
            <select name="tipo" placeholder="tipo:">
                <option value=""></option>
                <option value="CONTROLE">Controle de Praga</option>
                <option value="LIMPEZA">Limpeza de Ar Condicionado</option>
                <option value="COLETA">Coleta de Lixo</option>
            </select>






        
        <div id="result"></div>
        </div>
        <br>



        <script>
            $(document).ready(function() {
                $('select[name="tipo"]').click(function() {
                    var tipo = $(this).val();
                    $.ajax({
                        url: "fab_insert_1.php",
                        method: "POST",
                        data: {
                            tipo: tipo
                        },
                        success: function(data) {
                            $('#result').html(data);
                        }
                    });
                });
            });
        </script>


        </hr>


        <hr>
        <div>



            <label><b>Materiais Utilizados:</b></label>
            <br>
            <label>Seleção de Material:</label>
            <select name="materiais" placeholder="listagem">
                <option value=""></option>
                <option value="material1">Material de exemplo 1</option>
                <option value="material2">Material de exemplo 2</option>
                <option value="material3">Material de exemplo 3</option>
                <option value="material4">Material de exemplo 4</option>
                <option value="material5">Material de exemplo 5</option>
                <option value="material6">Material de exemplo 6</option>
            </select>
            <input type="button" name="add" id="add" value='Adicionar Selecionado'>

            <br>

            <label>Utilizados:</label>
            <br>
            <label>Material:</label>
            <input type="text" id="material" name="material" value="Material de exemplo 2">
            <label>Qtd:</label>
            <input type="number" id="qtd" name="qtd" value="3">
            <br>
            <label>Material:</label>
            <input type="text" id="material" name="material" value="Material de exemplo 3">
            <label>Qtd:</label>
            <input type="number" id="qtd" name="qtd" value="1">
            <br><label>Material:</label>
            <input type="text" id="material" name="material" value="Material de exemplo 4">
            <label>Qtd:</label>
            <input type="number" id="qtd" name="qtd" value="2">
            <br>


            </hr>
        </div>


        <hr>
        <div>
            <label><b>Status do Serviço:</b></label>
            <br>

            <label>Situação:</label>
            <input type="text" name="situacao" id="situacao" value='PARADA'>

            <input type="button" name="iniciar" id="iniciar" value='Iniciar'>
            <input type="button" name="finalizar" id="finalizar" value='Finalizar'>

            <br>


            <label>Data e Hora do Início da OS:</label>
            <input type="datetime" id="data_servico" name="data_servico" value="01/01/2022 10:30">


            <label>-</label>
            <input type="text" name="colaborador_inicial" id="colaborador_inicial" value="João">

            <br>

            <label>Data e Hora do Término da OS:</label>
            <input type="datetime" id="data_servico" name="data_servico" value="">


            <label>-</label>
            <input type="text" name="colaborador_final" id="colaborador_final" value=''>




            <hr>
        </div>
        <div>
            <label>Paradas da OS:</label>



            <input type="button" name="parada" id="parada" value='Parara'>
            <input type="button" name="reiniciar" id="reiniciar" value='Reiniciar'>
            <br><br>
            <label>Data e Hora:</label>
            <input type="datetime" id="data_servico" name="data_servico" value="01/01/2022 15:00">

            <label>-</label>
            <input type="text" name="colaborador_parada" id="colaborador_parada" value='João'>


            <label>Motivo:</label>
            <input type="text" name="motivo" id="motivo" value='Teste'>
            <br><br>


            </hr>
        </div>

    </ul>
</body>

</html>