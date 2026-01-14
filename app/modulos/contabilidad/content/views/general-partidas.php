<?php
require_once ('../../../../libraries/includes/logic/mgc/datos94.php');
include ('../../../../libraries/includes/logic/functions/funciones-conta.php');
@session_start();

$jsonPrueba = array(
    'typeOperation' => 'update',
    'operation' => 'prueba',
    'id' => 2
);

$currentPeriodo = buscarPeriodoActual($cloud);

$funcionP = htmlspecialchars(json_encode($jsonPrueba));
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Partidas contables</h2>
    </div>
</div>
<hr>

<!-- Filtros de busqueda -->
<div class="row mb-2">
    <div class="col-md-4 mb-2">
        <div class="form-outline">
            <i class="fas fa-list-ol trailing"></i>
            <input type="text" id="numPartida" name="numPartida" class="form-control" />
            <label class="form-label" for="numPartida">Número de partida</label>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="form-outline">
            <input type="date" id="fechaInicio" class="form-control" name="fechaInicio" required>
            <label class="form-label" for="fechaInicio">Desde</label>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="form-outline">
            <input type="date" id="fechaFinal" class="form-control" name="fechaFinal" required>
            <label class="form-label" for="fechaFinal">Hasta</label>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="form-select-control">
            <select class="form-select" id="periodoPartidas" name="periodoPartidas" style="width:100%;" required>
                <option value="0">Todos los periodos</option>
                <?php
                $dataPeriodos = $cloud->rows("
                    SELECT partidaContaPeriodoId,mes,anio,concat(mesNombre,' ',anio) as periodoNombre
                    FROM conta_partidas_contables_periodos
                    WHERE flgDelete = ?
                    ORDER BY  anio ASC, mes ASC ", [
                    0,
                ]);
                foreach ($dataPeriodos as $periodo) {
                    // $selected = ($periodo->partidaContaPeriodoId == $currentPeriodo) ? ' selected' : '';
                    echo '<option data-anio="' . $periodo->anio . '" data-mes="' . $periodo->mes . '" value="' . $periodo->partidaContaPeriodoId . '">' . $periodo->periodoNombre . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <div class="form-select-control">
            <select class="form-select" id="tipoPartidas" name="tipoPartidas" style="width:100%;" required>
                <option value="0">Todos los tipos</option>
                <?php

                // ! Volver a agregar flgDelete = 0

                $dataTipoPartida = $cloud->rows('
                        SELECT tipoPartidaId, descripcionPartida 
                        FROM cat_tipo_partida_contable', []);

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
        <button type="button" id="btnLimpiar" class="btn btn-secondary">
            <i class="fas fa-undo-alt"></i> Limpiar
        </button>
        <button type="button" id="btnFiltrar" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar Partida
        </button>
        <!--<button type="button" id="btnFiltrar" class="btn btn-primary" onclick="prueba(<?= $funcionP ?>)"><i class="fas fa-rotate"></i>Sincronizar partidas</button>-->
    </div>
    <div class="col-6 text-end" id="accionesDerecha">
        <span id="contingencias"></span>
        <button class="btn btn-primary" onclick='generadorDePartidas();'>
            <i class="fas fa-file-import"></i> Generador de Partidas
        </button>
        <button class="btn btn-primary"
            onclick='changePage(`<?php echo $_SESSION['currentRoute']; ?>`, `general-partida`, `partidaContableId=0`)'>
            <i class="fas fa-plus-circle"></i> Nueva partida
        </button>

    </div>
</div>
<div class="mb-3 d-flex">
    <button class="btn btn-outline-primary flex-fill me-2 py-2" id="filtroGenerales">Generales</button>
    <button class="btn btn-outline-primary flex-fill py-2" id="filtroAutomaticas">Automáticas</button>
</div>
<hr>
<div class="table-responsive">
    <table id="tblPartidas" class="table table-hover mt-4" style="width: 100%;">
        <thead>
            <tr id="filterboxrow">
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
//!Funcion de Prueba 
//function prueba(frmData) { asyncData("<?php echo $_SESSION['currentRoute']; ?>transaction/operation", frmData, function (data) { console.log(data); }) }

var onFilterAutomatica = false;
var tblPartidas = null;


function abrirPartida(frmData, reenvio) {
    mensaje_confirmacion(
        '¿Está seguro que desea habilitar esta partida?',
        `Una vez habilitada, la información actual podrá ser modificada.`,
        `warning`,
        function(param) {
            asyncData(
                "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                frmData,
                function(data) {
                    if (data == "success") {
                        mensaje_do_aceptar(
                            "Partida Habilitada",
                            "",
                            "success",
                            function() {
                                changePage(`<?php echo $_SESSION['currentRoute']; ?>`, `general-partida`,
                                    `data=${JSON.stringify(reenvio)}`);
                            }
                        );
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

function clearAll() {
    //partidas automaticas
    onFilterAutomatica = false;
    tblPartidas.ajax.reload();
}

function automaticas() {
    //partidas automaticas
    onFilterAutomatica = true;
    tblPartidas.ajax.reload();
}

function contingencias() {
    //partida-contable-contingencia
    asyncData(
        "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", {
            typeOperation: "update",
            operation: "partida-contable-contingencia"
        },
        function(data) {
            if (data.status == "success") {
                tblPartidas.ajax.reload();
                loadModal(
                    "modal-container", {
                        modalDev: "-1",
                        modalSize: 'fullscreen',
                        modalTitle: "Gestión de partidas en contingencias",
                        modalForm: 'partidaContingencias',
                        formData: null,
                        buttonCancelShow: true,
                        buttonCancelText: 'Cerrar'
                    }
                );
            }
        }
    );
};

//TODOS: Generador de partidas

function generadorDePartidas() {
    loadModal(
        "modal-container", {
            modalDev: "-1",
            modalSize: 'lg',
            modalTitle: "Gestión de partidas automáticas",
            modalForm: 'generadorParidas',
            formData: null,
            buttonCancelShow: true,
            buttonCancelText: 'Cerrar'
        }
    );
}


function eliminar(frmData) {
    mensaje_confirmacion(
        `¿Desea eliminar este registro contable?`,
        `Una vez eliminado, no podrá recuperarlo. Esta acción es irreversible.`,
        `warning`,
        (param) => {
            // Segunda confirmación: advertencia final
            mensaje_confirmacion(
                `Confirmación final`,
                `Está a punto de eliminar definitivamente este registro. 
                <br><b>¿Confirma que desea continuar?</b>`,
                `error`,
                (param) => {
                    asyncData(
                        '<?php echo $_SESSION['currentRoute']; ?>/transaction/operation',
                        frmData,
                        (data) => {
                            if (data == "success") {
                                $(`#tblPartidas`).DataTable().ajax.reload(null, false);

                            } else {
                                mensaje(
                                    "Aviso:",
                                    data,
                                    "warnig"
                                );
                                $(`#tblPartidas`).DataTable().ajax.reload(null, false);
                            }
                        }
                    );
                },
                `Sí, eliminar definitivamente`,
                `Cancelar`
            );
        },
        `Eliminar`,
        `Cancelar`
    );
}


$(document).ready(function() {

    $("#tipoPartidas").select2({
        placeholder: "Tipo de partida contable"
    });

    $("#periodoPartidas").select2({
        placeholder: "Periodo contable"
    });

    asyncData(
        '<?php echo $_SESSION['currentRoute']; ?>content/divs/partidasContingencias', null,
        function(data) {
            $("#contingencias").html(data);
        }
    );

    tblPartidas = $('#tblPartidas').DataTable({
        dom: 'lrtip',
        ajax: {
            method: "POST",
            url: "<?php echo $_SESSION['currentRoute']; ?>content/tables/tablePartidasContables",
            data: function(d) {
                d.numPartida = $("#numPartida").val() || 0;
                d.periodo = $("#periodoPartidas").val() || 0;
                d.tipo = $("#tipoPartidas").val() || 0;
                d.inicio = $("#fechaInicio").val() || "";
                d.final = $("#fechaFinal").val() || "";
                d.estado = null;
                d.automatica = onFilterAutomatica ? true : null;
            }
        },
        autoWidth: false,
        columns: [{
                orderable: true
            },
            {
                orderable: true
            },
            {
                orderable: true
            },
            {
                orderable: true
            },
            {
                orderable: true
            },
            {
                orderable: true
            },
            {
                orderable: true
            },
            {
                orderable: true
            },
            {
                orderable: false
            }
        ],
        language: {
            url: "../libraries/packages/js/spanish_dt.json"
        }
    });

    // Filtro manual
    $("#btnFiltrar").on("click", function() {
        tblPartidas.ajax.reload();
    });

    function activarFiltro(botonActivo) {
        // Primero quitamos la clase de todos los botones
        $('#filtroGenerales, #filtroAutomaticas').removeClass('btn-primary').addClass('btn-outline-primary');

        // Ponemos la clase activa al botón seleccionado
        $(botonActivo).removeClass('btn-outline-primary').addClass('btn-primary');
    }


    $('#filtroGenerales').on('click', function() {
        clearAll();
        activarFiltro(this);
    });
    $('#filtroAutomaticas').on('click', function() {
        automaticas();
        activarFiltro(this);
    });

    // Limpieza de filtros y recarga
    $("#btnLimpiar").on("click", function() {
        $("#numPartida").val(null).trigger('change');
        $("#periodoPartidas").val(<?= $currentPeriodo ?>).trigger('change');
        $("#tipoPartidas").val(0).trigger('change');
        $("#fechaInicio").val(null).trigger('change');
        $("#fechaFinal").val(null).trigger('change');
        onFilterAutomatica = false;
        tblPartidas.ajax.reload();
    });
});
</script>