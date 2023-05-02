<?php

if ($_POST["tipo4"] === '+') {

?>

<label>Isca:</label>
<select name="isca" placeholder="Isca">
    <option value="isca">Isca Exemplo 1</option> 
    <option value="isca">Isca Exemplo 2</option> 
    <option value="isca">Isca Exemplo 3</option> 
</select>
<label>Identificação:</label>
    <input type="text" id="identificacao" name="identificacao" value="">
    <input type="button" name="addi" id="addi" value='+'>
    <br>

    <hr>
    <label><b>Aplicados:</b></label>
    <br>

   
<label>Porta Isca:</label>
<input type="text" id="pi" name="pi" value="P/I Exemplo 1">
<input type="text" id="identificacao" name="identificacao" value="teste">
<br>



    </div>
    <div id="result5"></div>
    </div>
   


    
    <script>
        $(document).ready(function() {
            $('input[name="addi"]').click(function() {
                var tipo5 = $(this).val();
                $.ajax({
                    url: "fab_insert_5.php",
                    method: "POST",
                    data: {
                        tipo5: tipo5
                    },
                    success: function(data) {
                        $('#result5').html(data);
                    }
                });
            });
        });
    </script>






<?php
}