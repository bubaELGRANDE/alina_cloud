<?php 
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$expedienteCapacitacionId = 0;

if ($_POST['typeOperation'] == "update") {
    $expedienteCapacitacionId = $_POST['expedienteCapacitacionId'];

    $dataCapacitaciones = $cloud->row("
        SELECT
            descripcionCapacitacion,
            nombreOrganizador,
            tipoFormacion,
            tipoModalidad,
            fechaIniCapacitacion,
            fechaFinCapacitacion,
            duracionCapacitacion,
            costoInsaforp,
            costoalina
        FROM th_expediente_capacitaciones
        WHERE flgDelete = ? AND expedienteCapacitacionId = ?
    ", [0, $_POST['expedienteCapacitacionId']]);

       //$txtSuccess = "Capacitación actualizada con éxito.";
} else {
       //$txtSuccess = "Capacitación agregada con éxito.";
}
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="capacitaciones">
<input type="hidden" id="expedienteCapacitacionId" name="expedienteCapacitacionId" value="<?php echo $expedienteCapacitacionId;?>">

<div id="divSelectEmpleado" class="form-select-control mb-4">
    <select id="expedienteId" name="expedienteId[]" multiple="multiple" style="width:100%;" required>
        <option></option>
        <?php
        $dataPersonasCapacitacion = $cloud->rows("
            SELECT
                prsExpedienteId,
                personaId,
                nombreCompleto
            FROM view_expedientes
            ORDER BY nombreCompleto
        ");
        foreach ($dataPersonasCapacitacion as $dataPersonaCapacitacion) {
            echo "<option value='$dataPersonaCapacitacion->prsExpedienteId'>$dataPersonaCapacitacion->nombreCompleto</option>";
        }
        ?>
    </select>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fa fa-list trailing"></i>
            <textarea id="capacitacion" class="form-control" name="capacitacion" required></textarea>
            <label class="form-label" for="capacitacion">Capacitación</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4 input-daterange">
            <input type="date" id="fechaInicio" class="form-control" name="fechaInicio" value="<?php echo date('Y-m-d'); ?>" required/>
            <label class="form-label" for="fechaInicio">Fecha inicio</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4 input-daterange">
            <input type="date" id="fechaFin" class="form-control" name="fechaFin" value="<?php echo date('Y-m-d'); ?>" required/>
            <label class="form-label" for="fechaFin">Fecha Fin</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fa fa-list trailing"></i>
            <input type="text" id="organizador" class="form-control" name="organizador" required/>
            <label class="form-label" for="organizador">Organizador</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fa fa-list-ol trailing"></i>
            <input type="number" id="duracion" class="form-control" name="duracion" required/>
            <label class="form-label" for="duracion">Duración (Horas)</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-select-control mb-4">
            <select name="selectModalidad" id="selectModalidad" style="width:100%;" required>
                <option></option>
                <option value="Interna">Interna</option>
                <option value="Externa">Externa</option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-select-control mb-4">
            <select name="selectTipoFormacion" id="selectTipoFormacion" style="width:100%;" required>
                <option></option>
                <option value="Conferencia">Conferencia</option>
                <option value="Charla">Charla</option>
                <option value="Diplomado">Diplomado</option>
                <option value="Entrenamiento">Entrenamiento</option>
                <option value="Seminario">Seminario</option>
                <option value="Taller">Taller</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fa fa-dollar-sign trailing"></i>
            <input type="number" id="costoInsaforp" class="form-control" name="costoInsaforp" step="0.01" min="0.00" required/>
            <label class="form-label" for="costoInsaforp">Costo INSAFORP</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fa fa-dollar-sign trailing"></i>
            <input type="number" id="costoEmpresa" class="form-control" name="costoEmpresa" step="0.01" min="0.00" required/>
            <label class="form-label" for="costoEmpresa">Costo empresa</label>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#expedienteId").select2({
            placeholder: "Empleado(s)",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#selectModalidad").select2({
            placeholder: "Modalidad",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });
        $("#selectTipoFormacion").select2({
            placeholder: "Tipo de formación",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if (data == "success") {
                            mensaje(
                                "Operación completada:",
                                `Capacitación ${$("#typeOperation").val() == 'insert' ? 'agregada' : 'actualizada'} con éxito.`,
                                "success"
                            );
                            $(`#tblCapacitaciones`).DataTable().ajax.reload(null, false);
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
        <?php 
            if ($_POST['typeOperation'] == "update") {
        ?>
                $("#divSelectEmpleado").hide();
                $("#typeOperation").val("update");
                $("#capacitacion").val(`<?php echo $dataCapacitaciones->descripcionCapacitacion; ?>`);
                $("#fechaInicio").val(`<?php echo $dataCapacitaciones->fechaIniCapacitacion; ?>`);
                $("#fechaFin").val(`<?php echo $dataCapacitaciones->fechaFinCapacitacion; ?>`);
                $("#organizador").val(`<?php echo $dataCapacitaciones->nombreOrganizador; ?>`);
                $("#duracion").val(`<?php echo $dataCapacitaciones->duracionCapacitacion; ?>`);
                $("#selectModalidad").val(`<?php echo $dataCapacitaciones->tipoModalidad; ?>`).trigger('change');
                $("#selectTipoFormacion").val(`<?php echo $dataCapacitaciones->tipoFormacion; ?>`).trigger('change');
                $("#costoInsaforp").val(`<?php echo $dataCapacitaciones->costoInsaforp; ?>`);
                $("#costoEmpresa").val(`<?php echo $dataCapacitaciones->costoalina; ?>`);
        <?php
            } else {
                // Ya se especificó el título de la modal
            }
        ?>
    });
</script>
