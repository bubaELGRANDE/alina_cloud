<?php 
    @session_start();
?>
<p align="justify">Estimado(a) <?php echo $_SESSION["nombrePersona"]; ?>, Recursos Humanos realizó una actualización en su expediente, por lo que no se mostrarán los menús dentro de Cloud <b>temporalmente</b>. Por favor, comuníquese con su jefe inmediato para restablecer sus accesos.</p>
<script>
    $(document).ready(function() {
        $("#btnModalCancelAction").click(function() {
            $("#modal-container").modal("hide");
        });
    });
</script>