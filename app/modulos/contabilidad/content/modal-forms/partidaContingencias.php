<?php
require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
include("../../../../../libraries/includes/logic/functions/funciones-conta.php");
@session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$jsonPrueba = array(
    "typeOperation" => "update",
    "operation" => "prueba",
    "id" => 2
);

$currentPeriodo = buscarPeriodoActual($cloud);

$funcionP = htmlspecialchars(json_encode($jsonPrueba));
?>

<!-- Filtros de busqueda -->
<div class="row mb-2">
    <div class="col-md-4 mb-2">
        <div class="form-outline">
            <i class="fas fa-list-ol trailing"></i>
            <input type="text" id="numPartidaC" name="numPartida" class="form-control" />
            <label class="form-label" for="numPartida">Número de partida</label>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="form-outline">
            <input type="date" id="fechaInicioC" class="form-control" name="fechaInicio" required>
            <label class="form-label" for="fechaInicio">Desde</label>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="form-outline">
            <input type="date" id="fechaFinalC" class="form-control" name="fechaFinal" required>
            <label class="form-label" for="fechaFinal">Hasta</label>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="form-select-control">
            <select class="form-select" id="periodoPartidasC" name="periodoPartidas" style="width:100%;" required>
                <option value="0">Todos los periodos</option>
                <?php
                $dataPeriodos = $cloud->rows("
                    SELECT partidaContaPeriodoId,mes,anio,concat(mesNombre,' ',anio) as periodoNombre
                    FROM conta_partidas_contables_periodos
                    WHERE flgDelete = ?
                    ORDER BY  anio ASC, mes ASC ", [0,]);
                foreach ($dataPeriodos as $periodo) {
                    //$selected = ($periodo->partidaContaPeriodoId == $currentPeriodo) ? ' selected' : '';
                    echo '<option data-anio="' . $periodo->anio . '" data-mes="' . $periodo->mes . '" value="' . $periodo->partidaContaPeriodoId . '">' . $periodo->periodoNombre . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="form-select-control">
            <select class="form-select" id="tipoPartidasC" name="tipoPartidas" style="width:100%;" required>
                <option value="0">Todos los tipos</option>
                <?php

                //! Volver a agregar flgDelete = 0
                
                $dataTipoPartida = $cloud->rows("
                        SELECT tipoPartidaId, descripcionPartida 
                        FROM cat_tipo_partida_contable  ORDER BY descripcionPartida ASC", []);

                foreach ($dataTipoPartida as $tipo) {

                    echo "<option value='$tipo->tipoPartidaId'>$tipo->descripcionPartida</option>";
                }
                ?>
            </select>

        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-6">
        <button type="button" id="btnLimpiarC" class="btn btn-secondary">
            <i class="fas fa-undo-alt"></i> Limpiar
        </button>
        <button type="button" id="btnFiltrarC" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar Partida
        </button>
        <!--<button type="button" id="btnFiltrar" class="btn btn-primary" onclick="prueba(<?= $funcionP ?>)"><i class="fas fa-rotate"></i>Sincronizar partidas</button>-->
    </div>
</div>

<hr>
<div class="table-responsive">
    <table id="tblPartidasC" class="table table-hover mt-4" style="width: 100%;">
        <thead>
            <tr id="filterboxrowC">
                <th>#</th>
                <th>Número de partida</th>
                <th>Tipo de partida</th>
                <th>Periodo</th>
                <th>Cargo</th>
                <th>Abono</th>
                <th>Balance</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>

    var tblPartidasC = null;

    $(document).ready(function () {

        $("#tipoPartidasC").select2({
            dropdownParent: $('#modal-container'),
            placeholder: "Tipo de partida contable"
        });

        $("#periodoPartidasC").select2({
            dropdownParent: $('#modal-container'),
            placeholder: "Periodo contable"
        });

        tblPartidasC = $('#tblPartidasC').DataTable({
            dom: 'lrtip',
            ajax: {
                method: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePartidasContables",
                data: function (d) {
                    d.numPartida = $("#numPartidaC").val() || 0;
                    d.periodo = $("#periodoPartidasC").val() || 0;
                    d.tipo = $("#tipoPartidasC").val() || 0;
                    d.inicio = $("#fechaInicioC").val() || "";
                    d.final = $("#fechaFinalC").val() || "";
                    d.estado = true;
                    d.automatica = null;
                }
            },
            autoWidth: false,
            columns: [
                { orderable: true },
                { orderable: true },
                { orderable: true },
                { orderable: true },
                { orderable: false },
                { orderable: false },
                { orderable: false },
                { orderable: true },
                { orderable: false }
            ],
            language: {
                url: "../libraries/packages/js/spanish_dt.json"
            }
        });

        // Filtro manual
        $("#btnFiltrarC").on("click", function () {
            tblPartidasC.ajax.reload();
        });

        // Limpieza de filtros y recarga
        $("#btnLimpiarC").on("click", function () {
            $("#numPartidaC").val(null).trigger('change');
            $("#periodoPartidasC").val(<?= $currentPeriodo ?>).trigger('change');
            $("#tipoPartidasC").val(0).trigger('change');
            $("#fechaInicioC").val(null).trigger('change');
            $("#fechaFinalC").val(null).trigger('change');
            tblPartidasC.ajax.reload();
        });
    });
</script>