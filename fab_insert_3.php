<?php

if ($_POST["tipo3"] === '1') {

?>

    </select>

    <label>Porta Isca:</label>
    <select name="porta_isca" placeholder="Porta Isca">
        <option value="porta_isca">P/I Exemplo 1</option>
        <option value="porta_isca">P/I Exemplo 2</option>
        <option value="porta_isca">P/I Exemplo 3</option>
    </select>
    <label>Identificação:</label>
    <input type="text" id="identificacao" name="identificacao" value="">
    <input type="button" name="addpi" id="addpi" value='+'>
    <br>



    </div>
    <div id="result4"></div>
    </div>
    <br>


    
    <script>
        $(document).ready(function() {
            $('input[name="addpi"]').click(function() {
                var tipo4 = $(this).val();
                $.ajax({
                    url: "fab_insert_4.php",
                    method: "POST",
                    data: {
                        tipo4: tipo4
                    },
                    success: function(data) {
                        $('#result4').html(data);
                    }
                });
            });
        });
    </script>






<?php
}

if ($_POST["tipo3"] === '2') {

?>

    </select>

    <label>Porta Isca:</label>
    <select name="porta_isca" placeholder="Porta Isca">
        <option value="porta_isca">P/I Exemplo 1</option>
        <option value="porta_isca">P/I Exemplo 2</option>
        <option value="porta_isca">P/I Exemplo 3</option>
    </select>
    <label>Qtd:</label>
    <input type="number" id="qtd" name="qtd" value="3">
    <br>
    <label>Isca:</label>
    <select name="isca" placeholder="Isca">
        <option value="isca">Isca Exemplo 1</option>
        <option value="isca">Isca Exemplo 2</option>
        <option value="isca">Isca Exemplo 3</option>
    </select>

    <label>Qtd:</label>
    <input type="number" id="qtd" name="qtd" value="4">
    <br>

    <input type="button" name="registrar" id="registrar" value='Registrar'>



    </div>
    <div id="result3"></div>
    </div>
    <br>










    <script>
        /* não usar por enquanto talvez para registrar
    $(document).ready(function() {
        $('select[name="setor"]').click(function() {
            var tipo3 = $(this).val();
            $.ajax({
                url: "insert_3.php",
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
*/
    </script>




<?php
}




?>