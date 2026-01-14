<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    $dataAdjunto = $cloud->row("SELECT personaId, tipoPrsAdjunto, descripcionPrsAdjunto, urlPrsAdjunto 
                                FROM th_personas_adjuntos WHERE prsAdjuntoId = ?", 
                                [$_POST["prsAdjuntoId"]]);

    $ext = pathinfo(strtolower($dataAdjunto->urlPrsAdjunto), PATHINFO_EXTENSION);

    

    $defaultOutput = '
        <h4 class="text-center mt-5">
            <i class="far fa-eye-slash fa-2x"></i><br>
            Vista previa no disponible
        </h4>
    ';
    switch ($ext){
        case "pdf":
            //$urlImagen = '../libraries/resources/images/icons/pdf.png';
            $urlImagen = '../libraries/resources/images/'. $dataAdjunto->urlPrsAdjunto;
            $altura = 'style="height: 80vh; width: 100%;"';
            $output = '<object class="img-fluid"  data="'.$urlImagen.'" '.$altura.' >';
            break;
        case "doc":
            $output = $defaultOutput;
            break;
        case "docx":
            $output = $defaultOutput;
            break;
        case "xls":
            $output = $defaultOutput;
            break;
        case "xlsx":
            $output = $defaultOutput;
            break;
        default:
            $urlImagen = '../libraries/resources/images/'. $dataAdjunto->urlPrsAdjunto;
            $altura ='style="margin: 0 auto; display: block;"';
            $output = '<object class="img-fluid"  data="'.$urlImagen.'" '.$altura.' >';

            break;
    }
?>

<div class="row">
    <div class="col-md-3">
        <div id="info-adjunto" class="mb-4" >
            <b><i class="fas fa-file-alt"></i> Tipo de adjunto:</b><br> 
            <?php echo $dataAdjunto->tipoPrsAdjunto; ?> <br><br>
            <b><i class="fas fa-edit"></i> Descripción:</b><br> 
            <?php echo $dataAdjunto->descripcionPrsAdjunto; ?>
        </div>
        <a href="<?php echo '../libraries/resources/images/'. $dataAdjunto->urlPrsAdjunto; ?>" class="btn btn-success btn-block mb-2" download>
        <i class="fas fa-download"></i> Descargar adjunto</a>
    </div>
    <div class="col-md-9">
        <!-- object class="img-fluid"  data="<?php echo $urlImagen;?>" <?php echo $altura; ?> -->
        <?php echo $output; ?>
    </div>
</div>
