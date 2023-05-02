<?php

if ($_POST["tipo5"] === '+') {

?>


<label>Isca:</label>
<input type="text" id="pi" name="pi" value="Isca Exemplo 1">
<input type="text" id="identificacao" name="identificacao" value="teste">
<label>Capturados:</label>
<input type="number" id="qtd" name="qtd" value="0">





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