<?php 
	require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();

?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="parametrizacion-compartida-vendedores">
<input type="hidden" id="idDParam" name="idDParam" value="<?php echo $_POST['idDParam'];?>">

<div id="formVendedor" style="display: none;">
    <div class="row">
        <div class="col-6">
            <div id="divVendedorId" class="form-select-control mb-4">
                <select id="vendedorId" name="vendedorId" style="width: 100%;" required>
                    <option></option>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-outline">
                <i class="fas flist-ul trailing"></i>
                <input type="number" id="porcentaje" class="form-control" name="porcentaje" required>
                <label class="form-label" for="nombreFam">Porcentaje</label>
            </div>
        </div>
    </div>
    <div class="text-end">
        <button type="submit" id="agregar" class="btn btn-primary btn-sm">
            <i class="fas fa-save"></i> Guardar
        </button> 
        <button id="btnCancel" type="button" class="btn btn-secondary btn-sm">
            <i class="fas fa-times-circle"></i> Cancelar
        </button>
    </div>
    <hr>
</div>
<div class="text-end" id="btn-nuevo">
    <button type="button" id="nuevaParam" class="btn btn-primary btn-sm">
        <i class="fas fa-save"></i> Nueva parametrización
    </button>
</div>
<div class="table-responsive">
    <table id="tblParamVend" class="table table-hover mt-3" style="width: 100%;">
    <thead>
        <tr id="filterboxrow-tblParamVend">
            <th>#</th>
            <th>Vendedor</th>
            <th>Porcentaje compartido</th>
            <th>Acciones</th>
        </tr>
    </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    
    function delParamComparDet(frmData){
        mensaje_confirmacion(
            `¿Está seguro que desea eliminar esta parametrización?`, 
            ``, 
            `warning`, 
            function(param) {
                asyncData(
                    '<?php echo $_SESSION["currentRoute"]; ?>/transaction/operation', 
                    frmData,
                    function(data) {
                        if(data == "success") {
                            mensaje_do_aceptar(`Operación completada:`, `Parametrización eliminada con éxito`, `success`, function() {
                                $(`#tblParamVend`).DataTable().ajax.reload(null, false);
                                
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
    $(document).ready(function() {
        $("#nuevaParam").click(function(){
            $("#formVendedor").show();
            $("#btn-nuevo").hide();
        });
        $("#btnCancel").click(function(){
            $("#formVendedor").hide();
            $("#btn-nuevo").show();

            $('#frmModal').trigger("reset");
            $('#vendedorId').trigger('change');
        });

        $("#vendedorId").select2({
            dropdownParent: $('#modal-container'),
            placeholder: 'Vendedor', 
            ajax: {
                type: "POST",
                url: "<?php echo $_SESSION['currentRoute']; ?>content/divs/selectListarVendedoresComisionesAjax",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        txtBuscar: params.term, //Input de búsqueda
                        idDParam: $("#idDParam").val()
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

        $("#frmModal").validate({
            submitHandler: function(form) {
                button_icons("btnModalAccept", "fas fa-circle-notch fa-spin", "Cargando...", "disabled");
                asyncDoDataReturn(
                    "<?php echo $_SESSION['currentRoute']; ?>transaction/operation/", 
                    $("#frmModal").serialize(),
                    function(data) {
                        button_icons("btnModalAccept", "fas fa-save", "Guardar", "enabled");
                        if(data == "success") {
                            mensaje(
                                "Operación completada:",
                                `Porcentaje de comisión asignado correctamente`,
                                "success",
                            );
                            // $('#modal-container').modal("hide");
                            $(`#tblComisionCompa`).DataTable().ajax.reload(null,false);
                            $(`#tblParamVend`).DataTable().ajax.reload(null,false);
                            $("#formVendedor").hide();
                            $("#btn-nuevo").show();

                            $('#frmModal').trigger("reset");
                            $('#vendedorId').trigger('change');
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


        $('#tblParamVend thead tr#filterboxrow th').each(function(index) {
            //if(index == 1) {
                var title = $('#tblParamVend thead tr#filterboxrow th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblParamVend.column($(this).index()).search($(`#input${$(this).index()}`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            //} else {
            //}
        });

        let tblParamVend = $('#tblParamVend').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableParamCompDetalle",
                "data": function() { // En caso que se quiera enviar variable a la consulta
                    return {
                        "id": $("#idDParam").val()
                    }
                }
            },
            "autoWidth": false,
            "columns": [
            	null,
                {"width": "35%"},
                {"width": "25%"},
                null
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>