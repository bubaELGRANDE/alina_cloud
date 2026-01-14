<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $planillaId = (isset($_POST['planillaId']) ? $_POST['planillaId'] : 0);

    // Para romper el bucle en la function que trae los descuentos al select
    $catPlanillaDescuentoId = 0;
    $superiorId = 0;

    if(isset($_POST['descuentoId'])) {
        // Es update
        $typeOperation = "update";
        $descuentoId = $_POST["descuentoId"];

        if($planillaId == 0) {
            // Programado
            $dataOtroDescuento = $cloud->row("
                SELECT
                    pdesc.idDescuentoProgramado AS catPlanillaDescuentoId, 
                    pdesc.descripcionDescuentoProgramado AS descripcionDescuento, 
                    pdesc.montoDescuentoProgramado AS montoDescuento,
                    cpdesc.catPlanillaDescuentoIdSuperior AS superiorId
                FROM conta_planilla_programado_descuentos pdesc
                JOIN cat_planilla_descuentos cpdesc ON cpdesc.catPlanillaDescuentoId = pdesc.idDescuentoProgramado
                WHERE pdesc.planillaDescuentoProgramadoId = ? AND pdesc.flgDelete = ?
            ", [$_POST['descuentoId'], 0]);
            $catPlanillaDescuentoId = $dataOtroDescuento->catPlanillaDescuentoId;
            $superiorId = $dataOtroDescuento->superiorId;
        } else {
            // Consulta a conta_planilla_descuentos
        }
    } else {
        // Es insert
        $typeOperation = "insert";
        $descuentoId = 0;
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $typeOperation; ?>">
<input type="hidden" id="operation" name="operation" value="planilla-otros-descuentos">
<input type="hidden" id="descuentoId" name="descuentoId" value="<?php echo $descuentoId; ?>">
<input type="hidden" id="planillaId" name="planillaId" value="<?php echo $planillaId; ?>">
<input type="hidden" id="quincenaId" name="quincenaId" value="<?php echo $_POST['quincenaId']; ?>">
<input type="hidden" id="prsExpedienteId" name="prsExpedienteId" value="<?php echo $_POST['prsExpedienteId']; ?>">
<input type="hidden" id="flgSubdescuento" name="flgSubdescuento" value="No">
<input type="hidden" id="nombreCompleto" name="nombreCompleto" value="<?php echo $_POST['nombreCompleto']; ?>">
<?php 
    if(isset($_POST['Multiple'])) {
?>
        <input type="hidden" id="flgMultiple" name="flgMultiple" value="1">
        <div class="form-select-control mb-4">
            <select class="form-select" id="expedienteMultiple" name="expedienteMultiple[]" style="width:100%;" multiple="multiple" required>
                <option></option>
                <?php 
                    if($planillaId == 0) {
                        // Traer los expedientes con salario
                        $dataEmpleados = $cloud->rows("
                            SELECT 
                                expsa.prsExpedienteId, 
                                vexp.nombreCompleto
                            FROM th_expediente_salarios expsa
                            JOIN view_expedientes vexp ON vexp.prsExpedienteId = expsa.prsExpedienteId
                            WHERE expsa.estadoSalario = ? AND expsa.flgDelete = ?
                            ORDER BY vexp.apellido1, vexp.apellido2, vexp.nombre1, vexp.nombre2
                        ", ['Activo', 0]);
                        foreach($dataEmpleados as $empleados) {
                            echo "<option value='$empleados->prsExpedienteId'>$empleados->nombreCompleto</option>";
                        }
                    } else {
                        // Traer los planillaId de la quincena que se calculó
                    }
                ?>
            </select>
        </div>
<?php 
    } else {
        // Es directamente a un empleado
    }
?>
<div class="row mb-4">
    <div class="col-6">
        <div class="form-select-control">
            <select class="form-select" id="catPlanillaDescuentoId" name="catPlanillaDescuentoId" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
    <div id="divSubDescuento" class="col-6">
        <div class="form-select-control">
            <select class="form-select" id="subCatPlanillaDescuentoId" name="subCatPlanillaDescuentoId" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-6">
        <div class="form-outline">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="number" id="montoDescuento" name="montoDescuento" class="form-control" onchange="$(this).val(parseFloat($(this).val()).toFixed(2));" min="0.00" step="0.01" required />
            <label class="form-label" for="montoDescuento">Descuento</label>
        </div>
    </div>
    <div class="col-6">
        <div class="form-outline">
            <i class="fas fa-edit trailing"></i>
            <textarea type="text" id="descripcionDescuento" class="form-control" name="descripcionDescuento"></textarea>
            <label class="form-label" for="descripcionDescuento">Descripción</label>
        </div>
    </div>
</div>
<script>
    function cargarSelectDescuentos(superiorId, descuentoId) {
        let selectDescuento = '';
        if(superiorId > 0) {
            selectDescuento = 'subCatPlanillaDescuentoId';
        } else {
            selectDescuento = 'catPlanillaDescuentoId';
        }
        asyncSelect(
            "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectPlanillaDescuentos",
            {
                superiorId: superiorId
            },
            selectDescuento,
            function() {
                if(descuentoId > 0) {
                    <?php 
                        if($superiorId == 0) {
                    ?>
                            $("#catPlanillaDescuentoId").val(<?php echo $catPlanillaDescuentoId; ?>).trigger('change');
                    <?php 
                        } else {
                    ?>
                            if($("#flgSubdescuento").val() == "No") {

                                $("#catPlanillaDescuentoId").val(<?php echo $superiorId; ?>).trigger('change');
                            } else {
                                $("#subCatPlanillaDescuentoId").val(<?php echo $catPlanillaDescuentoId; ?>).trigger('change');
                            }
                    <?php 
                        }
                    ?>
                } else {
                    // Es insert
                }
            }
        );
    }

    $(document).ready(function() {
        $("#catPlanillaDescuentoId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Descuento'
        });
        $("#subCatPlanillaDescuentoId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Subdescuento'
        });
        $("#expedienteMultiple").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Empleado(s)'
        });
        $("#divSubDescuento").hide();
        // Cargar los descuentos que no dependen de otro (superior = 0)
        cargarSelectDescuentos(0, <?php echo $descuentoId; ?>);

        $("#catPlanillaDescuentoId").change(function(e) {
            let superiorId = $(this).val();
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/checkPlanillaCatalogoSuperior",
                {
                    superiorId: superiorId,
                    catalogo: "Descuentos"
                },
                function(data) {
                    if(data) {
                        $("#divSubDescuento").show();
                        cargarSelectDescuentos(superiorId, <?php echo $descuentoId; ?>);
                        $("#flgSubdescuento").val("Sí");
                    } else {
                        $("#divSubDescuento").hide();
                        $("#flgSubdescuento").val("No");
                    }
                }
            );
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
                            <?php 
                                if(isset($_POST['Multiple'])) {
                                    // Fue descuento multiple
                            ?>
                                    mensaje_do_aceptar(
                                        "Operación completada:",
                                        'Descuentos aplicados con éxito',
                                        "success",
                                        function() {
                                            asyncPage(41, 'sub-submenu');
                                            $('#modal-container').modal("hide");
                                        }
                                    );
                            <?php 
                                } else {
                                    // Fue descuento individual
                            ?>
                                    mensaje_do_aceptar(
                                        "Operación completada:",
                                        'Descuento aplicado con éxito',
                                        "success",
                                        function() {
                                            cargarCalculoEmpleado(<?php echo $_POST['prsExpedienteId']; ?>, `<?php echo $_POST['nombreCompleto']; ?>`);
                                            cargarDescuentosEmpleado(<?php echo $_POST['prsExpedienteId']; ?>, `<?php echo $_POST['nombreCompleto']; ?>`);
                                            $('#modal-container').modal("hide");
                                        }
                                    );
                            <?php 
                                }
                            ?>
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
            if(isset($_POST['descuentoId'])) {
        ?>
                $("#montoDescuento").val(<?php echo $dataOtroDescuento->montoDescuento; ?>);
                $("#descripcionDescuento").val(`<?php echo $dataOtroDescuento->descripcionDescuento; ?>`);
        <?php 
            } else {
                // Es insert
            }
        ?>
    });
</script>