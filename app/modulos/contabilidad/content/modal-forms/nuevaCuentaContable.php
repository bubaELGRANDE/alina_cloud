<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$cuentasMayor = $cloud->rows("
        SELECT cuentaContaId, descripcionCuenta, numeroCuenta
        FROM conta_cuentas_contables
        WHERE tipoCuenta = 'Mayor'
        ORDER BY numeroCuenta
    ");
if (isset($_POST['typeOperation']) && $_POST['typeOperation'] == "update") {
    $dataCuenta = $cloud->row("
            SELECT cuentaContaId, numeroCuenta AS numCuenta, descripcionCuenta, tipoCuenta, tipoMayoreo,
                   categoriaCuenta, cuentaPadreId, flgCentroCostos, centroCostoId, centroCostoDetalleId
            FROM conta_cuentas_contables
            WHERE cuentaContaId = ? AND flgDelete = 0
        ", [$_POST['cuentaContaId']]);
} else {
    // Fue insert
}
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="nueva-cuenta-contable">
<?php if (isset($dataCuenta)) { ?>
    <input type="hidden" id="cuentaContaId" name="cuentaContaId" value="<?= $dataCuenta->cuentaContaId ?>">
<?php } ?>


<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="form-select-control">
            <select class="form-select" id="tipoCuenta" name="tipoCuenta" style="width:100%;" required>
                <option></option>
                <option value="Mayor">Mayor</option>
                <option value="Auxiliar">Auxiliar</option>
            </select>
        </div>
    </div>

    <div class="col-6">
        <div class="form-outline">
            <i class="fas fa-edit trailing"></i>
            <input type="text" id="descripcionCuenta" class="form-control" name="descripcionCuenta" required
                value="<?= isset($dataCuenta) ? $dataCuenta->descripcionCuenta : '' ?>">
            <label class="form-label" for="descripcionCuenta">Descripción</label>
        </div>
    </div>

    <!-- Campo número de cuenta -->
    <div class="col-md-6" id="campoNumeroCuenta">
        <div class="form-outline">
            <i class="fas fa-list-ol trailing"></i>
            <input type="number" id="numeroCuenta" class="form-control" name="numeroCuenta" required />
            <label class="form-label" for="numeroCuenta">Número de cuenta</label>
        </div>
    </div>

    <!-- Campo cuenta mayor (solo visible si es auxiliar) -->  
    <div class="col-md-6 d-none" id="campoCuentaPadre">
        <div class="form-select-control">
            <select id="cuentaPadreId" name="cuentaPadreId" style="width: 100%;">
                <option></option>
                <?php foreach ($cuentasMayor as $cuenta): ?>
                    <option value="<?= $cuenta->cuentaContaId ?>">
                        <?= $cuenta->numeroCuenta ?> - <?= $cuenta->descripcionCuenta ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="form-select-control">
            <select class="form-select" id="categoriaCuenta" name="categoriaCuenta" style="width:100%;" required>
                <option></option>
                <option value="Capital">Capital</option>
                <option value="Gastos">Gastos</option>
                <option value="Ingresos">Ingresos</option>
                <!--<option value="Orden">Orden</option>-->
                <option value="Resultado">Resultado</option>
                <option value="Contrapartida">Contrapartida</option>
                <option value="Activo">Activo</option>
                <option value="Pasivo">Pasivo</option>
            </select>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="form-select-control">
            <select class="form-select" id="tipoMayoreo" name="tipoMayoreo" style="width:100%;" required>
                <option></option>
                <option value="Cargo">Cargo</option>
                <option value="Abono">Abono</option>
            </select>
        </div>
    </div>

    <div class="row d-none" id="inputCentroCosto">
        <div class="col-md-6 mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="flgCentroCostos" name="flgCentroCostos" value="No">
                <label class="form-check-label" for="flgCentroCostos">
                    ¿Agregar centro de costos?
                </label>
            </div>
        </div>
    </div>
    <!-- Contenedor de centros de costo, oculto inicialmente 
    <div class="row d-none" id="centroCostoContainer">
         Select Centro de Costo 
        <div class="col-md-6 mb-4">
            <div class="form-select-control">
                <label for="centroCostoId">Centro de Costo</label>
                <select class="form-select" id="centroCostoId" name="centroCostoId" style="width: 100%;">
                    <option></option>
                    /*
$centros = $cloud->rows("SELECT centroCostoId, nombreCentroCosto FROM conta_centros_costo WHERE flgDelete = 0 AND estadoCentroCosto = 'Activo' ORDER BY nombreCentroCosto");
foreach ($centros as $c) {
echo "<option value='{$c->centroCostoId}'>{$c->nombreCentroCosto}</option>";
}*/
                        ?>
                </select>
            </div>
        </div>

   Select Subcentro de Costo 
        <div class="col-md-6 mb-4">
            <div class="form-select-control">
                <label for="subCentroCostoId">Subcentro de Costo</label>
                <select class="form-select" id="subCentroCostoId" name="subCentroCostoId" style="width: 100%;">
                    <option></option>
              Se llenará vía JS onChange 
                </select>
            </div>
        </div>
    </div>-->
</div>

<script>
    /*$("#flgCentroCostos").on("change", function () {
        if ($(this).is(":checked")) {
            $("#centroCostoContainer").removeClass("d-none");
        } else {
            $("#centroCostoContainer").addClass("d-none");
            $("#centroCostoId, #subCentroCostoId").val('').trigger('change');
        }
    });*/

    var onInit = false

    $("#centroCostoId").on("change", function () {
        const centroId = $(this).val();
        if (centroId) {
            $("#subCentroCostoId").html(`<option></option>`); // limpiar antes de llenar

            <?php
            // construimos un array asociativo centro => [subcentros...]
            $centroSubcentrosMap = [];
            $detalles = $cloud->rows("SELECT centroCostoId, centroCostoDetalleId, nombreCentroCostoDetalle FROM conta_centros_costo_detalle WHERE flgDelete = 0");
            foreach ($detalles as $d) {
                $centroSubcentrosMap[$d->centroCostoId][] = [
                    'id' => $d->centroCostoDetalleId,
                    'nombre' => $d->nombreCentroCostoDetalle
                ];
            }
            ?>
            const subcentrosPorCentro = <?= json_encode($centroSubcentrosMap) ?>;

            if (subcentrosPorCentro[centroId]) {
                subcentrosPorCentro[centroId].forEach((item) => {
                    $("#subCentroCostoId").append(`<option value="${item.id}">${item.nombre}</option>`);
                });
            }
        }
    });

    $(document).ready(function () {
        
        $("#tipoMayoreo").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Tipo de Mayore"
        });

        $("#tipoCuenta").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Tipo de cuenta"
        });
        $("#cuentaPadreId").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Cuenta Mayor"
        });
        $("#centroCostoId").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Centro de costo"
        });

        $("#subCentroCostoId").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Sub centro de costo"
        });



        function generarNumeroCuentaAuxiliar(cuentaPadreId) {
            if (onInit) {

                $.post("<?= $_SESSION['currentRoute']; ?>content/divs/selectCuentaAuxiliar", {
                    cuentaPadreId: cuentaPadreId
                }, function (numeroGenerado) {
                    $("#numeroCuenta").val(numeroGenerado);
                    $(this).addClass("active");
                });
            }
        }



        $("#categoriaCuenta").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Categoria"
        });

        $("#categoriaCuenta").on("change", function () {
            const categoria = $(this).val();
            const map = {
                "Capital": "Cargo",
                "Gastos": "Abono",
                "Ingresos": "Cargo",
                "Resultado": "Cargo",
                "Contrapartida": "Cargo",
                "Activo": "Abono",
                "Pasivo": "Cargo"
            };

            if (map[categoria]) {
                $("#tipoMayoreo").val(map[categoria]).trigger("change");
            }

            if (categoria === 'Gastos' || categoria === 'Ingresos') {
                $("#inputCentroCosto").removeClass("d-none");
                $("#flgCentroCostos").prop("checked", false)
                $("#flgCentroCostos").val("Si")
            } else {
                $("#inputCentroCosto").addClass("d-none");
                $("#flgCentroCostos").prop("check", false)
                $("#flgCentroCostos").val("No")
            }
        });



        $("#tipoCuenta").on("change", function () {
            const tipo = $(this).val();
            if (tipo === "Mayor") {
                $("#campoNumeroCuenta").removeClass("d-none");
                $("#numeroCuenta").prop("readonly", false).val("");
                $("#campoCuentaPadre").addClass("d-none");
                $("#campoNumeroCuenta").show();
                $("#numeroCuenta").addClass("active");
                $("#campoCuentaPadre").removeClass("d-none"); // <- mostrar igual
                $("#cuentaPadreId").val('').trigger('change'); // limpiar selección
            } else if (tipo === "Auxiliar") {
                $("#campoNumeroCuenta").removeClass("d-none");
                $("#numeroCuenta").prop("readonly", false).val(""); // Aquí cambie para que no quede readonly cuando es una auxiliar y solo le da el sugerido
                $("#campoCuentaPadre").removeClass("d-none");
                $("#campoNumeroCuenta").show();
                $("#numeroCuenta").addClass("active");
            } else {
                $("#campoNumeroCuenta").addClass("d-none");
                $("#campoCuentaPadre").addClass("d-none");
                $("#campoNumeroCuenta").show();
                $("#numeroCuenta").addClass("active");
            }
        });

        $("#cuentaPadreId").on("change", function () {
            const cuentaPadreId = $(this).val();
            if (cuentaPadreId !== "" && onInit == true) {
                generarNumeroCuentaAuxiliar(cuentaPadreId);
            }
        });

        $("#frmModal").validate({
            submitHandler: function (form) {
                const numeroCuenta = $("#numeroCuenta").val();
                const tipoOperacion = $("#typeOperation").val();

                if (!$('#flgCentroCostos').is(':checked')) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'flgCentroCostos',
                        value: 'No'
                    }).appendTo('#frmModal');
                }


                // Mensajes dinámicos según tipo operación
                const titulo = (tipoOperacion === "update")
                    ? "¿Está seguro que desea actualizar esta cuenta contable?"
                    : "¿Está seguro que desea crear una nueva cuenta contable?";

                const texto = (tipoOperacion === "update")
                    ? "La cuenta contable será actualizada con los nuevos datos."
                    : "La nueva cuenta quedará creada en el catálogo de cuentas contables.";

                const btn = (tipoOperacion === "update") ? "Sí, Actualizar" : "Sí, Crear";

                mensaje_confirmacion(
                    titulo,
                    texto,
                    "warning",
                    function () {
                        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>/transaction/operation",
                            $("#frmModal").serialize(),
                            function (data) {
                                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                if (data == "success") {
                                    const doneMsg = (tipoOperacion === "update")
                                        ? "Cuenta actualizada con éxito."
                                        : "Cuenta creada con éxito.";
                                    mensaje_do_aceptar("Operación completada:", doneMsg, "success", function () {
                                        $("#modal-container").modal("hide");
                                        $("#tblCuentasContables").DataTable().ajax.reload(null, false);
                                    });
                                } else {
                                    mensaje("Aviso:", data, "warning");
                                }
                            }
                        );
                    },
                    btn,
                    "Cancelar"
                );
            }
        });



        <?php if (isset($dataCuenta)) { ?>

            $("#typeOperation").val("update");
            $("#descripcionCuenta").val("<?= $dataCuenta->descripcionCuenta ?>");
            $("#tipoCuenta").val("<?= $dataCuenta->tipoCuenta ?>").trigger("change");
            $("#tipoMayoreo").val("<?= $dataCuenta->tipoMayoreo ?>").trigger("change");
            $("#categoriaCuenta").val("<?= $dataCuenta->categoriaCuenta ?>").trigger("change");
            $("#cuentaPadreId").val("<?= $dataCuenta->cuentaPadreId ?>").trigger("change");
            $("#numeroCuenta").val("<?= $dataCuenta->numCuenta ?>").trigger("change");

            onInit = true;

            //$("#flgCentroCostos").prop("checked", true).trigger("change");
            <?php if ($dataCuenta->flgCentroCostos == "Si") { ?>
                        /*$("#flgCentroCostos").prop("checked", true);
                        $("#centroCostoId").val("<?= $dataCuenta->centroCostoId ?>").trigger("change");
                // Esperar a que se carguen subcentros antes de seleccionar el correcto
                setTimeout(function () {
                    $("#subCentroCostoId").val("<?= $dataCuenta->centroCostoDetalleId ?>").trigger("change");
                }, 300);*/
            <?php } ?>
        <?php } else { ?>
            // Fue insert
            onInit = true;
        <?php } ?>

    });
</script>