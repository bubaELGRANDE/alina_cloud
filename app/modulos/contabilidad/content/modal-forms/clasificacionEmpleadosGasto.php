<?php 
    require_once("../../../../../libraries/includes/logic/mgc/datos94.php");
    @session_start();
    
?>
<input type="hidden" id="typeOperation" name="typeOperation" value="insert">
<input type="hidden" id="operation" name="operation" value="clasificacion-gasto-empleado">
<input type="hidden" id="clasifGastoSalarioId" name="clasifGastoSalarioId" value="<?php echo $_POST['clasifGastoSalarioId']; ?>">

<div class="row">
    <div class="col-md-9">
        <div class="form-select-control mb-4">
            <select class="form-select" id="expedientes" name="expedientes[]" style="width:100%;" multiple required>
                <option></option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-select-control mb-4">
            <button class="btn btn-primary" id="agregarEmpleados" name="agregarEmpleados" onclick="">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>                    

    </div>
</div>

<div class="table-responsive">
    <table id="tblEmpGasto" class="table table-hover">
        <thead>
            <tr id="filterboxrow-tblEmpGasto">
                <th>#</th>
                <th>Empleado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script>
    function cargarEmpleados() {
        asyncSelect(
            "<?php echo $_SESSION['currentRoute']; ?>/content/divs/selectListarExpedientesClasificacion",
            {
                clasifGastoSalarioId: 0
            },
            `expedientes`
        );
    }
    $(document).ready(function() {
        $("#expedientes").select2({
            placeholder: 'Empleado(s)',
            dropdownParent: $('#modal-container')
        });

        cargarEmpleados();
        
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
                                "Empleado agregado con éxito a la clasificación.",
                                "success"
                            );
                            $("#tblClasiEmp").DataTable().ajax.reload(null, false);
                            $("#tblEmpGasto").DataTable().ajax.reload(null, false);
                            cargarEmpleados();
                            $("#expedientes").val([]).trigger('change');
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

        $('#tblEmpGasto thead tr#filterboxrow-tblEmpGasto th').each(function(index) {
            if(index == 1) {
                var title = $('#tblEmpGasto thead tr#filterboxrow-tblEmpGasto th').eq($(this).index()).text();
                $(this).html(`<div class="form-outline mb-1"><input id="input${$(this).index()}tblEmpGasto" type="text" class="form-control" /><label class="form-label" for="input${$(this).index()}tblEmpGasto">Buscar</label></div>${title}`);
                $(this).on('keyup change', function() {
                    tblEmpGasto.column($(this).index()).search($(`#input${$(this).index()}tblEmpGasto`).val()).draw();
                });
                document.querySelectorAll('.form-outline').forEach((formOutline) => {
                    new mdb.Input(formOutline).init();
                });
            } else {
            }
        });
        
        let tblEmpGasto = $('#tblEmpGasto').DataTable({
            "dom": 'lrtip',
            "ajax": {
                "method": "POST",
                "url": "<?php echo $_SESSION['currentRoute']; ?>content/tables/tableClasificacionEmpleadoGasto",
                "data": { // En caso que se quiera enviar variable a la consulta
                    "clasifGastoSalarioId": "<?php echo $_POST['clasifGastoSalarioId']; ?>"
                }
            },
            "autoWidth": false,
            "columns": [
                {"width": "5%"},
                null,
                {"width": "30%"}
            ],
            "columnDefs": [
                { "orderable": false, "targets": [1, 2] }
            ],
            "language": {
                "url": "../libraries/packages/js/spanish_dt.json"
            }
        });
    });
</script>