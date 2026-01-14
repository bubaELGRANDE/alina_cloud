<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $planillaId = (isset($_POST['planillaId']) ? $_POST['planillaId'] : 0);

    // Para romper el bucle en la function que trae los descuentos al select
    $catPlanillaDevengoId = 0;
    $superiorId = 0;

    if(isset($_POST['devengoId'])) {
        // Es update
        $typeOperation = "update";
        $devengoId = $_POST["devengoId"];

        if($planillaId == 0) {
            // Programado
            $dataDevengo = $cloud->row("
                SELECT
                     pdev.catPlanillaDevengoId AS catPlanillaDevengoId,
                     pdev.descripcionDevengoProgramado AS descripcionDevengo, 
                     pdev.montoDevengoProgramado AS montoDevengo,
                     cpdev.catPlanillaDevengoIdSuperior AS superiorId
                FROM conta_planilla_programado_devengos pdev
                JOIN cat_planilla_devengos cpdev ON cpdev.catPlanillaDevengoId = pdev.catPlanillaDevengoId
                WHERE pdev.planillaDevengoProgramadoId = ? AND pdev.flgDelete = ?
            ", [$_POST['devengoId'], 0]);
            $catPlanillaDevengoId = $dataDevengo->catPlanillaDevengoId;
            $superiorId = $dataDevengo->superiorId;
        } else {
            // Consulta a conta_planilla_descuentos
        }
    } else {
        // Es insert
        $typeOperation = "insert";
        $devengoId = 0;
    }
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="<?php echo $typeOperation; ?>">
<input type="hidden" id="operation" name="operation" value="planilla-devengos">
<input type="hidden" id="devengoId" name="devengoId" value="<?php echo $devengoId; ?>">
<input type="hidden" id="tipoDevengo" name="tipoDevengo" value="<?php echo $_POST['tipoDevengo']; ?>">
<input type="hidden" id="planillaId" name="planillaId" value="<?php echo $planillaId; ?>">
<input type="hidden" id="quincenaId" name="quincenaId" value="<?php echo $_POST['quincenaId']; ?>">
<input type="hidden" id="prsExpedienteId" name="prsExpedienteId" value="<?php echo $_POST['prsExpedienteId']; ?>">
<input type="hidden" id="flgSubdevengo" name="flgSubdevengo" value="No">
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
            <select class="form-select" id="catPlanillaDevengoId" name="catPlanillaDevengoId" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
    <div id="divSubDevengo" class="col-6">
        <div class="form-select-control">
            <select class="form-select" id="subCatPlanillaDevengoId" name="subCatPlanillaDevengoId" style="width:100%;" required>
                <option></option>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-6">
        <div class="form-outline">
            <i class="fas fa-dollar-sign trailing"></i>
            <input type="number" id="montoDevengo" name="montoDevengo" class="form-control" onchange="$(this).val(parseFloat($(this).val()).toFixed(2));" min="0.00" step="0.01" required />
            <label class="form-label" for="montoDevengo">Devengo</label>
        </div>
    </div>
    <div class="col-6">
        <div class="form-outline">
            <i class="fas fa-edit trailing"></i>
            <textarea type="text" id="descripcionDevengo" class="form-control" name="descripcionDevengo"></textarea>
            <label class="form-label" for="descripcionDevengo">Descripción</label>
        </div>
    </div>
</div>
<script>
    function cargarSelectDevengos(superiorId, devengoId, tipoDevengo) {
        let selectDevengo = '';
        if(superiorId > 0) {
            selectDevengo = 'subCatPlanillaDevengoId';
        } else {
            selectDevengo = 'catPlanillaDevengoId';
        }
        asyncSelect(
            "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectPlanillaDevengos",
            {
                superiorId: superiorId,
                tipoDevengo: tipoDevengo
            },
            selectDevengo,
            function() {
                if(devengoId > 0) {
                    <?php 
                        if($superiorId == 0) {
                    ?>
                            $("#catPlanillaDevengoId").val(<?php echo $catPlanillaDevengoId; ?>).trigger('change');
                    <?php 
                        } else {
                    ?>
                            if($("#flgSubdevengo").val() == "No") {
                                $("#catPlanillaDevengoId").val(<?php echo $superiorId; ?>).trigger('change');
                            } else {
                                $("#subCatPlanillaDevengoId").val(<?php echo $catPlanillaDevengoId; ?>).trigger('change');
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
        $("#catPlanillaDevengoId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Devengo'
        });
        $("#subCatPlanillaDevengoId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Subdevengo'
        });
        $("#expedienteMultiple").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Empleado(s)'
        });
        $("#divSubDevengo").hide();
        // Cargar los descuentos que no dependen de otro (superior = 0)
        cargarSelectDevengos(0, <?php echo $devengoId; ?>, $("#tipoDevengo").val());

        $("#catPlanillaDevengoId").change(function(e) {
            let superiorId = $(this).val();
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/checkPlanillaCatalogoSuperior",
                {
                    superiorId: superiorId,
                    catalogo: "Devengos"
                },
                function(data) {
                    if(data) {
                        $("#divSubDevengo").show();
                        cargarSelectDevengos(superiorId, <?php echo $devengoId; ?>, $("#tipoDevengo").val());
                        $("#flgSubdevengo").val("Sí");
                    } else {
                        $("#divSubDevengo").hide();
                        $("#flgSubdevengo").val("No");
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
                                        'Devengos aplicados con éxito',
                                        "success",
                                        function() {
                                            asyncPage(41, 'sub-submenu');
                                            $('#modal-container').modal("hide");
                                        }
                                    );
                            <?php 
                                } else {
                                    // Fue devengo individual
                            ?>
                                    mensaje_do_aceptar(
                                        "Operación completada:",
                                        'Devengo aplicado con éxito',
                                        "success",
                                        function() {
                                            cargarCalculoEmpleado(<?php echo $_POST['prsExpedienteId']; ?>, `<?php echo $_POST['nombreCompleto']; ?>`);
                                            cargarDevengosEmpleado(<?php echo $_POST['prsExpedienteId']; ?>, `<?php echo $_POST['nombreCompleto']; ?>`, `<?php echo $_POST['tipoDevengo']; ?>`);
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
            if(isset($_POST['devengoId'])) {
        ?>
                $("#montoDevengo").val(<?php echo $dataDevengo->montoDevengo; ?>);
                $("#descripcionDevengo").val(`<?php echo $dataDevengo->descripcionDevengo; ?>`);
        <?php 
            } else {
                // Es insert
            }
        ?>
    });
</script>