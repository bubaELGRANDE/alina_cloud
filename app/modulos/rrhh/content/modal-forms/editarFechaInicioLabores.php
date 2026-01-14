<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

if (isset($_POST["personaId"])) {
    $dataPersona = $cloud->row("SELECT fechaInicioLabores FROM th_personas WHERE personaId = ? AND flgDelete = ?", [$_POST["personaId"], 0]);
}

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="fecha-inicio-labores">
<input type="hidden" name="personaId" value="<?= $_POST["personaId"] ?? 0 ?>">
<div class="row">
    <div class="col-md-6">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar-check trailing"></i>
            <input type="date" id="fechaContratacionActual" class="form-control" disabled
                value="<?= $dataPersona && $dataPersona->fechaInicioLabores ? date('Y-m-d', strtotime($dataPersona->fechaInicioLabores)) : '' ?>" />
            <label class="form-label" for="fechaContratacionActual"> Fecha de contratación (Actual)</label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-outline mb-4 input-daterange">
            <input type="date" id="fechaContratacion" class="form-control masked" name="fechaInicioLabores" required
                value="<?= $dataExpediente && $dataExpediente->fechaInicio ? date('Y-m-d', strtotime($dataExpediente->fechaInicio)) : '' ?>" />
            <label class="form-label" for="fechaContratacion"> Fecha de contratación (Nueva) </label>
        </div>
    </div>
</div>
<script>

    $(document).ready(function () {
        $("#frmModal").validate({
            submitHandler: function (form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    $("#frmModal").serialize(),
                    function (data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if (data == "success") {
                            $("#modal-container").modal("hide");
                            $("#tblExpedientesActivos").DataTable().ajax.reload(null, false);
                            $("#tblExpedientesPendientes").DataTable().ajax.reload(null, false);
                        } else {
                            mensaje("Aviso:", data, "warning");
                        }
                    }
                );
            }
        });
    });
</script>