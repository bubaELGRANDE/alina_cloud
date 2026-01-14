<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();
$arrayFormData = explode("^", $_POST["arrayFormData"]);

if (!empty($arrayFormData[1])) {
    $dataEsp = $cloud->row("SELECT 
        catProdEspecificacionId, tipoEspecificacion, nombreProdEspecificacion, tipoMagnitud 
        FROM cat_productos_especificaciones 
        WHERE flgDelete = 0 AND  catProdEspecificacionId = ?", [$arrayFormData[1]]);
}

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $arrayFormData[0]; ?>">
<input type="hidden" id="operation" name="operation" value="especificacion">
<?php if ($arrayFormData[0] == "update") { ?>
    <input type="hidden" id="operation" name="especificacionId" value="<?php echo $arrayFormData[1]; ?>">
<?php } ?>
<div class="row">
    <div class="col-md-12">
        <div id="newEspecificaion">
            <div class="form-outline mb-4">
                <i class="fas fa-align-justify trailing"></i>
                <input type="text" id="nombreEsp" class="form-control" name="nombreEsp" required>
                <label class="form-label" for="nombreEsp">Nombre de especificación</label>
            </div>
            <div class="form-select-control mb-4">
                <select class="form-select tipoEspecificacion" id="tipoEspecificacionN" name="tipoEspecificacionN"
                    style="width:100%;" required>
                    <option></option>
                    <?php
                    $especificaciones = array("Especificación técnica", "Especificación fisica", "Equipamiento");
                    foreach ($especificaciones as $especificacion) {
                        echo '<option value="' . $especificacion . '">' . $especificacion . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-select-control mb-4">
                <select id="tipoMagnitud" name="tipoMagnitud" style="width:100%;" required>
                    <option></option>
                    <?php
                    $arrayOptions = array(
                        "Longitud",
                        "Masa",
                        "Tiempo",
                        "Velocidad",
                        "Aceleración",
                        "Fuerza",
                        "Material",
                        "Superficie",
                        "Volumen",
                        "Temperatura",
                        "Presión",
                        "Trabajo/Energía",
                        "Potencia",
                        "Carga Eléctrica",
                        "Potencial Eléctrico",
                        "Frecuencia",
                        "Conductancia Eléctrica",
                        "Intensidad de corriente eléctrica",
                        "Actividad Radiactiva",
                        "Resistencia eléctrica",
                        "Cantidad de sustancia",
                        "Intensidad luminosa",
                        "Carga Magnética",
                        "Flujo Magnético",
                        "Intensidad del Flujo Magnético",
                        "Flujo Luminoso",
                        "Capacidad eléctrica",
                        "Iluminancia",
                        "Radiación Ionizante",
                        "Dosis de Radiación"
                    );
                    foreach ($arrayOptions as $option) {
                        echo '<option value="' . $option . '">' . $option . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $(".tipoEspecificacion").select2({
            placeholder: "Tipo de especificación",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#tipoMagnitud").select2({
            placeholder: "Tipo de magnitud",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#tipoEspecificacionN").on("change", function () {
            var tipoEsp = $("#tipoEspecificacionN").val();

            if (tipoEsp == "Equipamiento") {
                $("#tipoMagnitud").prop("disabled", true);
            } else {
                $("#tipoMagnitud").prop("disabled", false);
            }
        });

        <?php if ($arrayFormData[0] == "update") { ?>
            $("#tipoEspecificacionN").val('<?php echo $dataEsp->tipoEspecificacion; ?>').trigger('change');
            $("#tipoMagnitud").val('<?php echo $dataEsp->tipoMagnitud; ?>').trigger('change');
            $("#nombreEsp").val('<?php echo $dataEsp->nombreProdEspecificacion; ?>');
        <?php } ?>

        $("#frmModal").validate({
            submitHandler: function (form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/",
                    $("#frmModal").serialize(),
                    function (data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if (data == "success") {
                            mensaje(
                                "Operación completada:",
                                "Especificación guardada correctamente.",
                                "success"
                            );
                            $("#tblEspecificaciones").DataTable().ajax.reload(null, false);
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
</script>