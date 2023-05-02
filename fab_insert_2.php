<?php

if ($_POST["tipo2"] === 'RATO') {

?>

    </select>

    <label>Modo de Aplicação:</label>
    <select name="aplicacao" placeholder="Modo de Aplicação">
        <option value="ISCAS">Iscas c/ Porta Iscas</option>
    </select>
    <br>
    <label>Setor:</label>
    <select name="setor" placeholder="Setor">
        <option value=""></option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
    </select>



    </div>
    <div id="result3"></div>
    </div>
    <br>



    <script>
        $(document).ready(function() {
            $('select[name="setor"]').click(function() {
                var tipo3 = $(this).val();
                $.ajax({
                    url: "fab_insert_3.php",
                    method: "POST",
                    data: {
                        tipo3: tipo3
                    },
                    success: function(data) {
                        $('#result3').html(data);
                    }
                });
            });
        });
    </script>




<?php
}



if ($_POST["tipo2"] === 'BARATA') {

?>

    </select>

    <label>Modo de Aplicação:</label>
    <select name="aplicacao" placeholder="Modo de Aplicação">
        <option value="PULVERIZACAO">Pulverização</option>
        <option value="ISCAS">Iscas c/ Porta Iscas</option>
    </select>
    <br>
    <label>Setor:</label>
    <select name="setor" placeholder="Setor">
        <option value=""></option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
    </select>



    </div>
    <div id="result3"></div>
    </div>
    <br>

<?php

}



if ($_POST["tipo2"] === 'FORMIGA') {

    ?>
    
        </select>
    
        <label>Modo de Aplicação:</label>
        <select name="aplicacao" placeholder="Modo de Aplicação">
            <option value="PULVERIZACAO">Pulverização</option>
            <option value="ISCAS">Iscas c/ Porta Iscas</option>
        </select>
        <br>
        <label>Setor:</label>
        <select name="setor" placeholder="Setor">
            <option value=""></option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select>
    
    
    
        </div>
        <div id="result3"></div>
        </div>
        <br>
    
    <?php
    
    }




?>


