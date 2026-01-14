<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    $yearBD = $_POST['yearBD'];
?>
<input type="hidden" id="extension" name="extension" value="pdf">
<input type="hidden" id="reporteClienteUbicacionId" name="reporteClienteUbicacionId" value="<?php echo $_POST['proveedorUbicacionId']; ?>">
<input type="hidden" id="reporteFacturaId" name="facturaId" value="<?php echo $_POST['facturaId']; ?>">
<!-- Para bit_fel_correos -->
<input type="hidden" id="tipoEnvioMail" name="tipoEnvioMail" value="Manual">
<input type="hidden" id="yearBDModal" name="yearBD" value="<?php echo $yearBD; ?>">
<div class="row">
    <div class="col-md-3">
        <div class="form-select-control mb-4">
            <select id="file" name="file" style="width: 100%;" required>
                <option></option>
                <option value="dte">Documento tributario electrónico</option>
            </select>
        </div>
        <div id="divTipoDTE" class="form-select-control mb-4">
            <select id="reporteTipoDTE" name="tipoDTEId" style="width: 100%;" required>
                <option></option>
                <?php
                    $dataTipoDTE = $cloud->rows("
                        SELECT 
                            tipoDTEId,
                            codigoMH,
                            tipoDTE
                        FROM mh_002_tipo_dte
                        WHERE tipoDTEId NOT IN (7, 8, 11) AND flgDelete = ?
                    ",[0]);
                    foreach ($dataTipoDTE as $dataTipoDTE) {
                        if($dataTipoDTE->tipoDTEId == $_POST['tipoDTEId']) {
                            echo '<option value='.$dataTipoDTE->tipoDTEId.'>'.'('.$dataTipoDTE->codigoMH.') '.$dataTipoDTE->tipoDTE.'</option>';
                        } else {
                            // No dibujar los otros tipos de DTE para que no toquen los filtros
                        }
                    }
                ?>
            </select>
        </div>
        <div id="divFechaEmision" class="form-outline mb-4 input-daterange">
            <input type="date" id="reporteFechaEmision" class="form-control" name="fechaEmision" value="<?php echo date('Y-m-d'); ?>" readonly required />
            <label class="form-label" for="reporteFechaEmision">Fecha de emisión</label>
        </div>
        <!--  
        <div id="divFacturas" class="form-select-control mb-4">
            <select id="reporteFacturaId" name="facturaId" style="width: 100%;" required>
                <option></option>
            </select>
            <div class="mb-1">
                <small>Núm. interno (Núm. Magic) - Total del DTE</small>
            </div>
        </div>
        -->

        <div id="divEnviarCorreo" class="form-select-control mb-4">
            <select id="reporteCorreoCliente" name="correoCliente[]" style="width: 100%;" multiple="multiple">
                <option></option>
            </select>
            <div class="text-end mt-1">
                <button id="btnEnviarCorreo" type="button" class="btn btn-primary btn-sm ttip">
                    <i class="fas fa-envelope-open-text"></i> Enviar correo
                    <span class="ttiptext">Enviar DTE por correo electrónico</span>
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="hideTipoContacto(1)">
                    <i class="fas fa-user"></i> Agregar correo
                </button>
            </div>
           
        </div>
        <div id="divNuevoCorreo">
             
            <div class="form-outline mt-4">
                <input type="email" id="nuevoCorreoCliente" class="form-control" name="nuevoCorreoCliente" />
                <label class="form-label" for="nuevoCorreoCliente">Correo electrónico</label>
            </div>
            <div class="text-end mt-1">
                <button id="btnCorreoCliente" type="button" class="btn btn-primary btn-sm">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button type="button" class="btn btn-danger btn-sm" onclick="hideTipoContacto(0);">
                    <i class="fas fa-times-circle"></i> Cancelar
                </button>
            </div> 
           
        </div> 
    </div>
    <div class="col-md-9">
        <div ></div>
        <object id="divReporte" class="report" data=""></object>
    </div>
</div>
<script>
    /*
    function cargarDTEs(tipoDTE, fechaEmision, flgSubmit = "No") {
        asyncSelect(
            `<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectDTECertificados`,
            {
                tipoDTE: tipoDTE,
                fechaEmision: fechaEmision
            },
            `reporteFacturaId`,
            function() {
                $("#reporteFacturaId").val('<?php echo $_POST['facturaId'] ?>').trigger('change');
                if(flgSubmit == "Sí") {
                    $("#frmModal").submit();
                } else {
                    // Para prevenir error
                }
            }
        );
    }
    */

    function hideTipoContacto(tipo) {
        if(tipo == 1) {
            $("#divEnviarCorreo").hide();
            $("#divNuevoCorreo").show();
        } else {
            $("#nuevoCorreoCliente").val('');
            $("#divEnviarCorreo").show();
            $("#divNuevoCorreo").hide();
        }
    }

    function cargarSelectCorreos() {
        asyncSelect(
            "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectCorreosProveedor",
            {
                "proveedorUbicacionId": $("#reporteClienteUbicacionId").val()
            },
            "reporteCorreoCliente",
            function() {
                // De momento no hay function
                // Agrega una nueva opción al select
                /*
                var nuevaOpcion = $('<option>', {
                    value: 'mromero@indupal.com',
                    text: 'mromero@indupal.com'
                });

                // Agrega la nueva opción al final del select
                $('#reporteCorreoCliente').append(nuevaOpcion);
                var nuevaOpcion = $('<option>', {
                    value: 'xavi.viches@gmail.com',
                    text: 'xavi.viches@gmail.com'
                });
                $('#reporteCorreoCliente').append(nuevaOpcion);
                var nuevaOpcion = $('<option>', {
                    value: 'swg.valdez@gmail.com',
                    text: 'Samuel'
                });

                // Agrega la nueva opción al final del select
                $('#reporteCorreoCliente').append(nuevaOpcion); 
                */
            }
        );
    }

    $(document).ready(function() {
        $("#divTipoDTE").hide();
        $("#divFechaEmision").hide();
        $("#divFacturas").hide();
        $("#divEnviarCorreo").hide();
        $("#divNuevoCorreo").hide();

        cargarSelectCorreos();

        $("#file").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de reporte'
        });

        $("#reporteTipoDTE").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Tipo de documento'
        });

        /*
        $("#reporteFacturaId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'DTE a imprimir'
        });
        */

        $("#reporteCorreoCliente").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Correo(s) del proveedor'
        });

        $("#file").change(function(e) {
            if($(this).val() == "ticket-dte") {
                $("#divTipoDTE").show();
                $("#divFechaEmision").show();
                $("#divFacturas").show();
                $("#divEnviarCorreo").hide();
            } else if($(this).val() == "dte") {
                $("#divTipoDTE").show();
                $("#divFechaEmision").show();
                $("#divFacturas").show();
                $("#divEnviarCorreo").show();
            } else {
                $("#divTipoDTE").hide();
                $("#divFechaEmision").hide();
                $("#divFacturas").hide();
                $("#divEnviarCorreo").hide();
            }
        });
        /*
        $("#reporteTipoDTE").change(function(e) {
            cargarDTEs($(this).val(), $("#reporteFechaEmision").val(), "No");
        });

        $("#reporteFechaEmision").change(function(e) {
            cargarDTEs($("#reporteTipoDTE").val(), $(this).val(), "Sí");
        });
        */

        $("#btnCorreoCliente").click(function(e) {
            if($("#nuevoCorreoCliente").val() == "") {
                mensaje("Aviso:", "Debe ingresar el correo del proveedor", "warning");
            } else {                
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation",
                    {
                        "typeOperation": "insert",
                        "operation": "correo-proveedor",
                        "nuevoCorreoCliente": $("#nuevoCorreoCliente").val(),
                        "proveedorUbicacionId": $("#reporteClienteUbicacionId").val(),
                        "tipoDTEId": $("#reporteTipoDTE").val()
                    },
                    function(data) {
                        if(data == "success") {
                            mensaje("Operación completada:", "Correo electrónico registrado con éxito. Por favor, seleccione el correo del proveedor para enviar su DTE", "success");
                            cargarSelectCorreos();
                            hideTipoContacto(0);
                        } else {
                            mensaje("Aviso:", data, "warning");
                        }
                    }
                );
            }
        });

        $("#btnEnviarCorreo").click(function(e) {
            // Obtener el número de opciones seleccionadas en el select
            var numOpcionesSeleccionadas = $('#reporteCorreoCliente option:selected').length;
            
            // Validar si hay al menos una opción seleccionada
            if (numOpcionesSeleccionadas === 0) {
                mensaje("Aviso:", "Debe seleccionar al menos un correo electrónico", "warning");
            } else {                
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>reportes",
                    $("#frmModal").serialize()+'&flgCorreo=' + 1,
                    function(data) {
                        asyncData(
                            data,
                            {x:''},
                            function(data) {
                                mensaje_do_aceptar(
                                    "Operación completada:", 
                                    "Correo electrónico enviado con éxito", 
                                    "success",
                                    function() {                                    
                                        $("#reporteCorreoCliente").val([]).trigger('change');
                                        listarRetencion();
                                    }
                                );
                            }
                        )
                    }
                );
            }
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                /* if($("#file").val() == "dte") {
                    asyncData(
                        "<?php echo $_SESSION['currentRoute']; ?>reports/html/facturaDTE",
                        $("#frmModal").serialize(),
                        function(data) {
                            // Mantener el botón disabled para prevenir que generen más de uno sino carga
                            button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                            $("#divReporte").html(data);
                        }
                    );
                } else {        */             
                    asyncData(
                        "<?php echo $_SESSION['currentRoute']; ?>reportes", 
                        $("#frmModal").serialize(),
                        function(data) {
                            // Mantener el botón disabled para prevenir que generen más de uno sino carga
                            button_icons("btnModalAccept", "fas fa-print", "Generar reporte", "enabled");
                            $("#divReporte").html(data);
                        }
                    );
                // }
            }
        });

        $("#file").val('<?php echo $_POST['file'] ?>').trigger('change');
        $("#reporteTipoDTE").val('<?php echo $_POST['tipoDTEId'] ?>').trigger('change');
        $("#reporteFechaEmision").val('<?php echo $_POST['fechaEmision'] ?>').trigger('change');
        $("#frmModal").submit();
    });
</script>