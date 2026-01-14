<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

    /*
        POST:
        pagoTransferenciaId
        bancoId
        tipoTransferencia
        estadoPago
    */
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="pagos-transferencias-otros-pagos">
<input type="hidden" id="pagoTransferenciaId" name="pagoTransferenciaId" value="<?php echo $_POST['pagoTransferenciaId']; ?>">
<input type="hidden" id="tipoTransferencia" name="tipoTransferencia" value="<?php echo $_POST['tipoTransferencia']; ?>">
<input type="hidden" id="bancoId" name="bancoId" value="<?php echo $_POST['bancoId']; ?>">
<?php 
    if($_POST['estadoPago'] == "Pendiente") {
?>
        <b>Registro de Otros Pagos</b>
        <div class="row mt-2">
            <div class="col-6 mb-4">
                <div class="form-select-control">
                    <select id="proveedorId" name="proveedorId" style="width:100%;" required>
                        <option></option>
                    </select>
                </div>
            </div>
            <div class="col-6 mb-4">
                <div class="form-select-control">
                    <select id="proveedorCBancariaId" name="proveedorCBancariaId" style="width: 100%;" required>
                        <option></option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6 mb-4">
                <div class="form-outline">
                    <i class="fas fa-edit trailing"></i>
                    <textarea type="text" id="conceptoTransferencia" class="form-control" name="conceptoTransferencia" required></textarea>
                    <label class="form-label" for="conceptoTransferencia">Concepto de la transferencia</label>
                </div>   
            </div>
            <div class="col-6 mb-4">
                <div class="form-outline">
                    <i class="fas fa-dollar-sign trailing"></i>
                    <input type="number" id="montoTransferencia" class="form-control" name="montoTransferencia" step="0.01" min="0.01" required>
                    <label class="form-label" for="montoTransferencia">Monto de la transferencia</label>
                </div>
            </div>
        </div>
        <div class="text-end mb-4">
            <button type="submit" id="btnAgregarOtroPago" class="btn btn-primary btn-sm ttip">
                <i class="fas fa-plus-circle"></i> Agregar Pago
                <span class="ttiptext">Agregar pago a la transferencia</span>
            </button>
        </div>
<?php 
    } else {
        // Otro estado, no mostrar inputs
    }
?>
<div class="table-responsive">
    <table id="tblOtrosPagosTransferencia" class="table table-hover" style="width: 100%;">
        <thead>
            <tr id="filterboxrow-otros-pagos">
                <th>#</th>
                <th>Concepto</th>
                <th>Proveedor</th>
                <th>Cuenta</th>
                <th>Monto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
<script>
    function eliminarDetalleOtrosPagos(frmData) {
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar el pago de la transferencia?`, 
            `Ya no será aplicado ni consolidado en los archivos de banco de la transferencia.`, 
            `warning`, 
            function(param) {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Pago eliminado de la transferencia con éxito`, `success`, function() {
                                $('#tblTransferencias').DataTable().ajax.reload(null, false);
                                $('#tblOtrosPagosTransferencia').DataTable().ajax.reload(null, false);
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
            `Sí, eliminar`,
            `Cancelar`
        );
    }

    $(document).ready(function() {
        $("#proveedorId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'NRC, NIT o Nombre del proveedor',
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectProveedorIdAjax",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        txtBuscar: params.term,
                        tipoProveedor: 'Local'
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

        $("#proveedorCBancariaId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Cuenta bancaria del Proveedor'
        });

        $("#proveedorId").change(function(e) {
            asyncSelect(
                "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectProveedorCBancaria",
                {
                    proveedorId: $(this).val(),
                    tipoTransferencia: "<?php echo $_POST['tipoTransferencia'] ?>",
                    bancoId: "<?php echo $_POST['bancoId'] ?>"
                },
                "proveedorCBancariaId"
            );
        });

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnAgregarOtroPago", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncData(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnAgregarOtroPago", "fas fa-plus-circle", "Agregar pago", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                `Pago agregado a la transferencia con éxito.`,
                                "success"
                            );
                            $('#tblTransferencias').DataTable().ajax.reload(null, false);
                            $('#tblOtrosPagosTransferencia').DataTable().ajax.reload(null, false);
                            $("#frmModal")[0].reset();
                            $("#proveedorId").val(null).trigger("change");
                            //$('#modal-container').modal("hide");
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

        $('#tblOtrosPagosTransferencia thead tr#filterboxrow-otros-pagos th').each(function(index) {
            if(index==1 || index == 2 || index == 3 || index == 4) {
                var title = $('#tblOtrosPagosTransferencia thead tr#filterboxrow-otros-pagos th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}-otros-pagos" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}-otros-pagos">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblOtrosPagosTransferencia.column($(this).index()).search($(`#input${$(this).index()}-otros-pagos`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });

        let tblOtrosPagosTransferencia = $('#tblOtrosPagosTransferencia').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableOtrosPagosTransferencias",
                "data": {
                    "pagoTransferenciaId": '<?php echo $_POST["pagoTransferenciaId"]; ?>'
                }
            },
            "autoWidth": false,
            "footerCallback": function(tfoot) {
                var response = this.api().ajax.json();
                if(response && Object.keys(response.footer).length !== 0) {
                    var td = $(tfoot).find('td');
                    td.eq(2).html(response["footer"][2]);
                    td.eq(4).html(response["footer"][4]);
                } else {
                    // Es la primera vez que sse dibuja la DT
                }
            },
            "columns": [
                null, null, null, null, null, null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3, 4, 5] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });  
    });
</script>