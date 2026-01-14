<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    $tipoArea=$_POST["arrayFormData"];

        /* arrayFormData 
        Nuevo = nuevo ^ estudio 
        Editar = editar ^ prsHabilidadId ^ nombreCompleto ^ tipoHabilidad
    */
    $arrayFormData = explode("^", $_POST["arrayFormData"]);

    switch ($arrayFormData[1]){
        case "estudio":
            $label      = "Nombre del área de estudio";
            $txtSuccess = "Área de estudio creada con éxito";
        break;
        case "experiencia":
            $label      = "Nombre del área de experiencia";
            $txtSuccess = "Área de experiencia creada con éxito";
        break;
        case "software":
            $label      = "Nombre del software";
            $txtSuccess = "Software creado con éxito";
        break;
        case "herraEqu":
            $label      = "Nombre de la herramienta o equipo";
            $txtSuccess = "Herramienta/Equipo creada con éxito";
        break;
        default:
            $label      = "Nombre";
            $txtSuccess = "";
        break;
    }

if ($arrayFormData[0] == "nuevo") {
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="<?php echo $arrayFormData[1]; ?>">
<div class="row justify-content-md-center">
    <div class="col-8">
        <div class="form-outline">
            <i class="fas fa-tag trailing"></i>
            <input type="text" id="nombreArea" class="form-control" name="nombreArea" required />
            <label class="form-label" for="nombreArea"><?php echo $label; ?></label>
        </div>
    </div> 
</div>

<script>
    $("#frmModal").validate({
        
        submitHandler: function(form) {
            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
            asyncDoDataReturn(
                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                $("#frmModal").serialize(),
                function(data) {
                    button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                    if(data == "success") {
                        mensaje(
                            "Operación completada:",
                            '<?php echo $txtSuccess; ?>',
                            "success"
                        );
                        var tablaUpd = $("#operation").val();
                        if (tablaUpd == "estudio"){
                            var idTabla = "#tblEstudio";
                        } else if (tablaUpd == "experiencia") {
                            var idTabla = "#tblExperiencia";
                        } else if (tablaUpd == "software") {
                            var idTabla = "#tbProgramas";
                        } else if (tablaUpd == "herraEqu") {
                            var idTabla = "#tbHerraEq";
                        }
                        $(idTabla).DataTable().ajax.reload(null, false);
                        $('#modal-container').modal("hide");
                    } else {
                        mensaje(
                            "Aviso:",
                            data,
                            "warning"
                        );
                    }
                }
            );
        }
    });
</script>
<?php } else {   

switch ($arrayFormData[1]){
    case "estudio":
        $dataEditArea = $cloud->row("SELECT areaEstudio AS area FROM cat_personas_ar_estudio WHERE  prsArEstudioId = ? ", [$arrayFormData[2]]);
        $label        = "Nombre del área de estudio";
        $txtSuccess   = "Área de estudio actualizada con éxito";
        break;
    case "experiencia":
        $dataEditArea = $cloud->row("SELECT areaExperiencia AS area FROM cat_personas_ar_experiencia WHERE prsArExperienciaId = ?", [$arrayFormData[2]]);
        $label        = "Nombre del área de experiencia";
        $txtSuccess   = "Área de experiencia actualizada con éxito";
        break;
    case "software":
        $dataEditArea = $cloud->row("SELECT nombreSoftware AS area FROM cat_personas_software WHERE prsSoftwareId  = ?", [$arrayFormData[2]]);
        $label        = "Nombre del software";
        $txtSuccess   = "Software creado con éxito";
        break;
    case "herraEqu":
        $dataEditArea = $cloud->row("SELECT nombreHerrEquipo AS area FROM cat_personas_herr_equipos WHERE prsHerrEquipoId  = ?", [$arrayFormData[2]]);
        $label        = "Nombre de la herramienta o equipo";
        $txtSuccess   = "Herramienta/Equipo actualizada con éxito";
        break;
        
}
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="<?php echo $arrayFormData[1]; ?>">
<input type="hidden" id="idArea" name="idArea" value="<?php echo $arrayFormData[2]; ?>">
<div class="row justify-content-md-center">
    <div class="col-8">
        <div class="form-outline">
            <i class="fas fa-tag trailing"></i>
            <input type="text" id="nombreArea" class="form-control" name="nombreArea" value="<?php echo $dataEditArea->area; ?>" required />
            <label class="form-label" for="nombreArea"><?php echo $label; ?></label>
        </div>
    </div> 
</div>
<script>
$(document).ready(function() {
    $("#frmModal").validate({
        submitHandler: function(form) {
            button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
            asyncDoDataReturn(
                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                $("#frmModal").serialize(),
                function(data) {
                    button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                    if(data == "success") {
                        mensaje(
                            "Operación completada:", 
                            "<?php echo $txtSuccess; ?>", 
                            "success"
                        );
                        var tablaUpd = $("#operation").val();
                        if (tablaUpd == "estudio"){
                            var idTabla = "#tblEstudio";
                        } else if (tablaUpd == "experiencia") {
                            var idTabla = "#tblExperiencia";
                        } else if (tablaUpd == "software") {
                            var idTabla = "#tbProgramas";
                        } else if (tablaUpd == "herraEqu") {
                            var idTabla = "#tbHerraEq";
                        }
                        $(idTabla).DataTable().ajax.reload(null, false);
                        $('#modal-container').modal("hide");
                    } else {
                        mensaje(
                            "Aviso:",
                            data,
                            "warning"
                        );
                    }
                }
            );
        }
    });
});

<?php 
    if($arrayFormData[0] == "editar") { 
    switch ($arrayFormData[1]){
        case "estudio": 
        $dataEditArea = $cloud->row("SELECT areaEstudio AS area FROM cat_personas_ar_estudio WHERE  prsArEstudioId = ? ", [$arrayFormData[2]]);
    ?>
        $("#modalTitle").html('Editar área de estudio: <?php echo $dataEditArea->area; ?>');
    <?php    
    break;
    case "experiencia":
        $dataEditArea = $cloud->row("SELECT areaExperiencia AS area FROM cat_personas_ar_experiencia WHERE prsArExperienciaId = ?", [$arrayFormData[2]]);
    ?>
        $("#modalTitle").html('Editar área de experiencia: <?php echo $dataEditArea->area; ?>');
    <?php
    break;
    case "software":
        $dataEditArea = $cloud->row("SELECT nombreSoftware AS area FROM cat_personas_software WHERE prsSoftwareId  = ?", [$arrayFormData[2]]);
    ?>
        $("#modalTitle").html('Editar nombre del software: <?php echo $dataEditArea->area; ?>');
    <?php
    break;
    case "herraEqu":
        $dataEditArea = $cloud->row("SELECT nombreHerrEquipo AS area FROM cat_personas_herr_equipos WHERE prsHerrEquipoId  = ?", [$arrayFormData[2]]);
    ?>
        $("#modalTitle").html('Editar de la herramienta o equipo: <?php echo $dataEditArea->area; ?>');
    <?php
    break;
    }
?>
    
<?php 
    } 
?>
</script>
<?php }?>