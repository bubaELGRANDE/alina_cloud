<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    // nuevo
    // editar ^ lineaId
    $arrayFormData = explode("^", $_POST['arrayFormData']);
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="parametrizacion-comision">
<input type="hidden" id="flgSubmit" name="flgSubmit" value="0">
<?php 
    if($arrayFormData[0] == 'editar') {
        $dataNombreLinea = $cloud->row("
            SELECT 
                CONCAT('(', abreviatura, ') ', linea) AS nombreLinea
            FROM temp_cat_lineas
            WHERE lineaId = ?
        ", [$arrayFormData[1]]);
?>
        <div class="form-outline mb-4">
            <i class="fas fa-tag trailing"></i>
            <input type="text" id="linea" class="form-control" name="linea" value="<?php echo $dataNombreLinea->nombreLinea; ?>" readonly />
            <label class="form-label" for="linea">Línea</label>
        </div>
        <div id="divForm">
            <p align="justify">
                <b>Nota:</b> Los rangos deben ser ingresados de menor a mayor.
            </p>
            <?php 
                $correlativoWrapper = 0; $conteoWrappers = 0;
                $dataCondiciones = $cloud->rows("
                    SELECT
                        comisionPorcentajeLineaId, 
                        lineaId, 
                        rangoPorcentajeInicio, 
                        rangoPorcentajeFin, 
                        porcentajePago
                    FROM conta_comision_porcentaje_lineas
                    WHERE lineaId = ? AND flgDelete = '0'
                ", [$arrayFormData[1]]);
                foreach($dataCondiciones as $dataCondiciones) {
                    $correlativoWrapper += 1;
                    $conteoWrappers += 1;
            ?>
                    <div class="row" id="divFilaWrapper<?php echo $correlativoWrapper; ?>">
                        <div class="col-md-4">
                            <div class="form-outline mb-4">
                                <input type="number" id="rangoInicio<?php echo $correlativoWrapper; ?>" class="form-control" name="rangoInicio[]" onchange="validarRangos('rangoInicio', '<?php echo $correlativoWrapper; ?>');" min="0" step="0.01" value="<?php echo $dataCondiciones->rangoPorcentajeInicio; ?>" required />
                                <label class="form-label" for="rangoInicio">Rango inicio</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-outline mb-4">
                                <input type="number" id="rangoFin<?php echo $correlativoWrapper; ?>" class="form-control" name="rangoFin[]" onchange="validarRangos('rangoFin', '<?php echo $correlativoWrapper; ?>');" min="0" step="0.01" value="<?php echo $dataCondiciones->rangoPorcentajeFin; ?>" required />
                                <label class="form-label" for="rangoFin">Rango fin</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-outline mb-4">
                                <input type="number" id="porcentajePagar<?php echo $correlativoWrapper; ?>" class="form-control" name="porcentajePagar[]" min="0" step="0.01" value="<?php echo $dataCondiciones->porcentajePago; ?>" required />
                                <label class="form-label" for="porcentajePagar">Porcentaje a pagar</label>
                            </div>
                        </div>
                        <div class="col-md-1 text-center controles">
                            <button class="btn btn-danger btn-sm" type="button" onclick="eliminarWrapperEdit(<?php echo $dataCondiciones->comisionPorcentajeLineaId; ?>, <?php echo $correlativoWrapper; ?>);">
                                <i class="fas fa-minus-circle"></i>
                            </button>
                        </div>
                        <input type="hidden" id="comisionPorcentajeLineaId<?php echo $correlativoWrapper; ?>" name="comisionPorcentajeLineaId[]" value="<?php echo $dataCondiciones->comisionPorcentajeLineaId; ?>">
                    </div>
            <?php 
                } // foreach dataCondicioens
            ?>
            <input type="hidden" id="conteoWrappersActual" name="conteoWrappersActual" value="<?php echo $conteoWrappers; ?>">
            <input type="hidden" id="correlativoWrapper" name="correlativoWrapper" value="<?php echo $correlativoWrapper; ?>">
            <input type="hidden" id="conteoWrappers" name="conteoWrappers" value="<?php echo $conteoWrappers; ?>">
        </div>
        <script>
            function eliminarWrapperEdit(id, correlativo) {
                if($("#conteoWrappers").val() == 1) { // Solo queda 1 condicion
                    mensaje(
                        "Aviso:",
                        'Debe agregar al menos una condición.',
                        "warning"
                    );
                } else {
                    mensaje_confirmacion(
                        `¿Está seguro que desea eliminar esta condición?`, 
                        `Se eliminará de la parametrización de línea y no volverá a ser tomada en cuenta en el cálculo de comisiones.`, 
                        `warning`, 
                        function(param) {
                            asyncDoDataReturn(
                                '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation/', 
                                {
                                    typeOperation: `delete`,
                                    operation: `parametrizacion-comision`,
                                    id: id
                                },
                                function(data) {
                                    if(data == "success") {
                                        mensaje_do_aceptar(`Operación completada:`, `Condición eliminada con éxito.`, `success`, function() {
                                            $(`#tblParametrizacion`).DataTable().ajax.reload(null, false);
                                            $(`#divFilaWrapper${correlativo}`).remove();
                                            $("#conteoWrappers").val(parseInt($("#conteoWrappers").val()) - 1);
                                        });
                                    } else {
                                        mensaje(
                                            "Aviso:",
                                            data,
                                            "warning"
                                        );
                                    }
                                }
                            );
                        },
                        `Eliminar`,
                        `Cancelar`
                    );
                }
            }
            function eliminarWrapper(correlativo) {
                if($("#conteoWrappers").val() == 1) { // Solo queda 1 condicion
                    mensaje(
                        "Aviso:",
                        'Debe agregar al menos una condición.',
                        "warning"
                    );
                } else {
                    $(`#divFilaWrapper${correlativo}`).remove();
                    $("#conteoWrappers").val(parseInt($("#conteoWrappers").val()) - 1);
                }
            }
        </script>   
<?php 
    } else {
?>
        <div class="form-select-control mb-4">
            <select id="linea" name="linea[]" style="width: 100%;" multiple="multiple" required>
                <option></option>
                <?php 
                    // Seleccionar las lineas exceptuando las que ya fueron agregadas
                    $dataLineas = $cloud->rows("
                        SELECT 
                            lineaId, 
                            CONCAT('(', abreviatura, ') ', linea) AS nombreLinea
                        FROM temp_cat_lineas
                        WHERE lineaId NOT IN (
                            SELECT lineaId
                            FROM conta_comision_porcentaje_lineas
                        ) AND flgDelete = '0'
                    ");
                    foreach ($dataLineas as $dataLineas) {
                        echo '<option value="'.$dataLineas->lineaId.'">'.$dataLineas->nombreLinea.'</option>';
                    }
                ?>
            </select>
        </div>
        <div id="divForm">
            <p align="justify">
                <b>Nota:</b> Los rangos deben ser ingresados de menor a mayor.
            </p>
            <div class="row" id="divFilaWrapper1">
                <div class="col-md-4">
                    <div class="form-outline mb-4">
                        <input type="number" id="rangoInicio1" class="form-control" name="rangoInicio[]" onchange="validarRangos('rangoInicio', '1');" min="0" step="0.01" required />
                        <label class="form-label" for="rangoInicio">Rango inicio</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-outline mb-4">
                        <input type="number" id="rangoFin1" class="form-control" name="rangoFin[]" onchange="validarRangos('rangoFin', '1');" min="0" step="0.01" required />
                        <label class="form-label" for="rangoFin">Rango fin</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-outline mb-4">
                        <input type="number" id="porcentajePagar1" class="form-control" name="porcentajePagar[]" min="0" step="0.01" required />
                        <label class="form-label" for="porcentajePagar">Porcentaje a pagar</label>
                    </div>
                </div>
                <div class="col-md-1 text-center controles">
                    <button class="btn btn-danger btn-sm" type="button" onclick="eliminarWrapper(1);">
                        <i class="fas fa-minus-circle"></i>
                    </button>
                </div>
            </div>
        </div>
        <input type="hidden" id="correlativoWrapper" name="correlativoWrapper" value="1">
        <input type="hidden" id="conteoWrappers" name="conteoWrappers" value="1">
        <script>
            function eliminarWrapper(correlativo) {
                if($("#conteoWrappers").val() == 1) { // Solo queda 1 condicion
                    mensaje(
                        "Aviso:",
                        'Debe agregar al menos una condición.',
                        "warning"
                    );
                } else {
                    $(`#divFilaWrapper${correlativo}`).remove();
                    $("#conteoWrappers").val(parseInt($("#conteoWrappers").val()) - 1);
                }
            }
        </script>   
<?php 
    }
?>
<div class="row">
    <div class="col-md-12 text-center">
        <button type="button" class="btn btn-success" id="btnAddRango"><i class="fas fa-plus-circle"></i> Nuevo rango</button>
    </div>
</div>
<script>
    function validarRangos(input, correlativo) {
        // Asegurarse que los rangos entre sí mismos sean mayores
        if($(`#rangoFin${correlativo}`).val() != "" && !(parseFloat($(`#rangoFin${correlativo}`).val()) > parseFloat($(`#rangoInicio${correlativo}`).val()))) {
            mensaje("Aviso: ", `El rango fin (${$(`#rangoFin${correlativo}`).val()}) debe ser mayor que el rango inicio (${$(`#rangoInicio${correlativo}`).val()})`, "warning");
            $(`#rangoFin${correlativo}`).val('');
            $("#flgSubmit").val('0');
        } else {
            if($("#conteoWrappers").val() == 1) { // Solo queda 1 fila
                // No validar nada ya que no existen más filas
                $("#flgSubmit").val('1');
            } else {
                let nombreInput = (input == "rangoInicio") ? "inicio" : "fin";
                // Comparar el rango inicio actual con el anterior y asegurarse que sea mayor
                if(parseFloat($(`#rangoInicio${correlativo}`).val()) <= parseFloat($(`#rangoFin${(parseInt(correlativo) - 1)}`).val())) {
                    mensaje("Aviso:", `El rango ${nombreInput} ingresado se encuentra en el rango de la condición anterior`, "warning");
                    $(`#rangoInicio${correlativo}`).val('');
                    $("#flgSubmit").val('0');
                } else {
                    // Verificar si se ha modificado el rangoFin y comparar con el siguiente rango inicio
                    if(nombreInput == "fin" && parseFloat($(`#rangoFin${correlativo}`).val()) >= parseFloat($(`#rangoInicio${parseInt(correlativo) + 1}`).val())) {
                        mensaje("Aviso:", `El rango fin ingresado es mayor que el rango inicio de la siguiente condición`, "warning");
                        $(`#rangoFin${correlativo}`).val('');
                        $("#flgSubmit").val('0');
                    } else {
                        // La condición está bien
                        $("#flgSubmit").val('1');
                    }
                }
            }
        }
    }

    $(document).ready(function() {
        <?php 
            if($arrayFormData[0] == 'nuevo') {
        ?>
                $("#linea").select2({
                    dropdownParent: $('#modal-container'),
                    placeholder: 'Línea(s)'
                });
        <?php 
            } else {
                // No hay select en el editar
            }
        ?>

        $("#btnAddRango").click(function(e){
            e.preventDefault(); 

            // Validar si los inputs del correlativoWrapper actual está completo antes de sumar 1
            if($(`#rangoInicio${$("#correlativoWrapper").val()}`).val() == "" || $(`#rangoFin${$("#correlativoWrapper").val()}`).val() == "" || $(`#porcentajePagar${$("#correlativoWrapper").val()}`).val() == "") {
                mensaje("Aviso:", "Debe completar la condición anterior", "warning");
            } else {
                $("#correlativoWrapper").val(parseInt($("#correlativoWrapper").val()) + 1);
                $("#conteoWrappers").val(parseInt($("#conteoWrappers").val()) + 1);

                $("#divForm").append(`
                    <div class="row" id="divFilaWrapper${$("#correlativoWrapper").val()}">
                        <div class="col-md-4">
                            <div class="form-outline mb-4">
                                <input type="number" id="rangoInicio${$("#correlativoWrapper").val()}" class="form-control" name="rangoInicio[]" onchange="validarRangos('rangoInicio',${$("#correlativoWrapper").val()});" min="0" step="0.01" required />
                                <label class="form-label" for="rangoInicio">Rango inicio</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-outline mb-4">
                                <input type="number" id="rangoFin${$("#correlativoWrapper").val()}" class="form-control" name="rangoFin[]" onchange="validarRangos('rangoFin',${$("#correlativoWrapper").val()});" min="0" step="0.01" required />
                                <label class="form-label" for="rangoFin">Rango fin</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-outline mb-4">
                                <input type="number" id="porcentajePagar${$("#correlativoWrapper").val()}" class="form-control" name="porcentajePagar[]" min="0" step="0.01" required />
                                <label class="form-label" for="porcentajePagar">Porcentaje a pagar</label>
                            </div>
                        </div>
                        <div class="col-md-1 text-center controles">
                            <button class="btn btn-danger btn-sm" type="button" onclick="eliminarWrapper(${$("#correlativoWrapper").val()});">
                                <i class="fas fa-minus-circle"></i>
                            </button>
                        </div>
                    </div>
                `);
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                // Validar los últimos input de condiciones por si se presiona enter sin querer
                if($(`#rangoInicio${$("#correlativoWrapper").val()}`).val() == "") {
                    validarRangos("rangoInicio", $("#correlativoWrapper").val());
                } else if($(`#rangoFin${$("#correlativoWrapper").val()}`).val() == "") {
                    validarRangos("rangoFin", $("#correlativoWrapper").val());
                } else if($(`#porcentajePagar${$("#correlativoWrapper").val()}`).val() == "") {
                    mensaje("Aviso:", "Debe completar la condición anterior", "warning");
                } else {
                    if($("#flgSubmit").val() == '1') {
                        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                        asyncDoDataReturn(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                            $("#frmModal").serialize(),
                            function(data) {
                                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                if(data == "success") {
                                    mensaje(
                                        "Operación completada:",
                                        'Parametrización creada con éxito.',
                                        "success"
                                    );
                                    $('#tblParametrizacion').DataTable().ajax.reload(null, false);
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
                    } else {
                        // Saltó una sweetalert de validarRangos() y al darle enter se enviaba el submit, con esto ya no
                    }
                }
            }
        });

        <?php 
            if($arrayFormData[0] == 'editar') {
        ?>
                $("#typeOperation").val('update');
        <?php 
            } else {
            }
        ?>
    });
</script>