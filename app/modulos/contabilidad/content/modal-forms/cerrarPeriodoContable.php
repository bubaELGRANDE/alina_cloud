<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="update">
<input type="hidden" id="operation" name="operation" value="cerrar-periodo-contable">
<div class="row">
    <div class="col-md-6">
        <div class="form-select-control lg">
            <select class="form-select" id="periodoContable" name="periodoContable" style="width:100%;" required>
                <option></option>
                <?php
                $dataPeriodoActivo = $cloud->rows("
                    SELECT partidaContaPeriodoId,concat(mesNombre,' ',anio) AS periodo
                    FROM desarrollo_cloud.conta_partidas_contables_periodos
                    WHERE flgDelete = ? AND estadoPeriodoPartidas = ?
                    ORDER BY fechaCierrePeriodo ASC, mes ASC, anio ASC;",
                    [0, 'Activo']
                );
                foreach ($dataPeriodoActivo as $p) {
                    echo '<option value="' . $p->partidaContaPeriodoId . '">' . $p->periodo . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#mes").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Periodo a cerrar'
        });

        $("#frmModal").validate({
            submitHandler: function (form) {
                mensaje_confirmacion(
                    '¿Está seguro que desea aplicar el cierre del periodo?',
                    `Una vez aplicado ya no podrá emitir ni invalidar documentos en el periodo seleccionado`,
                    `warning`,
                    function (param) {
                        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                            $("#frmModal").serialize(),
                            function (data) {
                                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                if (data == "success") {
                                    $("#modal-container").modal("hide");
                                    $('#tblPeriodos').DataTable().ajax.reload(null, false);
                                } else {
                                    mensaje("Aviso:", data, "warning");
                                }
                            }
                        );
                    },
                    'Sí, aplicar',
                    `Cancelar`
                );
            }
        });
    });
</script>