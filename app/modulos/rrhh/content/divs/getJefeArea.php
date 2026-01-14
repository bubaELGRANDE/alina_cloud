<?php
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();


    $dataEmpRamas = $cloud->rows(" SELECT
    expor.expedienteOrganigramaId,
    CONCAT(
        IFNULL(per.apellido1, '-'),
        ' ',
        IFNULL(per.apellido2, '-'),
        ', ',
        IFNULL(per.nombre1, '-'),
        ' ',
        IFNULL(per.nombre2, '-')
    ) AS nombreCompleto,
    pc.cargoPersona AS cargoPersona,
    ram.organigramaRama,
    ram.organigramaRamaId,
    exp.prsExpedienteId AS prsExpedienteId, 
    exp.personaId AS personaId, 
    exp.prsCargoId AS prsCargoId

    FROM th_expediente_organigrama expor
    JOIN cat_organigrama_ramas ram ON ram.organigramaRamaId = expor.organigramaRamaId
    JOIN th_expediente_personas exp ON exp.prsExpedienteId = expor.prsExpedienteId
    JOIN cat_personas_cargos pc ON pc.prsCargoId = exp.prsCargoId
    JOIN th_personas per ON per.personaId = exp.personaId
        
    WHERE expor.flgDelete = '0' AND exp.estadoExpediente = 'Activo' AND expor.organigramaRamaId = ? AND expor.flgJefe = 'Jefe'
            ORDER BY prsExpedienteId DESC",[$_POST["ramaId"]]);

    $n = 0;

    if (!empty($dataEmpRamas) && $_POST["expOrganigramaId"] == 0){
        foreach ($dataEmpRamas as $organigrama) {
            $n += 1;
            echo '<span class="mb-2 h5"><b>Jefe de área:</b> ' .$organigrama->nombreCompleto . '</span>
            <button id="submitJefe" type="button" class="btn btn-primary btn-sm ttip" onclick="getJefe(`'.$_POST["ramaId"].'`, `'.$organigrama->expedienteOrganigramaId.'`)">
            <i class="fas fa-edit"></i>
            <span class="ttiptext">Editar jefe de área</span>
            </button>
            <hr>';
        }

    } else {
?>
<form id="jefeForm">
    <input type="hidden" id="typeOperation" name="typeOperation" value="update">
    <input type="hidden" id="operation" name="operation" value="jefe-organigrama">
    <input type="hidden" id="rama" name="rama" value="<?php echo $_POST["ramaId"]; ?>">
    <input type="hidden" id="expedienteOrganigrama" name="expedienteOrganigrama" value="<?php echo $_POST["expOrganigramaId"]; ?>">
        <div class="row">
            <div class="col-md-4">
                <div class="form-select-control mb-4">
                    <select id="personaJefe" name="personaJefe" style="width:100%;" required>
                        <option></option>
                        <?php 
                            $dataPersonas = $cloud->rows("
                            SELECT
                            exp.prsExpedienteId,
                                per.personaId, 
                                CONCAT(
                                    IFNULL(per.apellido1, '-'),
                                    ' ',
                                    IFNULL(per.apellido2, '-'),
                                    ', ',
                                    IFNULL(per.nombre1, '-'),
                                    ' ',
                                    IFNULL(per.nombre2, '-')
                                ) AS nombreCompleto
                            FROM th_expediente_personas exp
                            JOIN th_personas per ON exp.personaId = per.personaId
                            WHERE exp.flgDelete = '0' AND exp.estadoExpediente = 'Activo' AND exp.prsExpedienteId IN (
                                SELECT
                                    prsExpedienteId
                                FROM th_expediente_organigrama
                                WHERE flgDelete = '0' AND organigramaRamaId = ?
                            )
                            ORDER BY apellido1, apellido2, nombre1, nombre2
                            ", [$_POST["ramaId"]]);
                            foreach ($dataPersonas as $dataPersonas) {
                                echo '<option value="'.$dataPersonas->prsExpedienteId.'">'.$dataPersonas->nombreCompleto.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <button id="submitJefe" type="submit" class="btn btn-primary btn-sm ttip">
                <i class="fas fa-save"></i>
                <span class="ttiptext">Guardar jefe de área</span>
                </button>
            </div>
        </div>
</form>


    <script>
        $(document).ready(function() {
            $("#personaJefe").select2({
                placeholder: "Seleccionar jefe de área",
                //dropdownParent: $('#form-select-control'),
                allowClear: true
            });

            $("#jefeForm").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#jefeForm").serialize(),
                    function(data) {
                        button_icons("submitJefe", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                "Empleados agregados exitosamente.",
                                "success"
                            );
                            $('#tblOrganigramaRama').DataTable().ajax.reload(null, false);
                            getJefe(<?php echo $_POST["ramaId"]; ?>, 0);
                            //$('#modal-container').modal("hide");
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
<?php
}