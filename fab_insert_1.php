<?php

if ($_POST["tipo"] === 'CONTROLE') {

?>


<label>Vetores:</label>
   <select name="vetores" placeholder="Vetores:">
   <option value=""></option>
   <option value="RATO">Rato</option>
     <option value="BARATA">Barata</option>
     <option value="FORMIGA">Formiga</option>

   </select>



            
        </div>
        <div id="result2"></div>
        </div>
        <br>



<script>
    $(document).ready(function() {
        $('select[name="vetores"]').click(function() {
            var tipo2 = $(this).val();
            $.ajax({
                url: "fab_insert_2.php",
                method: "POST",
                data: {
                    tipo2: tipo2
                },
                success: function(data) {
                    $('#result2').html(data);
                }
            });
        });
    });
</script>




<?php
}

if ($_POST["tipo"] === 'LIMPEZA') {

?>

 <label>Limpar?</label>

<?php
}

if ($_POST["tipo"] === 'COLETA') {

?>

 <label>Coletar?</label>


<?php
}



?>