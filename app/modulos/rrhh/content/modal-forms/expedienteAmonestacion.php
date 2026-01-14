<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    if($_POST["typeOperation"] == "update") {
        $dataAmonestacion = $cloud->row("
            SELECT
                per.personaId, 
                exp.prsExpedienteId,
                CONCAT(
                    IFNULL(per.apellido1, '-'),
                    ' ',
                    IFNULL(per.apellido2, '-'),
                    ', ',
                    IFNULL(per.nombre1, '-'),
                    ' ',
                    IFNULL(per.nombre2, '-')
                ) AS nombreCompleto,
                am.expedienteAmonestacionId,
                am.expedienteIdJefe,
                am.expedienteId,
                date_format(am.fhAmonestacion, '%d-%m-%Y')  as fhAmonestacion,
                am.tipoAmonestacion,
                am.suspension,
                am.totalDiasSuspension,
                am.causaFalta,
                am.descripcionFalta,
                am.consecuenciaFalta,
                am.descripcionConsecuencia,
                am.advertenciaSiguienteFalta,
                am.compromisoMejora,
                am.flgReincidencia,
                am.estadoAmonestacion,
                date_format(am.fechaVigenciaInicio, '%d-%m-%Y') as fechaVigenciaInicio,
                date_format(am.fechaVigenciaFin, '%d-%m-%Y') as fechaVigenciaFin,
                date_format(am.fechaSuspensionInicio, '%d-%m-%Y') as fechaSuspensionInicio,
                date_format(am.fechaSuspensionFin, '%d-%m-%Y') as fechaSuspensionFin
            FROM ((th_expediente_amonestaciones am
            JOIN th_expediente_personas exp ON am.expedienteId = exp.prsExpedienteId)
            JOIN th_personas per ON per.personaId = exp.personaId)
            WHERE am.flgDelete = ? and am.expedienteAmonestacionId = ?
        ", [0, $_POST["expedienteAmonestacionId"]]);

        $amonestacionID = $_POST["expedienteAmonestacionId"];
        $txtSuccess = "Se ha actualizado los datos de la amonestación.";
    } else {
        $amonestacionID = 0;
        $txtSuccess = "Se ha creado con éxito la amonestación.";
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $_POST['typeOperation'];?>">
<input type="hidden" id="amonestacionId" name="amonestacionId" value="<?php echo $amonestacionID; ?>">
<input type="hidden" id="operation" name="operation" value="amonestacion">

<div class="row">
    <div class="col-md-8">
        <div class="form-select-control mb-4">
            <select class="form-select" id="persona" name="persona" style="width:100%;" required>
                <option></option>
                <?php // falta join con expediente
                    $dataPersonas = $cloud->rows("
                        SELECT
                            pers.personaId as personaId, 
                            exp.prsExpedienteId as expedienteId,
                            CONCAT(
                                IFNULL(pers.apellido1, '-'),
                                ' ',
                                IFNULL(pers.apellido2, '-'),
                                ', ',
                                IFNULL(pers.nombre1, '-'),
                                ' ',
                                IFNULL(pers.nombre2, '-')
                            ) AS nombreCompleto
                        FROM th_personas pers
                        JOIN th_expediente_personas exp ON pers.personaId = exp.personaId
                        WHERE pers.prsTipoId = '1' AND pers.flgDelete = '0' AND pers.estadoPersona = 'Activo' AND exp.estadoExpediente = 'Activo' 
                        ORDER BY apellido1, apellido2, nombre1, nombre2
                    ");
                    foreach ($dataPersonas as $dataPersonas) {
                        echo '<option value="'.$dataPersonas->expedienteId.'">'.$dataPersonas->nombreCompleto.'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4 input-daterange">
            <i class="fas fa-calendar trailing"></i>
            <input type="date" id="fechaAmonestacion" class="form-control" name="fechaAmonestacion"  required />
            <label class="form-label" for="fechaAmonestacion">Fecha de amonestación</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-briefcase trailing"></i>
            <input type="text" id="cargo" class="form-control" name="cargo" readonly>
            <label id="labelCarrera" class="form-label" for="cargo">Cargo</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-outline mb-4">
            <i class="fas fa-building trailing"></i>
            <input type="text" id="depto" class="form-control" name="depto" readonly>
            <label id="labelCarrera" class="form-label" for="depto">Departamento</label>
        </div>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="tipoSancion" name="tipoSancion" style="width:100%;" required>
            <option></option>
            <?php 
                $tiposDeSancion = array("Verbal","Escrita","Verbal y Escrita");
                for ($i=0; $i < count($tiposDeSancion); $i++) { 
                    echo '<option value="'.$tiposDeSancion[$i].'">'.$tiposDeSancion[$i].'</option>';
                }
            ?>
        </select>
    </div> 
</div> 
<div class="row align-items-start">
    <div class="col-md-8" >
        <select class="form-select" id="jefeId" name="jefeId" style="width:100%;">
            <option></option>
        </select>
    </div>
    <div class="col-md-4 mb-4">
        Reincidencia:
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="reincidencia" id="reincidencia1" value="Si">
            <label class="form-check-label" for="reincidencia">Si</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="reincidencia" id="reincidencia2" value="No" checked>
            <label class="form-check-label" for="reincidencia">No</label>
        </div>
        <div id="reincidencia" class="form-select-control mt-2" style="display: none;">
            <select class="form-select" id="amonestacionAnt" name="amonestacionAnt" style="width:100%;">
                <option></option>
            </select>
        </div>
    </div> 
</div>
<div class="row">
    <div class="col-md-12 mb-4">
        <select class="form-select mb-4" id="causaSancion" name="causaSancion" style="width:100%;" required>
            <option></option>
            <?php 
                $causasDeSancion = array("Faltas repetidas e injustificadas de asistencia o puntualidad al trabajo", "Indisciplina o desobediencia en el trabajo", "Transgresión de la buena fe contractual, así como el abuso de confianza en el desempeño del trabajo", "Disminución continuada y voluntaria en el rendimiento de sus funciones", "Presentarse en estado de embriaguez o en efectos de sustancias tóxicas", "Conductas de irrespeto o intolerancia hacia su jefe y/o compañeros", "Robo o hurto a la empresa o a otros empleados", "Abandono del puesto de trabajo sin motivos o autorización del jefe inmediato", "Incumplimiento con la política de seguridad de la empresa", "Acoso o abuso laboral (ideológico, sexual, religión, entre otros)", "Incumplimiento en marcar asistencia (física o virtual)","Falta de cumplimiento en el uso del uniforme institucional", "Otros");
                for ($i=0; $i < count($causasDeSancion); $i++) { 
                    echo '<option value="'.$causasDeSancion[$i].'">'.$causasDeSancion[$i].'</option>';
                }
            ?>
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-12 mb-4" id="descripcionDiv" style="display:none;">
        <div class="form-outline mb-4">
            <i class="fas fa-align-justify trailing"></i>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
            <label class="form-label" for="descripcion">Descripción de otro tipo de falta</label>
        </div>
    </div>
    <div class="col-md-12 mb-4">
        <div class="form-outline mb-4">
            <i class="fas fa-align-justify trailing"></i>
            <textarea class="form-control" id="descripcionFalta" name="descripcionFalta" rows="3" required></textarea>
            <label class="form-label" for="descripcionFalta">Descripción de la causa</label>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 mb-4">
        <select class="form-select mb-4" id="consecuencia" name="consecuencia" style="width:100%;" required onchange="toggleVigencia()">
            <option></option>
            <?php 
                $consecuencias = array("Descuento del día", "Descuento de día y séptimo", "Descuento de séptimo", "Descuento del acumulativo de minutos en concepto de llegadas tardes", "Descuento total o parcial de bono", "Llamado de atención verbal y escrito", "Suspensión de labores sin goce de sueldo", "Destitución");
                for ($i=0; $i < count($consecuencias); $i++) { 
                    echo '<option value="'.$consecuencias[$i].'">'.$consecuencias[$i].'</option>';
                }
            ?>
        </select>
    </div>
</div>
<br>
<div id="vigenciaDiv" style="display:none;">
    <div class="row">
        <div class="col-md-2">Vigencia de sanción:</div>
        <div class="col-md-4">
            <div class="form-outline mb-4 input-daterange">
                <i class="fas fa-calendar trailing"></i>
                <input type="date" id="fechaIni" class="form-control" name="fechaVigenciaIni" required />
                <label class="form-label" for="fechaIni">Fecha de inicio</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-outline mb-4 input-daterange">
                <i class="fas fa-calendar trailing"></i>
                <input type="date" id="fechaFin" class="form-control" name="fechaVigenciaFin" required />
                <label class="form-label" for="fechaFin">Fecha de finalización</label>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="form-outline mb-4">
            <i class="fas fa-align-justify trailing"></i>
            <textarea class="form-control" id="descripcionConsecuencia" name="descripcionConsecuencia" rows="3"></textarea>
            <label class="form-label" for="descripcionConsecuencia">Descripción de la consecuencia</label>
        </div>
    </div>
</div>
<!--  
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="form-outline mb-4">
            <i class="fas fa-align-justify trailing"></i>
            <textarea class="form-control" id="compromiso" name="compromiso" rows="3" required></textarea>
            <label class="form-label" for="compromiso">Compromiso de mejora</label>
        </div>
    </div>
</div>
-->
<script>
    function toggleVigencia() {
        var select = document.getElementById("consecuencia");
        var vigenciaDiv = document.getElementById("vigenciaDiv");
        if (select.value === "Suspensión de labores sin goce de sueldo") {
            vigenciaDiv.style.display = "block";
        } else {
            vigenciaDiv.style.display = "none";
        }
    }

    function contarDias() {
        var diaIni = $("#fechaIniSus").val();
        var diaFin = $("#fechaFinSus").val();

        var fecha1 = moment(diaIni);
        var fecha2 = moment(diaFin);

        if (diaIni == "" || diaFin == ""){
            $("#totalDias").val('0');
            $("#totalDias").addClass("active"); 
        } else if (fecha2.diff(fecha1, 'days') < 0){
            mensaje(
                "AVISO",
                "La fecha inicial debe ser anterior a la final.",
                "warning"
            );
            $("#fechaIniSus").val('');
            $("#fechaFinSus").val('');
            $("#totalDiasSus").val('0');
        } else {
            $("#totalDias").val(fecha2.diff(fecha1, 'days')+1);
            $("#totalDias").addClass("active"); 
        }
    }

    $(document).ready(function() {
        $('#inlineCheckbox1').on('change', function() { 
            $("#diasSus").toggle();
        });
        $('#reincidencia1').on('change', function() { 
            $("#reincidencia").toggle(); 
        });
        $('#reincidencia2').on('change', function() { 
            $("#reincidencia").toggle(); 
        });

        $("#persona").select2({
            placeholder: "Empleado",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#jefeId").select2({
            placeholder: "Jefe inmediato",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#amonestacionAnt").select2({
            placeholder: "Amonestación anterior",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#tipoSancion").select2({
            placeholder: "Tipo de sanción",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#causaSancion").select2({
            placeholder: "Motivo de sanción",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#consecuencia").select2({
            placeholder: "Consecuencia de la falta",
            dropdownParent: $('#modal-container'),
            allowClear: true
        });

        $("#persona").change(function(e) {
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/getInfoEmpleado", 
                {
                    expedienteId: $(this).val()
                },
                function(data) {
                    $("#cargo").val(data.cargoPersona);
                    $("#depto").val(data.departamentoSucursal);
                    document.querySelectorAll('.form-outline').forEach((formOutline) => {
                        new mdb.Input(formOutline).update();
                    });
                }
            );
            asyncSelect(
                `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectAmonestacionesAnteriores`,
                {
                    expedienteId: $(this).val()
                },
                `amonestacionAnt`,
                function() {
                }
            );
            // asyncSelect para jefes 
            asyncSelect(
                `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarJefaturasEmpleado`,
                {
                    expedienteId: $(this).val()
                },
                `jefeId`,
                function() {
                }
            );
        });

        $("#causaSancion").change(function(e) {
            if($(this).val() == "Otros") {
                $("#descripcionDiv").show();
            } else {
                $("#descripcionDiv").hide();
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje_do_aceptar(
                                "Operación completada:", 
                                '<?php echo $txtSuccess; ?>', 
                                'success', 
                                function() {
                                    $('#tblAmonestaciones').DataTable().ajax.reload(null, false);
                                    $('#tblAmonestacionesAnuladas').DataTable().ajax.reload(null, false);
                                    $('#tblAmonestacionesInactivas').DataTable().ajax.reload(null, false);
                                    $('#modal-container').modal("hide");
                                }
                            );
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
            if($_POST["typeOperation"] == "update") {
        ?>
                $("#persona").val('<?php echo $dataAmonestacion->prsExpedienteId; ?>').trigger('change');
                $("#fechaAmonestacion").val('<?php echo date("Y-m-d", strtotime($dataAmonestacion->fhAmonestacion)); ?>');
                $("#jefeId").val('<?php echo $dataAmonestacion->expedienteIdJefe; ?>').trigger('change');
                $("input[name='tipoSancion'][value='<?php echo $dataAmonestacion->tipoAmonestacion; ?>']").prop("checked",true).trigger('change');
                $("input[name='suspension'][value='<?php echo $dataAmonestacion->suspension; ?>']").prop("checked",true).trigger('change');
                $("#fechaIniSus").val('<?php echo date("Y-m-d", strtotime($dataAmonestacion->fechaSuspensionInicio)); ?>');
                $("#fechaFinSus").val('<?php echo date("Y-m-d", strtotime($dataAmonestacion->fechaSuspensionFin)); ?>');
                $("#totalDias").val('<?php echo $dataAmonestacion->totalDiasSuspension; ?>')
                $('#fechaIniSus, #fechaFinSus, #totalDias').on('change', function() { 
                    $(this).addClass("active"); 
                });

                if ("<?php echo $dataAmonestacion->flgReincidencia; ?>" == "Si"){
                    $("input[name='reincidencia'][value='<?php echo $dataAmonestacion->flgReincidencia; ?>']").prop("checked",true).trigger('change');//activa el toggle
                }
                $("#causaSancion").val('<?php echo $dataAmonestacion->causaFalta; ?>');
                //abrir accordeones
                $("#descripcion").val('<?php echo $dataAmonestacion->descripcionFalta; ?>');
                $("#consecuencia").val('<?php echo $dataAmonestacion->consecuenciaFalta; ?>');
                $("#descripcionConsecuencia").val('<?php echo $dataAmonestacion->descripcionConsecuencia; ?>');
                $("#advertencia").val('<?php echo $dataAmonestacion->advertenciaSiguienteFalta; ?>');
                $("#compromiso").val('<?php echo $dataAmonestacion->compromisoMejora; ?>');
                $(".collapse").addClass('show');
                //
                $("#fechaIni").val('<?php echo date("Y-m-d", strtotime($dataAmonestacion->fechaVigenciaInicio)); ?>');
                $("#fechaFin").val('<?php echo date("Y-m-d", strtotime($dataAmonestacion->fechaVigenciaFin)); ?>');
        <?php 
            }
        ?>
    });
</script>