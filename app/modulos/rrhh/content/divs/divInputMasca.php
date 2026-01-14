<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    if($_POST["tipoContacto"] == "") {
        echo '<i class="fas fa-address-book trailing"></i>
        <input type="text" id="contactoPersona" class="form-control masked" name="contactoPersona" disabled />
        <label class="form-label" for="nombreContacto">Contacto</label>';
    } else {
        $dataTipoCon = $cloud->row("
                SELECT formatoContacto FROM cat_tipos_contacto WHERE flgDelete = 0 AND tipoContactoId = ?
            ", [$_POST["tipoContacto"]]);

        if ($dataTipoCon->formatoContacto == "email"){
            $mascara = "";
            $type = "email";
        } else{
            $mascara = $dataTipoCon->formatoContacto;
            $type = "text";
        }

        echo '<i class="fas fa-address-book trailing"></i>
            <input type="'. $type .'" id="contactoPersona" class="form-control contactoSucursal masked" name="contactoPersona" data-mask="'. $mascara .'" required />
            <label class="form-label" for="contactoPersona">Contacto</label>';

?>

        <script>
            Maska.create('#frmModal .masked');
        </script>
<?php 
    }
?>