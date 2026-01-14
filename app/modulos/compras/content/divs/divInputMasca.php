<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

if(!isset($_POST["tipoContacto"])) {
    echo '<i class="fas fa-address-book trailing"></i>
    <input type="text" id="contactoUbicacion" class="form-control contactoUbicacion masked" name="contactoUbicacion" disabled />
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
        <input type="'. $type .'" id="contactoUbicacion" class="form-control contactoUbicacion masked" name="contactoUbicacion" data-mask="'. $mascara .'" required />
        <label class="form-label" for="nombreContacto">Contacto</label>';
?>

    <script>
        Maska.create('#frmModal .masked');
    </script>
<?php 
}
?>