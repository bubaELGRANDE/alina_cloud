<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
@session_start();

$periodoId = isset($_POST['periodoPartidas']) ? $_POST['periodoPartidas'] : null;

$siguiente = "00000001"; // valor por defecto

if ($periodoId) {
    $ultimo = $cloud->row("
        SELECT MAX(numPartida) AS ultimo
        FROM conta_partidas_contables
        WHERE partidaContaPeriodoId = ? && tipoPartidaId = ?", [$periodoId]);
        $siguiente = str_pad(($ultimo && $ultimo->ultimo ? $ultimo->ultimo + 1 : 1), 8, "0", STR_PAD_LEFT);
}

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="nueva-partida-contable">

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="form-outline">
            <i class="fas fa-list-ol trailing"></i>
            <input type="text" id="numPartidaDisplay" class="form-control" value="<?= $siguiente ?>" readonly />
            <input type="hidden" id="numPartida" name="numPartida" value="<?= $siguiente ?>" />
            <label class="form-label" for="numPartida">Número de partida</label>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="form-select-control">
            <select class="form-select" id="periodoPartidas" name="periodoPartidas" style="width:100%;" required>
                <option></option>
                <?php
                $dataPeriodos = $cloud->rows("
                    SELECT partidaContaPeriodoId,concat(mesNombre,' ',anio) as periodoNombre
                    FROM conta_partidas_contables_periodos
                    WHERE flgDelete = ? AND estadoPeriodoPartidas = ?
                    ORDER BY  anio ASC, mes ASC ", [0,'Activo']);
                foreach ($dataPeriodos as $periodo) {
                    echo "<option value='$periodo->partidaContaPeriodoId'>$periodo->periodoNombre</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="form-select-control">
            <select class="form-select" id="tipoPartidas" name="tipoPartidas" style="width:100%;" required>
                <option></option>
                <?php
                $dataTipoPartida = $cloud->rows("
                            SELECT tipoPartidaId, descripcionPartida FROM cat_tipo_partida_contable
                            WHERE  flgDelete = ?
                        ", [0]);
                foreach ($dataTipoPartida as $dataTipoPartida) {
                    echo "<option value='$dataTipoPartida->tipoPartidaId'> $dataTipoPartida->descripcionPartida</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="form-outline">
            <input type="date" id="fechaPartida" class="form-control" name="fechaPartida" required>
            <label class="form-label" for="fechaPartida">Fecha de partida</label>
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-outline">
            <i class="fas fa-edit trailing"></i>
            <input type="text" id="descripcionPartida" class="form-control" name="descripcionPartida" required>
            <label class="form-label" for="descripcionPartida">Concepto general</label>
        </div>
    </div>
</div>

<script>
    $("#periodoPartidas").on("change", function () {
        const periodoId = $(this).val();
        const tipoPartidaId = $(tipoPartidas).val();

        if (periodoId && tipoPartidaId) {
            $.post("<?= $_SESSION['currentRoute']; ?>content/divs/getNumPartida", {
                periodoPartidas: periodoId
            }, function (nuevoNum) {
                $("#numPartida").val(nuevoNum);
                $("#numPartidaDisplay").val(nuevoNum);
            });
        }
    });

    $(document).ready(function () {
        $("#periodoPartidas").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Periodo"
        });

        $("#tipoPartidas").select2({
            dropdownParent: $("#modal-container"),
            placeholder: "Tipo de partida"
        });

        $("#frmModal").validate({
            submitHandler: function (form) {
                mensaje_confirmacion(
                    '¿Está seguro que desea crear la partida contable?',
                    `Se creará una nueva partida`,
                    `warning`,
                    function (param) {
                        button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                        asyncData(
                            "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                            $("#frmModal").serialize(),
                            function (data) {
                                button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                                if (data == "success") {
                                    mensaje(
                                        "Operación completada:",
                                        'Partida creada con éxito.',
                                        "success"
                                    );
                                    $('#tblPartidas').DataTable().ajax.reload(null, false);
                                    $("#modal-container").modal("hide");
                                    changePage(`<?php echo $_SESSION["currentRoute"]; ?>`, `general-partidas`, `partidaContableId=${data.partidaContableId}`);
                                } else {
                                    mensaje("Aviso:", data, "warning");
                                }
                            }
                        );
                    },
                    'Sí, Crear',
                    `Cancelar`
                );
            }
        });

    });
</script>