<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();


$dataPeriodos = $cloud->rows("SELECT partidaContaPeriodoId,mes,anio,concat(mesNombre,' ',anio) as periodoNombre
FROM conta_partidas_contables_periodos
WHERE flgDelete = ? AND estadoPeriodoPartidas = ?
ORDER BY  anio ASC, mes ASC ", [0, 'Activo']);
?>

<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="bit-mayorizacion">
<input type="hidden" name="personaId" value="<?php echo $_SESSION['personaId']; ?>">
<input type="hidden" id="desc" name="desc" value="">


<div class="row">
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select class="form-select" id="fechaMayorizacionInicio" name="fechaMayorizacionInicio" style="width:100%;"
                required>
                <option></option>
                <?php
                foreach ($dataPeriodos as $periodo) {
                    echo '<option value="' . $periodo->partidaContaPeriodoId . '">' . $periodo->periodoNombre . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-select-control mb-4">
            <select class="form-select" id="fechaMayorizacionFin" name="fechaMayorizacionFin" style="width:100%;"
                required>
                <option></option>
                <?php
                foreach ($dataPeriodos as $periodo) {
                    echo '<option value="' . $periodo->partidaContaPeriodoId . '">' . $periodo->periodoNombre . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
</div>
<script>

    function mayorizar(nuevo) {
        const now = new Date();
        const fechaHora = now.toLocaleString('es-SV', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });

        let msg = '';

        if (nuevo) {
            msg = `Proceso de mayorización ejecutado por primera vez. Fecha y hora: ${fechaHora}.`;
            $("#desc").val(msg);
        } else {
            msg = `Mayorización ejecutada nuevamente. El registro anterior fue sustituido. Fecha y hora: ${fechaHora}.`;
            $("#desc").val(msg);
        }

        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
        asyncData(
            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
            $("#frmModal").serialize(),
            function (data) {
                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                if (data.status == 'success') {

                    mensaje(
                        "Operación completada:",
                        'Mayorización completa.',
                        "success"
                    );
                    $('#tblMayorizacion').DataTable().ajax.reload(null, false);

                    $("#modal-container").modal("hide");
                } else {
                    mensaje("Aviso:", data, "warning");
                }
            }
        );
    }

    $(document).ready(function () {

        $("#check").hide();

        $("#fechaMayorizacionInicio").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Desde'
        });

        $("#fechaMayorizacionFin").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Hasta'
        });

        $("#fechaMayorizacionInicio, #fechaMayorizacionFin").on("change", function () {
            const inicio = parseInt($("#fechaMayorizacionInicio").val());
            const fin = parseInt($("#fechaMayorizacionFin").val());

            if (!isNaN(inicio) && !isNaN(fin)) {
                if (inicio > fin) {
                    $("#fechaMayorizacionInicio").val(fin).trigger('change');
                    $("#fechaMayorizacionFin").val(inicio).trigger('change');
                }
            }
        });

        $("#frmModal").validate({
            submitHandler: function (form) {
                mensaje_confirmacion(
                    '¿Está seguro que desea mayorizar?',
                    `Esta operación puede tardan mas de un minuto.`,
                    `warning`,
                    function (param) {
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>content/divs/getMayorizacionBitacora",
                            $("#frmModal").serialize(),
                            function (response) {
                                if (response.status) {
                                    mayorizar(true);
                                }
                                else {
                                    mensaje_confirmacion(
                                        'Ya existe un registro de mayorización',
                                        `Ya existe una mayorización en el rango seleccionado. Al continuar, el registro anterior se eliminará. ¿Desea proceder con la nueva mayorización?`,
                                        `warning`,
                                        function (param) {
                                            mayorizar(false);

                                        },
                                        'Sí, mayorizar',
                                        `Cancelar`
                                    );
                                }
                            }
                        )
                    },
                    'Sí, mayorizar',
                    `Cancelar`
                );
            }
        });
    });
</script>