<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
?>
<input type="hidden" name="extension" value="pdf">
<input type="hidden" name="file" value="inventario-general">
<div id="divReporte"></div>

<script>
    $(document).ready(function () {
        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
        asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>reportes",
            $("#frmModal").serialize(),
            function (data) {
                button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                $("#divReporte").html(data);
            }
        );
    });

</script>