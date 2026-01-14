<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$dataAdjuntos = $cloud->rows("SELECT prsAdjuntoId, tipoPrsAdjunto, descripcionPrsAdjunto, urlPrsAdjunto FROM th_personas_adjuntos WHERE flgDelete=0 AND personaId = ?", [$_POST["personaId"]]);

$dataEstadoPersona = $cloud->row("
    SELECT
        estadoPersona
    FROM th_personas
    WHERE personaId = ?
",[$_POST["personaId"]]);

if (!empty($dataAdjuntos)){

    foreach ($dataAdjuntos as $adjunto) {
        $ext = pathinfo(strtolower($adjunto->urlPrsAdjunto), PATHINFO_EXTENSION);
        switch ($ext){
            case "pdf":
                $urlImagen = '../libraries/resources/images/icons/pdf.png';
                break;
            case "doc":
                $urlImagen = '../libraries/resources/images/icons/texto.png';
                break;
            case "docx":
                $urlImagen = '../libraries/resources/images/icons/texto.png';
                break;
            case "xls":
                $urlImagen = '../libraries/resources/images/icons/calculo.png';
                break;
            case "xlsx":
                $urlImagen = '../libraries/resources/images/icons/calculo.png';
                break;
            default:
                $urlImagen = '../libraries/resources/images/'. $adjunto->urlPrsAdjunto;
                break;
        }

?>
        <div class="item">
            <div class="card" onclick="verAdjuntoModal(<?php echo $adjunto->prsAdjuntoId;?>);">
                <img src="<?php echo $urlImagen;?>" class="card-img-top" alt="<?php echo $ext;?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $adjunto->tipoPrsAdjunto;?></h5>
                    <p class="card-text"><?php echo $adjunto->descripcionPrsAdjunto;?></p>
                </div>
            </div>
            <?php 
                if($dataEstadoPersona->estadoPersona == "Inactivo" || $adjunto->tipoPrsAdjunto == "Baja de empleado") {
            ?>
                    <a role="button" class="delItem badge rounded-pill bg-danger" style="cursor: not-allowed;">
                        <i class="fas fa-times"></i>
                    </a>
            <?php
                } else {
            ?>
                    <a role="button" class="delItem badge rounded-pill bg-danger" onclick="delAdjunto('<?php echo $adjunto->prsAdjuntoId;?>', '<?php echo $_POST["personaId"] ?>');">
                        <i class="fas fa-times"></i>
                    </a>
            <?php
                }
            ?>
        </div>
<?php   
    } // cierre foreach
} else {
    echo '<p style="width:100%; text-align:center;">No se encontraron resultados</p>';
}
?>