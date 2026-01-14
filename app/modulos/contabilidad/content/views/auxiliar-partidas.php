<?php
require_once('../../../../libraries/includes/logic/mgc/datos94.php');
@session_start();
?>
<input type="hidden" id="tipoCuenta" name="tipoCuenta" value="Auxiliar">
<div class="row align-items-center mb-3">
    <div class="col-md-8">
        <h2 class="mb-0">Auxiliar de cuentas</h2>
    </div>
</div>
<hr>
<!-- Filtros -->
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label for="cuentaIdInicio" class="form-label">Número de cuenta contable</label>
        <select style="width: 100%;" class="form-select" id="cuentaIdInicio" name="cuentaIdInicio" required>
            <option></option>
        </select>
    </div>
    <div class="col-md-4">
        <label for="periodoPartidas" class="form-label">Periodo</label>
        <select style="width: 100%;" class="form-select" id="periodoId" name="periodoPartidas" required>
            <option value="0">Sin periodo</option>
            <?php
            $dataPeriodos = $cloud->rows("
          SELECT partidaContaPeriodoId, mes, anio, concat(mesNombre,' ',anio) as periodoNombre
          FROM conta_partidas_contables_periodos
          WHERE flgDelete = ?
          ORDER BY anio ASC, mes ASC ", [0]);
            foreach ($dataPeriodos as $periodo) {
                echo '<option data-anio="' . $periodo->anio . '" data-mes="' . $periodo->mes . '" value="' . $periodo->partidaContaPeriodoId . '">' . $periodo->periodoNombre . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="col-md-2">
        <label for="fechaInicio" class="form-label">Fecha inicio</label>
        <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
    </div>
    <div class="col-md-2">
        <label for="fechaFin" class="form-label">Fecha fin</label>
        <input type="date" class="form-control" id="fechaFin" name="fechaFin" required>
    </div>
</div>
<!-- Botones -->
<div class="row mb-4">
    <div class="col-12 text-end">
        <button type="button" id="btnFiltrar" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar Auxiliar
        </button>
        <button type="button" id="btnLimpiar" class="btn btn-secondary">
            <i class="fas fa-undo-alt"></i> Limpiar
        </button>
    </div>
</div>
<!-- Resumen de cuenta -->
<div class="card shadow-sm mb-2" id="resumenCuenta">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Resumen de cuenta - <span id="nombreCuenta"></span></h5>
    </div>
    <div class="card-body">
        <div class="row text-center g-3">
            <div class="col-md-2 col-6">
                <h6 class="text-muted mb-1">Cuenta</h6>
                <h5 class="mb-0" id="lblCuenta">--</h5>
            </div>
            <div class="col-md-2 col-6">
                <h6 class="text-muted mb-1">Saldo inicial</h6>
                <h5 class="mb-0 fw-bold" id="lblSaldoInicial">0.00</h5>
            </div>
            <div class="col-md-2 col-6">
                <h6 class="text-muted mb-1">Cargos</h6>
                <h5 class="mb-0" id="lblCargo">0.00</h5>
            </div>
            <div class="col-md-2 col-6">
                <h6 class="text-muted mb-1">Abonos</h6>
                <h5 class="mb-0" id="lblAbono">0.00</h5>
            </div>
            <div class="col-md-2 col-6">
                <h6 class="text-muted mb-1">Saldo final</h6>
                <h5 class="mb-0 fw-bold" id="lblSaldoFinal">0.00</h5>
            </div>
            <div class="col-md-2 col-6">
                <button onclick="anterior()" type="button" id="btnFiltrar" class="btn btn-secondary">
                    <i class="fas fa-caret-left"></i>
                </button>
                <button onclick="siguiente()" type="button" id="btnLimpiar" class="btn btn-secondary">
                    <i class="fas fa-caret-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>
<br>
<!-- Tabla -->
<div id="tabla" class="table-responsive">
    <table id="tbl" class="table table-hover align-middle" style="width: 100%;">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Partida</th>
                <th>Numero Partida</th>
                <th>Tipo Partida</th>
                <th>Periodo</th>
                <th>Fecha</th>
                <th>Documento</th>
                <th>Descripción</th>
                <th>Saldo Inicial</th>
                <th>Cargo</th>
                <th>Abono</th>
                <th>Saldo Final</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<script>
    $(document).on("keydown", function(e) {
        switch (e.which) {
            case 33:
                e.preventDefault();
                anterior();
                break;
            case 34:
                e.preventDefault();
                siguiente();
                break;
            case 13:
                e.preventDefault();
                let cuentaId = $("#cuentaIdInicio").val() || 0;
                let periodoId = $("#periodoId").val() || 0;
                let fechaInicio = $("#fechaInicio").val() || "";
                let fechaFin = $("#fechaFin").val() || "";
                if (cuentaId > 0 && (periodoId > 0 || (fechaInicio !== "" && fechaFin !== ""))) {
                    $("#btnFiltrar").trigger("click");
                }
                break;
        }
    });

    function siguiente() {
        asyncData(
            '<?php echo $_SESSION['currentRoute']; ?>content/divs/divDirectorioCuentasContables', {
                numeroCuenta: $("#cuentaIdInicio").val() || 0,
                direccion: "siguiente"
            },
            function(data) {
                if (data.status === "success") {
                    console.log(data);
                    const optionText = `${data.numero}`;
                    const newOptionD = new Option(optionText, data.numero, true, true);
                    $("#cuentaIdInicio").append(newOptionD).val(data.numero).trigger("change");
                    $("#btnFiltrar").trigger("click");
                } else {
                    mensaje("Aviso:", data.message || "No se encontraron datos.", "warning");
                }
            }
        );
    }

    function anterior() {
        asyncData(
            '<?php echo $_SESSION['currentRoute']; ?>content/divs/divDirectorioCuentasContables', {
                numeroCuenta: $("#cuentaIdInicio").val() || 0,
                direccion: "anterior"
            },
            function(data) {
                if (data.status === "success") {
                    console.log(data);
                    const optionText = `${data.numero}`;
                    const newOptionD = new Option(optionText, data.numero, true, true);
                    $("#cuentaIdInicio").append(newOptionD).val(data.numero).trigger("change");
                    $("#btnFiltrar").trigger("click");
                } else {
                    mensaje("Aviso:", data.message || "No se encontraron datos.", "warning");
                }
            }
        );
    }

    function setupDateRange() {
        const $ini = $("#fechaInicio");
        const $fin = $("#fechaFin");

        function updateLimits() {
            const ini = $ini.val();
            if (ini) {
                $fin.attr("min", ini);
            } else {
                $fin.removeAttr("min");
            }
        }

        // Cuando cambia la fecha de inicio → iguala la fecha fin
        $ini.on("change input", function() {
            const ini = $ini.val();
            if (ini) {
                $fin.val(ini);
                updateLimits();
            }
        });

        // Cuando cambia la fecha fin → valida que no sea menor que inicio
        $fin.on("change input", function() {
            const ini = $ini.val();
            const fin = $fin.val();
            if (ini && fin && new Date(fin) < new Date(ini)) {
                $fin.val(ini);
            }
            updateLimits();
        });

        updateLimits();
    }


    function modalPartida(frmData) {
        loadModal(
            "modal-container", {
                modalDev: "-1",
                modalSize: 'fullscreen',
                modalTitle: frmData.tituloModal,
                modalForm: 'partidaContableDetalle',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }


    //TODOS: Finalizar partida

    function abrirPartida(frmData) {
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
                                    $('#modal-container').modal("hide");
                                    loadModal(
                                        "modal-container", {
                                            modalDev: "-1",
                                            modalSize: 'fullscreen',
                                            modalTitle: frmData.tituloModal,
                                            modalForm: 'partidaContableDetalle',
                                            formData: frmData,
                                            buttonCancelShow: true,
                                            buttonCancelText: 'Cerrar'
                                        }
                                    );
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

    function modalImprimirPartida(frmData) {
        loadModal(
            "modal-container", {
                modalDev: "-1",
                modalSize: 'fullscreen',
                modalTitle: frmData.tituloModal,
                modalForm: 'imprimirPartida',
                formData: frmData,
                buttonCancelShow: true,
                buttonCancelText: 'Cerrar'
            }
        );
    }
    $(document).ready(function() {
        $("#resumenCuenta").hide();
        $("#tabla").hide();
        $("#periodoPartidas").select2({
            placeholder: "Periodo contable"
        });
        $('#tbl thead tr')
            .clone(true)
            .addClass('filters')
            .appendTo('#tbl thead');
        $("#cuentasIdInicio").on("change", function() {
            let cuentaId = $("#cuentaIdInicio").val() || 0;
            let periodoId = $("#periodoId").val() || 0;
            let fechaInicio = $("#fechaInicio").val() || "";
            let fechaFin = $("#fechaFin").val() || "";
            if (cuentaId > 0 && (periodoId > 0 || (fechaInicio !== "" && fechaFin !== ""))) {
                $("#btnFiltrar").trigger("click");
            }
        });
        $("#periodoId").on("change", function() {
            let $option = $(this).find("option:selected");
            let mes = parseInt($option.data("mes"), 10);
            let anio = parseInt($option.data("anio"), 10);
            if (mes > 0 && anio > 0) {
                let fechaInicio = new Date(anio, mes - 1, 1);
                let fechaFin = new Date(anio, mes, 0);
                let inicioStr = fechaInicio.toISOString().split("T")[0];
                let finStr = fechaFin.toISOString().split("T")[0];
                $("#fechaInicio").val(inicioStr);
                $("#fechaFin").val(finStr);
                $("#fechaInicio, #fechaFin").prop("readonly", true);
            } else {
                $("#fechaInicio").val("").prop("readonly", false);
                $("#fechaFin").val("").prop("readonly", false);
            }
        });
        $("#cuentaIdInicio").select2({
            placeholder: "Seleccione la cuenta contable de inicio",
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectContaCuenta",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        busquedaSelect: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        setupDateRange();
        let tblPartidas = $('#tbl').DataTable({
            dom: '<"d-flex justify-content-end"B>rt<"row align-items-center"<"col-md-6"l><"col-md-6 text-end"p>>',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Descargar Excel',
                    className: 'btn btn-success',
                    title: function() {
                        const cuenta = $("#cuentaIdInicio").select2('data')[0]?.text || '';
                        return `LIBRO AUXILIAR NUMERO DE CUENTA ${cuenta}`;
                    },
                    exportOptions: {
                        columns: [2, 3, 4, 5, 6, 7, 9, 10, 11] // las correctas
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> Descargar PDF',
                    className: 'btn btn-danger ms-2',
                    orientation: 'portrait', // vertical
                    pageSize: 'LETTER', // tamaño carta
                    title: '', // se deja vacío porque el header es manual
                    exportOptions: {
                        columns: [2, 3, 5, 6, 7, 9, 10, 11]
                    },
                    customize: function(doc) {
                        // === CONFIGURACIÓN GENERAL ===
                        doc.pageMargins = [35, 90, 30, 40]; // izquierda, arriba, derecha, abajo
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 9;
                        doc.styles.tableHeader.alignment = 'center';
                        doc.styles.tableHeader.fillColor = '#2c3e50';
                        doc.styles.tableHeader.color = 'white';
                        doc.styles.tableHeader.bold = true;

                        // === ENCABEZADO MANUAL ===
                        const cuenta = $("#cuentaIdInicio").select2('data')[0]?.text || '';
                        const periodo = $("#periodoId option:selected").text() || '';
                        const fechaEmision = new Date().toLocaleDateString('es-SV');

                        doc['header'] = function() {
                            return {
                                columns: [{
                                        text: 'INDUSTRIAL LA PALMA, S.A. DE C.V.',
                                        alignment: 'left',
                                        fontSize: 10,
                                        bold: true
                                    },
                                    {
                                        text: 'LIBRO AUXILIAR',
                                        alignment: 'center',
                                        fontSize: 11,
                                        bold: true
                                    },
                                    {
                                        text: `Fecha: ${fechaEmision}`,
                                        alignment: 'right',
                                        fontSize: 8
                                    }
                                ],
                                margin: [35, 20, 35, 0]
                            };
                        };

                        // === SUBTÍTULO CENTRAL ===
                        doc.content.splice(0, 0, {
                            text: [{
                                    text: `CUENTA ${cuenta}\n`,
                                    bold: true,
                                    fontSize: 11
                                },
                                {
                                    text: periodo ? `PERIODO: ${periodo}\n` : '',
                                    fontSize: 9
                                },
                                {
                                    text: '\n',
                                    fontSize: 6
                                }
                            ],
                            alignment: 'center',
                            margin: [0, 0, 0, 10]
                        });

                        // === AJUSTAR ANCHOS DE COLUMNA ===
                        const table = doc.content[1].table;
                        table.widths = ['8%', '12%', '10%', '10%', '18%', '14%', '9%', '9%', '10%'];


                        // === FORZAR TÍTULOS DE COLUMNA ===
                        table.body[0] = [{
                                text: 'Número Partida',
                                style: 'tableHeader'
                            },
                            {
                                text: 'Tipo Partida',
                                style: 'tableHeader'
                            },
                            {
                                text: 'Fecha',
                                style: 'tableHeader'
                            },
                            {
                                text: 'Documento',
                                style: 'tableHeader'
                            },
                            {
                                text: 'Descripción',
                                style: 'tableHeader'
                            },
                            {
                                text: 'Cargo',
                                style: 'tableHeader',
                                alignment: 'right'
                            },
                            {
                                text: 'Abono',
                                style: 'tableHeader',
                                alignment: 'right'
                            },
                            {
                                text: 'Saldo Final',
                                style: 'tableHeader',
                                alignment: 'right'
                            }
                        ];

                        // === ALINEAR MONTOS A LA DERECHA ===
                        doc.styles.tableBodyEven.alignment = 'right';
                        doc.styles.tableBodyOdd.alignment = 'right';

                        // === PIE DE PÁGINA (opcional) ===
                        doc['footer'] = function(currentPage, pageCount) {
                            return {
                                columns: [{
                                    text: `Página ${currentPage.toString()} de ${pageCount}`,
                                    alignment: 'right',
                                    fontSize: 8
                                }],
                                margin: [0, 0, 35, 10]
                            };
                        };
                    }
                }



                , {
                    text: '<i class="fas fa-calendar-day"></i> Agrupar por día',
                    className: 'btn btn-primary ms-2',
                    action: function(e, dt, node) {
                        let agrupar = $("#tbl").data("agrupar") === 1 ? 0 : 1;
                        $("#tbl").data("agrupar", agrupar);

                        if (agrupar === 1) {
                            $(node)
                                .removeClass("btn-primary")
                                .addClass("btn-dark")
                                .html('<i class="fas fa-calendar-day"></i> Agrupado por día');
                        } else {
                            $(node)
                                .removeClass("btn-dark")
                                .addClass("btn-primary")
                                .html('<i class="fas fa-calendar-day"></i> Agrupar por día');
                        }

                        dt.ajax.reload();
                    }
                }
            ],
            ajax: {
                method: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableAuxiliarPartida",
                data: function(d) {
                    d.cuentaIdInicio = $("#cuentaIdInicio").val() || 0;
                    d.fechaInicio = $("#fechaInicio").val() || "";
                    d.fechaFin = $("#fechaFin").val() || "";
                    d.agruparPorDia = $("#tbl").data("agrupar") || 0;
                }
            },
            autoWidth: false,
            columns: [{
                    orderable: false
                },
                {
                    orderable: true
                },
                {
                    visible: false
                },
                {
                    visible: false
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
                },
                {
                    visible: false
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
            },
            initComplete: function() {
                let api = this.api();
                api.columns().eq(0).each(function(colIdx) {
                    let cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                    let title = $(cell).text();
                    if ([1, 2, 4, 5, 6, 7, 8, 9, 10, 11].includes(colIdx)) {
                        $(cell).html(
                            '<input type=\"text\" class=\"form-control\" placeholder=\"Buscar\" style=\"width:100%;\"/>'
                        );
                        $('input', cell).off('keyup change').on('keyup change', function() {
                            if (api.column(colIdx).search() !== this.value) {
                                api.column(colIdx).search(this.value).draw();
                            }
                        });
                    } else {
                        $(cell).html('');
                    };
                });
            }
        });



        $("#btnFiltrar").on("click", function() {
            let tipoCuenta = $("#tipoCuenta").val();
            asyncData(
                '<?php echo $_SESSION['currentRoute']; ?>content/divs/getSaldosMayorizacion', {
                    tipoCuenta: tipoCuenta,
                    cuentaIdInicio: $("#cuentaIdInicio").val() || 0,
                    periodoId: $("#periodoId").val() || 0,
                    fechaInicio: $("#fechaInicio").val() || "",
                    fechaFin: $("#fechaFin").val() || ""
                },
                function(data) {
                    if (data.status === "success") {
                        $("#nombreCuenta").text(data.descripcion);
                        $("#lblCuenta").text(data.numero);
                        $("#lblSaldoInicial").text(data.inicial);
                        $("#lblCargo").text(data.cargo);
                        $("#lblAbono").text(data.abono);
                        $("#lblSaldoFinal").text(data.final);
                    } else {
                        mensaje("Aviso:", data.message || "No se encontraron datos.", "warning");
                    }
                }
            );
            $("#resumenCuenta").show();
            $("#tabla").show();
            tblPartidas.ajax.reload();
        });
        $("#btnLimpiar").on("click", function() {
            $("#periodoId").val(0).trigger('change');
            $("#resumenCuenta").hide();
            $("#tabla").hide();
            $("#cuentaIdInicio").val(null).trigger('change');
            $("#cuentaIdFin").val(null).trigger('change');
            $("#fechaInicio").val('');
            $("#fechaFin").val('');
            $("#fechaInicio").removeAttr('max');
            $("#fechaFin").removeAttr('min');
            tblPartidas.ajax.reload();
        });
    });
</script>